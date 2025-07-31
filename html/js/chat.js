(function () {
	"use strict";

	let FUNCT = {};
	let receivers = [];


	// Cache DOM elements
	let select = document.querySelector('select[name="receiver"]');
	let chatBody = document.getElementById('chat_body');
	let useridInput = document.querySelector('input[name="current_userid"]');
	let usernameInput = document.querySelector('input[name="current_username"]');

	/**
	 * Load and render receiver list (lazy-load on first click)
	 */
	FUNCT.renderReceivers = () => {
		select.addEventListener("click", async function (e) {
			if (receivers.length == 0) {
				let response = await getApi("user/getReceivers");
				// Only load once
				if (response.code !== "0") return;

				receivers = response.data.receivers;

				// Populate <select> with options
				receivers.forEach((receiver) => {
					let option = document.createElement("option");
					option.value = receiver.userid;
					option.textContent = receiver.name;

					select.append(option);
				});
			}
		});
	};

	/**
	 * Render chat messages for the selected receiver
	 */
	FUNCT.renderMessageByReceiver = () => {
		select.addEventListener("change", async function (e) {
			let receiverid = e.target.value;
			
			// If messages for this receiver are not cached, fetch them
			if (typeof chatMessages[receiverid] == "undefined") {
				let payload = {
					receiverid,
					userid: useridInput.value.trim(),
				};

				let response = await postApi("message/getMessage", payload);

				if (response.code !== "0") return;

				chatMessages[receiverid] = response.data.messages;
			}

			// Clear chat body before rendering
			chatBody.innerHTML ="";

			if (!chatMessages[receiverid].length) return;

			// Render messages: distinguish sent vs received
			chatMessages[receiverid].forEach((messageObject) => {
				if(messageObject.sender == receiverid){
					let receiveMessage = createReceiveMessage(messageObject);
					chatBody.append(receiveMessage);
				}else{
					let sendMessage =  createSendMessage(messageObject);
					chatBody.append(sendMessage);
				}
			});
		});
	};

	
	/**
	 * Handle send message button click
	 */
	FUNCT.sendMessageEvent = () => {
		document.addEventListener("click", function (e) {
			let messageInput = document.querySelector('textarea[name="message"]');
			let receiver = select.value.trim();

			// Skip empty message
			if(!messageInput.value.length) return;

			if (e.target.matches("button.sendBtn")) {
				let messageObj = {
					type: "chat",
					data:{
						sender: useridInput.value.trim(),
						name: usernameInput.value.trim(),
						receiver,
						message: messageInput.value.trim()
					}
				}

				// Add message to local cache under receiver ID
				chatMessages[receiver].push(messageObj.data);

				// Clear the textarea after sending
				messageInput.value = '';

				// Render message immediately in chat box
				let sendMessage = createSendMessage(messageObj.data);
				chatBody.append(sendMessage);

				// Send to WebSocket server
				sendSocketMessage(messageObj);
			}
		});
	}

	/**
	 * Initialize events on page load
	 */
	document.addEventListener("DOMContentLoaded", function () {

		FUNCT.sendMessageEvent();
		FUNCT.renderReceivers();
		FUNCT.renderMessageByReceiver();
	});
})();
