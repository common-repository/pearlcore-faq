<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, hooks for enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @link:       http://pearlcore.com/
 * @since      1.0
 * @package    Pc_Faq
 * @subpackage Pc_Faq/includes
 */
class Pc_Faq_backend {

    /**
     * Page hook for the options screen
     *
     * @since 1.0
     * @type string
     */
    protected $pc_screen = null;

    /**
     * The ID of this plugin.
     *
     * @since    1.0
     * @access   private
     * @var      string    $name    The ID of this plugin.
     */
    private $name;

    /**
     * The version of this plugin.
     *
     * @since    1.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0
     * @var      string    $name       The name of this plugin.
     * @var      string    $version    The version of this plugin.
     */
    public function __construct($name, $version) {

        $this->name = $name;
        $this->version = $version;

        add_action('admin_menu', array($this, 'pc_faq_add_menu')); //register the plugin menu in backend

        add_action('init', array($this, 'pc_faq_post_type_register'));

        add_action('init', array($this, 'pc_faq_category_register'), 1);

        add_action('woocommerce_product_write_panel_tabs', array($this, 'pc_faq_product_tab'));

        add_action('woocommerce_product_write_panels', array($this, 'pc_faq_product_tab_content'));

        add_action('woocommerce_process_product_meta', array($this, 'pc_faq_product_tab_meta_save'), 10, 2);

        add_action('add_meta_boxes', array($this, 'pc_faq_asker_info'));

        add_action('save_post', array($this, 'pc_faq_asker_info_save'));

        add_filter('page_row_actions', array($this, 'pc_faq_post_row_actions'), 999, 2);

        add_filter('manage_pc-faq_posts_columns', array($this, 'book_cpt_columns'));

        add_action('manage_pc-faq_posts_custom_column', array($this, 'custom_book_column'), 10, 2);
    }

    /**
     * 
     * @since      2.2
     * @param string $actions
     * @param type $post
     * @return string
     */
    public function pc_faq_post_row_actions($actions, $post) {


        if ('pc-faq' === $post->post_type) {

            $post_type_object = get_post_type_object($post->post_type);

            $post_type_label = $post_type_object->labels->singular_name;

            if ($post->post_status == 'draft' || $post->post_status == 'pending') {

                $faq_approve_link = "<a href='#' class='pc_faq_approve_link' data-id='" .
                        $post->ID . "' title='" . esc_attr(__('Approve this', PC_FAQ_TEXT_DOMAIN)) .
                        $post_type_label . "' data-nonce='" .
                        wp_create_nonce('publish-post_' . $post->ID) . "'>" .
                        __('Approve', PC_FAQ_TEXT_DOMAIN) . "</a>";
                $faq_approve_link .= $this->faq_appove_popup_html($post);

                $actions['publish'] = $faq_approve_link;
            }
        }

        return $actions;
    }

