<?php
$asset = $view->helper('Asset');

// If page title has been set by sub-template
if($title = $view->head()->title()) {
	$title .= " - Alloy Framework";
} else {
	$title = "Alloy Framework Application";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?php echo $title; ?></title>
    <?php echo $asset->stylesheet('app.css'); ?>
</head>
<body>
    
    <?php echo $content; ?>
    
</body>
</html>