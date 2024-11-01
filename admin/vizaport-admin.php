<?php

// vizaport-admin.php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Add a top-level menu item in the WordPress admin
function vizaport_plugin_menu() {
    $icon_class = 'dashicons-format-chat';
    add_menu_page('Vizaport Settings', 'Vizaport Settings', 'manage_options', 'vizaport-settings', 'vizaport_settings_page', $icon_class);
}

add_action('admin_menu', 'vizaport_plugin_menu');

// Register a custom setting and section for Vizaport API Key
function vizaport_api_key_settings_init() {
    register_setting('vizaport_settings_api_key', 'vizaport_ai_model');
    register_setting('vizaport_settings_api_key', 'vizaport_ai_prompt');
    register_setting('vizaport_settings_api_key', 'vizaport_openai_api_key');
    register_setting('vizaport_settings_api_key', 'vizaport_tab_text');
    register_setting('vizaport_settings_api_key', 'vizaport_initial_placeholder');
    register_setting('vizaport_settings_api_key', 'vizaport_loading_message');
    register_setting('vizaport_settings_api_key', 'vizaport_base_color');
    register_setting('vizaport_settings_api_key', 'vizaport_title_color');
    register_setting('vizaport_settings_api_key', 'vizaport_user_text_color');
    register_setting('vizaport_settings_api_key', 'vizaport_chat_text_color');
    register_setting('vizaport_settings_api_key', 'vizaport_default_width');
    register_setting('vizaport_settings_api_key', 'vizaport_default_height');
    register_setting('vizaport_settings_api_key', 'vizaport_font_family');
    register_setting('vizaport_settings_api_key', 'vizaport_font_size');
    register_setting('vizaport_settings_api_key', 'vizaport_default_position', array('default' => 'right'));

    add_settings_section(
        'vizaport_settings_header_section',
        '',
        'vizaport_settings_header_section_callback',
        'vizaport-settings-api-key'
    );

    add_settings_section(
        'vizaport_settings_api_key_section',
        'Step 1: Select an AI Model',
        'vizaport_settings_api_key_section_callback',
        'vizaport-settings-api-key'
    );

    add_settings_section(
        'vizaport_settings_additional_section_two_column',
        'Step 2: Customize your AI Widget (optional)',
        'vizaport_additional_settings_section_callback',
        'vizaport-settings-additional'
    );

    vizaport_publish_settings_init();

}

add_action('admin_init', 'vizaport_api_key_settings_init');

// Callback function for the new section
function vizaport_settings_header_section_callback() {
    ?>
    <link rel="stylesheet" href="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../assets/css/admin.css' ); ?>">
    <div class="container" style="background-image: url('<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../assets/img/background.jpg' ); ?>');">
      <div class="close-btn" onclick="closeContainer()">X</div>
      <div class="left-column">
        <h3>Welcome to Vizaport</h3>
        <h1>Setting up your AI Chatbot</h1>
        <p>First, select an AI model in Step 1 and modify any settings in Step 2. Next, click <b>Save & Preview</b> to test your AI widget at the bottom of this admin page. When ready to publish to your site, choose Add Widget in Step 3. </p>
        <ul style="list-style-type: disc; margin-left: 20px;">
          <li>AI icon hovers at the bottom of all pages until clicked by users</li>
          <li>Opens a chatbot that answers questions about your products and services</li>
          <li>Use the AI prompt to give the AI guidance on how to answer questions</li>
        </ul>
        <p>To train the AI on specific or recent data, or for additional features, check out <a href='https://www.vizaport.com/' style='color: white;' target='_blank'>Vizaport.com</a> to upgrade.</p>
      </div>
      <div class="right-column">
        <iframe src="https://player.vimeo.com/video/883328255?h=9bf2ebfb63" width="100%" height="360" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
     </div>

    </div>
    <script>
    function closeContainer() {
      document.querySelector('.container').style.display = 'none';
    }
    </script>
    <?php
}

