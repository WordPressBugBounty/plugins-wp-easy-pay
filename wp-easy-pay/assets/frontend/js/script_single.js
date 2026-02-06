jQuery( document ).ready(
	function () {

		jQuery( ".selectedPlan input[type='radio']" ).change(
			function () {

				var radioValue = jQuery( this ).val();
				if (radioValue) {
					var form_id = jQuery( this ).parents( 'form' ).data( 'id' );
					
					if ( jQuery(`#theForm-${form_id} input[name="wpep-discount"]`).length > 0 ) { 
						jQuery(`#theForm-${form_id} input[name="wpep-discount"]`).remove();
						jQuery(`#wpep-coupons-${form_id}`).children().show();
					}
				
					if ( jQuery(`#theForm-${form_id} .wpep-alert-coupon`).length > 0 ) {
						jQuery(`#theForm-${form_id} .wpep-alert-coupon`).remove();
					}
					jQuery( '#wpep_quantity_' + form_id ).val( 1 );
					if ( undefined != jQuery( '#wpep_quantity_'+form_id ).val() ) {
						var amount_value_with_quantity = radioValue * jQuery( '#wpep_quantity_'+form_id ).val();
					} else {
						var amount_value_with_quantity = radioValue;
					}
					jQuery( 'form[data-id="' + form_id + '"] .display' ).text( amount_value_with_quantity );
					var selected_payment_tab = jQuery( `ul.wpep_tabs-${form_id} li.tab-link.current` ).data( 'tab' );
					if(selected_payment_tab == 'giftcard-'+form_id){
						jQuery(`#amount_display_${form_id}`).hide();
						if(jQuery('#giftcard_text_'+form_id).length == 0){
							jQuery('.wpep-single-form-'+form_id+' span' ).append(`<small id="giftcard_text_${form_id}" class="giftcard_text" style="font-size: 100%;">with Giftcard</small>`);
						}
					} else {
						jQuery('#giftcard_text_'+form_id).remove();
						jQuery(`#amount_display_${form_id}`).show();
						// jQuery(`#amount_display_${current_form_id}`).text(jQuery(`#amount_display_${current_form_id}`).siblings('input[name="wpep-selected-amount"]').val());
					}
					jQuery( 'form[data-id="' + form_id + '"] .display' ).next().next( 'input[name="one_unit_cost"]' ).val( radioValue ).trigger('change');
					jQuery( 'form[data-id="' + form_id + '"] .display' ).next( 'input[name="wpep-selected-amount"]' ).val( amount_value_with_quantity ).trigger('change');
					//jQuery( '#one_unit_cost' ).val( radioValue.trim() );
					jQuery(`#wpep_coupon_applied_${form_id}`).hide();
					jQuery( 'form[data-id="' + form_id + '"] .wpep-single-form-submit-btn' ).removeClass( 'wpep-disabled' );
					jQuery( 'form[data-id="' + form_id + '"] .wpep-wizard-form-submit-btn' ).removeClass( 'wpep-disabled' );
				}
			}
		);

		function prepare_display_amount(currencyType, currency, val) {

			if (currencyType == 'symbol') {

				if (currency == 'USD') {
					currency = '$' + val;
				}

				if (currency == 'CAD') {
					currency = 'C$' + val;
				}

				if (currency == 'AUD') {
					currency = 'A$' + val;
				}

				if (currency == 'JPY') {
					currency = '¥' + val;
				}

				if (currency == 'GBP') {
					currency = '£' + val;
				}

			} else {

				currency = val + ' ' + currency;

			}

			return currency;

		}

		// Select the target node (in this case, .paynowDrop)
		var targetNode = document.querySelector('.paynowDrop');
		// Options for the observer (which mutations to observe)
		var config = { childList: true, subtree: true };

		// Callback function to execute when mutations are observed
		var callback = function(mutationsList, observer) {
			for (var mutation of mutationsList) {
				if (mutation.type === 'childList') {
					// The code you want to execute when DOM changes occur
					
					$selected_value = jQuery( '.selection' ).data( 'value' );
					var form_id     = jQuery( this ).parents( 'form' ).data( 'id' );

					if ( jQuery(`#theForm-${form_id} input[name="wpep-discount"]`).length > 0 ) { 
						jQuery(`#theForm-${form_id} input[name="wpep-discount"]`).remove();
						jQuery(`#wpep-coupons-${form_id}`).children().show();
					}

					if ( jQuery(`#theForm-${form_id} .wpep-alert-coupon`).length > 0 ) {
						jQuery(`#theForm-${form_id} .wpep-alert-coupon`).remove();
					}

					jQuery( 'form[data-id="' + form_id + '"] .display' ).text( $selected_value );
					var selected_payment_tab = jQuery( `ul.wpep_tabs-${form_id} li.tab-link.current` ).data( 'tab' );
					if(selected_payment_tab == 'giftcard-'+form_id){
						jQuery(`#amount_display_${form_id}`).hide();
						if(jQuery('#giftcard_text_'+form_id).length == 0){
							jQuery('.wpep-single-form-'+form_id+' span' ).append(`<small id="giftcard_text_${form_id}" class="giftcard_text" style="font-size: 100%;">with Giftcard</small>`);
						}
					} else {
						jQuery('#giftcard_text_'+form_id).remove();
						jQuery(`#amount_display_${form_id}`).show();
						// jQuery(`#amount_display_${current_form_id}`).text(jQuery(`#amount_display_${current_form_id}`).siblings('input[name="wpep-selected-amount"]').val());
					}
					jQuery( 'form[data-id="' + form_id + '"] .display' ).next().next( 'input[name="one_unit_cost"]' ).val( $selected_value ).trigger('change');
					jQuery( 'form[data-id="' + form_id + '"] .display' ).next( 'input[name="wpep-selected-amount"]' ).val( $selected_value ).trigger('change');
					jQuery( 'form[data-id="' + form_id + '"] .wpep-single-form-' + current_form_id).removeClass( 'wpep-disabled' );
					jQuery( 'form[data-id="' + form_id + '"] .wpep-wizard-form-' + current_form_id).removeClass( 'wpep-disabled' );
					jQuery(`#wpep_coupon_applied_${form_id}`).hide();
					if (jQuery( '.paynowDrop option:selected' ).text() == "Other") {
						jQuery( 'form[data-id="' + form_id + '"] .showPayment' ).addClass( 'shcusIn' );
						jQuery( 'form[data-id="' + form_id + '"] .showPayment input' ).val( '' );
					} else {
						jQuery( 'form[data-id="' + form_id + '"] .showPayment' ).removeClass( 'shcusIn' );
						jQuery( 'form[data-id="' + form_id + '"] .showPayment input' ).val( '' );
					}
				}
			}
		};

		// Create an observer instance linked to the callback function
		var observer = new MutationObserver(callback);

		// Start observing the target node for configured mutations
		if (targetNode) {
			observer.observe(targetNode, config);
		}

		// To stop observing at some point, call observer.disconnect()
		// observer.disconnect();

		jQuery( '.otherPayment' ).on(
			'change',
			function (e) {

				var form_id      = jQuery( this ).parents( 'form' ).data( 'id' );
				var currency     = jQuery( this ).parents( 'form' ).data( 'currency' );
				var currencyType = jQuery( this ).parents( 'form' ).data( 'currency-type' );
				var max          = parseFloat( jQuery( this ).attr( 'max' ) );
				var min          = parseFloat( jQuery( this ).attr( 'min' ) );
				var val          = jQuery( this ).val();
				jQuery( '#one_unit_cost' ).val( val );
				jQuery( '#wpep_quantity_' + form_id ).val( 1 );

				if ( jQuery(`#theForm-${form_id} input[name="wpep-discount"]`).length > 0 ) { 
					jQuery(`#theForm-${form_id} input[name="wpep-discount"]`).remove();
					jQuery(`#wpep-coupons-${form_id}`).children().show();
				}
			
				if ( jQuery(`#theForm-${form_id} .wpep-alert-coupon`).length > 0 ) {
					jQuery(`#theForm-${form_id} .wpep-alert-coupon`).remove();
				}

				setTimeout(function() {
					if (val != '' && val >= min && val <= max) {
						currency = prepare_display_amount(currencyType, currency, val);
					
						jQuery( this ).val( val );
						jQuery( 'form[data-id="' + form_id + '"] .display' ).text( currency );
						var selected_payment_tab = jQuery( `ul.wpep_tabs-${form_id} li.tab-link.current` ).data( 'tab' );
						if(selected_payment_tab == 'giftcard-'+form_id){
							jQuery(`#amount_display_${form_id}`).hide();
							if(jQuery('#giftcard_text_'+form_id).length == 0){
								jQuery('.wpep-single-form-'+form_id+' span' ).append(`<small id="giftcard_text_${form_id}" class="giftcard_text" style="font-size: 100%;">with Giftcard</small>`);
							}
						} else {
							jQuery('#giftcard_text_'+form_id).remove();
							jQuery(`#amount_display_${form_id}`).show();
							// jQuery(`#amount_display_${current_form_id}`).text(jQuery(`#amount_display_${current_form_id}`).siblings('input[name="wpep-selected-amount"]').val());
						}
						jQuery( 'form[data-id="' + form_id + '"] .display' ).next( 'input[name="wpep-selected-amount"]' ).val( jQuery('.otherPayment ').val() ).trigger('change');
						jQuery( 'form[data-id="' + form_id + '"] .wpep-single-form-' + current_form_id).removeClass( 'wpep-disabled' );
						jQuery( 'form[data-id="' + form_id + '"] .wpep-wizard-form-' + current_form_id).removeClass( 'wpep-disabled' );
						jQuery( 'form[data-id="' + form_id + '"] .wpep-single-form-submit-btn' ).removeClass( 'wpep-disabled' );
						jQuery( 'form[data-id="' + form_id + '"] .wpep-wizard-form-submit-btn' ).removeClass( 'wpep-disabled' );

					} else {
						currency = prepare_display_amount(currencyType, currency);
						
						jQuery( this ).val( '' );
						jQuery( 'form[data-id="' + form_id + '"] .display' ).text( '' );
						var selected_payment_tab = jQuery( `ul.wpep_tabs-${form_id} li.tab-link.current` ).data( 'tab' );
						if(selected_payment_tab == 'giftcard-'+form_id){
							jQuery(`#amount_display_${form_id}`).hide();
							if(jQuery('#giftcard_text_'+form_id).length == 0){
								jQuery('.wpep-single-form-'+form_id+' span' ).append(`<small id="giftcard_text_${form_id}" class="giftcard_text" style="font-size: 100%;">with Giftcard</small>`);
							}
						} else {
							jQuery('#giftcard_text_'+form_id).remove();
							jQuery(`#amount_display_${form_id}`).show();
							// jQuery(`#amount_display_${current_form_id}`).text(jQuery(`#amount_display_${current_form_id}`).siblings('input[name="wpep-selected-amount"]').val());
						}
						jQuery( 'form[data-id="' + form_id + '"] .display' ).next( 'input[name="wpep-selected-amount"]' ).val( '' ).trigger('change');
						jQuery( 'form[data-id="' + form_id + '"] .wpep-single-form-submit-btn' ).addClass( 'wpep-disabled' );
						jQuery( 'form[data-id="' + form_id + '"] .wpep-wizard-form-submit-btn' ).addClass( 'wpep-disabled' );
					}
					jQuery(`#wpep_coupon_applied_${form_id}`).hide();
					jQuery('#wpep-single-form-'+ current_form_id ).click();
				}, 1000);
			}
		);

		jQuery( '.minus-btn' ).on(
			'click',
			function (e) {
				e.preventDefault();
				var $this  = $( this );
				var $input = $this.closest( 'div' ).find( 'input' );
				var value  = parseFloat( $input.val() );

				if (value > 1) {
					value = value - 1;
				} else {
					value = 0;
				}

				$input.val( value );
			}
		);

		jQuery( '.plus-btn' ).on(
			'click',
			function (e) {
				e.preventDefault();
				var $this  = $( this );
				var $input = $this.closest( 'div' ).find( 'input' );
				var value  = parseFloat( $input.val() );

				if (value < 100) {
					value = value + 1;
				} else {
					value = 100;
				}

				$input.val( value );
			}
		);

		jQuery( '.like-btn' ).on(
			'click',
			function () {
				$( this ).toggleClass( 'is-active' );
			}
		);

		jQuery( "#btn-download" ).click(
			function () {
				$( this ).toggleClass( "downloaded" );
			}
		);


		function validateEmail(email) {
			var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
			return re.test( String( email ).toLowerCase() );
		}

		jQuery( ".cardsBlock01 input[name$='savecards']" ).click(
			function () {
				var test = jQuery( this ).val();

				jQuery( "div.desc" ).hide();
				jQuery( "#cardContan" + test ).show();
			}
		);

		jQuery( ".custom-select" ).each(
			function() {

				var classes     = jQuery( this ).attr( "class" ),
				id              = jQuery( this ).attr( "id" ),
				name            = jQuery( this ).attr( "name" );
				var placeholder = 'Please Select';

				if (jQuery( this ).attr( "placeholder" ) !== undefined) {
					placeholder = jQuery( this ).attr( "placeholder" );
				}

				var template = '<div class="' + classes + '">';
				template    +=
				'<span class="custom-select-trigger">' +
				placeholder +
				"</span>";
				template    += '<div class="custom-options">';
				jQuery( this )
				.find( "option" )
				.each(
					function() {
						if(jQuery( this ).attr( "selected" ) !== undefined && jQuery( this ).val() !== '' ){
							template +=
							'<span class="custom-option selection' +
							'" data-value="' +
							jQuery( this ).attr( "value" ) +
							'">' +
							jQuery( this ).html() +
							"</span>";
						} else {
							template +=
							'<span class="custom-option ' +
							jQuery( this ).attr( "class" ) +
							'" data-value="' +
							jQuery( this ).attr( "value" ) +
							'">' +
							jQuery( this ).html() +
							"</span>";
						} 
					}
				);
				template += "</div></div>";

				jQuery( this ).wrap( '<div class="custom-select-wrapper"></div>' );
				jQuery( this ).hide();
				jQuery( this ).after( template );
				jQuery( this )
					.find( "option" )
					.each(
					function() {
						if(jQuery( this ).attr( "selected" ) !== undefined && jQuery( this ).val() !== '' ){
							jQuery( this ).addClass( "selection" );
							jQuery( '.custom-select-trigger' ).text(jQuery( this ).text());
						}
					}
				);
			}
		);

		jQuery( ".custom-option:first-of-type" ).hover(
			function() {
				jQuery( this )
					.parents( ".custom-options" )
					.addClass( "option-hover" );
			},
			function() {
				jQuery( this )
					.parents( ".custom-options" )
					.removeClass( "option-hover" );
			}
		);
		jQuery( ".custom-select-trigger" ).on(
			"click",
			function() {
				jQuery( "html" ).one(
					"click",
					function() {
						jQuery( ".custom-select" ).removeClass( "opened" );
					}
				);
				jQuery( this )
				.parents( ".custom-select" )
				.toggleClass( "opened" );
				event.stopPropagation();
			}
		);
		jQuery( ".custom-option" ).on(
			"click",
			function() {
				var form_id = jQuery( this ).parents( 'form' ).data( 'id' );
				jQuery( this )
				.parents( ".custom-select-wrapper" )
				.find( "select" )
				.val( jQuery( this ).data( "value" ) );

				jQuery( this )
				.parents( ".custom-options" )
				.find( ".custom-option" )
				.removeClass( "selection" );

				jQuery( this ).addClass( "selection" );
				jQuery( this )
				.parents( ".custom-select" )
				.removeClass( "opened" );

				jQuery( this )
				.parents( ".custom-select" )
				.find( ".custom-select-trigger" )
				.text( jQuery( this ).text() );
				if ( jQuery(`#theForm-${form_id} input[name="wpep-discount"]`).length > 0 ) { 
					jQuery(`#theForm-${form_id} input[name="wpep-discount"]`).remove();
					jQuery(`#wpep-coupons-${form_id}`).children().show();
				}
				jQuery( '#wpep_quantity_'+form_id ).val( 1 )
				if ( jQuery(`#theForm-${form_id} .wpep-alert-coupon`).length > 0 ) {
					jQuery(`#theForm-${form_id} .wpep-alert-coupon`).remove();
				}
				if ( undefined != jQuery( '#wpep_quantity_'+form_id ).val() ) {
					var amount_value_with_quantity = jQuery( this ).data( "value" ) * jQuery( '#wpep_quantity_'+form_id ).val();
				} else {
					var amount_value_with_quantity = jQuery( this ).data( "value" );
				}
				jQuery( 'form[data-id="' + form_id + '"] .display' ).next().next( 'input[name="one_unit_cost"]' ).val( jQuery( this ).data( "value" ) ).trigger('change');
				jQuery( 'form[data-id="' + form_id + '"] .display' ).next( 'input[name="wpep-selected-amount"]' ).val( amount_value_with_quantity ).trigger('change');
				jQuery(`#wpep_coupon_applied_${form_id}`).hide();
				
			}
		);

		jQuery( ".file-upload-wrapper" ).on(
			"change",
			".file-upload-field",
			function () {
				jQuery( this ).parent( ".file-upload-wrapper" ).attr( "data-text", jQuery( this ).val().replace( /.*(\/|\\)/, '' ) );
			}
		);

		jQuery( ".form-control" ).on(
			'focus',
			function () {
				var tmpThis = jQuery( this ).val();
				if (tmpThis == '') {
					jQuery( this ).parent().addClass( "focus-input" );
				} else if (tmpThis != '') {
					jQuery( this ).parent().addClass( "focus-input" );
				}
			}
		).on(
			'blur',
			function () {
				var tmpThis = jQuery( this ).val();
				if (tmpThis == '') {
					jQuery( this ).parent().removeClass( "focus-input" );
					jQuery( this ).siblings( '.wizard-form-error' ).slideDown( "3000" );
				} else if (tmpThis != '') {
					jQuery( this ).parent().addClass( "focus-input" );
					jQuery( this ).siblings( '.wizard-form-error' ).slideUp( "3000" );
				}
			}
		);

		jQuery( ".otherpayment" ).click(
			function () {
				var form_id = jQuery( this ).parents( 'form' ).data( 'id' );
				jQuery( '.wpep_coupon_remove_btn' ).trigger("click");
				setTimeout(function() {
					jQuery( 'form[data-id="' + form_id + '"] .showPayment' ).addClass( 'shcusIn' );
					jQuery( 'form[data-id="' + form_id + '"] .display' ).empty();
					jQuery( 'form[data-id="' + form_id + '"] .wpep-single-form-submit-btn' ).addClass( 'wpep-disabled' );
					jQuery( 'form[data-id="' + form_id + '"] .wpep-wizard-form-submit-btn' ).addClass( 'wpep-disabled' );
					jQuery( 'form[data-id="' + form_id + '"] .showPayment input' ).val( '' );
					jQuery( 'form[data-id="' + form_id + '"] .display' ).next().next( 'input[name="one_unit_cost"]' ).val( 0 ).trigger('change');
					jQuery( 'form[data-id="' + form_id + '"] .display' ).next( 'input[name="wpep-selected-amount"]' ).val( 0 ).trigger('change');
				}, 1000 );
			}
		);


		jQuery( '.paynow' ).click(
			function () {

				var form_id = jQuery( this ).parents( 'form' ).data( 'id' );
				
				if ( jQuery(`#theForm-${form_id} input[name="wpep-discount"]`).length > 0 ) { 
					jQuery(`#theForm-${form_id} input[name="wpep-discount"]`).remove();
					jQuery(`#wpep-coupons-${form_id}`).children().show();
				}

				if ( jQuery(`#theForm-${form_id} .wpep-alert-coupon`).length > 0 ) {
					jQuery(`#theForm-${form_id} .wpep-alert-coupon`).remove();
				}

				var selected_payment_tab = jQuery( `ul.wpep_tabs-${form_id} li.tab-link.current` ).data( 'tab' );
				if(selected_payment_tab == 'giftcard-'+form_id){
					jQuery(`#amount_display_${form_id}`).hide();
					if(jQuery('#giftcard_text_'+form_id).length == 0){
						jQuery('.wpep-single-form-'+form_id+' span' ).append(`<small id="giftcard_text_${form_id}" class="giftcard_text" style="font-size: 100%;">with Giftcard</small>`);
					}
				} else {
					jQuery('#giftcard_text_'+form_id).remove();
					jQuery(`#amount_display_${form_id}`).show();
					// jQuery(`#amount_display_${current_form_id}`).text(jQuery(`#amount_display_${current_form_id}`).siblings('input[name="wpep-selected-amount"]').val());
				}
				jQuery( 'form[data-id="' + form_id + '"] .display' ).next().next( 'input[name="one_unit_cost"]' ).val( jQuery( this ).text() ).trigger('change');
				jQuery( 'form[data-id="' + form_id + '"] .display' ).next( 'input[name="wpep-selected-amount"]' ).val( jQuery( this ).text() ).trigger('change');
				//jQuery( '#one_unit_cost' ).val( jQuery( this ).text().trim() );
				jQuery( '#wpep_quantity_' + form_id ).val( 1 );
				jQuery( 'form[data-id="' + form_id + '"] .wpep-single-form-submit-btn' ).removeClass( 'wpep-disabled' );
				jQuery( 'form[data-id="' + form_id + '"] .wpep-wizard-form-submit-btn' ).removeClass( 'wpep-disabled' );

				jQuery( 'form[data-id="' + form_id + '"] .showPayment' ).removeClass( 'shcusIn' );
				jQuery( 'form[data-id="' + form_id + '"] .customPayment' ).text( jQuery( this ).val() );

				// jQuery('#wpep_amount_'+form_id).val(jQuery(this).text().replace('$',''));
			}
		);
		
		jQuery('#wpep_personal_information input[type="checkbox"]').change(function() {
			if (jQuery(this).is(':checked')) {
				jQuery(this).val('1');
			} else {
				jQuery(this).val('0');
			}
		});

	}
);
