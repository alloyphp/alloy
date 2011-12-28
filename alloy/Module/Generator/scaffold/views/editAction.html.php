<?php $view->head()->title('Edit Item'); ?>

<?php
// Edit existing item
$form->action($kernel->url(array('module' => '{$generator.name}', 'item' => $item->id), 'module_item'))
  ->method('put');
echo $form->content();
?>