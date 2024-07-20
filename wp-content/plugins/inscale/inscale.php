<?php

/**
 * @package InScale
 */
/*
Plugin Name: InScale
Description:
Version: 1.0.0
License: GPLv2 or later
Text Domain: InScale
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

class InScalePlugin
{
	private $initialized = false;
	private $version = '1.0.0';
	private $basename;
	public $baseUrl;

	public function __construct()
	{
		$this->basename = plugin_basename(__FILE__);
		$this->baseUrl = plugins_url('', __FILE__);
		add_action('init', array($this, 'init'));
		register_activation_hook(__FILE__, array($this, 'activate'));
		register_deactivation_hook(__FILE__, array($this, 'deactivate'));
	}

	public function activate()
	{

	}

	public function deactivate()
	{

	}

	public function uninstall()
	{

	}

	public function init()
	{
		if (!$this->initialized) {
			$this->init_hooks();
		}
	}

	public function add_admin_pages()
	{
		add_menu_page('InScale Plugin', 'InScale', 'manage_options', 'inscale_plugin', array($this, 'admin_index'), '', null);
	}

	public function admin_index()
	{
		require_once __DIR__ . '/templates/admin_index.php';
	}

	public function settings_link($links)
	{
		array_push($links, '<a href="admin.php?page=inscale_plugin">Settings</a>');
		return $links;
	}

	private function init_hooks()
	{
		$this->initialized = true;
		function inscale_enqueue_js_and_css()
		{
			if (isset($_GET['page'], $_GET['action'])
				&& $_GET['page'] == 'inscale_plugin' && $_GET['action'] == 'image_combining') {
				wp_register_script(
					'inscale-imageComposer-js',
					plugins_url('/assets/js/imageComposer.js', __FILE__),
					array('jquery', 'jquery-ui-draggable')
				);
				wp_enqueue_script('inscale-imageComposer-js');
				wp_register_style(
					'inscale-imageComposer-css',
					plugins_url('/assets/css/imageComposer.css', __FILE__),
					array('jquery-ui-style')
				);
				wp_enqueue_style('inscale-imageComposer-css');
			}
		}
		
		add_action('admin_enqueue_scripts', 'inscale_enqueue_js_and_css');
		add_action('admin_menu', array($this, 'add_admin_pages'));
		add_filter("plugin_action_links_$this->basename", array($this, 'settings_link'));
	}

}

$inScalePlugin = new InScalePlugin();