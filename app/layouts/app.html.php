<?php
$asset = $this->helper('Asset');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>Alloy Framework</title>
	<?php echo $asset->stylesheet('app.css'); ?>
    <?php echo $asset->script('http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js'); ?>
</head>
<body>
    
    <?php echo $this->content; ?>

</body>
</html>
