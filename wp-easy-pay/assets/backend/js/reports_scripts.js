jQuery( 'document' ).ready(
	function(){

	// Reports list: move search box into top actions row.
	if ( jQuery( 'body' ).hasClass( 'post-type-wpep_reports' ) && jQuery( 'body' ).hasClass( 'edit-php' ) ) {
		function moveReportsSearchBox() {
			var $reportSearchBox = jQuery( '.post-type-wpep_reports #posts-filter .search-box' );
			if ( $reportSearchBox.length === 0 ) {
				return;
			}

			var $targetRow = jQuery( '.post-type-wpep_reports #posts-filter .tablenav.top .alignleft.actions:not(.bulkactions)' ).first();
			if ( $targetRow.length === 0 ) {
				$targetRow = jQuery( '.post-type-wpep_reports #posts-filter .tablenav.top .alignleft.actions' ).eq( 1 );
			}

			if ( $targetRow.length > 0 && $targetRow.find( '#post-search-input' ).length === 0 ) {
				$targetRow.append( $reportSearchBox.children().clone() );
				$reportSearchBox.hide();
			}
		}

		moveReportsSearchBox();
		setTimeout( moveReportsSearchBox, 150 );
	}

	// This below code is for Subscription, Report and Coupons custom post type search
	// This jQuery file was calling upon backend so added this chunk here.
	// Chunk Open.
	var urlParams = new URLSearchParams(window.location.search);
	if(urlParams.has('s')) {
		var searchVal = urlParams.get('s');
		jQuery('span.subtitle strong').text(searchVal);
	}
	// Chunk Closed.

	let post_ids = jQuery('input[name="post[]"]').map(function() {return this.value;}).get().join();
		var data = {
			'action': 'wpep_status_check',
			'post_id': post_ids,
			'wp_global_nonce': wpep_reports_data.nonce,
		};
	jQuery.post(
			wpep_reports_data.ajax_url,
			data,
			function(response) {
	  
				/* response = JSON.parse( response );
	  
				if ('failed' == response.status) {
				}
	  
				if ('success' == response.status) {
					location.reload();
				} */
	  
			}
		);

		
		jQuery( '.give_refund_button' ).click(
			function(event) {
				event.preventDefault();

				var transaction_id = jQuery( this ).data( 'transactionid' );
				var amount         = jQuery( this ).data( 'amount' ).toString();
				var postid         = jQuery( this ).data( 'postid' );
				
				if (confirm( "You are about to process refund. Click OK to proceed and CANCEL to stop refund" )) {

					var currency_symbols = ['USD', 'CAD', 'GBP', 'AUD', 'JPY', 'C$', 'A$', '¥', '£', '$'];

					currency_symbols.forEach(
						element => {
						amount = amount.replace( element, "" );
						}
					);

					var data = {
						'action': 'wpep_payment_refund',
						'transaction_id': transaction_id,
						'amount': amount,
						'post_id': postid
					};


					jQuery.post(
						ajaxurl,
						data,
						function(response) {
				  
							response = JSON.parse( response );
				  
							if ('failed' == response.status) {
							}
				  
							if ('success' == response.status) {
								location.reload();
							}
				  
						}
					);


				}

			}
		);



		jQuery( '#wpep_refund_amount' ).keyup(function(event){


			if (jQuery(this).val() == '') {

				jQuery('#wpep_refund_number').text('0.00');
				jQuery('#give_refund_button').attr( "data-amount", '0.00' );

			}else {

				var refund_value = parseFloat(jQuery(this).val()).toFixed(2);
				jQuery('#wpep_refund_number').text(refund_value);
				jQuery('#give_refund_button').attr( "data-amount",  refund_value);

			}
			

		});


	}
);

jQuery(document).ready(function($) {
	function wpepInsertContextBackButton() {
		var urlParams = new URLSearchParams(window.location.search);
		var from = urlParams.get('wpep_from');

		if (!from || !wpep_reports_data.back_contexts || !wpep_reports_data.back_contexts[from]) {
			return;
		}

		var cfg = wpep_reports_data.back_contexts[from];
		var backButton = '<a href="' + cfg.url + '" class="wpep-back-button page-title-action">' + cfg.label + '</a>';

		if ($('.page-title-action').length > 0) {
			$('.page-title-action').first().after(backButton);
		} else {
			$('.wp-heading-inline').after(backButton);
		}
	}

	if ($('body').hasClass('post-php') &&
		($('body').hasClass('post-type-wpep_reports') || $('body').hasClass('post-type-wpep_subscriptions'))) {
		wpepInsertContextBackButton();
	}
});
