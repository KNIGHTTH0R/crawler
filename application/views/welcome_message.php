<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href=<?= base_url("assets/semantic/semantic.min.css") ?>>
	<style>
		/*.ui.items>.item {
			-webkit-box-orient: vertical;
			-webkit-box-direction: normal;
			-ms-flex-direction: column;
			flex-direction: column;
			margin: 2em 0;
		}

		.ui.items>.item>.image {
			max-width: 100%!important;
			width: auto!important;
			max-height: 250px!important;
			display: block;
			margin-left: auto;
			margin-right: auto;
		}

		.ui.items>.item>.image+.content {
			display: block;
			padding: 1.5em 0 0;
			text-align: justify;
		}*/
	</style>
	<script
		src="https://code.jquery.com/jquery-3.1.1.min.js"
		integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
		crossorigin="anonymous"></script>
	<script src=<?= base_url("assets/semantic/semantic.min.js") ?>></script>
	<script src="https://js.pusher.com/4.1/pusher.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.11/handlebars.min.js"></script>
</head>
<body>

<div class="ui text container">
	<div class="ui centered grid">
		<div class="column">

			<div class="ui celled list">
				<?php

				foreach ($data as $dat) { ?>
					<div class="item">
						<img class="ui avatar image" src="<?= $dat->img ?>">
						<div class="content">
							<div class="header"><?= $dat->title ?></div>
							<div class="description">
								<?= strip_tags(substr($dat->description, 0, 70)) ?></div>
						</div>
					</div>
					<?php
				}

				?>
			</div>
		</div>
	</div>
</div>
<script id="item-template" type="text/x-handlebars-template">
	<div class="item">
		<img class="ui avatar image" src="{{img}}">
		<div class="content">
			<div class="header">{{title}}</div>
			<div class="description">{{description}}</div>
		</div>
	</div>
</script>

<script>
	var pusher = new Pusher('2004af424b4f1fa90746', {
		cluster: 'mt1'
	});
	var channel = pusher.subscribe('my-channel');

	channel.bind('my-event', function(data) {
		console.log(data);
		var source   = document.getElementById("item-template").innerHTML;
		var template = Handlebars.compile(source);
		var $elm = template(data.message);
		$(".ui.celled.list").prepend($elm);
	});
</script>
</body>
</html>
