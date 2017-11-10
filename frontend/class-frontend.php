<?php

namespace reference_configurator;
include_once('assets/dompdf/autoload.inc.php');
use Dompdf\Dompdf;
use Dompdf\Options;


/**
 * The code used on the frontend.
 */
class Frontend
{
    private $plugin_slug;
    private $version;
    private $option_name;
    private $settings;

    public function __construct($plugin_slug, $version, $option_name) {
        $this->plugin_slug = $plugin_slug;
        $this->version = $version;
        $this->option_name = $option_name;
        $this->settings = get_option($this->option_name);
    }

    public function assets() {
	    wp_enqueue_style('simple-pagination-css', plugin_dir_url(__FILE__).'css/simplePagination.css', [], $this->version);
	    wp_enqueue_style($this->plugin_slug, plugin_dir_url(__FILE__).'css/reference-configurator.css', [], $this->version);
	    wp_enqueue_style('fancybox-css', plugin_dir_url(__FILE__).'assets/fancybox-master/dist/jquery.fancybox.css', [], $this->version);
	    wp_enqueue_style('ionicons-css', 'http://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css' , [], $this->version);

	    wp_enqueue_script('fancybox-js', plugin_dir_url(__FILE__).'assets/fancybox-master/dist/jquery.fancybox.js', ['jquery'], $this->version, true);
	    wp_enqueue_script('jquery-ui', plugin_dir_url(__FILE__).'js/jquery-ui.js', ['jquery'], $this->version, true);
	    wp_enqueue_script('simple-pagination', plugin_dir_url(__FILE__).'js/jquery.simplePagination.js', ['jquery'], $this->version, true);
	    wp_enqueue_script('jquery-validate', plugin_dir_url(__FILE__).'assets/jquery-validation-1.17.0/dist/jquery.validate.js', ['jquery'], $this->version, true);
	    wp_enqueue_script('r-ajax-script', plugin_dir_url(__FILE__).'js/reference-configurator.js', ['jquery'], $this->version, true);

	    $posts = $this->get_projects();
	    $translations = $this->get_translations_for_ajax();
	    wp_localize_script( 'r-ajax-script', 'ajax_object',
		    array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'posts' => $posts, 'translations' => $translations ) );

