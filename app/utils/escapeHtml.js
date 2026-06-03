const HTML_ESCAPE_LOOKUP = {
  "&": "&amp;",
  "<": "&lt;",
  ">": "&gt;",
  '"': "&quot;",
  "'": "&#39;",
};

const escapeHtml = (value) =>
  String(value ?? "").replace(
    /[&<>"']/g,
    (character) => HTML_ESCAPE_LOOKUP[character],
  );

const sanitizeUrl = (value) => {
  if (typeof value !== "string") {
    return "";
  }

  const trimmed = value.trim();
  if (!trimmed) {
    return "";
  }

  try {
    const parsedUrl = new URL(trimmed, window.location.href);
    const allowedProtocols = ["http:", "https:", "file:", "data:"];

    if (allowedProtocols.includes(parsedUrl.protocol)) {
      return escapeHtml(parsedUrl.href);
    }
  } catch {
    if (!trimmed.includes(":")) {
      return escapeHtml(trimmed);
    }
  }

  return "";
};

export { escapeHtml, sanitizeUrl };

//ce fichier contient la fonction escapeHtml() qui permet d'échapper les caractères spéciaux en HTML pour éviter les failles XSS. La fonction sanitizeUrl() est également exportée pour nettoyer les URLs avant de les utiliser dans les attributs src ou href.

// lien mdn eviter les failles xss : https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP#script-src_directives
// lien sur les failles xss : https://www.owasp.org/index.php/Cross-site_Scripting_(XSS)
// lien sur les bonnes pratiques pour éviter les failles xss : https://www.owasp.org/index.php/XSS_(Cross_Site_Scripting)_Prevention_Cheat_Sheet
