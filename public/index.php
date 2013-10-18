<?php

/**
 * Index file.
 *
 * @package		Peakium Documentation Viewer
 * @author		Dan Schultzer <dan@dreamconception.com>
 * @license		http://www.opensource.org/licenses/mit-license.php
 */

require ('../bootstrap.php');

try
{
	handle_request();
}
catch(Exception $e)
{
	exception($e);
}