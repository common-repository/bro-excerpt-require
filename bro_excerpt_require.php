<?php
/*
* Plugin Name: Bro Excerpt Require
* Plugin URI: https://alkoweb.ru/bro_excerpt_require
* Author: vovasik, Petrozavodsky
* Author URI: https://alkoweb.ru
* Requires PHP: 5.4
* Version: 1.0.1
* License: GPLv2 or later
* Text Domain: bro_excerpt_require
* Description: After activation, the plugin moves the post to drafts if excerpt is empty when saving it
*/

function bro_excerpt_require_admin_notices()
{
    if (!isset($_GET['empty_excerpt'])) {
        return;
    }
    $message = __('Without an excerpt, the record can only be saved as a draft.', 'bro_excerpt_require');

    echo '<div id="notice" class="notice notice-warning is-dismissible"> <p>' . $message . '</p>';
    echo '<button class="notice-dismiss" type="button">';
    echo '<span class="screen-reader-text">' . $message . '</span>';
    echo '</button>';
    echo '</div>';
}

add_action('admin_notices', 'bro_excerpt_require_admin_notices');

function bro_excerpt_require_update_post($ID, $post)
{

    if (empty(wp_strip_all_tags($post->post_excerpt, true))) {


        $post_types = apply_filters('bro_excerpt_require_update_post__post_types', ['post']);

        $statuses = apply_filters('bro_excerpt_require_update_post__allow_statuses', ['pending', 'pending', 'auto-draft']);

        if (in_array($post->post_type, $post_types)) {

            if (!in_array($post->post_status, $statuses)) {
                remove_action('save_post', 'bro_excerpt_require_update_post', 8);


                wp_update_post(['ID' => $ID, 'post_status' => 'draft']);

                add_filter('redirect_post_location', function ($location) use ($ID) {
                    return add_query_arg(['empty_excerpt' => $ID], $location);
                }, 99);

                add_action('save_post', 'bro_excerpt_require_update_post', 8);
            }

        }

    }

}

add_action('save_post', 'bro_excerpt_require_update_post', 8, 2);


function bro_excerpt_require_load_textdomain()
{
    $mo_file_path = dirname(__FILE__) . '/lang/bro_excerpt_require-' . get_locale() . '.mo';
    load_textdomain('bro_excerpt_require', $mo_file_path);
}

add_action('plugins_loaded', 'bro_excerpt_require_load_textdomain');