    public function faq_appove_popup_html($post) {
        $post_id = $post->ID;
//    var_dump($product);
        $faq_detail = '';
        $post_title = $post->post_title;
        $post_content = $post->post_content;
        $faq_button = '';
        $pc_faq_author_name = get_post_meta($post->ID, 'pc_faq_author_name', true);
        $pc_faq_author_email = get_post_meta($post->ID, 'pc_faq_author_email', true);
        $post_type_label = 'FAQ Post';
        if ($post->post_status == 'draft' || $post->post_status == 'pending'):
            $faq_button = '<a class="button-primary faq_action_link" data-id="' . $post_id . '" title=" ' . esc_attr(__("Approve this ", PC_FAQ_TEXT_DOMAIN), $post_type_label) . '" data-nonce="' . wp_create_nonce('publish-post_' . $post_id) . '" action-type="approve">Approve</a>';
            $pc_faq_send_email = get_post_meta($post_id, 'pc_faq_send_email', TRUE);
            if ($pc_faq_send_email == 'yes'):
                $faq_button .= '<span class="pc_faq_email_sent">' . __('Email Sent', PC_FAQ_TEXT_DOMAIN) . '</span>';
            else:
                $faq_button .= '<a class="button-primary faq_action_link" data-id="' . $post_id . '" title=" ' . esc_attr(__("Send Email", PC_FAQ_TEXT_DOMAIN), $post_type_label) . '" data-nonce="' . wp_create_nonce('publish-post_' . $post_id) . '" action-type="send_email">Send Email</a>';
            endif;

        endif;

        $faq_detail .= $post_title;
        $return_html = '<div class="faq_fields_popup" id="faq_detail_popup_' . $post_id . '">
            <div class="faq_close_wrapper faq_popup_close_button">X</div>
            <div id="" class="section faq_popup_header">
                <h3>User Asked Question</h3>
            </div>
            <div class="faq_network_field_html">
                <div class="bs_user_question faq_black_text">
                    <table>
                    <tr>
                    <td class="faq_strong">' . __('Question', PC_FAQ_TEXT_DOMAIN) . '</td>
                    <td>' . $faq_detail . '</td>
                    </tr>
                    <tr>
                    <td class="faq_strong">' . __('Answer', PC_FAQ_TEXT_DOMAIN) . '</td>
                    <td><textarea style="width:100%;" rows="4" class="pc_faq_answer" required>' . $post_content . '</textarea></td>
                    </tr>
                    <tr>
                    <td class="faq_strong">' . __('Name', PC_FAQ_TEXT_DOMAIN) . '</td>
                    <td>' . $pc_faq_author_name . '</td>
                    </tr>
                    <tr>
                    <td class="faq_strong">' . __('Email', PC_FAQ_TEXT_DOMAIN) . '</td>
                    <td>' . $pc_faq_author_email . '</td>
                    </tr>
                    <tr>
                    <td class="faq_strong">' . __('Show on Frontend', PC_FAQ_TEXT_DOMAIN) . '</td>
                    <td><input type="checkbox" value="yes" class="pc_faq_show_frontend"></td>
                    </tr>
                    </table>
                </div>
            </div>
            <div class="section faq_popup_footer">
                ' . $faq_button . '
                <input type="button" class="button-secondary faq_popup_close_button" value="Close" id="faq_button_social_network_facebook">
            </div>
        </div>';

        return $return_html;
    }

    /**
     * Register the stylesheets for the Dashboard.
     *
     * @since    1.0
     */
    public function enqueue_styles($hook) {
        /**
         * @since    2.0
         */
        wp_enqueue_style($this->name . '-theme-a', PC_FAQ_ASSETS_URL . 'css/theme-a.css', array(), $this->version, 'all');

        wp_enqueue_style('pc-faq-product', PC_FAQ_ASSETS_URL . 'css/pc-faq-product.css', array(), $this->version);

        wp_enqueue_style($this->name . '-menu', PC_FAQ_ASSETS_URL . 'css/menu.css', array(), $this->version);

        if ($this->pc_screen != $hook):
            return;
        endif;
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Pc_Faq_Admin_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Pc_Faq_Admin_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style('pc-faq-options-framework', PC_FAQ_ASSETS_URL . 'css/pc-faq-options-framework.css', array(), $this->version);
        wp_enqueue_style('wp-color-picker');

        wp_enqueue_style('font-awesome.min', PC_FAQ_ASSETS_URL . 'css/font-awesome.min.css', array(), $this->version);

        wp_enqueue_style('pc-faq-backend', PC_FAQ_ASSETS_URL . 'css/pc-faq-backend.css', array(), $this->version);
    }

