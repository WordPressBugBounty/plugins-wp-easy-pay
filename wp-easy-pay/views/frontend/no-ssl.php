<p> Sorry, your website is not using HTTPS. Please click the link below to convert your website to HTTPS.</p>
<?php
/**
 * Filename: no-ssl.php
 * Description: no ssl.
 *
 * @package WP_Easy_Pay
 */

?>
<?php
$site_url = 'https://' . ( isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '' )
. ( isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '' );
?>
<a href="<?php echo esc_url( $site_url ); ?>" class="form-control"> Convert to HTTPS (Secure)</a>
