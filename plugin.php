<?php
/*
Plugin Name: Shoestrap Product Preview
Plugin URI: http://wpmu.io
Description: Add some product screenshots
Version: 0.1
Author: Aristeides Stathopoulos
Author URI:  http://aristeides.com
GitHub Plugin URI:   https://github.com/shoestrap/shoestrap-gridder
*/

if ( !defined( 'SPP_PLUGIN_URL' ) )
	define( 'SPP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Calls the class on the post edit screen.
 */
function call_Shoestrap_SCRN() {
	new Shoestrap_SCRN();
}

if ( is_admin() ) {
	add_action( 'load-post.php', 'call_Shoestrap_SCRN' );
	add_action( 'load-post-new.php', 'call_Shoestrap_SCRN' );
}

/** 
 * The Class.
 */
class Shoestrap_SCRN {

	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ) );
	}

	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box( $post_type ) {
		add_meta_box(
			'ssscrenshot_meta_box',
			__( 'Preview URL', 'ssscrenshot' ),
			array( $this, 'render_meta_box_content' ),
			$post_type,
			'advanced',
			'high'
		);
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save( $post_id ) {
	
		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['ssscrenshot_inner_custom_box_nonce'] ) ) return $post_id;

		$nonce = $_POST['ssscrenshot_inner_custom_box_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'ssscrenshot_inner_custom_box' ) ) return $post_id;

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) return $post_id;
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) return $post_id;
		}

		/* OK, its safe for us to save the data now. */

		// Sanitize the user input.
		$data = wp_kses_post( $_POST['ssscrenshot_content'] );

		// Update the meta field.
		update_post_meta( $post_id, '_ssscrenshot_meta_value_key', $data );
	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {
	
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'ssscrenshot_inner_custom_box', 'ssscrenshot_inner_custom_box_nonce' );

		// Use get_post_meta to retrieve an existing value from the database.
		$value = get_post_meta( $post->ID, '_ssscrenshot_meta_value_key', true );

		// Display the form, using the current value.
		echo '<input type="text" id="ssscrenshot_content" name="ssscrenshot_content" value="' . esc_attr( $value ) . '" style="width: 100%;" />';
	}
}

/*
 * Checks if a custom Jumbotron content exists
 */
function ssscrenshot_check_preview_content( $id ) {
	$data  = get_post_meta( $id, '_ssscrenshot_meta_value_key', true );
	$value = ( !empty( $data ) ) ? true : false;
	return $value;
}


/*
 * Render the content.
 */
function ssscrenshot_preview_content() {
	global $post;

	// Check if a custom Jumbotron content exists
	if ( !ssscrenshot_check_preview_content( $post->ID ) )
		return;

	if ( !is_singular() )
		return;

	$content      = get_post_meta( $post->ID, '_ssscrenshot_meta_value_key', true );
	?>
<!-- IE8 BugFixes thanks to @ingozoell details are  https://github.com/justincavery/am-i-responsive/issues/2?utm_source=buffer&utm_campaign=Buffer&utm_content=buffer8b8d6&utm_medium=twitter -->
<!--[if IE 8]>
<style>
.desktop iframe {-ms-filter: "progid:DXImageTransform.Microsoft.Matrix(M11=0.3181, M12=0, M21=0, M22=0.3181, SizingMethod='auto expand')";}
.laptop iframe {-ms-filter: "progid:DXImageTransform.Microsoft.Matrix(M11=0.277, M12=0, M21=0, M22=0.277, SizingMethod='auto expand')";}
.tablet iframe {-ms-filter: "progid:DXImageTransform.Microsoft.Matrix(M11=0.234, M12=0, M21=0, M22=0.234, SizingMethod='auto expand')";}
.mobile iframe {-ms-filter: "progid:DXImageTransform.Microsoft.Matrix(M11=0.219, M12=0, M21=0, M22=0.219, SizingMethod='auto expand')";}
</style>
<![endif]-->
<section class="display">
	<div class="desktop"><div class="trim"><iframe id="desktop" src="<?php echo $content; ?>"></iframe></div></div>
	<div class="laptop"><div class="trim"><iframe id="laptop" src="<?php echo $content; ?>"></iframe></div></div>
	<div class="tablet"><div class="trim"><iframe id="tablet" src="<?php echo $content; ?>"></iframe></div></div>
	<div class="mobile"><div class="trim"><iframe id="mobile" src="<?php echo $content; ?>"></iframe></div></div>
</section>
<?php
}
add_action( 'shoestrap_pre_main', 'ssscrenshot_preview_content', 10 );

function shoestrap_preview_enqueue_scripts() {
	global $post;

	// Check if a custom Jumbotron content exists
	if ( !ssscrenshot_check_preview_content( $post->ID ) )
		return;

	wp_enqueue_style( 'preview_styles', SPP_PLUGIN_URL . '/style.css', false, null );
	wp_register_script( 'preview_script', SPP_PLUGIN_URL . '/script.js', false, null, false );
	wp_enqueue_script( 'preview_script' );
	wp_enqueue_script( 'jquery-ui-draggable' );
}
add_action( 'wp_enqueue_scripts', 'shoestrap_preview_enqueue_scripts', 130 );
