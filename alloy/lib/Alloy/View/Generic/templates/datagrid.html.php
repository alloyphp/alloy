<?php
$i = 0;
$ci = 0;
?>

<table cellpadding=="0" cellspacing="0" border="0" class="app_datagrid table table-striped">
  <thead>
    <tr>
      <?php foreach($columns as $colName => $colOpts): $ci++; ?>
      <th><?php echo $colName; ?></th>
      <?php endforeach; ?>
    </tr>
  </thead>
  <tbody>
  <?php foreach($columnData as $data => $item): $i++; ?>
    <tr class="app_datagrid_row <?php echo ($i % 2) ? 'even' : 'odd'; ?>">
    <?php foreach($columns as $colName => $colOpts):
        $colLabel = isset($fieldOpts['title']) ? $fieldOpts['title'] : ucwords(str_replace('_', ' ', $colName));
    ?>
      <td class="app_datagrid_cell"><?php echo $colOpts['callback']($item); ?></td>
    <?php endforeach; ?>
    </tr>
  <?php endforeach; ?>
  <?php
  // No data was displayed, counter still at '0', and there is a 'noData' callback
  if(0 === $i && isset($noDataCallback)):
  ?>
    <tr>
      <td colspan="<?php echo $ci; ?>" class="app_datagrid_nodata">
        <?php echo $noDataCallback(); ?>
      </td>
    </tr>
  <?php endif; ?>
  </tbody>
</table>