    /**
     * Register the JavaScript for the dashboard.
     *
     * @since    1.0
     */
    public function enqueue_scripts($hook) {
        /**
         * @since    2.0
         */
        wp_enqueue_script($this->name . '-pc-faq-product', PC_FAQ_ASSETS_URL . 'js/pc-faq-product.js', array('jquery'), $this->version, true);

        wp_enqueue_script($this->name . '-test', PC_FAQ_ASSETS_URL . 'js/test.js', array('jquery'), $this->version, true);

        wp_localize_script($this->name . '-pc-faq-product', 'pc_product', array('pc_product_ajax' => admin_url('admin-ajax.php')), $this->version);
        if ($this->pc_screen != $hook):
            return;
        endif;

        if (function_exists('wp_enqueue_media')):
            wp_enqueue_media();
        endif;

        wp_enqueue_script($this->name . '-pc-faq-functions', PC_FAQ_ASSETS_URL . 'js/pc-faq-functions.js', array('jquery', 'wp-color-picker'), $this->version, true);

        wp_enqueue_script($this->name . '-pc-faq-backend', PC_FAQ_ASSETS_URL . 'js/pc-faq-backend.js', array('jquery'), $this->version, true);

        wp_localize_script($this->name . '-pc-faq-backend', 'pc_backend', array('pc_ajax' => admin_url('admin-ajax.php')), $this->version);

        add_action('admin_head', array($this, 'of_admin_head'));
    }

    function of_admin_head() {
        do_action('optionsframework_custom_scripts');
    }

    /*
     * Define menu options (still limited to appearance section)
     *
     * Examples usage:
     *
     * add_filter( 'pc_faq_backend_menu', function( $menu ) {
     *     $menu['page_title'] = 'The Options';
     * 	   $menu['menu_title'] = 'The Options';
     *     return $menu;
     * });
     *
     * @since 1.0
     *
     */

    static function pc_faq_menus() {
        $pc_menu = array(
// Modes: submenu, menu
            'mode' => 'submenu',
            // Submenu default settings
            'page_title' => __('Pearlcore Faq Settings', PC_FAQ_TEXT_DOMAIN),
            'menu_title' => __('Setting', PC_FAQ_TEXT_DOMAIN),
            'capability' => 'manage_options',
            'menu_slug' => 'pc-faq-settings',
            'parent_slug' => 'edit.php?post_type=pc-faq',
            'menu_callback' => 'pc_faq_main_page',
            // Menu default settings
            'icon_url' => 'dashicons-admin-generic',
            'position' => '62'
        );
        return apply_filters('pc_faq_backend_menu', $pc_menu);
    }

    /**
     * Main Setting Page
     * 
     * @since      1.0
     */
    public function pc_faq_main_page() {
        ?>
        <div id="" class="wrap">
            <?php $menu = $this->pc_faq_menus(); ?>
            <h2><?php echo esc_html($menu['page_title']); ?></h2>
            <div class="pc_about_wrapper">
                <span>Feel Free To ask any question or have any problem. <a href="http://pearlcore.com/contact/">Contact Us</a></span>
            </div>

            <h2 class="nav-tab-wrapper">
                <?php echo Pcs_Faq_Framework_Interface::pcs_faq_framework_tabs(); ?>
            </h2>

            <?php settings_errors('options-framework'); ?>
            <div class="pc_faq_setting_overlay"></div>
            <div class="pc_setting_spinner_wrapper">
                <div class="pc_setting_spinner">
                    <i class="fa fa-spinner fa-spin"></i>
                </div>
                <div class="pc_setting_message"></div>
            </div>
            <div id="optionsframework-metabox" class="metabox-holder">
                <div id="optionsframework" class="postbox">
                    <form action="options.php" method="post">
                        <?php settings_fields('optionsframework'); ?>
                        <?php Pcs_Faq_Framework_Interface::pcs_faq_framework_fields(); /* Settings */ ?>
                        <div id="optionsframework-submit">
                            <input type="submit" class="button-primary" name="update" value="<?php esc_attr_e('Save Options', PC_FAQ_TEXT_DOMAIN); ?>" />
                            <input type="submit" class="reset-button button-secondary" name="reset" value="<?php esc_attr_e('Restore Defaults', PC_FAQ_TEXT_DOMAIN); ?>" onclick="return confirm('<?php print esc_js(__('Click OK to reset. Any theme settings will be lost!', PC_FAQ_TEXT_DOMAIN)); ?>');" />
                            <div class="clear"></div>
                        </div>
                    </form>
                </div> <!-- / #container -->
            </div>
            <?php do_action('optionsframework_after'); ?>
        </div> <!-- / .wrap -->

        <?php
    }

