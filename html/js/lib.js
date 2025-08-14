// socket instance
var socket;
//cache message between users
var chatMessages = [];

// Define api error code
var errorCode = {
	auth0001: "auth-0001",
	auth0002: "auth-0002",
	groupChat0001: "groupchat-0001",
};

// define chat type
var chatType = {
	private: "chat_private",
	group: "chat_group",
};

/**
 * Send a POST request to API
 */
const postApi = async (method, payload) => {
	try {
		let result = await fetch(`router.php/${method}`, {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
			},
			body: JSON.stringify(payload),
		});

		if (!result.ok) return false;

		let data = await result.json();
		return data;
	} catch (err) {
		console.log(err);
	}
};

/**
 * Send a GET request to API
 */
const getApi = async (method) => {
	try {
		let result = await fetch(`router.php/${method}`, {
			method: "GET",
		});

		if (!result.ok) return false;

		let data = await result.json();
		return data;
	} catch (err) {
		console.log(err);
	}
};

/**
 * Create DOM element for sent message
 */
const createSendMessage = (messageObject) => {
	let divEl = document.createElement("div");
	let strongEl = document.createElement("strong");
	let spanEl = document.createElement("span");

	divEl.className = "chat-message align-self-right";

	strongEl.className = "text-orange-700";
	strongEl.textContent = `You: `;

	spanEl.textContent = messageObject.message;

	divEl.append(strongEl);
	divEl.append(spanEl);

	return divEl;
};

/**
 * Create DOM element for received message
 */
const createReceiveMessage = (messageObject) => {
	let divEl = document.createElement("div");
	let strongEl = document.createElement("strong");
	let spanEl = document.createElement("span");

	divEl.className = "chat-message align-self-left";

	strongEl.className = "text-blue-700";
	strongEl.textContent = `${messageObject.name}: `;

	spanEl.textContent = messageObject.message;

	divEl.append(strongEl);
	divEl.append(spanEl);

	return divEl;
};

/**
 * Send a message via WebSocket (stringified JSON)
 */
const sendSocketMessage = (messageObj) => {
	if (socket && socket.readyState === WebSocket.OPEN) {
		socket.send(JSON.stringify(messageObj));
	} else {
		console.warn("WebSocket is not open. Cannot send message.");
	}
};

/**
 * Parse JSON from received socket message
 */
const getSocketMessage = (message) => {
	return JSON.parse(message);
};

/**
 * Establish WebSocket connection and handle events
 */
const connectChatSocket = (userid) => {
	socket = new WebSocket("ws://192.168.0.99:5000");

	// When WebSocket connection is successfully opened
	socket.onopen = () => {
		console.log("WebSocket connection opened");
		if (socket.readyState === WebSocket.OPEN) {
			let messageObj = {
				type: "connect",
				data: {
					userid,
				},
			};

			sendSocketMessage(messageObj);
		}
	};

	// When a new message is received from WebSocket server
	socket.onmessage = (event) => {
		let messageObj = getSocketMessage(event.data);
		let chatBody = document.getElementById("chat_body");

		// Route message to proper renderer based on type
		if (messageObj.type === chatType.private) {
			renderPrivateMessageFromSocket(messageObj.data, chatBody);
		} else if (messageObj.type === chatType.group) {
			renderGroupMessageFromSocket(messageObj.data, chatBody);
		}
	};
};

/**
 * Render private chat messages received from socket.
 * Only displays messages if they match the selected partner and user.
 */
const renderPrivateMessageFromSocket = (messageData, chatBodyEl) => {
	let useridInput = document.querySelector('input[name="current_userid"]');
	let partneridInput = document.querySelector('select[name="partner"]');

	let partnerid = partneridInput.value.trim();
	let userid = useridInput.value.trim();

	// Cache received message by sender ID
	chatMessages[messageData.sender].push(messageData);

	// Only render if message belongs to current conversation
	if (partnerid == messageData.sender && userid == messageData.receiver) {
		// create message element
		let receiveMessage = createReceiveMessage(messageData);
		// Append message to chat UI
		chatBodyEl.append(receiveMessage);
	}
};

