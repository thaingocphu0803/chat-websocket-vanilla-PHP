// socket instance
var socket;
//cache message between users
var chatMessages = [];

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
