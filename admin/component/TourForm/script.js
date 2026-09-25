let template = await (
  await fetch(new URL("./template.html", import.meta.url))
).text();

let rowTemplate = await (
  await fetch(new URL("./row-template.html", import.meta.url))
).text();

let TourForm = {};

// Stocker les traductions de chaque tour pour le changement de langue
let tourTranslations = {};

// Remplace toutes les occurrences d'un placeholder dans un template.
// On l'utilise ici parce qu'un même marqueur peut apparaître plusieurs fois
// dans le bloc HTML d'un tour (par exemple {{id}} dans plusieurs attributs).
function replaceAllOccurrences(html, searchValue, replacementValue) {
  return html.split(searchValue).join(String(replacementValue)); //split + join : une astuce pour remplacer toutes les occurrences sans utiliser de regex => plus simple et plus sûr que replaceAll (pas de problème d'échappement de caractères spéciaux)
}

TourForm.format = function (handlerAdd, handlerUpdate, tours, images) {
  let allRowsHtml = "";

  // Réinitialiser les traductions stockées
  tourTranslations = {};

  // Boucle simple pour parcourir chaque tour
  for (let i = 0; i < tours.length; i++) {
    let tour = tours[i];
    let rowHtml = rowTemplate;

    // Stocker les traductions si disponibles
    // Déterminer la langue courante (anglais par défaut ou première disponible)
    let currentLocale = "en";
    let currentTitle = tour.title || "";
    let currentTagline = tour.tagline || "";
    let currentSummary = tour.summary || "";
    let currentGroupType = tour.group_type || "small";

    if (tour.translations && tour.translations["en"]) {
      currentLocale = "en";
      currentTitle = tour.translations["en"].title;
      currentTagline = tour.translations["en"].tagline;
      currentSummary = tour.translations["en"].summary;
      currentGroupType = tour.group_type || "small";
    } else if (tour.title) {
      // Fallback vers les anciennes colonnes si elles existent encore
      currentTitle = tour.title;
      currentTagline = tour.tagline;
      currentSummary = tour.summary;
      currentGroupType = tour.group_type || "small";
    }

    // 1. On prépare un objet contenant toutes les traductions du tour actuel.
    // On utilise la décomposition (spread operator { ... }) pour créer une COPIE. spread operator : permet de créer une copie superficielle d'un objet. Cela signifie que les propriétés de l'objet sont copiées dans un nouvel objet, mais les objets imbriqués à l'intérieur ne sont pas profondément copiés (ils restent des références). C'est suffisant ici car on ne modifie pas les objets de traduction eux-mêmes, on les stocke juste tels quels.
    // C'est une bonne pratique : on évite de modifier directement l'objet 'tour' d'origine.
    const availableTranslations = tour.translations
      ? { ...tour.translations }
      : {};

    // 2. Sécurité : On s'assure qu'il y a au moins une version anglaise ('en').
    // Si le tour n'a aucune traduction enregistrée, on remplit la clé 'en' avec
    // les valeurs par défaut (fallback) récupérées juste avant dans la boucle. (fallback : mécanisme qui permet de fournir une valeur de remplacement lorsqu'une donnée attendue est absente ou invalide. Ici, si une traduction en anglais n'est pas disponible, on utilise les valeurs par défaut du tour pour éviter d'avoir des champs vides dans le formulaire.)
    if (!availableTranslations.en) {
      availableTranslations.en = {
        title: currentTitle,
        tagline: currentTagline,
        summary: currentSummary,
      };
    }

    // 3. Mise en cache : On stocke cet objet de traductions dans le dictionnaire global 'tourTranslations'.
    // On utilise l'ID du tour comme clé. Cela permet à la fonction TourForm.switchEditLang()
    // de retrouver instantanément les textes sans devoir reparcourir tout le tableau 'tours'.
    tourTranslations[tour.id] = availableTranslations;

    // Remplacement des variables par les vraies valeurs
    rowHtml = replaceAllOccurrences(
      rowHtml,
      "{{handlerUpdate}}",
      handlerUpdate,
    );
    rowHtml = replaceAllOccurrences(rowHtml, "{{id}}", tour.id);
    rowHtml = replaceAllOccurrences(rowHtml, "{{duration}}", tour.duration);
    rowHtml = replaceAllOccurrences(
      rowHtml,
      "{{capacity}}",
      tour.capacity ?? tour.price,
    );
    rowHtml = replaceAllOccurrences(rowHtml, "{{title}}", currentTitle);
    rowHtml = replaceAllOccurrences(rowHtml, "{{summary}}", currentSummary);
    rowHtml = replaceAllOccurrences(rowHtml, "{{tagline}}", currentTagline);
    rowHtml = replaceAllOccurrences(
      rowHtml,
      "{{currentLocale}}",
      currentLocale,
    );
    rowHtml = replaceAllOccurrences(
      rowHtml,
      "{{currentLocaleLabel}}",
      currentLocale.toUpperCase(),
    );

    rowHtml = replaceAllOccurrences(
      rowHtml,
      "{{selectedGroupPrivate}}",
      currentGroupType === "private" ? "selected" : "",
    );

    rowHtml = replaceAllOccurrences(
      rowHtml,
      "{{selectedGroupSmall}}",
      currentGroupType === "small" ? "selected" : "",
    );

    rowHtml = replaceAllOccurrences(
      rowHtml,
      "{{selectedGroupSchool}}",
      currentGroupType === "school" ? "selected" : "",
    );

    const selectedImageOptions = [
      `<option value="">No image</option>`,
      ...(Array.isArray(images) ? images : []).map(
        (fileName) =>
          `<option value="${fileName}" ${
            fileName === tour.image_url ? "selected" : ""
          }>${fileName}</option>`,
      ),
    ].join("");

    rowHtml = replaceAllOccurrences(
      rowHtml,
      "{{imageOptions}}",
      selectedImageOptions,
    );

    // Gestion de l'état actif/inactif
    if (tour.is_active == 1) {
      rowHtml = replaceAllOccurrences(rowHtml, "{{selectedYes}}", "selected");
      rowHtml = replaceAllOccurrences(rowHtml, "{{selectedNo}}", "");
    } else {
      rowHtml = replaceAllOccurrences(rowHtml, "{{selectedYes}}", "");
      rowHtml = replaceAllOccurrences(rowHtml, "{{selectedNo}}", "selected");
    }

    // Gestion de la sélection de langue
    const supportedLangs = ["en", "es", "fr", "it"];
    supportedLangs.forEach((lang) => {
      const selectedAttr = lang === currentLocale ? "selected" : "";
      rowHtml = replaceAllOccurrences(
        rowHtml,
        `{{selectedLang${lang.charAt(0).toUpperCase() + lang.slice(1)}}}`,
        selectedAttr,
      );
    });

    allRowsHtml = allRowsHtml + rowHtml;
  }

  const imageOptions = [
    `<option value="">No image</option>`,
    ...(Array.isArray(images) ? images : []).map(
      (fileName) => `<option value="${fileName}">${fileName}</option>`,
    ),
  ].join("");

  let finalHtml = template;
  finalHtml = replaceAllOccurrences(
    finalHtml,
    "{{imageOptions}}",
    imageOptions,
  );
  finalHtml = replaceAllOccurrences(finalHtml, "{{handlerAdd}}", handlerAdd);
  finalHtml = replaceAllOccurrences(
    finalHtml,
    "{{handlerUpdate}}",
    handlerUpdate,
  );

  // Affichage d'un message si la liste est vide
  if (allRowsHtml === "") {
    finalHtml = replaceAllOccurrences(
      finalHtml,
      "{{tourRows}}",
      "<p>No tours available.</p>",
    );
  } else {
    finalHtml = replaceAllOccurrences(finalHtml, "{{tourRows}}", allRowsHtml);
  }

  return finalHtml;
};

