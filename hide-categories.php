<?php
/**
 * Plugin Name: Hide Categories
 * Description: A simple plugin to hide specific categories and their posts for specific users.
 * Version: 1.3
 * Author: Piero Meloni
 * Author URI:: https://www.subitoweb.it
 * Text Domain: Hide Categories
 */

// Add admin menu
function hide_categories_menu() {
    add_options_page(
        'Hide Categories',
        'Hide Categories',
        'manage_options',
        'hide_categories',
        'hide_categories_options_page'
    );
}
add_action('admin_menu', 'hide_categories_menu');

// Admin page content
function hide_categories_options_page() {
    ?>
    <div class="wrap">
        <h2>Hide Categories</h2>
        <form action="options.php" method="post">
            <?php
            settings_fields('hide_categories_options');
            do_settings_sections('hide_categories');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">User IDs to Allow:</th>
                    <td><input type="text" name="allowed_user_ids" value="<?php echo get_option('allowed_user_ids'); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Category IDs to Hide:</th>
                    <td><input type="text" name="hidden_category_ids" value="<?php echo get_option('hidden_category_ids'); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Initialize settings
function hide_categories_settings_init() {
    register_setting('hide_categories_options', 'allowed_user_ids');
    register_setting('hide_categories_options', 'hidden_category_ids');
}
add_action('admin_init', 'hide_categories_settings_init');

// Logic for hiding categories
function hide_categories_logic( $args ) {
    // If the user is an administrator, do not hide any categories
    if (current_user_can('administrator')) {
        return $args;
    }

    // Get the allowed user IDs and hidden category IDs from the options
    $allowed_user_ids = explode(',', get_option('allowed_user_ids'));
    $hidden_category_ids = explode(',', get_option('hidden_category_ids'));

    // Check if the current user ID is not in the allowed user IDs
    if (!in_array(get_current_user_id(), $allowed_user_ids)) {
        // Add the hidden category IDs to the query args
        $args['exclude'] = $hidden_category_ids;
    }

    return $args;
}
add_filter( 'get_terms_args', 'hide_categories_logic', 10, 2 );

// Logic for hiding posts
function hide_category_posts_logic( $query ) {
    // If the user is an administrator, do not hide any posts
    if (current_user_can('administrator')) {
        return $query;
    }

    // Get the allowed user IDs and hidden category IDs from the options
    $allowed_user_ids = explode(',', get_option('allowed_user_ids'));
    $hidden_category_ids = explode(',', get_option('hidden_category_ids'));

    // Check if the current user ID is not in the allowed user IDs
    if (!in_array(get_current_user_id(), $allowed_user_ids)) {
        // Modify the query to exclude posts in the hidden categories
        $query->set('category__not_in', $hidden_category_ids);
    }

    return $query;
}
add_action( 'pre_get_posts', 'hide_category_posts_logic' );
