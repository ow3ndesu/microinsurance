<?php
session_start();
if (isset($_SESSION['MGNSVN03M10Z174U'])) { # authenticated
	echo '<script> window.location.href = \'authenticated/dashboard\'; </script>';
	return;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="assets/auth/css/style.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet" href="assets/libs/sweetalert2/sweetalert2.min.css" />

	<title>Admin Authentication</title>
</head>

<body>
	<div class="container">
		<div class="screen">
			<div class="screen__content">
				<form class="login" id="login">
					<div class="login__field">
						<i class="login__icon fas fa-user"></i>
						<input type="text" class="login__input" id="username" placeholder="Username" minlength="5" required>
					</div>
					<div class="login__field">
						<i class="login__icon fas fa-lock"></i>
						<input type="password" class="login__input" id="password" placeholder="Password" minlength="8" required>
					</div>
					<button type="submit" id="submit" class="button login__submit">
						<span class="button__text" id="middle">Authenticate Credentials</span>
						<i class="button__icon fas fa-chevron-right"></i>
					</button>
				</form>
				<div class="social-login">
					<h5>Admin Authentication</h5>
				</div>
			</div>
			<div class="screen__background">
				<span class="screen__background__shape screen__background__shape4"></span>
				<span class="screen__background__shape screen__background__shape3"></span>
				<span class="screen__background__shape screen__background__shape2"></span>
				<span class="screen__background__shape screen__background__shape1"></span>
			</div>
		</div>
	</div>

	<script src="assets/libs/sweetalert2/sweetalert2.min.js"></script>
	<script src="assets/js/auth.js"></script>

</body>

</html>