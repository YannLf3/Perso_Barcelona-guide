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

  /** @type {Array<string>} Filtres valides (liste blanche pour la valeur lue dans le cookie) */
  filterTypes: ['all', 'private', 'small', 'school'],

  /**
   * Mémorise le filtre de visites dans un cookie (un an, SameSite=Lax).
   * @param {string} groupType
   */
  saveFilter(groupType) {
    const secure = window.location.protocol === 'https:' ? '; Secure' : '';

    document.cookie = `tour_filter=${groupType}; Max-Age=31536000; Path=/; SameSite=Lax${secure}`;
  },

  /**
   * Relit le filtre mémorisé dans le cookie ('all' si absent ou invalide).
   * @returns {string}
   */
  loadFilter() {
    const match = document.cookie.match(/(?:^|;\s*)tour_filter=([a-z]+)/);

    return match && M.filterTypes.includes(match[1]) ? match[1] : 'all';
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

  /**
   * Valide l'apparence envoyée par l'administration à l'aperçu (page en mode ?preview=1).
   * Seuls les messages de la même origine et les valeurs en liste blanche sont acceptés.
   * @param {any} data Contenu du message
   * @param {string} origin Origine de l'expéditeur
   * @param {string} expectedOrigin Origine de la page
   * @returns {Object|null} Apparence nettoyée, null si le message est refusé
   */
  readPreviewMessage(data, origin, expectedOrigin) {
    if (origin !== expectedOrigin || !data || data.type !== 'bt-design-preview') return null;

    // Couleur hexadécimale, transparent, ou renvoi vers une autre variable du site
    const colorPattern = /^(#[0-9a-f]{6}|transparent|var\(--ui-[a-z-]+\))$/i;
    const colors = Object.fromEntries(
      Object.entries(data.colors || {}).filter(([key, value]) => /^[a-z-]+$/.test(key) && typeof value === 'string' && colorPattern.test(value)),
    );
    const readFont = (font) => {
      const valid = font
        && /^[A-Za-z0-9 ]+$/.test(font.family)
        && ['serif', 'sans-serif'].includes(font.fallback)
        && /^\d{3,4} \d{3,4}$/.test(font.weight)
        && /^\/assets\/fonts\/[A-Za-z0-9-]+\.woff2$/.test(font.url);

      return valid ? { family: font.family, fallback: font.fallback, weight: font.weight, url: font.url } : null;
    };
    const fonts = { heading: readFont(data.fonts && data.fonts.heading), body: readFont(data.fonts && data.fonts.body) };
    const radius = data.radius || {};
    const photos = Array.isArray(data.photos) ? data.photos.filter((url) => typeof url === 'string' && /^\/assets\/(images|uploads)\/[A-Za-z0-9._%-]+$/.test(url)) : [];

    return {
      colors,
      fonts,
      textSize: ['90', '100', '110', '120'].includes(data.textSize) ? data.textSize : '100',
      radius: {
        button: ['initial', '0', '0.5rem', '9999px'].includes(radius.button) ? radius.button : 'initial',
        card: ['16px', '0', '8px', '24px'].includes(radius.card) ? radius.card : '16px',
      },
      photos,
    };
  },
};
