/**
 * C (Contrôleur) : fait le lien entre M (données) et V (DOM).
 * Les méthodes handler_<action>(ev) sont appelées par les écouteurs déclarés dans V.init().
 */
const C = {
  handler_showIntro(ev) {
    ev.preventDefault();
    V.closeNav();
    V.scrollToTop();
  },

  handler_showTours() {
    V.closeNav();
    V.scrollToElement('#tour-list');
  },

  handler_showContact() {
    V.closeNav();
    V.scrollToElement('#contact-form');
  },

  handler_toggleNav() {
    V.toggleNav();
  },

  /**
   * Changement de langue : la langue fait partie de l'URL, on recharge donc la page dans la nouvelle langue.
   */
  handler_changeLang(ev) {
    const lang = ev.target.value;

    if (M.isSupportedLang(lang)) {
      V.navigateToLang(lang);
    }
  },

  /**
   * Filtre les cartes de visite selon le type de groupe choisi.
   */
  async handler_filterTours(ev) {
    const groupType = V.readFilter(ev.target);

    M.saveFilter(groupType);
    await C.applyFilter(groupType);
  },

  /**
   * Affiche les cartes du type de groupe donné et active le bouton correspondant.
   * @param {string} groupType
   */
  async applyFilter(groupType) {
    try {
      const tours = await M.loadTours(V.getLang());
      const visibleIds = M.filterTours(tours, groupType).map((tour) => String(tour.id));

      V.setActiveFilter(groupType);
      V.showTourCards(visibleIds);
    } catch (error) {
      console.error('[C.applyFilter]', error);
    }
  },

  /**
   * Ouvre la modale de détail de la carte cliquée (visite).
   */
  async handler_openCard(ev) {
    const card = V.readCard(ev.target);

    if (!card) return;

    // La touche Espace ferait défiler la page : on l'empêche
    ev.preventDefault();

    try {
      const tour = M.findById(await M.loadTours(V.getLang()), card.id);

      if (tour) V.openTourModal(tour);
    } catch (error) {
      console.error('[C.handler_openCard]', error);
    }
  },

  /**
   * Bouton "réserver" d'une carte : sur mobile on ouvre d'abord le détail, sur ordinateur on va au formulaire.
   */
  handler_bookTour(ev) {
    ev.preventDefault();
    ev.stopPropagation();

    if (V.isMobile()) {
      C.handler_openCard(ev);
    } else {
      V.scrollToElement('#contact-form');
    }
  },

  handler_closeModal() {
    V.closeModal();
  },

  /**
   * Bouton d'appel à l'action dans une modale : on ferme puis on va au formulaire.
   */
  handler_bookFromModal(ev) {
    ev.preventDefault();
    V.closeModal();
    V.scrollToElement('#contact-form');
  },

  /**
   * Envoi du formulaire de contact.
   */
  async handler_sendMessage(ev) {
    ev.preventDefault();

    const form = ev.target;
    const formData = V.readForm(form);

    V.setSending(form, true);
    const answer = await M.sendMessage(formData, V.getCsrfToken(), V.getFormError(form));
    V.setSending(form, false);

    V.renderMessage(answer);

    if (answer.success) {
      V.resetForm(form);
    }
  },

  /**
   * Aperçu de l'administration : applique l'apparence reçue si le message est valide.
   */
  handler_previewDesign(ev) {
    const design = M.readPreviewMessage(ev.data, ev.origin, V.getOrigin());

    if (design) V.applyPreviewDesign(design);
  },

  init() {
    V.init();

    // Pages sans visites (mentions légales en aperçu) : rien d'autre à préparer
    if (!V.hasTours()) return;

    // Préchargement en arrière-plan pour que les modales s'ouvrent sans attente
    M.loadTours(V.getLang()).catch(() => {});

    // Rétablit le filtre choisi avant le rafraîchissement (cookie)
    const savedFilter = M.loadFilter();

    if (savedFilter !== 'all') C.applyFilter(savedFilter);
  },
};

C.init();
