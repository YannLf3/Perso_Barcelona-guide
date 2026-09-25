const HOST_URL = "../server/script.php";

let DataMessage = {};

DataMessage.requestAll = async function (password) {
  let answer = await fetch(
    HOST_URL + "?todo=readmessages&password=" + encodeURIComponent(password),
  );
  let data = await answer.json();
  return data;
};

export { DataMessage };
