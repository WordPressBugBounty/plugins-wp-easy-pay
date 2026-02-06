jQuery( 'document' ).ready(
	function(){
		jQuery( '#wpep-export-transactions' ).on(
			'click',
			function(e) {
				e.preventDefault();
				jQuery('#wpep-form-fields').hide();
				jQuery('.wpep-reports-footer').hide();
				jQuery('.wpep-check-all-fields').hide();
				jQuery('.wpep-reports-list').empty();
				jQuery( 'div#wpep-reports-popup-container' ).show();
			}
		);

		jQuery('#wpep-form-selection').on('change', function() {
			jQuery("#wpep-check-all").next("label").text(" Check All");
			jQuery("#wpep-check-all").prop("checked", false); 
			jQuery('#wpep-download-now').prop('disabled', true);
			jQuery('#wpep-form-fields').hide();
			jQuery('.wpep-reports-footer').hide();
			jQuery('.wpep-check-all-fields').hide();
            var selectedForm = jQuery(this).val();
			jQuery('.wpep-reports-list').empty();
			jQuery.ajax(
				{
					url: wpep_reports.ajaxUrl,
					type: "POST",
					data: {
						action: 'wpep_selected_form_fields',
						nonce: jQuery('#wpep_download_report_nonce').val(), 
						form_id: selectedForm, 
					},
					success: function (html) {
						if(html != ''){
							jQuery('.wpep-reports-footer').show();
							jQuery('.wpep-check-all-fields').show();
							jQuery('#wpep-form-fields').show();
							jQuery('.wpep-reports-list').append(html);
						}
					},
					error: function (xhr, status, error) {
						// Handle errors
						console.error('AJAX Error:', error);
					}
				}
			);
        });

		jQuery("#wpep-check-all").on("change", function () {
			let isChecked = jQuery(this).is(":checked");
			
			// Check/uncheck all checkboxes except disabled ones
			jQuery('input[name="wpep_reports_export_fields"]').prop("checked", isChecked);
			if (isChecked) {
				jQuery("#wpep-check-all").next("label").text(" Uncheck All");
				jQuery('#wpep-download-now').prop('disabled', false);
			} else { 
				jQuery("#wpep-check-all").next("label").text(" Check All");
				jQuery('#wpep-download-now').prop('disabled', true);
			}
		});
		jQuery(document).on("change", 'input[name="wpep_reports_export_fields"]', function () {
			// Check if any checkbox is checked
			if (jQuery('input[name="wpep_reports_export_fields"]:checked').length > 0) {
				jQuery('#wpep-download-now').prop('disabled', false); // Enable the button
			} else {
				jQuery('#wpep-download-now').prop('disabled', true); // Disable the button
			}
		});

        // Handle "Check All" checkbox functionality
        jQuery('#wpep-check-all').on('change', function() {
            var isChecked = jQuery(this).prop('checked');
            jQuery('#wpep-form-fields input[type="checkbox"]').prop('checked', isChecked);
        });

		jQuery( '.wpep-reports-close, .wpep-reports-popup-overlay' ).on(
			'click',
			function(e) {
				e.preventDefault();
				jQuery( 'div#wpep-reports-popup-container' ).hide();
				jQuery( '#wpep-form-selector' )[0].reset();
			}
		);
 
		jQuery( "#wpep-check-all" ).click(
			function(){
				jQuery( "input[name=wpep_reports_export_fields]" ).prop( 'checked', jQuery( this ).prop( 'checked' ) );
			}
		);

		jQuery( '#wpep-download-now' ).on(
			'click',
			function(a){
				a.preventDefault();
				jQuery.ajax(
					{
						url: wpep_reports.ajaxUrl,
						type: "POST",
						dataType: "json",
						data: {
							action: wpep_reports.action,
							nonce: wpep_reports.nonce,
							post_type: wpep_reports.post_type,
							form_id: jQuery('#wpep-form-selection').val(),
							fields: jQuery( '#wpep-form-selector' ).serializeArray(),
						},
						beforeSend: function () {
							jQuery( '#wpep-export-transactions' ).attr( 'disabled', 'true' );
						},
						success: function (response) {
							// print response in console log for test only
							// jQuery.fileDownload(wpep_reports.reports_download_url);
							if (response.status == true) {
								jQuery( 'div#wpep-reports-popup-container' ).hide();
								jQuery( '#wpep-form-selector' )[0].reset();
								window.location.href = wpep_reports.reports_download_url;
								jQuery( '#wpep-export-transactions' ).removeAttr( 'disabled', 'true' );
							}
						}
					}
				);
			}
		);
	}
);
