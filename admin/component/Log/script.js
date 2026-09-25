let template = await (
  await fetch(new URL("./template.html", import.meta.url))
).text();

let itemTemplate = await (
  await fetch(new URL("./item-template.html", import.meta.url))
).text();

let history = [];
let Log = {};

function add(msg) {
  // On ajoute le message à la fin du tableau en utilisant sa longueur actuelle comme index
  history[history.length] = msg;

  // Si on a plus de 10 messages, on "décale" manuellement pour supprimer le plus vieux
  if (history.length > 10) {
    let temporaryHistory = [];
    // On commence la boucle à 1 pour ignorer le premier élément (index 0)
    for (let i = 1; i < history.length; i++) {
      temporaryHistory[i - 1] = history[i];
    }
    history = temporaryHistory;
  }
}

Log.format = function (txt) {
  add(txt);

  let allLogsHtml = "";
  for (let i = 0; i < history.length; i++) {
    let item = itemTemplate;
    item = item.replace("{{text}}", history[i]);
    allLogsHtml = allLogsHtml + item;
  }

  let finalHtml = template;
  finalHtml = finalHtml.replace("{{log}}", allLogsHtml); //allLogshtml c'est tous les items créés, défini à l'interieur de la fonction format
  return finalHtml;
};

export { Log };
