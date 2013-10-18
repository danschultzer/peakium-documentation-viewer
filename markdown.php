<?php

/**
 * Markdown formatter class.
 * Extending default Markdown handling to adjust for several features:
 * 		- Add anchors for headers
 * 		- Load linked files into same view
 * 		- Handle github flavored code block
 *
 * @package		Peakium Documentation Viewer
 * @author		Dan Schultzer <dan@dreamconception.com>
 * @license		http://www.opensource.org/licenses/mit-license.php
 */

class Markdown extends \Michelf\MarkdownExtra
{
	public $menu_list = array();
	public $additional_pages = array();
	public $html;

	protected $level;
	protected $max_levels;
	protected $sub_directory;
	protected $_current_level_header = array();
	protected static $_used_anchors = array();
	protected static $_pages_loaded = array();

	public function __construct($markdown_directory, $file = 'README.md', $max_levels = 3, $sub_directory = '', $level = 1)
	{
		// Defined values
		$this->level = $level;
		$this->max_levels = $max_levels;
		$this->sub_directory = $sub_directory;

		// Setup PHP Markdown, and do the transform
		$return = parent::__construct();
		$file = ROOT_DIR . $markdown_directory . '/' . $this->sub_directory . $file;
		$markdown = file_get_contents($file);
		$this->html = $this->transform($markdown);

		// Maximum depth, we should not go further
		if ($this->level >= $this->max_levels) return $return;

		// Add additional pages to this page
		$level++;
		foreach ($this->additional_pages as $anchor => $uri)
		{
			// Get directory and filename
			$pos = strrpos($uri, '/'); // Last directory separator
			$additional_page_file_name = ($pos ? substr($uri, $pos + 1) : $uri);
			$additional_page_directory = ($pos ? substr($uri, 0, $pos + 1) : '');

			// If this is not an absolute path, add sub_directory path
			if ($uri{0} != '/')
				$additional_page_directory = $this->sub_directory . $additional_page_directory;

			// Make sure we only load page once
			if (isset(self::$_pages_loaded[$additional_page_directory . $additional_page_file_name])) continue;
			self::$_pages_loaded[$additional_page_directory . $additional_page_file_name] = true;


			// Parse additional page
			if ($parser = new \Markdown($markdown_directory, $additional_page_file_name, $max_levels, $additional_page_directory, $level))
			{
				// Add anchor to header
				$this->html .= $this->html_anchor($anchor);

				// Add additional page HTML
				$this->html .= $parser->html;

				// Add menu list
				$this->menu_list = array_merge($this->menu_list, $parser->menu_list);
			}
		}

		return $return;
	}

	protected function _doHeaders_callback_setext($matches) {
		$text = parent::_doHeaders_callback_setext($matches);
		return $this->parse_header($text, $matches[1], ($matches[3]{0} == '=' ? 1 : 2));
	}

	protected function _doHeaders_callback_atx($matches) {
		$text = parent::_doHeaders_callback_atx($matches);
		return $this->parse_header($text, $matches[2], strlen($matches[1]));
	}

	protected function parse_header($text, $title, $level = 1) {
		$anchor = $this->make_unique_anchor($this->convert_header_to_anchor($title));
		$text = trim($text);

		if ($level < 3)
		{
			$this->add_header_to_menu('#' . $anchor, $title, $level);
			$text = $this->html_anchor($anchor) . $text;
		}

		if ($level == 1)
			$text .= '<hr/>';

		return PHP_EOL . $text . PHP_EOL . PHP_EOL;
	}

	protected function convert_header_to_anchor($string) {
		return preg_replace('/[^a-z0-9_]/i', '', strtolower(str_replace(' ', '_', $string)));
	}

