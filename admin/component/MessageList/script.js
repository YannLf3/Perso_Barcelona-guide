let template = await (
  await fetch(new URL("./template.html", import.meta.url))
).text();

let rowTemplate = await (
  await fetch(new URL("./row-template.html", import.meta.url))
).text();

let MessageList = {};

MessageList.format = function (messages) {
  let allRowsHtml = "";

  for (let i = 0; i < messages.length; i++) {
    let messageObj = messages[i];
    let row = rowTemplate;

    row = row.replace("{{fullname}}", messageObj.fullname);
    row = row.replace("{{email}}", messageObj.email);
    row = row.replace("{{date}}", messageObj.created_at);
    row = row.replace("{{message}}", messageObj.message);

    allRowsHtml = allRowsHtml + row;
  }

  let finalHtml = template;
  if (allRowsHtml === "") {
    allRowsHtml = "<p>No message found.</p>";
  }
  finalHtml = finalHtml.replace("{{rows}}", allRowsHtml);
  return finalHtml;
};

export { MessageList };
