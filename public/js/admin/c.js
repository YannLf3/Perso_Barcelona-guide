/**
 * C (Contrôleur) : fait le lien entre M (données) et V (DOM) pour le back-office.
 * Les méthodes handler_<action>(ev) sont appelées par les écouteurs déclarés dans V.init().
 * Les méthodes build*VM préparent les données affichées par V (textes, états, libellés).
 */
const C = {
  /** @type {string} Écran affiché */
  screen: 'home',

  /** @type {{page: string, device: string, loaded: boolean}} État de l'aperçu de l'apparence */
  preview: { page: 'home', device: 'desktop', loaded: false },

  /** @type {{target: string, index: number}} Destination de la photo choisie dans la fenêtre de sélection */
  picker: { target: 'tour', index: 0 },

  /** @type {boolean} L'écran d'apparence a été construit */
  designBuilt: false,

  /* ---------- Coquille ---------- */

  handler_toggleTheme() {
    V.toggleTheme();
  },

  handler_toggleMenu() {
    V.toggleMenu();
  },

  handler_togglePassword(ev) {
    V.togglePassword(ev.target.checked);
  },

  /**
   * Clic dans le menu : on referme le menu sur téléphone, le changement d'ancre fait le reste.
   */
  handler_menuLink() {
    V.toggleMenu(false);
  },

  /**
   * Changement d'ancre (#texts, #design...) : affiche l'écran demandé, après confirmation si des changements seraient perdus.
   */
  handler_route() {
    const requested = C.validScreen(V.readScreenFromHash());

    if (requested === C.screen) return;

    if (!C.confirmLeave(C.screen)) {
      V.setHash(C.screen);
      return;
    }

    C.showScreen(requested, true);
  },

  /**
   * Fermeture ou rechargement de l'onglet avec des changements non enregistrés.
   */
  handler_beforeUnload(ev) {
    if (M.pendingTextCount() > 0 || (M.design.catalogue && M.isDesignDirty())) {
      ev.preventDefault();
      ev.returnValue = '';
    }
  },

  handler_resize() {
    if (C.screen === 'design') V.scalePreview(C.preview.device);
  },

  /**
   * @param {string} screen
   * @returns {string} Écran existant, l'accueil par défaut
   */
  validScreen(screen) {
    return V.screenNames().includes(screen) ? screen : 'home';
  },

  /**
   * Demande confirmation avant de quitter un écran qui a des changements non enregistrés.
   * Si la cliente accepte, les changements sont abandonnés.
   * @param {string} screen Écran quitté
   * @returns {boolean}
   */
  confirmLeave(screen) {
    const dirty = (screen === 'texts' && M.pendingTextCount() > 0) || (screen === 'design' && M.design.catalogue && M.isDesignDirty());

    if (!dirty) return true;
    if (!V.confirm(M.t('feedback.unsaved'))) return false;

    if (screen === 'texts') C.handler_cancelTexts();
    if (screen === 'design') C.handler_cancelDesign();

    return true;
  },

  /**
   * Affiche un écran (et charge l'aperçu à la première ouverture de l'apparence).
   * @param {string} screen
   * @param {boolean} moveFocus
   */
  showScreen(screen, moveFocus) {
    C.screen = screen;
    V.showScreen(screen, moveFocus);

    if (screen === 'design') {
      C.loadPreview();
      window.requestAnimationFrame(() => V.scalePreview(C.preview.device));
    }
  },

  /**
   * Affiche le résultat d'un appel à l'API ; renvoie vers la connexion si la session a expiré.
   * @param {{status: number, data: Object}} answer
   * @param {string} successText Message affiché en cas de succès (sinon celui du serveur)
   * @returns {boolean} true si l'appel a réussi
   */
  report(answer, successText) {
    if (answer.status === 401) {
      V.toast(M.t('feedback.expired'), 'error');
      window.setTimeout(() => V.redirectToLogin(), 2500);
      return false;
    }

    const ok = Boolean(answer.data && answer.data.success);

    V.toast(ok ? successText : (answer.data && answer.data.message) || M.t('feedback.network'), ok ? 'success' : 'error');

    return ok;
  },

  /* ---------- Accueil ---------- */

  renderHome() {
    const counts = M.tourCounts();

    V.renderHome({
      messages: M.messages.slice(0, 3).map((message) => ({
        fullname: message.fullname,
        date: M.formatDate(message.created_at),
        message: message.message,
      })),
      toursText: `${M.t('home.tours_visible', { count: counts.visible })} · ${M.t('home.tours_hidden', { count: counts.hidden })}`,
      messageCount: M.messages.length,
      emptyText: M.t('messages.empty'),
    });
  },

  /* ---------- Textes du site ---------- */

  /**
   * État d'affichage d'un champ (valeur, origine, avertissement) dans la langue courante.
   * @param {Object} field
   * @returns {Object}
   */
  buildTextFieldVM(field) {
    const lang = M.textLang;
    const value = M.textValue(field, lang);
    const tooLong = field.seo && value.length > 155;

    return {
      key: field.key,
      label: field.label,
      long: field.long,
      max: M.texts.max,
      value,
      origin: field.defaults[lang],
      modified: M.isTextModified(field, lang),
      warning: tooLong ? M.t('texts.too_long', { count: value.length }) : '',
    };
  },

  /**
   * Nombre de textes modifiés dans un groupe, pour la langue courante.
   * @param {Object} group
   * @returns {number}
   */
  modifiedInGroup(group) {
    return group.fields.filter((field) => M.isTextModified(field, M.textLang)).length;
  },

  renderTexts() {
    V.renderTexts(M.texts.groups.map((group) => {
      const count = C.modifiedInGroup(group);

      return {
        id: group.id,
        label: group.label,
        help: group.help,
        advanced: group.advanced,
        modifiedCount: count,
        modifiedLabel: `${count} ${M.t('texts.modified').toLowerCase()}`,
        fields: group.fields.map((field) => C.buildTextFieldVM(field)),
      };
    }), M.textLang);

    C.applyTextSearch();
    C.syncTextsSavebar();
  },

  syncTextsSavebar() {
    const count = M.pendingTextCount();

    V.setSavebar('texts', count > 0 ? M.t('savebar.texts', { count }) : '');
  },

  /**
   * Met à jour un champ et la pastille de son groupe après une saisie.
   * @param {string} key
   */
  refreshTextField(key) {
    const field = M.findTextField(key);
    const group = M.texts.groups.find((item) => item.fields.includes(field));
    const count = C.modifiedInGroup(group);

    V.syncTextField(key, C.buildTextFieldVM(field));
    V.syncTextGroupCount(group.id, count, `${count} ${M.t('texts.modified').toLowerCase()}`);
    C.syncTextsSavebar();
  },

  applyTextSearch() {
    const query = V.readTextSearch();
    const keys = M.textFields().filter((field) => M.matchesTextSearch(field, query, M.textLang)).map((field) => field.key);

    V.filterTextFields(keys, M.normalize(query) !== '');
  },

  handler_textInput(ev) {
    const { key, value } = V.readTextInput(ev.target);

    M.setTextDraft(key, M.textLang, value);
    C.refreshTextField(key);
  },

  handler_textReset(ev) {
    const key = V.readTextKey(ev.target);
    const field = M.findTextField(key);

    M.setTextDraft(key, M.textLang, field.defaults[M.textLang]);
    V.setTextFieldValue(key, field.defaults[M.textLang]);
    C.refreshTextField(key);
  },

  handler_textLang(ev) {
    M.textLang = V.readLang(ev.target);
    C.renderTexts();
  },

  handler_textSearch() {
    C.applyTextSearch();
  },

  async handler_saveTexts(ev) {
    const button = ev ? ev.target.closest('button') : null;

    V.setBusy(button, true, M.t('savebar.saving'));
    const answer = await M.request('PUT', '/api/admin/texts', M.textPayload());
    V.setBusy(button, false, '');

    if (C.report(answer, M.t('feedback.texts_saved'))) {
      M.commitTextDrafts();
      C.renderTexts();
    }
  },

  handler_cancelTexts() {
    M.discardTextDrafts();
    C.renderTexts();
  },

  /* ---------- Apparence ---------- */

  /**
   * Prépare tout l'écran d'apparence à partir du brouillon.
   * @returns {Object}
   */
  buildDesignVM() {
    const catalogue = M.design.catalogue;
    const issues = M.contrastIssues();
    const issueGroups = issues.flatMap((issue) => issue.groups);

    return {
      presets: catalogue.presets.map((preset) => ({ ...preset, swatches: M.presetSwatches(preset.key) })),
      fontSample: M.t('design.font_sample'),
      fonts: catalogue.fonts.map((font) => {
        const isOrigin = Object.values(catalogue.defaultFonts).includes(font.key);

        return {
          key: font.key,
          name: isOrigin ? `${font.family} (${M.t('design.font_current')})` : font.family,
          description: font.description,
          stack: `"${font.family}", ${font.fallback}`,
        };
      }),
      selectedFonts: M.design.draft.fonts,
      choices: Object.fromEntries(['textSize', 'button', 'card'].map((name) => [name, M.optionChoices(name)])),
      selectedOptions: Object.fromEntries(['textSize', 'button', 'card'].map((name) => [name, M.optionValue(name)])),
      groups: catalogue.groups.map((group) => {
        const warnings = issueGroups.filter((id) => id === group.id).length;

        return {
          id: group.id,
          label: group.label,
          help: group.help,
          swatches: M.groupSwatches(group.id),
          warningLabel: warnings > 0 ? M.t('design.contrast_title') : '',
          rows: group.tokens.map((token) => {
            const state = M.colorState(token.key);
            const followText = state.followsLabel !== '' ? M.t('design.follows', { name: state.followsLabel }) : '';
            const originText = state.isTransparent ? M.t('design.same_as_page') : followText;
            const stateText = state.isCustom ? M.t('design.custom') : originText;

            return {
              key: token.key,
              label: token.label,
              color: M.resolveColor(token.key),
              inputLabel: M.t('design.choose_color', { name: token.label }),
              stateText,
              isCustom: state.isCustom,
              canBeTransparent: state.canBeTransparent,
              isTransparent: state.isTransparent,
            };
          }),
        };
      }),
      photos: M.design.draft.photos.map((name, index) => ({
        index,
        url: M.imageUrl(name),
        label: M.t('design.photo_slot', { number: index + 1 }),
        changeLabel: M.t('design.change_photo'),
      })),
      history: M.design.history.map((item) => ({
        id: item.id,
        date: M.formatDate(item.created_at),
        label: item.label,
        current: item.id === M.design.currentId,
      })),
      historyEmpty: M.t('design.history_empty'),
      contrast: {
        summary: issues.length === 0 ? M.t('design.contrast_ok') : M.t('design.contrast_intro'),
        countLabel: M.t('design.contrast_count', { count: issues.length }),
        items: issues.map((issue) => M.t('design.contrast_item', {
          zone: catalogue.groups.find((group) => group.id === issue.groups[0]).label,
          fg: issue.fgLabel,
          bg: issue.bgLabel,
          ratio: issue.ratio.toFixed(1).replace('.', ','),
          min: String(issue.min).replace('.', ','),
        })),
      },
    };
  },

  renderDesign() {
    if (!M.design.catalogue) return;

    const vm = C.buildDesignVM();

    if (!C.designBuilt) {
      V.loadFontSamples(M.design.catalogue.fonts);
      V.renderDesign(vm);
      C.designBuilt = true;
    } else {
      V.syncDesign(vm);
    }

    V.setSavebar('design', M.isDesignDirty() ? M.t('savebar.design') : '');
  },

  /**
   * Met à jour l'écran et l'aperçu après un changement du brouillon.
   */
  designChanged() {
    C.renderDesign();
    V.postPreview(M.previewMessage());
  },

  /**
   * URL de la page affichée dans l'aperçu.
   * @returns {string}
   */
  previewUrl() {
    return C.preview.page === 'legal' ? '/fr/legal?preview=1' : '/fr/?preview=1';
  },

  loadPreview() {
    V.loadPreview(C.previewUrl());
  },

  /**
   * L'aperçu a fini de charger : on lui envoie le brouillon en cours.
   */
  handler_previewLoaded() {
    if (M.design.catalogue) V.postPreview(M.previewMessage());
  },

  handler_previewPage(ev) {
    C.preview.page = V.readSegmentValue(ev.target);
    V.setSegment('preview-page', C.preview.page);
    C.loadPreview();
  },

  handler_previewDevice(ev) {
    C.preview.device = V.readSegmentValue(ev.target);
    V.setSegment('preview-device', C.preview.device);
    V.scalePreview(C.preview.device);
  },

  handler_previewScroll() {
    V.scrollToPreview();
  },

  handler_applyPreset(ev) {
    M.applyPreset(V.readPreset(ev.target));
    C.designChanged();
  },

  handler_colorInput(ev) {
    const { key, value } = V.readColorInput(ev.target);

    M.setColor(key, value);
    C.designChanged();
  },

  /**
   * Case « même fond que la page » : cochée, le fond redevient transparent ; décochée, il prend la couleur actuelle de la page.
   */
  handler_colorTransparent(ev) {
    const key = V.readColorKey(ev.target);

    if (ev.target.checked) {
      M.resetColor(key);
    } else {
      M.setColor(key, M.resolveColor('page-bg'));
    }

    C.designChanged();
  },

  handler_resetColor(ev) {
    M.resetColor(V.readColorKey(ev.target));
    C.designChanged();
  },

  handler_chooseFont(ev) {
    const { role, key } = V.readFontChoice(ev.target);

    M.setFont(role, key);
    C.designChanged();
  },

  handler_chooseOption(ev) {
    const { name, value } = V.readOptionChoice(ev.target);

    M.setOption(name, value);
    C.designChanged();
  },

  handler_changePhoto(ev) {
    const index = V.readPhotoIndex(ev.target);

    C.picker = { target: 'photo', index };
    C.openPicker(M.design.draft.photos[index]);
  },

  async handler_publishDesign(ev) {
    const button = ev ? ev.target.closest('button') : null;

    V.setBusy(button, true, M.t('savebar.saving'));
    const answer = await M.publishDesign();
    V.setBusy(button, false, '');

    if (C.report(answer, M.t('feedback.design_published'))) {
      M.commitDesign();
      await M.reloadDesign();
      C.renderDesign();
      V.postPreview(M.previewMessage());
    }
  },

  handler_cancelDesign() {
    M.discardDesignDraft();
    C.designChanged();
  },

  async handler_restoreDesign(ev) {
    const { id, date } = V.readHistoryItem(ev.target);

    if (!V.confirm(M.t('feedback.confirm_restore', { date }))) return;

    const answer = await M.restoreDesign(id);

    if (C.report(answer, M.t('feedback.design_restored'))) {
      await M.reloadDesign();
      C.designChanged();
    }
  },

  /* ---------- Visites ---------- */

  renderTours() {
    V.renderTours(M.tours.map((tour) => ({
      id: tour.id,
      title: M.tourTitle(tour),
      meta: M.tourMeta(tour),
      active: Number(tour.is_active) === 1,
      statusText: Number(tour.is_active) === 1 ? M.t('tours.visible') : M.t('tours.hidden'),
      imageUrl: M.imageUrl(tour.image_url || ''),
    })), M.t('tours.empty'));
  },

  /**
   * Ouvre la fenêtre d'édition d'une visite.
   * @param {Object|null} tour Visite existante, null pour une création
   */
  openTourEditor(tour) {
    const empty = { title: '', tagline: '', summary: '' };
    const translations = Object.fromEntries(M.langs.map((lang) => [lang, { ...empty, ...((tour && tour.translations[lang]) || {}) }]));

    V.openTourEditor({
      isNew: tour === null,
      id: tour ? String(tour.id) : '',
      duration: tour ? tour.duration : '',
      capacity: tour ? tour.capacity : '',
      group_type: tour ? tour.group_type : 'small',
      is_active: tour ? Number(tour.is_active) === 1 : true,
      image_url: tour ? tour.image_url || '' : '',
      imageUrl: tour ? M.imageUrl(tour.image_url || '') : '',
      translations,
    });
  },

  handler_newTour() {
    C.openTourEditor(null);
  },

  handler_editTour(ev) {
    const tour = M.findTour(V.readTourId(ev.target));

    if (tour) C.openTourEditor(tour);
  },

  handler_tourLang(ev) {
    V.showTourLang(V.readLang(ev.target));
  },

  handler_pickTourImage() {
    C.picker = { target: 'tour', index: 0 };
    C.openPicker(V.readTourImageName());
  },

  /**
   * Ouvre la fenêtre de choix d'une photo, la photo actuelle présélectionnée.
   * @param {string} selected
   */
  openPicker(selected) {
    V.openImagePicker(M.images.map((image, index) => ({
      name: image.name,
      url: image.url,
      selected: image.name === selected,
      label: M.t('design.photo_slot', { number: index + 1 }),
    })));
  },

  handler_pickImage(ev) {
    const name = V.readPickedImage(ev.target);

    if (C.picker.target === 'photo') {
      M.setPhoto(C.picker.index, name);
      C.designChanged();
    } else {
      V.setTourImage(name, M.imageUrl(name));
    }

    V.closeDialog(ev.target);
  },

  handler_closeDialog(ev) {
    V.closeDialog(ev.target);
  },

  async handler_saveTour(ev) {
    ev.preventDefault();

    const form = ev.target;
    const { id, payload } = V.readTourForm(form, M.langs);
    const error = M.validateTour(payload);

    if (error !== '') {
      V.setTourError(error);
      return;
    }

    const button = form.querySelector('button[type="submit"]');

    V.setBusy(button, true, M.t('savebar.saving'));
    const answer = await M.saveTour(id, payload);
    V.setBusy(button, false, '');

    if (answer.status === 401) {
      C.report(answer, '');
      return;
    }

    if (!(answer.data && answer.data.success)) {
      V.setTourError((answer.data && answer.data.message) || M.t('feedback.network'));
      return;
    }

    V.closeDialog(form);
    V.toast(M.t('feedback.tour_saved'), 'success');
    await M.reloadTours();
    C.renderTours();
    C.renderHome();
  },

  /* ---------- Photos ---------- */

  renderImages() {
    V.renderImages(M.images, M.t('images.empty'));
  },

  /**
   * Envoie une photo (bouton ou glisser-déposer).
   * @param {File|null} file
   */
  async upload(file) {
    if (!file) return;

    V.setUploading(true);
    const answer = await M.uploadImage(file);
    V.setUploading(false);

    if (C.report(answer, M.t('feedback.image_uploaded'))) {
      C.renderImages();
      C.renderTours();
    }
  },

  handler_uploadImage(ev) {
    C.upload(V.readUploadFile(ev));
  },

  /**
   * Survol de la zone de dépôt : on empêche le navigateur d'ouvrir le fichier.
   */
  handler_dragImage(ev, active) {
    ev.preventDefault();
    V.setDragging(active);
  },

  handler_dropImage(ev) {
    ev.preventDefault();
    C.upload(V.readUploadFile(ev));
  },

  /* ---------- Messages ---------- */

  renderMessages() {
    V.renderMessages(M.messages.map((message) => ({
      id: Number(message.id),
      fullname: message.fullname,
      date: M.t('messages.received', { date: M.formatDate(message.created_at) }),
      email: message.email,
      replyHref: M.replyHref(message.email),
      message: message.message,
    })), M.t('messages.empty'));
  },

  async handler_deleteMessage(ev) {
    const id = V.readMessageId(ev.target);
    const message = M.findMessage(id);

    if (!message || !V.confirm(M.t('feedback.confirm_delete_message', { name: message.fullname }))) return;

    V.setMessageBusy(ev.target, true);
    const answer = await M.deleteMessage(id);
    V.setMessageBusy(ev.target, false);

    // 404 : le message a déjà disparu (supprimé dans un autre onglet ou purgé), on le retire aussi de l'écran
    if (C.report(answer, M.t('feedback.message_deleted')) || answer.status === 404) {
      M.removeMessage(id);
      C.renderMessages();
      C.renderHome();
    }
  },

  /* ---------- Session ---------- */

  /** @type {number} Date (ms) de la dernière frappe ou du dernier clic de la cliente */
  lastActivity: Date.now(),

  /** @type {number} Intervalle entre deux prolongations de session (5 minutes, bien sous les 30 minutes d'inactivité du serveur) */
  keepAliveDelay: 5 * 60 * 1000,

  handler_activity() {
    C.lastActivity = Date.now();
  },

  /**
   * Prolonge la session si la cliente a été active depuis le dernier passage ; un onglet oublié laisse la session expirer.
   */
  async keepAlive() {
    if (Date.now() - C.lastActivity > C.keepAliveDelay) return;

    const answer = await M.keepAlive();

    if (answer.status === 401) C.report(answer, '');
  },

  /* ---------- Démarrage ---------- */

  async init() {
    V.init();

    if (!V.isDashboard()) return;

    M.csrfToken = V.getCsrfToken();
    M.strings = V.readStrings();

    C.showScreen(C.validScreen(V.readScreenFromHash()), false);

    const result = await M.loadAll();

    if (result.unauthorized) {
      V.redirectToLogin();
      return;
    }

    if (result.failed) V.toast(M.t('feedback.loading_error'), 'error');

    C.renderHome();
    C.renderTexts();
    C.renderDesign();
    C.renderTours();
    C.renderImages();
    C.renderMessages();

    if (C.screen === 'design') V.postPreview(M.previewMessage());

    window.setInterval(() => C.keepAlive(), C.keepAliveDelay);
  },
};

C.init();
