<?php
/**
*@package neartail
**/
/** 
* Plugin Name: Neartail
* Plugin URI: https://neartail.com/website/how-to-embed-google-forms-in-wordpress.html
* Description: Take food orders for weekly changing menu
* Version: 0.0.5
* Author: Neartail
* Author URI: https://neartail.com
* License: GPL v2 or Later
* Text Domain: neartail
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

Copyright 2024 Mailrecipe LLC.
*/

defined('ABSPATH') or die('Sorry! This request is not called properly');
function_exists('add_action') or die('Sorry! This request is called outside wordpress');


// Add menu items
add_action('admin_menu', 'neartail_plugin_menu');

function neartail_plugin_menu() {
    // Add top-level menu item
    add_menu_page(
        'Neartail Plugin',
        'Neartail',
        'manage_options',
        'neartail_home',
        'neartail_home_page',
        'dashicons-forms',
        6
    );

    // Add submenu page
    add_submenu_page(
        'neartail_home',
        'About',
        'About',
        'manage_options',
        'neartail_home',
        'neartail_home_page'
    );

    add_submenu_page(
        'neartail_home',
        'Create Order Forms',
        'Create Order Forms',
        'manage_options',
        'neartail_activate',
        'neartail_activate_page'
    );
}

function neartail_home_page() {
    wp_enqueue_style('neartail_styles_bootstrap', plugins_url('assets/css/bootstrap.min.css', __FILE__), [], '1.0.0');
    wp_enqueue_style('neartail_styles_custom', plugins_url('assets/css/style.css', __FILE__), [], '1.0.0');

    wp_enqueue_script('neartail_home_script', plugins_url('assets/js/home.js', __FILE__), [], '1.0.0', true);
    wp_enqueue_script('lottie_script', plugins_url('assets/js/lottie.js', __FILE__), [], '5.7.13', true);
    include(plugin_dir_path(__FILE__) . 'templates/home.php');
}

function neartail_dashboard_page() {
    $domain = "https://neartail.com";
    $url = $domain . "/login/website.html";

    if ( isset($_GET['redirectURL']) ) {
        // Sanitize and validate the redirectURL parameter
        $redirectURL = sanitize_url($_GET['redirectURL']);

        // Ensure redirectURL only contains alphanumeric characters and slashes
        if (preg_match('/^[a-zA-Z0-9\/\-_.]+$/', $redirectURL)) {
            if (substr($redirectURL, 0, 1) === '/') {
                $redirectURL = substr($redirectURL, 1);
            }

            $url = $domain . '/' . $redirectURL;
        } else {
            $url = $domain . "/404.html";
        }
    }

    ?>
        <div class="wrap" style="height: 100vh;">
            <iframe id="myIframe" src="<?php echo esc_url($url); ?>" width="100%" height="100%" frameborder="0" marginheight="0" marginwidth="0">Loading…</iframe>
        </div>
    <?php
}

// Function to run on plugin activation
function neartail_plugin_activate() {
    // Set an option to indicate the plugin was just activated
    add_option('neartail_plugin_activated', true);
}

// Register the activation hook
register_activation_hook(__FILE__, 'neartail_plugin_activate');


// Show admin notice and redirect to custom page
function neartail_plugin_redirect() {
    if (get_option('neartail_plugin_activated', false)) {
        delete_option('neartail_plugin_activated');
        wp_safe_redirect(admin_url('admin.php?page=neartail_activate'));
        exit;
    }
}
add_action('admin_init', 'neartail_plugin_redirect');

