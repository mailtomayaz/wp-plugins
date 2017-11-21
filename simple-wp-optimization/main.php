<?php
 /*Plugin Name: Simpe Wordpress Optimization
Plugin URI: http://datumsqure.com
Description: This plugin optimize the preformance of blog.
Version: 1.0
Author: Muhammad Ayaz
Author URI: ''
License: GPL2
*/
// Clean up wp_head
// Remove Really simple discovery link
remove_action('wp_head', 'rsd_link');
// Remove Windows Live Writer link
remove_action('wp_head', 'wlwmanifest_link');
// Remove the version number
remove_action('wp_head', 'wp_generator');
// Remove curly quotes
remove_filter('the_content', 'wptexturize');
remove_filter('comment_text', 'wptexturize');
// Allow HTML in user profiles
remove_filter('pre_user_description', 'wp_filter_kses');
// SEO
// add tags as keywords
function tags_to_keywords(){
    global $post;
    if(is_single() || is_page()){
        $tags = wp_get_post_tags($post->ID);
        foreach($tags as $tag){
            $tag_array[] = $tag->name;
        }
        $tag_string = implode(', ',$tag_array);
        if($tag_string !== ''){
            echo "<meta name='keywords' content='".$tag_string."' />\r\n";
        }
    }
}

//add tags to the head section of page
add_action('wp_head','tags_to_keywords');

// add except as description
function excerpt_to_description(){
    global $post;
    if(is_single() || is_page()){
        $all_post_content = wp_get_single_post($post->ID);
        $excerpt = substr($all_post_content->post_content, 0, 100).' [...]';
        echo "<meta name='description' content='".$excerpt."' />\r\n";
    }
    else{
        echo "<meta name='description' content='".get_bloginfo('description')."' />\r\n";
    }
}
add_action('wp_head','excerpt_to_description');

//Optimize Database
function optimize_database(){
    global $wpdb;
    $all_tables = $wpdb->get_results('SHOW TABLES',ARRAY_A);
    foreach ($all_tables as $tables){
        $table = array_values($tables);
        $wpdb->query("OPTIMIZE TABLE ".$table[0]);
    }
}

function simple_optimization_cron_on(){
    wp_schedule_event(time(), 'daily', 'optimize_database');
}

function simple_optimization_cron_off(){
    wp_clear_scheduled_hook('optimize_database');
}
register_activation_hook(__FILE__,'simple_optimization_cron_on');
register_deactivation_hook(__FILE__,'simple_optimization_cron_off');


//adding js files 

function wptuts_scripts_with_the_lot()
{
    // Register the script like this for a plugin:
    wp_register_script( 'custom-script', plugins_url( '/js/custom-script.js', __FILE__ ), array( 'jquery', 'jquery-ui-core' ), '20120208', true );
    // or
    // Register the script like this for a theme:
    wp_register_script( 'custom-script', get_template_directory_uri() . '/js/custom-script.js', array( 'jquery', 'jquery-ui-core' ), '20120208', true );
 
    // For either a plugin or a theme, you can then enqueue the script:
    wp_enqueue_script( 'custom-script' );
}
add_action( 'wp_enqueue_scripts', 'wptuts_scripts_with_the_lot' );


	
function wptuts_styles_with_the_lot()
{
    // Register the style like this for a plugin:
    wp_register_style( 'custom-style', plugins_url( '/css/custom-style.css', __FILE__ ), array(), '20120208', 'all' );
    // or
    // Register the style like this for a theme:
    wp_register_style( 'custom-style', get_template_directory_uri() . '/css/custom-style.css', array(), '20120208', 'all' );
 
    // For either a plugin or a theme, you can then enqueue the style:
    wp_enqueue_style( 'custom-style' );
}
add_action( 'wp_enqueue_scripts', 'wptuts_styles_with_the_lot' );