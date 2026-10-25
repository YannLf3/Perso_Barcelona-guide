/**
 * M (Modèle) : données du site public et appels à l'API.
 * Aucune référence au DOM ici, les méthodes retournent uniquement des données.
 */
const M = {
  supportedLangs: ['en', 'fr', 'es', 'it'],

  /** @type {Object<string, Array<Object>>} Visites déjà chargées, par langue */
  toursByLang: {},

  /**
   * Vérifie qu'une langue fait partie de la liste blanche.
   * @param {string} lang
   * @returns {boolean}
   */
  isSupportedLang(lang) {
    return M.supportedLangs.includes(lang);
  },

  /**
   * Charge les visites actives dans une langue (mise en cache pour la durée de la page).
   * @param {string} lang
   * @returns {Promise<Array<Object>>}
   */
  async loadTours(lang) {
    if (M.toursByLang[lang]) {
      return M.toursByLang[lang];
    }

    const response = await fetch(`/api/tours?lang=${encodeURIComponent(lang)}`, {
      headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
      throw new Error(`Tours request failed (${response.status})`);
    }

    const tours = await response.json();
    M.toursByLang[lang] = Array.isArray(tours) ? tours : [];

    return M.toursByLang[lang];
  },

  /**
   * Filtre les visites par type de groupe (private, small, school ou all).
   * @param {Array<Object>} tours
   * @param {string} groupType
   * @returns {Array<Object>}
   */
  filterTours(tours, groupType) {
    if (groupType === 'all') {
      return tours;
    }

    return tours.filter((tour) => tour.group_type === groupType);
  },

  /**
   * Retrouve un élément par son identifiant (comparaison en texte, l'id du DOM est une chaîne).
   * @param {Array<Object>} list
   * @param {string} id
   * @returns {Object|undefined}
   */
  findById(list, id) {
    return list.find((item) => String(item.id) === String(id));
  },

  /**
   * Envoie le formulaire de contact.
   * @param {FormData} formData Champs du formulaire
   * @param {string} csrfToken Jeton CSRF signé fourni par la page
   * @param {string} fallbackMessage Message affiché si le serveur est injoignable
   * @returns {Promise<{success: boolean, message: string}>}
   */
  async sendMessage(formData, csrfToken, fallbackMessage) {
    try {
      const response = await fetch('/api/messages', {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-CSRF-Token': csrfToken },
        body: formData,
      });
      const answer = await response.json();

      return { success: Boolean(answer.success), message: String(answer.message || fallbackMessage) };
    } catch (error) {
      return { success: false, message: fallbackMessage };
    }
  },
};
