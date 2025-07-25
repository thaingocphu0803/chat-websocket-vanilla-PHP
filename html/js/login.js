(function () {
	let FUNCT = {};

	FUNCT.loginEvent = () => {
		document.addEventListener("click", async function (e) {
			if (e.target.matches("button.loginBtn")) {
				let userid = document
					.querySelector('input[name="userid"]')
					.value.trim();
				let pssw = document
					.querySelector('input[name="pssw"]')
					.value.trim();

				
				if(!validateLogin(userid, pssw)) return;

				let payload = {
					userid,
					pssw,
				};

				let data = await postApi('test', payload);
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

	const postApi = async (method, payload) => {
		try {
			let result = await fetch(`/api/${method}.php`, {
				method: "POST",
				headers: {
					"Content-Type": "application/json",
				},
				body: JSON.stringify(payload),
			});

			if(!result.ok) return false;

			let data = await result.json();
			return data;
		} catch (err) {
			console.log(err);
		}
	};

	const getApi = async (method) => {
		try {
			let result = await fetch(`/api/${method}.php`, {
				method: "GET",
				headers: {
					"Content-Type": "application/json",
				},
			});

			if(!result.ok) return false;

			let data = await result.json();
			return data;
		} catch (err) {
			console.log(err);
		}
	};

	const validateLogin = (userid, psssw) => {
		let flag = true;
		let loginError = document.querySelector('.login-error');

		if(!userid.length ||!psssw.length){
			flag = false;
			let message = 'Please enter user id and password!';
			loginError.textContent = message;

			if(loginError.classList.contains('invisible')){
				loginError.classList.remove('invisible');
			}
		}else{
				loginError.classList.add('invisible');
		}

		return flag;
	}

	document.addEventListener("DOMContentLoaded", function () {
		FUNCT.loginEvent();
		FUNCT.keyUpLoginEvent();
	});
})();
