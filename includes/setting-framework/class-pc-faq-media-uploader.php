<?php
/**
 * @package   Options_Framework
 * @license   GPL-2.0+
 */

class Pcs_Framework_Media_Uploader {


	/**
	 * Media Uploader Using the WordPress Media Library.
	 *
	 * Parameters:
	 *
	 * string $_id - A token to identify this field (the name).
	 * string $_value - The value of the field, if present.
	 * string $_desc - An optional description of the field.
	 *
	 */

	static function pcs_framework_uploader( $_id, $_value, $_desc = '', $_name = '' ) {

		$optionsframework_settings = get_option( 'optionsframework' );

		// Gets the unique option id
		$option_name = $optionsframework_settings['id'];

		$output = '';
		$id = '';
		$class = '';
		$int = '';
		$value = '';
		$name = '';

		$id = strip_tags( strtolower( $_id ) );

		// If a value is passed and we don't have a stored value, use the value that's passed through.
		if ( $_value != '' && $value == '' ) {
			$value = $_value;
		}

		if ($_name != '') {
			$name = $_name;
		}
		else {
			$name = $option_name.'['.$id.']';
		}

		if ( $value ) {
			$class = ' has-file';
		}
		$output .= '<input id="' . $id . '" class="upload' . $class . '" type="text" name="'.$name.'" value="' . $value . '" placeholder="' . __('No file chosen', PC_FAQ_TEXT_DOMAIN) .'" />' . "\n";
		if ( function_exists( 'wp_enqueue_media' ) ) {
			if ( ( $value == '' ) ) {
				$output .= '<input id="upload-' . $id . '" class="upload-button button" type="button" value="' . __( 'Upload', PC_FAQ_TEXT_DOMAIN ) . '" />' . "\n";
			} else {
				$output .= '<input id="remove-' . $id . '" class="remove-file button" type="button" value="' . __( 'Remove', PC_FAQ_TEXT_DOMAIN ) . '" />' . "\n";
			}
		} else {
			$output .= '<p><i>' . __( 'Upgrade your version of WordPress for full media support.', PC_FAQ_TEXT_DOMAIN ) . '</i></p>';
		}

		if ( $_desc != '' ) {
			$output .= '<span class="of-metabox-desc">' . $_desc . '</span>' . "\n";
		}

		$output .= '<div class="screenshot" id="' . $id . '-image">' . "\n";

		if ( $value != '' ) {
			$remove = '<a class="remove-image">Remove</a>';
			$image = preg_match( '/(^.*\.jpg|jpeg|png|gif|ico*)/i', $value );
			if ( $image ) {
				$output .= '<img src="' . $value . '" alt="" />' . $remove;
			} else {
				$parts = explode( "/", $value );
				for( $i = 0; $i < sizeof( $parts ); ++$i ) {
					$title = $parts[$i];
				}

				// No output preview if it's not an image.
				$output .= '';

				// Standard generic output if it's not an image.
				$title = __( 'View File', PC_FAQ_TEXT_DOMAIN );
				$output .= '<div class="no-image"><span class="file_link"><a href="' . $value . '" target="_blank" rel="external">'.$title.'</a></span></div>';
			}
		}
		$output .= '</div>' . "\n";
		return $output;
	}
	
}