// Fonction pour changer la langue d'édition d'un tour
TourForm.switchEditLang = function (tourId, locale) {
  const tourData = tourTranslations[tourId] || {};
  const translation = tourData[locale] || null;
  const fallbackTranslation = tourData["en"] || {
    title: "",
    summary: "",
    tagline: "",
  };
  const nextTranslation = translation || fallbackTranslation;

  // Mettre à jour les champs du formulaire
  const form = document
    .querySelector(`form input[name="id"][value="${tourId}"]`)
    .closest("form");
  if (form) {
    form.querySelector('input[name="title"]').value = nextTranslation.title;
    form.querySelector('textarea[name="summary"]').value =
      nextTranslation.summary;
    form.querySelector('input[name="tagline"]').value =
      nextTranslation.tagline || "";
    form.querySelector('input[name="locale"]').value = locale;

    // Mettre à jour le label du bouton
    const button = form.querySelector('button[type="submit"]');
    if (button) {
      button.textContent = `Update tour (${locale.toUpperCase()})`;
    }
  }
};

// Initialiser les écouteurs d'événements pour les sélecteurs de langue
TourForm.initLangSelectors = function () {
  // querySelectorAll retourne une "NodeList" (une collection d'éléments HTML).
  const selectors = document.querySelectorAll(".edit-lang-select");

  // On utilise forEach pour "visiter" chaque élément de la liste un par un.
  // 'select' est une variable temporaire qui représente l'élément actuel à chaque tour.
  selectors.forEach((select) => {
    // Pour chaque menu déroulant trouvé, on attache un écouteur d'événement 'change'.
    select.addEventListener("change", function () {
      // 'this' représente ici l'élément HTML qui a déclenché l'événement.
      const tourId = this.getAttribute("data-tour-id");
      const locale = this.value; // La nouvelle langue choisie (en, fr, etc.)

      // On demande au composant de mettre à jour les champs du formulaire.
      TourForm.switchEditLang(tourId, locale);
    });
  });
};

