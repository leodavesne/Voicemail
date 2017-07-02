<?php

/*
 * A sample widget initialization
 */
class VO_Sample_Widget extends WP_Widget {

    /**
     * Register the widget
     */
    public function __construct() {
        parent::__construct(
            'vo_sample_widget',
            __("VO Sample Widget", 'vobase'),
            array( 'classname' => 'vo_widget_sample_single', 'description' => __( "Display a sample VO Widget", 'vobase' ) ),
            array( ) // you can pass width/height as parameters with values here
        );
    }

    /**
     * Output of widget
     *
     * The $args array holds a number of arguments passed to the widget
     */
    public function widget ( $args, $instance ) {
        extract( $args );

        // Get widget field values
        $title = apply_filters( 'widget_title', $instance[ 'title' ] );

        // Start sample widget body creation with output code (get arguments from options and output something)

        $out = '<p>Widget body<p>';
		$out .= '<p>Sample text: '. $instance['sample_text'] . '</p>';
		$out .= '<p>Sample dropdown: '.  $instance['sample_dropdown'] . '</p>';

        // End sample widget body creation

        if ( !empty( $out ) ) {
        	echo $before_widget;
        	if ( $title ) {
        		echo $before_title . $title . $after_title;
        	}
        	?>
        		<div>
        			<?php echo $out; ?>
        		</div>
        	<?php
        		echo $after_widget;
        }
    }

    /**
     * Updates the new instance when widget is updated in admin
     *
     * @return array $instance new instance after update
     */
    public function update ( $new_instance, $old_instance ) {
        $instance = $old_instance;

        $instance['title'] = strip_tags($new_instance['title']);
        $instance['sample_text'] = strip_tags($new_instance['sample_text']);
        $instance['sample_dropdown'] = strip_tags($new_instance['sample_dropdown']);

        return $instance;
    }

    /**
     * Widget Form
     */
    public function form ( $instance ) {
		$instance_defaults = array(
				'title' => 'Instance title',
				'sample_text' => '',
				'sample_dropdown' => '',
		);

		$instance = wp_parse_args( $instance, $instance_defaults );

        $title = esc_attr( $instance[ 'title' ] );
        $sample_text = esc_attr( $instance[ 'sample_text' ] );
        $sample_dropdown = esc_attr( $instance[ 'sample_dropdown' ] );

        ?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( "Title:", 'vobase'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('sample_text'); ?>"><?php _e( "Sample Text:", 'vobase'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('sample_text'); ?>" name="<?php echo $this->get_field_name('sample_text'); ?>" type="text" value="<?php echo $sample_text; ?>" /></p>
		<p>
			<label for="<?php echo $this->get_field_id('sample_dropdown'); ?>"><?php _e( "Areas:", 'vobase' ); ?></label>
			<select name="<?php echo $this->get_field_name('sample_dropdown'); ?>" id="<?php echo $this->get_field_id('sample_dropdown'); ?>" class="widefat">
				<option value="asia"<?php selected( $instance['sample_dropdown'], 'asia' ); ?>><?php _e( "Asia", 'vobase' ); ?></option>
				<option value="africa"<?php selected( $instance['sample_dropdown'], 'africa' ); ?>><?php _e( "Africa", 'vobase' ); ?></option>
				<option value="north_america"<?php selected( $instance['sample_dropdown'], 'north_america' ); ?>><?php _e( "North America", 'vobase' ); ?></option>
				<option value="south_america"<?php selected( $instance['sample_dropdown'], 'south_america' ); ?>><?php _e( "South America", 'vobase' ); ?></option>
				<option value="antarctica"<?php selected( $instance['sample_dropdown'], 'antarctica' ); ?>><?php _e( "Antarctica", 'vobase' ); ?></option>
				<option value="europe"<?php selected( $instance['sample_dropdown'], 'europe' ); ?>><?php _e( "Europe", 'vobase' ); ?></option>
				<option value="australia"<?php selected( $instance['sample_dropdown'], 'australia' ); ?>><?php _e( "Australia", 'vobase' ); ?></option>
			</select>
		</p>
	<?php
    }
}

// Register the widget for use
register_widget('VO_Sample_Widget');
