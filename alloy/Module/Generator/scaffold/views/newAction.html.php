<h2>New Item</h2>

<?php
// Create new item
$form->action($kernel->url(array('module' => '{$generator.name}'), 'module'))
  ->method('post');
echo $form->content();
?>