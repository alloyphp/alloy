<?php
/**
 * HTML Head Helper
 * Collects HTML elements that need to be added within the <head></head> tags of an HTML document
 * Used so any view can add items to the final HTML layout that gets produced
 * 
 * @package Alloy Framework
 * @link http://alloyframework.com
 */
class Alloy_View_Helper_Head extends Alloy_View_Helper
{
	protected $_assetHelper;
	protected $_styles = array();
	protected $_scripts = array();
	protected $_contentPrepend = array();
	protected $_contentAppend = array();
	
	
	/**
	 * Load required Asset helper
	 */
	public function init()
	{
		$this->_assetHelper = $this->_view->helper('Asset');
	}
	
	
	/**
	 *	Stylesheet <link> tag input
	 */
	public function stylesheet()
	{
		$args = func_get_args();
		$this->_styles[] = call_user_func_array(array($this->_assetHelper, __FUNCTION__), $args);
	}
	
	
	/**
	 *	Javascript <script> tag input
	 */
	public function script()
	{
		$args = func_get_args();
		$this->_scripts[] = call_user_func_array(array($this->_assetHelper, __FUNCTION__), $args);
	}
	
	
	/**
	 * Prepend content
	 */
	public function prepend($content)
	{
		$this->_contentPrepend[] = $content;
	}
	
	
	/**
	 * Append content
	 */
	public function append($content)
	{
		$this->_contentAppend[] = $content;
	}
	
	
	/**
	 * Return HTML content string
	 *
	 * @return string
	 */
	public function content()
	{
		$content = "";
		
		// Numeric keys with array_merge just appends items in order
		$contentItems = array_merge(
			$this->_contentPrepend,
			$this->_styles,
			$this->_scripts,
			$this->_contentAppend
			);
		
		// Format content items
		foreach($contentItems as $item) {
			$content .= "\t" . (string) $item . "\n";
		}
		
		return $content;
	}
	
	
	/**
	 * Return HTML content string
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->content();
	}
}