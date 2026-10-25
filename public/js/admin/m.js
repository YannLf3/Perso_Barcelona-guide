/**
 * M (Modèle) : données du back-office, règles métier et appels à l'API.
 * Aucune référence au DOM ici, les méthodes retournent uniquement des données.
 */
const M = {
  /** @type {string} Jeton CSRF de la session admin (transmis dans l'en-tête X-CSRF-Token) */
  csrfToken: '',

  /** @type {Object} Textes du back-office fournis par le serveur (feedback, savebar, design...) */
  strings: {},

  /** @type {Array<{name: string, url: string}>} */
  images: [],

  /** @type {Array<Object>} Visites avec toutes leurs traductions */
  tours: [],

  /** @type {Array<Object>} */
  messages: [],

  /** @type {{groups: Array<Object>, max: number}} Textes du site, regroupés par zone */
  texts: { groups: [], max: 5000 },

  /** @type {Object<string, Object<string, string>>} Textes modifiés et pas encore enregistrés, par clé puis par langue */
  textDrafts: {},

  /** @type {string} Langue affichée dans l'écran des textes */
  textLang: 'fr',

  /** @type {{catalogue: Object|null, published: Object, draft: Object, currentId: number, history: Array<Object>}} */
  design: { catalogue: null, published: {}, draft: {}, currentId: 0, history: [] },

  /** @type {Array<string>} Langues des contenus, l'anglais (langue de repli) en premier */
  langs: ['en', 'fr', 'es', 'it'],

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
   * Texte du back-office à partir d'un chemin à points ("feedback.network").
   * @param {string} path
   * @param {Object<string, string|number>} params
   * @returns {string}
   */
  t(path, params = {}) {
    const value = path.split('.').reduce((carry, part) => (carry && typeof carry === 'object' ? carry[part] : undefined), M.strings);

    return M.format(typeof value === 'string' ? value : path, params);
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
    const options = { method, headers, credentials: 'same-origin' };

    if (body instanceof FormData) {
      options.body = body;
    } else if (body !== null) {
      headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(body);
    }

    try {
      const response = await fetch(url, options);
      const data = await response.json().catch(() => ({ success: false, message: M.t('feedback.loading_error') }));

      return { status: response.status, data };
    } catch (error) {
      return { status: 0, data: { success: false, message: M.t('feedback.network') } };
    }
  },

  /**
   * Charge toutes les données du tableau de bord.
   * @returns {Promise<{unauthorized: boolean, failed: boolean}>}
   */
  async loadAll() {
    const results = await Promise.all([
      M.request('GET', '/api/admin/images'),
      M.request('GET', '/api/admin/tours'),
      M.request('GET', '/api/admin/messages'),
      M.request('GET', '/api/admin/texts'),
      M.request('GET', '/api/admin/design'),
    ]);
    const [images, tours, messages, texts, design] = results;
    const list = (result) => (Array.isArray(result.data) ? result.data : []);

    M.images = list(images);
    M.tours = list(tours);
    M.messages = list(messages);

    if (texts.data && Array.isArray(texts.data.groups)) M.texts = texts.data;

    if (design.data && design.data.catalogue) M.setDesignData(design.data);

    return {
      unauthorized: results.some((result) => result.status === 401),
      failed: results.some((result) => result.status !== 200),
    };
  },

  /* ---------- Accueil ---------- */

  /**
   * Nombre de visites visibles et masquées.
   * @returns {{visible: number, hidden: number}}
   */
  tourCounts() {
    const visible = M.tours.filter((tour) => Number(tour.is_active) === 1).length;

    return { visible, hidden: M.tours.length - visible };
  },

  /**
   * Date lisible en français ("25 octobre 2026 à 14:32").
   * @param {string} value Date SQL "AAAA-MM-JJ HH:MM:SS"
   * @returns {string}
   */
  formatDate(value) {
    const date = new Date(String(value).replace(' ', 'T'));

    if (Number.isNaN(date.getTime())) return String(value || '');

    const day = new Intl.DateTimeFormat('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }).format(date);
    const time = new Intl.DateTimeFormat('fr-FR', { hour: '2-digit', minute: '2-digit' }).format(date);

    return `${day} à ${time}`;
  },

  /**
   * Lien "mailto:" de réponse, vide si l'adresse n'a pas une forme valide.
   * @param {string} email
   * @returns {string}
   */
  replyHref(email) {
    const value = String(email || '').trim();

    // Forme simple d'adresse, sans espace ni caractère qui casserait le lien
    if (!/^[^\s@<>"?&]+@[^\s@<>"?&]+\.[^\s@<>"?&]+$/.test(value)) return '';

    return `mailto:${value}?subject=${encodeURIComponent(M.t('messages.reply_subject'))}`;
  },

  /* ---------- Textes du site ---------- */

  /**
   * Tous les champs de texte, à plat.
   * @returns {Array<Object>}
   */
  textFields() {
    return M.texts.groups.flatMap((group) => group.fields);
  },

  /**
   * @param {string} key
   * @returns {Object|undefined}
   */
  findTextField(key) {
    return M.textFields().find((field) => field.key === key);
  },

  /**
   * Texte affiché dans le champ : brouillon, sinon texte enregistré, sinon texte d'origine.
   * @param {Object} field
   * @param {string} lang
   * @returns {string}
   */
  textValue(field, lang) {
    const draft = M.textDrafts[field.key] && M.textDrafts[field.key][lang];

    if (draft !== undefined) return draft === '' ? field.defaults[lang] : draft;

    return field.values[lang] !== '' ? field.values[lang] : field.defaults[lang];
  },

  /**
   * Indique si le texte diffère de l'origine (en tenant compte du brouillon).
   * @param {Object} field
   * @param {string} lang
   * @returns {boolean}
   */
  isTextModified(field, lang) {
    return M.textValue(field, lang) !== field.defaults[lang];
  },

  /**
   * Enregistre une saisie dans le brouillon. Un texte identique à l'origine est stocké vide (retour à l'origine).
   * @param {string} key
   * @param {string} lang
   * @param {string} value
   */
  setTextDraft(key, lang, value) {
    const field = M.findTextField(key);

    if (!field) return;

    const normalized = value.trim() === field.defaults[lang].trim() ? '' : value;
    const saved = field.values[lang];

    M.textDrafts = { ...M.textDrafts, [key]: { ...(M.textDrafts[key] || {}), [lang]: normalized } };

    // Si la saisie revient à la valeur enregistrée, ce n'est plus un changement en attente
    if (normalized === saved) {
      const { [lang]: removed, ...rest } = M.textDrafts[key];
      M.textDrafts = Object.keys(rest).length === 0
        ? Object.fromEntries(Object.entries(M.textDrafts).filter(([draftKey]) => draftKey !== key))
        : { ...M.textDrafts, [key]: rest };
    }
  },

  /**
   * Nombre de textes modifiés en attente d'enregistrement.
   * @returns {number}
   */
  pendingTextCount() {
    return Object.values(M.textDrafts).reduce((sum, byLang) => sum + Object.keys(byLang).length, 0);
  },

  /**
   * Abandonne les changements en attente.
   */
  discardTextDrafts() {
    M.textDrafts = {};
  },

  /**
   * Après enregistrement, les brouillons deviennent les valeurs enregistrées.
   */
  commitTextDrafts() {
    M.texts.groups.forEach((group) => {
      group.fields.forEach((field) => {
        const draft = M.textDrafts[field.key];

        if (draft) field.values = { ...field.values, ...draft };
      });
    });
    M.textDrafts = {};
  },

  /**
   * Corps de la requête d'enregistrement des textes.
   * @returns {{texts: Object<string, Object<string, string>>}}
   */
  textPayload() {
    return { texts: M.textDrafts };
  },

  /**
   * Indique si un champ correspond à la recherche (libellé, texte d'origine ou texte actuel).
   * @param {Object} field
   * @param {string} query
   * @param {string} lang
   * @returns {boolean}
   */
  matchesTextSearch(field, query, lang) {
    const needle = M.normalize(query);

    if (needle === '') return true;

    return [field.label, field.defaults[lang], M.textValue(field, lang)].some((text) => M.normalize(text).includes(needle));
  },

  /**
   * Texte en minuscules sans accents, pour une recherche tolérante.
   * @param {string} text
   * @returns {string}
   */
  normalize(text) {
    return String(text || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
  },

  /* ---------- Apparence ---------- */

  /**
   * Mémorise les données d'apparence reçues du serveur.
   * @param {{catalogue: Object, current: Object, currentId: number, history: Array<Object>}} data
   */
  setDesignData(data) {
    // Le catalogue doit être en place avant de compléter les valeurs par défaut
    M.design = { ...M.design, catalogue: data.catalogue };
    M.design = {
      catalogue: data.catalogue,
      published: M.normalizeDesign(data.current),
      draft: M.normalizeDesign(data.current),
      currentId: data.currentId,
      history: Array.isArray(data.history) ? data.history : [],
    };
  },

  /**
   * Apparence complète avec ses valeurs par défaut, pour comparer et manipuler sans cas particulier.
   * @param {Object} data Données enregistrées (seulement ce qui diffère de l'origine)
   * @returns {{colors: Object<string, string>, fonts: {heading: string, body: string}, textSize: string, radius: {button: string, card: string}, photos: Array<string>}}
   */
  normalizeDesign(data) {
    const source = data && typeof data === 'object' ? data : {};
    const catalogue = M.design.catalogue;

    return {
      colors: { ...(source.colors || {}) },
      fonts: { ...catalogue.defaultFonts, ...(source.fonts || {}) },
      textSize: source.textSize || '100',
      radius: { button: 'default', card: 'default', ...(source.radius || {}) },
      photos: Array.isArray(source.photos) ? [...source.photos] : [...catalogue.defaultPhotos],
    };
  },

  /**
   * Tous les jetons de couleur à plat, avec leur groupe.
   * @returns {Array<{key: string, label: string, default: string, group: string}>}
   */
  designTokens() {
    return M.design.catalogue.groups.flatMap((group) => group.tokens.map((token) => ({ ...token, group: group.id })));
  },

  /**
   * @param {string} key
   * @returns {Object|undefined}
   */
  findToken(key) {
    return M.designTokens().find((token) => token.key === key);
  },

  /**
   * Valeur de référence d'un jeton : couleur choisie, sinon valeur d'origine ("#hex", "@autre" ou "transparent").
   * @param {string} key
   * @param {Object} design
   * @returns {string}
   */
  rawColor(key, design = M.design.draft) {
    const token = M.findToken(key);

    return design.colors[key] !== undefined ? design.colors[key] : (token ? token.default : '#000000');
  },

  /**
   * Couleur réellement affichée (hexadécimale), en suivant les renvois "@autre".
   * Un fond transparent laisse voir le fond de la page.
   * @param {string} key
   * @param {Object} design
   * @param {number} depth Garde-fou contre une boucle de renvois
   * @returns {string}
   */
  resolveColor(key, design = M.design.draft, depth = 0) {
    const raw = M.rawColor(key, design);

    if (depth > 10) return '#000000';
    if (raw === 'transparent') return M.resolveColor('page-bg', design, depth + 1);
    if (raw.startsWith('@')) return M.resolveColor(raw.slice(1), design, depth + 1);

    return raw;
  },

  /**
   * Description de l'état d'un jeton pour l'affichage : suit une autre couleur, transparent, ou personnalisé.
   * @param {string} key
   * @returns {{followsLabel: string, isTransparent: boolean, isCustom: boolean, canBeTransparent: boolean}}
   */
  colorState(key) {
    const token = M.findToken(key);
    const raw = M.rawColor(key);
    const isCustom = M.design.draft.colors[key] !== undefined;
    const target = raw.startsWith('@') ? M.findToken(raw.slice(1)) : null;

    return {
      followsLabel: target ? target.label : '',
      isTransparent: raw === 'transparent',
      isCustom,
      canBeTransparent: token.default === 'transparent',
    };
  },

  /**
   * Change une couleur du brouillon.
   * @param {string} key
   * @param {string} value "#rrggbb" ou "transparent"
   */
  setColor(key, value) {
    const token = M.findToken(key);

    if (!token) return;

    const valid = /^#[0-9a-f]{6}$/i.test(value) || (value === 'transparent' && token.default === 'transparent');

    if (!valid) return;

    const colors = { ...M.design.draft.colors, [key]: value.toLowerCase() };

    // Revenir à la valeur d'origine supprime la personnalisation
    if (value.toLowerCase() === token.default) delete colors[key];

    M.design.draft = { ...M.design.draft, colors };
  },

  /**
   * Rend sa valeur d'origine à une couleur.
   * @param {string} key
   */
  resetColor(key) {
    const colors = { ...M.design.draft.colors };
    delete colors[key];
    M.design.draft = { ...M.design.draft, colors };
  },

  /**
   * Applique une palette : ses couleurs remplacent toutes les couleurs du brouillon.
   * @param {string} presetKey
   */
  applyPreset(presetKey) {
    const preset = M.design.catalogue.presets.find((item) => item.key === presetKey);

    if (preset) M.design.draft = { ...M.design.draft, colors: { ...preset.colors } };
  },

  /**
   * Couleurs représentatives d'une palette (pour ses pastilles).
   * @param {string} presetKey
   * @returns {Array<string>}
   */
  presetSwatches(presetKey) {
    const preset = M.design.catalogue.presets.find((item) => item.key === presetKey);
    const design = { ...M.design.draft, colors: { ...(preset ? preset.colors : {}) } };

    return ['band', 'page-bg', 'accent', 'text', 'card-btn-bg'].map((key) => M.resolveColor(key, design));
  },

  /**
   * Pastilles d'un groupe de couleurs (4 premières couleurs résolues).
   * @param {string} groupId
   * @returns {Array<string>}
   */
  groupSwatches(groupId) {
    const group = M.design.catalogue.groups.find((item) => item.id === groupId);

    return group ? group.tokens.slice(0, 4).map((token) => M.resolveColor(token.key)) : [];
  },

  /**
   * @param {'heading'|'body'} role
   * @param {string} fontKey
   */
  setFont(role, fontKey) {
    if (!M.design.catalogue.fonts.some((font) => font.key === fontKey)) return;

    M.design.draft = { ...M.design.draft, fonts: { ...M.design.draft.fonts, [role]: fontKey } };
  },

  /**
   * Taille du texte ou forme (bouton, carte).
   * @param {'textSize'|'button'|'card'} name
   * @param {string} value
   */
  setOption(name, value) {
    const catalogue = M.design.catalogue;

    if (name === 'textSize' && catalogue.textSizes.some((size) => size.value === value)) {
      M.design.draft = { ...M.design.draft, textSize: value };
    }

    if ((name === 'button' || name === 'card') && catalogue.radius[name].some((shape) => shape.value === value)) {
      M.design.draft = { ...M.design.draft, radius: { ...M.design.draft.radius, [name]: value } };
    }
  },

  /**
   * Valeur courante d'une option du brouillon.
   * @param {'textSize'|'button'|'card'} name
   * @returns {string}
   */
  optionValue(name) {
    return name === 'textSize' ? M.design.draft.textSize : M.design.draft.radius[name];
  },

  /**
   * Options proposées pour un réglage.
   * @param {'textSize'|'button'|'card'} name
   * @returns {Array<{value: string, label: string}>}
   */
  optionChoices(name) {
    return name === 'textSize' ? M.design.catalogue.textSizes : M.design.catalogue.radius[name];
  },

  /**
   * Remplace une photo du carrousel.
   * @param {number} index
   * @param {string} name
   */
  setPhoto(index, name) {
    if (!M.images.some((image) => image.name === name)) return;

    const photos = [...M.design.draft.photos];
    photos[index] = name;
    M.design.draft = { ...M.design.draft, photos };
  },

  /**
   * @param {string} name
   * @returns {string} URL publique de l'image, vide si inconnue
   */
  imageUrl(name) {
    const image = M.images.find((item) => item.name === name);

    return image ? image.url : '';
  },

  /**
   * Indique si le brouillon diffère de l'apparence en ligne.
   * @returns {boolean}
   */
  isDesignDirty() {
    return JSON.stringify(M.compactDesign(M.design.draft)) !== JSON.stringify(M.compactDesign(M.design.published));
  },

  /**
   * Abandonne les changements de l'aperçu.
   */
  discardDesignDraft() {
    M.design.draft = M.normalizeDesign(M.compactDesign(M.design.published));
  },

  /**
   * Apparence réduite à ce qui diffère de l'origine (format attendu par le serveur).
   * @param {Object} design
   * @returns {Object}
   */
  compactDesign(design) {
    const catalogue = M.design.catalogue;
    const sortedColors = Object.fromEntries(Object.entries(design.colors).sort(([a], [b]) => a.localeCompare(b)));
    const fonts = Object.fromEntries(Object.entries(design.fonts).filter(([role, key]) => key !== catalogue.defaultFonts[role]));
    const radius = Object.fromEntries(Object.entries(design.radius).filter(([, shape]) => shape !== 'default'));
    const photosChanged = design.photos.join('|') !== catalogue.defaultPhotos.join('|');

    return {
      ...(Object.keys(sortedColors).length ? { colors: sortedColors } : {}),
      ...(Object.keys(fonts).length ? { fonts } : {}),
      ...(design.textSize !== '100' ? { textSize: design.textSize } : {}),
      ...(Object.keys(radius).length ? { radius } : {}),
      ...(photosChanged ? { photos: design.photos } : {}),
    };
  },

  /**
   * Corps de la requête de publication.
   * @returns {{design: Object}}
   */
  designPayload() {
    return { design: M.compactDesign(M.design.draft) };
  },

  /**
   * Après publication, le brouillon devient l'apparence en ligne.
   */
  commitDesign() {
    M.design.published = M.normalizeDesign(M.compactDesign(M.design.draft));
  },

  /**
   * Luminance relative d'une couleur (formule WCAG 2).
   * @param {Array<number>} rgb Composantes 0 à 255
   * @returns {number}
   */
  luminance(rgb) {
    const [r, g, b] = rgb.map((channel) => {
      const value = channel / 255;

      return value <= 0.03928 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4;
    });

    return 0.2126 * r + 0.7152 * g + 0.0722 * b;
  },

  /**
   * @param {string} hex "#rrggbb"
   * @returns {Array<number>}
   */
  hexToRgb(hex) {
    const value = parseInt(hex.slice(1), 16);

    return [(value >> 16) & 255, (value >> 8) & 255, value & 255];
  },

  /**
   * Rapport de contraste entre un texte (éventuellement semi-transparent) et son fond.
   * @param {string} fg Couleur du texte
   * @param {string} bg Couleur du fond
   * @param {number} alpha Opacité du texte telle qu'appliquée sur le site
   * @returns {number}
   */
  contrastRatio(fg, bg, alpha = 1) {
    const back = M.hexToRgb(bg);

    // Le texte semi-transparent est mélangé au fond avant le calcul
    const front = M.hexToRgb(fg).map((channel, index) => channel * alpha + back[index] * (1 - alpha));
    const [light, dark] = [M.luminance(front), M.luminance(back)].sort((a, b) => b - a);

    return (light + 0.05) / (dark + 0.05);
  },

  /**
   * Paires texte / fond sous le seuil WCAG AA, pour le brouillon.
   * @returns {Array<{fgLabel: string, bgLabel: string, ratio: number, min: number, groups: Array<string>}>}
   */
  contrastIssues() {
    return M.design.catalogue.contrast
      .map((pair) => ({
        ...pair,
        ratio: M.contrastRatio(M.resolveColor(pair.fg), M.resolveColor(pair.bg), pair.alpha),
        groups: [M.findToken(pair.fg).group, M.findToken(pair.bg).group],
      }))
      .filter((pair) => pair.ratio < pair.min);
  },

  /**
   * Message envoyé à l'aperçu : valeur CSS de chaque jeton, polices, taille, formes et photos.
   * Toutes les valeurs sont envoyées (même celles d'origine) pour neutraliser l'apparence publiée.
   * @returns {Object}
   */
  previewMessage() {
    const catalogue = M.design.catalogue;
    const draft = M.design.draft;
    const fontInfo = (key) => {
      const font = catalogue.fonts.find((item) => item.key === key);

      return { family: font.family, fallback: font.fallback, weight: font.weight, url: font.url };
    };
    const radiusCss = (kind, fallback) => {
      const shape = catalogue.radius[kind].find((item) => item.value === draft.radius[kind]);

      return shape && shape.css !== null ? shape.css : fallback;
    };

    return {
      type: 'bt-design-preview',
      colors: Object.fromEntries(
        M.designTokens().map((token) => {
          const raw = M.rawColor(token.key, draft);

          return [token.key, raw.startsWith('@') ? `var(--ui-${raw.slice(1)})` : raw];
        }),
      ),
      fonts: { heading: fontInfo(draft.fonts.heading), body: fontInfo(draft.fonts.body) },
      textSize: draft.textSize,
      radius: { button: radiusCss('button', 'initial'), card: radiusCss('card', '16px') },
      photos: draft.photos.map((name) => M.imageUrl(name)).filter((url) => url !== ''),
    };
  },

  /**
   * Publie le brouillon.
   */
  publishDesign() {
    return M.request('PUT', '/api/admin/design', M.designPayload());
  },

  /**
   * Remet en ligne une version de l'historique.
   * @param {number} id
   */
  restoreDesign(id) {
    return M.request('POST', `/api/admin/design/restore/${encodeURIComponent(id)}`, {});
  },

  /**
   * Recharge l'apparence (après publication ou restauration).
   */
  async reloadDesign() {
    const answer = await M.request('GET', '/api/admin/design');

    if (answer.data && answer.data.catalogue) M.setDesignData(answer.data);

    return answer;
  },

  /* ---------- Visites ---------- */

  /**
   * @param {string|number} id
   * @returns {Object|undefined}
   */
  findTour(id) {
    return M.tours.find((tour) => String(tour.id) === String(id));
  },

  /**
   * Titre d'une visite à afficher dans l'administration (français, sinon anglais).
   * @param {Object} tour
   * @returns {string}
   */
  tourTitle(tour) {
    const translations = tour.translations || {};
    const entry = translations.fr || translations.en || Object.values(translations)[0];

    return entry && entry.title ? entry.title : M.t('tours.no_title');
  },

  /**
   * Résumé des informations d'une visite ("Petit groupe · 2 h · 12 pers.").
   * @param {Object} tour
   * @returns {string}
   */
  tourMeta(tour) {
    return [M.t(`tours.group_${tour.group_type}`), tour.duration, `${parseInt(tour.capacity, 10)} ${M.t('tours.persons')}`].filter(Boolean).join(' · ');
  },

  /**
   * Vérifie le formulaire d'une visite avant l'envoi.
   * @param {Object} payload
   * @returns {string} Message d'erreur, vide si tout est correct
   */
  validateTour(payload) {
    if (payload.duration.trim() === '' || !(Number(payload.capacity) >= 1)) return M.t('tours.error_common');

    const filled = (entry) => [entry.title, entry.tagline, entry.summary].filter((text) => text.trim() !== '').length;
    const en = payload.translations.en;

    if (filled(en) < 3) return M.t('tours.error_english');

    // Une langue partiellement remplie serait ignorée par le serveur : on prévient la cliente
    const partial = M.langs.find((lang) => {
      const count = filled(payload.translations[lang]);

      return count > 0 && count < 3;
    });

    return partial ? M.t('tours.error_partial', { lang: M.t(`lang.${partial}`) }) : '';
  },

  /**
   * Crée ou met à jour une visite.
   * @param {string} id Identifiant, vide pour une création
   * @param {Object} payload
   */
  saveTour(id, payload) {
    return id === ''
      ? M.request('POST', '/api/admin/tours', payload)
      : M.request('PUT', `/api/admin/tours/${encodeURIComponent(id)}`, payload);
  },

  /**
   * Recharge la liste des visites.
   */
  async reloadTours() {
    const answer = await M.request('GET', '/api/admin/tours');

    if (Array.isArray(answer.data)) M.tours = answer.data;

    return answer;
  },

  /* ---------- Photos ---------- */

  /**
   * Envoie une photo puis recharge la liste.
   * @param {File} file
   */
  async uploadImage(file) {
    const formData = new FormData();
    formData.append('image', file);

    const answer = await M.request('POST', '/api/admin/images', formData);

    if (answer.data && answer.data.success) {
      const images = await M.request('GET', '/api/admin/images');

      if (Array.isArray(images.data)) M.images = images.data;
    }

    return answer;
  },
};
