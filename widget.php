<?php
/*
* Plugin Name:       Telegram Channel Last Post
* Description:       Get las post from a Telegram channel_username and ad a widget
* Version:           0.0.2
* Author:            Michel Romero
* Author URI:        https://github.com/studentenherz
* License:           GPL v2 or later
*/
 
include "request_helper.php";

class TelegramChannelLastPostWidget extends WP_Widget {
    function __construct() {
        parent::__construct(
            // Base ID of your widget
            'telegram_channel_last_post_widget', 
            
            // Widget name that will appear in UI
            __('Telegram Channel Last Post Widget', 'telegram_channel_last_post_widget_domain'), 
            
            // Widget description
            array( 'description' => __( 'Add last post of a public Telegram channel_username to your website', 'telegram_channel_last_post_widget_domain' ), ) 
        );
    }

    // Register and load the widget
    public static function load_telegram_channel_last_post_widget() {
        register_widget('TelegramChannelLastPostWidget');
    }

    public function form($instance) {
        if($instance && isset($instance['channel_username'])) { 
            $channel_username = $instance['channel_username'];
        }
        else {
            $channel_username = __('', 'telegram_channel_last_post_widget_domain');
        }
        
		if($instance && isset($instance['search_parameter'])) { 
            $search_parameter = $instance['search_parameter'];
        }
        else {
            $search_parameter = __('', 'telegram_channel_last_post_widget_domain');
        }
        // Admin Form
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('channel_username'); ?>"><?php _e('Channel username:'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('channel_username'); ?>" name="<?php echo $this->get_field_name('channel_username'); ?>" type="text" value="<?php echo esc_attr($channel_username); ?>" />

            <label for="<?php echo $this->get_field_id('search_parameter'); ?>"><?php _e('Search parameter:'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('search_parameter'); ?>" name="<?php echo $this->get_field_name('search_parameter'); ?>" type="text" value="<?php echo esc_attr($search_parameter); ?>" />
        </p>
        <?php 
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['channel_username'] = (!empty($new_instance['channel_username'])) ? strip_tags($new_instance['channel_username']) : '';
        $instance['search_parameter'] = (!empty($new_instance['search_parameter'])) ? strip_tags($new_instance['search_parameter']) : '';
        return $instance;
    }

    public function widget($args, $instance) {
        // Output to the front-end
        echo $args['before_widget'];
        if (!empty($instance['channel_username'])) {
			$post_link = get_last_post_link($instance['channel_username'], $instance['search_parameter']);
            echo '<script async src="https://telegram.org/js/telegram-widget.js?22" data-telegram-post="'.$post_link.' data-width="100%" data-userpic="false"></script>';
        }
        echo $args['after_widget'];
    }
}

add_action('widgets_init', array('TelegramChannelLastPostWidget', 'load_telegram_channel_last_post_widget'));