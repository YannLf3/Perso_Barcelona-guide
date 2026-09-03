const template = await (
  await fetch(new URL("./template.html", import.meta.url))
).text();

const rowTemplate = await (
  await fetch(new URL("./row-template.html", import.meta.url))
).text();

const escapeHtml = (value) =>
  String(value ?? "").replace(
    /[&<>"']/g,
    (character) =>
      ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;",
      })[character],
  );// cette fonction est utilisée pour échapper les caractères spéciaux dans les valeurs de texte afin d'éviter les problèmes de sécurité liés à l'injection de code HTML ou JavaScript.

function replaceAllOccurrences(html, searchValue, replacementValue) {
  return html.split(searchValue).join(String(replacementValue));
}

export const MonumentForm = {
  format(monuments, images, handlerUpdate) {
    const safeMonuments = Array.isArray(monuments) ? monuments : [];
    const safeImages = Array.isArray(images) ? images : [];

    const monumentRows = safeMonuments
      .map((monument) => {
        const imageOptions = [
          `<option value="">No image</option>`,
          ...safeImages.map(
            (fileName) => `
              <option value="${escapeHtml(fileName)}" ${
                fileName === monument.image_url ? "selected" : ""
              }>
                ${escapeHtml(fileName)}
              </option>
            `,
          ),
        ].join("");

        let row = rowTemplate;

        row = replaceAllOccurrences(row, "{{handlerUpdate}}", handlerUpdate);
        row = replaceAllOccurrences(row, "{{id}}", escapeHtml(monument.id));
        row = replaceAllOccurrences(row, "{{name}}", escapeHtml(monument.name));
        row = replaceAllOccurrences(
          row,
          "{{district}}",
          escapeHtml(monument.district),
        );
        row = replaceAllOccurrences(
          row,
          "{{description}}",
          escapeHtml(monument.description),
        );
        row = replaceAllOccurrences(row, "{{imageOptions}}", imageOptions);

        return row;
      })
      .join("");

    return replaceAllOccurrences(template, "{{monumentRows}}", monumentRows);
  },
};
