(function () {
	"use strict";

	let FUNCT = {};
	let receivers = [];
	let choosenUsers = [];
	let groups = [];

	// Cache DOM elements
	let selectPrivate = document.querySelector('select[name="receiver"]');
	let selectGroup = document.querySelector('select[name="group"]');
	let chatBody = document.getElementById("chat_body");
	let useridInput = document.querySelector('input[name="current_userid"]');
	let usernameInput = document.querySelector('input[name="current_username"]');

	let choosenUserEl = document.querySelector(".choosen-users");
	let dropdown = document.querySelector(".select-memeber-dropdown");
	let groupNameInput = document.querySelector('input[name="group_name"]');
	let errorEl = document.querySelector(".create-group-error");
	let selectChatType = document.querySelector('select[name="chat-type"]')

	/**
	 * Load and render receiver list (lazy-load on first click)
	 */
	FUNCT.renderReceivers = () => {
		selectPrivate.addEventListener("click", async function (e) {
			if (receivers.length == 0) {
				let response = await getApi("user/getReceivers");
				// Only load once
				if (response.code !== "0") return;

				receivers = response.data.receivers;
			}

			// render if select had not render before
			if (selectPrivate.children.length > 1) return;

			// Populate <select> with options
			receivers.forEach((receiver) => {
				let option = createReceiverOption(receiver);

				selectPrivate.append(option);
			});
		});
	};

	/**
	 * Render chat messages for the selected receiver
	 */
	FUNCT.renderMessageByReceiver = () => {
		selectPrivate.addEventListener("change", async function (e) {
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
			chatBody.innerHTML = "";

			if (!chatMessages[receiverid].length) return;

			// Render messages: distinguish sent vs received
			chatMessages[receiverid].forEach((messageObject) => {
				if (messageObject.sender == receiverid) {
					let receiveMessage = createReceiveMessage(messageObject);
					chatBody.append(receiveMessage);
				} else {
					let sendMessage = createSendMessage(messageObject);
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
			let chatType = selectChatType.value.trim();
			
			// get receiver id by chat type
			let receiver = (chatType === 'private') ? selectPrivate.value.trim() : selectGroup.value.trim();

			// Skip empty message
			if (!messageInput.value.length) return;

			if (e.target.matches("button.sendBtn")) {
				let messageObj = {
					type: `chat_${chatType}`,
					data: {
						sender: useridInput.value.trim(),
						name: usernameInput.value.trim(),
						receiver,
						message: messageInput.value.trim(),
					},
				};

				// Add message to local cache under receiver ID
				chatMessages[receiver].push(messageObj.data);
				console.log(chatMessages);

				// Clear the textarea after sending
				messageInput.value = "";

				// Render message immediately in chat box
				let sendMessage = createSendMessage(messageObj.data);
				chatBody.append(sendMessage);

				// Send to WebSocket server
				sendSocketMessage(messageObj);
			}
		});
	};

	/**
	 * Handle render select element following chat type
	 */
	FUNCT.selectChatTypeEvent = () => {
		selectChatType.addEventListener("change", function (e) {
				let value = e.target.value;
				if (value === "private") {
					selectPrivate.classList.remove("d-none");
					selectGroup.parentElement.classList.add("d-none");
				} else if (value === "group") {
					selectGroup.parentElement.classList.remove("d-none");
					selectPrivate.classList.add("d-none");
				}
		});
	};

	/**
	 * Handle userlist to add group
	 */
	FUNCT.renderUserListEvent = () => {
		document.addEventListener("click", async function (e) {
			if (e.target.closest(".select-member")) {
				if (receivers.length == 0) {
					let response = await getApi("user/getReceivers");
					// Only load once
					if (response.code !== "0") return;

					receivers = response.data.receivers;
				}

				// render if dropdown had not render before
				if (!dropdown.children.length) {
					receivers.forEach((receiver) => {
						let dropdownItem = createUserDropdownItem(receiver);
						dropdown.append(dropdownItem);
					});
				}

				// toggle dropdown
				toggleDropdown(dropdown);

			} else if (!e.target.matches(".select-memeber-dropdown")) {
				closeDropdown(dropdown);
			}
		});
	};

	/**
	 * Handle choosenUserEvent
	 */
	FUNCT.ChoosenUserEvent = () => {
		document.addEventListener("change", function (e) {
			if (e.target.matches('input[name="group_member[]"]')) {
				let _this = e.target;
				let userId = _this.value;

				let userName = _this.nextElementSibling.textContent;

				let userInfor = {
					id: userId,
					name: userName,
				};

				if (_this.checked) {
					choosenUsers.push(userInfor);
				} else {
					choosenUsers = choosenUsers.filter(
						(user) => user.id != userId
					);
				}

				FUNCT.renderChoosenUsers();
			}
		});
	};

	/**
	 * Handle render choosen users
	 */
	FUNCT.renderChoosenUsers = () => {
		let content;

		if (!choosenUsers.length) {
			content = "Choose member...";
		} else {
			let userName = choosenUsers.map((user) => user.name);
			content = userName.join(", ");
		}

		choosenUserEl.textContent = content;
	};

	/**
	 * Handle clear modal input
	 */
	FUNCT.ClearChoosenUsers = () => {
		let userCheckBoxs = document.querySelectorAll(
			'input[name="group_member[]"]'
		);

		// clear cache choosen users
		choosenUsers = [];

		// clear group name input
		groupNameInput.value = "";

		errorEl.textContent = "";

		userCheckBoxs.forEach((checkbox) => {
			checkbox.checked = false;
		});

		FUNCT.renderChoosenUsers();
	};

	/**
	 * Handle create group event
	 */
	FUNCT.createGroupEvent = () => {
		document.addEventListener("click", async function (e) {
			if (e.target.matches(".createGroup")) {
				if (!groupNameInput.value.length) {
					let message = "Please enter group name.";

					renderError(errorEl, message);
					return;
				}

				let payload = {
					groupName: groupNameInput.value,
					memberIds: choosenUsers.map((user) => user.id),
				};

				let response = await postApi("groupchat/create", payload);

				if (response.code && response.code == errorCode.groupChat0001) {
					renderError(errorEl, response.message);
					return;
				}

				// clear all data modal if created successfully
				FUNCT.ClearChoosenUsers();

				// alert if created successfully
				if (response.data.id && response.message.length) {
					// render success message
					renderSuccess(errorEl, response.message);

					// reset select group data to fetch new data from db
					selectGroup
						.querySelectorAll("option:not([disabled])")
						.forEach((option) => option.remove());
					groups = [];
				}
			}
		});
	};

	/**
	 * Handle render group event
	 */
	FUNCT.renderGroupListEvent = () => {
		selectGroup.addEventListener("click", async function (e) {
			if (groups.length == 0) {
				let response = await getApi("groupchat/getGroups");
				// Only load once
				if (response.code !== "0") return;

				groups = response.data.groups;
			}

			// render if select had not render before
			if (selectGroup.children.length > 1) return;

			// Populate <select> with options
			groups.forEach((group) => {
				let option = createGroupOption(group);

				selectGroup.append(option);
			});
		});
	};

	/**
	 * Initialize events on page load
	 */
	document.addEventListener("DOMContentLoaded", function () {
		FUNCT.sendMessageEvent();
		FUNCT.renderReceivers();
		FUNCT.renderMessageByReceiver();
		FUNCT.selectChatTypeEvent();
		FUNCT.renderUserListEvent();
		FUNCT.ChoosenUserEvent();
		FUNCT.createGroupEvent();
		FUNCT.renderGroupListEvent();
	});
})();