// Callback for the Vizaport API Key section
function vizaport_settings_api_key_section_callback() {
    ?>

    <div id='ai_model_options'>
        <p>Choose a model and prompt, <b>click save</b>, then test your AI widget at the bottom of this page by clicking the AI icon. Need to train an AI for better answers? Consider <a href='https://www.vizaport.com' target='_blank'>Vizaport Lite or Pro</a>.<br>
    </div>

    <table class="form-table">
        <tr>
            <td class="column1">
              <h4>AI Model</h4>
            </td>
            <td class="column2">
              <?php vizaport_ai_model_callback(); ?>
            </td>
            <td class="column3">
              <h4 class="open-ai-key-label">OpenAI Key</h4>
            </td>
            <td class="column4 open-ai-key-field" style="display: none;">
              <?php vizaport_openai_api_key_callback(); ?>
            </td>
        </tr>
        <tr>
            <td class="column1">
              <h4>AI Prompt</h4>
            </td>
            <td class="column2">
              <?php vizaport_ai_prompt_callback(); ?>
            </td>
            <td class="column3">
              <h4></h4>
            </td>
            <td class="column4">
            </td>
        </tr>
    </table>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var aiModelSelect = document.getElementsByName('vizaport_ai_model')[0]; // Assuming only one select element for AI model
            var openaiElements = document.querySelectorAll('.open-ai-key-label, .open-ai-key-field'); // Select all elements with the common class

            // Function to toggle display of OpenAI key elements based on selected AI model
            function toggleOpenaiElements() {
                if (aiModelSelect.value === 'openai') {
                    document.getElementById('openai_description').style.display = 'block';
                    document.getElementById('google_description').style.display = 'none';
                    openaiElements.forEach(function(element) {
                        element.style.display = 'block'; // Show elements
                    });
                } else {
                    document.getElementById('openai_description').style.display = 'none';
                    document.getElementById('google_description').style.display = 'block';
                    openaiElements.forEach(function(element) {
                        element.style.display = 'none'; // Hide elements
                    });
                }
            }

            // Call toggle function initially
            toggleOpenaiElements();

            // Add event listener to the AI model select dropdown
            aiModelSelect.addEventListener('change', function() {
                toggleOpenaiElements();
            });
        });
    </script>

    <?php
}

// Callback for the AI Model field
function vizaport_ai_model_callback() {
    $aiModel = esc_attr(get_option('vizaport_ai_model'));
    if (empty($aiModel)) {
        $aiModel = 'google';
    }
    ?>
    <select name='vizaport_ai_model'>
        <option value='google' <?php selected('google', $aiModel); ?>>Google Gemini (Free)</option>
        <option value='openai' <?php selected('openai', $aiModel); ?>>OpenAI ChatGPT</option>
    </select>
    <?php
    echo "<p class='input-description' id='google_description'>Google: Free through 2024. Limited to 10,000 chats per month. Performance may be slower on shared server.</p>";
    echo "<p class='input-description' id='openai_description' style='display: none;'>OpenAI: To use this model, create and enter an OpenAI key (on right). </p>";

}

// Callback for the Vizaport API Key field
function vizaport_ai_prompt_callback() {
    $aiPrompt = esc_attr(get_option('vizaport_ai_prompt'));
    $aiPromptPlaceholder = "Answer questions with information you know about <company name>.";
    echo "<input type='text' name='vizaport_ai_prompt' value='" . esc_attr($aiPrompt) . "' size='60' maxlength='200' placeholder='" . esc_attr($aiPromptPlaceholder) . "' />";
    echo "<p class='input-description'>Instructions for the AI, such as limiting answers to your company. See <a href='https://www.vizaport.com/plugin/#prompt' target='_blank'>help</a>.</p>";
}

// Callback for the Vizaport API Key field
function vizaport_openai_api_key_callback() {
    $apiKey = esc_attr(get_option('vizaport_openai_api_key'));
    echo "<input type='text' name='vizaport_openai_api_key' value='" . esc_attr($apiKey) . "' size='60' />";
    echo "<p class='input-description'>To obtain an OpenAI API key, go to <a href='https://platform.openai.com' target='_blank'>OpenAI</a>. See <a href='https://www.vizaport.com/plugin/#openai_key' target='_blank'>help</a>.</p>";
}


