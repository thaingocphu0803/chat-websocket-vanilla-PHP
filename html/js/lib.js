// socket instance
var socket;
//cache message between users
var chatMessages = [];

// Define apo error code
var errorCode = {
	auth0001: "auth-0001",
	auth0002: "auth-0002",
	groupChat0001: "groupchat-0001",
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
	let divE = document.createElement("div");
	let strongE = document.createElement("strong");
	let spanE = document.createElement("span");

	divE.className = "chat-message align-self-right";

	strongE.className = "text-orange-700";
	strongE.textContent = `You: `;

	spanE.textContent = messageObject.message;

	divE.append(strongE);
	divE.append(spanE);

	return divE;
};

/**
 * Create DOM element for received message
 */
const createReceiveMessage = (messageObject) => {
	let divE = document.createElement("div");
	let strongE = document.createElement("strong");
	let spanE = document.createElement("span");

	divE.className = "chat-message align-self-left";

	strongE.className = "text-blue-700";
	strongE.textContent = `${messageObject.name}: `;

	spanE.textContent = messageObject.message;

	divE.append(strongE);
	divE.append(spanE);

	return divE;
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
const getSoketMessage = (message) => {
	return JSON.parse(message);
};

/**
 * Connect to WebSocket server
 */
const socketConnect = (userid) => {
	socket = new WebSocket("ws://192.168.0.99:5000");

	// When WebSocket connection is opened
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

	// Receive message from server
	socket.onmessage = (event) => {
		let messageObj = getSoketMessage(event.data);
		let chatBody = document.getElementById("chat_body");
		let useridInput = document.querySelector(
			'input[name="current_userid"]'
		);
		let receiveridInput = document.querySelector('select[name="receiver"]');

		let partnerid = receiveridInput.value.trim();
		let userid = useridInput.value.trim();

		let receiveMessage = createReceiveMessage(messageObj.data);

		// Render message only if it's from the selected partner to current user
		if (
			partnerid == messageObj.data.sender &&
			userid == messageObj.data.receiver
		) {
			// Cache received message by partner ID
			chatMessages[partnerid].push(messageObj.data);

			// Append message to chat UI
			chatBody.append(receiveMessage);
		}
	};
};

/**
 * Create DOM element for dropdown user list
 */
const createUserDropdownItem = (user) => {
	// create dropdown item
	let dropdownItem = document.createElement("div");
	dropdownItem.className = "dropdown-item";

	// create dropdown item checkbox
	let checkbox = document.createElement("input");
	checkbox.type = "checkbox";
	checkbox.name = "group_member[]";
	checkbox.id = user.userid;
	checkbox.value = user.userid;

	// create dropdown item label
	let label = document.createElement("label");
	label.htmlFor = user.userid;
	label.textContent = user.name;

	// append checkbox and label to dropdown item
	dropdownItem.append(checkbox, label);

	return dropdownItem;
};

/**
 * handle render error
 */
const renderError = (errorEl, message) => {
	errorEl.textContent = message;

	// Make the error visible if currently hidden
	if (errorEl.classList.contains("invisible")) {
		errorEl.classList.remove("invisible");
	}
};

/**
 * handle render success message
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
		element.textContent = "";
	}, 3000);
};

/**
 * create DOM element for option receivers
 */
const createReceiverOption = (receiver) => {
	let option = document.createElement("option");
	option.value = receiver.userid;
	option.textContent = receiver.name;

	return option;
};

/**
 * create DOM element for option group
 */
const createGroupOption = (group) => {
	let option = document.createElement("option");
	option.value = group.id;
	option.textContent = group.name;

	return option;
};

/**
 * Handle close dropdown
 */
const closeDropdown = (dropdown) => {
	dropdown.style.maxHeight = 0;
	dropdown.style.padding = 0;
};

/**
 * Handle close dropdown
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
 * Handle Modal Logic
 */
(function () {
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
