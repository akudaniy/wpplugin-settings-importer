<?php

/*
Plugin Name: Settings Importer
Version: 1.2
Plugin URI: http://www.murdanieko.com/
Description: Export blog settings to be imported on other fresh WordPress installation
Author: Murdani Eko
Author URI: http://www.murdanieko.com/
Text Domain: tsi
*/

define('WF_PLUGIN_TEXTDOMAIN', 'tsi');


add_action('admin_enqueue_scripts', 'tsi_admin_script');
function tsi_admin_script() {

  if( is_admin() ) {
    wp_enqueue_script('jquery');
  }   
}


register_activation_hook( __FILE__, 'tsi_plugin_setup' );
function tsi_plugin_setup() {

}


/**
 * Option names
 */
function tsi_exported_option_names() {
  $items = array(

    // general settings
    'default_role',
    'timezone_string',
    'date_format',
    'time_format',
    'links_updated_date_format',
    'start_of_week',

    // posts settings
    'posts_per_rss',
    'posts_per_page',

    // media settings
    'thumbnail_size_w',
    'thumbnail_size_h',
    'thumbnail_crop',
    'medium_size_w',
    'medium_size_h',
    'large_size_w',
    'large_size_h',
    'uploads_use_yearmonth_folders',

    // permalink settings
    'permalink_structure',
    'category_base',
    'tag_base',
    'rewrite_rules',

    // comments settings
    'comments_notify',
    'default_comment_status',
    'comment_moderation',
    'comment_max_links',
    'comment_whitelist',
    'comment_registration',
    'close_comments_for_old_posts',
    'close_comments_days_old',
    'thread_comments',
    'thread_comments_depth',
    'page_comments',
    'comments_per_page',
    'default_comments_page',
    'comment_order',

    // tinymce advanced settings
    'tadv_version',
    'tadv_plugins',
    'tadv_options',
    'tadv_toolbars',
    'tadv_btns1',
    'tadv_btns2',
    'tadv_btns3',
    'tadv_btns4',
    'tadv_allbtns',

    );
  return $items;
}

require_once plugin_dir_path(__FILE__) . '/helper.php';
require_once plugin_dir_path(__FILE__) . '/plugin-installer.php';
require_once plugin_dir_path(__FILE__) . '/views.php';
require_once plugin_dir_path(__FILE__) . '/request-collector.php';
