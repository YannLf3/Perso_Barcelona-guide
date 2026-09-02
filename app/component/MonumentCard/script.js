/**
 * MonumentCard — composant carte monument
 * Grille CSS Grid propre, hauteur uniforme
 */

import { escapeHtml } from "../../utils/escapeHtml.js";
import { resolveMediaUrl } from "../../utils/mediaUrl.js";

// Chargement du template HTML externe
const template = await (
  await fetch(new URL("./template.html", import.meta.url))
).text();

export const MonumentCard = {
  /**
   * @param {Object} monument
   * @param {string} lang
   * @returns {string} HTML d'une carte monument
   */
  format(monument, lang = "en") {
    const labels = {
      en: { district: "District", learnMore: "Learn more" },
      fr: { district: "Quartier", learnMore: "En savoir plus" },
      es: { district: "Barrio", learnMore: "Saber más" },
      it: { district: "Quartiere", learnMore: "Scopri di più" },
    };
    const l = labels[lang] || labels.en;

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

    const imgHtml = imgSrc
      ? `<img src="${imgSrc}" alt="${safeName}" class="monument-card__img" loading="lazy" />`
      : `<div class="monument-card__img monument-card__img--placeholder" aria-hidden="true"></div>`;

    const monumentIdAttr = escapeHtml(
      monument.id != null ? String(monument.id) : "",
    );
    const ariaLabel = escapeHtml(`${l.learnMore}: ${name}`);

    // Préparation du bloc conditionnel pour le quartier
    const districtHtml = district
      ? `<p class="monument-card__district">${escapeHtml(l.district)} · ${safeDistrict}</p>`
      : "";

    // Injection des données dans le template via .replaceAll()
    // C'est beaucoup plus propre et facile à lire que les backticks (``)
    return template
      .replaceAll("{{id}}", monumentIdAttr)
      .replaceAll("{{ariaLabel}}", ariaLabel)
      .replaceAll("{{imgHtml}}", imgHtml)
      .replaceAll("{{districtHtml}}", districtHtml)
      .replaceAll("{{name}}", safeName)
      .replaceAll("{{description}}", safeDescription);
  },

  /**
   * @param {Array} monuments
   * @param {string} lang
   * @returns {string} HTML de toutes les cartes
   */
  formatMany(monuments, lang = "en") {
    if (!Array.isArray(monuments) || monuments.length === 0) {
      return `<p class="cards__empty">Aucun monument disponible.</p>`;
    }
    return monuments.map((m) => MonumentCard.format(m, lang)).join("");
  },
};
