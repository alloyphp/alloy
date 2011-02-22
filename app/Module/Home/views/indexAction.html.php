<?php
// Sets page title
// @see app/layouts/app.html.php
$view->head()->title('Hello from Alloy Framework!');
?>

<p><?php echo $greeting; ?>!</p>
<p><code>Find me in: <?php echo __FILE__; ?></code></p>