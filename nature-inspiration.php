<?php
  /*
  Plugin Name: Nature Inspiration
  Plugin URI: http://github.com/Shanarelle/nature-inspiration
  Description: Accepts a set of likes and searches 500px for an image in some
    of those likes - returning a hopefully inspirational picture
  Version: 0.0.1
  Author: Clare Reid
  Author URI: https://github.com/Shanarelle
  */

  /**
   * Copyright (c) `date "+%Y"` . All rights reserved.
   *
   * Released under the GPL license
   * http://www.opensource.org/licenses/gpl-license.php
   *
   * This is an add-on for WordPress
   * http://wordpress.org/
   *
   * **********************************************************************
   * This program is free software; you can redistribute it and/or modify
   * it under the terms of the GNU General Public License as published by
   * the Free Software Foundation; either version 2 of the License, or
   * (at your option) any later version.
   *
   * This program is distributed in the hope that it will be useful,
   * but WITHOUT ANY WARRANTY; without even the implied warranty of
   * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   * GNU General Public License for more details.
   * **********************************************************************
   */

// Creating the widget
class insp_widget extends WP_Widget {

    function __construct() {
    parent::__construct(
        // Base ID of your widget
        'insp_widget',

        // Widget name will appear in UI
        __('Inspiration Widget', 'wpb_widget_domain'),

        // Widget description
        array( 'description' => __( 'An inspirational widget of amazingness', 'wpb_widget_domain' ), )
        );
    }

    // Creating widget front-end
    // This is where the action happens
    public function widget( $args, $instance ) {
        wp_enqueue_style( 'styling', plugins_url() . '/nature-inspiration/nat-insp-styles.css' );
        wp_enqueue_script( 'popup', plugins_url() . '/nature-inspiration/popup.js', array( 'jquery' ) );
        $title = apply_filters( 'widget_title', $instance['title'] );
        // before and after widget arguments are defined by themes
        echo $args['before_widget'];
        if ( ! empty( $title ) )
        echo $args['before_title'] . $title . $args['after_title'];

        $checkedItems = $this->create_data_options( $instance );
        $this->display_widget( $checkedItems, $instance['api_key'] );
        $this->create_popup();

        echo $args['after_widget'];
    }

    /* display the actual widget contents */
    public function display_widget( $checkedItems, $key ) {
        echo __( 'Click the button for a dose of inspiration', 'wpb_widget_domain' );
        ?>
        <br>
        <!-- figure out what styling to use here - bootstrap seemingly not present -->
        <input type="submit" name="search500" id="widget-insp_widget" class="button button-primary widget-control-save right" value="Search" data-options=<?php echo $checkedItems ?> data-key=<?php echo $key ?>>
        <?php
    }

    /* prints the html for the popup box - hidden by default */
    public function create_popup() {
        ?>
        <div id="nature_box">
            <div>
                <p class="attribution"></p>
                <img class="image_holder" src="">
                <p class="description"></p>
                <input type="button" class="button close" value="Close">
            </div>
        </div>
        <?php
    }

    /*  Loads the file of synonyms and figures out which ones to add based on which checkboxes
    *   have been selected in the admin screen. Then formats them as a comma separated list
    *   with a trailing comma.
    */
    public function create_data_options( $instance ) {
        $synonymsAsString = file_get_contents( plugins_url() . '/nature-inspiration/synonyms.txt' );
        $synonymMap = json_decode( $synonymsAsString, true );
        $checkedItems = "";
        foreach ( $instance as $item => $val ) {
            if ( $val == '1' ) {
                // Add overall tag to list
                $checkedItems = $checkedItems . $item . ',';
                // Add each synonym to the list
                foreach ( $synonymMap[$item] as $index => $value) {
                    $checkedItems = $checkedItems . $value . ',';
                }
            }
        }
        return $checkedItems;
    }

    /*  Widget admin section. Allows you to set a title and select a set of checkbox
    *   based preferences.
    */
    public function form( $instance ) {
        //sort out which variables have been set - default checkboxes to unchecked
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = __( 'New title', 'wpb_widget_domain' );
        }
        if ( isset( $instance[ 'people' ] ) ) {
            $people = $instance[ 'people' ];
        }
        else {
            $people = '0';
        }
        if ( isset( $instance[ 'cities' ] ) ) {
            $cities = $instance[ 'cities' ];
        }
        else {
            $cities = '0';
        }
        if ( isset( $instance[ 'animals' ] ) ) {
            $animals = $instance[ 'animals' ];
        }
        else {
            $animals = '0';
        }
        if ( isset( $instance[ 'api_key' ] ) ) {
            $api_key = $instance[ 'api_key' ];
        }
        else {
            $api_key = '';
        }
        // Widget admin form
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
            <br>
            <br>
            <!-- checkboxes -->
            <input id="<?php echo $this->get_field_id('people'); ?>" name="<?php echo $this->get_field_name('people'); ?>" type="checkbox" value="1" <?php checked( '1', $people ); ?> />
            <label for="<?php echo $this->get_field_id('people'); ?>"><?php _e('People', 'wpb_widget_domain'); ?></label>

            <input id="<?php echo $this->get_field_id('cities'); ?>" name="<?php echo $this->get_field_name('cities'); ?>" type="checkbox" value="1" <?php checked( '1', $cities ); ?> />
            <label for="<?php echo $this->get_field_id('cities'); ?>"><?php _e('Cities', 'wpb_widget_domain'); ?></label>

            <input id="<?php echo $this->get_field_id('animals'); ?>" name="<?php echo $this->get_field_name('animals'); ?>" type="checkbox" value="1" <?php checked( '1', $animals ); ?> />
            <label for="<?php echo $this->get_field_id('animals'); ?>"><?php _e('Animals', 'wpb_widget_domain'); ?></label>
            <br>
            <br>
            <!-- input for 500px api key -->
            <label for="<?php echo $this->get_field_id( 'api_key' ); ?>"><?php _e( '500px API key:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'api_key' ); ?>" name="<?php echo $this->get_field_name( 'api_key' ); ?>" type="text" value="<?php echo esc_attr( $api_key ); ?>" />
        </p>
        <?php
    }

    /* Updating widget - replacing old instances with new */
    public function update( $new_instance, $old_instance ) {
        $instance = array(
            'title' => ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '',
            'people' => ( ! empty( $new_instance['people'] ) ) ? strip_tags( $new_instance['people'] ) : '',
            'cities' => ( ! empty( $new_instance['cities'] ) ) ? strip_tags( $new_instance['cities'] ) : '',
            'animals' => ( ! empty( $new_instance['animals'] ) ) ? strip_tags( $new_instance['animals'] ) : '',
            'api_key' => ( ! empty( $new_instance['api_key'] ) ) ? strip_tags( $new_instance['api_key'] ) : '',
        );

        return $instance;
    }
} // Class insp_widget ends here

// Register and load the widget
function wpb_load_widget() {
	register_widget( 'insp_widget' );
}

add_action( 'widgets_init', 'wpb_load_widget' );

?>