/**
 * Render group chat messages received from socket.
 * Only displays messages if they belong to the selected group.
 */
const renderGroupMessageFromSocket = (messageData, chatBodyEl) => {
	let groupUidInput = document.querySelector('select[name="group"]');

	let groupUid = groupUidInput.value.trim();

	// Cache received message by group ID
	chatMessages[messageData.receiver].push(messageData);

	// Only render if message belongs to the selected group
	if (groupUid == messageData.receiver) {
		// create message element
		let receiveMessage = createReceiveMessage(messageData);
		// Append message to chat UI
		chatBodyEl.append(receiveMessage);
	}
};

/**
 * Create a dropdown item for user list with checkbox.
 */
const createUserDropdownItem = (user) => {
	// create dropdown item
	let dropdownItem = document.createElement("div");
	dropdownItem.className = "dropdown-item";

	// create dropdown item checkbox
	let checkbox = document.createElement("input");
	checkbox.type = "checkbox";
	checkbox.name = "group_member[]";
	checkbox.id = user.id;
	checkbox.value = user.id;

	// create dropdown item label
	let label = document.createElement("label");
	label.htmlFor = user.id;
	label.textContent = user.name;

	// append checkbox and label to dropdown item
	dropdownItem.append(checkbox, label);

	return dropdownItem;
};

/**
 * Show error message in UI.
 * Ensures error element is visible if hidden.
 */
const renderError = (errorEl, message) => {
	errorEl.textContent = message;

	// Make the error visible if currently hidden
	if (errorEl.classList.contains("invisible")) {
		errorEl.classList.remove("invisible");
	}
};

/**
 * Show success message in UI and auto-hide after 3 seconds.
 */
const renderSuccess = (element, message) => {
	element.textContent = message;

	// Make the success visible if currently hidden
	if (element.classList.contains("invisible")) {
		element.classList.remove("invisible");
	}

	// change text to success color
	if (!element.classList.contains("text-success")) {
		element.classList.add("text-success");
	}

	// reset state after 3s
	setTimeout(() => {
		if (!element.classList.contains("invisible")) {
			element.classList.add("invisible");
		}
		if (element.classList.contains("text-success")) {
			element.classList.remove("text-success");
		}
	}, 3000);
};

/**
 * Create <option> element for <select>.
 */
const createSelectOption = (object) => {
	let option = document.createElement("option");
	option.value = object.id;
	option.textContent = object.name;

	return option;
};

/**
 * Populate a <select> with options created from a list.
 */
const populateSelect = (selectEl, list, callback) => {
	list.forEach((object) => {
		let option = callback(object);

		selectEl.append(option);
	});
};

/**
 * Collapse dropdown menu.
 */
const closeDropdown = (dropdown) => {
	dropdown.style.maxHeight = 0;
	dropdown.style.padding = 0;
};

/**
 * Toggle dropdown open/close state.
 */
const toggleDropdown = (dropdown) => {
	if (dropdown.style.padding == "10px") {
		closeDropdown(dropdown);
	} else {
		dropdown.style.padding = "10px";
		dropdown.style.maxHeight = dropdown.scrollHeight + "px";
	}
};

/**
 * Modal handling logic.
 * Supports open/close via buttons and clicking outside the modal content.
 */
(function handleModal() {
	document.addEventListener("click", function (e) {
		// Open modal
		if (e.target.matches("[data-open-modal]")) {
			let modalId = e.target.dataset.openModal;
			let modal = document.querySelector(modalId);
			modal.style.display = "block";
			// Close modal via close button or element
		} else if (e.target.matches("[data-close-modal]")) {
			let modalId = e.target.dataset.closeModal;
			let modal = document.querySelector(modalId);
			modal.style.display = "none";
			// Close modal when clicking on the overlay background
		} else if (e.target.classList.contains("modal")) {
			e.target.style.display = "none";
		}
	});
})();
