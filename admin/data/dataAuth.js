const HOST_URL = "../server/script.php";

let DataAuth = {};

DataAuth.login = async function (password) {
  let fdata = new FormData();
  fdata.append("password", password); // append différente de append du python, ici append veut dire ajouter une clé/valeur et en python append veut dire ajouter un élément à une liste

  let config = {
    method: "POST",
    body: fdata,
  };

  let answer = await fetch(HOST_URL + "?todo=adminlogin", config);
  let data = await answer.json();
  return data;
};

export { DataAuth };
