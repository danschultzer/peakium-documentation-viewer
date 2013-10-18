<?php

/**
 * Controller class.
 *
 * @package		Peakium Documentation Viewer
 * @author		Dan Schultzer <dan@dreamconception.com>
 * @license		http://www.opensource.org/licenses/mit-license.php
 */

abstract class Controller
{
	public $template = 'layout';
	public $title;
	public $menu_sections = array();
	public $reference;
	public $format;
	public $paths = array();

	public function __construct($paths, $config = array())
	{
		if (!empty($config)) $this->load_config($config);

		$this->paths = $paths;
		$this->title = ($this->title ? $this->title . ' | ' : ''); // Display titles as Title | Main title
		$this->title .= config()->title;
		$this->setup_sidebar_menu();
	}

	public function load_config($config)
	{
		foreach ($config as $key => $value) {
			$this->$key = $value;
		}
	}

	public function get()
	{
		switch($this->format):
			case 'markdown':
				$this->content = $this->display_markdown_documentation($this->reference);
			break;
			case 'view':
				$this->content = $this->display_view_documentation($this->reference);
				break;
			default:
				throw new \Exception(sprintf('Format "%s" is invalid.', $this->format));
		endswitch;

		return $this;
	}

	/**
	 * Render the layout
	 */
	public function send()
	{
		headers_sent() OR header('Content-Type: text/html; charset=utf-8');

		$layout = new \View($this->template);
		$layout->set((array) $this);
		print $layout;

		$layout = NULL;
	}

	public function display_view_documentation($reference)
	{
		return new \View($this->reference);
	}

	public function display_markdown_documentation($reference)
	{
		$file_extension = (isset($this->file_extension) ? $this->file_extension : '.md');

		if (count($this->paths) > 1)
		{
			$file = $this->paths[count($this->paths) - 1] . $file_extension;
			$sub_directory = implode('/', array_slice($this->paths, 1, count($this->paths) - 2)) . '/';
		}
		else
		{
			$file = (isset($this->default_file) ? $this->default_file : 'README' . $file_extension);
			$sub_directory = '';
		}
		$parser = new \Markdown('docs/' . $reference, $file, (isset($this->depth) ? $this->depth : 3), $sub_directory);
		$this->add_sub_menu($parser->menu_list);

		return $parser->html;
	}

	public function add_sub_menu($menu_list)
	{
		// Add headers to sidebar menu
		foreach ($this->menu_sections as $section_name => $section)
		{
			if (isset($section[$this->paths[0]]))
			{
				$this->menu_sections[$section_name][$this->paths[0]]->items = $menu_list;
				break;
			}
		}
	}

	public function setup_sidebar_menu()
	{
		foreach(config()->pages as $uri => $page)
			$this->add_menu_item($page->section, $page->title, $uri);
	}

	protected function add_menu_item($section, $name, $uri)
	{
		$this->menu_sections[$section][$uri] = (object) array('name' => $name, 'items' => array());
	}
}
