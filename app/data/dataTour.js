const HOST_URL = "../server/script.php";

let DataTour = {};
// requestAll() est une méthode de DataTour qui permet de récupérer tous les tours
// Le paramètre lang permet de spécifier la langue (en, es, fr, it)
DataTour.requestAll = async function (lang = "en") {
  let answer = await fetch(
    HOST_URL + "?todo=readtours&lang=" + encodeURIComponent(lang),
  );
  let data = await answer.json();
  return data;
};

export { DataTour };
