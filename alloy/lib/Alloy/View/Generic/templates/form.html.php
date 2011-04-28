<?php
$form = $view->helper('Form');
$action = isset($action) ? $action : '';
$method = isset($method) ? $method : 'GET';
$formMethod = strtoupper(($method == 'GET' || $method == 'POST') ? $method : 'POST');
$formMethodRest = ($formMethod == 'POST' && $method != 'POST') ? $method : false;
?>

<form action="<?php echo $action; ?>" method="<?php echo $formMethod; ?>" enctype="<?php echo $enctype; ?>">
  <div class="app_form">
  <?php if($fields && count($fields) >0): ?>
  <?php
  foreach($fields as $fieldName => $fieldOpts):
    $fieldLabel = isset($fieldOpts['title']) ? $fieldOpts['title'] : ucwords(str_replace('_', ' ', $fieldName));
    $fieldType = isset($fieldOpts['type']) ? $fieldOpts['type'] : 'string';
    ?>
    <div class="app_form_field app_form_field_<?php echo strtolower($fieldOpts['type']); ?>">
      <label><?php echo $fieldLabel; ?></label>
      <?php
      // Content that comes before the field
      echo isset($fieldOpts['before']) ? '<span class="app_form_field_before">' . $fieldOpts['before'] . '</span>' : '';
      ?>
      <span>
      <?php
      // Adjust field depending on field type
      switch($fieldType) {
        case 'text':
        case 'editor':
          $attrs = array('rows' => 10, 'cols' => 60);
          echo $form->textarea($fieldName, $view->data($fieldName), $attrs);
        break;
        
        case 'bool':
        case 'boolean':
          echo $form->checkbox($fieldName, (int) $view->data($fieldName));
        break;
        
        case 'int':
        case 'integer':
          echo $form->text($fieldName, $view->data($fieldName), array('size' => 10));
        break;
        
        case 'string':
          echo $form->text($fieldName, $view->data($fieldName), array('size' => 40));
        break;
        
        case 'select':
          $options = isset($fieldOpts['options']) ? $fieldOpts['options'] : array();
          echo $form->select($fieldName, $options, $view->data($fieldName));
        break;
        
        case 'password':
          echo $form->input('password', $fieldName, $view->data($fieldName), array('size' => 25));
        break;
        
        default:
          echo $form->input($fieldType, $fieldName, $view->data($fieldName));
      }
      ?>
      </span>
      <?php
      // Content that comes after the field
      echo isset($fieldOpts['after']) ? '<span class="app_form_field_after">' . $fieldOpts['after'] . '</span>' : '';
      ?>
    </div>
  <?php endforeach; ?>
  <?php endif; ?>
    <div class="app_form_hidden">
      <?php
      // Print out set data without fields as hidden fields in form
      $setData = $view->data();
      $setFields = $view->fields();
      $dataWithoutFields = array_diff_key($setData, $setFields);
      foreach($dataWithoutFields as $unsetField => $unsetValue):
        if(is_scalar($unsetValue)):
      ?>
        <input type="hidden" name="<?php echo $unsetField; ?>" value="<?php echo $unsetValue; ?>" />  
      <?php
        endif;
      endforeach;
      ?>
      <?php if($formMethodRest): ?>
      <input type="hidden" name="_method" value="<?php echo $formMethodRest; ?>" />
      <?php endif; ?>
    </div>
    <div class="app_form_actions">
      <button type="submit" class="app_action_primary"><?php echo $view->submit(); ?></button>
      <!--<a href="#" class="app_action_cancel">Cancel</a>-->
    </div>
  </div>
</form>