/**
 * V (Vue) : seul objet autorisé à manipuler le DOM du back-office.
 * Les écrans sont construits en clonant les balises <template> rendues par les vues PHP.
 * Tout texte venant de la base est inséré avec textContent ou .value, jamais en HTML brut.
 */
const V = {
  themeStorageKey: 'admin-theme',
  logLineClass: 'my-1 first:mt-0 last:mb-0',

  /**
   * Déclare les écouteurs d'événements (délégation sur le document).
   */
  init() {
    document.addEventListener('click', (ev) => {
      if (ev.target.closest('[data-action="toggle-theme"]')) return C.handler_toggleTheme(ev);
    });

    document.addEventListener('change', (ev) => {
      if (ev.target.matches('[data-action="edit-lang"]')) return C.handler_changeEditLang(ev);
    });

    document.addEventListener('submit', (ev) => {
      const form = ev.target;

      if (form.matches('[data-form="tour-add"]')) return C.handler_addTour(ev);
      if (form.matches('[data-form="tour-edit"]')) return C.handler_updateTour(ev);
      if (form.matches('[data-form="image-upload"]')) return C.handler_uploadImage(ev);
    });

    V.syncThemeToggle();
  },

  /**
   * @returns {boolean} true sur le tableau de bord (pas sur la page de connexion)
   */
  isDashboard() {
    return document.querySelector('[data-view="dashboard"]') !== null;
  },

  /**
   * Jeton CSRF placé dans le <head> par le serveur.
   * @returns {string}
   */
  getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');

    return meta ? meta.content : '';
  },

  /**
   * Renvoie vers la page de connexion (session expirée).
   */
  redirectToLogin() {
    window.location.assign('/admin/login');
  },

  /**
   * Met à jour l'icône, le libellé et aria-pressed du bouton de thème d'après l'attribut data-theme du <html>.
   */
  syncThemeToggle() {
    const toggle = document.querySelector('#themeToggle');
    const isDark = document.documentElement.dataset.theme === 'dark';

    toggle.setAttribute('aria-pressed', String(isDark));
    toggle.querySelector('[data-theme-icon]').textContent = isDark ? toggle.dataset.iconLight : toggle.dataset.iconDark;
    toggle.querySelector('[data-theme-label]').textContent = isDark ? toggle.dataset.labelLight : toggle.dataset.labelDark;
  },

  /**
   * Bascule entre thème clair et sombre et mémorise le choix dans le navigateur.
   */
  toggleTheme() {
    const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';

    try {
      window.localStorage.setItem(V.themeStorageKey, next);
    } catch (error) {
      // Stockage indisponible : le thème reste valable pour la page courante
    }

    document.documentElement.dataset.theme = next;
    V.syncThemeToggle();
  },

  /**
   * Lit les messages déjà affichés dans le journal (rendus par le serveur).
   * @returns {Array<string>}
   */
  readLog() {
    return Array.from(document.querySelectorAll('[data-log] p')).map((p) => p.textContent);
  },

  /**
   * Affiche l'historique du journal (les retours à la ligne deviennent des <br>).
   * @param {Array<string>} history
   */
  renderLog(history) {
    const panel = document.querySelector('[data-log]');

    const lines = history.map((text) => {
      const p = document.createElement('p');
      p.className = V.logLineClass;

      text.split('\n').forEach((line, index) => {
        if (index > 0) p.append(document.createElement('br'));
        p.append(line);
      });

      return p;
    });

    panel.replaceChildren(...lines);
  },

  /**
   * Clone le contenu d'un <template>.
   * @param {string} id
   * @returns {DocumentFragment}
   */
  cloneTemplate(id) {
    return document.importNode(document.querySelector(`#${id}`).content, true);
  },

  /**
   * Remplit un <select> avec la liste des images (option "aucune image" en tête).
   * @param {HTMLSelectElement} select
   * @param {Array<{name: string}>} images
   * @param {string} selected Nom de fichier à présélectionner
   */
  fillImageOptions(select, images, selected = '') {
    const noImage = document.querySelector('#images-template').dataset.noImage;
    const options = [{ name: '', label: noImage }, ...images.map((image) => ({ name: image.name, label: image.name }))];

    select.replaceChildren(
      ...options.map((item) => {
        const option = document.createElement('option');
        option.value = item.name;
        option.textContent = item.label;
        option.selected = item.name === selected;

        return option;
      }),
    );
  },

  /**
   * Affiche le formulaire d'ajout et la liste des visites existantes.
   * @param {Array<Object>} tours
   * @param {Array<{name: string}>} images
   */
  renderTours(tours, images) {
    const fragment = V.cloneTemplate('tours-template');

    fragment.querySelectorAll('[data-images]').forEach((select) => V.fillImageOptions(select, images));

    const list = fragment.querySelector('[data-tour-list]');

    if (tours.length === 0) {
      const empty = document.createElement('p');
      empty.textContent = document.querySelector('#tours-template').dataset.empty || '';
      list.replaceWith(empty);
    } else {
      list.append(...tours.map((tour) => V.buildTourRow(tour, images)));
    }

    document.querySelector('#tourForm').replaceChildren(fragment);
  },

  /**
   * Construit le bloc d'édition d'une visite.
   * @param {Object} tour
   * @param {Array<{name: string}>} images
   * @returns {DocumentFragment}
   */
  buildTourRow(tour, images) {
    const fragment = V.cloneTemplate('tour-row-template');
    const row = fragment.querySelector('[data-tour-row]');
    const set = (name, value) => {
      row.querySelector(`[name="${name}"]`).value = value;
    };

    row.dataset.tourId = tour.id;
    row.querySelector('[data-field="id"]').textContent = tour.id;
    set('duration', tour.duration);
    set('capacity', tour.capacity);
    set('is_active', String(tour.is_active));
    set('group_type', tour.group_type);
    set('id', tour.id);
    V.fillImageOptions(row.querySelector('select[name="image_url"]'), images, tour.image_url || '');

    V.fillTranslation(row.querySelector('[data-form="tour-edit"]'), M.getTranslation(tour.id, 'en'), 'en');

    return fragment;
  },

  /**
   * Remplit le formulaire d'édition avec la traduction d'une langue.
   * @param {HTMLFormElement} form
   * @param {{title: string, tagline: string, summary: string}} translation
   * @param {string} locale
   */
  fillTranslation(form, translation, locale) {
    form.querySelector('[name="title"]').value = translation.title;
    form.querySelector('[name="tagline"]').value = translation.tagline || '';
    form.querySelector('[name="summary"]').value = translation.summary;
    form.querySelector('[name="locale"]').value = locale;

    const button = form.querySelector('button[type="submit"]');
    button.textContent = `${button.dataset.label} (${locale.toUpperCase()})`;
  },

  /**
   * Affiche le formulaire d'envoi et la galerie d'images.
   * @param {Array<{name: string, url: string}>} images
   */
  renderImages(images) {
    const fragment = V.cloneTemplate('images-template');

    fragment.querySelector('[data-gallery]').append(
      ...images.map((image) => {
        const item = V.cloneTemplate('image-item-template');
        const img = item.querySelector('img');

        img.src = image.url;
        img.alt = image.name;
        item.querySelector('figcaption').textContent = image.name;

        return item;
      }),
    );

    document.querySelector('#imageGallery').replaceChildren(fragment);
  },

  /**
   * Affiche la liste des messages de contact (texte brut échappé par textContent).
   * @param {Array<Object>} messages
   */
  renderMessages(messages) {
    const fragment = V.cloneTemplate('messages-template');
    const list = fragment.querySelector('[data-message-list]');

    if (messages.length === 0) {
      const empty = document.createElement('p');
      empty.textContent = document.querySelector('#messages-template').dataset.empty;
      list.append(empty);
    } else {
      list.append(
        ...messages.map((message) => {
          const card = V.cloneTemplate('message-template');

          ['fullname', 'email', 'created_at', 'message'].forEach((field) => {
            card.querySelector(`[data-field="${field}"]`).textContent = message[field];
          });

          return card;
        }),
      );
    }

    document.querySelector('#messageList').replaceChildren(fragment);
  },

  /**
   * Lit le formulaire d'ajout de visite.
   * @param {HTMLFormElement} form
   * @returns {Object}
   */
  readTourAddForm(form) {
    const value = (name) => form.querySelector(`[name="${name}"]`).value;
    const translations = Object.fromEntries(
      ['en', 'es', 'fr', 'it'].map((code) => [
        code,
        { title: value(`title_${code}`), tagline: value(`tagline_${code}`), summary: value(`summary_${code}`) },
      ]),
    );

    return {
      duration: value('duration'),
      capacity: value('capacity'),
      group_type: value('group_type'),
      image_url: value('image_url'),
      translations,
    };
  },

  /**
   * Lit le bloc d'édition d'une visite (champs communs + traduction de la langue choisie).
   * @param {HTMLFormElement} form Formulaire de traduction
   * @returns {{id: string, payload: Object}}
   */
  readTourEditForm(form) {
    const row = form.closest('[data-tour-row]');
    const value = (scope, name) => scope.querySelector(`[name="${name}"]`).value;
    const locale = value(form, 'locale');

    return {
      id: value(form, 'id'),
      payload: {
        duration: value(row, 'duration'),
        capacity: value(row, 'capacity'),
        is_active: Number(value(row, 'is_active')),
        group_type: value(row, 'group_type'),
        image_url: value(row, 'image_url'),
        translations: {
          [locale]: { title: value(form, 'title'), tagline: value(form, 'tagline'), summary: value(form, 'summary') },
        },
      },
    };
  },

  /**
   * Lit le formulaire d'envoi d'image.
   * @param {HTMLFormElement} form
   * @returns {FormData}
   */
  readUploadForm(form) {
    return new FormData(form);
  },

  /**
   * Vide un formulaire.
   * @param {HTMLFormElement} form
   */
  resetForm(form) {
    form.reset();
  },
};
