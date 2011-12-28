<h2>Edit Item</h2>

<?php
// Edit existing item
$form->action($kernel->url(array('module' => '{$generator.name}', 'item' => $item->id), 'module_item'))
  ->method('put');
echo $form->content();
?>