/**
 * V (Vue) : seul objet autorisé à manipuler le DOM du back-office.
 * Les écrans sont construits en clonant les balises <template> rendues par les vues PHP.
 * Tout texte venant de la base est inséré avec textContent ou .value, jamais en HTML brut.
 * Les données arrivent déjà préparées par C (aucune logique métier ici).
 */
const V = {
  themeStorageKey: 'admin-theme',

  /** @type {number|null} Minuteur de disparition du message de confirmation */
  toastTimer: null,

  /**
   * Déclare les écouteurs d'événements (délégation sur le document).
   */
  init() {
    document.addEventListener('click', (ev) => {
      const actionEl = ev.target.closest('[data-action]');
      const action = actionEl ? actionEl.dataset.action : '';

      if (action === 'toggle-theme') return C.handler_toggleTheme(ev);
      if (action === 'toggle-menu') return C.handler_toggleMenu(ev);
      if (action === 'text-lang') return C.handler_textLang(ev);
      if (action === 'text-reset') return C.handler_textReset(ev);
      if (action === 'texts-save') return C.handler_saveTexts(ev);
      if (action === 'texts-cancel') return C.handler_cancelTexts(ev);
      if (action === 'apply-preset') return C.handler_applyPreset(ev);
      if (action === 'color-reset') return C.handler_resetColor(ev);
      if (action === 'photo-change') return C.handler_changePhoto(ev);
      if (action === 'history-restore') return C.handler_restoreDesign(ev);
      if (action === 'preview-page') return C.handler_previewPage(ev);
      if (action === 'preview-device') return C.handler_previewDevice(ev);
      if (action === 'preview-scroll') return C.handler_previewScroll(ev);
      if (action === 'design-save') return C.handler_publishDesign(ev);
      if (action === 'design-cancel') return C.handler_cancelDesign(ev);
      if (action === 'tour-new') return C.handler_newTour(ev);
      if (action === 'tour-edit') return C.handler_editTour(ev);
      if (action === 'tour-lang') return C.handler_tourLang(ev);
      if (action === 'tour-pick-image') return C.handler_pickTourImage(ev);
      if (action === 'pick-image') return C.handler_pickImage(ev);
      if (action === 'dialog-close') return C.handler_closeDialog(ev);
      if (action === 'message-delete') return C.handler_deleteMessage(ev);
      if (ev.target.closest('[data-screen-link]')) return C.handler_menuLink(ev);

      // Un clic sur le fond d'une fenêtre (backdrop) cible directement l'élément <dialog>
      if (ev.target instanceof HTMLDialogElement) return C.handler_closeDialog(ev);
    });

    document.addEventListener('input', (ev) => {
      if (ev.target.matches('[data-field-input]')) return C.handler_textInput(ev);
      if (ev.target.matches('[data-action="text-search"]')) return C.handler_textSearch(ev);
      if (ev.target.matches('[data-action="color-input"]')) return C.handler_colorInput(ev);
    });

    document.addEventListener('change', (ev) => {
      if (ev.target.matches('[data-action="toggle-password"]')) return C.handler_togglePassword(ev);
      if (ev.target.matches('[data-action="color-transparent"]')) return C.handler_colorTransparent(ev);
      if (ev.target.matches('[data-action="choose-font"]')) return C.handler_chooseFont(ev);
      if (ev.target.matches('[data-action="choose-option"]')) return C.handler_chooseOption(ev);
      if (ev.target.matches('[data-action="image-upload"]')) return C.handler_uploadImage(ev);
    });

    document.addEventListener('submit', (ev) => {
      if (ev.target.matches('[data-form="tour"]')) return C.handler_saveTour(ev);
    });

    const dropzone = document.querySelector('[data-dropzone]');

    if (dropzone) {
      ['dragenter', 'dragover'].forEach((type) => dropzone.addEventListener(type, (ev) => C.handler_dragImage(ev, true)));
      ['dragleave', 'drop'].forEach((type) => dropzone.addEventListener(type, (ev) => C.handler_dragImage(ev, false)));
      dropzone.addEventListener('drop', (ev) => C.handler_dropImage(ev));
    }

    const frame = document.querySelector('[data-preview-frame]');

    if (frame) frame.addEventListener('load', (ev) => C.handler_previewLoaded(ev));

    // Toute frappe ou tout clic compte comme une activité : la session reste ouverte tant que la cliente travaille
    ['keydown', 'pointerdown'].forEach((type) => document.addEventListener(type, () => C.handler_activity(), { passive: true }));

    window.addEventListener('hashchange', (ev) => C.handler_route(ev));
    window.addEventListener('beforeunload', (ev) => C.handler_beforeUnload(ev));
    window.addEventListener('resize', () => C.handler_resize());

    V.syncThemeToggle();
  },

  /**
   * @returns {boolean} true sur le tableau de bord (pas sur la page de connexion)
   */
  isDashboard() {
    return document.querySelector('[data-view="dashboard"]') !== null;
  },

  /**
   * Jeton CSRF placé dans le <head> par le serveur.
   * @returns {string}
   */
  getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');

    return meta ? meta.content : '';
  },

  /**
   * Textes du back-office placés par le serveur dans une balise JSON.
   * @returns {Object}
   */
  readStrings() {
    const node = document.querySelector('#admin-strings');

    try {
      return node ? JSON.parse(node.textContent) : {};
    } catch (error) {
      return {};
    }
  },

  /**
   * Renvoie vers la page de connexion (session expirée).
   */
  redirectToLogin() {
    window.location.assign('/admin/login');
  },

  /**
   * Demande une confirmation à la cliente.
   * @param {string} message
   * @returns {boolean}
   */
  confirm(message) {
    return window.confirm(message);
  },

  /* ---------- Coquille : thème, menu, écrans, messages ---------- */

  /**
   * Met à jour le libellé et aria-pressed du bouton de thème d'après l'attribut data-theme du <html>.
   */
  syncThemeToggle() {
    const toggle = document.querySelector('[data-action="toggle-theme"]');

    if (!toggle) return;

    const isDark = document.documentElement.dataset.theme === 'dark';

    toggle.setAttribute('aria-pressed', String(isDark));
    toggle.querySelector('[data-theme-label]').textContent = isDark ? toggle.dataset.labelLight : toggle.dataset.labelDark;
  },

  /**
   * Bascule entre thème clair et sombre et mémorise le choix dans le navigateur.
   */
  toggleTheme() {
    const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';

    try {
      window.localStorage.setItem(V.themeStorageKey, next);
    } catch (error) {
      // Stockage indisponible : le thème reste valable pour la page courante
    }

    document.documentElement.dataset.theme = next;
    V.syncThemeToggle();
  },

  /**
   * Ouvre ou ferme le menu sur téléphone.
   * @param {boolean} [force]
   */
  toggleMenu(force) {
    const menu = document.querySelector('#admin-menu');
    const button = document.querySelector('[data-action="toggle-menu"]');

    if (!menu || !button) return;

    const open = typeof force === 'boolean' ? force : menu.dataset.open !== 'true';

    menu.dataset.open = String(open);
    button.setAttribute('aria-expanded', String(open));
  },

  /**
   * Écran demandé par l'ancre de l'URL.
   * @returns {string}
   */
  readScreenFromHash() {
    return window.location.hash.replace('#', '');
  },

  /**
   * Remet l'ancre de l'URL sans déclencher de changement d'écran.
   * @param {string} screen
   */
  setHash(screen) {
    window.history.replaceState(null, '', `#${screen}`);
  },

  /**
   * Liste des écrans existants.
   * @returns {Array<string>}
   */
  screenNames() {
    return Array.from(document.querySelectorAll('[data-screen]')).map((section) => section.dataset.screen);
  },

  /**
   * Affiche un écran, met à jour le menu et place le focus sur son titre.
   * @param {string} screen
   * @param {boolean} moveFocus
   */
  showScreen(screen, moveFocus) {
    document.querySelectorAll('[data-screen]').forEach((section) => {
      section.hidden = section.dataset.screen !== screen;
    });

    document.querySelectorAll('[data-screen-link]').forEach((link) => {
      if (link.dataset.screenLink === screen) {
        link.setAttribute('aria-current', 'page');
      } else {
        link.removeAttribute('aria-current');
      }
    });

    V.toggleMenu(false);
    window.scrollTo({ top: 0 });

    const title = document.querySelector(`[data-screen="${screen}"] h1`);

    if (moveFocus && title) {
      title.setAttribute('tabindex', '-1');
      title.focus({ preventScroll: true });
    }
  },

  /**
   * Affiche un message de confirmation ou d'erreur en bas de l'écran.
   * @param {string} message
   * @param {'success'|'error'} tone
   */
  toast(message, tone) {
    const node = document.querySelector('[data-toast]');

    if (!node || !message) return;

    node.textContent = message;
    node.dataset.tone = tone;
    node.setAttribute('role', tone === 'error' ? 'alert' : 'status');
    node.hidden = false;

    window.clearTimeout(V.toastTimer);
    V.toastTimer = window.setTimeout(() => {
      node.hidden = true;
    }, tone === 'error' ? 8000 : 4500);
  },

  /**
   * Barre d'enregistrement : visible avec son texte, ou masquée.
   * @param {string} name "texts" ou "design"
   * @param {string} text Texte vide pour masquer la barre
   */
  setSavebar(name, text) {
    const bar = document.querySelector(`[data-savebar="${name}"]`);

    if (!bar) return;

    bar.hidden = text === '';
    bar.querySelector('[data-savebar-text]').textContent = text;
  },

  /**
   * Bouton en cours d'envoi : désactivé, avec un libellé d'attente.
   * @param {HTMLButtonElement|null} button
   * @param {boolean} busy
   * @param {string} busyLabel
   */
  setBusy(button, busy, busyLabel) {
    if (!button) return;

    button.disabled = busy;
    button.textContent = busy ? busyLabel : button.dataset.label || button.textContent;
  },

  /**
   * Clone le contenu d'un <template>.
   * @param {string} id
   * @returns {DocumentFragment}
   */
  cloneTemplate(id) {
    return document.importNode(document.querySelector(`#${id}`).content, true);
  },

  /**
   * Paragraphe de message simple (liste vide, chargement).
   * @param {string} text
   * @returns {HTMLParagraphElement}
   */
  emptyNote(text) {
    const p = document.createElement('p');
    p.className = 'text-admin-muted';
    p.textContent = text;

    return p;
  },

  /**
   * Affiche ou masque le champ mot de passe en clair (page de connexion).
   * @param {boolean} visible
   */
  togglePassword(visible) {
    const input = document.querySelector('[data-password]');

    if (input) input.type = visible ? 'text' : 'password';
  },

  /* ---------- Accueil ---------- */

  /**
   * @param {{messages: Array<Object>, toursText: string, messageCount: number, emptyText: string}} vm
   */
  renderHome(vm) {
    const list = document.querySelector('[data-home-messages]');

    list.replaceChildren(
      ...(vm.messages.length === 0
        ? [Object.assign(document.createElement('li'), { className: 'text-admin-muted', textContent: vm.emptyText })]
        : vm.messages.map((message) => {
          const item = V.cloneTemplate('home-message-template');

          ['fullname', 'date', 'message'].forEach((field) => {
            item.querySelector(`[data-field="${field}"]`).textContent = message[field];
          });

          return item;
        })),
    );

    document.querySelector('[data-home-tours]').textContent = vm.toursText;

    const badge = document.querySelector('[data-message-badge]');

    badge.textContent = String(vm.messageCount);
    badge.hidden = vm.messageCount === 0;
  },

  /* ---------- Textes du site ---------- */

  /**
   * Construit les groupes de textes pour une langue.
   * @param {Array<Object>} groups Groupes préparés par C (champs avec valeur, origine, état)
   * @param {string} lang
   */
  renderTexts(groups, lang) {
    const build = (group) => {
      const fragment = V.cloneTemplate('text-group-template');
      const details = fragment.querySelector('[data-text-group]');

      details.dataset.group = group.id;
      details.querySelector('[data-group-label]').textContent = group.label;
      details.querySelector('[data-group-help]').textContent = group.help;
      details.querySelector('[data-group-fields]').append(...group.fields.map((field) => V.buildTextField(field, lang)));

      return fragment;
    };

    document.querySelector('[data-text-groups]').replaceChildren(...groups.filter((group) => !group.advanced).map(build));
    document.querySelector('[data-text-advanced-groups]').replaceChildren(...groups.filter((group) => group.advanced).map(build));
    document.querySelector('[data-text-advanced]').hidden = false;

    document.querySelectorAll('[data-action="text-lang"]').forEach((button) => {
      button.setAttribute('aria-pressed', String(button.dataset.lang === lang));
    });

    groups.forEach((group) => V.syncTextGroupCount(group.id, group.modifiedCount, group.modifiedLabel));
  },

  /**
   * Construit un champ de texte.
   * @param {Object} field
   * @param {string} lang
   * @returns {DocumentFragment}
   */
  buildTextField(field, lang) {
    const fragment = V.cloneTemplate('text-field-template');
    const wrap = fragment.querySelector('[data-text-field]');
    const input = wrap.querySelector('[data-field-input]');
    const id = `text-${field.key.replace(/\./g, '-')}`;

    wrap.dataset.key = field.key;
    wrap.querySelector('[data-field-label]').textContent = field.label;
    wrap.querySelector('[data-field-label]').htmlFor = id;
    input.id = id;
    input.lang = lang;
    input.value = field.value;
    input.maxLength = field.max;
    input.rows = field.long ? 4 : 1;
    wrap.querySelector('[data-field-origin]').textContent = field.origin;
    V.syncTextField(wrap, field);

    return fragment;
  },

  /**
   * Met à jour l'état d'un champ (badge « Modifié », texte d'origine, avertissement).
   * @param {HTMLElement|string} target Élément [data-text-field] ou clé du texte
   * @param {{modified: boolean, warning: string}} state
   */
  syncTextField(target, state) {
    const wrap = typeof target === 'string' ? document.querySelector(`[data-text-field][data-key="${CSS.escape(target)}"]`) : target;

    if (!wrap) return;

    wrap.querySelector('[data-field-modified]').hidden = !state.modified;
    wrap.querySelector('[data-field-origin-wrap]').hidden = !state.modified;
    wrap.querySelector('[data-action="text-reset"]').hidden = !state.modified;

    const warning = wrap.querySelector('[data-field-warning]');
    warning.hidden = state.warning === '';
    warning.textContent = state.warning;
  },

  /**
   * Remet une valeur dans un champ (retour au texte d'origine).
   * @param {string} key
   * @param {string} value
   */
  setTextFieldValue(key, value) {
    const wrap = document.querySelector(`[data-text-field][data-key="${CSS.escape(key)}"]`);

    if (wrap) wrap.querySelector('[data-field-input]').value = value;
  },

  /**
   * Pastille du nombre de textes modifiés dans un groupe.
   * @param {string} groupId
   * @param {number} count
   * @param {string} label
   */
  syncTextGroupCount(groupId, count, label) {
    const badge = document.querySelector(`[data-text-group][data-group="${groupId}"] [data-group-count]`);

    if (!badge) return;

    badge.hidden = count === 0;
    badge.textContent = label;
  },

  /**
   * Lit le champ de texte qui vient d'être modifié.
   * @param {HTMLTextAreaElement} input
   * @returns {{key: string, value: string}}
   */
  readTextInput(input) {
    return { key: input.closest('[data-text-field]').dataset.key, value: input.value };
  },

  /**
   * Clé du texte dont on a cliqué le bouton « revenir au texte d'origine ».
   * @param {Element} target
   * @returns {string}
   */
  readTextKey(target) {
    return target.closest('[data-text-field]').dataset.key;
  },

  /**
   * Filtre l'affichage : seuls les champs (et groupes) correspondant à la recherche restent visibles.
   * @param {Array<string>} visibleKeys
   * @param {boolean} searching Recherche en cours : les groupes concernés s'ouvrent
   */
  filterTextFields(visibleKeys, searching) {
    const visible = new Set(visibleKeys);

    document.querySelectorAll('[data-text-field]').forEach((field) => {
      field.hidden = !visible.has(field.dataset.key);
    });

    document.querySelectorAll('[data-text-group]').forEach((group) => {
      const hasVisible = group.querySelector('[data-text-field]:not([hidden])') !== null;

      group.hidden = !hasVisible;
      if (searching && hasVisible) group.open = true;
    });

    document.querySelector('[data-text-advanced]').hidden = document.querySelector('[data-text-advanced-groups] [data-text-group]:not([hidden])') === null;
    document.querySelector('[data-text-noresult]').hidden = visible.size > 0;
  },

  /**
   * @param {Element} target Bouton de langue cliqué
   * @returns {string}
   */
  readLang(target) {
    return target.closest('[data-lang]').dataset.lang;
  },

  /**
   * @returns {string} Recherche saisie dans l'écran des textes
   */
  readTextSearch() {
    const input = document.querySelector('[data-action="text-search"]');

    return input ? input.value : '';
  },

  /* ---------- Apparence ---------- */

  /**
   * Construit tout l'écran d'apparence (une seule fois, puis syncDesign met à jour les valeurs).
   * @param {Object} vm Modèle de vue préparé par C
   */
  renderDesign(vm) {
    document.querySelector('[data-presets]').replaceChildren(...vm.presets.map((preset) => {
      const fragment = V.cloneTemplate('preset-template');
      const button = fragment.querySelector('button');

      button.dataset.preset = preset.key;
      button.querySelector('[data-preset-label]').textContent = preset.label;
      button.querySelector('[data-preset-description]').textContent = preset.description;
      button.querySelector('[data-preset-swatches]').append(...preset.swatches.map((color) => V.swatch(color, 'h-full flex-1')));

      return fragment;
    }));

    ['heading', 'body'].forEach((role) => {
      document.querySelector(`[data-fonts="${role}"]`).replaceChildren(...vm.fonts.map((font) => {
        const fragment = V.cloneTemplate('font-template');
        const input = fragment.querySelector('input');
        const sample = fragment.querySelector('[data-font-sample]');

        input.name = `font-${role}`;
        input.value = font.key;
        input.dataset.role = role;
        sample.textContent = vm.fontSample;
        sample.style.fontFamily = font.stack;
        sample.style.fontWeight = role === 'heading' ? '700' : '400';
        fragment.querySelector('[data-font-name]').textContent = font.name;
        fragment.querySelector('[data-font-description]').textContent = font.description;

        return fragment;
      }));
    });

    Object.entries(vm.choices).forEach(([name, options]) => {
      document.querySelector(`[data-choices="${name}"]`).replaceChildren(...options.map((option) => {
        const fragment = V.cloneTemplate('choice-template');
        const input = fragment.querySelector('input');

        input.name = `choice-${name}`;
        input.value = option.value;
        input.dataset.option = name;
        fragment.querySelector('[data-choice-label]').textContent = option.label;

        return fragment;
      }));
    });

    document.querySelector('[data-color-groups]').replaceChildren(...vm.groups.map((group, index) => {
      const fragment = V.cloneTemplate('color-group-template');
      const details = fragment.querySelector('[data-color-group]');

      details.dataset.group = group.id;
      details.open = index === 0;
      details.querySelector('[data-group-label]').textContent = group.label;
      details.querySelector('[data-group-help]').textContent = group.help;
      details.querySelector('[data-group-rows]').append(...group.rows.map((row) => {
        const rowFragment = V.cloneTemplate('color-row-template');
        const rowEl = rowFragment.querySelector('[data-color-row]');
        const input = rowEl.querySelector('[data-action="color-input"]');
        const id = `color-${row.key}`;

        rowEl.dataset.key = row.key;
        input.id = id;
        rowEl.querySelector('[data-color-label]').htmlFor = id;
        rowEl.querySelector('[data-color-label]').textContent = row.label;
        rowEl.querySelector('[data-transparent-wrap]').hidden = !row.canBeTransparent;

        return rowFragment;
      }));

      return fragment;
    }));

    V.syncDesign(vm);
  },

  /**
   * Met à jour les valeurs affichées de l'écran d'apparence, sans reconstruire les éléments en cours d'utilisation.
   * @param {Object} vm
   */
  syncDesign(vm) {
    ['heading', 'body'].forEach((role) => {
      document.querySelectorAll(`[data-fonts="${role}"] input`).forEach((input) => {
        input.checked = input.value === vm.selectedFonts[role];
      });
    });

    Object.entries(vm.selectedOptions).forEach(([name, value]) => {
      document.querySelectorAll(`[data-choices="${name}"] input`).forEach((input) => {
        input.checked = input.value === value;
      });
    });

    vm.groups.forEach((group) => {
      const details = document.querySelector(`[data-color-group][data-group="${group.id}"]`);

      if (!details) return;

      details.querySelector('[data-group-swatches]').replaceChildren(...group.swatches.map((color) => V.swatch(color, 'size-6')));

      const warning = details.querySelector('[data-group-warning]');
      warning.hidden = group.warningLabel === '';
      warning.textContent = group.warningLabel;

      group.rows.forEach((row) => {
        const rowEl = details.querySelector(`[data-color-row][data-key="${row.key}"]`);
        const input = rowEl.querySelector('[data-action="color-input"]');

        // On ne touche pas à la valeur du sélecteur que la cliente est en train d'utiliser
        if (document.activeElement !== input) input.value = row.color;

        input.disabled = row.isTransparent;
        input.setAttribute('aria-label', row.inputLabel);
        rowEl.querySelector('[data-color-state]').textContent = row.stateText;
        rowEl.querySelector('[data-action="color-transparent"]').checked = row.isTransparent;
        rowEl.querySelector('[data-action="color-reset"]').hidden = !row.isCustom;
      });
    });

    document.querySelector('[data-photos]').replaceChildren(...vm.photos.map((photo) => {
      const fragment = V.cloneTemplate('photo-slot-template');
      const img = fragment.querySelector('[data-photo-image]');

      img.src = photo.url;
      img.alt = photo.label;
      fragment.querySelector('[data-photo-label]').textContent = photo.label;
      fragment.querySelector('[data-action="photo-change"]').dataset.index = String(photo.index);
      fragment.querySelector('[data-action="photo-change"]').setAttribute('aria-label', `${photo.changeLabel} : ${photo.label}`);

      return fragment;
    }));

    document.querySelector('[data-history]').replaceChildren(...(vm.history.length === 0
      ? [Object.assign(document.createElement('li'), { className: 'text-admin-muted', textContent: vm.historyEmpty })]
      : vm.history.map((item) => {
        const fragment = V.cloneTemplate('history-item-template');

        fragment.querySelector('[data-history-date]').textContent = item.date;
        fragment.querySelector('[data-history-label]').textContent = item.label;
        fragment.querySelector('[data-history-current]').hidden = !item.current;

        const restore = fragment.querySelector('[data-action="history-restore"]');
        restore.hidden = item.current;
        restore.dataset.id = String(item.id);
        restore.dataset.date = item.date;

        return fragment;
      })));

    document.querySelector('[data-contrast-summary]').textContent = vm.contrast.summary;
    document.querySelector('[data-contrast-list]').replaceChildren(...vm.contrast.items.map((text) => Object.assign(document.createElement('li'), { textContent: text })));
    document.querySelector('[data-contrast-details]').hidden = vm.contrast.items.length === 0;
    document.querySelector('[data-contrast-count]').textContent = vm.contrast.countLabel;
    document.querySelector('[data-contrast]').classList.toggle('border-admin-warning', vm.contrast.items.length > 0);
  },

  /**
   * Pastille de couleur décorative.
   * @param {string} color
   * @param {string} className
   * @returns {HTMLSpanElement}
   */
  swatch(color, className) {
    const span = document.createElement('span');
    span.className = `block ${className}`;
    span.style.backgroundColor = color;

    return span;
  },

  /**
   * Charge les polices du catalogue pour afficher les exemples (API FontFace, fichiers du site uniquement).
   * @param {Array<{family: string, url: string, weight: string}>} fonts
   */
  loadFontSamples(fonts) {
    fonts.forEach((font) => {
      const face = new FontFace(font.family, `url("${font.url}") format("woff2")`, { weight: font.weight, display: 'swap' });

      face.load().then((loaded) => document.fonts.add(loaded)).catch(() => {});
    });
  },

  /**
   * @param {Element} target
   * @returns {{key: string, value: string}}
   */
  readColorInput(target) {
    return { key: target.closest('[data-color-row]').dataset.key, value: target.value };
  },

  /**
   * @param {Element} target
   * @returns {string}
   */
  readColorKey(target) {
    return target.closest('[data-color-row]').dataset.key;
  },

  /**
   * @param {Element} target
   * @returns {string}
   */
  readPreset(target) {
    return target.closest('[data-preset]').dataset.preset;
  },

  /**
   * @param {HTMLInputElement} input
   * @returns {{role: string, key: string}}
   */
  readFontChoice(input) {
    return { role: input.dataset.role, key: input.value };
  },

  /**
   * @param {HTMLInputElement} input
   * @returns {{name: string, value: string}}
   */
  readOptionChoice(input) {
    return { name: input.dataset.option, value: input.value };
  },

  /**
   * @param {Element} target
   * @returns {{id: number, date: string}}
   */
  readHistoryItem(target) {
    const button = target.closest('[data-action="history-restore"]');

    return { id: Number(button.dataset.id), date: button.dataset.date };
  },

  /**
   * @param {Element} target
   * @returns {number}
   */
  readPhotoIndex(target) {
    return Number(target.closest('[data-action="photo-change"]').dataset.index);
  },

  /**
   * @param {Element} target
   * @returns {string} Valeur du bouton d'un groupe segmenté (page ou format de l'aperçu)
   */
  readSegmentValue(target) {
    return target.closest('[data-value]').dataset.value;
  },

  /**
   * Active un bouton dans un groupe segmenté.
   * @param {string} action
   * @param {string} value
   */
  setSegment(action, value) {
    document.querySelectorAll(`[data-action="${action}"]`).forEach((button) => {
      button.setAttribute('aria-pressed', String(button.dataset.value === value));
    });
  },

  /**
   * Charge une page publique dans le cadre d'aperçu (mode ?preview=1, réservé à la session admin).
   * @param {string} url
   */
  loadPreview(url) {
    const frame = document.querySelector('[data-preview-frame]');

    if (frame && frame.getAttribute('src') !== url) frame.setAttribute('src', url);
  },

  /**
   * Envoie l'apparence en cours à l'aperçu (même origine uniquement).
   * @param {Object} message
   */
  postPreview(message) {
    const frame = document.querySelector('[data-preview-frame]');

    if (frame && frame.contentWindow) frame.contentWindow.postMessage(message, window.location.origin);
  },

  /**
   * Met l'aperçu à l'échelle : largeur réelle d'un écran d'ordinateur (1280 px) ou de téléphone (375 px).
   * @param {'desktop'|'mobile'} device
   */
  scalePreview(device) {
    const stage = document.querySelector('[data-preview-stage]');
    const frame = document.querySelector('[data-preview-frame]');

    if (!stage || !frame || stage.clientWidth === 0) return;

    const width = device === 'mobile' ? 375 : 1280;
    const scale = Math.min(1, stage.clientWidth / width);

    frame.style.width = `${width}px`;
    frame.style.height = `${Math.round(stage.clientHeight / scale)}px`;
    frame.style.transform = `scale(${scale})`;
    frame.style.left = `${Math.max(0, Math.round((stage.clientWidth - width * scale) / 2))}px`;
  },

  /**
   * Fait défiler la page jusqu'à l'aperçu (petits écrans).
   */
  scrollToPreview() {
    const preview = document.querySelector('#design-preview');

    if (preview) preview.scrollIntoView({ behavior: 'smooth', block: 'start' });
  },

  /* ---------- Visites ---------- */

  /**
   * @param {Array<Object>} tours Cartes préparées par C
   * @param {string} emptyText
   */
  renderTours(tours, emptyText) {
    const list = document.querySelector('[data-tour-list]');

    if (tours.length === 0) {
      list.replaceChildren(V.emptyNote(emptyText));
      return;
    }

    list.replaceChildren(...tours.map((tour) => {
      const fragment = V.cloneTemplate('tour-card-template');
      const img = fragment.querySelector('[data-tour-image]');
      const status = fragment.querySelector('[data-tour-status]');

      img.hidden = tour.imageUrl === '';
      if (tour.imageUrl !== '') img.src = tour.imageUrl;
      fragment.querySelector('[data-tour-noimage]').hidden = tour.imageUrl !== '';
      status.className = `justify-self-start ${tour.active ? 'admin-badge-success' : 'admin-badge-warning'}`;
      status.textContent = tour.statusText;
      fragment.querySelector('[data-tour-title]').textContent = tour.title;
      fragment.querySelector('[data-tour-meta]').textContent = tour.meta;

      const edit = fragment.querySelector('[data-action="tour-edit"]');
      edit.dataset.id = String(tour.id);
      edit.setAttribute('aria-label', `${edit.textContent} : ${tour.title}`);

      return fragment;
    }));
  },

  /**
   * @param {Element} target
   * @returns {string}
   */
  readTourId(target) {
    return target.closest('[data-action="tour-edit"]').dataset.id;
  },

  /**
   * Ouvre la fenêtre d'édition remplie avec une visite (ou vide pour une création).
   * @param {Object} vm {id, duration, capacity, group_type, is_active, image_url, imageUrl, translations, isNew}
   */
  openTourEditor(vm) {
    const dialog = document.querySelector('#tour-editor');
    const form = dialog.querySelector('[data-form="tour"]');
    const set = (name, value) => {
      form.querySelector(`[name="${name}"]`).value = value;
    };

    form.reset();
    dialog.querySelector('[data-editor-title]').textContent = vm.isNew ? dialog.dataset.titleNew : dialog.dataset.titleEdit;
    V.setTourError('');
    set('id', vm.id);
    set('duration', vm.duration);
    set('capacity', vm.capacity);
    form.querySelectorAll('[name="group_type"]').forEach((radio) => {
      radio.checked = radio.value === vm.group_type;
    });
    form.querySelector('[name="is_active"]').checked = vm.is_active;
    V.setTourImage(vm.image_url, vm.imageUrl);

    Object.entries(vm.translations).forEach(([lang, entry]) => {
      set(`title_${lang}`, entry.title);
      set(`tagline_${lang}`, entry.tagline);
      set(`summary_${lang}`, entry.summary);
    });

    V.showTourLang('en');
    dialog.showModal();
  },

  /**
   * Affiche les champs d'une langue dans la fenêtre d'édition.
   * @param {string} lang
   */
  showTourLang(lang) {
    const dialog = document.querySelector('#tour-editor');

    dialog.querySelectorAll('[data-lang-panel]').forEach((panel) => {
      panel.hidden = panel.dataset.langPanel !== lang;
    });
    dialog.querySelectorAll('[data-action="tour-lang"]').forEach((button) => {
      button.setAttribute('aria-pressed', String(button.dataset.lang === lang));
    });
  },

  /**
   * Photo choisie dans la fenêtre d'édition.
   * @param {string} name
   * @param {string} url
   */
  setTourImage(name, url) {
    const dialog = document.querySelector('#tour-editor');
    const img = dialog.querySelector('[data-editor-image]');

    dialog.querySelector('[name="image_url"]').value = name;
    img.hidden = url === '';
    if (url !== '') img.src = url;
    dialog.querySelector('[data-editor-noimage]').hidden = url !== '';
  },

  /**
   * @returns {string} Photo actuellement choisie dans la fenêtre d'édition
   */
  readTourImageName() {
    return document.querySelector('#tour-editor [name="image_url"]').value;
  },

  /**
   * @param {string} message Message d'erreur, vide pour masquer
   */
  setTourError(message) {
    const error = document.querySelector('[data-editor-error]');

    error.hidden = message === '';
    error.textContent = message;

    if (message !== '') error.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  },

  /**
   * Lit la fenêtre d'édition.
   * @param {HTMLFormElement} form
   * @param {Array<string>} langs
   * @returns {{id: string, payload: Object}}
   */
  readTourForm(form, langs) {
    const value = (name) => form.querySelector(`[name="${name}"]`).value;
    const checked = form.querySelector('[name="group_type"]:checked');

    return {
      id: value('id'),
      payload: {
        duration: value('duration'),
        capacity: value('capacity'),
        group_type: checked ? checked.value : 'small',
        is_active: form.querySelector('[name="is_active"]').checked ? 1 : 0,
        image_url: value('image_url'),
        translations: Object.fromEntries(langs.map((lang) => [
          lang,
          { title: value(`title_${lang}`), tagline: value(`tagline_${lang}`), summary: value(`summary_${lang}`) },
        ])),
      },
    };
  },

  /**
   * Ferme la fenêtre ouverte la plus proche de l'élément cliqué (ou toutes).
   * @param {Element} target
   */
  closeDialog(target) {
    const dialog = target instanceof HTMLDialogElement ? target : target.closest('dialog');

    if (dialog) dialog.close();
  },

  /* ---------- Photos ---------- */

  /**
   * Ouvre la fenêtre de choix d'une photo.
   * @param {Array<{name: string, url: string, selected: boolean, label: string}>} images
   */
  openImagePicker(images) {
    const dialog = document.querySelector('#image-picker');

    dialog.querySelector('[data-picker-grid]').replaceChildren(...images.map((image) => {
      const fragment = V.cloneTemplate('picker-item-template');
      const button = fragment.querySelector('button');
      const img = button.querySelector('img');

      button.dataset.name = image.name;
      button.setAttribute('aria-pressed', String(image.selected));
      button.setAttribute('aria-label', image.label);
      img.src = image.url;

      return fragment;
    }));

    dialog.showModal();
  },

  /**
   * @param {Element} target
   * @returns {string} Nom de la photo choisie
   */
  readPickedImage(target) {
    return target.closest('[data-action="pick-image"]').dataset.name;
  },

  /**
   * @param {Array<{url: string}>} images
   * @param {string} emptyText
   */
  renderImages(images, emptyText) {
    const gallery = document.querySelector('[data-gallery]');

    if (images.length === 0) {
      gallery.replaceChildren(V.emptyNote(emptyText));
      return;
    }

    gallery.replaceChildren(...images.map((image) => {
      const item = V.cloneTemplate('gallery-item-template');

      item.querySelector('img').src = image.url;

      return item;
    }));
  },

  /**
   * Fichier choisi dans le champ d'envoi (ou déposé).
   * @param {Event} ev
   * @returns {File|null}
   */
  readUploadFile(ev) {
    const files = ev.dataTransfer ? ev.dataTransfer.files : ev.target.files;

    return files && files.length > 0 ? files[0] : null;
  },

  /**
   * Zone de dépôt survolée par un fichier.
   * @param {boolean} active
   */
  setDragging(active) {
    const zone = document.querySelector('[data-dropzone]');

    if (zone) zone.dataset.dragging = String(active);
  },

  /**
   * État de l'envoi d'une photo.
   * @param {boolean} uploading
   */
  setUploading(uploading) {
    const status = document.querySelector('[data-upload-status]');
    const input = document.querySelector('[data-action="image-upload"]');

    status.textContent = uploading ? status.dataset.uploading : '';
    input.disabled = uploading;

    if (!uploading) input.value = '';
  },

  /* ---------- Messages ---------- */

  /**
   * @param {Array<{id: number, fullname: string, date: string, email: string, replyHref: string, message: string}>} messages
   * @param {string} emptyText
   */
  renderMessages(messages, emptyText) {
    const list = document.querySelector('[data-message-list]');

    if (messages.length === 0) {
      list.replaceChildren(V.emptyNote(emptyText));
      return;
    }

    list.replaceChildren(...messages.map((message) => {
      const card = V.cloneTemplate('message-template');
      const reply = card.querySelector('[data-field="reply"]');

      ['fullname', 'date', 'email', 'message'].forEach((field) => {
        card.querySelector(`[data-field="${field}"]`).textContent = message[field];
      });

      if (message.replyHref === '') {
        reply.remove();
      } else {
        reply.href = message.replyHref;
      }

      card.querySelector('[data-action="message-delete"]').dataset.id = String(message.id);

      return card;
    }));
  },

  /**
   * Identifiant du message dont le bouton « Supprimer » a été cliqué.
   * @param {Element} target
   * @returns {number}
   */
  readMessageId(target) {
    return Number(target.closest('[data-action="message-delete"]').dataset.id);
  },

  /**
   * Désactive le bouton de suppression pendant l'appel à l'API.
   * @param {Element} target
   * @param {boolean} busy
   */
  setMessageBusy(target, busy) {
    const button = target.closest('[data-action="message-delete"]');

    if (button) button.disabled = busy;
  },
};
