<?php
/**
 * Elementor Widget for WP Easy Pay Forms
 * 
 * This file registers an Elementor widget that allows users to select
 * and display WP Easy Pay payment forms in Elementor editor.
 * 
 * @package wp_easy_pay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Ensure Elementor Widget_Base class exists before proceeding
if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
	return;
}

/**
 * WP Easy Pay Elementor Widget Class
 * 
 * Registers a widget in Elementor that allows users to select
 * and display WP Easy Pay payment forms.
 */
class WPEP_Elementor_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name
	 * 
	 * @return string Widget name
	 */
	public function get_name() {
		return 'wpep-form';
	}

	/**
	 * Get widget title
	 * 
	 * @return string Widget title
	 */
	public function get_title() {
		return esc_html__( 'WP Easy Pay Form', 'wp_easy_pay' );
	}

	/**
	 * Get widget icon
	 * 
	 * @return string Widget icon
	 */
	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	/**
	 * Get widget categories
	 * 
	 * @return array Widget categories
	 */
	public function get_categories() {
		return array( 'general' );
	}

	/**
	 * Get widget keywords
	 * 
	 * @return array Widget keywords
	 */
	public function get_keywords() {
		return array( 'payment', 'form', 'easy pay', 'wpep', 'donation' );
	}

	/**
	 * Get widget script dependencies
	 * 
	 * @return array Widget script dependencies
	 */
	public function get_script_depends() {
		return array();
	}

	/**
	 * Get widget style dependencies
	 * 
	 * @return array Widget style dependencies
	 */
	public function get_style_depends() {
		return array();
	}

	/**
	 * Register widget controls
	 * 
	 * Adds form selection dropdown control
	 */
	protected function register_controls() {
		// Content Section
		$this->start_controls_section(
			'content_section',
			array(
				'label' => esc_html__( 'Form Settings', 'wp_easy_pay' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		// Form Selection Control
		$this->add_control(
			'form_id',
			array(
				'label'       => esc_html__( 'Select Form', 'wp_easy_pay' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => $this->get_payment_forms_for_elementor(),
				'default'     => '',
				'label_block' => true,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend
	 * 
	 * Uses the shortcode to render the selected form
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		
		// Ensure settings array exists
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}
		
		// Get form_id from settings
		$form_id = isset( $settings['form_id'] ) ? absint( $settings['form_id'] ) : 0;
		
		// Check if form_id is valid
		if ( empty( $form_id ) || $form_id <= 0 ) {
			// Show message in both editor and frontend
			?>
			<div class="wpep-elementor-widget-empty" style="padding: 20px; text-align: center; background: #e9f2ff; border: 2px dashed #629ef3;">
				<p style="margin: 0; font-size: 14px; color: #5e5d5d;font-family: 'Figtree', sans-serif;"><?php echo esc_html__( 'Please select a form from the widget settings.', 'wp_easy_pay' ); ?></p>
			</div>
			<?php
			return;
		}
		
		// Render the shortcode
		$shortcode = '[wpep-form id="' . esc_attr( $form_id ) . '"]';
		
		?>
		<div class="wpep-elementor-widget-container">
			<?php echo do_shortcode( $shortcode ); ?>
		</div>
		<?php
	}

	/**
	 * Render widget output in the editor
	 * 
	 * @return void
	 */
	protected function content_template() {
		?>
		<#
		if ( settings.form_id ) {
			var shortcode = '[wpep-form id="' + settings.form_id + '"]';
		#>
			<div class="wpep-elementor-widget-preview">
				<div class="wpep-elementor-widget-placeholder">
					<div style="padding: 20px; text-align: center; background: #e9f2ff; border: 2px dashed #629ef3;">
						<img src="<?php echo esc_url( WPEP_ROOT_URL . 'assets/backend/img/Logo.png' ); ?>" alt="<?php echo esc_attr__( 'WP Easy Pay', 'wp_easy_pay' ); ?>" style="max-width: 200px; height: auto; margin-bottom: 10px;">
						<p style="margin: 0;color: #313131;font-size: 18px;font-weight: 600;font-family: 'Figtree', sans-serif;">
							<?php echo esc_html__( 'Form ID:', 'wp_easy_pay' ); ?> {{ settings.form_id }}
						</p>
						<p style="margin: 10px 0 0; font-size: 14px; color: #747474;font-family: 'Figtree', sans-serif;">
							<?php echo esc_html__( 'Form will be displayed on the frontend.', 'wp_easy_pay' ); ?>
						</p>
					</div>
				</div>
			</div>
		<#
		} else {
		#>
			<div style="padding: 20px; text-align: center; background: #e9f2ff; border: 2px dashed #629ef3;">
				<p style="margin: 0; color: #747474;font-family: 'Figtree', sans-serif;"><?php echo esc_html__( 'Please select a form from the widget settings.', 'wp_easy_pay' ); ?></p>
			</div>
		<#
		}
		#>
		<?php
	}

	/**
	 * Get payment forms list for Elementor widget.
	 *
	 * @return array Array of form IDs and titles.
	 */
	protected function get_payment_forms_for_elementor() {
		$args = array(
			'numberposts' => -1,
			'post_type'   => 'wp_easy_pay',
			'post_status' => 'publish',
			'orderby'     => 'title',
			'order'       => 'ASC',
		);

		$forms = get_posts( $args );

		$form_list = array(
			'' => esc_html__( 'Please select your form', 'wp_easy_pay' ),
		);

		foreach ( $forms as $form ) {
			$form_title = trim( (string) $form->post_title );
			if ( '' === $form_title ) {
				$form_title = __( 'Untitled form', 'wp_easy_pay' ) . ' (ID: ' . $form->ID . ')';
			}
			$form_list[ $form->ID ] = $form_title;
		}

		return $form_list;
	}

	/**
	 * Register this widget with Elementor.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager instance.
	 */
	public static function register_widget( $widgets_manager ) {
		if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
			return;
		}

		$widgets_manager->register( new self() );
	}
}

add_action( 'elementor/widgets/register', array( 'WPEP_Elementor_Widget', 'register_widget' ) );