function neartail_activate_page() {
    $pages = get_pages();
    foreach ($pages as &$page) {
        $page->edit_url = get_edit_post_link($page->ID);
    }

    $domain = "https://neartail.com";
    $url = $domain . "/wordpress/onboard.html";
    $preview_url = '';
    $admin_url = admin_url('admin.php?page=neartail_dashboard');

    $user = wp_get_current_user();
    $user_details = array(
        'email' => $user->user_email,
        'displayName' => $user->display_name,
        'siteName' => get_bloginfo('name'),
        'siteDesc' => get_bloginfo('description'),
        'siteURL' => get_site_url(),
        'homeURL' => get_home_url(),
        'emailHash' => md5($user->user_email),
        'siteHash' => md5(get_site_url())
    );

    // Sanitize and validate the input
    $page_id = isset($_GET['pageId']) ? intval(sanitize_text_field(wp_unslash($_GET['pageId']))) : 0;
    $user_id = isset($_GET['userId']) ? sanitize_text_field(wp_unslash($_GET['userId'])) : '';
    $publish_id = isset($_GET['publishId']) ? sanitize_text_field(wp_unslash($_GET['publishId'])) : '';
    $page_name = isset($_GET['pageName']) ? sanitize_text_field(wp_unslash($_GET['pageName'])) : '';

    // Validate that the IDs are numeric or alphanumeric as appropriate
    if ($page_id > 0 && $user_id && $publish_id) {
        $url = $url . "?pageId=" . esc_attr($page_id) . "&userId=" . esc_attr($user_id) . "&publishId=" . esc_attr($publish_id);
        $preview_url = get_permalink($page_id) . '?preview=true';
        neartail_emebd_wordpress_script($page_id, $user_id, $publish_id);
    } else if($page_name){
        $page_id = neartail_new_page($page_name, $user_id, $publish_id);
        $url = $url . "?pageId=" . esc_attr($page_id) . "&userId=" . esc_attr($user_id) . "&publishId=" . esc_attr($publish_id);
        $preview_url = get_permalink($page_id) . '?preview=true';
    }


    ?>
    <div class="wrap" style="height: 100vh;">
        <iframe id="myIframe" src="<?php echo esc_url($url); ?>" width="100%" height="100%" frameborder="0" marginheight="0" marginwidth="0">Loading…</iframe>
    </div>
    <?php

    // Register and enqueue the script
    wp_register_script('neartail_script', '', [], time(), true);
    wp_enqueue_script('neartail_script');

    // Add inline script
    $inline_script = "
        document.addEventListener('DOMContentLoaded', function() {
            var iframe = document.getElementById('myIframe');
            var postedMessage = false;

            function postMessageToIframe() {
                var pages = " . wp_json_encode($pages) . ";
                var preview_url = '" . esc_url($preview_url) . "';
                var user = " . wp_json_encode($user_details) . ";
                var data = {pages: pages, previewURL: preview_url, wordpressUser: user };
                // console.log('Before postinggggggg', data);
                iframe.contentWindow.postMessage(data, '" . esc_url($domain) . "');
                postedMessage = true;
                clearInterval(interval);
            }

            iframe.addEventListener('load', function() {
                if (!postedMessage) postMessageToIframe();
            });

            var interval = setInterval(function() {
                var iframe = document.getElementById('myIframe');
                if (!postedMessage && iframe) postMessageToIframe();
            }, 1000);

            window.addEventListener('message', function(event) {
                if (event.origin !== '" . esc_url($domain) . "') return; // Verify the origin
                var formData = event.data; // This will contain the form data sent from the iframe
                var admin_url = '" . esc_url($admin_url) . "';
                if (formData && formData.indexOf('pageId') > -1) {
                    var data = JSON.parse(formData);
                    var url = window.location.href;
                    var separator = url.indexOf('?') !== -1 ? '&' : '?';
                    url += separator + 'pageId=' + data.pageId + '&userId=' + data.userId + '&publishId=' + data.publishId;
                    window.location.href = url;
                } else if(formData && formData.indexOf('pageName') > -1) {
                    var data = JSON.parse(formData);
                    var url = window.location.href;
                    var separator = url.indexOf('?') !== -1 ? '&' : '?';
                    url += separator + 'pageName=' + data.pageName + '&userId=' + data.userId + '&publishId=' + data.publishId;
                    window.location.href = url;
                }   

                if (formData && formData.indexOf('redirectURL') > -1) {
                    var data = JSON.parse(formData);
                    url = admin_url + '&redirectURL=' + data.redirectURL;
                    window.location.href = url;
                }
            });
        });
    ";
    wp_add_inline_script('neartail_script', $inline_script);
}

