/**
 * C (Contrôleur) : fait le lien entre M (données) et V (DOM) pour le back-office.
 * Les méthodes handler_<action>(ev) sont appelées par les écouteurs déclarés dans V.init().
 */
const C = {
  /**
   * Ajoute un message au journal et l'affiche.
   * @param {string|Object} answer
   */
  log(answer) {
    V.renderLog(M.addLog(M.toLogText(answer)));
  },

  handler_toggleTheme() {
    V.toggleTheme();
  },

  /**
   * Charge toutes les données et reconstruit les écrans du tableau de bord.
   */
  async loadAdminData() {
    const data = await M.loadAll();

    if (data.unauthorized) {
      V.redirectToLogin();
      return;
    }

    V.renderTours(data.tours, data.images);
    V.renderImages(data.images);
    V.renderMessages(data.messages);
  },

  async handler_addTour(ev) {
    ev.preventDefault();

    const form = ev.target;
    const answer = await M.createTour(V.readTourAddForm(form));

    C.log(answer.data);
    V.resetForm(form);
    await C.loadAdminData();
  },

  async handler_updateTour(ev) {
    ev.preventDefault();

    const { id, payload } = V.readTourEditForm(ev.target);
    const answer = await M.updateTour(id, payload);

    C.log(answer.data);
    await C.loadAdminData();
  },

  async handler_uploadImage(ev) {
    ev.preventDefault();

    const form = ev.target;
    const answer = await M.uploadImage(V.readUploadForm(form));

    C.log(answer.data);

    if (answer.data.success) {
      V.resetForm(form);
      await C.loadAdminData();
    }
  },

  /**
   * Changement de langue dans l'édition d'une visite : on affiche la traduction correspondante.
   */
  handler_changeEditLang(ev) {
    const row = ev.target.closest('[data-tour-row]');
    const locale = ev.target.value;

    V.fillTranslation(row.querySelector('[data-form="tour-edit"]'), M.getTranslation(row.dataset.tourId, locale), locale);
  },

  async init() {
    V.init();

    if (!V.isDashboard()) return;

    M.csrfToken = V.getCsrfToken();
    M.logHistory = V.readLog();

    await C.loadAdminData();
  },
};

C.init();
