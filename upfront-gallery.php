<?php
/*
Plugin Name: UpFront Galerie Plugin
Plugin URI: http://n3rds.work
Description: Einfach, flexibel und leistungsstark - Das ultimative Galeriesystem für UpFront.
Version: 1.0.4
Author: WMS N@W
Author URI: https://n3rds.work
License: GNU GPL v2
*/
require 'psource-plugin-update/plugin-update-checker.php';
$MyUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://n3rds.work//wp-update-server/?action=get_metadata&slug=upfront-gallery', 
	__FILE__, 
	'upfront-gallery' 
);
/*

PUR = UPFRONT UNLIMITED ROCKET

*/

define('UPFRONT_GALLERY_VERSION', '1.0.4');
define('UPFRONT_GALLERY_PATH', plugin_dir_path(__FILE__));
define('UPFRONT_GALLERY_URL', plugin_dir_url(__FILE__));

/* we call the Butler framework */
include(UPFRONT_GALLERY_PATH . 'butler/butler.php');

if ( version_compare( BUTLER_VERSION, '1.2.1', '<' ) )
	return;

/* we call the UpFrontRocket framework */
butler_load(UPFRONT_GALLERY_PATH . 'upfrontrocket/upfrontrocket');

if ( version_compare( UPFRONTROCKET_VERSION, '1.1.1', '<' ) )
	return;

/* register block
***************************************************************/
add_action('after_setup_theme', 'upfront_gallery_register');

function upfront_gallery_register() {

	if ( !class_exists('UpFront') )
		return;

	require_once 'block-styling.php';	
	require_once 'block-options.php';
	require_once 'gallery-display.php';
	require_once 'depreciate.php';

	$class = 'UpFrontGalleryBlock';
	$block_type_url = substr(WP_PLUGIN_URL . '/' . str_replace(basename(__FILE__), '', plugin_basename(__FILE__)), 0, -1);		
	$class_file = __DIR__ . '/block.php';
	$icons = array(
			'path' => __DIR__,
			'url' => $block_type_url
		);

	upfront_register_block(
		$class,
		$block_type_url,
		$class_file,
		$icons
	);

	/**
	 *
	 * Check if there is the UpFront Loader
	 *
	 */		
	if ( version_compare(UPFRONT_VERSION, '1.2.0', '<=') ){			
		include_once $class_file;
	}

}


/* include wp admin scripts
***************************************************************/
add_action('admin_enqueue_scripts', 'upfront_gallery_wp_scripts');

function upfront_gallery_wp_scripts($hook) {

	global $post;

	/* we register all our scripts */
	wp_register_script('upfront-gallery-wp-admin-js', plugins_url('/admin/js/wp-admin.js', __FILE__), array('jquery'), UPFRONT_GALLERY_VERSION);
	wp_register_script('pur-wp-uploader-js', plugins_url('/admin/js/wp-updoader.js', __FILE__), array('jquery','media-upload','thickbox'), UPFRONT_GALLERY_VERSION);

	/* we register all our stylesheets */
	wp_register_style( 'upfront-gallery-wp-admin-css', plugins_url('/admin/css/wp-admin.css', __FILE__), false, UPFRONT_GALLERY_VERSION );
	wp_register_style( 'upfront-gallery-wp-gallery-css', plugins_url('/admin/css/wp-gallery.css', __FILE__), false, UPFRONT_GALLERY_VERSION );
	wp_register_style( 'pur-wp-attachment-css', plugins_url('/admin/css/wp-attachment.css', __FILE__), false, UPFRONT_GALLERY_VERSION );
	wp_register_style( 'pur-wp-uploader-css', plugins_url('/admin/css/wp-uploader.css', __FILE__), false, UPFRONT_GALLERY_VERSION );

	/* we enqueue the admin css */
	wp_enqueue_style('upfront-gallery-wp-admin-css');

	if ( version_compare( get_bloginfo( 'version' ), '3.8', '<' ) )
		wp_enqueue_style( 'upfront-gallery-depreciate-wp-admin-css', plugins_url('/admin/css/depreciate-wp-admin.css', __FILE__), false, UPFRONT_GALLERY_VERSION );

	/* we only enqueue the gallery js and css on our gallery plugin */
	if ( isset($post->post_type) && $post->post_type == 'upfront_gallery' ) {

		wp_enqueue_style('upfront-gallery-wp-gallery-css');
		wp_enqueue_script('upfront-gallery-wp-admin-js');

	}

	/* we only enqueue uploader js and css on if it is open in an iframe */
	if ( isset($_GET['upfront_media_editor']) && $_GET['upfront_media_editor'] == true ) {

		wp_enqueue_style('pur-wp-uploader-css');
		wp_enqueue_script('pur-wp-uploader-js');

	}

	/* we only enqueue the gallery attachment css for the media custom field */
	if ( isset($post->post_type) && $post->post_type == 'attachment' )
		wp_enqueue_style('pur-wp-attachment-css');

}


