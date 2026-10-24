<?php
/**
 * Plugin Name:       Events Manager Presenter Addon
 * Description:       Adds a Presenter Custom Post Type with contact fields, linked seamlessly into the Events Manager layout with an auto-injector toggle.
 * Version:           1.1.0
 * Author:            Jonathan Eiseman
 * Author URI:        https://github.com/jeiseman
 * Plugin URI:        https://github.com/jeiseman/events-manager-presenter-addon
 * License:           GPL v2 or later
 * License URI:       https://gnu.org/licenses/gpd-2.0.html
 * Text Domain:       events-manager-presenter-addon
 * Requires at least: 6.0
 * Requires PHP:      7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. REGISTER AND ENQUEUE EXTERNAL FRONT-END STYLE SHEET
 */
function evmpr_enqueue_frontend_styles() {
    wp_enqueue_style( 
        'evmpr-frontend-styles', 
        plugin_dir_url( __FILE__ ) . 'style.css', 
        array(), 
        '1.1.0' 
    );
}
add_action( 'wp_enqueue_scripts', 'evmpr_enqueue_frontend_styles' );

/**
 * 2. REGISTER THE PRESENTERS CUSTOM POST TYPE
 */
function evmpr_register_presenter_cpt() {
    $labels = array(
        'name'               => _x( 'Presenters', 'post type general name', 'events-manager-presenter-addon' ),
        'singular_name'      => _x( 'Presenter', 'post type singular name', 'events-manager-presenter-addon' ),
        'menu_name'          => _x( 'Presenters', 'admin menu', 'events-manager-presenter-addon' ),
        'name_admin_bar'     => _x( 'Presenter', 'add new on admin bar', 'events-manager-presenter-addon' ),
        'add_new'            => _x( 'Add New', 'presenter', 'events-manager-presenter-addon' ),
        'add_new_item'       => __( 'Add New Presenter', 'events-manager-presenter-addon' ),
        'new_item'           => __( 'New Presenter', 'events-manager-presenter-addon' ),
        'edit_item'          => __( 'Edit Presenter', 'events-manager-presenter-addon' ),
        'view_item'          => __( 'View Presenter', 'events-manager-presenter-addon' ),
        'all_items'          => __( 'All Presenters', 'events-manager-presenter-addon' ),
        'search_items'       => __( 'Search Presenters', 'events-manager-presenter-addon' ),
        'not_found'          => __( 'No presenters found.', 'events-manager-presenter-addon' ),
        'not_found_in_trash' => __( 'No presenters found in Trash.', 'events-manager-presenter-addon' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'presenter' ),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 25,
        'menu_icon'          => 'dashicons-businessman',
        'supports'           => array( 'title', 'editor', 'thumbnail' ),
        'show_in_rest'       => true,
    );

    register_post_type( 'evmpr_presenter', $args );
}
add_action( 'init', 'evmpr_register_presenter_cpt' );

/**
 * 3. ADD META BOXES (Contact Fields for Presenter CPT & Linked Selector for Event CPT)
 */
function evmpr_add_meta_boxes() {
    // Meta box inside the Presenter editor screen for contact info
    add_meta_box(
        'evmpr_presenter_contact_box',
        __( 'Presenter Contact Information', 'events-manager-presenter-addon' ),
        'evmpr_render_contact_meta_box',
        'evmpr_presenter',
        'normal',
        'high'
    );

    // Meta box inside the Events Manager 'event' screen
    add_meta_box(
        'evmpr_event_presenter_box',
        __( 'Event Presenter Settings', 'events-manager-presenter-addon' ),
        'evmpr_render_event_meta_box',
        'event', 
        'side',
        'default'
    );
}
add_action( 'add_meta_boxes', 'evmpr_add_meta_boxes' );

/**
 * 4. RENDER PRESENTER CONTACT META BOX
 */
