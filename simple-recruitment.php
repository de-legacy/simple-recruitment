<?php
/**
 * Plugin Name: Simple Recruitment
 * Plugin URI:  http://septianfujianto.com/
 * Description: Simple recruitment plugin for WordPress
 * Version:     0.1
 * Author:      Septian Ahmad Fujianto
 * Author URI:  http://septianfujianto.com
 * @package    simple-rec
 */
//@TODO Create default user for form submit
//@TODO Create setting for email recipient and CC
//@TODO Create redirect / thank you page options

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Link external php files
if ( file_exists( plugin_dir_path( __FILE__ ) . '/vendor/cmb2/init.php' ) ) {
  require_once plugin_dir_path( __FILE__ ) . '/vendor/cmb2/init.php';
} elseif ( file_exists(  plugin_dir_path( __FILE__ ) . '/vendor/CMB2/init.php' ) ) {
  require_once plugin_dir_path( __FILE__ ) . '/vendor/CMB2/init.php';
}

if ( file_exists( plugin_dir_path( __FILE__ )."inc/register-cpt-taxonomy.php" ) ) {
	require_once(plugin_dir_path( __FILE__ )."inc/register-cpt-taxonomy.php");
}

if ( file_exists( plugin_dir_path( __FILE__ )."admin/theme-options-cmb.php" ) ) {
	require_once(plugin_dir_path( __FILE__ )."admin/theme-options-cmb.php");
}


// Register Frontend scripts and styles
function simple_rec_enqueue_script(){   
	// Register Scripts
    wp_enqueue_script( 'jquery-form-validator', plugin_dir_url( __FILE__ ) . 'vendor/node_modules/jquery-form-validator/form-validator/jquery.form-validator.min.js', array('jquery'), '2.3.73', false );
    wp_enqueue_script( 'rec-scripts', plugin_dir_url( __FILE__ ) . 'js/rec-scripts.js', array('jquery'), '1.0.0', false );

    // Register Styles
    wp_register_style( 'style-form-validator', plugin_dir_url( __FILE__ ) . 'vendor/node_modules/jquery-form-validator/form-validator/theme-default.min.css');
    wp_enqueue_style( 'rec-styles', plugin_dir_url( __FILE__ ) . 'css/rec-styles.css');
}

add_action('wp_enqueue_scripts', 'simple_rec_enqueue_script');

function simple_rec_form_html() {
	global $fullname, $email, $message;
	$form_url = get_permalink();
	$form_nonce = wp_nonce_field( 'submit_form', 'form_nonce' );

	$fullname = ( isset( $_POST['fullname'] ) ? $fullname : null );
	$email = ( isset( $_POST['email'] ) ? $email : null );
	$message = ( isset( $_POST['message'] ) ? $message : null );

	$fields = <<<HTML
<div id="content" class="simple-rec-wrapper">
	<span class="title-tes"></span>
	<form action="$form_url" method="post" class="simple-rec-form">
		$form_nonce

		<label for="fullname">Full Name</label>
		<input type="text" name="fullname" id="fullname" value="$fullname" data-validation="length" data-validation-length="min4" required>

		<label for="email">Email Address</label>
		<input type="email" name="email" id="email" value="$email" data-validation="email" required>

		<label for="message">Your Message</label>
		<textarea name="message" id="message" data-validation="length" data-validation-length="min4">$message</textarea>


		<input type="submit" name="send_message" value="Send My Message">
	</form>
</div>	
HTML;

	wp_enqueue_script('jquery-form-validator');
	wp_enqueue_style('style-form-validator');

	echo $fields;

	$validator_scripts = <<<HTML
	<script>
		jQuery.validate({

		});
	</script>
HTML;

	echo $validator_scripts;
}

// Register form Shortcode
function simple_rec_form_fields_shortcode( $atts ){
	ob_start();
    simple_rec_process_form();
    return ob_get_clean();
}

add_shortcode( 'simple_rec', 'simple_rec_form_fields_shortcode' );

// Validate form input on submit
function simple_rec_process_form() {
	global $fullname, $email, $message;

	if ( isset($_POST['send_message']) ) {
		// Check nonce to prevent csrf
		if (!wp_verify_nonce( $_POST['form_nonce'], 'submit_form' )) {
			exit("Invalid input, wrong nonce");
		} else {
        	// Get the form data
			$fullname       =   $_POST['fullname'];
			$email          =   $_POST['email'];
			$message        =   $_POST['message'];

       		// validate the user form input
			simple_rec_validate_form( $fullname, $email, $message );

        	// send the mail / create entry
        	simple_rec_send_mail($fullname, $email, $message);
			simple_rec_create_entry( $fullname, $email, $message );

		}
	}

	simple_rec_form_html(); 
}

function simple_rec_validate_form($fullname, $email, $message) {
	global $form_error;
	$form_error = new WP_Error();

	// Check empty field
	if (empty($fullname) || empty($email) || empty($message)) {
		$form_error->add('empty_field', __('No field should be empty', 'simple-rec'));
	}

     // Check if the email is valid
    if ( ! is_email( $email ) ) {
        $form_error->add( 'invalid_email', 'Email is not valid' );
    }

    // Echo available error
    if (is_wp_error( $form_error )) {
    	foreach ($form_error->get_error_messages() as $error) {
    		echo '<div>';
    		echo '<strong>ERROR</strong>: ';
    		echo $error . '<br/>';
    		echo '</div>';
    	}
    }
}

function simple_rec_create_entry($fullname, $email, $message) {
	global $form_error;

	if (count($form_error->errors) < 1) {
		// Sanitize field
		$fullname = sanitize_text_field($fullname);
		$email = sanitize_text_field($email);
		$message = esc_textarea($subject);

		$post_attr = array(
			'post_type' => 'simple_rec',
			'post_status' => 'publish',
			'post_title' => $fullname.' | '.$email,
			'post_content' => 'Content from Simple rec form: '.$fullname.' '.$email,
			'meta_input' => array (
				"_simple_rec_name" => $fullname,
				"_simple_rec_email" => $email,
			)
		);

		if ( wp_insert_post($post_attr) ) {
			echo "You are registered.";
			$_POST = array();
			// Redirect here
		}
	}
}

function simple_rec_send_mail($fullname, $email, $message) {
	global $form_error;

	if (count($form_error->errors) < 1) {
		$fullname = sanitize_text_field($fullname);
		$email = sanitize_text_field($email);
		$message = esc_textarea($subject);

		// set the variable argument use by the wp_mail
        $message    =  'Content from Simple rec form: '.$fullname.' '.$email;
        $to         =  simple_rec_get_option('_simple_rec_email_recipient');
        $subject    =  "Receuitment: $fullname <$email>"; 
        $headers    =  "From: $fullname <$email>" . "\r\n";
         
        // If email has been process for sending, display a success message 
        if ( wp_mail( $to, $subject, $message, $headers) ) {
            echo "Thanks for contacting me.";
        }
	}
}