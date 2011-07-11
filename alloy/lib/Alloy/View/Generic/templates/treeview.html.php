<?php
$currentLevel = $view::$_level;

// Check if we're okay to display against min level set
$levelMinCheck = (!$levelMin || $currentLevel >= $levelMin);

// Check if we're okay to display against max level set
$levelMaxCheck = (!$levelMax || $currentLevel <= $levelMax);


if($levelMaxCheck):

  $setDisplay = false;
  if(!$levelMin || ($levelMin && $currentLevel != $levelMin)):
    $setDisplay = true;
    echo "\n" . str_repeat("\t", $currentLevel) . $beforeItemSetCallback();
  endif;

  if(isset($itemData)):
    foreach($itemData as $item):

      // Check filter if set
      if(isset($itemFilter)):
        $filterResult = $itemFilter($item);
        // Skip current item on boolean false filter result
        if(false === $filterResult):
          continue;
        endif;
      endif;

      if($levelMinCheck):
        // Item before
        echo str_repeat("\t", $currentLevel) . $beforeItemCallback($item);

        // Item content
        echo str_repeat("\t", $currentLevel+1) . $itemCallback($item) . "\n";
      endif;

      // Ensure we can go to next level
      // Don't show children if current level is equal to max
      if($levelMaxCheck && $currentLevel != $levelMax):
        // Item children (hierarchy)
        if(isset($itemChildrenCallback)):
          $children = $itemChildrenCallback($item);
          if($children):
            // Increment current level
            $view::$_level++;

            // Display treeview recursively
            $sub = clone $view;
            $sub->data($children);
            echo $sub->content();

            // Reset level for remaining items
            $view::$_level = $currentLevel;
          endif;
        endif;
      endif;

      if($levelMinCheck):
        // Item after
        echo str_repeat("\t", $currentLevel) . $afterItemCallback($item);
      endif;
    endforeach;
  
  // noData display
  elseif(isset($noDataCallback)):
    str_repeat("\t", $currentLevel) . $noDataCallback() . "\n";
  endif;

  if($setDisplay):
    echo str_repeat("\t", $currentLevel) . $afterItemSetCallback() . "\n";
  endif;
endif;