// Function to delete settings on plugin uninstall - Hook in main file vizaport-ai.chat.php
function vizaport_delete_settings_on_uninstall() {
    // Delete the setting from the database
    delete_option( 'vizaport_ai_model' );
    delete_option( 'vizaport_ai_prompt' );
    delete_option( 'vizaport_openai_api_key' );
    delete_option( 'vizaport_tab_text' );
    delete_option( 'vizaport_initial_placeholder' );
    delete_option( 'vizaport_loading_message' );
    delete_option( 'vizaport_base_color' );
    delete_option( 'vizaport_title_color' );
    delete_option( 'vizaport_user_text_color' );
    delete_option( 'vizaport_chat_text_color' );
    delete_option( 'vizaport_default_width' );
    delete_option( 'vizaport_default_height' );
    delete_option( 'vizaport_font_family' );
    delete_option( 'vizaport_font_size' );
    delete_option( 'vizaport_default_position' );
    delete_option( 'vizaport_publish_status' );
}


// Additional Settings UI
function vizaport_additional_settings_section_callback() {
    ?>
    <div class="wrap, additional_steps">
        <p>If you prefer alternative colors, text or placement, customize below and then <b>click save</b>. If you need additional features not listed, check out <a href='https://www.vizaport.com/features' target='_blank'>Vizaport's premium features</a>.</p>
        <form method="post" action="options.php">
            <table class="form-table">
                <tr>
                    <td class="column1">
                      <h4>Title Text</h4>
                    </td>
                    <td class="column2">
                      <?php vizaport_tab_text_callback(); ?>
                    </td>
                    <td class="column3">
                      <h4>Widget Color</h4>
                    </td>
                    <td class="column4">
                      <?php vizaport_base_color_callback(); ?>
                    </td>
                </tr>
                <tr>
                    <td class="column1">
                      <h4>Input Text </h4>
                    </td>
                    <td class="column2">
                      <?php vizaport_initial_placeholder_callback(); ?>
                    </td>
                    <td class="column3">
                      <h4>Title Color</h4>
                    </td>
                    <td class="column4">
                      <?php vizaport_title_color_callback(); ?>
                    </td>
                </tr>
                <tr>
                    <td class="column1">
                      <h4>Loading Text</h4>
                    </td>
                    <td class="column2">
                      <?php vizaport_loading_message_callback(); ?>
                    </td>
                    <td class="column3">
                      <h4>User Chat Color</h4>
                    </td>
                    <td class="column4">
                      <?php vizaport_user_text_color_callback(); ?>
                    </td>
                </tr>
                <tr>
                    <td class="column1">
                      <h4>Default Height</h4>
                    </td>
                    <td class="column2">
                      <?php vizaport_default_height_callback(); ?>
                    </td>
                    <td class="column3">
                      <h4>System Chat Color</h4>
                    </td>
                    <td class="column4">
                      <?php vizaport_chat_text_color_callback(); ?>
                    </td>
                </tr>
                <tr>
                    <td class="column1">
                      <h4>Default Width</h4>
                    </td>
                    <td class="column2">
                      <?php vizaport_default_width_callback(); ?>
                    </td>
                    <td class="column3">
                      <h4>Font Size</h4>
                    </td>
                    <td class="column4">
                      <?php vizaport_font_size_callback(); ?>
                    </td>
                </tr>
                <tr>
                    <td class="column1">
                      <h4>Default Position</h4>
                    </td>
                    <td class="column2">
                      <?php vizaport_default_position_callback(); ?>
                    </td>
                    <td class="column3">
                      <h4>Font Type</h4>
                    </td>
                    <td class="column4">
                      <?php vizaport_font_family_callback(); ?>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save & Preview', 'primary', 'save_changes_button'); ?>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get the Save button element
            var saveButton = document.getElementById('save_changes_button');

            // Attach event listener to the Save button
            saveButton.addEventListener('click', function(event) {
                // Get the selected AI model
                var aiModelSelector = document.querySelector('[name="vizaport_ai_model"]');
                var selectedAiModel = aiModelSelector.value;

                // If OpenAI is selected, check the API key field
                if (selectedAiModel === 'openai') {
                    var apiKeyField = document.querySelector('[name="vizaport_openai_api_key"]');
                    var apiKeyValue = apiKeyField.value.trim();

                    // If API key is blank, show alert and prevent form submission
                    if (apiKeyValue === '') {
                        alert('Please enter an OpenAI key');
                    }
                }
            });
        });
    </script>
    <?php
}