/* we add the meta boxes to post type(s) set
***************************************************************/
add_action('admin_head', 'upfront_gallery_wp_head');

function upfront_gallery_wp_head() {

	$options = wp_parse_args( wp_get_referer() );

	$output = '<script type="text/javascript">';

		$output .= 'upfront_admin_url = "' .  admin_url() . '";';

		if ( isset($options['upfront_action']) && $options['upfront_action'] === 'done_editing' )
			$output .= 'self.parent.tb_remove();';

	$output .= '</script>';

	if ( isset($options['upfront_action']) && $options['upfront_action'] === 'done_editing')
		$output .= '<style type="text/css"">body { display:none; }</style>';

	echo $output;

}


/* create custom post type
***************************************************************/
add_action('init', 'upfront_gallery_init');

function upfront_gallery_init() {
	/* we register the post type */
    $args = array(
        'public' => true,
        'publicly_queryable' => true,
        'show_in_nav_menus' => true,
        'show_in_menu' => true,
        'labels' => array(
            'name' => _x('Alben', 'upfront-gallery'),
            'singular_name' => _x('Album', 'upfront-gallery'),
            'add_new' => _x('Neues Album hinzufügen', 'upfront-gallery'),
            'add_new_item' => sprintf( __( 'Neues %s hinzufügen', 'upfront-gallery' ), __( 'Album', 'upfront-gallery' ) ),
            'edit_item' => sprintf( __( '%s bearbeiten', 'upfront-gallery' ), __( 'Album', 'upfront-gallery' ) ),
            'new_item' => sprintf( __( 'Neues %s', 'upfront-gallery' ), __( 'Album', 'upfront-gallery' ) ),
            'all_items' => sprintf( __( 'Alle %s', 'upfront-gallery' ), __( 'Alben', 'upfront-gallery' ) ),
            'view_item' => sprintf( __( '%s anzeigen', 'upfront-gallery' ), __( 'Album', 'upfront-gallery' ) ),
            'search_items' => sprintf( __( 'Suche %a', 'upfront-gallery' ), __( 'Alben', 'upfront-gallery' ) ),
            'not_found' =>  sprintf( __( 'Keine %s gefunden', 'upfront-gallery' ), __( 'Album', 'upfront-gallery' ) ),
            'not_found_in_trash' => sprintf( __( 'Keine %s im Papierkorb gefunden', 'upfront-gallery' ), __( 'Album', 'upfront-gallery' ) ),
            'parent_item_colon' => '',
            'menu_name' => __( 'Galerie', 'upfront-gallery' )
        ),
        'supports' => array(
            'title',
            'thumbnail'
        ),
        'has_archive' => true,
        'taxonomies' => array('gallery_categories', 'gallery_tags'),
        'menu_position' => 20,
        'rewrite' => array('slug' => 'albums') ,
        'menu_icon' => 'dashicons-format-gallery'
    );

    register_post_type('upfront_gallery', $args);

    /* we create our own taxonomie for the categories */
    $labels = array(
        'name'                       => _x('Albumkategorien', 'upfront_gallery'),
        'singular_name'              => _x('Albumkategorie', 'upfront_gallery'),
        'search_items'               => __('Suche nach Albumkategorien', 'upfront_gallery'),
        'popular_items'              => __('Beliebte Albumkategorien', 'upfront_gallery'),
        'all_items'                  => __('Alle Albumkategorien', 'upfront_gallery'),
        'parent_item'                => __('Übergeordnete Albumkategorie', 'upfront_gallery'),
        'edit_item'                  => __('Albumkategorie bearbeiten', 'upfront_gallery'),
        'update_item'                => __('Albumkategorie aktualisieren', 'upfront_gallery'),
        'add_new_item'               => _x('Neue Albumkategorie hinzufügen', 'upfront_gallery'),
        'new_item_name'              => __('Neue Albumkategorie', 'upfront_gallery'),
        'separate_items_with_commas' => __('Separate Albumkategorien mit Kommas', 'upfront_gallery'),
        'add_or_remove_items'        => __('Hinzufügen oder Entfernen von Albumkategorien', 'upfront_gallery'),
        'choose_from_most_used'      => __('Wähle aus den am häufigsten verwendeten Albumkategorien', 'upfront_gallery')
    );
    $args = array(
        'labels'                     => $labels,
        'public'                     => true,
        'hierarchical'               => true,
        'show_ui'                    => true,
        'show_in_nav_menus'          => true,
        'query_var'                  => true,
        'rewrite' 					 => array('slug' => 'album-categories')
    );

    register_taxonomy( 'gallery_categories', 'upfront_gallery', $args );

    /* we create our own taxonomie for the tags */
    $labels = array(
        'name'                       => _x( 'Album-Tags', 'upfront_gallery' ),
        'singular_name'              => _x( 'Album-Tag', 'upfront_gallery' ),
        'search_items'               => __( 'Suche nach Album-Tags', 'upfront_gallery' ),
        'popular_items'              => __( 'Beliebte Album-Tags', 'upfront_gallery' ),
        'all_items'                  => __( 'Alle Album-Tags', 'upfront_gallery' ),
        'parent_item'                => __( 'Übergeordnetes Album-Tag', 'upfront_gallery' ),
        'edit_item'                  => __( 'Album-Tag bearbeiten', 'upfront_gallery' ),
        'update_item'                => __( 'Album-Tag aktualisieren', 'upfront_gallery' ),
        'add_new_item'               => _x( 'Neues Album-Tag hinzufügen', 'upfront_gallery' ),
        'new_item_name'              => __( 'Neues Album-Tag', 'upfront_gallery' ),
        'separate_items_with_commas' => __( 'Separate Album-Tags mit Kommas', 'upfront_gallery' ),
        'add_or_remove_items'        => __( 'Hinzufügen oder Entfernen von Album-Tags', 'upfront_gallery' ),
        'choose_from_most_used'      => __( 'Wähle aus den am häufigsten verwendeten Album-Tags', 'upfront_gallery' )
    );
    $args = array(
        'labels'                     => $labels,
        'public'                     => true,
        'hierarchical'               => false,
        'show_ui'                    => true,
        'show_in_nav_menus'          => true,
        'query_var'                  => true,
        'rewrite' 					 => array('slug' => 'album-tags')
    );

    register_taxonomy( 'gallery_tags', 'upfront_gallery', $args );

}


