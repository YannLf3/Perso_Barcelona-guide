/**
 * GuidePresentation — Composant de présentation du guide
 * Carrousel : défilement infini en CSS (piste dupliquée), pause au survol
 */

// On importe une fonction utilitaire pour transformer un nom de fichier en URL valide.
// Cela permet de centraliser la gestion des chemins d'images dans un seul fichier.
import { resolveMediaUrl } from "../../utils/mediaUrl.js";
import { escapeHtml } from "../../utils/escapeHtml.js";

// Chargement du template HTML externe
const template = await (
  await fetch(new URL("./template.html", import.meta.url))
).text();

export const GuidePresentation = {
  /**
   * @param {string} lang
   * @returns {string} HTML
   */
  format(lang = "en") {
    const labels = {
      en: {
        eyebrow: "WELCOME TO BARCELONA",
        title:
          "Meet your <span class='guide-presentation__title-accent'>guide</span> to Barcelona",
        desc: "A local guide brings the city to life through walking tours, small group moments and natural photo stops.",
        tags: ["SMALL GROUPS", "PHOTO STOPS", "LOCAL STORIES"],
        carouselLabel: "PHOTO CAROUSEL",
        photos: [
          {
            num: "01",
            file: "asset23.webp",
            alt: "Guided walk in the old town",
            caption: "Guided walk in the old town",
          },
          {
            num: "02",
            file: "asset24.webp",
            alt: "Group pause by the sea",
            caption: "Group pause by the sea",
          },
          {
            num: "03",
            file: "asset25.webp",
            alt: "La Boqueria market",
            caption: "La Boqueria market",
          },
          {
            num: "04",
            file: "asset26.webp",
            alt: "Gaudí architecture",
            caption: "Gaudí architecture",
          },
          {
            num: "05",
            file: "asset27.webp",
            alt: "Parc Güell at sunset",
            caption: "Parc Güell at sunset",
          },
        ],
        carouselHint: "Photos scroll automatically. Hover to pause.",
        cta: "Discover the tours",
      },
      fr: {
        eyebrow: "BIENVENUE À BARCELONE",
        title:
          "Rencontrez <span class='guide-presentation__title-accent'>votre</span> guide de Barcelone",
        desc: "Une guide locale fait vivre la ville à travers des visites à pied, des moments en petits groupes et des arrêts photo qui restent naturels.",
        tags: ["PETITS GROUPES", "ARRÊTS PHOTO", "HISTOIRES LOCALES"],
        carouselLabel: "CARROUSEL PHOTO",
        photos: [
          {
            num: "01",
            file: "asset23.webp",
            alt: "Promenade guidée dans la vieille ville",
            caption: "Promenade guidée dans la vieille ville",
          },
          {
            num: "02",
            file: "asset24.webp",
            alt: "Pause en groupe au bord de mer",
            caption: "Pause en groupe au bord de mer",
          },
          {
            num: "03",
            file: "asset25.webp",
            alt: "Marché de la Boqueria",
            caption: "Marché de la Boqueria",
          },
          {
            num: "04",
            file: "asset26.webp",
            alt: "Architecture Gaudí",
            caption: "Architecture Gaudí",
          },
          {
            num: "05",
            file: "asset27.webp",
            alt: "Parc Güell au coucher du soleil",
            caption: "Parc Güell au coucher du soleil",
          },
        ],
        carouselHint:
          "Les photos défilent automatiquement. Survolez pour mettre en pause.",
        cta: "Découvrir les visites",
      },
      es: {
        eyebrow: "BIENVENIDO A BARCELONA",
        title:
          "Conoce <span class='guide-presentation__title-accent'>tu guía</span> de Barcelona",
        desc: "Una guía local da vida a la ciudad a través de tours a pie, momentos en grupos pequeños y paradas fotográficas naturales.",
        tags: ["GRUPOS PEQUEÑOS", "PARADAS FOTO", "HISTORIAS LOCALES"],
        carouselLabel: "CARRUSEL FOTOGRÁFICO",
        photos: [
          {
            num: "01",
            file: "asset23.webp",
            alt: "Paseo guiado por el casco antiguo",
            caption: "Paseo guiado por el casco antiguo",
          },
          {
            num: "02",
            file: "asset24.webp",
            alt: "Pausa en grupo junto al mar",
            caption: "Pausa en grupo junto al mar",
          },
          {
            num: "03",
            file: "asset25.webp",
            alt: "Mercado de la Boqueria",
            caption: "Mercado de la Boqueria",
          },
          {
            num: "04",
            file: "asset26.webp",
            alt: "Arquitectura de Gaudí",
            caption: "Arquitectura de Gaudí",
          },
          {
            num: "05",
            file: "asset27.webp",
            alt: "Parc Güell al atardecer",
            caption: "Parc Güell al atardecer",
          },
        ],
        carouselHint:
          "Las fotos se desplazan solas. Pasa el cursor para pausar.",
        cta: "Descubrir los tours",
      },
      it: {
        eyebrow: "BENVENUTI A BARCELLONA",
        title:
          "Incontra <span class='guide-presentation__title-accent'>la tua guida</span> di Barcellona",
        desc: "Una guida locale dà vita alla città attraverso tour a piedi, momenti in piccoli gruppi e soste fotografiche naturali.",
        tags: ["PICCOLI GRUPPI", "SOSTE FOTO", "STORIE LOCALI"],
        carouselLabel: "CAROSELLO FOTOGRAFICO",
        photos: [
          {
            num: "01",
            file: "asset23.webp",
            alt: "Passeggiata guidata nella città vecchia",
            caption: "Passeggiata guidata nella città vecchia",
          },
          {
            num: "02",
            file: "asset24.webp",
            alt: "Pausa di gruppo in riva al mare",
            caption: "Pausa di gruppo in riva al mare",
          },
          {
            num: "03",
            file: "asset25.webp",
            alt: "Mercato della Boqueria",
            caption: "Mercato della Boqueria",
          },
          {
            num: "04",
            file: "asset26.webp",
            alt: "Architettura di Gaudí",
            caption: "Architettura di Gaudí",
          },
          {
            num: "05",
            file: "asset27.webp",
            alt: "Parc Güell al tramonto",
            caption: "Parc Güell al tramonto",
          },
        ],
        carouselHint:
          "Le foto scorrono da sole. Passa il mouse per mettere in pausa.",
        cta: "Scopri i tour",
      },
    };

    const l = labels[lang] || labels.en;

    //  https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/map
    //  https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/join
    // + la vidéo que j'ai vu de Web Dev Simplified sur les méthodes de tableau JS : https://www.youtube.com/watch?v=R8rmfD9Y5-c&t=170s
    // --- GÉNÉRATION DES TAGS ---
    // .map() transforme chaque texte de l'array 'l.tags' en une balise HTML <span>.
    // Résultat intermédiaire : ["<span>TEXTE1</span>", "<span>TEXTE2</span>", ...]
    // au lieu de .map() on aurait pu faire une boucle for classique, mais .map() est plus concis et plus lisible pour ce genre de transformation de tableau.
    // .join("") fusionne tous les éléments du tableau en une seule chaîne de caractères.
    // On utilise "" pour dire qu'on ne veut aucun séparateur (sinon JS mettrait une virgule par défaut).
    // comme avec .map(), de façon plus classique, on aurait pu faire du += dans une boucle for pour construire la chaîne de caractères, mais .join() est plus efficace et plus lisible pour ce genre de concaténation d'array en string.

    const tagsHtml = l.tags
      .map((tag) => `<span class="guide-presentation__tag">${tag}</span>`)
      .join("");

    // --- GÉNÉRATION DES SLIDES DU CARROUSEL ---
    const slidesHtml = l.photos
      .map(
        (p) => `
        <div class="guide-presentation__slide" role="group" aria-label="${escapeHtml(p.caption)}">
          <img
            src="${resolveMediaUrl(p.file)}"
            alt="${escapeHtml(p.alt)}"
            class="guide-presentation__slide-img"
            loading="lazy"
            onerror="this.closest('.guide-presentation__slide').classList.add('guide-presentation__slide--no-img')"
          />
          <div class="guide-presentation__slide-caption">
            <span class="guide-presentation__slide-num">${p.num}</span>
            <span class="guide-presentation__slide-text">${escapeHtml(p.caption)}</span>
          </div>
        </div>`,
      )
      .join("");

    // Injection des données dans le template
    return template
      .replaceAll("{{eyebrow}}", escapeHtml(l.eyebrow))
      .replaceAll("{{title}}", l.title) // On n'escape pas le titre car il contient un <span>
      .replaceAll("{{desc}}", escapeHtml(l.desc))
      .replaceAll("{{tagsHtml}}", tagsHtml)
      .replaceAll("{{ctaLabel}}", escapeHtml(l.cta))
      .replaceAll("{{carouselLabel}}", escapeHtml(l.carouselLabel))
      .replaceAll("{{slidesHtml}}", slidesHtml)
      .replaceAll("{{carouselHint}}", escapeHtml(l.carouselHint));
  },
};