	    wp_enqueue_script( 'r-ajax-script' );

    }

    public function get_translations_for_ajax() {
    	$strings = array(
    		'count_reference' => __('Referenz gewählt','reference-configurator'),
		    'count_references' => __('Referenzen gewählt','reference-configurator'),
		    'message_ok' => __('Ihre Formular ist gesendet','reference-configurator'),
		    'message_error' => __('Error','reference-configurator'),
		    'download' => __('Herunterladen','reference-configurator'),
		    'prev' => __('zurück','reference-configurator'),
		    'next' => __('nächste','reference-configurator')
	    );
    	return $strings;
    }

	//display searching box and projects as shortcode
    public function display_references() {
	    $posts = $this->get_projects();
		$countries = $this->get_terms('countries');
		$categories = $this->get_terms('project_category');

        $output = '<div class="project__search">
	               		<div class="search__item search__land">
	               			<select id="search__country" class="search__select">';
                            $output .= '<option value=" " selected>'.__('Land', 'reference-configurator').'</option>';
	               			foreach($countries as $c)
			                {
			                    $output .= '<option class="term-'. $c->term_id.'" value="'. $c->name.'">'.$c->name.'</option>';
			                }
						$output .= '</select>
						</div>
	               		<div class="search__item search__category">
                        	<select id="search__category" class="search__select">';
	                        $output .= '<option value=" " selected>'.__('Kategorie', 'reference-configurator').'</option>';
                            foreach($categories as $c)
			                {
			                    $output .= '<option class="term-'. $c->term_id.'" value="'. $c->name.'">'.$c->name.'</option>';
			                }
						$output .=	'</select>
						</div>
	               		<div class="search__item search__title">
	               			<input type="text" id="search__input" class="search__input" placeholder="'.__('Referenz suchen', 'reference-configurator').'"/>
	               			<button type="submit" class="search__button">'.__('Suche', 'reference-configurator').'</button>
						</div>
	               </div>';
        $output .= '<div id="projects" class="projects">
        			<p class="info hide"><strong>'.__('Für die aktuellen Filter wurden keine Ergebnisse gefunden', 'reference-configurator').'</strong></p>';
        foreach($posts as $p) {
	        $output .= '<div id="guid-'.$p['id'].'" class="project__container draggable icon-hide" data-guid="'.$p['id'].'">
							<i class="icon icon-medium icon-ok ion-checkmark-circled"></i>
							<i class="icon icon-medium icon-remove ion-minus-circled"></i>
							<i class="icon icon-medium icon-add ion-plus-circled"></i>
							
								
							<div class="project__image">
								<img src="'.$p['thumbnail_url'].'" alt="project_image"/>
							</div>';
	                        $posts_countries =  $posts_categories = '';

	                        foreach($p['countries'] as $c)
					        {
						        $posts_countries .= $c->name . ', ';
					        }
					        foreach($p['categories'] as $c)
					        {
						        $posts_categories .= $c->name . ', ';
					        }
							$output .= '<div class="project__title">
								<h4 class="p-title" data-countries="'.$posts_countries.'" data-categories="'.$posts_categories.'">'.$p['title'].'</h4>
							</div>
							<div class="project__links">
								<a href="#" class="fancybox" data-id="'.$p['id'].'">'.__('Details', 'reference-configurator').'</a>
							</div>
						</div>';
        }
        $output .= '</div>';
        $output .= '<div class="pagination"></div>';
        $output .= '<div class="project__references-info"><h1>'.__('MEINE REFERENZ PDF KONFIGURATION', 'reference-configurator').'</h1></div>';
	    $output .= '<div id="project__droppable" class="projectsy project__droppable droppable empty sortable">
                  
                   </div>';
	    $output .='<div class="project__info"></div>';
	    $output .='<div class="project__buttons">
						<a id="pdf-create" class="et_pb_button  et_pb_button_9 et_pb_module et_pb_bg_layout_light" href="#">'.__('PDF erstellen', 'reference-configurator').'</a>
						<a id="pdf-send" class="et_pb_button  et_pb_button_10 et_pb_module et_pb_bg_layout_light" href="#">'.__('PDF versenden', 'reference-configurator').'</a>
						<a id="config-remove" class="et_pb_button  et_pb_button_11 et_pb_module et_pb_bg_layout_light" href="#">'.__('Konfiguration löschen', 'reference-configurator').'</a>
				   </div>';
	    $output .='<div class="ajax-loader"><img src="'.plugin_dir_url(dirname(__FILE__)).'frontend/assets/images/loader.svg" alt="loader"/></div>
				   <div class="downloads"></div>';
	    $output .='<div class="project__form">
						<h1>'.__('Mein Assmont Referenz PDF versenden', 'reference-configurator').'</h1>
						<form method="post" class="send-pdf-form">
							<input type="text" id="pdf_email" name="pdf_email" placeholder="'.__('E-mail', 'reference-configurator').'"/><br/>
							<input type="text" id="pdf_subject" name="pdf_subject" placeholder="'.__('Betreff', 'reference-configurator').'"/><br/>
							<textarea id="pdf_message" name="pdf_message" placeholder="'.__('Nachricht', 'reference-configurator').'"></textarea><br/>
							<input type="submit" id="pdf-send-mail" name="pdf_send" value="'.__('Senden', 'reference-configurator').'"/>
							<div class="mail-loader"><img src="'.plugin_dir_url(dirname(__FILE__)).'frontend/assets/images/loader.svg" alt="loader"/></div>
							<div class="form_info"></div>
						</form>
					</div>';
	    return $output;
	}

	public function get_projects() {
    	global $post;

    	$posts = get_posts('post_type=project&order_by=title&order=ASC&posts_per_page=-1');

		foreach($posts as $p) {
			$projects[] = array(
				'id' => $p->ID,
				'title' => get_the_title($p->ID),
				'thumbnail_url' => get_the_post_thumbnail_url($p->ID),
				'details' => do_shortcode($p->post_content),
				'categories' => wp_get_object_terms($p->ID, 'project_category'),
				'countries' => wp_get_object_terms($p->ID, 'countries')
			);
		}
        return  $projects;
	}

	public function get_terms($term) {
    	global $post;
        $terms = get_terms($term);
        return $terms;
	}

	public function create_pdf() {
		//global $wpdb;
		$options = new Options();
		$options->set('isRemoteEnabled', true);
		$dompdf = new Dompdf($options);
		include(plugin_dir_path(dirname(__FILE__)).'frontend/partials/create-pdf.php');
		die();
	}

    /**
     * Render the view using MVC pattern.
     */
    public function render() {

        // Model
        $settings = $this->settings;

        $settings = $this->plugin_slug;
        // Controller
        // Declare vars like so:
        // $var = $settings['slug'] ?? '';

        // View
        if (locate_template('partials/' . $this->plugin_slug . '.php')) {
            require_once(locate_template('partials/' . $this->plugin_slug . '.php'));
        } else {
            require_once plugin_dir_path(dirname(__FILE__)).'frontend/partials/view.php';
        }
    }
}
