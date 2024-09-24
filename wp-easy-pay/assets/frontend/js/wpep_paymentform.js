let afterpay = {};
let cashAppPay = {};
const payments   = initializePayments( wpep_local_vars.square_application_id, wpep_local_vars.square_location_id_in_use );

document.addEventListener('DOMContentLoaded', async function () {

	jQuery('.loader').hide();
	jQuery('#cash_app_pay_v1_element').bind('click', function(e){
		e.preventDefault();
		e.stopImmediatePropagation();
	});

	jQuery( '.wizard-section' ).css( 'visibility', 'visible' );
	jQuery( '.parent-loader' ).remove();
		
	
	var wpep_paymentForm = {};

	
	if (jQuery( 'form.wpep_payment_form' ).length > 0) {
		jQuery( 'form.wpep_payment_form' ).each(
			async function () {
				var current_form_id = jQuery( this ).data( 'id' );
				var currency        = jQuery( this ).data( 'currency' );
				let card;

				calculate( current_form_id, currency );

				jQuery('.qty').keyup(function(){
					calculate( current_form_id, currency );
				});
		
				if (!window.Square) {
					throw new Error('Square.js failed to load properly');
				}


				if ( wpep_local_vars.afterpay == 'on') {
			
					afterpay = displayAfterPay( payments, current_form_id, currency );

					if (afterpay !== undefined) {
						const afterpayButton = document.getElementById('afterpay-button');
						afterpayButton.addEventListener('click', async function (event) {
						await handlePaymentMethodSubmission(event, afterpay, current_form_id, currency, false);
						});
					} 	
		
				}

				if ( wpep_local_vars.cashapp == 'on') {
					
					cashAppPay = displayCashApp( payments, current_form_id, currency );
					
					jQuery('#cash-app-pay').on('click', function(){

						cashAppPay.addEventListener('ontokenization', function (event) {
							const { tokenResult, error } = event.detail;
							if (error) {
							  // developer handles error
							} else if (tokenResult.status === 'OK') {
								jQuery( "#theForm-" + current_form_id ).append( jQuery( '<div />' ).attr( 'class', 'wpepLoader' ).html( '<div class="initial-load-animation"><div class="payment-image icomoonLib"><span class="icon-pay"></span></div><div class="loading-bar"><div class="blue-bar"></div></div></div>' ) );
								handlePaymentMethodSubmission(event, cashAppPay, current_form_id, currency, tokenResult.token, 'cashapp');
							}
							
						  });
					
					});
				}

				async function initializeCard(payments, current_form_id) {
					const card = await payments.card();
					await card.attach('#card-container-'+current_form_id); 
					return card; 
				}

						   

				async function initializeACH(payments) {
					let redirectURI   = 'https://development-cloud.local/421-2/';
					let transactionId = '';
					const ach = await payments.ach({ redirectURI, transactionId });
					return ach;
				}		   
			   
		
				 
				try {

					let ach;
					card = await initializeCard(payments, current_form_id);
					jQuery('.wpep-single-form-submit-btn').click(async function (event) {
						event.preventDefault();
						var current_form_id = jQuery( this ).parents( 'form' ).data( 'id' );
						console.log(document.getElementsByClassName('sq-hidden-background-text')[0]);
						paymentButtonClicked(event, card, current_form_id, currency);
						
					});

					jQuery('.wpep-wizard-form-submit-btn').click(async function (event) {
						event.preventDefault();
						var current_form_id = jQuery( this ).parents( 'form' ).data( 'id' );
						paymentButtonClicked(event, card, current_form_id, currency);
						

					});
					if ( wpep_local_vars.ach_debit == 'on') {
						ach = await initializeACH(payments);
						const achButton = document.getElementById('ach-button');
						achButton.addEventListener('click', async function (event) {
							event.preventDefault();
							const paymentForm = document.getElementById('theForm-' + current_form_id);
							const achOptions = getACHOptions(paymentForm, current_form_id);
							await handlePaymentMethodSubmission(event, ach, current_form_id, currency, false, 'ach', achOptions);
							// ACH with the `accountHolderName` as an option.
						});
					}

				} catch (e) {

					console.error('Initializing Card failed', e);
					return;
					
				}	
			
			}
		);
	}



	function getBillingContact(form, current_form_id) {
		return {
		  givenName:  jQuery( "#theForm-" + current_form_id + " input[name='wpep-first-name-field']" ).val(),
		  familyName: jQuery( "#theForm-" + current_form_id + " input[name='wpep-last-name-field']" ).val(),
		};
	  }
	 
	  function getACHOptions(form, current_form_id) {
		const billingContact = getBillingContact(form, current_form_id);
		const accountHolderName = `${billingContact.givenName} ${billingContact.familyName}`;
		return { accountHolderName };
	  }
	 

	  function send_payment_request(data, current_form_id) {

		jQuery.post(
			wpep_local_vars.ajax_url,
			data,
			function (response) {

				var json_response = JSON.parse(response);
				
					if ('success' == json_response.status) {

						var form_id           = current_form_id;
						var current           = jQuery( 'form[data-id="' + form_id + '"]' );
						var next              = jQuery( 'form[data-id="' + form_id + '"]' );
						var currentActiveStep = current.find( '.form-wizard-steps .active' );
						next.find( '.wizard-fieldset' ).removeClass( "show", "400" );
						currentActiveStep.removeClass( 'active' ).addClass( 'activated' ).next().addClass( 'active', "400" );
						next.find( '.wizard-fieldset.orderCompleted' ).addClass( "show wpep-ptb-150", "400" );
						next.find( '.wpep-popup' ).addClass( 'completed' );
						next.find( '.wizard-fieldset.orderCompleted' ).siblings().remove();
						// remove form desc on thankyou page
						current.find( '.wpep-form-desc' ).remove();

						jQuery( 'html, body' ).animate(
							{
								scrollTop: jQuery( "#theForm-" + form_id ).offset().top - 50
							},
							800,
							function () {
								window.location.hash = '#';
							}
						);
						 if (current.data( 'redirection' ) == 'http://Yes' || current.data( 'redirection' ) == 'Yes' ) { 
							var counter = parseInt( current.data( 'delay' ) );

							current.find( '#counter' ).text( counter );

							if (current.data( 'redirectionurl' ) != '') {

								setInterval(
									function () {
										counter--;
										if (counter >= 0) {
											span           = document.getElementById( "counter-" + form_id );
											span.innerHTML = counter;
										}

										if (counter === 0) {
											window.location.href = current.data( 'redirectionurl' );
											clearInterval( counter );
										}

									},
									1000
								);

							} else {

								setInterval(
									function () {
										counter--;
										if (counter >= 0) {
											span           = document.getElementById( "counter-" + form_id );
											span.innerHTML = counter;
										}

										if (counter === 0) {
											location.reload();
											clearInterval( counter );
										}

									},
									1000
								);
							}
						} else {
							current.find( 'small.counterText' ).remove();
						}

					} else {

						var json_response = JSON.parse( response );

						jQuery( "#theForm-" + current_form_id + " .paymentsBlocks" ).prepend( '<div class="wpep-alert wpep-alert-danger wpep-alert-dismissable"><a href="#" data-dismiss="alert" class="wpep-alert-close">×</a>' + json_response.detail + '</div>' );
						jQuery( 'html, body' ).animate(
						{
							scrollTop: jQuery( "#theForm-" + current_form_id + " .paymentsBlocks" ).offset().top
						},
						800,
						function () {
								window.location.hash = '#';
						}
						);

					}
			}
		).done(
			function () {
				jQuery( '.wpepLoader' ).remove();
			}
		);

	}

	// Call this function to send a payment token, buyer name, and other details
	// to the project server code so that a payment can be created with 
	// Payments API
	async function createPayment(token, current_form_id, currency, cof, verifyToken = false) {

		var first_name       = jQuery( "#theForm-" + current_form_id + " input[name='wpep-first-name-field']" ).val();
		var last_name        = jQuery( "#theForm-" + current_form_id + " input[name='wpep-last-name-field']" ).val();
		var email            = jQuery( "#theForm-" + current_form_id + " input[name='wpep-email-field']" ).val();
		var currencies       = ['CAD', 'USD', 'EUR', 'JPY', 'AUD', 'GBP'];
		var currency_symbols = ['C$', 'A$', '¥', '£', '€', '$'];
		var amountElement = document.getElementById('amount_display_' + current_form_id);
		var amountText = amountElement.textContent.trim();
		var amount = amountText.replace(/[A-Za-z$]/g, '');
		var payment_type     = jQuery( '#wpep_payment_form_type_' + current_form_id ).val();
		var form_values      = [];
		var selectedCheckbox = [];
		var checkLabel       = '';

		if (jQuery( '#theForm-' + current_form_id ).find( 'input[type="checkbox"]' ).length > 0) {

			var checkboxName = jQuery( '#theForm-' + current_form_id + ' input[type="checkbox"]' ).attr( 'name' );
			if (checkboxName != undefined) {
				jQuery( 'form[data-id="' + current_form_id + '"] input[name="' + checkboxName + '"]' ).each(
					function () {
						checkLabel = jQuery( this ).data( 'main-label' );
						if (jQuery( this ).is( ':checked' )) {
							selectedCheckbox.push( jQuery( this ).val() );
						}
					}
				);
			}

		}

		form_values.push(
		{
			label: checkLabel,
			value: selectedCheckbox.join( ", " )
		});

		var product_label = [];
		jQuery( '.product_label' ).each(
			function(key, value) {
				var item_display = jQuery( this ).closest( '.wpItem' ).css( 'display' ) == 'none' ? 'no' : 'yes';
				if ('yes' == item_display) {
					product_label.push( jQuery( value ).text() );
				}
			}
		);

		var product_price = [];
		jQuery( '.price' ).each(
			function(key, value) {

				var item_display = jQuery( this ).closest( '.wpItem' ).css( 'display' ) == 'none' ? 'no' : 'yes';

				if ('yes' == item_display) {
					product_price.push( jQuery( value ).val() );
				}
			}
		);

		var product_qty = [];
		jQuery( '.qty' ).each(
			function(key, value) {
				var item_display = jQuery( this ).closest( '.wpItem' ).css( 'display' ) == 'none' ? 'no' : 'yes';

				if ('yes' == item_display) {
					if ('' !== jQuery( value ).val()) {
						product_qty.push( jQuery( value ).val() );
					}
				}
			}
		);

		var product_cost = [];
		jQuery( '.price' ).each(
			function(key, value) {
				var item_display = jQuery( this ).closest( '.wpItem' ).css( 'display' ) == 'none' ? 'no' : 'yes';
				if ('yes' == item_display) {
					product_cost.push( jQuery( value ).val() );
				}
			}
		);

		var products_data = {};
		jQuery.each(
			product_label ,
			function(key, value) {

				var tmp_product_data      = {};
				tmp_product_data.label    = product_label[key];
				tmp_product_data.quantity = product_qty[key];
				tmp_product_data.price    = product_price[key];
				tmp_product_data.cost     = product_cost[key];

				products_data[key] = tmp_product_data;

			}
		);

		products_data = JSON.stringify( products_data );

		if (jQuery( '#theForm-' + current_form_id ).find( 'input[type="radio"]' ).length > 0) {
			var radioName = jQuery( '#theForm-' + current_form_id + ' input[type="radio"]' ).attr( 'name' );
			if (radioName != undefined) {
				jQuery( 'form[data-id="' + current_form_id + '"] input[name="' + radioName + '"]' ).each(
					function () {

						if (jQuery( this ).is( ':checked' )) {
							form_values.push(
								{
									label: jQuery( this ).data( 'main-label' ),
									value: jQuery( this ).val()
								}
							);
						}

					}
				);
			}
		}

		form_values.push(
			{
				label: 'Products Data',
				value: products_data
			}
		);


		jQuery('.radio_amount').each(function(){
			
			if (jQuery( this ).is( ':checked' )) {

				form_values.push(
					{
						label: jQuery(this).data('label'),
						value: jQuery(this).val()
					}
				);

			}

		});

		jQuery( 'form[data-id="' + current_form_id + '"] input[type="date"]' ).each(
			function () {

				if (jQuery( this ).data( 'label' ) !== '' && typeof jQuery( this ).data( 'label' ) !== 'undefined') {
					form_values.push(
						{
							label: jQuery( this ).data( 'label' ),
							value: jQuery( this ).val()
						}
					);
				}

			}
		);

		jQuery( 'form[data-id="' + current_form_id + '"] input[type="number"]' ).each(
			function () {
				if (jQuery( this ).data( 'label' ) !== '' && typeof jQuery( this ).data( 'label' ) !== 'undefined') {
					form_values.push(
						{
							label: jQuery( this ).data( 'label' ),
							value: jQuery( this ).val()
						}
					);
				}
			}
		);

		jQuery( 'form[data-id="' + current_form_id + '"] select' ).each(
			function () {

				var selMulti = jQuery.map(
					jQuery( 'form[data-id="' + current_form_id + '"] select option:selected' ),
					function (el, i) {
						return jQuery( el ).text();
					}
				);

				if (jQuery( this ).data( 'label' ) !== '' && typeof jQuery( this ).data( 'label' ) !== 'undefined') {
					form_values.push(
						{
							label: jQuery( this ).data( 'label' ),
							value: selMulti.join( ", " )
						}
					);
				}

			}
		);

		jQuery( 'form[data-id="' + current_form_id + '"] input[type="text"]' ).each(
			function () {

				if (jQuery( this ).data( 'label' ) !== '' && typeof jQuery( this ).data( 'label' ) !== 'undefined') {
					form_values.push(
						{
							label: jQuery( this ).data( 'label' ),
							value: jQuery( this ).val()
						}
					);
				}
			}
		);

		jQuery( 'form[data-id="' + current_form_id + '"] textarea' ).each(
			function () {

				if (jQuery( this ).data( 'label' ) !== '' && typeof jQuery( this ).data( 'label' ) !== 'undefined') {
					form_values.push(
						{
							label: jQuery( this ).data( 'label' ),
							value: jQuery( this ).val()
						}
					);
				}

			}
		);

		jQuery( 'form[data-id="' + current_form_id + '"] input[type="email"]' ).each(
			function () {

				if (jQuery( this ).data( 'label' ) !== '' && typeof jQuery( this ).data( 'label' ) !== 'undefined') {
					form_values.push(
						{
							label: jQuery( this ).data( 'label' ),
							value: jQuery( this ).val()
						}
					);
				}

			}
		);

		jQuery( 'form[data-id="' + current_form_id + '"] input[type="tel"]' ).each(
			function () {
				if (jQuery( this ).data( 'label' ) !== '' && typeof jQuery( this ).data( 'label' ) !== 'undefined') {
						form_values.push(
						{
							label: jQuery( this ).data( 'label' ),
							value: jQuery( this ).val()
						}
					);
				}
			}
		);

		jQuery( 'form[data-id="' + current_form_id + '"] input[type="password"]' ).each(
			function () {
				if (jQuery( this ).data( 'label' ) !== '' && typeof jQuery( this ).data( 'label' ) !== 'undefined') {
						form_values.push(
						{
							label: jQuery( this ).data( 'label' ),
							value: jQuery( this ).val()
						}
					);
				}
			}
		);

		jQuery( 'form[data-id="' + current_form_id + '"] input[type="color"]' ).each(
			function () {
				if (jQuery( this ).data( 'label' ) !== '' && typeof jQuery( this ).data( 'label' ) !== 'undefined') {
					form_values.push(
						{
							label: jQuery( this ).data( 'label' ),
							value: jQuery( this ).val()
						}
					);
				}
			}
		);

		form_values.push(
			{
				label: 'total_amount',
				value: jQuery( '#amount_display_' + current_form_id ).text()
			}
		);

		var quantity_id = '#wpep_quantity_' + current_form_id;
		form_values.push(
			{
				label: 'quantity',
				value: jQuery( quantity_id ).val()
			}	
		);

		// check if discount applied or not
		if ( jQuery(`#theForm-${current_form_id} input[name="wpep-discount"]`).length > 0 ) { 
			var discount = jQuery(`#theForm-${current_form_id} input[name="wpep-discount"]`).val();
		} else {
			var discount = 0;
		}	
		
		var data = {
			'action': 'wpep_payment_request',
			'nonce': token,
			'first_name': first_name,
			'last_name': last_name,
			'email': email,
			'discount': discount,
			'amount': amount,
			'save_card': jQuery( '#saveCardLater' ).is( ':checked' ),
			'payment_type': payment_type,
			'current_form_id': current_form_id,
			'form_values': form_values,
			'currency': currency,
			'card_on_file': cof,
			'buyer_verification': verifyToken,
			'wp_payment_nonce': wpep_local_vars.wp_payment_nonce
		};


		if ( undefined !== jQuery( '#wpep_file_upload_field' )[0] ) {

			var files = jQuery( '#wpep_file_upload_field' )[0].files[0];

			if (undefined !== files) {

				var fd = new FormData();
				fd.append( 'file',files );
				fd.append( 'file_upload','true' );
				fd.append( 'action','wpep_file_upload' );
				fd.append('nonce', wpep_local_vars.nonce);
	
				jQuery.ajax(
					{
						url: wpep_local_vars.ajax_url,
						type: 'post',
						data: fd,
						contentType: false,
						processData: false,
						success: function(response){
							if (response != 0) {
								var parsed_response = JSON.parse( response );
								wpep_file_upload_url( parsed_response.uploaded_file_url );
								send_payment_request(data, current_form_id);
							} else {
		
							}
						}
					}
				);
	
			} else {

				send_payment_request(data, current_form_id);

			}

		} else {

			send_payment_request(data, current_form_id);
		}

	
		function wpep_file_upload_url(url) {
			form_values.push(
				{
					label: 'Uploaded URL',
					value: url
				}
			);
		}

		
	}


	
	
	// This function tokenizes a payment method. 
	// The ‘error’ thrown from this async function denotes a failed tokenization,
	// which is due to buyer error (such as an expired card). It is up to the
	// developer to handle the error and provide the buyer the chance to fix
	// their mistakes.
	async function tokenize(paymentMethod, options = false) {
		const tokenResult = await paymentMethod.tokenize(options);
		if (tokenResult.status === 'OK') {
			return tokenResult.token;
		} else {
		let errorMessage = `Tokenization failed-status: ${tokenResult.status}`;
		if (tokenResult.errors) {
			errorMessage += ` and errors: ${JSON.stringify(
			tokenResult.errors
			)}`;
		}
		jQuery( '.wpepLoader' ).remove();
		throw new Error(errorMessage);
		}
	}

	async function handlePaymentMethodSubmission(event, paymentMethod, current_form_id, currency, token = false, method = null, achOptions = null) {
		
		if ( method === null && method !== 'cashapp') {
			event.preventDefault();
		}


		try {

			var amount           = jQuery( '#amount_display_' + current_form_id ).text();
			amount               = amount.replace(/,/g, '');
			amount               = amount.match( /\d+/g ).map( Number );
			amount               = amount[1];
			if ( isNaN(amount) ){
				amount 		     = amount.replace("$", "");
			}

			var first_name       = jQuery( "#theForm-" + current_form_id + " input[name='wpep-first-name-field']" ).val();
			var last_name        = jQuery( "#theForm-" + current_form_id + " input[name='wpep-last-name-field']" ).val();
			var email            = jQuery( "#theForm-" + current_form_id + " input[name='wpep-email-field']" ).val();
			var payment_type     = jQuery( '#wpep_payment_form_type_' + current_form_id ).val();
			var save_card_later  = jQuery( '#saveCardLater' ).is( ':checked' );

			if ( payment_type == 'subscription' || save_card_later == true ) {
				var intent           = 'STORE';
			} else {
				var intent           = 'CHARGE';
			}

			if ( ! token ) {
				
				if ( method === 'ach' && jQuery('#ach-button').is(":visible") ) {
					tokenize(paymentMethod, achOptions).then(token => {
						createPayment(token, current_form_id, currency, false, false);
					});
				} else {
					tokenize(paymentMethod).then(token => {
						verifyBuyer(payments, token, amount, currency, intent, first_name, last_name, email).then(verifyToken => {
							createPayment(token, current_form_id, currency, false, verifyToken);
						});
					});
				}
				
				
			} else {

				verifyBuyer(payments, token, amount, currency, intent, first_name, last_name, email).then(verifyToken => {
					createPayment(token, current_form_id, currency, false, verifyToken);
				});
				
			}
		
	
		} catch (e) {
			console.log(e)
		}

		/* if (afterpay) {
			const afterpayButton = document.getElementById('afterpay-button');
			afterpayButton.addEventListener('click', async function (event) {
				alert('muneeb');
			  await handlePaymentMethodSubmission(event, afterpay, current_form_id, currency, false);
			});
		} */
	}

	async function verifyBuyer(payments, token, amount, currency, intent, first_name, last_name, email) {
		
		if ( isNaN(amount) ){
			amount 		     = amount.replace("$", "");
		}
		
		const verificationDetails = {
			amount: amount.toString(),
			billingContact: {
			  familyName: last_name,
			  givenName: first_name,
			  email: email,
			},
			currencyCode: currency,
			intent: intent
		};
	  
		const verificationResults = await payments.verifyBuyer(
		  token,
		  verificationDetails
		);
		return verificationResults.token;

	}

	function validateEmail(email) {
		var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test( String( email ).toLowerCase() );
	}


	function paymentButtonClicked(event, card, current_form_id, currency) {
		
		var first_name       = jQuery( "#theForm-" + current_form_id + " input[name='wpep-first-name-field']" ).val();
		var last_name        = jQuery( "#theForm-" + current_form_id + " input[name='wpep-last-name-field']" ).val();
		var email            = jQuery( "#theForm-" + current_form_id + " input[name='wpep-email-field']" ).val();
		var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		if (first_name != '' && last_name != '' && email != '' && emailRegex.test(email)){
			jQuery( '.wpep-alert' ).remove();
			if (jQuery( "#theForm-" + current_form_id ).find( 'div' ).hasClass( 'wpep-popup' )) {
				jQuery( "#theForm-" + current_form_id ).find( 'div.wpep-content' ).append( jQuery( '<div />' ).attr( 'class', 'wpepLoader' ).html( '<div class="initial-load-animation"><div class="payment-image icomoonLib"><span class="icon-pay"></span></div><div class="loading-bar"><div class="blue-bar"></div></div></div>' ) );
			} else {
				jQuery( "#theForm-" + current_form_id ).append( jQuery( '<div />' ).attr( 'class', 'wpepLoader' ).html( '<div class="initial-load-animation"><div class="payment-image icomoonLib"><span class="icon-pay"></span></div><div class="loading-bar"><div class="blue-bar"></div></div></div>' ) );
			}
		}
		

		var result1 = jQuery( "#theForm-" + current_form_id + " .wizard-fieldset.show .fieldMainWrapper div.wpep-required input" ).filter(
			function () {
				return jQuery.trim( jQuery( this ).val() ).length == 0
			}
		).length == 0;

		// client side validation
		var result2    = false;
		var emailCheck = false;
		var wpepError  = '';
		var termCond   = false;


		jQuery( "#theForm-" + current_form_id + " .wizard-fieldset.show .fieldMainWrapper div.wpep-required" ).each(
			function(){
				
				var current = jQuery( this );

				wpepError = jQuery( '<span />' ).attr( 'class', 'wpepError' ).html( 'Required Field' );

				if (current.find( 'input[type="text"]' ).length > 0) {

					if (current.find( 'input[type="text"]' ).val() == '' || current.find( 'input[type="text"]' ).val() == undefined) {
						if (current.find( 'input[type="text"] ~ .wpepError' ).length == 0) {
							jQuery( wpepError ).insertAfter( current.find( 'input[type="text"]' ) );
						}
						result2 = false;
						// return false;
					} else {
						wpepError = '';
						current.find( '.wpepError' ).remove();
						result2 = true;
					}
				}

				if (current.find( 'textarea' ).length > 0) {

					if (current.find( 'textarea' ).val() == '' || current.find( 'textarea' ).val() == undefined) {
						if (current.find( 'textarea ~ .wpepError' ).length == 0) {
							jQuery( wpepError ).insertAfter( current.find( 'textarea' ) );
						}
						result2 = false;
						// return false;
					} else {
						wpepError = '';
						current.find( '.wpepError' ).remove();
						result2 = true;
					}

				}

				if (current.find( 'select' ).length > 0) {
					if (current.find( 'select' ).val() == '' || current.find( 'select' ).val() == undefined) {
						if (current.find( 'select ~ .wpepError' ).length == 0) {
							jQuery( wpepError ).insertAfter( current.find( 'select' ) );
						}
						result2 = false;
					} else {
						wpepError = '';
						current.find( '.wpepError' ).remove();
						result2 = true;
					}
				}

				if (current.find( 'input[type="tel"]' ).length > 0) {

					if (current.find( 'input[type="tel"]' ).val() == '' || current.find( 'input[type="tel"]' ).val() == undefined) {
							if (current.find( 'input[type="tel"] ~ .wpepError' ).length == 0) {
							jQuery( wpepError ).insertAfter( current.find( 'input[type="tel"]' ) );
						}
						result2 = false;
						// return false;
					} else {
						wpepError = '';
						current.find( '.wpepError' ).remove();
						result2 = true;
					}
				}

				if (current.find( 'input[type="password"]' ).length > 0) {

					if (current.find( 'input[type="password"]' ).val() == '' || current.find( 'input[type="password"]' ).val() == undefined) {
						if (current.find( 'input[type="password"] ~ .wpepError' ).length == 0) {
							jQuery( wpepError ).insertAfter( current.find( 'input[type="password"]' ) );
						}
						result2 = false;
						// return false;
					} else {
						wpepError = '';
						current.find( '.wpepError' ).remove();
						result2 = true;
					}
				}

				if (current.find( 'input[type="color"]' ).length > 0) {

					if (current.find( 'input[type="color"]' ).val() == '' || current.find( 'input[type="color"]' ).val() == undefined) {
							if (current.find( 'input[type="color"] ~ .wpepError' ).length == 0) {
							jQuery( wpepError ).insertAfter( current.find( 'input[type="color"]' ) );
						}
						result2 = false;
						// return false;
					} else {
						wpepError = '';
						current.find( '.wpepError' ).remove();
						result2 = true;
					}
				}

				if (jQuery( '#theForm-' + current_form_id ).find( 'input[type="checkbox"]' ).length > 0) {

					// for checkbox input we need name because we can select atleast one at a time in group checkbox.
					var checkboxName = current.find( 'input[type="checkbox"]' ).attr( 'name' );

					if (checkboxName != undefined) {
						if ( ! (jQuery( '#theForm-' + current_form_id ).find( 'input[name="' + checkboxName + '"]' ).is( ':checked' )) ) {
							if (jQuery( '#theForm-' + current_form_id ).find( 'input[name="' + checkboxName + '"] ~ .wpepError' ).length == 0) {
								jQuery( wpepError ).insertAfter( current.find( 'input[name="' + checkboxName + '"]' ) );
							}
							result2 = false;
							// return false;
						} else {
							wpepError = '';
							jQuery( '#theForm-' + current_form_id ).find( 'input[name="' + checkboxName + '"] ~ .wpepError' ).remove();
							result2 = true;
						}
					}
				}

				if (jQuery( '#theForm-' + current_form_id ).find( 'input[type="radio"]' ).length > 0) {

					// for radio input we need name because we can select only one at a time.
					var radioName = current.find( 'input[type="radio"]' ).attr( 'name' );

					if (radioName != undefined) {
						if ( ! (jQuery( '#theForm-' + current_form_id ).find( 'input[name="' + radioName + '"]' ).is( ':checked' )) ) {
							if (jQuery( '#theForm-' + current_form_id ).find( 'input[name="' + radioName + '"] ~ .wpepError' ).length == 0) {
								jQuery( wpepError ).insertAfter( current.find( 'input[name="' + radioName + '"]' ) );
							}
							result2 = false;
						} else {
							wpepError = '';
							jQuery( '#theForm-' + current_form_id ).find( 'input[name="' + radioName + '"] ~ .wpepError' ).remove();
							result2 = true;
						}
					}
					
				}

				if (current.find( 'input[type="email"]' ).length > 0) {



					if (current.find( 'input[type="email"]' ).val() == '' || current.find( 'input[type="email"]' ).val() == undefined || validateEmail( current.find( 'input[type="email"]' ).val() ) == false ) {
						if (current.find( 'input[type="email"] ~ .wpepError' ).length == 0) {
							jQuery( wpepError ).insertAfter( current.find( 'input[type="email"]' ) );
						}
						emailCheck = false;

					} else {
						wpepError = '';
						current.find( '.wpepError' ).remove();
						emailCheck = true;
					}
				}

				if (current.find( 'input[type="date"]' ).length > 0) {

					if (current.find( 'input[type="date"]' ).val() == '' || current.find( 'input[type="date"]' ).val() == undefined) {

						if (current.find( 'input[type="date"] ~ .wpepError' ).length == 0) {
							jQuery( wpepError ).insertAfter( current.find( 'input[type="date"]' ) );
						}
						result2 = false;

					} else {

						wpepError = '';
						current.find( '.wpepError' ).remove();
						result2 = true;
					}
				}

				if (jQuery( '#theForm-' + current_form_id ).find( 'input[type="file"]' ).length) {

					if (jQuery( '#theForm-' + current_form_id ).find( 'input[type="file"]' ).val() !== '') {

						var file = jQuery( '#theForm-' + current_form_id ).find( 'input[type="file"]' ).val();
						var ext  = file.split( '.' ).pop();

						if ( 'exe' == ext || 'dll' == ext || 'deb' == ext ) {

							if ( jQuery( '#theForm-' + current_form_id ).find( 'input[type="file"] ~ .wpepError' ).length == 0 ) {

								alert( 'That file looks suspecious! Please upload another with supported extension.' );
								result2 = false;

							}
						}

					}

				}

				if (current.find( 'input[type="url"]' ).length > 0) {

					if (current.find( 'input[type="url"]' ).val() == '' || current.find( 'input[type="url"]' ).val() == undefined) {
						if (current.find( 'input[type="url"] ~ .wpepError' ).length == 0) {
							jQuery( wpepError ).insertAfter( current.find( 'input[type="url"]' ) );
						}
						result2 = false;
					} else {
						wpepError = '';
						current.find( '.wpepError' ).remove();
						result2 = true;
					}
				}

			}
		);

		var termContainer = jQuery( "#theForm-" + current_form_id + " #termsCondition-" + current_form_id ).is( ':checked' );

		if (termContainer == false) {
			if (jQuery( "#theForm-" + current_form_id + " div.termsCondition.wpep-required" ).find( '.wpepError' ).length == 0) {
				wpepError = jQuery( '<span />' ).attr( 'class', 'wpepError' ).html( 'Required Field' );
				jQuery( "#theForm-" + current_form_id + " div.termsCondition.wpep-required" ).append( jQuery( wpepError ) );
			}
			termCond = false;		
		} else {
			wpepError = '';
			jQuery( "#theForm-" + current_form_id + " div.termsCondition.wpep-required" ).find( '.wpepError' ).remove();
			termCond = true;		
		}

		var current           = jQuery( event.target );
		var result3           = false;
		var next              = jQuery( event.target );
		var currentActiveStep = jQuery( event.target ).parents( '.form-wizard' ).find( '.form-wizard-steps .active' );

		if ( jQuery('input[name="wpep-selected-amount"]').length > 0 ) {
			if ( jQuery('input[name="wpep-selected-amount"]').val() == '' ) {
				result3 = false;
				return false;
			} else {
				result3 = true;
			}
		}

		var selected_payment_tab = current.parents( 'form' ).find( 'ul.wpep_tabs li.tab-link.current' ).data( 'tab' );
		var finalCheck = jQuery( "#theForm-" + current_form_id + " .wizard-fieldset.show .fieldMainWrapper div.wpep-required" ).find( "span.wpepError" ).length;
		

		if (jQuery( 'input[name="card_on_file"]' ).is( ':checked' )) {
			var card_on_file = jQuery( 'input[name="card_on_file"]' ).val();
			createPayment(false, current_form_id, currency, card_on_file, false);
		}else{
			// if payment type is credit card
			if (selected_payment_tab == 'creditCard' && result1 == true && result3 == true && wpepError == '' && termCond == true && finalCheck == 0) {
				handlePaymentMethodSubmission(event, card, current_form_id, currency);
			} 
		}
		
		
					
	}
jQuery( window ).load(
	function () {
		jQuery( 'ul.wpep_tabs li' ).click(
			function () {
				var tab_id = jQuery( this ).attr( 'data-tab' );

				if ('cashapp' === tab_id){
					jQuery('.wpep-single-form-submit-btn').attr('disabled', 'disabled');
					jQuery('.wpep-single-form-submit-btn').css('cursor', 'not-allowed');
					// jQuery('.wpep-single-form-submit-btn').css('visibility', 'hidden');
				} else if( 'achdebit' === tab_id ) {
					jQuery('.wpep-single-form-submit-btn').attr('disabled', 'disabled');
					jQuery('.wpep-single-form-submit-btn').css('cursor', 'not-allowed');
				}else if( 'afterpay' === tab_id ) {
					jQuery('.wpep-single-form-submit-btn').attr('disabled', 'disabled');
					jQuery('.wpep-single-form-submit-btn').css('cursor', 'not-allowed');
				} else {
					jQuery('.wpep-single-form-submit-btn').removeAttr('disabled');
					jQuery('.wpep-single-form-submit-btn').css('cursor', 'pointer');
					// jQuery('.wpep-single-form-submit-btn').css('visibility', 'visible');
				}
				
				jQuery( 'ul.wpep_tabs li' ).removeClass( 'current' );
				jQuery( '.tab-content' ).removeClass( 'current' );

				jQuery( this ).addClass( 'current' );
				jQuery( "#" + tab_id ).addClass( 'current' );
			}
		);
	})
	


	jQuery('.wizard-section').css('visibility', 'visible');
    jQuery('.parent-loader').remove();

	jQuery("form div.qty").append('<div class="outer-button"><div class="inc btnqty"><i class="fa fa-plus"></i></div><div class="dec btnqty"><i class="fa fa-minus"></i></div></div>');
	jQuery(".btnqty").click('click', function () {

		var form_id = jQuery(this).parents('form').data('id');
		var currency = jQuery(this).parents('form').data('currency');

		var $button = jQuery(this);
		var oldQty = $button.parent().parent().find("input").val();
		if ($button.html() == '<i class="fa fa-plus"></i>') {
			var newQty = parseFloat(oldQty) + 1;
		} else {
			// Don't allow decrementing less than zero
			if (oldQty > 0) {
				var newQty = parseFloat(oldQty) - 1;
			} else {
				newQty = 0;
			}
		}

		$button.parent().parent().find("input").val(newQty);

		calculate(form_id, currency);

	
				
				if (typeof afterpay.destroy === "function") { 
						afterpay.destroy();
						jQuery('#afterpay-button').html('')
						displayAfterPay( payments, form_id, currency );
				}

				if (typeof cashAppPay.destroy === "function") { 
					cashAppPay.destroy();
					displayCashApp( payments, form_id, currency );	
				}		

				// console.clear();

	});


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

	jQuery( '.paynow' ).click(
		function () {

			var form_id = jQuery( this ).parents( 'form' ).data( 'id' );
			
			if ( jQuery(`#theForm-${form_id} input[name="wpep-discount"]`).length > 0 ) { 
				jQuery(`#theForm-${form_id} input[name="wpep-discount"]`).remove();
			}

			if ( jQuery(`#theForm-${form_id} .wpep-alert-coupon`).length > 0 ) {
				jQuery(`#theForm-${form_id} .wpep-alert-coupon`).remove();
			}

			jQuery( 'form[data-id="' + form_id + '"] .display' ).text( jQuery( this ).text() );
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
			}
		
			if ( jQuery(`#theForm-${form_id} .wpep-alert-coupon`).length > 0 ) {
				jQuery(`#theForm-${form_id} .wpep-alert-coupon`).remove();
			}


			if (val != '' && val >= min && val <= max) {
				
				currency = prepare_display_amount(currencyType, currency, val);
			
				jQuery( this ).val( val );
				jQuery( 'form[data-id="' + form_id + '"] .display' ).text( currency );
				jQuery( 'form[data-id="' + form_id + '"] .display' ).next( 'input[name="wpep-selected-amount"]' ).val( jQuery( this ).val() ).trigger('change');
				jQuery( 'form[data-id="' + form_id + '"] .wpep-single-form-submit-btn' ).removeClass( 'wpep-disabled' );
				jQuery( 'form[data-id="' + form_id + '"] .wpep-wizard-form-submit-btn' ).removeClass( 'wpep-disabled' );

			} else {

				currency = prepare_display_amount(currencyType, currency);
				
				jQuery( this ).val( '' );
				jQuery( 'form[data-id="' + form_id + '"] .display' ).text( '' );
				jQuery( 'form[data-id="' + form_id + '"] .display' ).next( 'input[name="wpep-selected-amount"]' ).val( '' ).trigger('change');
				jQuery( 'form[data-id="' + form_id + '"] .wpep-single-form-submit-btn' ).addClass( 'wpep-disabled' );
				jQuery( 'form[data-id="' + form_id + '"] .wpep-wizard-form-submit-btn' ).addClass( 'wpep-disabled' );
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


	jQuery( '.wpep_delete_tabular_product' ).click(
		function() {

			var form_id  = jQuery( this ).parents( 'form' ).data( 'id' );
			
			if ( jQuery(`#theForm-${form_id} input[name="wpep-discount"]`).length > 0 ) { 
				jQuery(`#theForm-${form_id} input[name="wpep-discount"]`).remove();
			}
		
			if ( jQuery(`#theForm-${form_id} .wpep-alert-coupon`).length > 0 ) {
				jQuery(`#theForm-${form_id} .wpep-alert-coupon`).remove();
			}

			var currency = jQuery( this ).parents( 'form' ).data( 'currency' );

			jQuery( this ).closest( '.wpItem' ).hide();
			calculate( form_id, currency );

		}
	);

});

function calculate(form_id, currency) {

	var currency_codes = ['CAD', 'GBP', 'AUD', 'JPY', 'EUR', 'USD'];
	var currency_symbol = ['C$', '£', 'A$', '¥', '€', '$'];
		if (wpep_local_vars.currencySymbolType == 'symbol') {
		jQuery.each( currency_codes, function( i, val ) {
			if( currency == val ) {
				currency = currency_symbol[i];
			}	
		});
	}

	if (wpep_local_vars.currencySymbolType == 'code') {

		jQuery.each( currency_symbol, function( i, val ) {
			if( currency == val ) {
				currency = currency_codes[i];
			}
		});
	}

	var item_display = 'yes';

	if ( jQuery( 'form[data-id="' + form_id + '"] .wpItem' ).length ) {

		jQuery( ".wpItem" ).each(
			function () {

				var priceVal = jQuery( this ).find( 'input.price' ).val();
				var qtyVal   = jQuery( this ).find( "input.qty" ).val();
				var costVal  = (priceVal * qtyVal);
				jQuery( this ).find( 'input.cost' ).val( (costVal).toFixed( 2 ) );

			}
		);

		var subtotalVal = 0;
		jQuery( '.cost' ).each(
			function () {

				item_display = jQuery( this ).closest( '.wpItem' ).css( 'display' ) == 'none' ? 'no' : 'yes';
				if ('yes' == item_display) {
					subtotalVal += parseFloat( jQuery( this ).val() );
				}

			}
		);

		jQuery( '.subtotal' ).val( (subtotalVal).toFixed( 2 ) );

		var total = parseFloat( subtotalVal );

		total     = (total).toFixed( 2 );

		jQuery( 'form[data-id="' + form_id + '"] .display' ).text( total + ' ' + currency );

		var layout = jQuery('#wpep_amount_layout').val();
		if (layout !== 'tabular_layout') {
			jQuery( 'form[data-id="' + form_id + '"] .display' ).next( 'input[name="wpep-selected-amount"]' ).val( currency ).trigger('change');
		}
		jQuery( 'form[data-id="' + form_id + '"] .wpep-single-form-submit-btn' ).removeClass( 'wpep-disabled' );
		jQuery( 'form[data-id="' + form_id + '"] .wpep-wizard-form-submit-btn' ).removeClass( 'wpep-disabled' );

	}
}

const filterNum = (str) => {
	const numericalChar = new Set([ ".",",","0","1","2","3","4","5","6","7","8","9" ]);
	str = str.split("").filter(char => numericalChar.has(char)).join("");
	return str;
}

 // jQuery('.otherPayment').on('input', function() {
  // var customAmount = jQuery(this).val();
  // jQuery('input[name="wpep-selected-amount"]').val(customAmount).trigger('change');
// });


 // Declare a variable to hold the timeout ID
var timeoutId;
// Bind an event handler to the custom amount input field
jQuery('.otherPayment').on('input', function() {
	// jQuery('#afterpay-button').empty()
  var customAmount = jQuery(this).val();
	
  clearTimeout(timeoutId);
  timeoutId = setTimeout(function() {
	jQuery('.loader').show();
    jQuery('input[name="wpep-selected-amount"]').val(customAmount).trigger('change');
	 
  }, 500);
}); 



jQuery( 'input[name="wpep-selected-amount"]' ).on('change paste keyup', function() {
	var current_form_id = jQuery( 'form.wpep_payment_form' ).data( 'id' );
	var currency        = jQuery( 'form.wpep_payment_form' ).data( 'currency' );
	
	var form_id = jQuery(this).parents('form.wpep_payment_form').data('id');
	var discount = 0.00;
	var signup_total = 0.00;
	//var amount = parseFloat(jQuery('#theForm-' + form_id).find('#amount_display_' + form_id).text());
	var amount = parseFloat( filterNum( jQuery(this).val() ) );
	if ( jQuery(`#theForm-${form_id}`).find('input[name="wpep-signup-amount"]').length > 0 ) {
		var signup_total = parseFloat(jQuery(`#theForm-${form_id}`).find('input[name="wpep-signup-amount"]').val());
	}
	//var gross_total = parseFloat(jQuery(`#theForm-${form_id}`).find('input[name="gross_total"]').val());
	var total_amount = amount + signup_total;
	if (isNaN(total_amount)) {
		if(!localStorage.getItem("alertDisplayed")) {
			alert('Select in Range Number');
			localStorage.setItem("alertDisplayed", "true");
		}
		var total_amount = 0;
	}
	var unit_cost = 0;
	if ( jQuery( '#one_unit_cost' ).length > 0 ) {
		var one_unit_cost = jQuery( '#one_unit_cost' ).val();
		unit_cost = parseFloat(one_unit_cost.split( " " )[0]).toFixed(2);
	}
	jQuery(`#theForm-${form_id}`).find('.wpep-fee-subtotal .fee_value').text( unit_cost + ' ' + wpep_local_vars.wpep_square_currency_new );
	jQuery(`#theForm-${form_id}`).find('.wpep-fee-total .fee_value').text( parseFloat( total_amount ).toFixed(2) + ' ' + wpep_local_vars.wpep_square_currency_new );
	jQuery(`#amount_display_${form_id}`).siblings('input[name="wpep-selected-amount"]').val(parseFloat( total_amount ).toFixed(2));
	if(wpep_local_vars.currencySymbolType == 'symbol'){
		var currency = '$';
	} else{
		var currency = 'USD';
	}
	 jQuery(`#amount_display_${form_id}`).text(currency + '' + parseFloat(total_amount).toFixed(2));
	if ( jQuery(`#theForm-${form_id}`).find('input[name="gross_total"]').length > 0 ) {
		jQuery(`#theForm-${form_id}`).find('input[name="gross_total"]').val(total_amount);
	}
	if ( jQuery(`#theForm-${form_id}`).find('input[name="wpep-discount"]').length > 0 ) {
		discount = parseFloat(jQuery(`#theForm-${form_id}`).find('input[name="wpep-discount"]').val());
	}

	



});



function afterPaybuildPaymentRequest(payments, current_form_id, currency) {

	var amountElement = document.getElementById('amount_display_' + current_form_id);
	var amountText = amountElement.textContent.trim();
	var amount = amountText.replace(/[A-Za-z$]/g, '');
	var min          = parseFloat( jQuery( '.otherPayment' ).attr( 'min' ) );
	if(amount == 0){
		amount = min;
	}
	const req = payments.paymentRequest({
	  countryCode: 'US',
	  currencyCode: currency,
	  total: {
		amount: amount.toString(),
		label: 'Total',
	  },
	  requestShippingContact: true,
	});
 
	// Note how afterpay has its own listeners
	req.addEventListener('afterpay_shippingaddresschanged', function (_address) {
	  return {
		shippingOptions: [
		  {
			amount: '0.00',
			id: 'shipping-option-1',
			label: 'Free',
			taxLineItems: [
			  {
				amount: '0.00',
				label: 'Tax'
			  }
			],
			total: {
			  amount: amount.toString(),
			  label: 'total',
			},
		  },
		],
	  };
	});
	return req;
	 
}
  function cashAppbuildPaymentRequest(payments, current_form_id, currency) {
		var amount = jQuery('input[name="wpep-selected-amount"]').val();
		amount = amount.trim();
		amount = amount.split(' ');
		amount = amount[0];
		amount = amount.replace("$", "");
		var min          = parseFloat( jQuery( '.otherPayment' ).attr( 'min' ) );
		if(amount == 0){
				amount = min;
		}
	  
		jQuery('#cashapp-amount').hide();
		paymentRequest = payments.paymentRequest({
		  countryCode: 'US',
		  currencyCode: currency,
		  total: {
			amount: amount.toString(),
			label: 'Total',
		  },
		});
	  

	  return paymentRequest;
}



  async function initializeAfterpay(payments, current_form_id, currency) {
	  if (jQuery( '#afterpay-button' ).html().length > 1) {
			afterpay.destroy();
		}
	var max          = parseFloat( jQuery( '.otherPayment' ).attr( 'max' ) );
	var min          = parseFloat( jQuery( '.otherPayment' ).attr( 'min' ) );
	var val          = jQuery(  '.otherPayment'  ).val();
	if('code' == wpep_local_vars.currencySymbolType) {
		currency = wpep_local_vars.wpep_square_currency_new;
	} else {
		currency = wpep_local_vars.wpep_currency_symbol;
	}
	var amount           = jQuery( '#amount_display_' + current_form_id ).text();
	amount               = amount.trim();
	amount 				 = amount.replace(currency, "");
	
	if (val == '') {
		jQuery( '.otherPayment' ).val(min);
		val = min;
	}
	if (!isNaN(val) && val >= min && val <= max) {
		const paymentRequest = afterPaybuildPaymentRequest(payments, current_form_id, currency);
		afterpay = await payments.afterpayClearpay(paymentRequest);
		await afterpay.attach('#afterpay-button');
		return afterpay;
	}
	else if (!isNaN(amount) && wpep_local_vars.wpep_square_user_defined_amount != 'on') {
		const paymentRequest = afterPaybuildPaymentRequest(payments, current_form_id, currency);
		afterpay = await payments.afterpayClearpay(paymentRequest);
		await afterpay.attach('#afterpay-button');
		return afterpay;
	}
	else{
		jQuery('#afterpay-amount').show();
	} 
}

jQuery('#afterpay-button').click(function (event) {
  var current_form_id = jQuery('form.wpep_payment_form').data('id');
  var first_name = jQuery("#theForm-" + current_form_id + " input[name='wpep-first-name-field']").val();
  var last_name = jQuery("#theForm-" + current_form_id + " input[name='wpep-last-name-field']").val();
  var email = jQuery("#theForm-" + current_form_id + " input[name='wpep-email-field']").val();
  var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (first_name == '' || last_name == '' || email == '' || !emailRegex.test(email)) {
    event.stopImmediatePropagation();
    jQuery("#theForm-" + current_form_id + " .wpepError").remove();
    jQuery("#theForm-" + current_form_id + " .wizard-fieldset.show .fieldMainWrapper div.wpep-required").each(
      function(){
        var current = jQuery(this);
        wpepError = jQuery('<span />').attr('class', 'wpepError').html('Required Field');
        jQuery(wpepError).insertAfter(current.find('input[type="text"]'));
        jQuery(wpepError).insertAfter(current.find('input[type="email"]'));
      }
    );
   
  }
});

jQuery('#ach-button').click(function (event) {
  var current_form_id = jQuery('form.wpep_payment_form').data('id');
  var first_name = jQuery("#theForm-" + current_form_id + " input[name='wpep-first-name-field']").val();
  var last_name = jQuery("#theForm-" + current_form_id + " input[name='wpep-last-name-field']").val();
  var email = jQuery("#theForm-" + current_form_id + " input[name='wpep-email-field']").val();
  var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (first_name == '' || last_name == '' || email == '' || !emailRegex.test(email)) {
    event.stopImmediatePropagation();
    jQuery("#theForm-" + current_form_id + " .wpepError").remove();
    jQuery("#theForm-" + current_form_id + " .wizard-fieldset.show .fieldMainWrapper div.wpep-required").each(
      function(){
        var current = jQuery(this);
        wpepError = jQuery('<span />').attr('class', 'wpepError').html('Required Field');
        jQuery(wpepError).insertAfter(current.find('input[type="text"]'));
        jQuery(wpepError).insertAfter(current.find('input[type="email"]'));
      }
    );
   
  }
});
jQuery('#cash-app-pay').click(function (event) {
  var current_form_id = jQuery('form.wpep_payment_form').data('id');
  var first_name = jQuery("#theForm-" + current_form_id + " input[name='wpep-first-name-field']").val();
  var last_name = jQuery("#theForm-" + current_form_id + " input[name='wpep-last-name-field']").val();
  var email = jQuery("#theForm-" + current_form_id + " input[name='wpep-email-field']").val();
  var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (first_name == '' || last_name == '' || email == '' || !emailRegex.test(email)) {
    event.stopImmediatePropagation();
    jQuery("#theForm-" + current_form_id + " .wpepError").remove();
    jQuery("#theForm-" + current_form_id + " .wizard-fieldset.show .fieldMainWrapper div.wpep-required").each(
      function(){
        var current = jQuery(this);
        wpepError = jQuery('<span />').attr('class', 'wpepError').html('Required Field');
        jQuery(wpepError).insertAfter(current.find('input[type="text"]'));
        jQuery(wpepError).insertAfter(current.find('input[type="email"]'));
      }
    );
   
  }
});
  async function initializeCashApp(payments, current_form_id, currency) {
	var max          = parseFloat( jQuery( '.otherPayment' ).attr( 'max' ) );
	var min          = parseFloat( jQuery( '.otherPayment' ).attr( 'min' ) );
	var val          = jQuery(  '.otherPayment'  ).val();

	if('$' == currency) {
		currency = 'USD';
	}
	var amountElement = document.getElementById('amount_display_' + current_form_id);
	var amountText = amountElement.textContent.trim();
	var amount = amountText.replace(/[A-Za-z$]/g, '');
	if (val == '') {
		jQuery( '.otherPayment' ).val(min);
		val = min;
	}
	if ((val != '' && val >= min && val <= max) || (!isNaN(amount) && amount >= min && amount <= max) && wpep_local_vars.wpep_square_user_defined_amount == 'on') {
		const paymentRequest = cashAppbuildPaymentRequest(payments, current_form_id, currency)
		cashAppPay = await payments.cashAppPay(paymentRequest,{
		  redirectURL: 'https://my.website/checkout',
		  referenceId: 'my-website-00000001',
		});
		await cashAppPay.attach('#cash-app-pay');
		return cashAppPay;
	}
	
	else if (!isNaN(amount) && wpep_local_vars.wpep_square_user_defined_amount != 'on') {
		const paymentRequest = cashAppbuildPaymentRequest(payments, current_form_id, currency)
		cashAppPay = await payments.cashAppPay(paymentRequest,{
		  redirectURL: 'https://my.website/checkout',
		  referenceId: 'my-website-00000001',
		});
		await cashAppPay.attach('#cash-app-pay');
		return cashAppPay;
	}else{
		jQuery('#cashapp-amount').show();
	} 
  }




function initializePayments( appId, locationId ) {
	return window.Square.payments(appId, locationId);
}

async function displayAfterPay(payments, current_form_id, currency) {
	if('$' == currency) {
		currency = 'USD';
	}
var amountElement = document.getElementById('amount_display_' + current_form_id);
var amountText = amountElement.textContent.trim();
var amount = amountText.replace(/[A-Za-z$]/g, '');
	if(currency !== amount){
	
		let afterpay;
		try {
			afterpay = await initializeAfterpay(payments, current_form_id, currency);
		} catch (e) {
			console.error('Initializing Afterpay/Clearpay failed', e);
		}
	}else{
			jQuery('#afterpay-amount').show();
	} 
	
	let timeoutId;
	jQuery('.otherPayment').on('input', function() {

		jQuery('#afterpay-amount').hide();
		if (jQuery( '#afterpay-button' ).html().length > 1) {
			afterpay.destroy();
			cashAppPay.destroy();
		}
	clearTimeout(timeoutId);
    timeoutId = setTimeout(function() {
     
	   let afterpay;
		try {

			afterpay =  initializeAfterpay(payments, current_form_id, currency);
			jQuery('.loader').hide();
		} catch (e) {
			console.error('Initializing Afterpay/Clearpay failed', e);
		}
	  
	 
    }, 1000); 
  });
	let timeoutpayId;
	jQuery('.paynow').on('click', function() {

		jQuery('#afterpay-amount').hide();
		if (jQuery( '#afterpay-button' ).html().length > 1) {	
			afterpay.destroy();
			cashAppPay.destroy();
		}
	clearTimeout(timeoutpayId);
    timeoutpayId = setTimeout(function() {
     
	   let afterpay;
		try {

			afterpay =  initializeAfterpay(payments, current_form_id, currency);
			jQuery('.loader').hide();
		} catch (e) {
			console.error('Initializing Afterpay/Clearpay failed', e);
		}
	  
	 
    }, 1000); 
  });
		
	return afterpay;		
}

jQuery('.otherpayment').click( function (event) {
let timeoutId;
	jQuery('.otherPayment').on('input', function() {
		jQuery('#cashapp-amount').hide();
		if(jQuery('#cash_app_pay_v1_element').is(':visible')){
			if(jQuery( '#cash_app_pay_v1_element' ).html().length > 1){
					cashAppPay.destroy();
					afterpay.destroy();
			} 
		}
		   
	clearTimeout(timeoutId);
    timeoutId = setTimeout(function() {
		let cashAppPay;
		  try {
			 	
			// Check if Cash App Pay is already attached before reinitializing
			if (typeof cashAppPay === 'undefined') {
				var current_form_id = jQuery(  'form.wpep_payment_form'  ).data( 'id' );
				var currency        = jQuery(  'form.wpep_payment_form'  ).data( 'currency' );
			  cashAppPay =  initializeCashApp(payments,current_form_id, currency);
				jQuery('.loader').hide();	
			}
			
		  } catch (e) {
			console.error('Initializing Cash App Pay failed', e);
		  }
		
    }, 1000); 
  });
})
jQuery('.paynow').click( function (event) {
let timeoutId;
		jQuery('#cashapp-amount').hide();
		if (jQuery('#cash_app_pay_v1_element').is(":visible") && jQuery('#cash_app_pay_v1_element').html().length > 1) {	
			afterpay.destroy();
			cashAppPay.destroy();
		}
	clearTimeout(timeoutId);
    timeoutId = setTimeout(function() {
		let cashAppPay;
		  try {
			 	
			// Check if Cash App Pay is already attached before reinitializing
			if (typeof cashAppPay === 'undefined') {
				var current_form_id = jQuery(  'form.wpep_payment_form'  ).data( 'id' );
				var currency        = jQuery(  'form.wpep_payment_form'  ).data( 'currency' );
			  cashAppPay =  initializeCashApp(payments,current_form_id, currency);
				jQuery('.loader').hide();	
			}
			
		  } catch (e) {
			console.error('Initializing Cash App Pay failed', e);
		  }
		
    }, 1000); 
})
async function displayCashApp(payments, current_form_id, currency) {
	if('$' == currency) {
		currency = 'USD';
	}
var amountElement = document.getElementById('amount_display_' + current_form_id);
var amountText = amountElement.textContent.trim();
var amount = amountText.replace(/[A-Za-z$]/g, '');
	
	if(currency !== amount){
		let cashAppPay;
		try {
		  cashAppPay = await initializeCashApp(payments,current_form_id, currency);
		} catch (e) {
		  console.error('Initializing Cash App Pay failed', e);
		}
	} else {
		jQuery('#cashapp-amount').show();
	}
	
		
	return cashAppPay;		
}