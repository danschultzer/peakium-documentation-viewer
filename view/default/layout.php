<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1,  user-scalable=no">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<title><?php echo $title; ?></title>

	<link href='http://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700' rel='stylesheet' type='text/css'>

	<link href="<?php echo ROOT_URI; ?>assets/default/css/master.css?v01" rel="stylesheet">

	<link rel="stylesheet" href="<?php echo ROOT_URI; ?>assets/default/css/code-view.css">
	<script src="<?php echo ROOT_URI; ?>assets/default/highlight.js/highlight.pack.js"></script>

	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.js"></script>
	<script type="text/javascript" src="<?php echo ROOT_URI; ?>assets/default/js/jquery-scrollspy.js"></script>

	<script type="text/javascript" src="<?php echo ROOT_URI; ?>assets/default/js/javascript.js"></script>
</head>
	
<body>

<?php
	$header = new \View('header');
	$header->uri = $paths[0];
	echo $header;
?>

	<div id="container">
	
		<div class="main-sidebar">
			<div class="box box-list">
<?php
	$build_menu = function($items, $class = "") use(&$build_menu, &$paths) {
		$text = '<ul class="' . $class . '">';
		foreach ($items as $item_uri => $item)
		{
			$text .= '<li' . ($paths[0] == $item_uri ? ' class="active"' : '') . '><a href="' . ($item_uri{0} != '#' ? ROOT_URI : '') . $item_uri . '">' . $item->name . '</a>';

			if (count($item->items))
				$text .= $build_menu($item->items, 'collapsed');

			$text .= '</li>';
		}
		$text .= '</ul>';

		return $text;
	};

	foreach ($menu_sections as $section_name => $section_items)
	{
		echo '<h4>' . $section_name . '</h4>';
		echo $build_menu($section_items);
		echo '</ul>';
	}
?>
			</div>
		</div>
		
		<div class="content">
			
<?php echo $content; ?>
			
		</div>
	
	</div>

<?php
	echo new \View('footer');
?>
</body>
</html>