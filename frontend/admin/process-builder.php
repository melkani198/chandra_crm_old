<?php require '../layout.php'; ?>

<h2>Create Process</h2>

<input type="text" id="processName" placeholder="Process Name">
<textarea id="processDesc"></textarea>
<button onclick="createProcess()">Create</button>

<hr>

<h3>Add Field</h3>
<input id="fieldLabel" placeholder="Field Label">
<input id="fieldKey" placeholder="Field Key">
<select id="fieldType">
    <option value="text">Text</option>
    <option value="number">Number</option>
    <option value="select">Select</option>
    <option value="textarea">Textarea</option>
</select>
<button onclick="addField()">Add Field</button>

<script>
let currentProcessId = null;

function createProcess() {
    fetch("../../php-backend/controllers/ProcessController.php?action=create", {
        method: "POST",
        body: JSON.stringify({
            name: document.getElementById("processName").value,
            description: document.getElementById("processDesc").value
        })
    }).then(r => r.json())
      .then(d => {
          currentProcessId = d.process_id;
          alert("Process Created");
      });
}

function addField() {
    fetch("../../php-backend/controllers/ProcessController.php?action=add_field", {
        method: "POST",
        body: JSON.stringify({
            process_id: currentProcessId,
            field: {
                label: document.getElementById("fieldLabel").value,
                key: document.getElementById("fieldKey").value,
                type: document.getElementById("fieldType").value,
                required: 1,
                order: 1
            }
        })
    }).then(() => alert("Field Added"));
}
</script>