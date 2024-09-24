<?php
/**
 * Filename: new-user-email.php
 * Description: silence is golden.
 *
 * @package WP_Easy_Pay
 */

/**
 * Sends a new user email notification.
 *
 * @param string $username  The username of the new user.
 * @param string $password  The password of the new user.
 * @param string $email     The email address of the new user.
 */
function wpep_new_user_email_notification( $username, $password, $email ) {
	$to          = $email;
	$subject     = 'Your login details for ' . site_url();
	$body        = 'username:' . $username . ' Password:' . $password;
	$headers     = array( 'Content-Type: text/html; charset=UTF-8' );
	$headers_str = 'From: ' . get_bloginfo( 'name' ) . ' <' . wp_strip_all_tags( $from ) . ">\r\n";
	$headers    .= $headers_str;

	wp_mail( $to, $subject, $body, $headers );
}

