<?php
/**
 * Simple Clock Widget
 *
 * @package     SimpleClockWidget
 * @author      Serge Liatko
 * @copyright   2017 Serge Liatko
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Simple Clock Widget
 * Plugin URI:
 * Description: Adds simple widget to display local time on your site.
 * Version:     0.0.1
 * Author:      Serge Liatko
 * Author URI:  https://sergeliatko.com
 * Text Domain: simple-clock-widget
 * Domain Path: /languages
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

//do not load this file directly
defined( 'ABSPATH' ) or exit;

//define plugin path
defined( 'SIMPLECLOCKWIDGETPATH' ) or define( 'SIMPLECLOCKWIDGETPATH', plugin_dir_url( __FILE__ ) );

/**
 * Class SimpleClockWidgetPlugin
 */
class SimpleClockWidgetPlugin {

	/** @var  \SimpleClockWidgetPlugin $instance */
	protected static $instance;

	/**
	 * SimpleClockWidgetPlugin constructor.
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'load_text_domain' ), 10, 0 );
		add_action( 'widgets_init', array( $this, 'register_widget' ), 10, 0 );
		add_action( 'wp_ajax_simple_clock_get_timestamp', array(
			'SimpleClockWidgetPlugin',
			'handleAjaxRequest',
		), 10, 0 );
		add_action( 'wp_ajax_nopriv_simple_clock_get_timestamp', array(
			'SimpleClockWidgetPlugin',
			'handleAjaxRequest',
		), 10, 0 );
	}

	/**
	 * @return \SimpleClockWidgetPlugin
	 */
	public static function getInstance() {
		if ( !( self::$instance instanceof SimpleClockWidgetPlugin ) ) {
			self::setInstance( new self() );
		}

		return self::$instance;
	}

	/**
	 * @param \SimpleClockWidgetPlugin $instance
	 */
	public static function setInstance( $instance ) {
		self::$instance = $instance;
	}

	/**
	 * Send current timestamp as json response.
	 */
	public static function handleAjaxRequest() {
		wp_send_json_success( current_time( 'timestamp' ), 200 );
	}

	/**
	 * Loads plugin text domain.
	 */
	public function load_text_domain() {
		load_plugin_textdomain( 'simple-clock-widget', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Registers widget in WordPress.
	 */
	public function register_widget() {
		require_once( dirname( __FILE__ ) . '/includes/classes/SimpleClockWidget.php' );
		register_widget( 'SimpleClockWidget' );
	}
}

add_action( 'plugins_loaded', array( 'SimpleClockWidgetPlugin', 'getInstance' ), 10, 0 );