    /**
     * register the plugin menu for backend.
     * 
     * @since      1.0
     */
    public function pc_faq_add_menu() {
        $pc_menus = $this->pc_faq_menus();
        switch ($pc_menus['mode']) {

            case 'menu':
// http://codex.wordpress.org/Function_Reference/add_menu_page
                $this->pc_screen = add_menu_page(
                        $pc_menus['page_title'], $pc_menus['menu_title'], $pc_menus['capability'], $pc_menus['menu_slug'], array($this, $pc_menus['menu_callback']), $pc_menus['icon_url'], $pc_menus['position']
                );
                break;

            default:
// http://codex.wordpress.org/Function_Reference/add_submenu_page
                $this->pc_screen = add_submenu_page(
                        $pc_menus['parent_slug'], $pc_menus['page_title'], $pc_menus['menu_title'], $pc_menus['capability'], $pc_menus['menu_slug'], array($this, $pc_menus['menu_callback']));
                break;
        }
    }

    /**
     * Register this Custom Post Type.
     *
     * @since    1.0
     */
    public function pc_faq_post_type_register() {

        $labels = array(
            'name' => _x('Pearlcore Faq', 'Post Type General Name', PC_FAQ_TEXT_DOMAIN),
            'singular_name' => _x('Faq', 'Post Type Singular Name', PC_FAQ_TEXT_DOMAIN),
            'menu_name' => __('Pearlcore Faq', PC_FAQ_TEXT_DOMAIN),
            'parent_item_colon' => __('Parent Item:', PC_FAQ_TEXT_DOMAIN),
            'all_items' => __('All Questions', PC_FAQ_TEXT_DOMAIN),
            'view_item' => __('View Item', PC_FAQ_TEXT_DOMAIN),
            'add_new_item' => __('Add Qustion', PC_FAQ_TEXT_DOMAIN),
            'add_new' => __('Add Faq ', PC_FAQ_TEXT_DOMAIN),
            'edit_item' => __('Edit Faq', PC_FAQ_TEXT_DOMAIN),
            'update_item' => __('Update Faq', PC_FAQ_TEXT_DOMAIN),
            'search_items' => sprintf(__('Search %s', PC_FAQ_TEXT_DOMAIN), 'Faq'),
            'not_found' => __('Not found', PC_FAQ_TEXT_DOMAIN),
            'not_found_in_trash' => __('Not found in Trash', PC_FAQ_TEXT_DOMAIN),
        );
        $args = array(
            'label' => __('pearlcore-faq', PC_FAQ_TEXT_DOMAIN),
            'description' => __('Pearlcore_Faq_Pearlcore_Faq_CPT', PC_FAQ_TEXT_DOMAIN),
            'labels' => $labels,
            'supports' => array('title', 'editor', 'author'),
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            'menu_position' => 20,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'rewrite' => false,
            'capability_type' => 'page',
            'menu_icon' => 'dashicons-info',
        );
        register_post_type('pc-faq', $args);
    }

    /**
     * @since      1.0
     */
    function pc_faq_category_register() {

        register_taxonomy('fa_category', 'pc-faq', array(
            'labels' => array(
                'name' => _x('Category', 'taxonomy general name'),
                'singular_name' => _x('Faq-Category', 'taxonomy singular name'),
                'search_items' => __('Search Faq-Categories'),
                'all_items' => __('All Categories'),
                'parent_item' => __('Parent Category'),
                'parent_item_colon' => __('Parent Category:'),
                'edit_item' => __('Edit Category'),
                'update_item' => __('Update Category'),
                'add_new_item' => __('Add New Category'),
                'new_item_name' => __('New Category Name'),
                'menu_name' => __('Categories'),
            ),
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array(
                'slug' => 'fa_category', // This controls the base slug that will display before each term
                'with_front' => true, // Don't display the category base before "/locations/"
                'hierarchical' => true,
            ),
        ));
    }

