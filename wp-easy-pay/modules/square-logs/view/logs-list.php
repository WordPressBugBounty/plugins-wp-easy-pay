<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $wpdb;

$table_name = $wpdb->prefix . 'wpep_square_logs';

// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
$logs = $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY datetime DESC LIMIT 50" );

require_once plugin_dir_path( __FILE__ ) . '../logs-formatting.php';

?>
<div class="logsListMain"> 
	<?php echo '<h2 class="mainHeadings">' . esc_html__( 'Square Connection Logs', 'wp_easy_pay' ) . '</h2>'; ?>
	<br>
	<button id="wpep-clear-logs-btn" class="button clearLogBtn">
		<?php esc_html_e( 'Clear All Logs', 'wp_easy_pay' ); ?>
	</button>
	<div class="logsTableWrapper">
		<table class="logsTable">
			<thead>
				<tr>
					<th width="20" class="selectCheckBoxTH">No.</th>
					<th width="90">Square Mode</th>
					<th>Date & Time</th>
					<th class="widthAdjust">Request Body</th>
					<th class="widthAdjust">Return Response</th>
					<th>Connection Status</th>
					<th>Form ID</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( $logs ) : ?>
					<?php
					$serial_number = 1;
					foreach ( $logs as $log ) :
						?>
						<tr>
							<td class="selectCheckBoxTD"><?php echo esc_html( $serial_number ); ?></td>
							<td><?php echo esc_html( $log->mode ); ?></td>
							<td><strong><?php echo esc_html( gmdate( 'h:i a d-M-y', strtotime( $log->datetime ) ) ); ?></strong></td>
							<td>
								<button class="toggle-button viewBtn"
										data-target="#expandableContentRequest-<?php echo esc_attr( $log->id ); ?>">View</button>
								<div id="expandableContentRequest-<?php echo esc_attr( $log->id ); ?>" class="expandable-content">
									<div class="render-logs">
										<pre><?php echo esc_html( print_r( wpep_format_request_body_for_display( maybe_unserialize( $log->request ) ), true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r ?></pre>

									</div>
								</div>
							</td>
							<td>
								<button class="toggle-button viewBtn"
										data-target="#expandableContentResponse-<?php echo esc_attr( $log->id ); ?>">View</button>
								<div id="expandableContentResponse-<?php echo esc_attr( $log->id ); ?>" class="expandable-content">
									<div class="render-logs">
										<pre><?php echo esc_html( print_r( wpep_format_response_body_for_display( maybe_unserialize( $log->response ) ), true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r ?></pre>
									</div>
								</div>
							</td>
							<td>
								<div class="connectionStatus">
									<?php
									if ( 'Success' === $log->status ) {
										?>
										<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/connected.png' ); ?>" alt="connected" />
										<span>Connected</span>
										<?php
									} else {
										?>
										<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/disconnected.png' ); ?>" alt="disconnected" />
										<span>Disconnected</span>
										<?php
									}
									?>
									
								</div>
							</td>
							<td><?php echo isset( $log->form_id ) && '' !== $log->form_id ? esc_html( $log->form_id ) : 'Global'; ?></td>
						</tr>
						<?php
						++$serial_number;
					endforeach;
					?>
				<?php else : ?>
					<tr><td colspan="7" class="noLogMsg">No logs found.</td></tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
