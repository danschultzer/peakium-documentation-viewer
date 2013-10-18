<?php

/**
 * View class slightly modified from MicroMVC/MicroMVC.
 *
 * @package		Peakium Documentation Viewer
 * @author		http://github.com/tweetmvc/tweetmvc-app
 * @author		Dan Schultzer <dan@dreamconception.com>
 * @license		http://micromvc.com/license
 */

class View
{

	public static $directory = NULL;
	private $__view = NULL;

	/**
	 * Returns a new view object for the given view.
	 *
	 * @param string $file the view file to load
	 * @param string $module name (blank for current theme)
	 */
	public function __construct($file)
	{
		$this->__view = $file;

		$path = static::$directory . $this->__view . '.php';
		if (!file_exists($path))
			throw new \Exception(sprintf('File doesn\'t exist: %s', $path));
	}


	/**
	 * Set an array of values
	 *
	 * @param array $array of values
	 */
	public function set($array)
	{
		foreach($array as $k => $v)
		{
			$this->$k = $v;
		}
	}


	/**
	 * Return the view's HTML
	 *
	 * @return string
	 */
	public function __toString()
	{
		try {
			ob_start();
			extract((array) $this);
			require static::$directory . $this->__view . '.php';
			return ob_get_clean();
		}
		catch(\Exception $e)
		{
			exception($e);
			return '';
		}
	}

}