/**
 * M (Modèle) : données du back-office et appels à l'API.
 * Aucune référence au DOM ici, les méthodes retournent uniquement des données.
 */
const M = {
  /** @type {string} Jeton CSRF de la session admin (transmis dans l'en-tête X-CSRF-Token) */
  csrfToken: '',

  /** @type {Array<string>} Derniers messages du journal (10 maximum) */
  logHistory: [],

  /** @type {Array<Object>} Visites chargées, avec toutes leurs traductions */
  tours: [],

  /**
   * Ajoute un message au journal et garde les 10 derniers.
   * @param {string} message
   * @returns {Array<string>}
   */
  addLog(message) {
    M.logHistory = [...M.logHistory, message].slice(-10);

    return M.logHistory;
  },

  /**
   * Transforme une réponse (texte ou objet JSON) en texte lisible pour le journal.
   * @param {string|Object} answer
   * @returns {string}
   */
  toLogText(answer) {
    if (answer && typeof answer === 'object') {
      return typeof answer.message === 'string' && answer.message !== '' ? answer.message : JSON.stringify(answer, null, 2);
    }

    return String(answer);
  },

  /**
   * Appel générique à l'API d'administration.
   * @param {string} method GET, POST ou PUT
   * @param {string} url
   * @param {Object|FormData|null} body Objet (envoyé en JSON) ou FormData (envoi de fichier)
   * @returns {Promise<{status: number, data: any}>}
   */
  async request(method, url, body = null) {
    const headers = { Accept: 'application/json', 'X-CSRF-Token': M.csrfToken };
    const options = { method, headers };

    if (body instanceof FormData) {
      options.body = body;
    } else if (body !== null) {
      headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(body);
    }

    try {
      const response = await fetch(url, options);
      const data = await response.json().catch(() => ({ success: false, message: `HTTP ${response.status}` }));

      return { status: response.status, data };
    } catch (error) {
      return { status: 0, data: { success: false, message: 'Network error. Please try again.' } };
    }
  },

  /**
   * Charge toutes les données du tableau de bord.
   * @returns {Promise<{images: Array, tours: Array, messages: Array, unauthorized: boolean}>}
   */
  async loadAll() {
    const [images, tours, messages] = await Promise.all([
      M.request('GET', '/api/admin/images'),
      M.request('GET', '/api/admin/tours'),
      M.request('GET', '/api/admin/messages'),
    ]);

    const unauthorized = [images, tours, messages].some((result) => result.status === 401);
    const list = (result) => (Array.isArray(result.data) ? result.data : []);

    M.tours = list(tours);

    return { images: list(images), tours: M.tours, messages: list(messages), unauthorized };
  },

  /**
   * Retrouve la traduction d'une visite dans une langue (repli sur l'anglais).
   * @param {string|number} tourId
   * @param {string} locale
   * @returns {{title: string, tagline: string, summary: string}}
   */
  getTranslation(tourId, locale) {
    const tour = M.tours.find((item) => String(item.id) === String(tourId));
    const translations = tour ? tour.translations : {};

    return translations[locale] || translations.en || { title: '', tagline: '', summary: '' };
  },

  /**
   * Crée une visite.
   * @param {Object} payload
   */
  createTour(payload) {
    return M.request('POST', '/api/admin/tours', payload);
  },

  /**
   * Met à jour une visite.
   * @param {string|number} id
   * @param {Object} payload
   */
  updateTour(id, payload) {
    return M.request('PUT', `/api/admin/tours/${encodeURIComponent(id)}`, payload);
  },

  /**
   * Envoie une image.
   * @param {FormData} formData
   */
  uploadImage(formData) {
    return M.request('POST', '/api/admin/images', formData);
  },
};
