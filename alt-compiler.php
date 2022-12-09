<?php
/**
 * @package Alt Compiler
 */

/*
Plugin Name:  Alt Compiler
Plugin URI:   https://www.enricomarogna.com 
Description:  This plugin takes care of filling in the ALT fields of featured images, but only if they are left blank. The ALT field is populated with the title of the post. 
Version:      1.0
Author:       Enrico Marogna 
Author URI:   https://www.enricomarogna.com
License:      GPL 3.0
License URI:  http://www.gnu.org/licenses/gpl-3.0.txt
Text Domain:  alt-compiler
Domain Path:  /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
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
    foreach($posts_array as $post_array){
        // Eseguo solo per i post che hanno una immagine in evidenza
        if ( has_post_thumbnail( $post_array->ID ) ) {
            // Assegno alla variabile l'id dell'immagine
            $thumbnail_id = get_post_thumbnail_id( $post_array->ID );
            // Assegno alla variabile il testo ALT dell'immagine
            $alt_text = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
            // Se l'immagine articolo non ha il campo ALT popolato allora procedo, altrimenti esco
            if ( ! $alt_text ) {
                // Assegno alla variabile il titolo del post
                $title = $post_array->post_title;
                // Assegno all'attributo ALT dell'immagine il titolo del post
                update_post_meta($thumbnail_id, '_wp_attachment_image_alt', $title);
                // Aggiorno il post
                wp_update_post($post_array->ID);
            }
        }
    }
}
