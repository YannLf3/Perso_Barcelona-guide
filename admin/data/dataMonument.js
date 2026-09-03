const HOST_URL = "../server/script.php";

const DataMonument = {};

DataMonument.requestAll = async function (password) {
  const response = await fetch(
    `${HOST_URL}?todo=adminmonuments&password=${encodeURIComponent(password)}`,
  );

  return response.json();
};

DataMonument.update = async function (formData) {
  const response = await fetch(
    `${HOST_URL}?todo=updatemonument`,
    {
      method: "POST",
      body: formData,
    },
  );

  return response.json();
};

export { DataMonument };