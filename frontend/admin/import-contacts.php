<?php require '../layout.php'; ?>

<h2>Import Contacts</h2>

<select id="campaignSelect"></select>
<input type="file" id="csvFile">
<button onclick="uploadCSV()">Upload</button>

<div id="mappingSection" style="display:none;">
    <h3>Field Mapping</h3>
    <div id="mappingArea"></div>
    <button onclick="processImport()">Import Now</button>
</div>

<script src="import.js"></script>