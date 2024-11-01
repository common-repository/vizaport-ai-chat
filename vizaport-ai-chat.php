<?php
/*
Plugin Name: Vizaport AI Chat
Description: Adds an AI chatbot on a web site and allows a user to interact with an AI server (Google or OpenAI) to ask questions.
Version: 1.4.1
Author: Vizaport
Author URI: https://www.vizaport.com/
License: GPL-2.0
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Setting under plug-in name
function vizaport_plugin_action_links($links) {
    $plugin_file = 'vizaport-ai-chat/vizaport-ai-chat.php';

    // Add custom links with labels and URLs
    $custom_links = array(
        'settings'      => '<a href="' . admin_url('admin.php?page=vizaport-settings') . '">Settings</a>',
        'deactivate'    => '<a href="' . admin_url('plugins.php?action=deactivate&plugin=' . urlencode($plugin_file)) . '">Deactivate</a>',
    );

    // Merge the custom links with the existing links
    $links = array_merge($custom_links, $links);

    return $links;
}

// Hook into the plugin_action_links filter
add_filter('plugin_action_links_vizaport-ai-chat/vizaport-ai-chat.php', 'vizaport_plugin_action_links');

// Admin Pages
include plugin_dir_path(__FILE__) . 'admin/vizaport-admin.php';
add_action('admin_menu', 'vizaport_plugin_menu');

// Client Script to Install the Javascript with parameters
function vizaport_ai_plugin_enqueue_script() {

    $aiModel = esc_attr(get_option('vizaport_ai_model'));
    $openaiApiKey = esc_attr(get_option('vizaport_openai_api_key'));

    // Check if it has an OpenAI key and has a value if OpenAI is the model. If Google, no key needed.
    if ( ($aiModel === 'google') || ($aiModel === 'openai' && !empty($openaiApiKey)) ) {

      // Parameters
      $vz_tab_text = esc_attr(get_option('vizaport_tab_text'));
      $vz_initial_placeholder = esc_attr(get_option('vizaport_initial_placeholder'));
      $vz_loading_message = esc_attr(get_option('vizaport_loading_message'));
      $vz_default_position = esc_attr(get_option('vizaport_default_position'));
      $vz_base_color = esc_attr(get_option('vizaport_base_color'));
      $vz_title_color = esc_attr(get_option('vizaport_title_color'));
      $vz_user_text_color = esc_attr(get_option('vizaport_user_text_color'));
      $vz_chat_text_color = esc_attr(get_option('vizaport_chat_text_color'));
      $vz_default_width = esc_attr(get_option('vizaport_default_width'));
      $vz_default_height = esc_attr(get_option('vizaport_default_height'));
      $vz_font_family = esc_attr(get_option('vizaport_font_family'));
      $vz_font_size = esc_attr(get_option('vizaport_font_size'));

      // Localize the script with new data, including additional parameters
      $ajax_params = array(
          'ajax_url' => admin_url('admin-ajax.php'),
          '_ajax_nonce' => wp_create_nonce('vizaport_ai_ajax_action'),
          'tabText' => $vz_tab_text,
          'initialPlaceholder' => $vz_initial_placeholder,
          'loadingMessage' => $vz_loading_message,
          'defaultPosition' => $vz_default_position,
          'baseColor' => $vz_base_color,
          'titleColor' => $vz_title_color,
          'userTextColor' => $vz_user_text_color,
          'chatTextColor' => $vz_chat_text_color,
          'defaultWidth' => $vz_default_width,
          'defaultHeight' => $vz_default_height,
          'fontFamily' => $vz_font_family,
          'fontSize' => $vz_font_size,
      );

      $script_attributes = array(
          'type' => 'text/javascript',
          'version' => '1.0',
          'data-minify' => 'false', // Add data-minify="false" attribute
      );

      if (is_admin()) {
          // Enqueue script for the admin side
          wp_enqueue_script('vizaport-ai-ajax-script-admin', plugin_dir_url(__FILE__) . 'js/vizaport-ai-ajax-script.js', array('jquery'), '1.0', true);
      } else {
          // Enqueue script for the main site
          wp_enqueue_script('vizaport-ai-ajax-script', plugin_dir_url(__FILE__) . 'js/vizaport-ai-ajax-script.js', array('jquery'), '1.0', true);
      }
        // Localize the script with parameters
      $ajax_params['base_url'] = plugins_url('/', __FILE__);
      wp_localize_script(is_admin() ? 'vizaport-ai-ajax-script-admin' : 'vizaport-ai-ajax-script', 'ajax_object', $ajax_params);
    }
}

// Add the script enqueue to the admin page
function vizaport_admin_page_script() {
    // Hook into admin_enqueue_scripts to enqueue the script
    add_action('admin_enqueue_scripts', 'vizaport_ai_plugin_enqueue_script');
}

// Call the admin page script function
vizaport_admin_page_script();

// Check the Publish/Unpublish status and enqueue script accordingly
$publishStatus = get_option('vizaport_publish_status');
if ($publishStatus === 'published') {
    add_action('wp_enqueue_scripts', 'vizaport_ai_plugin_enqueue_script');
}


// Server Script to handle the API calls
function vizaport_ai_ajax_function() {

    if ( ! wp_verify_nonce( $_POST['_ajax_nonce'], 'vizaport_ai_ajax_action' ) ) {
        // Nonce verification failed, handle error
        wp_die( 'Nonce verification failed.');
    }

    // Parameters
    $aiModel = esc_attr(get_option('vizaport_ai_model'));
    $aiPrompt = esc_attr(get_option('vizaport_ai_prompt'));
    $openaiApiKey = esc_attr(get_option('vizaport_openai_api_key'));
    $openaiApiModel = "gpt-4o";

    // Get the user prompt
    if (isset($_POST['question'])) {
        $userPrompt = filter_var($_POST['question'], FILTER_SANITIZE_STRING);
        if (strlen($userPrompt) > 100) {
            $userPrompt = "Question too long. Please keep it under 100 characters.";
        }
    } else {
        $userPrompt = "Tell me a joke";
    }

    // Set the system prompt
    $systemPrompt = "You are a helpful assistant, respond in less than three sentences.";
    if ($aiPrompt && (strlen($aiPrompt) <= 200))  {
      $systemPrompt .= ' ' . filter_var($aiPrompt, FILTER_SANITIZE_STRING);
    }

    // PREPARE FOR API CALLS - GOOGLE AND OPENAI
    $request_args = array();

    // GOOGLE MODEL
    if ($aiModel === "google") {
        // Define the Google API endpoint
        $googleEndpoint = "https://widgetgcp-e4yu4jlgzq-uw.a.run.app/google_gemini";

        // Prepare data for the HTTP request
        $data = array(
            'prompt' => $systemPrompt,
            'question' => $userPrompt
        );

        // Give the Google service a reasonable timeout
        $timeout_seconds = 20;

        // Set up request arguments
        $request_args = array(
            'body' => $data,
            'timeout' => $timeout_seconds,
        );

        // Make the request using wp_remote_post
        $response = wp_remote_post($googleEndpoint, $request_args);

        // Check for errors in the response
        if (is_wp_error($response)) {
            echo 'Request error: ' . esc_html($response->get_error_message());
            exit();
        }

        // Get the result
        $result = wp_remote_retrieve_body($response);

        // If result is not empty
        if (!empty($result)) {
            $response = array('ai_response' => $result);
            wp_send_json_success($response);
        }

    // OPEN AI MODEL
    } elseif ($aiModel === "openai") {
        $openaiEndpoint = "https://api.openai.com/v1/chat/completions";

        // Prepare the data for the HTTP request
        $data = [
            "model" => $openaiApiModel,
            "messages" => [
                [
                    "role" => "system",
                    "content" => $systemPrompt
                ],
                [
                    "role" => "user",
                    "content" => $userPrompt
                ]
            ]
        ];

        // Encode the data for the request
        $postData = wp_json_encode($data);

        // Set up request arguments
        $request_args = array(
            'body' => $postData,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $openaiApiKey,
            ),
        );

        // Make the request using wp_remote_post
        $response = wp_remote_post($openaiEndpoint, $request_args);

        // Check for errors in the response
        if (is_wp_error($response)) {
            echo 'Request error: ' . esc_html($response->get_error_message());
            exit();
        }

        // Get the response body
        $response_body = wp_remote_retrieve_body($response);

        // Decode the response from OpenAI
        $result = json_decode($response_body, true);

        // Check if the 'choices' key is present in the response
        if (isset($result['choices']) && !empty($result['choices'])) {
            $response = array('ai_response' => $result['choices'][0]['message']['content']);
            wp_send_json_success($response);
        } else {
            // Error
            $error_message = '';

            if (isset($result['error'])) {
                $error_message = $result['error']['message'];
            } else {
                $error_message = 'Sorry, there was an unknown error!';
            }

            $response = array('ai_response' => 'System Admin: ' . $error_message);
            wp_send_json_success($response);
        }
    }

}
add_action('wp_ajax_nopriv_vizaport_ai_ajax_action', 'vizaport_ai_ajax_function');
add_action('wp_ajax_vizaport_ai_ajax_action', 'vizaport_ai_ajax_function');


// Callback function to delete settings on plugin uninstall/delete
function vizaport_plugin_uninstall() {
    include_once plugin_dir_path( __FILE__ ) . 'admin/vizaport-admin.php';
    vizaport_delete_settings_on_uninstall();
}

// Hook into the deactivation event
register_uninstall_hook( __FILE__, 'vizaport_plugin_uninstall' );

?>