	protected function make_unique_anchor($anchor)
	{
		// If subdirectory is defined add it to the front of the anchor
		$anchor = ($this->sub_directory ? $this->convert_uri_to_anchor($this->sub_directory) . '_' : '') . $anchor;

		$tries = 0;
		do {
			// If anchor doesn't exist, return it
			if (!isset(self::$_used_anchors[$anchor]))
			{
				self::$_used_anchors[$anchor] = true;
				return $anchor;
			}

			// Add a integer to it
			$i = substr($anchor, -1);
			if (is_numeric($i))
			{
				$anchor = substr($anchor, 0, strlen($anchor) - 1);
				$i++;
			}
			else
			{
				$i = 1;
			}

			$anchor = $anchor . $i;

		} while ($tries++ < 100); // levels
	}

	protected function add_header_to_menu($url, $header, $level, $items = array())
	{
		// Build header item
		$header_item = array($url => (object) array('name' => $header, 'items' => $items));

		$this->_current_level_header[$level] = $url;

		$parent_level = $level - 1;

		// This is the minimum level
		if ($parent_level < 1)
		{
			$this->menu_list = array_merge($this->menu_list, $header_item);
		}
		else
		{
			if (!isset($this->_current_level_header[$parent_level]))
				return false;

			$this->menu_list[$this->_current_level_header[$parent_level]]->items = array_merge($this->menu_list[$this->_current_level_header[$parent_level]]->items, $header_item);
		}
	}

	protected function html_anchor($anchor) {
		return '<a name="' . $anchor . '"></a>';
	}

	protected function _doAnchors_reference_callback($matches) {
		$matches = $this->catch_anchor($matches);
		return parent::_doAnchors_inline_callback($matches);
	}

	protected function _doAnchors_inline_callback($matches) {
		$matches = $this->catch_anchor($matches);
		return parent::_doAnchors_inline_callback($matches);
	}

	protected function catch_anchor($matches) {
		if (substr($matches[4], -3, 3) == '.md')
		{
			// Convert to anchor, catch page to be loaded
			if ($this->level < $this->max_levels)
			{
				$anchor = $this->make_unique_anchor($this->convert_uri_to_anchor($matches[4]));
				$this->additional_pages[$anchor] = $matches[4];
				$matches[4] = '#' . $anchor;
			}
			// Convert to redirect hash, removing .md
			else
			{
				$uri = ltrim(substr($matches[4], 0, strlen($matches[4]) - 3), '/');
				$matches[4] = '#redirect-' . '/' . $this->sub_directory . $uri;
			}
		}

		return $matches;
	}

	protected function convert_uri_to_anchor($uri) {
		return trim(str_replace('/', '_', str_replace('.md', '', $uri)), '_');
	}

	function doFencedCodeBlocks($text) {
		$text = parent::doFencedCodeBlocks($text);

		// Support for github flavored ```
		$less_than_tab = $this->tab_width;
		$text = preg_replace_callback('{
				(?:\n|\A)
				# 1: Opening marker
				(
					`{3,} # Marker: three backtick or more.
				)
				[ ]*
				(?:
					\.?([-_:a-zA-Z0-9]+) # 2: standalone class name
				|
					'.$this->id_class_attr_catch_re.' # 3: Extra attributes
				)?
				[ ]* \n # Whitespace and newline following marker.
				
				# 4: Content
				(
					(?>
						(?!\1 [ ]* \n)	# Not a closing marker.
						.*\n+
					)+
				)
				
				# Closing marker.
				\1 [ ]* \n
			}xm',
			array(&$this, '_doFencedCodeBlocks_callback'), $text);

		return $text;
	}

	protected function _doFencedCodeBlocks_callback($matches) {
		$text = parent::_doFencedCodeBlocks_callback($matches);
		return $text;
	}

	protected function _doCodeBlocks_callback($matches) {
		$key = parent::_doCodeBlocks_callback($matches);
		$this->html_hashes[trim($key)] = str_replace('<code>', '<code class="no-highlight">', $this->html_hashes[trim($key)]);
		return $key;
	}
}