// Callback for the Tab Text field
function vizaport_tab_text_callback() {
    $tabText = esc_attr(get_option('vizaport_tab_text'));
    if (empty($tabText)) {
        $tabText = 'AI Chat';
    }
    echo "<input type='text' name='vizaport_tab_text' value='" . esc_attr($tabText) . "' size='40' />";
    echo "<p class='input-description'>Title of chatbot when opened</p>";
}

// Callback for the Initial Placeholder field
function vizaport_initial_placeholder_callback() {
    $initialPlaceholder = esc_attr(get_option('vizaport_initial_placeholder'));
    if (empty($initialPlaceholder)) {
        $initialPlaceholder = 'Enter your question';
    }
    echo "<input type='text' name='vizaport_initial_placeholder' value='" . esc_attr($initialPlaceholder) . "' size='40' />";
    echo "<p class='input-description'>Text in input field to prompt for question</p>";

}

// Callback for the Loading Message field
function vizaport_loading_message_callback() {
    $loadingMessage = esc_attr(get_option('vizaport_loading_message'));
    if (empty($loadingMessage)) {
        $loadingMessage = 'Processing...';
    }
    echo "<input type='text' name='vizaport_loading_message' value='" . esc_attr($loadingMessage) . "' size='40' />";
    echo "<p class='input-description'>Text that appears when AI is thinking</p>";
}


// Callback for the Default Width field
function vizaport_default_width_callback() {
    $defaultWidth = esc_attr(get_option('vizaport_default_width'));
    if (empty($defaultWidth)) {
        $defaultWidth = '400px';
    }
    echo "<input type='text' name='vizaport_default_width' value='" . esc_attr($defaultWidth) . "' size='40' />";
    echo "<p class='input-description'>Width of the widget when open in pixels (px)</p>";
}

// Callback for the Default Height field
function vizaport_default_height_callback() {
    $defaultHeight = esc_attr(get_option('vizaport_default_height'));
    if (empty($defaultHeight)) {
        $defaultHeight = '450px';
    }
    echo "<input type='text' name='vizaport_default_height' value='" . esc_attr($defaultHeight) . "' size='40' />";
    echo "<p class='input-description'>Height of the widget when open in pixels (px)</p>";
}

// Callback for the Base Color field
function vizaport_base_color_callback() {
    $baseColor = esc_attr(get_option('vizaport_base_color'));
    if (empty($baseColor)) {
        $baseColor = '#333';
    }
    echo "<input type='text' name='vizaport_base_color' value='" . esc_attr($baseColor) . "' size='40' />";
    echo "<p class='input-description'>Main widget color (<a href='https://htmlcolorcodes.com/color-picker/' target='_blank'>hex value</a>)</p>";
}

// Callback for the Title Color field
function vizaport_title_color_callback() {
    $titleColor = esc_attr(get_option('vizaport_title_color'));
    if (empty($titleColor)) {
        $titleColor = '#fff';
    }
    echo "<input type='text' name='vizaport_title_color' value='" . esc_attr($titleColor) . "' size='40' />";
    echo "<p class='input-description'>Title color in tab (<a href='https://htmlcolorcodes.com/color-picker/' target='_blank'>hex value</a>)</p>";
}

// Callback for the User Text Color field
function vizaport_user_text_color_callback() {
    $userTextColor = esc_attr(get_option('vizaport_user_text_color'));
    if (empty($userTextColor)) {
        $userTextColor = '#888';
    }
    echo "<input type='text' name='vizaport_user_text_color' value='" . esc_attr($userTextColor) . "' size='40' />";
    echo "<p class='input-description'>User text color in chat window (<a href='https://htmlcolorcodes.com/color-picker/' target='_blank'>hex value</a>)</p>";
}

