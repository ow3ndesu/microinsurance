<?php
	session_start();
	require_once("../database/constants.php");
	if ($_SERVER['REDIRECT_STATUS'] == 403) {
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Unautorized Access</title>
	<link rel="stylesheet" href="<?php echo APPCSS; ?>" />
	<link rel="stylesheet" href="<?php echo ERRORCSS; ?>" />
	<link rel="shortcut icon" href="<?php echo ICONSVG; ?>" type="image/x-icon" />
	<link rel="shortcut icon" href="<?php echo ICONPNG; ?>" type="image/png" />
</head>

<body>
	<div id="error">
		<div class="error-page container">
			<div class="col-md-8 col-12 offset-md-2">
				<div class="text-center">
					<img class="img-error" src="<?php echo ERROR403IMG; ?>" alt="Not Found" />
					<h1 class="error-title">Forbidden</h1>
					<p class="fs-5 text-gray-600">
						You are unauthorized to see this page.
					</p>
					<a href="<?php echo (isset($_SESSION['MGNSVN03M10Z174U'])) ? ADMIN : INDEX; ?>" class="btn btn-lg btn-outline-primary mt-3">Go Home</a>
				</div>
			</div>
		</div>
	</div>
</body>

</html>

<?php 
	} else {
		require_once("../database/constants.php");
		echo '<script> window.location.href = \'' . INDEX . '\'; </script>';
	}
?>