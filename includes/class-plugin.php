<?php

namespace reference_configurator;

/**
 * The main plugin class.
 */
class Plugin
{

    private $loader;
    private $plugin_slug;
    private $version;
    private $option_name;

    public function __construct() {
        $this->plugin_slug = Info::SLUG;
        $this->version     = Info::VERSION;
        $this->option_name = Info::OPTION_NAME;
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_frontend_hooks();

    }

    private function load_dependencies() {
	    $upload_dir = wp_upload_dir();
    	if (!file_exists($upload_dir['basedir']."/pdfs")) {
		    mkdir($upload_dir['basedir']."/pdfs", 0777, true);
	    }
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'frontend/class-frontend.php';

        $this->loader = new Loader();
    }

    private function define_admin_hooks() {
        $plugin_admin = new Admin($this->plugin_slug, $this->version, $this->option_name);
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'assets');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
	    $this->loader->add_action('init', $plugin_admin, 'register_post_type');
	    $this->loader->add_action('init', $plugin_admin, 'add_term');
	    $this->loader->add_action('admin_menu', $plugin_admin, 'add_menus');
	    $this->loader->add_action('wp_ajax_do_remove', $plugin_admin, 'remove_file');
	    $this->loader->add_action('wp_ajax_nopriv_do_remove', $plugin_admin, 'remove_file');
    }

    private function define_frontend_hooks() {
    	$plugin_frontend = new Frontend($this->plugin_slug, $this->version, $this->option_name);
        $this->loader->add_action('wp_enqueue_scripts', $plugin_frontend, 'assets');
	    $this->loader->add_action('wp_footer', $plugin_frontend, 'render');
	    $this->loader->add_action('wp_ajax_do_action', $plugin_frontend, 'create_pdf');
	    $this->loader->add_action('wp_ajax_nopriv_do_action', $plugin_frontend, 'create_pdf');
	    add_shortcode('references', array($plugin_frontend, 'display_references'));
    }

    public function run() {
        $this->loader->run();
    }
}
