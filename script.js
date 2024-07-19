function sendMessage() {
  const userInput = document.getElementById("user-input");
  const message = userInput.value.trim();
  if (!message) return;

  // Add user message to chat box
  addMessageToChatBox("user", message);
  userInput.value = "";

  // Send message to backend
  fetch("api-server.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ message }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        addMessageToChatBox("bot", data.reply);
      } else {
        addMessageToChatBox("bot", "An error occurred: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      addMessageToChatBox("bot", "An error occurred. Please try again later.");
    });
}

function addMessageToChatBox(role, message) {
  const chatBox = document.getElementById("chat-box");
  const messageElement = document.createElement("div");
  messageElement.classList.add("chat-message", role);
  messageElement.textContent = message;
  chatBox.appendChild(messageElement);
  chatBox.scrollTop = chatBox.scrollHeight;
}
