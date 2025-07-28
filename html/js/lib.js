	const postApi = async (method, payload) => {
		try {
			let result = await fetch(`/api/${method}.php`, {
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

	const getApi = async (method) => {
		try {
			let result = await fetch(`/api/${method}.php`, {
				method: "GET",
			});

			if (!result.ok) return false;

			let data = await result.json();
			return data;
		} catch (err) {
			console.log(err);
		}
	};