/* if no category is selected we set it to uncategorized */
add_action('publish_upfront_gallery', 'upfront_gallery_default_category');

function upfront_gallery_default_category($upfront_gallery_id) {

	if(!has_term('', 'gallery_categories', $upfront_gallery_id)){

		wp_set_object_terms($upfront_gallery_id, array('Unkategorisiert'), 'gallery_categories');
	}

}


/* we add our custom columns for the gallery post type */
add_filter( 'manage_edit-upfront_gallery_columns', 'upfront_gallery_set_columns' );

function upfront_gallery_set_columns($columns) {

	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Titel' ),
		'images' => __( 'Bilder' ),
		'gallery-categories' => __( 'Albumkategorien' ),
		'gallery-tags' => __( 'Album-Tags' ),
		'date' => __( 'Datum' )
	);


    return $columns;

}


/* we create the output for our custom columns */
add_action( 'manage_upfront_gallery_posts_custom_column', 'upfront_gallery_fill_columns' );

function upfront_gallery_fill_columns($column) {

	global $post;

    switch($column) {

    	case 'images' :

    		$album_imgs = get_post_meta( $post->ID, 'upfront_gallery_image', true );

			if ( !empty($album_imgs) ) {

				$album_img = array_slice($album_imgs, 0, 4);

			    foreach ( $album_img as $i => $album_img_id ) {

			    	$get_album_img = get_post( $album_img_id );
			    	$album_img_src = wp_get_attachment_image_src( $album_img_id, 'full' );
			    	$album_img_src = upfront_resize_image($album_img_src[0], 40, 40, true);

			    	echo '<div class="thumbnail-wrap"><div class="thumbnail"><img src="' . $album_img_src . '" /></div></div>';

			    }

			    $nbr_images = count($album_imgs) . ' Bilder';

			} else {

				$album_img = array();

				$nbr_images = 'kein Bild';

			}

			for ($i = 1; $i <= 4 - count($album_img); $i++)
			    echo '<div class="thumbnail-wrap"><div class="thumbnail"><img src="' .  plugins_url('/admin/images/no-image.png', __FILE__) . '" ></div></div>';

			echo '<a class="nbr-images" href="' . get_site_url() .'/wp-admin/post.php?post=' . $post->ID . '&action=edit">' . $nbr_images . '</span>';

        break;

        case 'featured-image' :

            if ( has_post_thumbnail($post->ID) )
                echo get_the_post_thumbnail($post->ID, array(30, 30));

            else
                echo 'Kein empfohlenes Bild';

        break;

        case 'gallery-categories' :

        	$taxonomy_type = 'gallery_categories';
        	$empty         = 'Keine Albumkategorie';

			echo upfront_gallery_taxonomy_columns_output($taxonomy_type, $empty);

        break;

        case 'gallery-tags':

        	$taxonomy_type = 'gallery_tags';
        	$empty         = 'Kein Album-Tag';

        	echo upfront_gallery_taxonomy_columns_output($taxonomy_type, $empty);

        break;

    }
}


