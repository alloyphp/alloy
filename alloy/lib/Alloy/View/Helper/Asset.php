<?php
namespace Alloy\View\Helper;

/**
 * Asset Helper
 * Functions useful for building HTML forms with less code
 * 
 * @package Alloy
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @link http://alloyframework.com/
 */
class Asset extends HelperAbstract
{
    /**
     * Stylesheet <link> tag input
     */
    public function stylesheet($file, array $options = array())
    {
        if(false === strpos($file, '://')) {
            $file = $this->kernel->config('url.assets') . 'styles/' . $file;
        }
        $tag = '<link type="text/css" href="' . $file . '" rel="stylesheet"' . $this->listExtra($options) . ' />';
        return $tag;
    }
    
    
    /**
     * Javascript <script> tag input
     */
    public function script($file, array $options = array())
    {
        if(false === strpos($file, '://')) {
            $file = $this->kernel->config('url.assets') . 'scripts/' . $file;
        }
        $tag = '<script type="text/javascript" src="' . $file . '"' . $this->listExtra($options) . '></script>';
        return $tag;
    }
    
    
    /**
     * Image <img> tag input
     *
     * @param string $file
     * @param array $options Array of Key => Value attributes for image tag
     */
    public function image($file, array $options = array())
    {
        if(false === strpos($file, '://')) {
            $file = $this->kernel->config('url.assets') . 'images/' . $file;
        }
        $tag = '<img src="' . $file . '"' . $this->listExtra($options) . ' />';
        return $tag;
    }
    
    
    /**
     * List extra attributes passed in
     */
    protected function listExtra(array $options)
    {
        $output = '';
        foreach($options as $key => $val) {
            if(!empty($val)) {
                $output .= ' ' . $key . '="' . $val . '"';
            }
        }
        return $output;
    }
}