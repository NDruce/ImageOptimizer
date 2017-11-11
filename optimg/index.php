<?php

include 'config.php';
include 'assets/lang/'.$config['lang'].'.php';
session_name('optimg');
session_start();

if (isset($_POST['pass']) && !isset($_SESSION['optimg'])) {
	if (empty($config['pass'])) {
		$error = $lang['nopass'];
	} else if (trim($_POST['pass']) != $config['pass']) {
		$error = $lang['wrongpass'];
	} else {
		$_SESSION['optimg'] = '';
		if (isset($_GET['logout'])) header('Location: /optimg');
	}
} else if (isset($_GET['logout']) && isset($_SESSION['optimg'])) {
	session_unset();
	session_destroy();
}

?><!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width">
	<title>NDruce Image Optimizer</title>
	<meta name="robots" content="noindex, nofollow">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" type="text/css">
	<link rel="stylesheet" href="assets/css/style.css" type="text/css">
</head>
<body>
	<?php
		if (isset($_SESSION['optimg'])) {
	?>

	<form action="optimize.php" method="POST" id="optiform">
		<div class="container margin-top">
			<div class="row">
				<div class="col-sm-6">
					<div id="console" class="console"></div>
					<div class="subconsole">
						<input type="text" name="url" class="form-control" placeholder="<?=$lang['inputurl']?>" value="<?=$lang['inputurl']?>" onfocus="if (this.value=='<?=$lang['inputurl']?>') this.value=''" onblur="if (this.value=='') this.value='<?=$lang['inputurl']?>'" autocomplete="off">
						<button type="submit" id="send" class="btn btn-success" data-text="<?=$lang['optimize']?>"><?=$lang['optimize']?></button>
					</div>
				</div>
				<div class="col-sm-6">
					<label><input type="checkbox" name="children"> <?=$lang['children']?></label>
				</div>
			</div>
		</div>
	</form>

	<?php
		} else {
	?>

	<div class="login">
		<div class="login__form">
			<?php if (isset($error)) echo '<div class="alert alert-danger" role="alert">',$error,'</div>'; ?> 
			<form action="" method="POST">
				<input type="password" name="pass" class="form-control" placeholder="<?=$lang['pass']?>">
				<br>
				<button type="submit" class="btn btn-success"><?=$lang['enter']?></button>
			</form>
		</div>
	</div>

	<?php
		}
	?>

	<div class="footer">
		<div class="container">
			<a href="https://ndruce.github.io/ImageOptimizer/">NDruce Image Optimizer</a> (c) 2017
			<?php if (isset($_SESSION['optimg'])) echo '<a href="?logout" class="pull-right">'.$lang['logout'].'</a>'; ?>
		</div>
	</div>

	<!-- IE fixes -->
	<!--[if lt IE 9]>
	<script src="assets/js/ie/html5shiv.min.js"></script>
	<script src="assets/js/ie/selectivizr.min.js"></script>
	<script src="assets/js/ie/respond.min.js"></script>
	<script src="assets/js/ie/ie.min.js"></script>
	<![endif]-->

	<!-- jQuery & Bootstrap -->
	<script src="assets/js/jquery-1.12.4.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script src="assets/js/script.js"></script><!-- Main JS -->
</body>
</html>