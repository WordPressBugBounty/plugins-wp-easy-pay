<?php
/**
 * Filename: tabs-list-and-switches.php
 * Description: tabs list and switches backend.
 *
 * @package WP_Easy_Pay
 */

$form_id = get_the_ID();

echo '<div style="text-align: center;"><span class="wpep_tags www"> <h4> [wpep-form id="' . esc_attr( $form_id ) . '"] </h4></span></div>';
