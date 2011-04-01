<table cellpadding=="0" cellspacing="0" border="0" class="app_cellgrid">
  <?php
  $cellWidthPct = floor(100 / $columns);
  $cellDataCount = count($cellData);

  if($cellData && $cellDataCount > 0):
    $ri = 0;
    $rit = 0;

    foreach($cellData as $item):
      $ri++;
      $rit++;
      
      if(1 == $ri): ?>
      <tr>
      <?php endif; ?>
        <td class="app_cellgrid_cell" width="<?php echo $cellWidthPct; ?>%">
          <?php echo $cellCallback($item); ?>
        </td>
      <?php if($columns == $ri): ?>
      </tr>
      <?php 
        $ri = 0;
      endif;
    endforeach;
  else:
  ?>
  <tr>
    <td class="app_cellgrid_nodata">
      <?php echo $noDataCallback(); ?>
    </td>
  </tr>
  <?php endif; ?>
</table>