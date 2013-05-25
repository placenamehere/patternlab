<?php include_once('functions.php'); ?><!DOCTYPE html>
<html>
<head>
    <title>Style Guide</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width" />
    <link rel="stylesheet" href="<?php echo $absolutePath; ?>styleguide/css/styleguide.css" media="all" />
    <link rel="stylesheet" href="<?php echo $absolutePath; ?>css/style.css" media="all" />
	<style>
		.has-comment, .has-comment a {
			cursor: help !important;
		}
	</style>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <script src="<?php echo $absolutePath; ?>js/modernizr.js"></script>
</head>
<body>
	<?php
		$url = $_GET["url"];
		include	$patternsPath.$url;
	?>
	<script src="/styleguide/js/annotations-pattern.js"></script>
</body>
</html>