// Callback for the Chat Text Color field
function vizaport_chat_text_color_callback() {
    $chatTextColor = esc_attr(get_option('vizaport_chat_text_color'));
    if (empty($chatTextColor)) {
        $chatTextColor = '#333';
    }
    echo "<input type='text' name='vizaport_chat_text_color' value='" . esc_attr($chatTextColor) . "' size='40' />";
    echo "<p class='input-description'>System text color in chat window (<a href='https://htmlcolorcodes.com/color-picker/' target='_blank'>hex value</a>)</p>";
}

// Callback for the Default Position field
function vizaport_default_position_callback() {
    $defaultPosition = esc_attr(get_option('vizaport_default_position'));
    if (empty($defaultPosition)) {
        $defaultPosition = 'right';
    }
    ?>
    <select name='vizaport_default_position'>
        <option value='right' <?php selected('right', $defaultPosition); ?>>Right</option>
        <option value='left' <?php selected('left', $defaultPosition); ?>>Left</option>
    </select>
    <?php
    echo "<p class='input-description'>Position of widget at bottom of page</p>";
}

// Callback for the Font Family field
function vizaport_font_family_callback() {
    $fontFamily = esc_attr(get_option('vizaport_font_family'));
    if (empty($fontFamily)) {
        $fontFamily = 'Arial';
    }
    echo "<input type='text' name='vizaport_font_family' value='" . esc_attr($fontFamily) . "' size='40' />";
    echo "<p class='input-description'>Font type (font family as found in CSS)</p>";
}

// Callback for the Font Size field
function vizaport_font_size_callback() {
    $fontSize = esc_attr(get_option('vizaport_font_size'));
    if (empty($fontSize)) {
        $fontSize = '16px';
    }
    echo "<input type='text' name='vizaport_font_size' value='" . esc_attr($fontSize) . "' size='40' />";
    echo "<p class='input-description'>Font size of text in widget (in px, em, rem)</p>";
}

// Register a custom setting and section for Publish/Unpublish
function vizaport_publish_settings_init() {
    // Register the setting with a default value
    register_setting('vizaport_settings_publish', 'vizaport_publish_status', array('default' => 'unpublished'));

    add_settings_section(
        'vizaport_settings_publish_section',
        'Step 3: Add or Remove your AI Widget',
        'vizaport_settings_publish_section_callback',
        'vizaport-settings-publish'
    );

}

add_action('admin_init', 'vizaport_publish_settings_init');


// Callback for the Publish/Unpublish section
function vizaport_settings_publish_section_callback() {
    $publishStatus = esc_attr(get_option('vizaport_publish_status'));
    $publishText = 'Currently, your widget only appears on this admin page. By selecting Add Widget, your widget will be added to all pages of your web site.';
    $unpublishText = 'By selecting Remove Widget, you will remove the widget from your web site. It will remain on this page for your testing.';
    $textString = ($publishStatus === 'published') ? $unpublishText : $publishText;
    echo '<p class="additional_steps">' . esc_attr($textString) . '</p>';

    ?>
    <select name="vizaport_publish_status" style='display: none;'>
        <option value="unpublished" <?php selected($publishStatus, 'published'); ?>>Add Chat Widget</option>
        <option value="published" <?php selected($publishStatus, 'unpublished'); ?>>Remove Chat Widget</option>
    </select>
    <?php

}


// Callback for the entire settings page
function vizaport_settings_page() {
    ?>
    <div class="wrap">
        <h1>Vizaport Settings</h1>

        <!-- Vizaport API Key Section -->
        <form method="post" action="options.php">
            <?php
            settings_fields('vizaport_settings_api_key');
            do_settings_sections('vizaport-settings-api-key');
            do_settings_sections('vizaport-settings-additional');
            //submit_button('Save Key');
            ?>
        </form>

        <!-- Publish/Unpublish Section -->
        <form method="post" action="options.php" class="additional_steps">
            <?php
            $apiKey = esc_attr(get_option('vizaport_openai_api_key'));
            $publishStatus = esc_attr(get_option('vizaport_publish_status'));
            $buttonText = ($publishStatus === 'published') ? 'Remove Widget' : 'Add Widget';
            settings_fields('vizaport_settings_publish');
            do_settings_sections('vizaport-settings-publish');
            submit_button($buttonText);
            ?>
        </form>
    </div>
    <?php
}

?>
