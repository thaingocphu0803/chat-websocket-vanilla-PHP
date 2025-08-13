(function () {
	'use strict';
	
	let FUNCT = {};

	// cache error
	let loginError = document.querySelector(".login-error");


	/**
	 * Toggle between login page and main page based on login state
	 */
	FUNCT.renderPagebyLoginState = (userid, username) => {
		let loginPage = document.getElementById("login_page");
		let mainPage = document.getElementById("main_page");
		let currentUserid = document.querySelector('input[name="current_userid"]');
		let currentUsername = document.querySelector('input[name="current_username"]');

		
		if (typeof userid !== 'undefined' && parseInt(userid) !== 0) {
			
			// Logged in: hide login page and show main page
			loginPage.hidden = true;
			mainPage.hidden = false;

			// Set current user info
			currentUserid.value = userid;
			currentUsername.value = username;
			
			// Connect to WebSocket server
			connectChatSocket(userid)
		} else {
			// Not logged in: show login page
			loginPage.hidden = false;
			mainPage.hidden = true;
		}
	};

	/**
	 * Check current login state from the server
	 */
	FUNCT.checkLoginState = async () => {
		let response = await getApi("auth/getLoginState");
		
		if(response.code !== "0") return;

		let userId = response.data.userid;
		let username = response.data.name;

		// Render page according to login state
		FUNCT.renderPagebyLoginState(userId, username);
	};

	/**
	 * Listen to login button click and perform login
	 */
	FUNCT.loginEvent = () => {
		document.addEventListener("click", async function (e) {
			if (e.target.matches("button.loginBtn")) {
				let userid = document
					.querySelector('input[name="userid"]')
					.value.trim();
				let pssw = document
					.querySelector('input[name="pssw"]')
					.value.trim();

				// Validate login input
				if (!FUNCT.validateLogin(userid, pssw)) return;

				let payload = {
					userid,
					pssw,
				};

				// Send login request
				let response = await postApi("auth/login", payload);

				// Handle login failure
				if (response.code && (response.code == errorCode.auth0001 || response.code ==  errorCode.auth0002)) {
					let message = response.message;

					renderError(loginError,message);

					return;
				}

				// Login success: render main page
				let userId = response.data.userid;
				let username = response.data.name;
				FUNCT.renderPagebyLoginState(userId, username);

			}
		});
	};

	/**
	 * Validate login input fields
	 */
	FUNCT.validateLogin = (userid, psssw) => {
		let flag = true;

		// Must enter both userid and password
		if (!userid.length || !psssw.length) {
			flag = false;
			let message = "Please enter user id and password!";
			renderError(loginError,message);
		}

		return flag;
	};

	/**
	 * Initialize events on page load
	 */
	document.addEventListener("DOMContentLoaded", function () {
		FUNCT.loginEvent();
		FUNCT.checkLoginState();
	});
})();
