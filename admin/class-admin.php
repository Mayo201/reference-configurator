<?php

namespace reference_configurator;

/**
 * The code used in the admin.
 */
class Admin
{
    private $plugin_slug;
    private $version;
    private $option_name;
    private $settings;
    private $settings_group;

    public function __construct($plugin_slug, $version, $option_name) {
        $this->plugin_slug = $plugin_slug;
        $this->version = $version;
        $this->option_name = $option_name;
        $this->settings = get_option($this->option_name);
        $this->settings_group = $this->option_name.'_group';
    }

    /**
     * Generate settings fields by passing an array of data (see the render method).
     *
     * @param array $field_args The array that helps build the settings fields
     * @param array $settings   The settings array from the options table
     *
     * @return string The settings fields' HTML to be output in the view
     */
    private function custom_settings_fields($field_args, $settings) {
        $output = '';

        foreach ($field_args as $field) {
            $slug = $field['slug'];
            $setting = $this->option_name.'['.$slug.']';
            $label = esc_attr__($field['label'], 'plugin-name');
            $output .= '<h3><label for="'.$setting.'">'.$label.'</label></h3>';

            if ($field['type'] === 'text') {
                $output .= '<p><input type="text" id="'.$setting.'" name="'.$setting.'" value="'.$settings[$slug].'"></p>';
            } elseif ($field['type'] === 'textarea') {
                $output .= '<p><textarea id="'.$setting.'" name="'.$setting.'" rows="10">'.$settings[$slug].'</textarea></p>';
            }
        }

        return $output;
    }

    public function assets() {
	    wp_enqueue_style('simple-pagination-css', plugin_dir_url(__FILE__).'css/simplePagination.css', [], $this->version);
	    wp_enqueue_style($this->plugin_slug, plugin_dir_url(__FILE__).'css/reference-configurator-admin.css', [], $this->version);
        wp_enqueue_script($this->plugin_slug, plugin_dir_url(__FILE__).'js/reference-configurator.js', ['jquery'], $this->version, true);
	    wp_enqueue_script('simple-pagination', plugin_dir_url(__FILE__).'js/jquery.simplePagination.js', ['jquery'], $this->version, true);
	    wp_enqueue_script('r-admin-script', plugin_dir_url(__FILE__).'js/r-settings.js', ['jquery'], $this->version, true);

	    wp_localize_script( 'r-admin-script', 'ajax_object',
		    array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ));

	    wp_enqueue_script( 'r-admin-script' );
    }

    public function remove_file(){

	    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		    $filename = $_POST['filename'];
		    if ( $filename != null ) {
			    $upload_dir = wp_upload_dir();
			    $path_dir   = $upload_dir['basedir'] . '/pdfs';
			    $remove_flag = unlink($path_dir.'/'.$filename);

			    wp_send_json( array(
					    'flag'  => $remove_flag
				    )
			    );
		    }
	    }
    }

    public function get_pdfs(){
	    $upload_dir = wp_upload_dir();
	    $path_dir   = $upload_dir['basedir'] . '/pdfs';
	    $allfiles = scandir($path_dir);
	    $pdfs = array_diff($allfiles, array('.', '..'));
	    return $pdfs;
    }

    public function register_settings() {
        register_setting($this->settings_group, $this->option_name);
    }

    public function register_post_type() {
		if(!post_type_exists('project')) {
			register_post_type( 'project',
				array(
					'labels'      => array(
						'name'          => __( 'Projekte' ),
						'singular_name' => __( 'Projekt' )
					),
					'public'      => true,
					'has_archive' => true
				)
			);
		}
    }

    public function add_term(){

	    register_taxonomy(
		    'countries',
		    'project',
		    array(
			    'label' => __( 'Countries' ),
			    'rewrite' => array( 'slug' => 'countries' ),
			    'hierarchical' => true,
		    )
	    );
    }

    public function add_menus() {
        $plugin_name = Info::get_plugin_title();
        add_submenu_page(
            'options-general.php',
            $plugin_name,
            $plugin_name,
            'manage_options',
            $this->plugin_slug,
            [$this, 'render']
        );
    }

    /**
     * Render the view using MVC pattern.
     */
    public function render() {

        // Generate the settings fields
        $field_args = [
            // [
            //     'label' => 'Text Label',
            //     'slug'  => 'text-slug',
            //     'type'  => 'text'
            // ],
            // [
            //     'label' => 'Textarea Label',
            //     'slug'  => 'textarea-slug',
            //     'type'  => 'textarea'
            // ]
        ];

        // Model
        $settings = $this->settings;
	    $list = $this->get_pdfs();

        // Controller
        $fields = $this->custom_settings_fields($field_args, $settings);
        $settings_group = $this->settings_group;
        $heading = Info::get_plugin_title();
        $submit_text = esc_attr__('Submit', 'plugin-name');

        // View
        require_once plugin_dir_path(dirname(__FILE__)).'admin/partials/view.php';
    }
}
