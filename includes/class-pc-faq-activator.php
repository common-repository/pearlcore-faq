<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0
 * @package    Pc_Faq
 * @subpackage Pc_Faq/includes
 */
if (!defined('ABSPATH')) {
    exit;
}

class Pc_Faq_Activator {

    /**
     * Hook in tabs.
     * 
     * @since      3.0
     */
    public static function init() {
        add_action('init', array(__CLASS__, 'check_version'), 10);
        add_action('in_plugin_update_message-pearlcore-faq/pearlcore-faq.php', array(__CLASS__, 'in_plugin_update_message'), 10, 2);
    }

    /**
     * Check WooCommerce version and run the updater is required.
     *
     * This check is done on all requests and runs if he versions do not match.
     */
    public static function check_version() {
        if (!defined('IFRAME_REQUEST') && get_option('pc_faq_version') !== PC_FAQ_VERSION) {
            self::install();
            self::pc_faq_update_version();
        }
    }

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0
     */
    public static function install() {
        if (!get_option('pc_faq_install')):
            self::pc_faq_create_tabels();
            self::pc_faq_update_version();
            add_option('pc_faq_install', true);
        endif;
    }

    /**
     * Update PC FAQ version to current.
     * @since      3.0
     */
    private static function pc_faq_update_version() {
        delete_option('pc_faq_version');
        add_option('pc_faq_version', PC_FAQ_VERSION);
    }

    /**
     * Likes Table
     * 
     * @since      1.0
     * @global type $wpdb
     */
    public static function pc_faq_create_tabels() {
        global $wpdb;
        $collate = $wpdb->get_charset_collate();

        $pc_faq_table_sql = "
            CREATE TABLE {$wpdb->prefix}pc_like_counts (
            id INT(11) NOT NULL AUTO_INCREMENT,
            user_ip VARCHAR(255) NOT NULL,
            post_id VARCHAR(255) NOT NULL,
            PRIMARY KEY (id)
            ) $collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($pc_faq_table_sql);
    }

    /**
     * Show plugin changes. Code adapted from W3 Total Cache.
     * @since      3.0
     */
    public static function in_plugin_update_message($args, $r) {
        $current_version = get_option('pc_faq_version');
        $transient_name = 'pc_faq_upgrade_notice_' . $current_version;
        if (false === ( $upgrade_notice = get_transient($transient_name) )) {
            $response = wp_safe_remote_get('https://plugins.svn.wordpress.org/pearlcore-faq/trunk/readme.txt');
            if (!is_wp_error($response) && !empty($response['body'])) {
                $upgrade_notice = self::parse_update_notice($response['body'], $args['new_version']);
                set_transient($transient_name, $upgrade_notice, DAY_IN_SECONDS);
            }
        }

        echo wp_kses_post($upgrade_notice);
    }

    /**
     * Parse update notice from readme file.
     *
     * @since      3.0
     * @param  string $content
     * @param  string $new_version
     * @return string
     */
    private static function parse_update_notice($content, $new_version) {
        // Output Upgrade Notice.
        $matches = null;
        $regexp = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote(PC_FAQ_VERSION) . '\s*=|$)~Uis';
        $upgrade_notice = '';
        if (preg_match($regexp, $content, $matches)) {
            $version = trim($matches[1]);
            $notices = (array) preg_split('~[\r\n]+~', trim($matches[2]));

            // Check the latest stable version and ignore trunk.
            if ($version === $new_version && version_compare(PC_FAQ_VERSION, $version, '<')) {
                $upgrade_notice .= '<div class="pc_faq_plugin_upgrade_notice">';
                foreach ($notices as $index => $line) {
                    $upgrade_notice .= wp_kses_post(preg_replace('~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line));
                }
                $upgrade_notice .= '</div> ';
            }
        }
        return wp_kses_post($upgrade_notice);
    }

}

Pc_Faq_Activator::init();
