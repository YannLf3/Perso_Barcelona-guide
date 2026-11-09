/**
 * C (Contrôleur) : fait le lien entre M (données) et V (DOM) pour l'espace simplifié.
 * Les méthodes handler_<action>(ev, element) sont appelées par les écouteurs déclarés dans V.init().
 */
const C = {
  /** @type {string} Clé du thème dans le localStorage (la même que celle lue par le script de l'en-tête) */
  themeKey: 'admin-theme',

  /**
   * Point d'entrée : lit la page, applique le thème, charge les données.
   */
  async init() {
    const bootstrap = V.readBootstrap();

    M.csrfToken = bootstrap.csrf;
    M.strings = bootstrap.strings;

    V.applyTheme(V.currentTheme(), C.themeLabels());
    V.init();
    V.renderLog(M.t('log_loaded'));
    await C.loadAdminData();
  },

  /**
   * Libellés du bouton de thème.
   * @returns {{toDark: string, toLight: string}}
   */
  themeLabels() {
    return { toDark: M.t('theme_to_dark'), toLight: M.t('theme_to_light') };
  },

  /**
   * Charge les données puis redessine tous les blocs.
   */
  async loadAdminData() {
    const result = await M.loadAll();

    // Session expirée : retour à la page de connexion
    if (result.unauthorized) {
      window.location.assign('/admin/login');

      return;
    }

    if (result.failed) V.renderLog(M.t('log_loading_error'));

    C.renderAll();
  },

  /**
   * Redessine les visites, la galerie, les messages et les textes.
   */
  renderAll() {
    const noImage = M.t('no_image');

    V.renderAddFormImages(M.images, noImage);
    V.renderTours(M.tours, M.images, C.tourLabels(), M.tourTranslation);
    V.renderGallery(M.images);
    V.renderMessages(M.messages, M.t('messages_none'));
    C.renderTexts();
  },

  /**
   * Libellés utilisés dans les cartes de visite.
   * @returns {Object}
   */
  tourLabels() {
    return {
      none: M.t('tours_none'),
      noImage: M.t('no_image'),
      tourNumber: (id) => M.t('tour_number', { id }),
      updateLabel: (locale) => M.t('update_tour', { lang: locale.toUpperCase() }),
    };
  },

  /**
   * Redessine le bloc des textes dans la langue choisie.
   */
  renderTexts() {
    V.renderTexts(M.texts.groups, M.textLang, M.textValue, M.t('texts_origin'));
  },

  /* ---------- Thème et session ---------- */

  handler_toggleTheme() {
    const next = V.currentTheme() === 'dark' ? 'light' : 'dark';

    try {
      window.localStorage.setItem(C.themeKey, next);
    } catch (error) {
      // Navigateur sans stockage : le thème vaut pour cette page seulement
    }

    V.applyTheme(next, C.themeLabels());
  },

  /* ---------- Visites ---------- */

  /**
   * Ajoute une visite.
   * @param {Event} ev
   * @param {HTMLFormElement} form
   */
  async handler_addTour(ev, form) {
    const { common, translations } = V.readAddForm(form, M.langs);
    const result = await M.createTour(M.tourBody(common, translations));

    V.renderLog(result.data.message);

    if (result.data.success) {
      V.resetForm(form);
      await C.loadAdminData();
    }
  },

  /**
   * Met à jour une visite : champs communs et traduction de la langue affichée.
   * @param {Event} ev
   * @param {HTMLFormElement} form
   */
  async handler_updateTour(ev, form) {
    const card = V.readTourCard(V.tourCard(form));
    const body = M.tourBody(card.common, { [card.locale]: card.translation });
    const result = await M.updateTour(card.id, body);

    V.renderLog(result.data.message);
    await C.loadAdminData();
  },

  /**
   * Change la langue éditée d'une visite.
   * @param {Event} ev
   * @param {HTMLSelectElement} select
   */
  handler_tourLang(ev, select) {
    const root = V.tourCard(select);
    const tour = M.tours.find((item) => String(item.id) === root.dataset.tourId);

    V.fillTourTranslation(root, M.tourTranslation(tour, select.value), select.value, C.tourLabels().updateLabel);
  },

  /* ---------- Images ---------- */

  /**
   * Envoie une photo puis recharge la galerie.
   * @param {Event} ev
   * @param {HTMLFormElement} form
   */
  async handler_uploadImage(ev, form) {
    const result = await M.uploadImage(new FormData(form));

    V.renderLog(result.data.message);

    if (result.data.success) {
      V.resetForm(form);
      await C.loadAdminData();
    }
  },

  /* ---------- Textes du site ---------- */

  /**
   * Change la langue des textes affichés (les saisies déjà faites sont gardées).
   * @param {Event} ev
   * @param {HTMLSelectElement} select
   */
  handler_textLang(ev, select) {
    M.textLang = select.value;
    C.renderTexts();
  },

  /**
   * Mémorise la saisie d'un champ de texte.
   * @param {Event} ev
   * @param {HTMLTextAreaElement} input
   */
  handler_textInput(ev, input) {
    M.setDraft(input.dataset.key, input.dataset.locale, input.value);
    V.resizeText(input);
  },

  /**
   * Enregistre les textes modifiés.
   */
  async handler_saveTexts() {
    if (Object.keys(M.textsBody().texts).length === 0) {
      V.renderLog(M.t('texts_none_changed'));

      return;
    }

    const result = await M.saveTexts();

    V.renderLog(result.data.message);

    if (result.data.success) {
      M.commitDrafts();
      C.renderTexts();
    }
  },
};

C.init();
