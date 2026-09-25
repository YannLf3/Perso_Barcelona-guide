const HOST_URL = "../server/script.php";
// Mets cette valeur à true seulement si tu veux revoir les traces réseau dans la console. m'a permis de me rendre compte que le serveur renvoyait parfois des erreurs 500 avec un message d'erreur HTML au lieu d'une réponse JSON, ce qui cassait la fonction JSON.parse() et empêchait d'avoir un message d'erreur clair dans l'interface. Avec ce debug, j'ai pu identifier et corriger ce problème côté serveur.
// note : vu avec claude discussion du 18 mai pr retrouver l'aide de debug avec debug_update_tour
const DEBUG_UPDATE_TOUR = false;

let DataTour = {};

DataTour.requestAll = async function (password) {
  let answer = await fetch(
    HOST_URL + "?todo=admintours&password=" + encodeURIComponent(password), //encoder une chaine de caract : pour la sécurité
  );
  let data = await answer.json();
  return data;
};

DataTour.add = async function (fdata) {
  let config = {
    method: "POST",
    body: fdata,
  };
  let answer = await fetch(HOST_URL + "?todo=addtour", config);
  let data = await answer.json();
  return data;
};

DataTour.update = async function (fdata) {
  if (DEBUG_UPDATE_TOUR) {
    console.log("[updatetour] DataTour.update.request", {
      keys: Array.from(fdata.keys()),
    });
  }

  let config = {
    method: "POST",
    body: fdata,
  };

  if (DEBUG_UPDATE_TOUR) {
    console.log(
      "[updatetour] DataTour.update.fetch",
      HOST_URL + "?todo=updatetour",
    );
  }
  let answer = await fetch(HOST_URL + "?todo=updatetour", config);
  let responseText = await answer.text();

  try {
    let data = JSON.parse(responseText);
    if (DEBUG_UPDATE_TOUR) {
      console.log("[updatetour] DataTour.update.response", data);
    }
    return data;
  } catch (error) {
    if (DEBUG_UPDATE_TOUR) {
      console.error("[updatetour] DataTour.update.invalidJson", {
        status: answer.status,
        responseText: responseText,
        error: error,
      });
    }

    return {
      success: false,
      message: "Le serveur a renvoyé une réponse non JSON.",
      statusCode: answer.status,
      raw: responseText,
    };
  }
};

export { DataTour };
