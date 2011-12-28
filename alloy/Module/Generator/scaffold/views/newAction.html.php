<?php $view->head()->title('New Item'); ?>

<?php
// Create new item
$form->action($kernel->url(array('module' => '{$generator.name}'), 'module'))
  ->method('post');
echo $form->content();
?>