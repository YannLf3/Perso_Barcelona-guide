/**
 * M (Modèle) : données de l'espace simplifié (compte « editor ») et appels à l'API d'administration.
 * Aucune référence au DOM ici, les méthodes retournent uniquement des données.
 */
const M = {
  /** @type {string} Jeton CSRF de la session admin (transmis dans l'en-tête X-CSRF-Token) */
  csrfToken: '',

  /** @type {Object} Textes de l'interface fournis par le serveur (groupe « simple ») */
  strings: {},

  /** @type {Array<string>} Langues des contenus */
  langs: ['en', 'es', 'fr', 'it'],

  /** @type {Array<{name: string, url: string}>} */
  images: [],

  /** @type {Array<Object>} Visites avec toutes leurs traductions */
  tours: [],

  /** @type {Array<Object>} */
  messages: [],

  /** @type {{groups: Array<Object>, max: number}} Textes du site, regroupés par zone */
  texts: { groups: [], max: 5000 },

  /** @type {Object<string, string>} Textes modifiés et pas encore enregistrés, clé "cle|langue" */
  textDrafts: {},

  /** @type {string} Langue affichée dans le bloc des textes */
  textLang: 'fr',

  /**
   * Remplace les marqueurs {nom} d'un texte.
   * @param {string} text
   * @param {Object<string, string|number>} params
   * @returns {string}
   */
  format(text, params = {}) {
    return Object.entries(params).reduce((carry, [key, value]) => carry.split(`{${key}}`).join(String(value)), String(text || ''));
  },

  /**
   * Texte de l'interface à partir de sa clé ("tour_number").
   * @param {string} key
   * @param {Object<string, string|number>} params
   * @returns {string}
   */
  t(key, params = {}) {
    const value = M.strings[key];

    return M.format(typeof value === 'string' ? value : key, params);
  },

  /**
   * Appel générique à l'API d'administration.
   * @param {string} method GET, POST, PUT ou DELETE
   * @param {string} url
   * @param {Object|FormData|null} body Objet (envoyé en JSON) ou FormData (envoi de fichier)
   * @returns {Promise<{status: number, data: any}>}
   */
  async request(method, url, body = null) {
    const headers = { Accept: 'application/json', 'X-CSRF-Token': M.csrfToken };
    const options = { method, headers, credentials: 'same-origin' };

    if (body instanceof FormData) {
      options.body = body;
    } else if (body !== null) {
      headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(body);
    }

    try {
      const response = await fetch(url, options);
      const data = await response.json().catch(() => ({ success: false, message: M.t('log_loading_error') }));

      return { status: response.status, data };
    } catch (error) {
      return { status: 0, data: { success: false, message: M.t('network') } };
    }
  },

  /**
   * Charge images, visites, messages et textes.
   * @returns {Promise<{unauthorized: boolean, failed: boolean}>}
   */
  async loadAll() {
    const results = await Promise.all([
      M.request('GET', '/api/admin/images'),
      M.request('GET', '/api/admin/tours'),
      M.request('GET', '/api/admin/messages'),
      M.request('GET', '/api/admin/texts'),
    ]);
    const [images, tours, messages, texts] = results;
    const list = (result) => (Array.isArray(result.data) ? result.data : []);

    M.images = list(images);
    M.tours = list(tours);
    M.messages = list(messages);

    if (texts.data && Array.isArray(texts.data.groups)) M.texts = texts.data;

    return {
      unauthorized: results.some((result) => result.status === 401),
      failed: results.some((result) => result.status !== 200),
    };
  },

  /* ---------- Visites ---------- */

  /**
   * Traduction d'une visite dans une langue, avec repli sur l'anglais puis sur des champs vides.
   * @param {Object} tour
   * @param {string} locale
   * @returns {{title: string, tagline: string, summary: string}}
   */
  tourTranslation(tour, locale) {
    const translations = tour.translations || {};

    return translations[locale] || translations.en || { title: '', tagline: '', summary: '' };
  },

  /**
   * Corps de la requête de création ou de modification d'une visite.
   * @param {{duration: string, capacity: string, group_type: string, image_url: string, is_active?: string}} common Champs communs
   * @param {Object<string, {title: string, tagline: string, summary: string}>} translations Traductions par langue
   * @returns {Object}
   */
  tourBody(common, translations) {
    return { ...common, translations };
  },

  /**
   * Enregistre une nouvelle visite.
   * @param {Object} body
   * @returns {Promise<{status: number, data: any}>}
   */
  createTour(body) {
    return M.request('POST', '/api/admin/tours', body);
  },

  /**
   * Met à jour une visite.
   * @param {number|string} id
   * @param {Object} body
   * @returns {Promise<{status: number, data: any}>}
   */
  updateTour(id, body) {
    return M.request('PUT', `/api/admin/tours/${encodeURIComponent(id)}`, body);
  },

  /* ---------- Images ---------- */

  /**
   * Envoie une photo.
   * @param {FormData} formData Contient le fichier (champ "image")
   * @returns {Promise<{status: number, data: any}>}
   */
  uploadImage(formData) {
    return M.request('POST', '/api/admin/images', formData);
  },

  /* ---------- Textes du site ---------- */

  /**
   * Clé du brouillon d'un texte.
   * @param {string} key
   * @param {string} locale
   * @returns {string}
   */
  draftKey(key, locale) {
    return `${key}|${locale}`;
  },

  /**
   * Texte affiché dans un champ : le brouillon, sinon le texte remplacé, sinon le texte d'origine.
   * @param {Object} field Champ renvoyé par /api/admin/texts
   * @param {string} locale
   * @returns {string}
   */
  textValue(field, locale) {
    const draft = M.textDrafts[M.draftKey(field.key, locale)];

    if (draft !== undefined) return draft;

    return field.values[locale] || field.defaults[locale] || '';
  },

  /**
   * Mémorise la saisie d'un champ.
   * @param {string} key
   * @param {string} locale
   * @param {string} value
   */
  setDraft(key, locale, value) {
    M.textDrafts[M.draftKey(key, locale)] = value;
  },

  /**
   * Corps de la requête d'enregistrement : seuls les champs modifiés sont envoyés.
   * Un texte identique à l'original est envoyé vide, ce qui rétablit l'origine côté serveur.
   * @returns {{texts: Object<string, Object<string, string>>}}
   */
  textsBody() {
    const fields = M.texts.groups.flatMap((group) => group.fields);
    const texts = Object.entries(M.textDrafts).reduce((carry, [draftKey, value]) => {
      const [key, locale] = draftKey.split('|');
      const field = fields.find((item) => item.key === key);

      if (!field) return carry;

      // Les retours à la ligne sont conservés tels que saisis : le serveur les normalise
      const sent = value === (field.defaults[locale] || '') ? '' : value;

      return { ...carry, [key]: { ...(carry[key] || {}), [locale]: sent } };
    }, {});

    return { texts };
  },

  /**
   * Intègre les brouillons enregistrés aux textes chargés puis les vide.
   */
  commitDrafts() {
    const body = M.textsBody().texts;

    M.texts.groups.forEach((group) => {
      group.fields.forEach((field) => {
        Object.entries(body[field.key] || {}).forEach(([locale, value]) => {
          field.values = { ...field.values, [locale]: value };
        });
      });
    });
    M.textDrafts = {};
  },

  /**
   * Enregistre les textes modifiés.
   * @returns {Promise<{status: number, data: any}>}
   */
  saveTexts() {
    return M.request('PUT', '/api/admin/texts', M.textsBody());
  },
};
