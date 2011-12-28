<h2>Delete Item</h2>

<p>Really delete this item?</p>

<?php
// Delete item
$form = $view->generic('Form');

$form->action($kernel->url(array('module' => '{$generator.name}', 'item' => $item->id), 'module_item'))
  ->method('delete')
  ->submit('Delete');
echo $form->content();
?>