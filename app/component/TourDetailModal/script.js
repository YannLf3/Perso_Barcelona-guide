/**
 * TourDetailModal — détail d'une visite (description complète + image agrandie)
 */

import { escapeHtml } from "../../utils/escapeHtml.js";
import { TourCard } from "../TourCard/script.js";

// Chargement du template HTML externe
const template = await (
  await fetch(new URL("./template.html", import.meta.url))
).text();

export const TourDetailModal = {
  _dialog: null,

  labels: {
    en: {
      close: "Close",
      duration: "Duration",
      price: "Price",
      book: "Book this tour",
      noImage: "No photo available for this tour",
    },
    fr: {
      close: "Fermer",
      duration: "Durée",
      price: "Prix",
      book: "Réserver cette visite",
      noImage: "Aucune photo disponible pour cette visite",
    },
    es: {
      close: "Cerrar",
      duration: "Duración",
      price: "Precio",
      book: "Reservar este tour",
      noImage: "No hay foto disponible para este tour",
    },
    it: {
      close: "Chiudi",
      duration: "Durata",
      price: "Prezzo",
      book: "Prenota questo tour",
      noImage: "Nessuna foto disponibile per questo tour",
    },
  },

  ensureDialog() {
    if (this._dialog) {
      return this._dialog;
    }

    const dialog = document.createElement("dialog");
    dialog.className = "tour-detail-modal";
    dialog.id = "tour-detail-modal";
    dialog.setAttribute("aria-modal", "true");
    document.body.appendChild(dialog);
    this._dialog = dialog;

    dialog.addEventListener("click", (event) => {
      if (event.target === dialog) {
        dialog.close();
      }
    });

    dialog.addEventListener("cancel", () => {
      dialog.close();
    });

    dialog.addEventListener("close", () => {
      dialog.innerHTML = "";
      document.body.classList.remove("tour-detail-modal--open");
    });

    return dialog;
  },

  /**
   * @param {Object} tour
   * @param {string} lang
   * @returns {string}
   */
  format(tour, lang = "en") {
    const l = this.labels[lang] || this.labels.en;
    const data = TourCard.getDisplayData(tour, lang);
    const safeTitle = escapeHtml(data.title);
    const safeSummary = escapeHtml(data.summary);
    const safeDuration = escapeHtml(data.duration);
    const safePrice = escapeHtml(data.priceStr);
    const safeImg = data.imgSrc;

    const mediaHtml = safeImg
      ? `<figure class="tour-detail-modal__media">
           <img src="${safeImg}" alt="${safeTitle}" class="tour-detail-modal__img" />
         </figure>`
      : `<div class="tour-detail-modal__media tour-detail-modal__media--placeholder" aria-hidden="true">
           <p class="tour-detail-modal__no-img">${escapeHtml(l.noImage)}</p>
         </div>`;

    // On prend le template et on remplace les marqueurs {{...}} par les données.
    // On utilise .replaceAll() qui est direct et plus lisible.
    return template
      .replaceAll("{{closeLabel}}", escapeHtml(l.close))
      .replaceAll("{{mediaHtml}}", mediaHtml)
      .replaceAll("{{title}}", safeTitle)
      .replaceAll("{{durationLabel}}", escapeHtml(l.duration))
      .replaceAll("{{duration}}", safeDuration)
      .replaceAll("{{priceLabel}}", escapeHtml(l.price))
      .replaceAll("{{price}}", safePrice)
      .replaceAll("{{summary}}", safeSummary)
      .replaceAll("{{bookLabel}}", escapeHtml(l.book));
  },

  /**
   * @param {Object} tour
   * @param {string} lang
   */
  open(tour, lang = "en") {
    const dialog = this.ensureDialog();
    dialog.innerHTML = this.format(tour, lang);
    dialog.setAttribute("aria-labelledby", "tour-detail-modal-title");

    const closeBtn = dialog.querySelector("[data-tour-detail-close]");
    closeBtn?.addEventListener("click", () => dialog.close());

    const bookBtn = dialog.querySelector("[data-tour-detail-book]");
    bookBtn?.addEventListener("click", (event) => {
      event.preventDefault();
      dialog.close();
      document
        .querySelector("#contact-form")
        ?.scrollIntoView({ behavior: "smooth" });
    });

    document.body.classList.add("tour-detail-modal--open");
    dialog.showModal();
  },

  /**
   * @param {HTMLElement} listEl
   * @param {() => Array} getTours
   * @param {() => string} getLang
   */
  bind(listEl, getTours, getLang) {
    if (!listEl || listEl.dataset.tourModalBound === "true") {
      // 1. PROTECTION CONTRE LES DOUBLES LIAISONS
      // Si on appelle bind() plusieurs fois sur le même élément, on risque d'attacher
      // plusieurs fois les mêmes écouteurs (ce qui doublerait les actions).
      // On utilise un attribut "data-*" comme marqueur pour savoir si c'est déjà fait.

      return;
    }

    listEl.dataset.tourModalBound = "true";
    // 2. UTILITAIRE : OUVRIR LA MODALE À PARTIR D'UNE CARTE
    // Cette fonction fait le pont entre l'élément HTML (DOM) et l'objet JavaScript (Data).
    const openFromCard = (card) => {
      const tourId = card.dataset.tourId;
      if (!tourId) {
        return;
      }
      // On cherche l'objet "tour" correspondant dans nos données

      const tours = getTours() || [];
      const tour = tours.find((item) => String(item.id) === String(tourId));
      if (tour) {
        // Si on le trouve, on demande à la modale de s'ouvrir avec ces infos

        this.open(tour, getLang());
      }
    };
    // 3. GESTION DU CLIC (DÉLÉGATION D'ÉVÉNEMENT)
    // Au lieu de mettre un écouteur sur CHAQUE carte (lourd en mémoire), on en met un seul
    // sur le parent. Grâce à la "propagation" (bubbling), le clic remonte jusqu'ici.

    listEl.addEventListener("click", (event) => {
      // .closest() permet de trouver la carte parente, peu importe où on a cliqué à l'intérieur

      const card = event.target.closest(".tour-card");
      if (!card) {
        return;
      }

      const clickedCta = event.target.closest(".tour-card__cta");
      const isMobile = window.matchMedia("(max-width: 768px)").matches;

      if (clickedCta) {
        event.preventDefault(); // On empêche le lien HTML par défaut (#contact-form)
        event.stopPropagation(); //On arrête la remontée de l'événement pour ne pas déclencher d'autres clics
        if (isMobile) {
          // Sur Mobile : cliquer sur "Réserver" ouvre d'abord la modale
          // car l'utilisateur a besoin de lire les détails avant de s'engager.

          openFromCard(card);
        } else {
          // Sur Desktop : on scrolle directement vers le formulaire de contact (plus rapide).

          document
            .querySelector("#contact-form")
            ?.scrollIntoView({ behavior: "smooth" });
        }
        return;
      }

      openFromCard(card);
    });
    // 4. ACCESSIBILITÉ AU CLAVIER
    // Pour les utilisateurs naviguant avec 'Tab', on permet d'ouvrir avec 'Entrée' ou 'Espace'.
    // en s'appuyant sur les lois/regles ux ui accesibilité de la france site du gouvernement : https://accessibilite.numerique.gouv.fr/
    listEl.addEventListener("keydown", (event) => {
      if (event.key !== "Enter" && event.key !== " ") {
        return;
      }

      const card = event.target.closest(".tour-card");
      if (!card) {
        return;
      }

      const clickedCta = event.target.closest(".tour-card__cta");
      const isMobile = window.matchMedia("(max-width: 768px)").matches; // .matches est plus simple que addListener et fonctionne dans tous les navigateurs modernes (y compris Safari) contrairement à window.matchMedia("(max-width: 768px)").addListener() qui est obsolète et pas supporté partout. https://caniuse.com/mdn-api_windowmatchmedia_addlistener
      //matches permet donc de vérifier en temps réel si on est en mobile ou desktop au moment du clic clavier, ce qui est important pour décider de l'action à faire (ouvrir la modale ou scroller vers le formulaire).

      if (clickedCta) {
        event.preventDefault();
        event.stopPropagation();
        if (isMobile) {
          openFromCard(card);
        } else {
          document
            .querySelector("#contact-form")
            ?.scrollIntoView({ behavior: "smooth" });
        }
        return;
      }

      // preventDefault ici évite que la touche 'Espace' ne fasse défiler la page vers le bas.
      event.preventDefault();
      openFromCard(card);
    });
  },
};