    /**
     * Woocommerce Product Tab
     * @since    2.0
     */
    function pc_faq_product_tab() {
        ?>
        <li class="pc_faq_tab"><a class="pc_faq_tab_link" id="pc_faq_tab_link" href="#pc_faq_tab_data"><?php _e("FAQ's", PC_FAQ_TEXT_DOMAIN); ?></a></li>
        <?php
    }

    /**
     * Woocommerce Product Tab Content
     * 
     * @since    2.0
     * @global type $post
     */
    function pc_faq_product_tab_content() {
        global $post;
        ?>
        <div id="pc_faq_tab_data" class="panel woocommerce_options_panel pc_faq_product_wrapper">
            <div class="hidden pc_faq_product_id"><?php echo $post->ID; ?></div>
            <div class="pc_faq_setting_overlay"></div>
            <div class="pc_setting_spinner_wrapper">
                <div class="pc_setting_spinner">
                    <div class="blockUI blockOverlay"></div>
                </div>
                <div class="pc_setting_message"></div>
            </div>
            <div class="options_group">
                <?php woocommerce_wp_checkbox(array('id' => 'pc_enable_single_product', 'label' => __('Enable FAQ Tab?', PC_FAQ_TEXT_DOMAIN), 'description' => __('Enable this option to enable the FAQ tab on the frontend for this product.', PC_FAQ_TEXT_DOMAIN))); ?>
            </div>
            <div class="options_group">
                <?php woocommerce_wp_checkbox(array('id' => 'pc_enable_ask_question', 'label' => __('Enable Ask Question?', PC_FAQ_TEXT_DOMAIN), 'description' => __('Enable this option for user to ask question on this product', PC_FAQ_TEXT_DOMAIN))); ?>
            </div>
            <div class="options_group pc_faq_product_fields_wrapper">
                <?php echo pc_get_product_faq($post->ID, 'true', 'pc_faq_product_wrapper'); ?>
            </div> 
            <div class="options_group pc_product_add_button_wrapper">                                                                         
                <input type="button" class="button-primary pc_add_faq_button" value="Add FAQ" name="pc_add_faq">
            </div> 
        </div>
        <?php
    }

    /**
     * Woocommerce Product Tab Save Meta 
     * 
     * @since    2.0
     * @param type $post_id
     */
    function pc_faq_product_tab_meta_save($post_id) {
        update_post_meta($post_id, 'pc_enable_single_product', ( isset($_POST['pc_enable_single_product']) && $_POST['pc_enable_single_product'] ) ? 'yes' : 'no' );
        update_post_meta($post_id, 'pc_enable_ask_question', ( isset($_POST['pc_enable_ask_question']) && $_POST['pc_enable_ask_question'] ) ? 'yes' : 'no' );
    }

    /**
     * Register Post Meta
     * 
     * @since      2.2
     */
    public function pc_faq_asker_info() {

        add_meta_box(
                'pc_faq_asker_info', __('FAQ Detail', PC_FAQ_TEXT_DOMAIN), array($this, 'pc_faq_asker_info_content'), 'pc-faq', 'normal', 'high'
        );
    }