export { TourForm };

// https://web.dev/looping-over-nodes-in-javascript/
// https://www.php.net/manual/fr/control-structures.foreach.php

// Chaque boucle a sa propre "personnalité" en JavaScript. Voici les différences majeures :

// 1. forEach (Le choix actuel)
// C'est quoi ? Une méthode intégrée aux Tableaux et aux NodeList.
// Avantage : Très lisible et "déclaratif". On dit quoi faire (exécuter cette fonction pour chaque élément) plutôt que comment le faire.
// Inconvénient : On ne peut pas l'arrêter en cours de route (pas de break).
// Pourquoi ici ? C'est le choix idéal car on veut attacher un écouteur à tous les sélecteurs sans exception. C'est propre et concis.
// 2. La boucle for classique (for (let i = 0; ...))
// C'est quoi ? La boucle historique, impérative.
// Avantage : La plus rapide techniquement et la plus flexible. On peut reculer, sauter des étapes ou t'arrêter quand tu veux.
// Inconvénient : Plus lourde à écrire. Il faut gérer l'index i, la condition d'arrêt, etc.
// Comparaison : On l'utilise souvent dans les fichiers format() (comme dans MessageList) car on construit une chaîne de caractères manuellement, mais pour attacher des événements, elle est un peu trop verbeuse.
// 3. La boucle for...of
// C'est quoi ? Apparue en ES6, elle parcourt les valeurs d'une collection.
// Avantage : Très lisible et permet d'utiliser break ou continue. Elle supporte aussi l'await à l'intérieur (très utile pour des appels API séquentiels).
// Comparaison : C'est la concurrente directe de forEach. Beaucoup de développeurs la préfèrent aujourd'hui car elle est plus polyvalente.
// 4. La boucle while
// C'est quoi ? "Tant que cette condition est vraie, continue".
// Pourquoi pas ici ? Le while est fait pour quand on ne sait pas à l'avance combien de tours on va faire (ex: lire un flux de données jusqu'à la fin). Ici, on sait exactement combien d'éléments on a (selectors.length).
