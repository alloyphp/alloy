<h2>View Item</h2>

<?php foreach($item->data() as $field => $value): ?>
  <p>
    <b><?php echo $field; ?></b>: <?php echo $value; ?>
  </p>
<?php endforeach; ?>