    /**
     * 
     * @param type $post
     * @since      2.2
     */
    function pc_faq_asker_info_content($post) {
        wp_nonce_field(plugin_basename(__FILE__), 'pc_faq_asker_info_content');
        ?>
        <div class="">
            <table>
                <tr>
                    <td><label for="pc_faq_author_name"><?php _e('Author Name', PC_FAQ_TEXT_DOMAIN); ?></label></td>
                    <td><input type="text" value="<?php echo get_post_meta($post->ID, 'pc_faq_author_name', true) ?>" name="pc_faq_author_name" class="pc_faq_author_name"></td>
                </tr>

                <tr>
                    <td><label for="pc_faq_author_email"><?php _e('Author Email', PC_FAQ_TEXT_DOMAIN); ?></label></td>
                    <td><input type="text" value="<?php echo get_post_meta($post->ID, 'pc_faq_author_email', true) ?>" name="pc_faq_author_email" class="pc_faq_author_email"></td>
                </tr>

                <tr>
                    <?php
                    $pc_faq_display_frontend = get_post_meta($post->ID, 'pc_faq_display_frontend', true);
                    $pc_faq_show = '';
                    if ($pc_faq_display_frontend == 'yes'):
                        $pc_faq_show = 'checked="checked"';
                    endif;
                    ?>
                    <td><label for="pc_faq_author_email"><?php _e('Display on Frontend', PC_FAQ_TEXT_DOMAIN); ?></label></td>
                    <td><input type="checkbox" value="yes" name="pc_faq_display_frontend" class="pc_faq_display_frontend" <?php echo $pc_faq_show; ?>></td>
                </tr>

            </table>
        </div>
        <?php
    }

    /**
     * 
     * @param type $post_id
     * @return type
     * 
     * @since      2.2
     */
    function pc_faq_asker_info_save($post_id) {
        $slug = 'pc-faq';

        if (isset($_POST['post_type']) && $slug != $_POST['post_type']) :
            return;
        endif;

        if (isset($_REQUEST['pc_faq_author_name'])) :
            $pc_faq_author_name = $_POST['pc_faq_author_name'];
            update_post_meta($post_id, 'pc_faq_author_name', $pc_faq_author_name);
        endif;
        if (isset($_REQUEST['pc_faq_author_email'])) :
            $pc_faq_author_email = $_POST['pc_faq_author_email'];
            update_post_meta($post_id, 'pc_faq_author_email', $pc_faq_author_email);
        endif;
        update_post_meta($post_id, 'pc_faq_product_id', '');
        if (isset($_POST['pc_faq_display_frontend']) && !empty($_POST['pc_faq_display_frontend'])):
            $pc_faq_display_frontend = $_POST['pc_faq_display_frontend'];
        else:
            $pc_faq_display_frontend = 'no';
        endif;
        update_post_meta($post_id, 'pc_faq_display_frontend', $pc_faq_display_frontend);
    }

    public function book_cpt_columns($columns) {
        unset($columns['author']);
        unset($columns['date']);
        $new_columns = array(
            'product_id' => __('Product ID', PC_FAQ_TEXT_DOMAIN),
            'total_likes' => __('Total Likes', PC_FAQ_TEXT_DOMAIN),
            'new_author' => __('Author', PC_FAQ_TEXT_DOMAIN),
            'date' => __('Date', PC_FAQ_TEXT_DOMAIN),
        );
        return array_merge($columns, $new_columns);
    }

    public function custom_book_column($column, $post_id) {
        switch ($column) {

            case 'product_id' :
                $product_id = get_post_meta($post_id, 'pc_faq_product_id', TRUE);
                if ($product_id)
                    echo $product_id;
                else
                    echo '—';
                break;
            case 'total_likes' :
                $pc_faq_like = get_post_meta($post_id, 'pc_faq_like', TRUE);
                if ($pc_faq_like)
                    echo $pc_faq_like;
                else
                    echo '—';
                break;
            case 'new_author' :
                $pc_faq_author_name = get_post_meta($post_id, 'pc_faq_author_name', TRUE);
                if ($pc_faq_author_name):
                    echo $pc_faq_author_name;
                else:
                    $post_author_id = get_post_field('post_author', $post_id);
                    $author_link = get_the_author_meta('url', $post_author_id);
//                    $post_author_link = $author_link.'&author='.$post_author_id;
                    $post_author_link = admin_url('edit.php')."?post_type=pc-faq&author={$post_author_id}";
                    $post_author_nicename = get_the_author_meta('nicename', $post_author_id);
                    echo '<a href="' . $post_author_link . '">' . $post_author_nicename . '</a>';
                endif;

                break;
        }
    }

}
