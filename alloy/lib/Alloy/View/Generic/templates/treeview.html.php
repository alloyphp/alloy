<?php echo $beforeItemSetCallback(); ?>
  <?php
  if(isset($itemData)):
    foreach($itemData as $item):
    ?>
      <?php echo $beforeItemCallback($item); ?>
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
      <?php echo $afterItemCallback($item); ?>
    <?php
    endforeach;
  
  // noData display
  elseif(isset($noDataCallback)):
    $noDataCallback();
  endif; ?>
<?php echo $afterItemSetCallback(); ?>