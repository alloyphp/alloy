<ul class="app_treeview">
  <?php
  if(isset($itemData)):
    foreach($itemData as $item):
    ?>
    <li class="app_treeview_item">
        <?php echo $itemCallback($item); ?>
        <?php
        // Item children (hierarchy)
        if(isset($itemChildrenCallback)):
          $children = $itemChildrenCallback($item);
          if($children):
            // Display treeview recursively
            $sub = clone $view;
            $sub->data($children);
            echo $sub->content();
          endif;
        endif;
        ?>
    </li>
    <?php
    endforeach;
  
  // noData display
  elseif(isset($noDataCallback)):
  ?>
  <li class="app_treeview_nodata">
    <?php echo $noDataCallback(); ?>
  </li>
  <?php endif; ?>
</ul>