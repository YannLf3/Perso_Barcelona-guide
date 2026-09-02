const HOST_URL = "../server/script.php";

let DataMonument = {};

DataMonument.requestAll = async function () {
  let answer = await fetch(HOST_URL + "?todo=readmonuments");
  let data = await answer.json();
  return data;
};

export { DataMonument };