/* we build the taxonomy culomns ouput */
function upfront_gallery_taxonomy_columns_output($taxonomy_type, $empty) {

	global $post;
	global $typenow;

	$terms = get_the_terms($post->ID, $taxonomy_type);

	if ( $terms ) {
		$out = array();

		foreach ( $terms as $term ) {

			$out[] = '<a href="edit.php?post_type=' . $typenow . '&' . $taxonomy_type .'=' . $term->slug . '">' . esc_html(sanitize_term_field('name', $term->name, $term->term_id, $taxonomy_type, 'display')) . '</a>';

		}

		$return = join( ', ', $out );

	} else {

		$return = $empty;

	}

	return $return;
}


/* we register the meta boxes */
add_action('init', 'upfront_gallery_meta_box');

function upfront_gallery_meta_box() {

	global $post;

	$gallery_meta_boxes = array();

	$gallery_meta_boxes[] = array(
		'id' => 'display_upfront_gallery',
		'title' => 'Album',
		'pages' => array('upfront_gallery'),
		'context' => 'normal',
		'priority' => 'high',
		'fields' => array(
			array(
				'name' => '',
				'desc' => '',
				'id' => 'upfront_gallery_count',
				'type' => 'thumbnail-count',
				'std' => 'kein Bild',
			),
			array(
				'name' => '',
				'desc' => 'Füge Bilder hinzu, die für Dein Album verwendet werden.',
				'id' => 'upfront_gallery_image',
				'type' => 'gallery',
				'std' => '',
			),
		)
	);

	$gallery_meta_boxes[] = array(
		'id' => 'display_upfront_gallery_options',
		'title' => 'Albumblockoptionen',
		'pages' => array('upfront_gallery'),
		'context' => 'normal',
		'priority' => 'high',
		'fields' => array(
			array(
				'name' => 'Albumtitel<br>',
				'desc' => 'Gib Deinen Albumtitel ein, der verwendet wird, wenn Du die Alben als Miniaturansichten anzeigen möchtest.<br>',
				'id' => 'upfront_gallery_caption',
				'type' => 'text',
				'std' => '',
			),
			array(
				'name' => 'Albumbeschreibung',
				'desc' => 'Beschreibe Dein Album.<br>',
				'id' => 'upfront_gallery_description',
				'type' => 'wysiwyg',
				'std' => '',
			),
			array(
				'name' => 'Benutzerdefinierter Readon-Link<br>',
				'desc' => 'Gib hier Deinen benutzerdefinierten Readon-Link ein. Lasse es leer, wenn Du das Standardverhalten beibehalten möchtest.<br>',
				'id' => 'upfront_gallery_readon_link',
				'type' => 'text',
				'std' => '',
			),
		)
	);

	foreach ( $gallery_meta_boxes as $gallery_meta_box ) {

		$my_box = new UpFrontGalleryMetaBox($gallery_meta_box);

	}

}


