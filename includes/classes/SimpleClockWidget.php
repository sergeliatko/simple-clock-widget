<?php
/** @noinspection PhpUnused */

/**
 * Class SimpleClockWidget
 */
class SimpleClockWidget extends WP_Widget {

	/** @var  bool $enqueued_scripts */
	protected static $enqueued_scripts;

	/** @var  string $min */
	protected static $min;

	/**
	 * SimpleClockWidget constructor.
	 */
	public function __construct() {
		parent::__construct(
			'clock',
			__( 'Clock', 'simple-clock-widget' ),
			array(
				'description' => __( 'Displays local time as a widget.', 'simple-clock-widget' ),
			)
		);
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_scripts' ), 10, 0 );
	}

	/**
	 * Loads scripts on front end.
	 */
	public function maybe_enqueue_scripts() {
		if ( $this->isEmpty( self::isEnqueuedScripts() ) && is_active_widget( false, false, $this->id_base, true ) ) {
			wp_register_script(
				'simple-clock',
				SIMPLECLOCKWIDGETPATH . 'includes/jquery-clock-plugin/jqClock' . self::getMin() . '.js',
				array( 'jquery' ),
				null,
				true
			);
			wp_enqueue_script(
				'clock-widget',
				SIMPLECLOCKWIDGETPATH . 'includes/js/front' . self::getMin() . '.js',
				array( 'simple-clock' ),
				null,
				true
			);
			wp_localize_script( 'clock-widget', 'clockWidget', array(
				'language' => substr( get_bloginfo( 'language' ), 0, 2 ),
				'action'   => 'simple_clock_get_timestamp',
				'url'      => admin_url( 'admin-ajax.php' ),
			) );
			wp_add_inline_style(
				sanitize_title_with_dashes( wp_get_theme()->get( 'Name' ) ),
				'.clock-widget .clockdate+.clocktime {margin-left: 0.5em;}'
			);
			self::setEnqueuedScripts( true );
		}
	}

	/**
	 * @return bool
	 */
	public static function isEnqueuedScripts() {
		return self::$enqueued_scripts;
	}

	/**
	 * @param bool $enqueued_scripts
	 */
	public static function setEnqueuedScripts( $enqueued_scripts ) {
		self::$enqueued_scripts = $enqueued_scripts;
	}

	/**
	 * @return string
	 */
	public static function getMin() {
		if ( is_null( self::$min ) ) {
			self::setMin( ( defined( 'SCRIPT_DEBUG' ) && ( true === SCRIPT_DEBUG ) ) ? '' : '.min' );
		}

		return self::$min;
	}

	/**
	 * @param string $min
	 */
	public static function setMin( $min ) {
		self::$min = $min;
	}

	/**
	 * @param mixed $data
	 *
	 * @return bool
	 */
	protected function isEmpty( $data = null ) {
		return empty( $data );
	}

	/**
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$instance   = wp_parse_args(
			(array) $instance,
			array(
				'time_format' => get_option( 'time_format' ),
				'date_format' => get_option( 'date_format' ),
				'hide_date'   => null,
			)
		);
		$attributes = array();
		$map        = array(
			'time_format' => 'data-time-format',
			'date_format' => 'data-date-format',
			'hide_date'   => 'data-hide-date',
		);
		foreach ( $map as $key => $data ) {
			$attributes[ $data ] = isset( $instance[ $key ] ) ? $instance[ $key ] : null;
		}
		echo $args['before_widget'];
		if ( !empty( $instance['title'] ) ) {
			echo $args['before_title']
			     . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base )
			     . $args['after_title'];
		}
		/** @noinspection HtmlUnknownAttribute */
		printf(
			'<div class="%1$s" %2$s></div>',
			'clock-widget',
			$this->sprint_attributes( array_filter( $attributes ) )
		);
		echo $args['after_widget'];
	}

	/**
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$keys     = array(
			'title'       => array( 'sanitize_text_field' ),
			'time_format' => array( 'sanitize_text_field' ),
			'date_format' => array( 'sanitize_text_field' ),
			'hide_date'   => array( 'sanitize_text_field' ),
		);
		$instance = array();
		foreach ( $keys as $key => $callbacks ) {
			$instance[ $key ] = isset( $new_instance[ $key ] ) ? $this->apply_callbacks( $new_instance[ $key ] ) : '';
		}

		return $instance;
	}


	/**
	 * @param array $instance
	 *
	 * @return string
	 */
	public function form( $instance ) {
		/** @noinspection HtmlUnknownAttribute */
		echo join( '', array(
			sprintf(
				'<p><label for="%1$s">%5$s <input type="text" id="%1$s" name="%2$s" class="%3$s" value="%4$s" /></label></p>',
				$this->get_field_id( 'title' ),
				$this->get_field_name( 'title' ),
				'widefat',
				( empty( $instance['title'] ) ? '' : esc_attr( $instance['title'] ) ),
				__( 'Title', 'simple-clock-widget' )
			),
			sprintf(
				'<p><label for="%1$s">%5$s <input type="text" id="%1$s" name="%2$s" class="%3$s" value="%4$s" /></label></p>',
				$this->get_field_id( 'time_format' ),
				$this->get_field_name( 'time_format' ),
				'widefat',
				( empty( $instance['time_format'] ) ? get_option( 'time_format' ) : esc_attr( $instance['time_format'] ) ),
				__( 'Time format', 'simple-clock-widget' )
			),
			sprintf(
				'<p><label for="%1$s">%5$s <input type="text" id="%1$s" name="%2$s" class="%3$s" value="%4$s" /></label></p>',
				$this->get_field_id( 'date_format' ),
				$this->get_field_name( 'date_format' ),
				'widefat',
				( empty( $instance['date_format'] ) ? get_option( 'date_format' ) : esc_attr( $instance['date_format'] ) ),
				__( 'Date format', 'simple-clock-widget' )
			),
			sprintf(
				'<p><label for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s /> %4$s</label></p>',
				$this->get_field_id( 'hide_date' ),
				$this->get_field_name( 'hide_date' ),
				checked( true, !empty( $instance['hide_date'] ), false ),
				__( 'Do not display date', 'simple-clock-widget' )
			),
		) );

		return '';
	}

	/**
	 * @param mixed $value
	 * @param array $callbacks
	 *
	 * @return mixed
	 */
	protected function apply_callbacks( $value, array $callbacks = array() ) {
		foreach ( (array) $callbacks as $callback ) {
			$value = is_callable( $callback ) ? call_user_func( $callback, $value ) : $value;
		}

		return $value;
	}

	/**
	 * @param array $attributes
	 *
	 * @return string
	 */
	protected function sprint_attributes( $attributes = array() ) {
		$attributes = (array) $attributes;

		return join( ' ', array_map( array(
			$this,
			'sprint_attribute',
		), array_keys( $attributes ), $attributes ) );
	}

	/**
	 * @param string $attribute
	 * @param string $value
	 *
	 * @return string
	 */
	protected function sprint_attribute( $attribute, $value = '' ) {
		return sprintf(
			'%1$s="%2$s"',
			$attribute,
			esc_attr( $value )
		);
	}
}
