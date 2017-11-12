<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title><?=(!empty($pageTitle) ? $pageTitle . ' &dash; ' : '');?> TG Book Store</title>

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
	<link rel="stylesheet" href="/vendors/phc/src/resources/css/components.css">
	<link rel="stylesheet" href="/resources/css/style.css">

	<?php
		if(!empty($styles)) {
			foreach ($styles as $style){
				?><link rel="stylesheet" href="<?=$style?>"><?php
			}
		}
	?>
</head>

<body>
