<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Test Chatting</title>
	<link rel="stylesheet" href="./css/style.css">
</head>

<body>
	<!-- login page -->
	<div id="login_page" class="wrapper">
		<div class="wrapper-title">
			<h3>Login Chat</h3>
		</div>
		<div class="wrapper-content">
			<div class="form-row">
				<label for="userid" class="label-control">User ID</label>
				<input class="input-control" type="text" name="userid" id="userid">
			</div>
			<div class="form-row">
				<label for="pssw" class="label-control">Password</label>
				<input class="input-control" type="password" name="pssw" id="pssw">
			</div>
			<span class="error-message login-error invisible">error</span>

			<button type="button" class="btn loginBtn">Login</button>
		</div>
	</div> <!-- end login page -->

	<!-- main page -->
	<div id="main_page" class="wrapper" hidden>
		<div class="wrapper-title">
			<h3>Wellcome to chatting!</h3>
			<span class="error-message invisible">error</span>
		</div>
		<div class="wrapper-content">
			<div class="chat-body">
				<?php for($i= 0; $i < 20; $i++){ ?>
				<div class="chat-message align-self-right">
					<strong class="text-orange-700">user1:</strong>
					<span>hello</span>
				</div>
				<div class="chat-message align-self-left">
				<strong class="text-blue-700">user2:</strong>
				<span>hello</span>
				</div>
				<?php } ?>
			</div>

			<div class="flex gap-10">
				<select name="" id="" class="input-control">
					<option value="" selected disabled>Choose user</option>
				</select>
				<textarea type="text" name="message" rows="3" placeholder="Enter message..."></textarea>
				<button type="button" class="btn sendBtn">send</button>
			</div>
		</div>
	</div> <!-- end main page -->

	<!-- input contains userid -->
	<input type="hidden" name="userid">

	<!-- lib.js -->
	<script src="./js/lib.js"></script>

	<!-- chat.js -->
	<script src="./js/chat.js"></script>

	<!-- login.js -->
	 <script src="./js/login.js"></script>
</body>

</html>