import { sanitizeUrl } from "./escapeHtml.js";

/**
 * URL de base du dossier Images/ (frère du dossier app/).
 * Utilise document.baseURI pour respecter <base href="app/"> sur /portfolio/.
 */
function getImagesDirectoryUrl() {
  try {
    return new URL("../Images/", document.baseURI);
  } catch {
    return new URL("../Images/", window.location.href);
  }
}

/**
 * Transforme un nom de fichier (ex. asset1.jpg) en URL absolue affichable.
 */
export function resolveMediaUrl(value) {
  if (value == null || value === "") {
    return "";
  }

  const raw = String(value).trim();
  if (!raw) {
    return "";
  }

  if (/^https?:\/\//i.test(raw) || /^data:/i.test(raw)) {
    return sanitizeUrl(raw);
  }

  const fileName = raw.replace(/^(\.\.\/|\.\/)*Images\//, "");

  try {
    const resolved = new URL(fileName, getImagesDirectoryUrl()).href;
    return sanitizeUrl(resolved);
  } catch {
    return sanitizeUrl(`../Images/${fileName}`);
  }
}

// concrètement ce fichier permet de résoudre les URLs des images qui peuvent être fournies de différentes manières (URL absolue, chemin relatif, etc.) et de s'assurer qu'elles sont sûres à utiliser dans les attributs src ou href. Cela centralise la logique de gestion des chemins d'images et évite les erreurs de chemin ou les failles XSS.
