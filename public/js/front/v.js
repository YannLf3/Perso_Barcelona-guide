/**
 * V (Vue) : seul objet autorisé à manipuler le DOM du site public.
 * Les événements sont déclarés par délégation dans V.init() et transmis au contrôleur C.
 * Les modales sont clonées depuis les balises <template> rendues par les vues PHP.
 */
const V = {
  /**
   * Déclare tous les écouteurs d'événements (délégation sur le document).
   */
  init() {
    document.addEventListener('click', (ev) => {
      const actionEl = ev.target.closest('[data-action]');
      const action = actionEl ? actionEl.dataset.action : '';

      if (action === 'show-intro') return C.handler_showIntro(ev);
      if (action === 'show-tours') return C.handler_showTours(ev);
      if (action === 'show-contact') return C.handler_showContact(ev);
      if (action === 'toggle-nav') return C.handler_toggleNav(ev);
      if (action === 'book-tour') return C.handler_bookTour(ev);
      if (ev.target.closest('[data-modal-close]')) return C.handler_closeModal(ev);
      if (ev.target.closest('[data-modal-book]')) return C.handler_bookFromModal(ev);

      // Un clic sur le fond (backdrop) cible directement l'élément <dialog>
      if (ev.target instanceof HTMLDialogElement) return C.handler_closeModal(ev);

      if (ev.target.closest('[data-card]')) return C.handler_openCard(ev);
    });

    document.addEventListener('keydown', (ev) => {
      if (ev.key !== 'Enter' && ev.key !== ' ') return;

      const target = ev.target;

      if (target.closest('[data-action="book-tour"]')) return C.handler_bookTour(ev);
      if (target.matches('[data-card]')) return C.handler_openCard(ev);
      if (target.matches('[data-action="show-intro"]')) return C.handler_showIntro(ev);
    });

    document.addEventListener('change', (ev) => {
      const action = ev.target.dataset ? ev.target.dataset.action : '';

      if (action === 'change-lang') return C.handler_changeLang(ev);
      if (action === 'filter-tours') return C.handler_filterTours(ev);
    });

    document.addEventListener('submit', (ev) => {
      if (ev.target.matches('[data-form="contact"]')) return C.handler_sendMessage(ev);
    });

    // L'événement error ne remonte pas : on l'écoute en phase de capture
    document.addEventListener('error', (ev) => V.markBrokenSlide(ev.target), true);

    V.markBrokenSlides();
    V.initCursorGlow();
  },

  /**
   * Langue de la page (attribut lang du <html>).
   * @returns {string}
   */
  getLang() {
    return document.documentElement.lang;
  },

  /**
   * Jeton CSRF signé placé dans le <head> par le serveur.
   * @returns {string}
   */
  getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');

    return meta ? meta.content : '';
  },

  /**
   * Valeur choisie dans le filtre des visites.
   * @returns {string}
   */
  getFilterValue() {
    return document.querySelector('#tour-group-filter').value;
  },

  /**
   * Message d'erreur générique du formulaire (rendu par le serveur dans la langue de la page).
   * @param {HTMLFormElement} form
   * @returns {string}
   */
  getFormError(form) {
    return form.dataset.error || '';
  },

  /**
   * Indique si l'écran est en affichage mobile (même seuil que le CSS).
   * @returns {boolean}
   */
  isMobile() {
    return window.matchMedia('(max-width: 768px)').matches;
  },

  /**
   * Remonte en haut de la page.
   */
  scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  },

  /**
   * Fait défiler la page jusqu'à un élément.
   * @param {string} selector
   */
  scrollToElement(selector) {
    const el = document.querySelector(selector);

    if (el) {
      el.scrollIntoView({ behavior: 'smooth' });
    }
  },

  /**
   * Ouvre ou ferme le menu mobile et met à jour aria-expanded.
   * @param {boolean} [forceOpen] Force l'état (sinon bascule)
   */
  toggleNav(forceOpen) {
    const nav = document.querySelector('nav[data-open]');
    const button = document.querySelector('[data-action="toggle-nav"]');
    const next = forceOpen === undefined ? nav.dataset.open !== 'true' : forceOpen;

    nav.dataset.open = String(next);
    button.setAttribute('aria-expanded', String(next));
  },

  /**
   * Ferme le menu mobile s'il est ouvert.
   */
  closeNav() {
    const nav = document.querySelector('nav[data-open]');

    if (nav && nav.dataset.open === 'true') {
      V.toggleNav(false);
    }
  },

  /**
   * Redirige vers la même page dans une autre langue (la langue fait partie de l'URL).
   * @param {string} lang
   */
  navigateToLang(lang) {
    const segments = window.location.pathname.split('/');
    segments[1] = lang;
    window.location.assign(segments.join('/'));
  },

  /**
   * Affiche seulement les cartes de visite dont l'identifiant est dans la liste.
   * @param {Array<string>} visibleIds
   */
  showTourCards(visibleIds) {
    const cards = document.querySelectorAll('#tour-list [data-card="tour"]');

    cards.forEach((card) => {
      card.hidden = !visibleIds.includes(card.dataset.tourId);
    });

    document.querySelector('[data-tour-empty]').hidden = visibleIds.length > 0;
  },

  /**
   * Identifiant de la carte (visite) dont l'élément fait partie.
   * @param {Element} target
   * @returns {{type: string, id: string}|null}
   */
  readCard(target) {
    const card = target.closest('[data-card]');

    if (!card) return null;

    return {
      type: card.dataset.card,
      id: card.dataset.tourId,
    };
  },

  /**
   * Ouvre la modale de détail d'une visite.
   * @param {Object} tour
   */
  openTourModal(tour) {
    const template = document.querySelector('#tour-modal-template');
    const persons = template.dataset.persons;
    const capacity = tour.capacity ? `${tour.capacity} ${persons}` : '-';

    V.openModal(template, (dialog) => {
      V.fillMedia(dialog, tour.image, tour.title, tour.image_width, tour.image_height);
      dialog.querySelector('[data-modal-title]').textContent = tour.title;
      dialog.querySelector('[data-modal-duration]').textContent = tour.duration;
      dialog.querySelector('[data-modal-capacity]').textContent = capacity;
      dialog.querySelector('[data-modal-summary]').textContent = tour.summary;
    });
  },

  /**
   * Remplit la zone image d'une modale, ou affiche le bloc "pas de photo".
   * @param {HTMLDialogElement} dialog
   * @param {string} src
   * @param {string} alt
   * @param {number|null} width
   * @param {number|null} height
   */
  fillMedia(dialog, src, alt, width, height) {
    const media = dialog.querySelector('[data-modal-media]');
    const placeholder = dialog.querySelector('[data-modal-placeholder]');
    const image = dialog.querySelector('[data-modal-image]');

    media.hidden = !src;
    placeholder.hidden = Boolean(src);

    if (src) {
      image.src = src;
      image.alt = alt;

      if (width && height) {
        image.width = width;
        image.height = height;
      }
    }
  },

  /**
   * Clone la modale depuis son <template>, la remplit et l'ouvre avec showModal().
   * @param {HTMLTemplateElement} template
   * @param {(dialog: HTMLDialogElement) => void} fill
   */
  openModal(template, fill) {
    V.closeModal();

    const dialog = document.importNode(template.content, true).firstElementChild;
    fill(dialog);

    document.body.append(dialog);
    document.body.classList.add('overflow-hidden');

    dialog.addEventListener('close', () => {
      dialog.remove();
      document.body.classList.remove('overflow-hidden');
    });

    dialog.showModal();
  },

  /**
   * Ferme la modale ouverte, s'il y en a une.
   */
  closeModal() {
    const dialog = document.querySelector('body > dialog');

    if (dialog) {
      dialog.close();
    }
  },

  /**
   * Affiche la réponse du serveur sous le formulaire de contact.
   * @param {{success: boolean, message: string}} answer
   */
  renderMessage(answer) {
    const box = document.querySelector('#message-box');

    box.textContent = answer.message || '';
    box.dataset.state = answer.success ? 'success' : 'error';
  },

  /**
   * Lit les champs d'un formulaire.
   * @param {HTMLFormElement} form
   * @returns {FormData}
   */
  readForm(form) {
    return new FormData(form);
  },

  /**
   * Vide le formulaire.
   * @param {HTMLFormElement} form
   */
  resetForm(form) {
    form.reset();
  },

  /**
   * Désactive le bouton d'envoi pendant l'appel réseau.
   * @param {HTMLFormElement} form
   * @param {boolean} isSending
   */
  setSending(form, isSending) {
    form.querySelector('button[type="submit"]').disabled = isSending;
  },

  /**
   * Remplace une image cassée du carrousel par le fond dégradé de secours.
   * @param {EventTarget} target
   */
  markBrokenSlide(target) {
    if (target instanceof HTMLImageElement && target.closest('[data-slide]')) {
      target.closest('[data-slide]').dataset.broken = 'true';
    }
  },

  /**
   * Gère les images déjà en échec avant l'initialisation du script.
   */
  markBrokenSlides() {
    document.querySelectorAll('[data-slide] img').forEach((img) => {
      if (img.complete && img.naturalWidth === 0) {
        V.markBrokenSlide(img);
      }
    });
  },

  /**
   * Halo lumineux qui suit le curseur (throttling avec requestAnimationFrame).
   */
  initCursorGlow() {
    const glow = document.querySelector('.cursor-glow');

    if (!glow || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    let isMoving = false;

    window.addEventListener('mousemove', (ev) => {
      // On évite de surcharger le navigateur si l'écran n'a pas encore été rafraîchi
      if (isMoving) return;

      isMoving = true;
      window.requestAnimationFrame(() => {
        // translate3d profite de l'accélération matérielle
        glow.style.transform = `translate3d(${ev.clientX}px, ${ev.clientY}px, 0)`;
        isMoving = false;
      });
    });
  },
};