/* we create the meta boxes */
class UpFrontGalleryMetaBox {

	protected $_gallery_meta_box;


	function __construct($gallery_meta_box) {

		if ( !is_admin() )
			return;

		$this->_gallery_meta_box = $gallery_meta_box;

		add_action('admin_menu', array(&$this, 'add'));
		add_action('save_post', array(&$this, 'save'));
		add_filter( 'attachment_fields_to_edit', array(&$this, 'attachment_fields_edit'), 10, 2);
		add_filter( 'attachment_fields_to_save', array(&$this, 'attachment_fields_save'), 10, 2);

	}


	/* we add the meta boxes to post type(s) set */
	function add() {

		$this->_gallery_meta_box['context'] = empty($this->_gallery_meta_box['context']) ? 'normal' : $this->_gallery_meta_box['context'];

		$this->_gallery_meta_box['priority'] = empty($this->_gallery_meta_box['priority']) ? 'high' : $this->_gallery_meta_box['priority'];

		foreach ( $this->_gallery_meta_box['pages'] as $page ) {

			add_meta_box($this->_gallery_meta_box['id'], $this->_gallery_meta_box['title'], array(&$this, 'show'), $page, $this->_gallery_meta_box['context'], $this->_gallery_meta_box['priority']);

		}

	}


