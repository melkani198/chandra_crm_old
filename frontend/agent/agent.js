let timerInterval;
let seconds = 0;

function setStatus(status) {
  const badge = document.getElementById("statusBadge");
  badge.className = "badge " + status;
  badge.innerText = status.toUpperCase();
}

function startTimer() {
  seconds = 0;
  timerInterval = setInterval(() => {
    seconds++;
    document.getElementById("callTimer").innerText = new Date(seconds * 1000)
      .toISOString()
      .substr(14, 5);
  }, 1000);
}

function stopTimer() {
  clearInterval(timerInterval);
}

function setReady() {
  fetch("../../php-backend/controllers/DialerController.php")
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "no_contacts") {
        alert("No more contacts");
        return;
      }

      window.currentCallId = data.call_id;
      window.currentContactId = data.contact.id;

      loadCustomerFromObject(data.contact);
      setStatus("dialing");
    });
}

function loadCustomerFromObject(contact) {
  document.getElementById("customerDetails").innerHTML =
    "<b>Name:</b> " +
    (contact.first_name || "") +
    "<br>" +
    "<b>Phone:</b> " +
    contact.phone;
}

function setBreak() {
  fetch("api/agent-action.php", {
    method: "POST",
    body: JSON.stringify({ action: "break" }),
  });
  setStatus("break");
}

function hangup() {
  fetch("api/agent-action.php", {
    method: "POST",
    body: JSON.stringify({ action: "hangup" }),
  });
}

function saveDisposition() {
  const data = {
    call_id: window.currentCallId,
    contact_id: window.currentContactId,
    disposition: document.getElementById("mainDisposition").value,
    sub: document.getElementById("subDisposition").value,
    notes: document.getElementById("callNotes").value,
    callback: document.getElementById("callbackTime").value,
  };

  fetch("api/save-disposition.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data),
  }).then(() => {
    setStatus("wrap_up");
    setTimeout(() => setStatus("ready"), 10000);
  });
}
function joinCampaign() {
  fetch("../../php-backend/controllers/AgentCampaignController.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      campaign_id: document.getElementById("campaignSelect").value,
    }),
  });
}
function loadCustomer(phone) {
  fetch("api/load-contact.php?phone=" + phone)
    .then((res) => res.json())
    .then((data) => {
      window.currentContactId = data.id;
      document.getElementById("customerDetails").innerHTML =
        "<b>Name:</b> " +
        data.first_name +
        "<br>" +
        "<b>Phone:</b> " +
        data.phone;
    });
}
/* SSE Connection */

const evtSource = new EventSource(
  "http://192.168.1.116:3200/api/events/stream",
);

evtSource.onmessage = function (event) {
  const data = JSON.parse(event.data);

  if (data.event === "connected") {
    window.currentCallId = data.call_id;
    loadCustomer(data.number);

    setStatus("on_call");
    startTimer();
  }
  if (data.event === "ended") {
    stopTimer();
    setStatus("wrap_up");
  }
};
