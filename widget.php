<?php
/*
* Plugin Name:       Telegram Channel Last Post
* Description:       Get las post from a Telegram channel_username and ad a widget
* Version:           0.0.3
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
        
        if($instance && isset($instance['accent_color'])) { 
            $accent_color = $instance['accent_color'];
        }
        else {
            $accent_color = __('', 'telegram_channel_last_post_widget_domain');
        }

        // Admin Form
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('channel_username'); ?>"><?php _e('Channel username:'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('channel_username'); ?>" name="<?php echo $this->get_field_name('channel_username'); ?>" type="text" value="<?php echo esc_attr($channel_username); ?>" />

            <label for="<?php echo $this->get_field_id('search_parameter'); ?>"><?php _e('Search parameter:'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('search_parameter'); ?>" name="<?php echo $this->get_field_name('search_parameter'); ?>" type="text" value="<?php echo esc_attr($search_parameter); ?>" />

            <label for="<?php echo $this->get_field_id('accent_color'); ?>"><?php _e('Accent color:'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('accent_color'); ?>" name="<?php echo $this->get_field_name('accent_color'); ?>" type="color" value="<?php echo esc_attr($accent_color); ?>" />
        </p>
        <?php 
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['channel_username'] = (!empty($new_instance['channel_username'])) ? strip_tags($new_instance['channel_username']) : '';
        $instance['search_parameter'] = (!empty($new_instance['search_parameter'])) ? strip_tags($new_instance['search_parameter']) : '';
        $instance['accent_color'] = (!empty($new_instance['accent_color'])) ? strip_tags($new_instance['accent_color']) : '';
        return $instance;
    }

    public function widget($args, $instance) {
            echo $args['before_widget'];

            if (!empty($instance['channel_username'])) {
                $post_link = get_last_post_link($instance['channel_username'], $instance['search_parameter']);

                $color = substr($instance['accent_color'], 1);
                $widget_content = file_get_contents('https://t.me/'.$post_link.'?embed=1&userpic=false&color='.$color);

                $widget_content = $this->removeFaviconLinks($widget_content);

                $pattern = '/url\(([\'"]https?:\/\/.*?)\)/';
                preg_match_all($pattern, $widget_content, $matches);
                $background_image_urls = $matches[1];

                $new_background_image_urls = [];
                $upload_dir = wp_upload_dir();
                foreach ($background_image_urls as $url) {
                    $url = trim($url, "'\"");
                    $image_filename = basename($url);
                    $file_extension = pathinfo($image_filename)['extension'];
                    $hashed_image_filename = md5($image_filename).$file_extension;

                    $image_path = $upload_dir['path'] . '/' .$hashed_image_filename;

                    if (!file_exists($image_path)) {
                        $image_data = file_get_contents($url);
                        $save_result = file_put_contents($image_path, $image_data);
                        if ($save_result === false) {
                            $error_message = "Error saving image: $hashed_image_filename";
                            error_log($error_message);
                        }
                    }

                    $new_background_image_urls[] = $upload_dir['url'] . '/' . $hashed_image_filename;
                }

                $new_widget_content = $widget_content;
                foreach ($new_background_image_urls as $index => $new_url) {
                    $new_widget_content = str_replace($background_image_urls[$index], $new_url, $new_widget_content);
                }

                echo '<div class="widget_frame_base tgme_widget body_widget_post emoji_image no_userpic nodark">'.$new_widget_content.'</div>';

            }

            echo $args['after_widget'];
    }

        private function removeFaviconLinks($html) {
            $doc = new DOMDocument();
            libxml_use_internal_errors(true); // Ignore HTML parsing errors
            $doc->loadHTML($html);
            libxml_clear_errors();
    
            $linkElements = $doc->getElementsByTagName('link');
            $linksToRemove = array();
    
            foreach ($linkElements as $link) {
                    $relValue = $link->getAttribute('rel');
                    if (strpos($relValue, 'icon') !== false) {
                            $linksToRemove[] = $link;
                    }
            }
    
            foreach ($linksToRemove as $link) {
                    $link->parentNode->removeChild($link);
            }
    
            return $doc->saveHTML();
        }
    
}

add_action('widgets_init', array('TelegramChannelLastPostWidget', 'load_telegram_channel_last_post_widget'));