function neartail_emebd_wordpress_script($pageId, $userId, $publishId) {
    $page = get_post($pageId);
    if ($page) {
        // Get the current post content
        $existing_content = $page->post_content;
        $embedUrl = 'https://neartail.com/include/' . $userId . '/form/' . $publishId . '/wordpress.js?div=ff-compose';

        if (strpos($existing_content, $publishId) === false) {
            $block_content = '<!-- wp:html -->';
            $block_content .= '<!-- Custom HTML block -->';

            wp_register_script('neartail_embed_script', $embedUrl, [], time(), true);
            wp_enqueue_script('neartail_embed_script');
            $script_tag = wp_get_inline_script_tag('', ['src' => $embedUrl, 'async' => true, 'defer' => true]);
            $block_content .= '<div id="ff-compose"></div>' . $script_tag;

            $block_content .= '<!-- /Custom HTML block -->';
            $block_content .= '<!-- /wp:html -->';
            $updated_content = $existing_content . "\n\n" . $block_content;
            wp_update_post([ 'ID' => $pageId, 'post_content' => $updated_content, ]);
        }
    }
}

function neartail_new_page($pageName, $userId, $publishId) {
    $page = array(
        'post_title' => $pageName,
        'post_content' => '',
        'post_status' => 'publish',
        'post_type' => 'page'
    );

    $embedUrl = 'https://neartail.com/include/' . $userId . '/form/' . $publishId . '/wordpress.js?div=ff-compose';
    $pageId = wp_insert_post($page);

    if ($pageId) {
        $block_content = '<!-- wp:html -->';
        $block_content .= '<!-- Custom HTML block -->';

        wp_register_script('neartail_embed_script', $embedUrl, [], time(), true);
        wp_enqueue_script('neartail_embed_script');
        $script_tag = wp_get_inline_script_tag('', ['src' => $embedUrl, 'async' => true, 'defer' => true]);
        $block_content .= '<div id="ff-compose"></div>' . $script_tag;

        $block_content .= '<!-- /Custom HTML block -->';
        $block_content .= '<!-- /wp:html -->';
        $updated_content = $existing_content . "\n\n" . $block_content;
        wp_update_post([ 'ID' => $pageId, 'post_content' => $updated_content, ]);
    }

    return $pageId;
}

class Neartail
{
	function activate()
	{
	}

	function deactivate()
	{
		echo 'Neartail plugin deactivated';
	}

	function renderNeartail($atts = []) 
	{
		$id = sanitize_text_field($atts['id']);
		$appearance = 'wordpress';

		// Check for appearance attributes
		if (array_key_exists('appearance', $atts)) {
			$appearance = sanitize_text_field($atts['appearance']);
		}

		// Check for owner attributes
		if (array_key_exists('owner', $atts)) {
			$owner = sanitize_text_field($atts['owner']);
            $script_url = 'https://neartail.com/include/' . esc_attr($owner) . '/form/' . esc_attr($id) . '/' . esc_attr($appearance) . '.js?div=ff-' . esc_attr($id);
            $script_tag = wp_get_inline_script_tag('', ['src' => $script_url, 'async' => true, 'defer' => true]);
            return '<div id="ff-' . esc_url($id) . '"></div>' . $script_tag;
		} else if ($id) {
            $embedUrl = 'https://neartail.com/forms/d/e/' . esc_attr($id) . '/' . esc_attr($appearance) . '.js?div=ff-' . esc_attr($id);
            wp_register_script('neartail_render_script', $embedUrl, [], null, true);
            wp_enqueue_script('neartail_render_script');
            $script_tag = wp_get_inline_script_tag('', ['src' => $embedUrl, 'async' => true, 'defer' => true]);
            return '<div id="ff-' . esc_url($id) . '"></div>' . $script_tag;
		} else {
			return '<div>Invalid form id.<br/>- For example, if the public url of your Google Form is:  https://docs.google.com/forms/d/e/<span style="background:yellow;color:red;">1FAIpQLSdN-M-uIQN8FfjAZul_BQi0MKYARV_vqNKFejV0QFomAjtdGg</span>/viewform<br/>- Your public id issssssssssss:  1FAIpQLSdN-M-uIQN8FfjAZul_BQi0MKYARV_vqNKFejV0QFomAjtdGg<br/>- So, the short code that you need to add to your page will be: <br/>[neartail id=1FAIpQLSdN-M-uIQN8FfjAZul_BQi0MKYARV_vqNKFejV0QFomAjtdGg]<br/><br/><i>For Support Contact: <b>support@neartail.com</b></i></div>';
		}
	}
}
$neartail = new Neartail();
add_shortcode('neartail', array($neartail, 'renderNeartail'));

?>