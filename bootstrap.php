<?php

/**
 * Boostrap file.
 *
 * @package		Peakium Documentation Viewer
 * @author		Dan Schultzer <dan@dreamconception.com>
 * @license		http://www.opensource.org/licenses/mit-license.php
 */

// Absolute path to the system folder
define('ROOT_DIR', realpath(__DIR__) . '/');

// Relative URI path
define('ROOT_URI', '/');

/**
 * Simple autoload handler.
 */
function autoload_class($class)
{
	$class_snake = camel_to_snake_case($class);
	$file = ROOT_DIR . str_replace('\\', '/', $class_snake) . '.php';

	if (!file_exists($file))
		throw new Exception(sprintf('Could not load class file "%s".', $file));

	include_once $file;
}
spl_autoload_register('autoload_class', true);

require(ROOT_DIR . 'vendor/autoload.php');

/**
 * Handle incoming HTTP requests.
 */
function handle_request($uri = null) {
	if ($uri == null) $uri = $_SERVER['REQUEST_URI'];

	// Remove relative path from URI
	if (ROOT_URI == substr($uri, 0, strlen(ROOT_URI)))
		$uri = substr($uri, strlen(ROOT_URI));

	// If URI is in fact none, switch to index
	if ($uri == null)
		$uri = 'index';

	$paths = explode('/', trim($uri, '/'));

	// Try load the controller, or revert to index
	try
	{
		// Turn this_is_a_controller to \Controller\ThisIsAController
		$controller = '\Controller\\' . snake_to_camel_case($paths[0]);
		$controller = new $controller($paths);
	}
	catch(\Exception $e)
	{
		if (isset(config()->pages->$paths[0]))
		{
			$controller = new \DocumentationController($paths, get_object_vars(config()->pages->$paths[0]));
		}
		else
		{
			$controller = new \Controller\Index($paths);
		}
	}

	// Get and send
	$controller->get()->send();
}

/**
 * Class to display standard documentation extended from default Controller.
 *
 * @package    Peakium Documentation Viewer
 * @author		Dan Schultzer <dan@dreamconception.com>
 * @license		http://www.opensource.org/licenses/mit-license.php
 */
class DocumentationController extends \Controller {}

/**
 * Convert string from CamelCase to snake_case.
 */
function camel_to_snake_case($string)
{
	return strtolower(preg_replace_callback(
		'/(^|[a-z])([A-Z])/', 
		function($matches) {
			return strlen($matches[1]) ? "$matches[1]_$matches[2]" : $matches[2];
		},
		$string 
	)); 
}

/**
 * Convert string from snake_case to CamelCase.
 */
function snake_to_camel_case($string)
{
	return preg_replace_callback(
		'/(^|[a-z]_)([a-z])/', 
		function($matches){
			return $matches[1] . strtoupper($matches[2]);
		},
		$string 
	);
}

/**
 * Handle error display.
 */
function exception(Exception $e)
{
	headers_sent() OR header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);

	$error = 'Exception: ' . $e->getMessage();
	try
	{
		$view = new \View('error');
		$view->error = $error;
		die($view);
	}
	catch(\Exception $e)
	{
		print $error;
	}
}

/**
 * Handle fatal shutdown.
 */
function fatal()
{
	if($e = error_get_last())
	{
		exception(new \ErrorException($e['message'], $e['type'], 0, $e['file'], $e['line']));
	}
}

/**
 * Handle errors
 */
function error_handler($code, $error, $file = 0, $line = 0)
{
	if ((error_reporting() & $code) === 0) return TRUE;

	$view = new \View('error');
	$view->error = $error;
	print $view;

	return TRUE;
}

/**
 * Load configuration file into object.
 */
function config()
{
	static $config = array();

	if(empty($config))
	{
		$string = file_get_contents(ROOT_DIR . 'config.json');
		$config = json_decode($string);
	}

	return $config;
}

/**
 * Simple colorizing method for CLI.
 */
function colorize($text, $color, $bold = FALSE)
{
	// Standard CLI colors
	$colors = array_flip(array(30 => 'gray', 'red', 'green', 'yellow', 'blue', 'purple', 'cyan', 'white', 'black'));

	$color_code = 0;
	if (isset($colors[$color])) $color_code = $colors[$color];

	// Escape string with color information
	return"\033[" . ($bold ? '1' : '0') . ';' . $color_code . "m$text\033[0m";
}

/**
 * Delete directory that contains files.
 *
 * @link http://stackoverflow.com/a/8688278
 */
function delete_dir($path)
{
	return is_file($path) ?
				@unlink($path) :
				array_map(__FUNCTION__, glob($path.'/*')) == @rmdir($path);
}


// Set view directory
\View::$directory = ROOT_DIR . 'view/' . (config()->{'layout-dir'} ? config()->{'layout-dir'} . '/' : 'default');

// Enable global error handling
register_shutdown_function('fatal');
set_error_handler('error_handler');