const HOST_URL = "../server/script.php";

let DataMessage = {};
// add() est une méthode de DataMessage qui permet d'ajouter un message
DataMessage.add = async function (fdata) {
  let config = {
    method: "POST",
    body: fdata,
  };
  let answer = await fetch(HOST_URL + "?todo=addmessage", config);
  let data = await answer.json();
  return data;
};

export { DataMessage };
