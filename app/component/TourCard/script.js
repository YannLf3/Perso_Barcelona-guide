/**
 * TourCard — composant carte de visite
 * Hauteur uniforme via CSS flex + overflow
 */

import { escapeHtml } from "../../utils/escapeHtml.js";
import { resolveMediaUrl } from "../../utils/mediaUrl.js";

// Chargement du template
const template = await (
  await fetch(new URL("./template.html", import.meta.url))
).text();

const CARD_LABELS = {
  en: {
    duration: "Duration",
    capacity: "Capacity",
    book: "Book this tour",
    viewDetails: "View tour details",
  },
  fr: {
    duration: "Durée",
    capacity: "Capacité",
    book: "Réserver cette visite",
    viewDetails: "Voir le détail de la visite",
  },
  es: {
    duration: "Duración",
    capacity: "Capacidad",
    book: "Reservar este tour",
    viewDetails: "Ver detalles del tour",
  },
  it: {
    duration: "Durata",
    capacity: "Capacità",
    book: "Prenota questo tour",
    viewDetails: "Vedi i dettagli del tour",
  },
};

export const TourCard = {
  /**
   * @param {Object} tour
   * @param {string} lang
   * @returns {{ id: string, title: string, summary: string, duration: string, capacityStr: string, imgSrc: string, labels: Object }}
   */
  getDisplayData(tour, lang = "en") {
    const labels = CARD_LABELS[lang] || CARD_LABELS.en;
    const title = tour.title || tour.titre || "—";
    const summary = tour.summary || tour.description || tour.resume || "";
    const duration = tour.duration || tour.duree || "—";
    const capacity = tour.capacity ?? tour.price ?? tour.tarif ?? tour.prix;
    const capacityStr =
      capacity !== undefined && capacity !== null && capacity !== ""
        ? `${capacity} pers.`
        : "—";
    const imgSrc = resolveMediaUrl(
      tour.image_url || tour.imageUrl || tour.image || tour.img || "",
    );

    return {
      id: tour.id != null ? String(tour.id) : "",
      title,
      summary,
      duration,
      capacityStr,
      imgSrc,
      labels,
    };
  },

  /**
   * @param {Object} tour
   * @param {string} lang
   * @returns {string} HTML d'une carte
   */
  format(tour, lang = "en") {
    const data = this.getDisplayData(tour, lang);
    const l = data.labels;
    const safeTitle = escapeHtml(data.title);
    const safeSummary = escapeHtml(data.summary);
    const safeDuration = escapeHtml(data.duration);
    const safeCapacity = escapeHtml(data.capacityStr);
    const safeImg = data.imgSrc;
    const tourIdAttr = escapeHtml(data.id);
    const ariaLabel = escapeHtml(`${l.viewDetails}: ${data.title}`);

    const imgHtml = safeImg
      ? `<div class="tour-card__img-wrap">
           <img src="${safeImg}" alt="${safeTitle}" class="tour-card__img" loading="lazy" />
         </div>`
      : `<div class="tour-card__img-wrap tour-card__img-wrap--placeholder" aria-hidden="true"></div>`;

    // Injection des données dans le template propre
    return template
      .replaceAll("{{id}}", tourIdAttr)
      .replaceAll("{{ariaLabel}}", ariaLabel)
      .replaceAll("{{imgHtml}}", imgHtml)
      .replaceAll("{{title}}", safeTitle)
      .replaceAll("{{summary}}", safeSummary)
      .replaceAll("{{durationLabel}}", escapeHtml(l.duration))
      .replaceAll("{{duration}}", safeDuration)
      .replaceAll("{{capacityLabel}}", escapeHtml(l.capacity))
      .replaceAll("{{capacity}}", safeCapacity)
      .replaceAll("{{bookLabel}}", escapeHtml(l.book));
  },

  /**
   * @param {Array} tours
   * @param {string} lang
   * @returns {string} HTML de toutes les cartes
   */
  formatMany(tours, lang = "en") {
    if (!Array.isArray(tours) || tours.length === 0) {
      return `<p class="cards__empty">Aucune visite disponible.</p>`;
    }
    return tours.map((t) => TourCard.format(t, lang)).join("");
  },
};
