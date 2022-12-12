<?php
/*
Plugin Name:  Alt Compiler
Plugin URI:   https://www.enricomarogna.com 
Description:  This plugin takes care of filling in the ALT fields of featured images, but only if they are left blank. The ALT field is populated with the title of the post. 
Version:      1.0
Author:       Enrico Marogna 
Author URI:   https://www.enricomarogna.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  alt-compiler
Domain Path:  /plugins
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

/** 
 * ===========================================================================-
 * Console Log
 * ===========================================================================
 */
function console_log($output, $with_script_tags = true) {
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . ');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
}

/**
 * ===========================================================================
 * La funzione, che viene avviata quando viene salvato un post, cerca i post
 * con immagini in evidenza e se l'immagine non ha l'attributo ALT lo popola
 * con il titolo del post
 * ===========================================================================
 */
add_action('save_post','set_attachment_alt_text');
function set_attachment_alt_text(){
    $args = array(
        'posts_per_page'    => -1,
    );
    $posts_array = get_posts( $args );
    foreach($posts_array as $post){
        // Eseguo solo per i post che hanno una immagine in evidenza
        if ( has_post_thumbnail( $post->ID ) ) {
            // Assegno alla variabile l id dell immagine
            $thumbnail_id = get_post_thumbnail_id( $post->ID );
            // Assegno alla variabile il testo ALT dellimmagine
            $alt_text = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
            // Se l immagine articolo non ha il campo ALT popolato allora procedo, altrimenti esco
            if ( ! $alt_text ) {
                // Assegno alla variabile il titolo del post
                $title = $post->post_title;
                // Assegno all attributo ALT dell immagine il titolo del post
                update_post_meta($thumbnail_id, '_wp_attachment_image_alt', $title);
                // Aggiorno il post
                wp_update_post($post->ID);
            }
        }
    }
}

/**
 * ===========================================================================
 * La funzione restituisce i primi 160 caratteri del riassunto del post o del suo content
 * ===========================================================================
 */
function get_custom_part($custom_id, $custom_part){
    $excerpt = apply_filters('the_'.$custom_part, get_post_field('post_'.$custom_part, $custom_id));;
    $excerpt = preg_replace(" ([.*?])",'',$excerpt);
    $excerpt = strip_shortcodes($excerpt);
    $excerpt = strip_tags($excerpt);
    $excerpt = substr($excerpt, 0, 160);
    $excerpt = substr($excerpt, 0, strripos($excerpt, " "));
    $excerpt = trim(preg_replace( '/\s+/', ' ', $excerpt));
    return $excerpt;
}

/**
 * ===========================================================================
 * La funzione popola la descrizione meta, se non popolata in YOAST, con i primi 
 * 160 caratteri dell excerpt del post, se presente, oppure i primi 160 caratteri del content
 * ===========================================================================
 */
add_action('wp_head','custom_meta_description');
function custom_meta_description(){
    if(is_single()){
        console_log('Is single');
        // Verifico se il plugin YOAST è attivo
        $yoast_active	= false;
        if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
            // Il plugin è attivo, allora assegno true alla variabile
            $yoast_active = true;
        }
        
        // Recupero il riassunto del post
		$cust_excerpt   = get_custom_part($post->ID, 'excerpt');
        // Recupero il contenuto del post
		$cust_content   = get_custom_part($post->ID, 'content');
        
        // Se YOAST è attivo, recupero la meta descrizione di YOAST
		$yoast_meta_description = '';
		if ( $yoast_active ){	
	        // Recupero la descrizione meta di YOAST e la assegno alla variabile $meta_description
    		$yoast_meta_description	.= YoastSEO()->meta->for_post($post->ID)->description;
		}

        // Se la descrizione meta di YOAST è vuota allora ne genero una con il contenuto del riassunto
		if( empty($yoast_meta_description) && !empty($cust_excerpt) ){
            // Inserisco nell header i meta
            $text_test = '<meta class="custom-seo-meta-tag" name="description" content="' . $cust_excerpt  . '" />';
            echo $text_test;
   		}
   		// Se la descrizione di yoast e del riassunto sono vuote, ne genero una con il contenuto del post
   		elseif( empty($yoast_meta_description ) && !empty($cust_content) ){
            // Inserisco nell header i meta
            $text_test = '<meta class="custom-seo-meta-tag" name="description" content="' . $cust_content  . '" />';
            echo $text_test;
   		}
        else{
            //do_nothing
        }	
    }
}
