<?php
$i = 0;
?>

<table cellpadding=="0" cellspacing="0" border="0" class="app_datagrid">
  <?php foreach($this->data as $data => $item): $i++; ?>
    <tr class="app_datagrid_row <?php echo ($i % 2) ? 'even' : 'odd'; ?>">
    <?php foreach($this->columns as $colName => $colOpts):
        $colLabel = isset($fieldOpts['title']) ? $fieldOpts['title'] : ucwords(str_replace('_', ' ', $colName));
    ?>
      <td class="app_datagrid_cell"><?php echo $colOpts['callback']($item, $this); ?></td>
    <?php endforeach; ?>
    </tr>
  <?php endforeach; ?>
</form>