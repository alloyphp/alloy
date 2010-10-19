<?php
namespace Alloy\View\Helper;

/**
 * Form Helper
 * Functions useful for building HTML forms with less code
 * 
 * @package Alloy
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @link http://alloyframework.com/
 */
class Form extends HelperAbstract
{
    /**
     * Form input element
     */
    public function input($type, $name, $value='', $extra='')
    {
        // Field type maps - for non-standard field types (and HTML5 types for today)
        $inputTypeMap = array(
            'string' => 'text',
            'int' => 'text',
            'integer' => 'text',
            'number' => 'text',
            'time' => 'text',
            'date' => 'text',
            'datetime' => 'text',
            'email' => 'text',
            'url' => 'text'
        );
        $type = (isset($inputTypeMap[$type]) ? $inputTypeMap[$type] : $type);
        $extra['id'] = isset($extra['id']) ? $extra['id'] : trim($name);
        $tag = '<input type="' . $type . '" name="' . $name . '" value="' . $value . '"' . $this->listExtra($extra) . ' />';
        return $tag;
    }
    
    /**
     * Text input
     */
    public function text($name, $value='', $extra='')
    {
        return $this->input('text', $name, $value, $extra);
    }
    
    
    /**
     * Password input
     */
    public function password($name, $value='', $extra='')
    {
        return $this->input('password', $name, '', $extra);
    }
    
    
    /**
     * Textarea input
     */
    public function textarea($name, $value='', $extra='')
    {
        $tag = '<textarea name="' . $name . '"' . $this->listExtra($extra) . '>' . $value . '</textarea>';
        return $tag;
    }
    
    
    /**
     * Selection box input (dropdown)
     */
    public function select($name, array $options, $value='', $extra='')
    {
        $blankOption = '';
        if(isset($extra['blank'])) {
            // blank selection <option>
            $blankOption = '<option value="">' . $extra['blank'] . '</option>' . "\n";
            // remove it from the attributes list
            unset($extra['blank']);
        }
        
        // Set 'id' attribute if not set already
        $extra['id'] = isset($extra['id']) ? $extra['id'] : $name;
        // Input array values will also be the keys (literal strings)
        if(isset($extra['literal']) && $extra['literal']) {
            unset($extra['literal']);
            $literal = true;
        } else {
            $literal = false;
        }
        
        // Begin <select> tag
        $tag = '<select name="' . $name . '"' . $this->listExtra($extra) . '>' . "\n";
        $tag .= $blankOption;
        
        // Loop over options
        foreach($options as $key => $val)
        {
            // Input array values will also be the keys (literal strings)
            if($literal) {
                $key = $val;
            }
            
            $selected = '';
            if(is_array($value)) {
                if(in_array($key, $value)) {
                    $selected = ' selected="selected"';
                }
            } elseif($key == $value) {
                $selected = ' selected="selected"';
            }
            // Print option
            $tag .= '<option value="' . $key . '"' . $selected . '>' . $val . '</option>' . "\n";
        }
        $tag .= '</select>';
        return $tag;
    }
    
    
    /**
     * Radio selection input
     */
    /*
    public function radio($name, array $options, $value='', $extra='')
    {
        // Set 'id' attribute if not set already
        $extra['id'] = isset($extra['id']) ? $extra['id'] : $name;
        // Input array values will also be the keys (literal strings)
        if(isset($extra['literal']) && $extra['literal']) {
            unset($extra['literal']);
            $literal = true;
        } else {
            $literal = false;
        }
        
        // Loop over options
        foreach($options as $key => $val)
        {
            // Input array values will also be the keys (literal strings)
            if($literal) {
                $key = $val;
            }
            
            $selected = '';
            if(is_array($value)) {
                if(in_array($key, $value)) {
                    $selected = ' checked="checked"';
                }
            } elseif($key == $value) {
                $selected = ' checked="checked"';
            }
            
            // Print radio input
            $tag .= '<input type="radio" name="' . $name . '" value="' . $key . '"' . $this->listExtra($extra) . '' . $selected . ' /><br />';
        }
        return $tag;
    }
    */
    
    
    /**
     * Show checkbox from element
     */
    public function checkbox($name, $value='', $extra='')
    {
        if(($value > 0 || !empty($value)) && $value) {
            $extra['checked'] = "checked";
        }
        $value = isset($extra['value']) ? $extra['value'] : 1;
        return $this->input('checkbox', $name, $value, $extra);
    }
    
    
    /**
     * List extra attributes passed in
     */
    protected function listExtra($extra)
    {
        $output = '';
        if(is_array($extra) && count($extra) > 0) {
            foreach($extra as $key => $val) {
                if(!empty($val)) {
                    $output .= ' ' . $key . '="' . $val . '"';
                }
            }
        }
        return $output;
    }
}