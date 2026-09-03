const HOST_URL = "../server/script.php";

const DataImage = {};

DataImage.requestAll = async function (password) {
  const response = await fetch(
    `${HOST_URL}?todo=listimages&password=${encodeURIComponent(password)}`,
  );

  return response.json();
};

DataImage.upload = async function (formData) {
  const response = await fetch(`${HOST_URL}?todo=uploadimage`, {
    method: "POST",
    body: formData,
  });

  return response.json();
};

export { DataImage };
