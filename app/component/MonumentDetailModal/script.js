/**
 * MonumentDetailModal — détail d'un monument (description complète + image agrandie)
 */

import { escapeHtml } from "../../utils/escapeHtml.js";
import { resolveMediaUrl } from "../../utils/mediaUrl.js";

// Chargement du template HTML externe
const template = await (
  await fetch(new URL("./template.html", import.meta.url))
).text();

export const MonumentDetailModal = {
  _dialog: null,

  labels: {
    en: {
      close: "Close",
      district: "District",
      book: "Book a tour of this monument",
      noImage: "No photo available for this monument",
    },
    fr: {
      close: "Fermer",
      district: "Quartier",
      book: "Réserver une visite pour ce monument",
      noImage: "Aucune photo disponible pour ce monument",
    },
    es: {
      close: "Cerrar",
      district: "Barrio",
      book: "Reservar un tour de este monumento",
      noImage: "No hay foto disponible para este monumento",
    },
    it: {
      close: "Chiudi",
      district: "Quartiere",
      book: "Prenota un tour di questo monumento",
      noImage: "Nessuna foto disponibile per questo monumento",
    },
  },

  ensureDialog() {
    if (this._dialog) {
      return this._dialog;
    }

    const dialog = document.createElement("dialog");
    dialog.className = "monument-detail-modal";
    dialog.id = "monument-detail-modal";
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
      document.body.classList.remove("monument-detail-modal--open");
    });

    return dialog;
  },

  /**
   * @param {Object} monument
   * @param {string} lang
   * @returns {string}
   */
  format(monument, lang = "en") {
    const l = this.labels[lang] || this.labels.en;
    const name = monument.name || monument.nom || "—";
    const description = monument.description || monument.desc || "";
    const district = monument.district || monument.quartier || "";
    const imgSrc = resolveMediaUrl(
      monument.image_url ||
        monument.imageUrl ||
        monument.image ||
        monument.img ||
        "",
    );

    const safeName = escapeHtml(name);
    const safeDescription = escapeHtml(description);
    const safeDistrict = escapeHtml(district);

    const mediaHtml = imgSrc
      ? `<figure class="monument-detail-modal__media">
           <img src="${imgSrc}" alt="${safeName}" class="monument-detail-modal__img" />
         </figure>`
      : `<div class="monument-detail-modal__media monument-detail-modal__media--placeholder" aria-hidden="true">
           <p class="monument-detail-modal__no-img">${escapeHtml(l.noImage)}</p>
         </div>`;

    // On prend le template et on remplace les marqueurs {{...}} par les données.
    // .replaceAll() est plus intuitif que split/join pour changer tout d'un coup.
    const html = template
      .replaceAll("{{closeLabel}}", escapeHtml(l.close))
      .replaceAll("{{mediaHtml}}", mediaHtml)
      .replaceAll("{{name}}", safeName)
      .replaceAll("{{districtLabel}}", escapeHtml(l.district))
      .replaceAll("{{district}}", safeDistrict)
      .replaceAll("{{description}}", safeDescription)
      .replaceAll("{{bookLabel}}", escapeHtml(l.book));

    return html;
  },

  /**
   * @param {Object} monument
   * @param {string} lang
   */
  open(monument, lang = "en") {
    const dialog = this.ensureDialog();
    dialog.innerHTML = this.format(monument, lang);
    dialog.setAttribute("aria-labelledby", "monument-detail-modal-title");

    const closeBtn = dialog.querySelector("[data-monument-detail-close]");
    closeBtn?.addEventListener("click", () => dialog.close());

    const bookBtn = dialog.querySelector("[data-monument-detail-book]");
    bookBtn?.addEventListener("click", (event) => {
      event.preventDefault();
      dialog.close();
      document
        .querySelector("#contact-form")
        ?.scrollIntoView({ behavior: "smooth" });
    });

    document.body.classList.add("monument-detail-modal--open");
    dialog.showModal();
  },

  /**
   * @param {HTMLElement} listEl
   * @param {() => Array} getMonuments
   * @param {() => string} getLang
   */
  bind(listEl, getMonuments, getLang) {
    if (!listEl || listEl.dataset.monumentModalBound === "true") {
      return;
    }

    listEl.dataset.monumentModalBound = "true";

    const openFromCard = (card) => {
      const monumentId = card.dataset.monumentId;
      if (!monumentId) {
        return;
      }

      const monuments = getMonuments() || [];
      const monument = monuments.find(
        (item) => String(item.id) === String(monumentId),
      );
      if (monument) {
        this.open(monument, getLang());
      }
    };

    listEl.addEventListener("click", (event) => {
      const card = event.target.closest(".monument-card");
      if (!card) {
        return;
      }
      openFromCard(card);
    });

    listEl.addEventListener("keydown", (event) => {
      if (event.key !== "Enter" && event.key !== " ") {
        return;
      }

      const card = event.target.closest(".monument-card");
      if (!card) {
        return;
      }

      event.preventDefault();
      openFromCard(card);
    });
  },
};
