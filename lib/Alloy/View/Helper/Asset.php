<?php
namespace Alloy\View;

/**
 * Asset Helper
 * Functions useful for building HTML forms with less code
 * 
 * @package Alloy
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @link http://alloyframework.com/
 */
class Helper_Asset extends HelperAbstract
{
	/**
	 *	Stylesheet <link> tag input
	 */
	public function stylesheet($file, $media = 'screen', $extra = '')
	{
		if(strpos($file, '://') === false) {
			$file = Alloy()->config('url.assets') . 'styles/' . $file;
		}
		$tag = '<link type="text/css" href="' . $file . '" media="' . $media . '" rel="stylesheet"' . $this->listExtra($extra) . ' />';
		return $tag;
	}
	
	
	/**
	 *	Javascript <script> tag input
	 */
	public function script($file, $extra = '')
	{
		if(strpos($file, '://') === false) {
			$file = Alloy()->config('url.assets') . 'scripts/' . $file;
		}
		$tag = '<script type="text/javascript" src="' . $file . '"' . $this->listExtra($extra) . '></script>';
		return $tag;
	}
	
	
	/**
	 *	image <img> tag input
	 */
	public function image($file, $alt = '', $extra = '')
	{
		if(strpos($file, '://') === false) {
			$file = Alloy()->config('url.assets') . 'images/' . $file;
		}
		$tag = '<img src="' . $file . '" alt="' . $alt . '"' . $this->listExtra($extra) . ' />';
		return $tag;
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
