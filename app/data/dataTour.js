const HOST_URL = "../server/script.php";

let DataTour = {};
// requestAll() est une méthode de DataTour qui permet de récupérer tous les tours
// Le paramètre lang permet de spécifier la langue (en, es, fr, it)
DataTour.requestAll = async function (lang = "en") {
  let answer = await fetch(
    HOST_URL + "?todo=readtours&lang=" + encodeURIComponent(lang),
  );
  let responseText = await answer.text();

  try {
    let data = JSON.parse(responseText);
    if (!answer.ok) {
      return [];
    }
    return Array.isArray(data) ? data : [];
  } catch (error) {
    console.error("[DataTour.requestAll] Invalid JSON response", {
      status: answer.status,
      responseText,
      error,
    });
    return [];
  }
};

export { DataTour };
