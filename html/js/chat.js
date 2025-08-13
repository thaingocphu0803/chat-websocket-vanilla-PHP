(function () {
	"use strict";

	let FUNCT = {};
	let partners = [];
	let choosenUsers = [];
	let groups = [];

	// Cache DOM elements
	let selectPrivate = document.querySelector('select[name="partner"]');
	let selectGroup = document.querySelector('select[name="group"]');
	let chatBody = document.getElementById("chat_body");
	let useridInput = document.querySelector('input[name="current_userid"]');
	let usernameInput = document.querySelector(
		'input[name="current_username"]'
	);

	let choosenUserEl = document.querySelector(".choosen-users");
	let dropdown = document.querySelector(".select-memeber-dropdown");
	let groupNameInput = document.querySelector('input[name="group_name"]');
	let errorEl = document.querySelector(".create-group-error");
	let selectChatType = document.querySelector('select[name="chat-type"]');

	/**
	 * Load and render partner list (lazy-load on first click)
	 */
	FUNCT.renderPartners = () => {
		selectPrivate.addEventListener("click", async function (e) {
			if (partners.length == 0) {
				let response = await getApi("user/getPartners");
				// Only load once
				if (response.code !== "0") return;

				partners = response.data.partners;
			}

			// render if select had not render before
			if (selectPrivate.children.length > 1) return;

			// Populate <select> with options
			populateSelect(selectPrivate, partners, createSelectOption);
		});
	};

	/**
	 * Render chat messages for the selected partner
	 */
	FUNCT.renderMessageByPartner = () => {
		selectPrivate.addEventListener("change", async function (e) {
			let partnerid = e.target.value;

			// If messages for this partner are not cached, fetch them
			if (typeof chatMessages[partnerid] == "undefined") {
				let payload = {
					partnerid,
					userid: useridInput.value.trim(),
				};

				let response = await postApi(
					"message/getPrivateMessage",
					payload
				);

				if (response.code !== "0") return;

				chatMessages[partnerid] = response.data.messages;
			}

			// Clear chat body before rendering
			chatBody.innerHTML = "";

			if (!chatMessages[partnerid].length) return;

			// Render messages: distinguish sent vs received
			chatMessages[partnerid].forEach((messageObject) => {
				if (messageObject.sender == partnerid) {
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
			let messageInput = document.querySelector(
				'textarea[name="message"]'
			);
			let chatType = selectChatType.value.trim();

			// get receiver id by chat type
			let receiver =
				chatType === "private"
					? selectPrivate.value.trim()
					: selectGroup.value.trim();

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
				console.log(chatMessages, messageObj);

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
	 * Toggle private/group select UI based on selected chat type
	 */
	FUNCT.selectChatTypeEvent = () => {
		selectChatType.addEventListener("change", function (e) {
			let value = e.target.value;
			if (value === "private") {
				// Show private select, hide group select
				selectPrivate.classList.remove("d-none");
				selectGroup.parentElement.classList.add("d-none");
				selectGroup.selectedIndex = 0;
			} else if (value === "group") {
				// Show group select, hide private select
				selectGroup.parentElement.classList.remove("d-none");
				selectPrivate.classList.add("d-none");
				selectPrivate.selectedIndex = 0;
			}
		});
	};

	/**
	 * Show dropdown user list when adding members to group
	 */
	FUNCT.renderDropdownUserEvent = () => {
		document.addEventListener("click", async function (e) {
			if (e.target.closest(".select-member")) {
				// Fetch partners only once
				if (partners.length == 0) {
					let response = await getApi("user/getPartners");
					// Only load once
					if (response.code !== "0") return;

					partners = response.data.partners;
				}

				// Render dropdown only if empty
				if (!dropdown.children.length) {
					populateSelect(
						dropdown,
						partners,
						createUserDropdownItem
					);
				}

				// toggle dropdown
				toggleDropdown(dropdown);
			} else if (!e.target.matches(".select-memeber-dropdown")) {
				closeDropdown(dropdown);
			}
		});
	};

	/**
	 * Add/remove selected user to group member list
	 */
	FUNCT.ChooseDropdownUserEvent = () => {
		document.addEventListener("change", function (e) {
			if (e.target.matches('input[name="group_member[]"]')) {
				let _this = e.target;
				let userId = _this.value;

				let userName = _this.nextElementSibling.textContent;

				let userInfor = {
					id: userId,
					name: userName,
				};

				// Add or remove from selected users
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
	 * Display chosen users' names or default text if none selected
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
	 * Reset group creation modal inputs and selections
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
	 * Submit group creation form
	 */
	FUNCT.createGroupEvent = () => {
		document.addEventListener("click", async function (e) {
			if (e.target.matches(".createGroup")) {
				
				// Validate group name
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

				// Show success and refresh group list
				if (response.status === "ok" && response.message.length) {
					renderSuccess(errorEl, response.message);
					
					selectGroup
						.querySelectorAll("option:not([disabled])")
						.forEach((option) => option.remove());
					
					groups = [];
				}
			}
		});
	};

	/**
	 * Load and render group list into select dropdown
	 */
	FUNCT.renderGroups = () => {
		selectGroup.addEventListener("click", async function (e) {
			// Fetch groups only once
			if (groups.length == 0) {
				let response = await getApi("groupchat/getGroups");
				// Only load once
				if (response.code !== "0") return;

				groups = response.data.groups;
			}

			// Render only if options not already loaded
			if (selectGroup.children.length > 1) return;

			// Populate <select> with options
			populateSelect(selectGroup, groups, createSelectOption);
		});
	};
	/**
	 * Load and display messages for the selected group chat
	 */
	FUNCT.renderMessageByGroupChat = () => {
		selectGroup.addEventListener("change", async function (e) {
			let groupUid = e.target.value;
			let userid = useridInput.value.trim();

			// If messages for this group are not cached, fetch them
			if (typeof chatMessages[groupUid] == "undefined") {
				let payload = {
					groupUid,
				};

				let response = await postApi(
					"message/getGroupMessage",
					payload
				);

				if (response.code !== "0") return;

				chatMessages[groupUid] = response.data.messages;
			}

			// Clear chat body before rendering
			chatBody.innerHTML = "";

			if (!chatMessages[groupUid].length) return;

			// Render messages: distinguish sent vs received
			chatMessages[groupUid].forEach((messageObject) => {
				if (messageObject.sender == userid) {
					let sendMessage = createSendMessage(messageObject);
					chatBody.append(sendMessage);
				} else {
					let receiveMessage = createReceiveMessage(messageObject);
					chatBody.append(receiveMessage);
				}
			});
		});
	};

	/**
	 * Initialize events on page load
	 */
	document.addEventListener("DOMContentLoaded", function () {
		FUNCT.sendMessageEvent();
		FUNCT.renderPartners();
		FUNCT.renderMessageByPartner();
		FUNCT.selectChatTypeEvent();
		FUNCT.renderDropdownUserEvent();
		FUNCT.ChooseDropdownUserEvent();
		FUNCT.createGroupEvent();
		FUNCT.renderGroups();
		FUNCT.renderMessageByGroupChat();
	});
})();
