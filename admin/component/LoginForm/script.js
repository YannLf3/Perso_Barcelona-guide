let template = await (
  await fetch(new URL("./template.html", import.meta.url))
) // permet de récupérer le template HTML en donnant le chemin absolue du fichier complet
  .text();

let LoginForm = {};

LoginForm.format = function (handlerLogin) {
  let html = template;
  html = html.replace("{{handlerLogin}}", handlerLogin);
  return html;
};

export { LoginForm };
