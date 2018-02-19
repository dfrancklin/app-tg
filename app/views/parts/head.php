<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title><?=(!empty($pageTitle) ? $pageTitle . ' &dash; ' : '');?> TG Book Store</title>

	<link rel="stylesheet" href="/public/css/font-family.css">
	<link rel="stylesheet" href="/public/css/bootstrap.min.css">
	<link rel="stylesheet" href="/public/css/components.css">
	<link rel="stylesheet" href="/public/css/style.css">

	<?php
		if(!empty($styles)) {
			foreach ($styles as $style){
				?><link rel="stylesheet" href="<?=$style?>"><?php
			}
		}
	?>
</head>

<body>
