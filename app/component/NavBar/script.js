/**
 * NavBar — composant barre de navigation
 */

// Chargement du template HTML externe
const template = await (
  await fetch(new URL("./template.html", import.meta.url))
).text();

export const NavBar = {
  format(
    hIntro,
    hTours,
    hMonuments,
    hContact,
    hMenuToggle,
    hLangChange,
    lang = "en",
  ) {
    const labels = {
      en: {
        brand: "BARCELONA TOURS",
        intro: "INTRO",
        tours: "TOURS",
        monuments: "MONUMENTS",
        contact: "CONTACT",
      },
      fr: {
        brand: "BARCELONA TOURS",
        intro: "ACCUEIL",
        tours: "VISITES",
        monuments: "MONUMENTS",
        contact: "CONTACT",
      },
      es: {
        brand: "BARCELONA TOURS",
        intro: "INICIO",
        tours: "TOURS",
        monuments: "MONUMENTOS",
        contact: "CONTACTO",
      },
      it: {
        brand: "BARCELONA TOURS",
        intro: "INTRO",
        tours: "TOUR",
        monuments: "MONUMENTI",
        contact: "CONTATTO",
      },
    };
    const l = labels[lang] || labels.en;

    const langs = ["en", "es", "fr", "it"];
    const langOptions = langs
      .map(
        (code) =>
          `<option value="${code}" ${code === lang ? "selected" : ""}>${code.toUpperCase()}</option>`,
      )
      .join("");

    // Injection des données et des handlers dans le template
    return template
      .replaceAll("{{brand}}", l.brand)
      .replaceAll("{{introLabel}}", l.intro)
      .replaceAll("{{toursLabel}}", l.tours)
      .replaceAll("{{monumentsLabel}}", l.monuments)
      .replaceAll("{{contactLabel}}", l.contact)
      .replaceAll("{{hIntro}}", hIntro)
      .replaceAll("{{hTours}}", hTours)
      .replaceAll("{{hMonuments}}", hMonuments)
      .replaceAll("{{hContact}}", hContact)
      .replaceAll("{{hMenuToggle}}", hMenuToggle)
      .replaceAll("{{hLangChange}}", hLangChange)
      .replaceAll("{{langOptions}}", langOptions);
  },
};