	/* we built the metabox content */
	function show() {

		global $post;

		/* we use nonce for verification */
		$output = '<input type="hidden" name="meta_box_nonce" value="' . wp_create_nonce(basename(__FILE__)) . '" />';

		$output .= '<div class="upfront-gallery-options">';

		foreach ( $this->_gallery_meta_box['fields'] as $field ) {

			/* we get current post meta data */
			$meta = get_post_meta($post->ID, $field['id'], true);

			$output .= $field['name'] ? '<label for="' . $field['id'] . '">' . $field['name'] . '</label>' : '';

			switch ($field['type']) {

				case 'text':
					$value = $meta ? $meta : $field['std'];

					$output .= '<input type="text" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $value . '" size="30" />';
					$output .= '<span class="pur-field-description">' . $field['desc'] . '</span>';

				break;

				case 'thumbnail-count':

					$value = $meta != 0 ? $meta : $field['std'];

					$output .= '<input type="hidden" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $meta . '" size="30" />';

					$output .= '<span class="pur-thumbnail-count"><strong>Images: </strong><span>' . $value . '</span></span>';

				break;

				case 'wysiwyg':

					$meta = html_entity_decode($meta, ENT_QUOTES, 'UTF-8');
					$value = $meta ? $meta : $field['std'];
			        $settings = array(
			        		'wpautop' => false,
			        		'media_buttons' => false,
			        	);

			       	ob_start();
			       		wp_editor( $value, $field['id'], $settings );
			       		$output .= ob_get_contents();
			       	ob_end_clean();

			       	$output .= '<span class="pur-field-description">' . $field['desc'] . '</span>';

				break;

				case 'gallery':

					$thumbnail_count = get_post_meta($post->ID, 'upfront_gallery_count', true);

					$src = '';

					$thumbnail_option = array(
					    'width' => get_option('thumbnail_size_w'),
					    'height' => get_option('thumbnail_size_h'),
					    'crop' => get_option('thumbnail_crop'),
					);

					$output .= '<input class="pur-upload-image button button-primary" type="button" value="Bild hinzufügen" /><span class="drag-notice"> Zum Neuordnen per Drag & Drop verschieben</span>';

					$output .= '<div data-thumb-w="' . $thumbnail_option['width'] . '" data-thumb-h="' . $thumbnail_option['height'] . '" class="pur-thumbnails ui-sortable">';

					$output .= '<input type="hidden" name="' . $field['id'] . '" value="" />';

					if ( $meta ) {

						foreach ( $meta as $attachment => $id ) {

							if ( $id ) {

								$img = wp_get_attachment_image_src( $id, 'full' );
								$img_src = $img[0];
								$img_w = $img[1];
								$img_h = $img[2];
								$thumb_crop = $thumbnail_option['crop'] == 1 ? true : false;

								if ( $img_src ) {

									$img_src = upfront_resize_image($img_src, $thumbnail_option['width'], $thumbnail_option['height'], $thumb_crop);

									$output .= '<div class="pur-thumbnail" style="width: ' . $thumbnail_option['width'] . 'px; height: ' . $thumbnail_option['height'] . 'px">';

										$output .= '<input class="pur-image-value" type="hidden" name="' . $field['id'] . '[]" value="' . $id .'" />';
										$output .= '<div class="pur-image-wrap">';

											$output .= '<img src="' . $img_src . '" />';

										$output .= '</div>';

										$output .= '<div class="pur-thumbnail-toolbar">';
											$output .= '
												<ul>
													<li><a href="#" class="pur-drag pur-btn">Ziehen</a></li>
													<li><a href="#" class="pur-edit pur-btn">Bearbeiten</a></li>
													<li><a href="#" class="pur-remove pur-btn">Entfernen</a></li>
												</ul>';
										$output .= '</div>';

									$output .= '</div>';

								}

							}

						}

					}

					$output .= '<div class="pur-no-thumbnail">
									<p>Du hast kein Bild ausgewählt!</p>
									<p><a href="#" class="pur-upload-image browser button button-hero">Bild hinzufügen</a></p>
								</div>';

					$output .= '</div>';

					$output .= '<span class="pur-field-description">' . $field['desc'] . '</span>';

				break;

			}

		}

		$output .= '</div>';

		echo $output;

	}


	/* we make sure the metabox content can be modified and saved */
	function save($post_id) {

		/* we verify the nonce before proceeding. */
		if ( !isset($_POST['meta_box_nonce']) || !wp_verify_nonce($_POST['meta_box_nonce'], basename(__FILE__)) )
			return $post_id;

		/* we verify if this is an auto save routine. If it is our form has not been submitted, so we dont want to do anything */
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
			return $post_id;

		/* we check permissions */
		if ( 'page' == $_POST['post_type'] ) {

			if ( !current_user_can('edit_page', $post_id) )
				return $post_id;

		} elseif ( !current_user_can('edit_post', $post_id) ) {

			return $post_id;

		}

		foreach ( $this->_gallery_meta_box['fields'] as $field ) {

			$name = $field['id'];

			$old = get_post_meta($post_id, $name, true);
			$new = $_POST[$field['id']];

			if ( $field['type'] == 'wysiwyg' )
				$new = wpautop($new);

			if ( $new && $new != $old )
				update_post_meta($post_id, $name, $new);

			elseif ( '' == $new && $old )
				delete_post_meta($post_id, $name, $old);

		}

	}


	/* we add a custom field to an attachment */
	function attachment_fields_edit($form_fields, $post) {

	    $form_fields['pur-custom-link']['label'] = __( 'Benutzerdefinierten Link', 'upfront_gallery' );
	    $form_fields['pur-custom-link']['value'] = get_post_meta($post->ID, '_upfront_custom_link', true);
	    $form_fields['pur-custom-link']['helps'] = __( 'Hinzugefügt von UpFrontRocket Gallery Block', 'upfront_gallery' );

	    return $form_fields;
	}


	/* we make sure the attachment custom field can be modified and saved */
	function attachment_fields_save($post, $attachment) {

	    if ( isset($attachment['pur-custom-link']) )
	        update_post_meta($post['ID'], '_upfront_custom_link', $attachment['pur-custom-link']);

	    return $post;

	}

}