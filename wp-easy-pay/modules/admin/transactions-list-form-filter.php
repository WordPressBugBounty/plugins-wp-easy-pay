<?php
/**
 * Transactions (wpep_reports) list: dropdown to filter by payment form.
 *
 * Uses the same GET parameter as All Forms → Transactions deep link:
 * {@see WPEP_REPORTS_FILTER_FORM_QUERY_ARG} in all-forms-transactions-column.php.
 *
 * @package WP_Easy_Pay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prints form filter dropdown next to built-in list filters (month, etc.).
 *
 * @param string $post_type Current list table post type.
 * @return void
 */
function wpep_transactions_list_form_filter_dropdown( $post_type ) {
	if ( 'wpep_reports' !== $post_type ) {
		return;
	}

	if ( ! defined( 'WPEP_REPORTS_FILTER_FORM_QUERY_ARG' ) ) {
		return;
	}

	$post_type_object = get_post_type_object( 'wpep_reports' );
	if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->edit_posts ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$current_form_id = isset( $_GET[ WPEP_REPORTS_FILTER_FORM_QUERY_ARG ] )
		? absint( wp_unslash( $_GET[ WPEP_REPORTS_FILTER_FORM_QUERY_ARG ] ) )
		: 0;

	$forms = get_posts(
		array(
			'post_type'              => 'wp_easy_pay',
			'posts_per_page'         => -1,
			'post_status'            => array( 'publish', 'draft', 'pending', 'private' ),
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	?>
	<span class="wpep-transactions-form-filter-wrap">
		<label for="wpep-transactions-form-filter" class="screen-reader-text">
			<?php esc_html_e( 'Filter transactions by form', 'wp_easy_pay' ); ?>
		</label>
		<select id="wpep-transactions-form-filter" name="<?php echo esc_attr( WPEP_REPORTS_FILTER_FORM_QUERY_ARG ); ?>" class="wpep-transactions-form-filter-select">
			<option value=""><?php esc_html_e( 'All forms', 'wp_easy_pay' ); ?></option>
			<?php
			foreach ( $forms as $form ) {
				if ( ! current_user_can( 'edit_post', $form->ID ) ) {
					continue;
				}
				$title = get_the_title( $form );
				if ( '' === trim( (string) $title ) ) {
					/* translators: %d: form post ID */
					$title = sprintf( __( 'Form ID %d', 'wp_easy_pay' ), $form->ID );
				}
				printf(
					'<option value="%1$s"%3$s>%2$s</option>',
					esc_attr( (string) $form->ID ),
					esc_html( $title ),
					selected( $current_form_id, $form->ID, false )
				);
			}
			?>
		</select>
	</span>
	<?php
}

/**
 * Loads script that applies filter by updating URL query (preserves month filter etc.).
 *
 * @param string $hook_suffix Current admin page hook.
 * @return void
 */
function wpep_transactions_list_form_filter_assets( $hook_suffix ) {
	if ( 'edit.php' !== $hook_suffix ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'wpep_reports' !== $screen->post_type ) {
		return;
	}

	if ( ! defined( 'WPEP_REPORTS_FILTER_FORM_QUERY_ARG' ) ) {
		return;
	}

	wp_enqueue_script(
		'wpep_transactions_form_filter',
		WPEP_ROOT_URL . 'assets/backend/js/wpep_transactions_form_filter.js',
		array(),
		WPEP_VERSION,
		true
	);

	wp_localize_script(
		'wpep_transactions_form_filter',
		'wpepTransactionsFormFilter',
		array(
			'param' => WPEP_REPORTS_FILTER_FORM_QUERY_ARG,
		)
	);
}

add_action( 'restrict_manage_posts', 'wpep_transactions_list_form_filter_dropdown', 20 );
add_action( 'admin_enqueue_scripts', 'wpep_transactions_list_form_filter_assets' );
