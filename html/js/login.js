(function () {
	'use strict';
	
	let FUNCT = {};

	FUNCT.renderLoginError = (message) => {
		let loginError = document.querySelector(".login-error");

		loginError.textContent = message;

		if (loginError.classList.contains("invisible")) {
			loginError.classList.remove("invisible");
		}
	};

	FUNCT.renderPagebyState = (userid) => {
		let loginPage = document.getElementById("login_page");
		let mainPage = document.getElementById("main_page");
		let useridInput = document.querySelector('input[name="userid"]');
		
		if (typeof userid !== 'undefined' && parseInt(userid) !== 0) {
			loginPage.hidden = true;
			mainPage.hidden = false;
			useridInput.value = userid;
		} else {
			loginPage.hidden = false;
			mainPage.hidden = true;
		}
	};

	FUNCT.checkLoginState = async () => {
		let response = await getApi("check_login_state_api");
		let userId = response.data.userid;
		FUNCT.renderPagebyState(userId);
	};

	FUNCT.loginEvent = () => {
		document.addEventListener("click", async function (e) {
			if (e.target.matches("button.loginBtn")) {
				let userid = document
					.querySelector('input[name="userid"]')
					.value.trim();
				let pssw = document
					.querySelector('input[name="pssw"]')
					.value.trim();

				if (!FUNCT.validateLogin(userid, pssw)) return;

				let payload = {
					userid,
					pssw,
				};

				let response = await postApi("login_api", payload);

				if (response.code && response.code !== "0") {
					let message;
					if (response.code == "api-0001") {
						message = response.message;
					} else {
						message = "Login failed.";
					}

					FUNCT.renderLoginError(message);

					return;
				}

				let userId = response.data.userId;
				FUNCT.renderPagebyState(userId);

			}
		});
	};

	FUNCT.keyUpLoginEvent = () => {
		document.addEventListener("keyup", function (e) {
			if (e.key === "Enter") {
				FUNCT.loginEvent();
			}
		});
	};

	FUNCT.validateLogin = (userid, psssw) => {
		let flag = true;

		if (!userid.length || !psssw.length) {
			flag = false;
			let message = "Please enter user id and password!";
			FUNCT.renderLoginError(message);
		}

		return flag;
	};

	document.addEventListener("DOMContentLoaded", function () {
		FUNCT.loginEvent();
		FUNCT.keyUpLoginEvent();
		FUNCT.checkLoginState();
	});
})();
