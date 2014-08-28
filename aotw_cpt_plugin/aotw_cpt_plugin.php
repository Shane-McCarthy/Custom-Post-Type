<?php
/*
    Plugin Name: Ahead Post Type
    Plugin URI:
    Description: Adding the custom post type. Version: 1.0
    Author: Shane McCarthy

*/


// add to init hook

add_action('init','ahead_post_types');
add_shortcode('aheadcustompost','aotw_cpt_shortcode');
// add these custom post types
function ahead_post_types(){


// register aotw post types
    register_post_type('ahead_post_type',
        array('labels' => array(
            'name' => 'Ahead Post Type',
            'menu_name' => 'Ahead Posts'),
            'singular_label' => 'Ahead Post Type',
            'public' => true,
            'show_ui' =>true,
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'show_in_menu' => true,
            'supports'=>
                array('title','thumbnail')
        )
    );
}
function aotw_cpt_shortcode($attr){


if($attr['page']<=10){
    $per_page['page'] = $attr['page'];
}else{
    $per_page['page'] = 10;
}


    $output =  '<h2 class="winner-title">Hall of Winners</h2><h3 class="winner-subtitle">Congratulations to all of our winners</h3>';
        $args = array( 'post_type' => 'ahead_post_type', 'posts_per_page' => $per_page['page'] );
    $loop = new WP_query( $args );

    if ( $loop->have_posts() ) :

        while ( $loop->have_posts() ) : $loop->the_post();

           $output.='<div class="winner-div"><h3>';
        $output.= get_the_title();
           $output.= '</h3>';
            if ( has_post_thumbnail() ) {
            // check if the post has a post thumbnail assigned to it.
                $thumb = wp_get_attachment_image_src( get_post_thumbnail_id($loop->ID), 'full' );
                $url = $thumb['0'];
                $output.= '<img src="'.$url.'"/>';
                }

            $output.= '</div>';
        endwhile;


    else :
        // if no content, include the "no posts found" template.
      get_template_part( 'content', 'none' );

    endif;


return $output;
}


class PageTemplater {

    /**
     * A Unique Identifier
     */
    protected $plugin_slug;

    /**
     * A reference to an instance of this class.
     */
    private static $instance;

    /**
     * The array of templates that this plugin tracks.
     */
    protected $templates;


    /**
     * Returns an instance of this class.
     */
    public static function get_instance() {

        if( null == self::$instance ) {
            self::$instance = new PageTemplater();
        }

        return self::$instance;

    }

    /**
     * Initializes the plugin by setting filters and administration functions.
     */
    private function __construct() {

        $this->templates = array();


        // Add a filter to the attributes metabox to inject template into the cache.
        add_filter(
            'page_attributes_dropdown_pages_args',
            array( $this, 'register_project_templates' )
        );


        // Add a filter to the save post to inject out template into the page cache
        add_filter(
            'wp_insert_post_data',
            array( $this, 'register_project_templates' )
        );


        // Add a filter to the template include to determine if the page has our
        // template assigned and return it's path
        add_filter(
            'template_include',
            array( $this, 'view_project_template')
        );


        // Add your templates to this array.
        $this->templates = array(
            'Ahead_cpt.php'     => 'Ahead Custom Post',
        );

    }


    /**
     * Adds our template to the pages cache in order to trick WordPress
     * into thinking the template file exists where it doens't really exist.
     *
     */

    public function register_project_templates( $atts ) {

        // Create the key used for the themes cache
        $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

        // Retrieve the cache list.
        // If it doesn't exist, or it's empty prepare an array
        $templates = wp_get_theme()->get_page_templates();
        if ( empty( $templates ) ) {
            $templates = array();
        }

        // New cache, therefore remove the old one
        wp_cache_delete( $cache_key , 'themes');

        // Now add our template to the list of templates by merging our templates
        // with the existing templates array from the cache.
        $templates = array_merge( $templates, $this->templates );

        // Add the modified cache to allow WordPress to pick it up for listing
        // available templates
        wp_cache_add( $cache_key, $templates, 'themes', 1800 );

        return $atts;

    }

    /**
     * Checks if the template is assigned to the page
     */
    public function view_project_template( $template ) {

        global $post;

        if (!isset($this->templates[get_post_meta(
            $post->ID, '_wp_page_template', true
        )] ) ) {

            return $template;

        }

        $file = plugin_dir_path(__FILE__). get_post_meta(
                $post->ID, '_wp_page_template', true
            );

        // Just to be safe, we check if the file exist first
        if( file_exists( $file ) ) {
            return $file;
        }
        else { echo $file; }

        return $template;

    }


}
function add_my_stylesheet()
{
    wp_enqueue_style( 'plugin-style', plugins_url( 'plugin-style.css', __FILE__ ) );
}
add_action('wp_enqueue_scripts', 'add_my_stylesheet');

add_action( 'plugins_loaded', array( 'PageTemplater', 'get_instance' ) );

?>