function evmpr_render_contact_meta_box( $post ) {
    wp_nonce_field( 'evmpr_save_contact_meta', 'evmpr_contact_nonce' );

    $website = get_post_meta( $post->ID, '_evmpr_website', true );
    $email   = get_post_meta( $post->ID, '_evmpr_email', true );
    $phone   = get_post_meta( $post->ID, '_evmpr_phone', true );

    echo '<table class="form-table">';
    echo '<tr>';
    echo '<th><label for="evmpr_website">' . esc_html__( 'Website URL', 'events-manager-presenter-addon' ) . '</label></th>';
    echo '<td><input type="url" name="evmpr_website" id="evmpr_website" value="' . esc_url( $website ) . '" class="regular-text" placeholder="https://" /></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th><label for="evmpr_email">' . esc_html__( 'Email Address', 'events-manager-presenter-addon' ) . '</label></th>';
    echo '<td><input type="email" name="evmpr_email" id="evmpr_email" value="' . esc_attr( $email ) . '" class="regular-text" /></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th><label for="evmpr_phone">' . esc_html__( 'Phone Number', 'events-manager-presenter-addon' ) . '</label></th>';
    echo '<td><input type="text" name="evmpr_phone" id="evmpr_phone" value="' . esc_attr( $phone ) . '" class="regular-text" /></td>';
    echo '</tr>';
    echo '</table>';
}

/**
 * 5b. ENQUEUE ADMIN SCRIPTS FOR AUTOCOMPLETE
 */
add_action( 'admin_enqueue_scripts', 'evmpr_enqueue_admin_scripts' );
function evmpr_enqueue_admin_scripts( $hook ) {
    global $post_type;
    
    // Only load the autocomplete script on the Event post edit screens
    if ( 'event' === $post_type && in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
        
        // Enqueue Core jQuery UI Autocomplete
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        
        // Enqueue Custom Search Script
        wp_enqueue_script( 
            'evmpr-admin-search', 
            plugin_dir_url( __FILE__ ) . 'admin-presenter-search.js', 
            array( 'jquery', 'jquery-ui-autocomplete' ), 
            '1.1.0', 
            true // Load in footer
        );
        
        // Pass the AJAX URL and secure Nonce to the external JS file
        wp_localize_script( 
            'evmpr-admin-search', 
            'evmpr_ajax_obj', 
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'evmpr_search_nonce' )
            )
        );
    }
}

/**
 * 5. RENDER EVENT SELECTION META BOX (AJAX SCALABLE VERSION)
 */
function evmpr_render_event_meta_box( $post ) {
    wp_nonce_field( 'evmpr_save_event_meta', 'evmpr_event_nonce' );

    $selected_presenter_id = get_post_meta( $post->ID, '_evmpr_selected_presenter', true );
    $auto_inject           = get_post_meta( $post->ID, '_evmpr_auto_inject_profile', true );

    // Get the title of the currently saved presenter to pre-fill the search box
    $selected_title = '';
    if ( ! empty( $selected_presenter_id ) && 'publish' === get_post_status( $selected_presenter_id ) ) {
        $selected_title = get_the_title( $selected_presenter_id );
    }

    echo '<p><label for="evmpr_presenter_search"><strong>' . esc_html__( 'Search and Select Presenter:', 'events-manager-presenter-addon' ) . '</strong></label></p>';
    
    // Visible text input for the AJAX search
    echo '<input type="text" id="evmpr_presenter_search" value="' . esc_attr( $selected_title ) . '" class="widefat" placeholder="' . esc_attr__( 'Type a name...', 'events-manager-presenter-addon' ) . '" />';
    
    // Hidden input that securely stores the ID to be saved to the database
    echo '<input type="hidden" name="evmpr_select_presenter" id="evmpr_select_presenter_id" value="' . esc_attr( $selected_presenter_id ) . '" />';
    
    echo '<p class="description">' . esc_html__( 'Start typing to search. Clear the field to remove the linked presenter.', 'events-manager-presenter-addon' ) . '</p>';

    echo '<p style="margin-top: 15px;">';
    echo '<label for="evmpr_auto_inject">';
    echo '<input type="checkbox" name="evmpr_auto_inject" id="evmpr_auto_inject" value="1" ' . checked( $auto_inject, '1', false ) . ' />';
    echo ' ' . esc_html__( 'Auto-inject profile panel to page bottom', 'events-manager-presenter-addon' );
    echo '</label>';
    echo '</p>';
}

/**
 * 6. SECURELY SAVE ALL METADATA
 */
/**
 * 6. SECURELY SAVE ALL METADATA
 */
