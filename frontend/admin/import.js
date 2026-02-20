let uploadedData = [];
let processFields = [];

function uploadCSV() {
  const file = document.getElementById("csvFile").files[0];
  const formData = new FormData();
  formData.append("file", file);

  fetch("../../php-backend/controllers/ImportController.php?action=preview", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      uploadedData = data.rows;
      processFields = data.process_fields;
      showMapping(data.headers);
    });
}

function showMapping(headers) {
  document.getElementById("mappingSection").style.display = "block";
  let html = "";

  headers.forEach((header) => {
    html += `<div>
            ${header} →
            <select data-header="${header}">
                ${processFields
                  .map(
                    (f) =>
                      `<option value="${f.field_key}">${f.field_label}</option>`,
                  )
                  .join("")}
            </select>
        </div>`;
  });

  document.getElementById("mappingArea").innerHTML = html;
}

function processImport() {
  const mapping = {};

  document.querySelectorAll("#mappingArea select").forEach((select) => {
    mapping[select.dataset.header] = select.value;
  });

  fetch("../../php-backend/controllers/ImportController.php?action=import", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      data: uploadedData,
      mapping: mapping,
      campaign_id: document.getElementById("campaignSelect").value,
    }),
  }).then(() => alert("Import Complete"));
}
