<?php
$asset = $this->helper('Asset');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Alloy Framework</title>
    <?php echo $asset->stylesheet('app.css'); ?>
</head>
<body>
    
    <?php echo $this->content; ?>
    
</body>
</html>
