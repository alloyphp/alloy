<?php $view->head()->title('View Item'); ?>

<?php foreach($item->data() as $field => $value): ?>
  <p>
    <b><?php echo $field; ?></b>: <?php echo $value; ?>
  </p>
<?php endforeach; ?>

<?php echo $view->link('&lt; Listing', array('module' => '{$generator.name_url}'), 'module', array('class' => 'btn')); ?>