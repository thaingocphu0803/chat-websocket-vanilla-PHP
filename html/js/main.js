(function () {

	document.addEventListener('DOMContentLoaded', function () {
		const socket = new WebSocket('ws://192.168.0.99:8000');

		// socket connect successfully
		socket.onopen =  () => {
			console.log('WebSocket connection opened');
			let message = {
				id: 1,
				item: "phu"
			}

			sendSocketMessage(socket, message);
		};

		//recieve message from socket server
		socket.onmessage =  (event) => {
			let message = getSoketMessage(event.data);
			console.log('Message received:', message);
		};

		// close socket connection event
		socket.onclose = (event) => {
			console.log('WebSocket is closed:', event);
		};

		// socket connection event error
		socket.onerror, (event) => {
			console.error('WebSocket error:', event);
		};

		document.addEventListener('click', function(e){
			if(e.target.matches('button.sendBtn')){
				sendSocketMessage(socket, 'helo');

			}
		})
		
	});

	const sendSocketMessage = (socket, message) => {
		if (socket && socket.readyState === WebSocket.OPEN) {
			socket.send(JSON.stringify(message));
		} else {
			console.warn('WebSocket is not open. Cannot send message.');
		}
	}

	const getSoketMessage = (message) => {
		return JSON.parse(message);
	}

})();
