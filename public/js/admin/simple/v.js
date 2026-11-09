/**
 * V (Vue) : seul endroit qui touche au DOM de l'espace simplifié.
 * Les écouteurs sont déclarés dans V.init() (délégation d'événements), aucune logique métier ici.
 * Tout texte venant de la base ou d'un visiteur est inséré avec textContent ou value, jamais avec innerHTML.
 */
const V = {
  /** @type {Array<string>} Derniers messages affichés dans la bande de retour (dix au maximum) */
  logHistory: [],

  /**
   * Raccourci de sélection.
   * @param {string} selector
   * @param {ParentNode} root
   * @returns {Element|null}
   */
  $(selector, root = document) {
    return root.querySelector(selector);
  },

  /**
   * Contenu d'un <template>, cloné.
   * @param {string} id
   * @returns {DocumentFragment}
   */
  clone(id) {
    return document.getElementById(id).content.cloneNode(true);
  },

  /**
   * Lit le jeton CSRF et les textes fournis par le serveur dans la page.
   * @returns {{csrf: string, strings: Object}}
   */
  readBootstrap() {
    const csrf = V.$('meta[name="csrf-token"]');
    const strings = V.$('#admin-strings');

    return {
      csrf: csrf ? csrf.content : '',
      strings: strings ? (JSON.parse(strings.textContent).simple || {}) : {},
    };
  },

  /**
   * Déclare tous les écouteurs. Chaque action correspond à une méthode handler_<action> de C.
   */
  init() {
    document.addEventListener('click', (ev) => {
      const target = ev.target.closest('[data-action]');

      if (!target || target.tagName === 'SELECT') return;

      const handler = C[`handler_${target.dataset.action.replace(/-([a-z])/g, (all, letter) => letter.toUpperCase())}`];

      if (handler) handler(ev, target);
    });

    document.addEventListener('change', (ev) => {
      const target = ev.target.closest('select[data-action]');

      if (!target) return;

      const handler = C[`handler_${target.dataset.action.replace(/-([a-z])/g, (all, letter) => letter.toUpperCase())}`];

      if (handler) handler(ev, target);
    });

    document.addEventListener('input', (ev) => {
      if (ev.target.matches('[data-field-input]')) C.handler_textInput(ev, ev.target);
    });

    document.addEventListener('submit', (ev) => {
      const form = ev.target.closest('form[data-form]');

      if (!form) return;

      ev.preventDefault();
      C[`handler_${form.dataset.form.replace(/-([a-z])/g, (all, letter) => letter.toUpperCase())}`](ev, form);
    });
  },

  /* ---------- Thème ---------- */

  /**
   * Applique le thème clair ou sombre et met à jour le bouton.
   * @param {string} theme "light" ou "dark"
   * @param {Object} labels Libellés du bouton ({toDark, toLight})
   */
  applyTheme(theme, labels) {
    const isDark = theme === 'dark';
    const button = V.$('[data-action="toggle-theme"]');

    document.documentElement.dataset.theme = theme;
    button.setAttribute('aria-pressed', String(isDark));
    V.$('.theme-toggle__icon', button).textContent = isDark ? '☀️' : '🌙';
    V.$('.theme-toggle__label', button).textContent = isDark ? labels.toLight : labels.toDark;
  },

  /**
   * Thème actuellement affiché.
   * @returns {string}
   */
  currentTheme() {
    return document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light';
  },

  /* ---------- Bande de retour ---------- */

  /**
   * Ajoute un message à la bande de retour (les dix derniers sont gardés).
   * @param {string} text
   */
  renderLog(text) {
    const zone = V.$('[data-log]');

    V.logHistory = [...V.logHistory, String(text)].slice(-10);

    const panel = document.createElement('section');

    panel.className = 'log-panel';
    panel.append(...V.logHistory.map((line) => {
      const paragraph = document.createElement('p');

      paragraph.textContent = line;

      return paragraph;
    }));
    zone.replaceChildren(panel);
  },

  /* ---------- Images ---------- */

  /**
   * Remplit une liste déroulante d'images ("Pas d'image" puis chaque fichier).
   * @param {HTMLSelectElement} select
   * @param {Array<{name: string}>} images
   * @param {string} noImageLabel
   * @param {string|null} selected Fichier à présélectionner
   */
  fillImageSelect(select, images, noImageLabel, selected = null) {
    const options = [{ name: '', label: noImageLabel }, ...images.map((image) => ({ name: image.name, label: image.name }))];

    select.replaceChildren(...options.map((item) => {
      const option = document.createElement('option');

      option.value = item.name;
      option.textContent = item.label;
      option.selected = item.name === (selected || '');

      return option;
    }));
  },

  /**
   * Met à jour les listes d'images du formulaire d'ajout.
   * @param {Array<{name: string}>} images
   * @param {string} noImageLabel
   */
  renderAddFormImages(images, noImageLabel) {
    V.fillImageSelect(V.$('.tour-add-form [data-image-select]'), images, noImageLabel);
  },

  /**
   * Affiche la galerie.
   * @param {Array<{name: string, url: string}>} images
   */
  renderGallery(images) {
    V.$('[data-image-gallery]').replaceChildren(...images.map((image) => {
      const item = V.clone('gallery-item-template');
      const picture = V.$('img', item);

      picture.src = image.url;
      picture.alt = image.name;
      V.$('figcaption', item).textContent = image.name;

      return item;
    }));
  },

  /* ---------- Visites ---------- */

  /**
   * Affiche les visites existantes, une carte d'édition par visite.
   * @param {Array<Object>} tours
   * @param {Array<{name: string}>} images
   * @param {Object} labels Libellés ({none, tourNumber, noImage, updateLabel})
   * @param {function(Object, string): {title: string, tagline: string, summary: string}} translationOf
   */
  renderTours(tours, images, labels, translationOf) {
    const zone = V.$('[data-tour-list]');

    if (tours.length === 0) {
      const empty = document.createElement('p');

      empty.textContent = labels.none;
      zone.replaceChildren(empty);

      return;
    }

    zone.replaceChildren(...tours.map((tour) => {
      const card = V.clone('tour-row-template');
      const root = V.$('[data-tour]', card);
      const locale = tour.translations && tour.translations.en ? 'en' : Object.keys(tour.translations || {})[0] || 'en';

      root.dataset.tourId = tour.id;
      V.$('[data-tour-title]', root).textContent = labels.tourNumber(tour.id);
      V.$('[name="duration"]', root).value = tour.duration;
      V.$('[name="capacity"]', root).value = tour.capacity;
      V.$('[name="is_active"]', root).value = Number(tour.is_active) === 1 ? '1' : '0';
      V.$('[name="group_type"]', root).value = tour.group_type || 'small';
      V.fillImageSelect(V.$('[data-image-select]', root), images, labels.noImage, tour.image_url);
      V.$('.edit-lang-select', root).value = locale;
      V.fillTourTranslation(root, translationOf(tour, locale), locale, labels.updateLabel);

      return card;
    }));
  },

  /**
   * Remplit le formulaire de traduction d'une carte de visite.
   * @param {Element} root Carte de la visite
   * @param {{title: string, tagline: string, summary: string}} translation
   * @param {string} locale
   * @param {function(string): string} updateLabel Libellé du bouton pour une langue
   */
  fillTourTranslation(root, translation, locale, updateLabel) {
    V.$('[name="title"]', root).value = translation.title;
    V.$('[name="tagline"]', root).value = translation.tagline;
    V.$('[name="summary"]', root).value = translation.summary;
    V.$('[data-update-label]', root).textContent = updateLabel(locale);
  },

  /**
   * Carte de visite qui contient un élément.
   * @param {Element} element
   * @returns {Element|null}
   */
  tourCard(element) {
    return element.closest('[data-tour]');
  },

  /**
   * Champs communs et traduction affichée d'une carte de visite.
   * @param {Element} root Carte de la visite
   * @returns {{id: string, locale: string, common: Object, translation: Object}}
   */
  readTourCard(root) {
    const value = (name) => V.$(`[name="${name}"]`, root).value;

    return {
      id: root.dataset.tourId,
      locale: V.$('.edit-lang-select', root).value,
      common: {
        duration: value('duration'),
        capacity: value('capacity'),
        is_active: value('is_active'),
        group_type: value('group_type'),
        image_url: value('image_url'),
      },
      translation: { title: value('title'), tagline: value('tagline'), summary: value('summary') },
    };
  },

  /**
   * Champs du formulaire d'ajout.
   * @param {HTMLFormElement} form
   * @param {Array<string>} langs
   * @returns {{common: Object, translations: Object}}
   */
  readAddForm(form, langs) {
    const value = (name) => form.elements[name].value;

    return {
      common: {
        duration: value('duration'),
        capacity: value('capacity'),
        group_type: value('group_type'),
        image_url: value('image_url'),
      },
      translations: langs.reduce((carry, lang) => ({
        ...carry,
        [lang]: { title: value(`title_${lang}`), tagline: value(`tagline_${lang}`), summary: value(`summary_${lang}`) },
      }), {}),
    };
  },

  /**
   * Vide un formulaire après un envoi réussi.
   * @param {HTMLFormElement} form
   */
  resetForm(form) {
    form.reset();
  },

  /* ---------- Messages ---------- */

  /**
   * Affiche les messages de contact.
   * @param {Array<Object>} messages
   * @param {string} noneLabel
   */
  renderMessages(messages, noneLabel) {
    const zone = V.$('[data-message-list]');

    if (messages.length === 0) {
      const empty = document.createElement('p');

      empty.textContent = noneLabel;
      zone.replaceChildren(empty);

      return;
    }

    zone.replaceChildren(...messages.map((message) => {
      const card = V.clone('message-row-template');

      V.$('[data-message-name]', card).textContent = message.fullname;
      V.$('[data-message-email]', card).textContent = message.email;
      V.$('[data-message-date]', card).textContent = message.created_at;
      V.$('[data-message-body]', card).replaceChildren(...V.paragraphs(message.message));

      return card;
    }));
  },

  /**
   * Un <p> par ligne non vide d'un texte, pour conserver les paragraphes.
   * @param {string} text
   * @returns {Array<HTMLParagraphElement>}
   */
  paragraphs(text) {
    return String(text || '').split(/\n+/).filter((line) => line.trim() !== '').map((line) => {
      const paragraph = document.createElement('p');

      paragraph.textContent = line;

      return paragraph;
    });
  },

  /* ---------- Textes du site ---------- */

  /**
   * Affiche les groupes de textes dans la langue choisie.
   * @param {Array<Object>} groups Groupes renvoyés par /api/admin/texts
   * @param {string} locale
   * @param {function(Object, string): string} valueOf Texte à afficher dans un champ
   * @param {string} originLabel Libellé « Texte d'origine : »
   */
  renderTexts(groups, locale, valueOf, originLabel) {
    V.$('[data-text-groups]').replaceChildren(...groups.map((group) => {
      const card = V.clone('text-group-template');

      V.$('[data-group-label]', card).textContent = group.label;
      V.$('[data-group-help]', card).textContent = group.help;
      V.$('[data-group-fields]', card).replaceChildren(...group.fields.map((field) => {
        const item = V.clone('text-field-template');
        const input = V.$('[data-field-input]', item);
        const value = valueOf(field, locale);
        const original = field.defaults[locale] || '';

        V.$('[data-field-label]', item).textContent = field.label;
        input.value = value;
        input.dataset.key = field.key;
        input.dataset.locale = locale;
        input.rows = V.rowsFor(value);
        V.$('[data-field-origin]', item).textContent = value === original ? '' : `${originLabel} ${original}`;

        return item;
      }));

      return card;
    }));
  },

  /**
   * Nombre de lignes d'un champ selon son contenu, pour voir tout le texte sans défiler.
   * @param {string} value
   * @returns {number}
   */
  rowsFor(value) {
    const lines = String(value).split('\n').reduce((total, line) => total + Math.max(1, Math.ceil(line.length / 60)), 0);

    return Math.min(14, Math.max(2, lines));
  },

  /**
   * Ajuste la hauteur d'un champ à ce qui est saisi.
   * @param {HTMLTextAreaElement} input
   */
  resizeText(input) {
    input.rows = V.rowsFor(input.value);
  },
};
