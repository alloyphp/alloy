<?php $view->head()->title('Listing Items'); ?>

<p><a href="<?php echo $kernel->url(array('module' => '{$generator.name_url}', 'action' => 'new'), 'module_action'); ?>" class="btn btn-primary">New {$generator.name}</a></p>

<?php
// Use generic datagrid table
$table = $view->generic('datagrid');

// Set data collection to use for rows
$table->data($items);

// Add each column heading and cell output callback
foreach($fields as $field => $info) {
  $table->column($kernel->formatUnderscoreWord($field), function($item) use($field, $info) {
    return $item->$field;
  });
}

// Edit/delete links
$table->column('View', function($item) use($view) {
  return $view->link('View', array('module' => '{$generator.name_url}', 'item' => $item->id), 'module_item');
});
$table->column('Edit', function($item) use($view) {
  return $view->link('Edit', array('module' => '{$generator.name_url}', 'item' => $item->id, 'action' => 'edit'), 'module_item_action');
});
$table->column('Delete', function($item) use($view) {
  return $view->link('Delete', array('module' => '{$generator.name_url}', 'item' => $item->id, 'action' => 'delete'), 'module_item_action');
});

// Output table
echo $table->content();
?>