function evmpr_save_custom_metadata( $post_id, $post ) {
    // Autosave check
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Handle Presenter Contact Fields Save
    if ( 'evmpr_presenter' === $post->post_type && isset( $_POST['evmpr_contact_nonce'] ) ) {
        
        $contact_nonce = sanitize_text_field( wp_unslash( $_POST['evmpr_contact_nonce'] ) );
        
        if ( wp_verify_nonce( $contact_nonce, 'evmpr_save_contact_meta' ) && current_user_can( 'edit_post', $post_id ) ) {
            
            $website = isset( $_POST['evmpr_website'] ) ? esc_url_raw( wp_unslash( $_POST['evmpr_website'] ) ) : '';
            $email   = isset( $_POST['evmpr_email'] ) ? sanitize_email( wp_unslash( $_POST['evmpr_email'] ) ) : '';
            $phone   = isset( $_POST['evmpr_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['evmpr_phone'] ) ) : '';

            update_post_meta( $post_id, '_evmpr_website', $website );
            update_post_meta( $post_id, '_evmpr_email', $email );
            update_post_meta( $post_id, '_evmpr_phone', $phone );
        }
    }

    // Handle Event Linked Field Save
    if ( 'event' === $post->post_type && isset( $_POST['evmpr_event_nonce'] ) ) {
        
        $event_nonce = sanitize_text_field( wp_unslash( $_POST['evmpr_event_nonce'] ) );
        
        if ( wp_verify_nonce( $event_nonce, 'evmpr_save_event_meta' ) && current_user_can( 'edit_post', $post_id ) ) {
            
            if ( isset( $_POST['evmpr_select_presenter'] ) ) {
                update_post_meta( $post_id, '_evmpr_selected_presenter', sanitize_text_field( wp_unslash( $_POST['evmpr_select_presenter'] ) ) );
            }
            
            $auto_inject_val = isset( $_POST['evmpr_auto_inject'] ) ? '1' : '0';
            update_post_meta( $post_id, '_evmpr_auto_inject_profile', $auto_inject_val );
        }
    }
}
add_action( 'save_post', 'evmpr_save_custom_metadata', 10, 2 );

/**
 * 7. REGISTER EVENTS MANAGER PLACEHOLDERS
 */
function evmpr_register_em_placeholders( $replace, $EM_Event, $result ) {
    if ( strpos( $result, '#_PRESENTER' ) === false ) {
        return $replace;
    }

    $presenter_id = get_post_meta( $EM_Event->post_id, '_evmpr_selected_presenter', true );
    if ( empty( $presenter_id ) || 'publish' !== get_post_status( $presenter_id ) ) {
        return '';
    }

    switch ( $result ) {
        case '#_PRESENTERNAME':
            $replace = esc_html( get_the_title( $presenter_id ) );
            break;
        case '#_PRESENTERBIO':
            $replace = wp_kses_post( wpautop( get_post( $presenter_id )->post_content ) );
            break;
        case '#_PRESENTERPHOTO':
            if ( has_post_thumbnail( $presenter_id ) ) {
                $replace = get_the_post_thumbnail( $presenter_id, 'medium', array( 'class' => 'evmpr-avatar-img' ) );
            }
            break;
        case '#_PRESENTERWEBSITE':
            $web = get_post_meta( $presenter_id, '_evmpr_website', true );
            $replace = ! empty( $web ) ? '<a href="' . esc_url( $web ) . '">' . esc_html__( 'Visit Website', 'events-manager-presenter-addon' ) . '</a>' : '';
            break;
        case '#_PRESENTEREMAIL':
            $email = get_post_meta( $presenter_id, '_evmpr_email', true );
            $replace = ! empty( $email ) ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '';
            break;
        case '#_PRESENTERPHONE':
            $phone = get_post_meta( $presenter_id, '_evmpr_phone', true );
            $replace = ! empty( $phone ) ? '<a href="tel:' . esc_attr( $phone ) . '">' . esc_html( $phone ) . '</a>' : '';
            break;
    }
    return $replace;
}
add_filter( 'em_event_output_placeholder', 'evmpr_register_em_placeholders', 10, 3 );

/**
 * GENERATE LAYOUT MARKUP FOR AUTO-INJECTOR
 */
function evmpr_get_presenter_markup( $presenter_id ) {
    if ( ! $presenter_id || 'publish' !== get_post_status( $presenter_id ) ) {
        return '';
    }
    
    $title   = get_the_title( $presenter_id );
    $content = get_post( $presenter_id )->post_content;
    
    // Note: style.css handles the 50% border-radius and width via .evmpr-profile-avatar img
    $avatar  = has_post_thumbnail( $presenter_id ) ? get_the_post_thumbnail( $presenter_id, 'thumbnail', array( 'alt' => esc_attr( $title ) ) ) : '';
    
    $web     = get_post_meta( $presenter_id, '_evmpr_website', true );
    $email   = get_post_meta( $presenter_id, '_evmpr_email', true );
    $phone   = get_post_meta( $presenter_id, '_evmpr_phone', true );
    
    ob_start();
    ?>
    <div class="evmpr-profile-box">
        
        <?php if ( $avatar ) : ?>
            <div class="evmpr-profile-avatar">
                <?php echo wp_kses_post( $avatar ); ?>
            </div>
        <?php endif; ?>
        
        <div class="evmpr-profile-details">
            <h3><?php echo esc_html( $title ); ?></h3>
            
            <?php if ( $content ) : ?>
                <div class="evmpr-bio-content">
                    <?php echo wp_kses_post( wpautop( $content ) ); ?>
                </div>
            <?php endif; ?>
            
            <?php if ( $web || $email || $phone ) : ?>
                <div class="evmpr-meta-links">
                    <?php if ( $web ) : ?>
                        <a href="<?php echo esc_url( $web ); ?>" target="_blank" rel="noopener noreferrer">🌐 <?php esc_html_e( 'Visit Website', 'events-manager-presenter-addon' ); ?></a>
                    <?php endif; ?>
                    
                    <?php if ( $email ) : ?>
                        <a href="mailto:<?php echo esc_attr( $email ); ?>">✉️ <?php echo esc_html( $email ); ?></a>
                    <?php endif; ?>
                    
                    <?php if ( $phone ) : ?>
                        <a href="tel:<?php echo esc_attr( $phone ); ?>">📞 <?php echo esc_html( $phone ); ?></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**AUTO-INJECT LAYOUT FILTER*/
function evmpr_inject_presenter_into_content( $content ) {
    if ( is_singular( 'event' ) && in_the_loop() && is_main_query() ) {
        $event_id    = get_the_ID();
        $presenter_id = get_post_meta( $event_id, '_evmpr_selected_presenter', true );
        $auto_inject  = get_post_meta( $event_id, '_evmpr_auto_inject_profile', true );
        if ( '1' === $auto_inject && ! empty( $presenter_id ) ) {
            $content .= evmpr_get_presenter_markup( $presenter_id );
        }
    }
    return $content;
}
add_filter( 'the_content', 'evmpr_inject_presenter_into_content' );

/**
 * 0. CHECK DEPENDENCIES ON ACTIVATION
 */
function evmpr_check_activation_dependencies() {
    // Include the core plugin library so we can use is_plugin_active()
    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }

    // Check if the core Events Manager plugin is active
    if ( ! is_plugin_active( 'events-manager/events-manager.php' ) ) {
        // Deactivate this add-on immediately
        deactivate_plugins( plugin_basename( __FILE__ ) );
        
        // Kill the execution and display an error message
        wp_die( 
            esc_html__( 'The Events Manager Presenter Addon requires the core Events Manager plugin to be installed and activated.', 'events-manager-presenter-addon' ), 
            esc_html__( 'Plugin Dependency Error', 'events-manager-presenter-addon' ), 
            array( 'back_link' => true ) 
        );
    }
}
register_activation_hook( __FILE__, 'evmpr_check_activation_dependencies' );
/**
 * 8. REGISTER AND RENDER THE HELP PAGE
 */
function evmpr_add_help_submenu() {
    add_submenu_page(
        'edit.php?post_type=evmpr_presenter', // The parent menu slug (Presenters CPT)
        esc_html__( 'Presenter Addon Help', 'events-manager-presenter-addon' ),
        esc_html__( 'Help & Instructions', 'events-manager-presenter-addon' ),
        'manage_options',
        'evmpr-help',
        'evmpr_render_help_page'
    );
}
add_action( 'admin_menu', 'evmpr_add_help_submenu' );

/**
 * GENERATE THE HELP PAGE HTML
 */
function evmpr_render_help_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Events Manager Presenter Addon - Help Guide', 'events-manager-presenter-addon' ); ?></h1>
        <p><?php esc_html_e( 'Follow the instructions below to manage presenters and link them to your Events Manager events.', 'events-manager-presenter-addon' ); ?></p>
        
        <hr />

        <h2><?php esc_html_e( '1. Adding a Presenter', 'events-manager-presenter-addon' ); ?></h2>
        <ol>
            <li>Navigate to <strong>Presenters > Add New</strong> in the WordPress admin menu.</li>
            <li>Enter the presenter's name in the title field and their biographical information in the main content editor.</li>
            <li>Upload a photo using the <strong>Featured Image</strong> box in the right sidebar.</li>
            <li>Scroll down to the <strong>Presenter Contact Information</strong> meta box to enter their Website URL, Email Address, and Phone Number.</li>
            <li>Publish the post.</li>
        </ol>

        <h2><?php esc_html_e( '2. Linking a Presenter to an Event', 'events-manager-presenter-addon' ); ?></h2>
        <ol>
            <li>Navigate to <strong>Events</strong> and add a new event or edit an existing one.</li>
            <li>Locate the <strong>Event Presenter Settings</strong> meta box.</li>
            <li>Click into the search field and start typing the presenter's name. Select the correct presenter from the dropdown results.</li>
            <li>Save or update your event.</li>
        </ol>

        <h2><?php esc_html_e( '3. Displaying Presenter Information', 'events-manager-presenter-addon' ); ?></h2>
        <p><?php esc_html_e( 'You have two ways to display the presenter information on the front end:', 'events-manager-presenter-addon' ); ?></p>
        
        <h3><?php esc_html_e( 'Method A: Auto-Injector', 'events-manager-presenter-addon' ); ?></h3>
        <p><?php esc_html_e( 'If you want a styled profile box to appear automatically at the very bottom of a single event page:', 'events-manager-presenter-addon' ); ?></p>
        <ul>
            <li>In the event editor, go to the <strong>Event Presenter Settings</strong> meta box.</li>
            <li>Check the box labeled <strong>"Auto-inject profile panel to page bottom"</strong>.</li>
        </ul>

        <h3><?php esc_html_e( 'Method B: Custom Placeholders', 'events-manager-presenter-addon' ); ?></h3>
        <p><?php esc_html_e( 'If you want precise control over where the presenter information appears within the Events Manager formatting settings (e.g., Settings > Formatting > Events > Single event page format), use the following custom placeholders:', 'events-manager-presenter-addon' ); ?></p>
        
        <table class="widefat striped" style="max-width: 800px; margin-top: 10px;">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Placeholder', 'events-manager-presenter-addon' ); ?></th>
                    <th><?php esc_html_e( 'Description', 'events-manager-presenter-addon' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>#_PRESENTERNAME</code></td>
                    <td><?php esc_html_e( 'Displays the presenter\'s full name.', 'events-manager-presenter-addon' ); ?></td>
                </tr>
                <tr>
                    <td><code>#_PRESENTERBIO</code></td>
                    <td><?php esc_html_e( 'Displays the presenter\'s biography.', 'events-manager-presenter-addon' ); ?></td>
                </tr>
                <tr>
                    <td><code>#_PRESENTERPHOTO</code></td>
                    <td><?php esc_html_e( 'Displays the presenter\'s featured image.', 'events-manager-presenter-addon' ); ?></td>
                </tr>
                <tr>
                    <td><code>#_PRESENTERWEBSITE</code></td>
                    <td><?php esc_html_e( 'Displays a linked "Visit Website" anchor tag.', 'events-manager-presenter-addon' ); ?></td>
                </tr>
                <tr>
                    <td><code>#_PRESENTEREMAIL</code></td>
                    <td><?php esc_html_e( 'Displays a linked email address.', 'events-manager-presenter-addon' ); ?></td>
                </tr>
                <tr>
                    <td><code>#_PRESENTERPHONE</code></td>
                    <td><?php esc_html_e( 'Displays a linked phone number.', 'events-manager-presenter-addon' ); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php
}
