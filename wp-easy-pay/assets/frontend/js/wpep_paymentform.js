let googlePay = {};
let afterpay = {};
let cashAppPay = {};
let applePay = {};
let recaptcha = '';
// Flags to prevent multiple simultaneous initializations
let afterpayInitializing = false;
let cashAppPayInitializing = false;
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

				// Bind an event handler to the custom amount input field
				jQuery('.other-'+current_form_id).click( function () {
					let timeoutId;
					jQuery('.other-'+current_form_id).on('input', function() {
					  var customAmount = jQuery(this).val();
					  clearTimeout(timeoutId);
					  timeoutId = setTimeout(function() {
						jQuery('.loader').show();
						jQuery(`#theForm-${current_form_id}`).find('input[name="one_unit_cost"]').val(customAmount);
						jQuery('#wpep-selected-amount-'+ current_form_id ).val(customAmount).trigger('change');
						jQuery('#wpep-single-form-'+ current_form_id ).click();
					  }, 500);
					}); 
				});
				if ( wpep_local_vars.applepay == 'on') {
					
					    var applePayButtonTarget = '';
						 jQuery('#wpep-selected-amount-'+current_form_id).val(jQuery('#amount_display_'+current_form_id).html());
            			     	    
					    applePay[current_form_id] = await displayApplePay( payments, current_form_id, currency ,applePay, applePayButtonTarget);
					    
						let timeoutpayId;

            			function handleDOMChanges(app_btn) {
            			  clearTimeout(timeoutpayId);
            			  timeoutpayId = setTimeout(async function() {
            			  
            			      jQuery('#wpep-selected-amount-'+current_form_id).val(jQuery('#amount_display_'+current_form_id).html());
            			      
            				try {
            				    
            				    jQuery('#applePay-'+current_form_id).remove(); 
            	                jQuery('#applePayfather-'+current_form_id).append('<div id="applePay-'+current_form_id+'" class="apple-pay-button tab-content-'+current_form_id+'"></div>');
            	                
            					var applePayButtonTarget = document.getElementById('applePay-'+current_form_id);
            					jQuery('#applePayfather-'+current_form_id).css('display', 'none');
            					applePay[current_form_id] = await displayApplePay( payments, current_form_id, currency ,applePay, applePayButtonTarget);
            					//jQuery('#testing_apple').html('');
            					applePayButtonTarget.addEventListener('click', eventHandlerapple);
            				
            					setTimeout(function() {
            					    if(jQuery('#applePayfather-'+current_form_id).hasClass('current')){
            					        jQuery('#applePayfather-' + current_form_id).css('display', 'block');
            					    }
                                }, 1200); // 2000 milliseconds = 2 seconds
                                
            					
            				} catch (e) {
            				  console.error('Initializing Applepay failed', e);
            				}
            			  }, 10); 	 
            			}
            			// Use Mutation Observer to detect DOM changes
            			const observer = new MutationObserver(handleDOMChanges);
            			// Observe changes in the amount display element
            			const amountDisplayElement = document.getElementById('amount_display_' + current_form_id);
            			if (amountDisplayElement) {
            			  observer.observe(amountDisplayElement, { subtree: true, childList: true });
            			}
					applePayButtonTarget = document.getElementById('applePay-'+current_form_id);
					applePayButtonTarget.addEventListener('click', eventHandlerapple);
				
					async function eventHandlerapple(event) {
					    
						event.preventDefault();
						event.stopImmediatePropagation();
						try {
						card = '';
						var res = false;
						res = paymentButtonClicked(event, card, current_form_id, currency);
						
						if(res || wpep_local_vars.wpep_show_wizard == 'on'){
							const result = await applePay[current_form_id].tokenize();
							if (result.status === 'OK') {
								await handlePaymentMethodSubmission(event, applePay, current_form_id, currency,  result.token);
							}
						} else {
							event.preventDefault();
							event.stopImmediatePropagation();
						} 
						} catch (e) {
						console.error(e);
						}
					};
				}
				if ( wpep_local_vars.gpay == 'on') {
				    setTimeout(function() {
    					googlePay[current_form_id] = displayGooglePay( payments, current_form_id, currency ,googlePay);
    					var googlePayButtonTarget = document.getElementById('google-pay-button-'+current_form_id);
    					googlePayButtonTarget.addEventListener('click', eventHandler);
    				
    					async function eventHandler(event) {
    						event.preventDefault();
    						event.stopImmediatePropagation();
    						
    						try {
    						
    						card = '';
    						var res = false;
    						res = paymentButtonClicked(event, card, current_form_id, currency);
    						
    						if(res || wpep_local_vars.wpep_show_wizard == 'on'){
    							const result = await googlePay[current_form_id].tokenize();
    							
    							if (result.status === 'OK') {
    								await handlePaymentMethodSubmission(event, googlePay, current_form_id, currency, result.token);
    							}
    						} else {
    							event.preventDefault();
    							event.stopImmediatePropagation();
    						} 
    						} catch (e) {
    						console.error(e);
    						}
    					};
    					
                    }, 2000);
				}

				if ( wpep_local_vars.afterpay == 'on') {
			
					afterpay = displayAfterPay( payments, current_form_id, currency );
					
					if (afterpay !== undefined) {
						const afterpayButton = document.getElementById('afterpay-button-'+current_form_id);
						// if(jQuery('#afterpay-'+current_form_id).is(':visible')){
							afterpayButton.addEventListener('click', async function (event) {
							
							card = '';
							var res = false;
							res = paymentButtonClicked(event, card, current_form_id, currency);
							if(res || wpep_local_vars.wpep_show_wizard == 'on'){
								await handlePaymentMethodSubmission(event, afterpay, current_form_id, currency, false);
								} else {
									event.preventDefault();
									event.stopImmediatePropagation();
								}
							});
						// }
					} 	
		
				}

				if ( wpep_local_vars.cashapp == 'on') {
			
					cashAppPay = displayCashApp( payments, current_form_id, currency );
					
					jQuery('#cash-app-pay-'+current_form_id).on('click', function(ee){
						card = '';
						var res = paymentButtonClicked(event, card, current_form_id, currency);
						
						if(res || wpep_local_vars.wpep_show_wizard == 'on'){
							 cashAppPay.addEventListener('ontokenization', function (event) {
								const { tokenResult, error } = event.detail;
								if (error) {
								  // developer handles error
								} else if (tokenResult.status === 'OK') {
									if(res || wpep_local_vars.wpep_show_wizard == 'on'){
										jQuery( "#theForm-" + current_form_id ).append( jQuery( '<div />' ).attr( 'class', 'wpepLoader' ).html( '<div class="initial-load-animation"><div class="payment-image icomoonLib"><span class="icon-pay"></span></div><div class="loading-bar"><div class="blue-bar"></div></div></div>' ) );
										handlePaymentMethodSubmission(event, cashAppPay, current_form_id, currency, tokenResult.token, 'cashapp');
									}
								}
								
							  }); 
						} else {
							ee.preventDefault();
							ee.stopImmediatePropagation();
						}
						
					
					});
				}
				if ( wpep_local_vars.giftcard == 'on') {

					var giftcard;
					jQuery(`ul.wpep_tabs-${current_form_id} li.tab-link`).on('click', async function () {
						const selectedTab = jQuery(this).data('tab');

						if (selectedTab === 'giftcard-' + current_form_id) {

							giftcard = await displayGiftcard( payments, current_form_id, currency );
							console.log(' clickiftcard');
							jQuery('.wpep-single-form-' + current_form_id).click(async function (event) {
								
								event.preventDefault();
								var res = paymentButtonClicked(event, giftcard, current_form_id, currency);
								if(res || wpep_local_vars.wpep_show_wizard == 'on'){
									await handlePaymentMethodSubmissiongftcpay(event, giftcard, current_form_id, currency, false);
								}
							});
						
							jQuery('.wpep-wizard-form-' + current_form_id).click(async function (event) {
								event.preventDefault();
								var res = paymentButtonClicked(event, giftcard, current_form_id, currency);
								if(res || wpep_local_vars.wpep_show_wizard == 'on'){
									const result = await giftcard.tokenize();
									if (result.status === 'OK') {
										await handlePaymentMethodSubmissiongftcpay(event, giftcard, current_form_id, currency, result.token);
									}
								}
							});

						}
						else{

							if(giftcard){
								giftcard.detach();
							}
							
						}
					});

				}

				async function initializeCard(payments, current_form_id) {
					const card = await payments.card();
					await card.attach('#card-container-'+current_form_id); 
					return card; 
				}

						   

				async function initializeACH(payments) {
					let redirectURI   = 'https://development-cloud.local/421-2/';
					let transactionId = 'wpeasypay-01';
					const ach = await payments.ach({ redirectURI, transactionId });
					return ach;
				}		   
			   
		
				 
				try {

					let ach;
					jQuery('.loader').show();
					card = await initializeCard(payments, current_form_id);
					jQuery('.loader').hide();
					 jQuery('.wpep-single-form-' + current_form_id).click(async function (event) {
						event.preventDefault();
						var current_form_id = jQuery( this ).parents( 'form' ).data( 'id' );
						paymentButtonClicked(event, card, current_form_id, currency);
					});
				
					jQuery('.wpep-wizard-form-' + current_form_id).click(async function (event) {
						event.preventDefault();
						var current_form_id = jQuery( this ).parents( 'form' ).data( 'id' );
						paymentButtonClicked(event, card, current_form_id, currency);
					});
					if ( wpep_local_vars.achDebit == 'on') {
						ach = await initializeACH(payments);
						const achButton = document.getElementById('ach-button-'+current_form_id);
						achButton.addEventListener('click', async function (event) {
						event.preventDefault();
						
						card = '';

						const paymentForm = document.getElementById('theForm-' + current_form_id);
						const achOptions = getACHOptions(paymentForm, current_form_id);
						ach.addEventListener(
							`ontokenization`, function (event) {
								const { tokenResult, error } = event.detail;
								if (error) {
									// add code here to handle errors
								}
								else if (tokenResult.status === `OK`) {
									handlePaymentMethodSubmission(event, ach, current_form_id, currency, tokenResult.token, 'ach', achOptions);
								}
							}
						);
						var res = paymentButtonClicked(event, card, current_form_id, currency);
						if(res || wpep_local_vars.wpep_show_wizard == 'on'){
							await handlePaymentMethodSubmission(event, ach, current_form_id, currency, false, 'ach', achOptions);
							// ACH with the `accountHolderName` as an option.
						}
						});
					}
					

				} catch (e) {

					console.error('Initializing Card failed', e);
					return;
					
				}	
			
			}
		);
	}

	function validateRecaptcha(current_form_id) {

		if (wpep_local_vars.enable_recaptcha === 'on') {

			recaptcha = (wpep_local_vars.recaptcha_version === 'v2')
				? grecaptcha.getResponse()
				: jQuery('#g-recaptcha-response').val();

			if (!recaptcha || recaptcha.length === 0) {
				jQuery("#theForm-" + current_form_id + " .paymentsBlocks-" + current_form_id)
					.prepend('<div class="wpep-alert wpep-alert-danger wpep-alert-dismissable"><a href="#" data-dismiss="alert" class="wpep-alert-close">Ã—</a>Please verify that you are not a robot.</div>');
				jQuery('.wpepLoader').remove();
				return false;
			}
			return recaptcha;
			
		}
		return true;

	}
	
jQuery('#terminialpay-button').on('click', function(e){
	e.preventDefault();
	card = '';
	var current_form_id = jQuery( this ).parents( 'form' ).data( 'id' );
	var first_name       = jQuery( "#theForm-" + current_form_id + " input[name='wpep-first-name-field']" ).val();
	var last_name        = jQuery( "#theForm-" + current_form_id + " input[name='wpep-last-name-field']" ).val();
	var email            = jQuery( "#theForm-" + current_form_id + " input[name='wpep-email-field']" ).val();
	
	currency =  jQuery( '.wpep_payment_form' ).data( 'currency' );
	var res = paymentButtonClicked(event, card, current_form_id, currency);

	if (! validateRecaptcha(current_form_id)) {
		return;
	}

	if(res){
		var amount           = jQuery( '#amount_display_' + current_form_id ).text();
		amount               = amount.trim();
		amount 				 = amount.replace(wpep_local_vars.wpep_currency_symbol, "");
		var adata = {
				action: 'wpep_pay_pos_terminal',
				id: current_form_id,
				first_name: first_name,
				last_name: last_name,
				last_name: email,
				amount: amount,
			}
		jQuery.ajax({
			url: wpep_local_vars.ajax_url,
			type: "POST",
			dataType: "json",
			data: adata,
			 success: function (response) { 
			jQuery('#terminialpay-button').prop('disabled', true);
			jQuery('#terminialpay-button').prop('disabled', true).css('cursor', 'not-allowed');
			jQuery('.loader').show();
				var  aj_status = 0;
				var refreshId = setInterval(function() {
				aj_status = aj_status+1;
				var formData = {
					action: 'wpep_check_pay_terminal',
					checkout_id: response.checkout.id,
					t_status: response.checkout.status,
					id: current_form_id,
				}
				jQuery.ajax({
					url: wpep_local_vars.ajax_url,
					'type' : 'GET',
					'data' : formData,
					'success' : function(response) {
							var response_json = JSON.parse(response);
							
							if(response_json.result_info.checkout.status == 'COMPLETED'){
								clearInterval(refreshId);
								
								jQuery(`#theForm-${current_form_id}`).append(`<input type="hidden" id="wpep-checkout-id-${current_form_id}" name="wpep-checkout-id-${current_form_id}" value="${response_json.result_info.checkout.id}" />`);
						
								 var token = 'cnon:customer-card-id-ok';
								createPayment(token, current_form_id, currency, false, false, recaptcha);
								
								// completepym(refreshId);
							}else{
								console.error('Card id error: ', response_json.result_info.checkout.status);
								this.disabled = false;	
								aj_status = 0;
							}
						},
					})
				},5000)
			}
		});
	}
	});
	  function getBillingContact(form, current_form_id) {
		return {
		  givenName:  jQuery( "#theForm-" + current_form_id + " input[name='wpep-first-name-field']" ).val(),
		  familyName: jQuery( "#theForm-" + current_form_id + " input[name='wpep-last-name-field']" ).val(),
		};
	  }
	 
	  function getACHOptions(form, current_form_id) {
		var amount           = jQuery( '#amount_display_' + current_form_id ).text();
		  if(wpep_local_vars.currencySymbolType == 'code'){
			  amount               = amount.trim();
			  amount               = amount.split(' ');
			  amount               = amount[0];
			  amount 				 = amount.replace(wpep_local_vars.wpep_square_currency_new+" ", "");
		  }else{
			  amount               = amount.trim();
			  amount               = amount.split(' ');
			  amount               = amount[0];
			  amount 				 = amount.replace(wpep_local_vars.wpep_currency_symbol, "");
		  }
		  amount = parseFloat(amount.replace(",", ""));
		  const billingContact = getBillingContact(form, current_form_id);
		  const accountHolderName = `${billingContact.givenName} ${billingContact.familyName}`;
		  return { 
			  accountHolderName,
			  intent: 'CHARGE',
			  total: {
				  amount: amount*100,
				  currencyCode: wpep_local_vars.wpep_square_currency_new
			  },
		  };
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
						if (current.data( 'redirection' ) == 'http://Yes' || current.data( 'redirection' ) == 'Yes') {
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

						jQuery( "#theForm-" + current_form_id + " .paymentsBlocks-" + current_form_id ).prepend( '<div class="wpep-alert wpep-alert-danger wpep-alert-dismissable"><a href="#" data-dismiss="alert" class="wpep-alert-close">Ã—</a>' + json_response.detail + '</div>' );
						jQuery( 'html, body' ).animate(
						{
							scrollTop: jQuery( "#theForm-" + current_form_id + " .paymentsBlocks-" + current_form_id ).offset().top
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
	async function createPayment(token, current_form_id, currency, cof, verifyToken = false, recaptcha) {
		var first_name       = jQuery( "#theForm-" + current_form_id + " input[name='wpep-first-name-field']" ).val();
		var last_name        = jQuery( "#theForm-" + current_form_id + " input[name='wpep-last-name-field']" ).val();
		var email            = jQuery( "#theForm-" + current_form_id + " input[name='wpep-email-field']" ).val();
		var currencies       = ['CAD', 'USD', 'EUR', 'JPY', 'AUD', 'GBP'];
		var currency_symbols = ['C$', 'A$', 'Â¥', 'Â£', 'â‚¬', '$'];
		var amount           = jQuery( '#amount_display_' + current_form_id ).text();
		if(jQuery.inArray(currency, currencies) !== -1){
			if(wpep_local_vars.currencySymbolType == 'code'){
				amount               = amount.trim();
				amount               = amount.split(' ');
				amount               = amount[0];
				amount 				 = amount.replace(currency+" ", "");
			}else{
				amount               = amount.trim();
				amount               = amount.split(' ');
				amount               = amount[0];
				amount 				 = amount.replace(wpep_local_vars.wpep_currency_symbol, "");
			}
		}
			
		var payment_type     = jQuery( '#wpep_payment_form_type_' + current_form_id ).val();
		var payment_source   = jQuery( '.tab-content-'+ current_form_id+'.current' ).attr('id');
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
		var product_price = [];
		var product_qty = [];
		var product_cost = [];
		var product_id = [];
		if(jQuery( '.product_label' ).length > 0){
			jQuery( '.product_label' ).each(
				function(key, value) {
					var item_display = jQuery( this ).closest( '.wpItem' ).css( 'display' ) == 'none' ? 'no' : 'yes';
					if ('yes' == item_display) {
						product_label.push( jQuery( value ).text() );
					}
				}
			);
			jQuery( '.price' ).each(
				function(key, value) {

					var item_display = jQuery( this ).closest( '.wpItem' ).css( 'display' ) == 'none' ? 'no' : 'yes';

					if ('yes' == item_display) {
						product_price.push( jQuery( value ).val() );
					}
				}
			);
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
			jQuery( '.cost' ).each(
				function(key, value) {
					var item_display = jQuery( this ).closest( '.wpItem' ).css( 'display' ) == 'none' ? 'no' : 'yes';
					if ('yes' == item_display) {
						product_cost.push( jQuery( value ).val() );
					}
				}
			);
			jQuery( '.wpep_square_product_id' ).each(
				function(key, value) {
					var item_display = jQuery( this ).closest( '.wpItem' ).css( 'display' ) == 'none' ? 'no' : 'yes';
					if ('yes' == item_display) {
						product_id.push( jQuery( value ).val() );
					}
				}
			);
		}else if(jQuery( '.subscriptionPlan' ).length > 0){
			if(jQuery( '.radio_amount' ).length > 0){
				product_label.push( jQuery( 'input[name="radio-name"]:checked' ).attr("data-label") );
				product_price.push( jQuery( 'input[name="radio-name"]:checked' ).val() );
				var p_price = jQuery( 'input[name="radio-name"]:checked' ).val() * jQuery( '#wpep_quantity_'+current_form_id ).val();
				product_cost.push( p_price );
				if ( undefined != jQuery( '#wpep_quantity_'+current_form_id ).val() ) {
					product_qty.push( jQuery( '#wpep_quantity_'+current_form_id ).val() );
				} else {
					product_qty.push( 1 );
				}
			}
		}else if(jQuery( '.paynowDrop' ).length > 0){
			if(jQuery( '.custom-option' ).length > 0){
				jQuery( '.custom-option' ).each(
					function(key, value) {
						if(jQuery(value).hasClass('selection')){
							product_label.push( jQuery(value).text() );
							var p_price = jQuery(value).attr("data-value") * jQuery( '#wpep_quantity_'+current_form_id ).val();
							product_price.push( jQuery(value).attr("data-value") );
							product_cost.push( p_price );
							if ( undefined != jQuery( '#wpep_quantity_'+current_form_id ).val() ) {
								product_qty.push( jQuery( '#wpep_quantity_'+current_form_id ).val() );
							} else {
								product_qty.push( 1 );
							}
						}
					}
				);
			}
		}else if(jQuery( '.paymentSelect' ).length > 0){
			var cc;
			if(wpep_local_vars.currencySymbolType == 'code'){
				cc = wpep_local_vars.wpep_square_currency_new;
			}else{
				cc = wpep_local_vars.wpep_currency_symbol;
			}
			if(jQuery( '.selection' ).length > 0){
				product_label.push( 'Custom Amount' );
				var p_price = jQuery("label[for='" + jQuery("input[name='doller']:checked").attr("id") + "']").text().trim()
				var pp_price = p_price.replace('$', '');
				var p_cost = parseFloat(pp_price) * parseFloat(jQuery( '#wpep_quantity_'+current_form_id ).val());

				var otherAmountCost = jQuery( 'input#gross_total-'+current_form_id ).val().replace(/,/g, "");
				
				if(pp_price == 'Other' || pp_price == ''){
					product_price.push( otherAmountCost );
					product_cost.push( p_cost );
				} else{
					product_price.push( pp_price );
					product_cost.push( p_cost );
				}
				
				if ( undefined != jQuery( '#wpep_quantity_'+current_form_id ).val() ) {
					product_qty.push( jQuery( '#wpep_quantity_'+current_form_id ).val() );
				} else {
					product_qty.push( 1 );
				}
			}
		}
		var products_data = {};
		jQuery.each(
			product_label ,
			function(key, value) {

				var tmp_product_data      = {};
				tmp_product_data.label    = product_label[key];
				tmp_product_data.quantity = product_qty[key];
				tmp_product_data.price    = product_price[key];
				tmp_product_data.cost     = product_cost[key];
				tmp_product_data.id       = product_id[key];

				products_data[key] = tmp_product_data;

			}
		);

		products_data = JSON.stringify( products_data );

		if (jQuery( '#theForm-' + current_form_id ).find( '.wpep-form-radio-btn' ).length > 0) {
			var radioName = jQuery( '#theForm-' + current_form_id + ' .wpep-form-radio-btn' ).attr( 'name' );
			if (radioName != undefined) {
				jQuery( 'form[data-id="' + current_form_id + '"] .wpep-form-radio-btn' ).each(
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
				label: 'Line Items',
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
		var amount_display = jQuery( '#amount_display_' + current_form_id ).text(); 
		var amount_display_neat = amount_display.replace(/\s/g, ''); 
		form_values.push(
			{
				label: 'total_amount',
				value: amount_display_neat
			}
		);

		var quantity_id = '#wpep_quantity_' + current_form_id;
		if ( undefined != jQuery( quantity_id ).val() ) {
			form_values.push(
				{
					label: 'quantity',
					value: jQuery( quantity_id ).val()
				}	
			);
		}
		// check if discount applied or not
		if ( jQuery(`#theForm-${current_form_id} input[name="wpep-discount"]`).length > 0 ) { 
			var discount = jQuery(`#theForm-${current_form_id} input[name="wpep-discount"]`).val();
		} else {
			var discount = 0;
		}

		wpepValidateFormSubmitButton();

		var data = {
			'action': 'wpep_payment_request',
			'nonce': token,
			'first_name': first_name,
			'last_name': last_name,
			'email': email,
			'discount': discount,
			'amount': amount,
			'save_card': jQuery( '#saveCardLater' ).is( ':checked' ),
			'save_customer_id': jQuery( '#wpep_square_customer_id' ).val(),
			'payment_type': payment_type,
			'payment_source': payment_source,
			'payment_tcheckoutid': jQuery('#wpep-checkout-id-'+current_form_id).val(),
			'current_form_id': current_form_id,
			'form_values': form_values,
			'currency': currency,
			'card_on_file': cof,
			'buyer_verification': verifyToken,
			'wp_payment_nonce': wpep_local_vars.wp_payment_nonce,
			'wp_payment_recapcha': recaptcha
		};

		if(wpep_local_vars.wpep_square_amount_type == 'tabular_layout_for_square'){
			var square_product_validation = {
				'action': 'wpep_square_product_validation_request',
				'form_values': form_values,
				'form_id': current_form_id,
				'wp_payment_nonce': wpep_local_vars.wp_payment_nonce,
			}
			jQuery.ajax({
				url: wpep_local_vars.ajax_url,
				type: 'post',
				data: square_product_validation,
				success: function (response) {
					var square_pro_validation = JSON.parse(response);
					if(square_pro_validation.status == 'success'){
						if (undefined !== jQuery('#wpep_file_upload_field')[0]) {
							var files = jQuery('#wpep_file_upload_field')[0].files[0];

							if (undefined !== files) {
								var fd = new FormData();
								fd.append('file', files);
								fd.append('file_upload', 'true');
								fd.append('action', 'wpep_file_upload');

								jQuery.ajax({
									url: wpep_local_vars.ajax_url,
									type: 'post',
									data: fd,
									contentType: false,
									processData: false,
									success: function (response) {
										var parsed_response = JSON.parse(response);
										if (parsed_response.hasOwnProperty('error')) {
											jQuery( "#theForm-" + current_form_id + " .paymentsBlocks-" + current_form_id ).prepend( '<div class="wpep-alert wpep-alert-danger wpep-alert-dismissable"><a href="#" data-dismiss="alert" class="wpep-alert-close">Ã—</a>' + parsed_response.error + '</div>' );
											jQuery( '.wpepLoader' ).remove();
											
										} else {
											 wpep_file_upload_url(parsed_response.uploaded_file_url);
											saveFileUploadUrlToSession(parsed_response.uploaded_file_url, data, current_form_id);
										}
									}
								});
							} else {
								send_payment_request(data, current_form_id);
							}
						} else {
							send_payment_request(data, current_form_id);
						}
					} else {
						jQuery.each(square_pro_validation.result, function(productId, details) {
							if(details.status == 'failed'){
								jQuery('#stock_'+productId).html('');
								if(details.stock == 0){
									jQuery('#stock_'+productId).html('Out of stock');
								} else {
									jQuery('#stock_'+productId).html('Available stock count ('+ details.stock +')');
								}
								jQuery('#stock_'+productId).show();
							} else {
								jQuery('#stock_'+productId).html('');
								jQuery('#stock_'+productId).hide();
							}	
						})
						jQuery( 'html, body' ).animate(
						{
							scrollTop: jQuery( "#theForm-" + current_form_id + " .shopping-cart" ).offset().top
						},
						800,
						function () {
								window.location.hash = '#';
						}
						);
						jQuery( '.wpepLoader' ).remove();
					}
				}
			});
		} else {
			if (undefined !== jQuery('#wpep_file_upload_field')[0]) {
				var files = jQuery('#wpep_file_upload_field')[0].files[0];

				if (undefined !== files) {
					var fd = new FormData();
					fd.append('file', files);
					fd.append('file_upload', 'true');
					fd.append('action', 'wpep_file_upload');

					jQuery.ajax({
						url: wpep_local_vars.ajax_url,
						type: 'post',
						data: fd,
						contentType: false,
						processData: false,
						success: function (response) {
							var parsed_response = JSON.parse(response);
							if (parsed_response.hasOwnProperty('error')) {
								jQuery( "#theForm-" + current_form_id + " .paymentsBlocks-" + current_form_id ).prepend( '<div class="wpep-alert wpep-alert-danger wpep-alert-dismissable"><a href="#" data-dismiss="alert" class="wpep-alert-close">Ã—</a>' + parsed_response.error + '</div>' );
								jQuery( '.wpepLoader' ).remove();
								
							} else {
								 wpep_file_upload_url(parsed_response.uploaded_file_url);
								saveFileUploadUrlToSession(parsed_response.uploaded_file_url, data, current_form_id);
							}
						}
					});
				} else {
					send_payment_request(data, current_form_id);
				}
			} else {
				send_payment_request(data, current_form_id);
			}
		}
	
		function saveFileUploadUrlToSession(fileUrl, data, formId) {
			jQuery.ajax({
				url: wpep_local_vars.ajax_url,
				type: 'post',
				data: {
					action: 'wpep_save_file_upload_url',
					file_url: fileUrl,
					data: data,
					form_id: formId
				},
				success: function (response) {
					send_payment_request(data, formId);
				}
			});
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
	// The â€˜errorâ€™ thrown from this async function denotes a failed tokenization,
	// which is due to buyer error (such as an expired card). It is up to the
	// developer to handle the error and provide the buyer the chance to fix
	// their mistakes.
	async function tokenize(paymentMethod, current_form_id, method, options = false) {

		var amount = jQuery('input[name="wpep-selected-amount"]').val();

	    if ( isNaN(amount) ){
			amount 		     = amount.replace(wpep_local_vars.wpep_currency_symbol, "");
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

		var amountText = jQuery('small.display').text();
		var amountValue = amountText.match(/[\d,.]+/)[0]; 

		const verificationDetails = {
			billingContact: {
			  familyName: last_name,
			  givenName: first_name,
			  email: email,
			},
			intent: intent,
			customerInitiated: true,
    		sellerKeyedIn: false,
		};
		if (intent === 'CHARGE') {
			verificationDetails.amount = amountValue.replace(/,/g, "")
			verificationDetails.currencyCode = wpep_local_vars.wpep_square_currency_new;
		}
		
		let tokenResult;
		if (method === 'ach') {

			tokenResult = await paymentMethod.tokenize(options);

		} else {
			tokenResult = await paymentMethod.tokenize(verificationDetails);
		}

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
	
		jQuery( "#theForm-" + current_form_id ).append( jQuery( '<div />' ).attr( 'class', 'wpepLoader' ).html( '<div class="initial-load-animation"><div class="payment-image icomoonLib"><span class="icon-pay"></span></div><div class="loading-bar"><div class="blue-bar"></div></div></div>' ) );
		if ( method === null && method !== 'cashapp') {
			event.preventDefault();
		}

		if (! validateRecaptcha(current_form_id)) {
			return;
		}

		try {
			
			var amount = jQuery('input[name="wpep-selected-amount"]').val();	
			
			if ( isNaN(amount) ){
				amount 		     = amount.replace(wpep_local_vars.wpep_currency_symbols, "");
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
				if ( method === 'ach' ) {
					tokenize(paymentMethod, current_form_id, method, achOptions).then(token => {
						createPayment(token, current_form_id, currency, false, false, recaptcha);
					});
				} else {
					tokenize(paymentMethod, current_form_id, method).then(token => {
						verifyBuyer(payments, token, amount, currency, intent, first_name, last_name, email).then(verifyToken => {
							createPayment(token, current_form_id, currency, false, verifyToken, recaptcha);
						});
					});
				}
				
				
			} else {
				verifyBuyer(payments, token, amount, currency, intent, first_name, last_name, email).then(verifyToken => {
					createPayment(token, current_form_id, currency, false, verifyToken, recaptcha);
				});
				
			}
		
	
		} catch (e) {
			console.log(e)
		}
		if(jQuery('#afterpay-'+current_form_id).is(':visible')){
			if (afterpay) {
				const afterpayButton = document.getElementById('afterpay-button');
				afterpayButton.addEventListener('click', async function (event) {
				  await handlePaymentMethodSubmission(event, afterpay, current_form_id, currency, false);
				});
			}
		}
	}
	
	async function handlePaymentMethodSubmissiongftcpay(event, paymentMethod, current_form_id, currency) {
	
		jQuery( "#theForm-" + current_form_id ).append( jQuery( '<div />' ).attr( 'class', 'wpepLoader' ).html( '<div class="initial-load-animation"><div class="payment-image icomoonLib"><span class="icon-pay"></span></div><div class="loading-bar"><div class="blue-bar"></div></div></div>' ) );
		
		if (! validateRecaptcha(current_form_id)) {
			return;
		}

		try {
			
			var amount = jQuery('input[name="wpep-selected-amount"]').val();	
			
			if ( isNaN(amount) ){
				amount 		     = amount.replace("$", "");
			}
			tokenize(paymentMethod).then(token => {
				createPayment(token, current_form_id, currency, false, false, recaptcha);
			});
		
		} catch (e) {
			console.log(e)
		}
	}

	async function verifyBuyer(payments, token, amount, currency, intent, first_name, last_name, email) {
		
		if ( isNaN(amount) ){
			amount 		     = amount.replace("$", "");
		}

		var currencySymbolVerifyBuyer = wpep_local_vars.wpep_square_currency_new;

		const verificationDetails = {
			amount: amount.toString(),
			billingContact: {
			  familyName: last_name,
			  givenName: first_name,
			  email: email,
			},
			currencyCode: currencySymbolVerifyBuyer,
			intent: intent
		};
		
		const verificationResults = await payments.verifyBuyer(
		  token,
		  verificationDetails
		);
		
		return verificationResults.token;

	}

	function validateEmail(email) {
		let re = /^[^\s@]+@[^\s@]+\.(com|net|org|edu|gov|mil|co)$/i;
		return re.test( String( email ).toLowerCase() );
	}


	function paymentButtonClicked(event, card, current_form_id, currency) {
		
		jQuery('#wpep-selected-amount-'+current_form_id).val(jQuery('#amount_display_'+current_form_id).html());
		jQuery('#one_unit_cost').val(jQuery('#amount_display_'+current_form_id).html().trim().replace(wpep_local_vars.wpep_currency_symbol, ""));
		var ewallet = false;
		var first_name       = jQuery( "#theForm-" + current_form_id + " input[name='wpep-first-name-field']" ).val();
		var last_name        = jQuery( "#theForm-" + current_form_id + " input[name='wpep-last-name-field']" ).val();
		var email            = jQuery( "#theForm-" + current_form_id + " input[name='wpep-email-field']" ).val();
		 
		if (first_name != '' && last_name != '' && email != ''){
			jQuery( '.wpep-alert' ).remove();
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

				if (current.find('input[type="number"]').length > 0) {
					current.find('input[type="number"]').each(function() {
						var numberInput = jQuery(this);
						var inputValue = numberInput.val();
						var minVal = parseInt(numberInput.attr('min'), 10);
						var maxVal = parseInt(numberInput.attr('max'), 10);
						numberInput.next('.wpepError').remove();
						jQuery(wpepError).addClass('num_fields_errors'+numberInput.attr('name'));
						if (isNaN(inputValue) || inputValue === undefined || inputValue === '') {
							if (current.find('input[type="number"] ~ .wpepError').length === 0) {
								jQuery(wpepError).insertAfter(numberInput);
							}
							result2 = false;
						} else if (inputValue.length < minVal) {
							if (current.find('input[type="number"] ~ .wpepError').length === 0) {
								jQuery(wpepError).insertAfter(numberInput);
							}
							jQuery('.num_fields_errors'+numberInput.attr('name')).text('Minimum value required. Min: ' + minVal);
							result2 = false;
						} else if (inputValue.length > maxVal) {
							if (current.find('input[type="number"] ~ .wpepError').length === 0) {
								jQuery(wpepError).insertAfter(numberInput);
							}
							jQuery('.num_fields_errors'+numberInput.attr('name')).text('Maximum value exceeded. Max: ' + maxVal);
							result2 = false;
						} else {
							wpepError = '';
							current.find('.wpepError').remove();
							result2 = true;
						}
					})

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
					let wpepEmailField = current.find( 'input[type="email"]' );
					if (wpepEmailField.val() == '' || wpepEmailField.val() == undefined) {
						if (current.find( 'input[type="email"] ~ .wpepError' ).length == 0) {
							jQuery( wpepError ).insertAfter( wpepEmailField );
						}
						emailCheck = false;
					}
					else if(validateEmail( wpepEmailField.val() ) == false){
						if (wpepEmailField.siblings('.wpepError').length === 0) {
							jQuery( wpepError ).insertAfter(wpepEmailField);
						}
						jQuery(wpepEmailField.siblings('.wpepError')).text('Please enter a valid email address.');
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

				if (jQuery('#theForm-' + current_form_id).find('input[type="file"]').length) {

					let fileInput = jQuery('#theForm-' + current_form_id).find('input[type="file"]');
					let fileVal = fileInput.val();
				
					if (fileVal !== '') {
						let ext = fileVal.split('.').pop().toLowerCase();
						let allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc'];
						
						if (!allowedExts.includes(ext)) {
							if (fileInput.siblings('.wpepError').length === 0) {
								jQuery( wpepError ).insertAfter(fileInput);
							}
							jQuery(fileInput.siblings('.wpepError')).text('Invalid file type. Allowed: jpg, jpeg, png, gif, pdf, doc.');
							result2 = false;
						} else {
							fileInput.next('.wpepError').remove();
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
			}  else {
			
				result3 = true;
			} 
		}

		var selected_payment_tab = current.parents( 'form' ).find( 'ul.wpep_tabs li.tab-link.current' ).data( 'tab' );
		var finalCheck = jQuery( "#theForm-" + current_form_id + " .wizard-fieldset.show .fieldMainWrapper div.wpep-required" ).find( "span.wpepError" ).length;
		if(finalCheck != 0){
			jQuery( '.wpepLoader' ).remove();
		}

		if (jQuery( 'input[name="card_on_file"]' ).is( ':checked' )) {
			
			var card_on_file = jQuery( 'input[name="card_on_file"]' ).val();

			if(finalCheck <= 0 && termCond == true){

				if (! validateRecaptcha(current_form_id)) {
					return;
				}

				jQuery( "#theForm-" + current_form_id ).append( jQuery( '<div />' ).attr( 'class', 'wpepLoader' ).html( '<div class="initial-load-animation"><div class="payment-image icomoonLib"><span class="icon-pay"></span></div><div class="loading-bar"><div class="blue-bar"></div></div></div>' ) );
				jQuery('.wpep-single-form-' + current_form_id).attr('disabled', 'disabled');
				jQuery('.wpep-single-form-' + current_form_id).css('cursor', 'not-allowed');
				jQuery('.wpep-wizard-form-' + current_form_id).css('cursor', 'not-allowed');
				jQuery('.wpep-wizard-form-' + current_form_id).attr('disabled', 'disabled');
				createPayment(false, current_form_id, currency, card_on_file, false, recaptcha);

			}
			
		}else{
			// if payment type is credit card
			creditcard = 'creditCard-' + current_form_id;
			
			
			
			if (
				selected_payment_tab == creditcard 
				&& result1 == true 
				&& result3 == true 
				&& wpepError == '' 
				&& termCond == true 
				&& finalCheck == 0
				) {
				 handlePaymentMethodSubmission(event, card, current_form_id, currency);
			} else if (result1 == true && result2 == true && result3 == true && emailCheck == true && wpepError == '' && termCond == true && finalCheck == 0) { // if payment type is google pay
				return true;
			}
			 
		}
		
		
					
	}



	jQuery('.wizard-section').css('visibility', 'visible');
    jQuery('.parent-loader').remove();

	// This code is only for the payment tabular layout.
	// On the first page load, if a quantity is already set from the backend, 
	// the user cannot apply a coupon because the gross_total input is not updated. 
	// To fix this, we manually set the gross_total input on the initial load.
	if(wpep_local_vars.wpep_square_amount_type == 'payment_tabular'){

            jQuery("input.qty").each(function () {
                let val = parseInt(jQuery(this).val(), 10);
                if (val > 0) {
					let wpepSelectedAmountQuantity = jQuery("input[name='wpep-selected-amount']").val();
                    jQuery('input[name="gross_total"]').val(wpepSelectedAmountQuantity).trigger('change');
                }
            });

	}

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

		if (typeof applePay.destroy === "function") { 
			displayApplePay( payments, form_id, currency ,applePay); 
		}
		if (typeof googlePay.destroy === "function") { 
			googlePay.destroy();
			displayGooglePay( payments, form_id, currency ,googlePay); 
		}
		if (typeof afterpay.destroy === "undefined") { 
			if ( wpep_local_vars.afterpay == 'on') {
				displayAfterPay( payments, form_id, currency ); 
			}
		}

		if (typeof cashAppPay.destroy === "undefined") { 
			if ( wpep_local_vars.cashapp == 'on') {
				displayCashApp( payments, form_id, currency );
			}
		}		

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
				jQuery(`#wpep-coupons-${form_id}`).children().show();
			}

			if ( jQuery(`#theForm-${form_id} .wpep-alert-coupon`).length > 0 ) {
				jQuery(`#theForm-${form_id} .wpep-alert-coupon`).remove();
			}
			jQuery( 'form[data-id="' + form_id + '"] .display' ).text( jQuery( this ).text() );
			var selected_payment_tab = jQuery( `ul.wpep_tabs-${form_id} li.tab-link.current` ).data( 'tab' );
			if(selected_payment_tab == 'giftcard-'+form_id){
				jQuery(`#amount_display_${form_id}`).hide();
				if(jQuery('#giftcard_text_'+form_id).length == 0){
					jQuery('.wpep-single-form-'+form_id+' span' ).append(`<small id="giftcard_text_${form_id}" class="giftcard_text" style="font-size: 100%;">with Gift Card</small>`);
				}
			} else {
				jQuery('#giftcard_text_'+form_id).remove();
				jQuery(`#amount_display_${form_id}`).show();
			}
			jQuery( 'form[data-id="' + form_id + '"] .display' ).next().next( 'input[name="one_unit_cost"]' ).val( jQuery( this ).text() ).trigger('change');
			jQuery( 'form[data-id="' + form_id + '"] .display' ).next( 'input[name="wpep-selected-amount"]' ).val( jQuery( this ).text() ).trigger('change');
			jQuery( '#wpep_quantity_' + form_id ).val( 1 );
			jQuery(`#wpep_coupon_applied_${form_id}`).hide();

			jQuery( 'form[data-id="' + form_id + '"] .showPayment' ).removeClass( 'shcusIn' );
			jQuery( 'form[data-id="' + form_id + '"] .customPayment' ).text( jQuery( this ).val() );
			var selected_payment_tab = jQuery( `ul.wpep_tabs-${form_id} li.tab-link.current` ).data( 'tab' );
			
		}
	);
	
	jQuery('.otherpayment').on('click', function (e) {
		var form_id      = jQuery( this ).parents( 'form' ).data( 'id' );
		var min = parseFloat(jQuery('.customPayment').attr('min'));
		setTimeout(function() {
			jQuery('input[name="wpep-selected-amount"]').val(min).trigger('change');	
			jQuery('.customPayment.otherPayment').val(min);
		}, 1500);
	});

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
							jQuery('.wpep-single-form-'+form_id+' span' ).append(`<small id="giftcard_text_${form_id}" class="giftcard_text" style="font-size: 100%;">with Gift Card</small>`);
						}
					} else {
						jQuery('#giftcard_text_'+form_id).remove();
						jQuery(`#amount_display_${form_id}`).show();
					}
					jQuery( 'form[data-id="' + form_id + '"] .display' ).next( 'input[name="wpep-selected-amount"]' ).val( jQuery('.otherPayment ').val() ).trigger('change');
					jQuery( 'span.valueCheckWpep' ).text('');

				} else {

					currency = prepare_display_amount(currencyType, currency);
					
					jQuery( this ).val( '' );
					jQuery( 'form[data-id="' + form_id + '"] .display' ).text( '' );
					var selected_payment_tab = jQuery( `ul.wpep_tabs-${form_id} li.tab-link.current` ).data( 'tab' );
					if(selected_payment_tab == 'giftcard-'+form_id){
						jQuery(`#amount_display_${form_id}`).hide();
						if(jQuery('#giftcard_text_'+form_id).length == 0){
							jQuery('.wpep-single-form-'+form_id+' span' ).append(`<small id="giftcard_text_${form_id}" class="giftcard_text" style="font-size: 100%;">with Gift Card</small>`);
						}
					} else {
						jQuery('#giftcard_text_'+form_id).remove();
						jQuery(`#amount_display_${form_id}`).show();
					}
					jQuery( 'form[data-id="' + form_id + '"] .display' ).next( 'input[name="wpep-selected-amount"]' ).val( '' ).trigger('change');
					jQuery( 'form[data-id="' + form_id + '"] .wpep-single-form-' + current_form_id).addClass( 'wpep-disabled' );
					jQuery( 'form[data-id="' + form_id + '"] .wpep-wizard-form-' + current_form_id).addClass( 'wpep-disabled' );
					var valueValidationWpEp = 'Please insert value between ' + min + ' - ' + max;
					jQuery( 'span.valueCheckWpep' ).text(valueValidationWpEp);
				}
				jQuery(`#wpep_coupon_applied_${form_id}`).hide();
			}, 1000);
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
				currency = 'Â¥' + val;
			}

			if (currency == 'GBP') {
				currency = 'Â£' + val;
			}

		} else {

			currency = val + ' ' + currency;

		}

		return currency;

	}


	jQuery( '.wpep_delete_tabular_product' ).click(
		function() {

			var form_id  = jQuery( this ).parents( 'form' ).data( 'id' );
			
			var currency = jQuery( this ).parents( 'form' ).data( 'currency' );

			jQuery( this ).closest( '.wpItem' ).hide();
			calculate( form_id, currency );

		}
	);

	jQuery( '.wpep_delete_tabular_square_product' ).click(
		function() {

			var form_id  = jQuery( this ).parents( 'form' ).data( 'id' );
			
			var currency = jQuery( this ).parents( 'form' ).data( 'currency' );
			var square_product_id = jQuery(this).closest('.wpItem').find('.wpep_square_product_id').val();
			jQuery( this ).closest( '.wpItem' ).hide();
			jQuery( '#stock_'+square_product_id ).hide();
			calculate( form_id, currency );

		}
	);

	function wpepValidateFormSubmitButton() {
		let wpepSelectedAmountHiddenVal = jQuery('input[name="wpep-selected-amount"]')
		if (
			(wpep_local_vars.wpep_square_amount_type === 'tabular_layout_for_square' ||
			wpep_local_vars.wpep_square_amount_type === 'payment_tabular') &&
			(wpepSelectedAmountHiddenVal.val() === '' || parseFloat(wpepSelectedAmountHiddenVal.val()) === 0)
		) {
			jQuery('button.wpep-single-form-submit-btn').addClass('wpep-disabled');
			jQuery('button.wpep-wizard-form-submit-btn').addClass('wpep-disabled');
		} else if (wpepSelectedAmountHiddenVal.val() > 0) {
			jQuery('button.wpep-single-form-submit-btn').removeClass('wpep-disabled');
			jQuery('button.wpep-wizard-form-submit-btn').removeClass('wpep-disabled');
		}
	}

	wpepValidateFormSubmitButton();

	jQuery('input[name="wpep-selected-amount"]').on('change', function () {
		wpepValidateFormSubmitButton();
	});

});

jQuery(window).on('load', function () {
	if (jQuery( 'form.wpep_payment_form' ).length > 0) {
		jQuery( 'form.wpep_payment_form' ).each(
			async function () {
				var current_form_id = jQuery( this ).data( 'id' );
				var coupon_data = {
						'action': 'wpep_check_coupon',
						'current_form_id': current_form_id,
						'coupon_code': jQuery(this).siblings('input[name="wpep-coupon"]').val(),
					};
				jQuery.post(
						wpep_local_vars.ajax_url,
						coupon_data,
						function (response) {
							if(response){
								jQuery('#wpep-coupons-'+current_form_id).find('input[name="wpep-coupon"]').focus();
								jQuery('#wpep-coupons-'+current_form_id).find('input[name="wpep-coupon"]').val(response);
								jQuery('#wpep-coupons-'+current_form_id).find('input[name="wpep-cp-submit"]').click();
							}
				});
			}
		)
	}
	
	jQuery( '.wpep_coupon_remove_btn' ).on('click', function(e){
		e.preventDefault();
		var form_id = jQuery(this).parents('form.wpep_payment_form').data('id');
		var coupon_delete_data = {
			'action': 'wpep_delete_coupon',
			'current_form_id': form_id,
		}
		jQuery.post(
				wpep_local_vars.ajax_url,
				coupon_delete_data,
				function (response) {
					if(response){
						var gross_amount = jQuery('#theForm-' + form_id).find('#gross_total-'+ form_id).val();
						if ( jQuery(`#theForm-${form_id} input[name="wpep-discount"]`).length > 0 ) { 
							jQuery(`#theForm-${form_id} input[name="wpep-discount"]`).remove();
							jQuery(`#wpep-coupons-${form_id}`).children().show();
						}
						if ( jQuery(`#theForm-${form_id} .wpep-alert-coupon`).length > 0 ) {
							jQuery(`#theForm-${form_id} .wpep-alert-coupon`).remove();
						}
						jQuery(`#theForm-${form_id}`).find('input[name="wpep-selected-amount"]').val(gross_amount).trigger('change');
						
						jQuery(`#wpep_coupon_applied_${form_id}`).hide();
						jQuery('input[name="wpep-coupon"]').val('');
					}
		});
		
	});
	
	function applyCoupon(element) {
		var form_id = jQuery(element).parents('form.wpep_payment_form').data('id');
		var discount = jQuery(`#theForm-${form_id} input[name="wpep-discount"]`).length > 0 ? 'yes' : 'no';
	
		var gross_amount = jQuery(`#theForm-${form_id}`).find(`#gross_total-${form_id}`).val();
	
		var currency = wpep_local_vars.currencySymbolType === 'code' ? 
					   wpep_local_vars.wpep_square_currency_new : 
					   wpep_local_vars.wpep_currency_symbol;
	
		var data = {
			'action': 'wpep_apply_coupon',
			'current_form_id': form_id,
			'coupon_code': jQuery(element).siblings('input[name="wpep-coupon"]').val(),
			'total_amount': gross_amount,
			'extra_fee': jQuery(element).parents('form.wpep_payment_form').find('.is_extra_fee').val(),
			'discounted': discount,
			'currency': currency,
			'cp_submit': jQuery(element).val()
		};
	
		jQuery.post(
			wpep_local_vars.ajax_url,
			data,
			function (response) {
				var response = jQuery.parseJSON(response);
	
				if (response.status == 'success') {

					var amountTotalWpep = response.subtotal;
					var couponAmountWpep = response.coupons_amount;

					if(response.discount_type != 'percentage'){
						if(couponAmountWpep > amountTotalWpep){  //coupon amount greater then total amount
							if (jQuery('.couponAmountError').length === 0) {
								jQuery('.coupon-field').after('<div style="margin: -24px 0px -10px 0px;"><span class="couponAmountError">Coupon is bigger then total amount</span></div>');	
							}
							return;
						}else{
							jQuery('.couponAmountError').remove();
						}
					}

					
					var discountPrice = parseFloat(response.discount).toFixed(2); 
					var subTotalPrice = parseFloat(response.subtotal).toFixed(2); 
					var totalPrice = parseFloat(response.total).toFixed(2); 
	
					jQuery(`#wpep-coupons-${form_id}`).children().hide();
					jQuery(`#wpep_coupon_applied_${form_id}`).show();
	
					if (jQuery(`#theForm-${form_id} input[name="wpep-discount"]`).length <= 0) {
						jQuery(`#theForm-${form_id}`).append(`
							<input type="hidden" name="wpep-coupon-amount" value="${response.coupons_amount}" />
							<input type="hidden" name="wpep-coupon-type" value="${response.discount_type}" />
							<input type="hidden" name="wpep-discount" value="${discountPrice}" />
						`);
	
						jQuery(`#wpep-coupons-${form_id}`).prepend(`
							<div class="wpep-alert-coupon wpep-alert wpep-alert-success wpep-alert-dismissable">
								<a href="#" class="wpep-dismiss-coupon wpep-alert-close">Ã—</a>${response.message_success}
							</div>
						`);
	
						let amountText = (wpep_local_vars.currencySymbolType == 'code') ? 
							`${totalPrice} ${response.currency}` : 
							`${response.currency} ${totalPrice}`;
	
						jQuery(`#theForm-${form_id}`).find(`small#amount_display_${form_id}`).text(amountText);
						jQuery(`#theForm-${form_id}`).find('input[name="wpep-selected-amount"]').val(`${subTotalPrice} ${response.currency}`).trigger('change');
					} else {
						jQuery(`#wpep-coupons-${form_id}`).prepend(`
							<div class="wpep-alert-coupon wpep-alert wpep-alert-danger wpep-alert-dismissable quantityCouponAlert">
								<a href="#" class="wpep-dismiss-coupon wpep-alert-close">Ã—</a>Coupon already applied!
							</div>
						`);
					}
				}
	
				if (response.status == 'failed') {
					jQuery(`#wpep-coupons-${form_id}`).prepend(`
						<div class="wpep-alert-coupon wpep-alert wpep-alert-danger wpep-alert-dismissable quantityCouponAlert">
							<a href="#" class="wpep-dismiss-coupon wpep-alert-close">Ã—</a>${response.message_failed}
						</div>
					`);
				}
			}
		);
	}
	
	jQuery('.cp-apply').on('click', function (e) {
		e.preventDefault();
		applyCoupon(this);
	});
	jQuery(document).on('keypress', '.otherPayment', function(event) {
		if (event.which === 13) {  // 13 is the keycode for Enter
			event.preventDefault(); // Prevent form submission
		}
	});
	// payment custom layout of pricing in form settings. 
	// This is for coupon apply when each tab click.
	jQuery('.selection input[type="radio"]').on('change', function (e) {
		if (jQuery(this).hasClass('otherpayment')) {
			return;
		}
		setTimeout(function () {
			jQuery('.cp-apply').trigger("click");
		}, 500);
	});

	// payment radio listing of pricing in form settings. 
	// This is for coupon apply when each tab click..
	jQuery('.selectedPlan').on('change', function (e) {
		e.preventDefault();
		setTimeout(function() { 
			jQuery( '.cp-apply' ).trigger("click");
		}, 500);
	});

	// payment radio listing of pricing in form settings. 
	// This is for coupon apply when each tab click..
	jQuery('.custom-option').on('click', function (e) {
		e.preventDefault();
		setTimeout(function() { 
			jQuery( '.cp-apply' ).trigger("click");
		}, 500);
	});

	// This is for coupon remove on beforeunload function.
	jQuery(window).bind('beforeunload',function(){
		jQuery( '.wpep_coupon_remove_btn' ).trigger("click");
   	});

if (jQuery('.ext_number').is(':visible')) {
	jQuery('.ext_number').on('input', function() {
		var inputElement = jQuery(this); // save the reference to the element
		setTimeout(function() {
		  var enteredValue = parseInt(inputElement.val());
		  var minValue = parseInt(inputElement.attr('min'));
		  var maxValue = parseInt(inputElement.attr('max'));
		  if (enteredValue < minValue || enteredValue > maxValue) {
			inputElement.val('');
		  }
		}, 1000);
	  });
	}

})
jQuery( document ).on( 'click', '.wpep-dismiss-coupon', function(e) {
	e.preventDefault();
	jQuery( '.wpep-alert-coupon' ).remove();
});
let current_form_id = jQuery( 'form.wpep_payment_form' ).data( 'id' );

let debounceTimer;

jQuery(document).on('input', 'input[name="wpep_quantity"]', function() {
    clearTimeout(debounceTimer); // Clear the previous timer if it's still running

    // Get the current value of the input fieldd
	jQuery(this).val(jQuery(this).val().replace(/[^0-9]/g, ''));
    var value = parseInt(this.value);
	// Ensure the value is at least 1
	if (isNaN(value) || value < 1) {
        value = 1;
        jQuery(this).val(1); // Update the input field to reflect the minimum value
    }
    debounceTimer = setTimeout(function() {
        // Get the current form ID
        var current_form_id = jQuery('form.wpep_payment_form').data('id');

        // Call the update function with the value
        wpep_update_amount_with_quantity(current_form_id, value);
    }, 1000); // 1-second delay
});

function wpep_update_amount_with_quantity(current_form_id, value) {

	var amount_field_id           = "amount_display_" + current_form_id;
	var amount_with_currency      = document.getElementById( amount_field_id ).innerHTML.trim();
	var amount_and_currency_split = amount_with_currency.split( " " );
	var currency                  = amount_and_currency_split[1];
	var one_unit_cost             = jQuery( '#one_unit_cost' ).val();
	var one_unit_cost                 = one_unit_cost.split( " " )[0];
	var one_unit_cost = one_unit_cost.replace("$", ""); // remove dollar sign
	var new_amount                = one_unit_cost * value;
	document.getElementById( amount_field_id ).innerHTML = new_amount + ' ' + currency;
	jQuery( 'form[data-id="' + current_form_id + '"] .display' ).next( 'input[name="wpep-selected-amount"]' ).val( new_amount + ' ' + currency ).trigger('change');
	var discount = 'no';
	if ( jQuery(`#theForm-${current_form_id} input[name="wpep-discount"]`).length > 0 ) { 
		discount = 'yes';	
	}
	var data = {
		'action': 'wpep_apply_coupon',
		'current_form_id': current_form_id,
		'coupon_code': jQuery('.cp-apply').siblings('input[name="wpep-coupon"]').val(),
		'total_amount': new_amount,
		'extra_fee': jQuery(this).parents('form.wpep_payment_form').find('.is_extra_fee').val(),
		'discounted': discount,
		'currency': currency,
		'cp_submit': jQuery('.cp-apply').val()
	};

	jQuery.post(
		wpep_local_vars.ajax_url,
		data,
		function (response) {
			var response = jQuery.parseJSON(response); // create an object with the key of the array

			if ( response.status == 'success') {

				var discountPrice = parseFloat(response.discount).toFixed(2); 
				var totalPrice = parseFloat(response.total).toFixed(2); 

				if ( jQuery(`#theForm-${current_form_id} input[name="wpep-discount"]`).length <= 0 ) {

					if ( jQuery(`#theForm-${current_form_id} .wpep-alert-coupon`).length > 0 ) {
						jQuery(`#theForm-${current_form_id} .wpep-alert-coupon`).remove();
					}
					
					jQuery(`#theForm-${current_form_id}`).append(`<input type="hidden" name="wpep-discount" value="${discountPrice}" />`);
					jQuery(`#wpep-coupons-${current_form_id}`).prepend(`<div class="wpep-alert-coupon wpep-alert wpep-alert-success wpep-alert-dismissable"><a href="#" class="wpep-dismiss-coupon wpep-alert-close">Ã—</a>${response.message_success}</div>`);
					jQuery(`#theForm-${current_form_id}`).find('input[name="wpep-selected-amount"]').val(`${totalPrice} ${response.currency}`).trigger('change');
					jQuery(`#theForm-${current_form_id}`).find(`small#amount_display_${current_form_id}`).text(`${totalPrice} ${response.currency}`);
				
				} else {
					jQuery(`#wpep-coupons-${current_form_id}`).prepend(`<div class="wpep-alert-coupon wpep-alert wpep-alert-danger wpep-alert-dismissable quantityCouponAlert"><a href="#" class="wpep-dismiss-coupon wpep-alert-close">Ã—</a>Coupon already applied!</div>`);
				}
				
			}

			if (response.status == 'failed') {

				if ( jQuery(`#theForm-${current_form_id} .wpep-alert-coupon`).length > 0 ) {
					jQuery(`#theForm-${current_form_id} .wpep-alert-coupon`).remove();
				}

				jQuery(`#wpep-coupons-${current_form_id}`).prepend(`<div class="wpep-alert-coupon wpep-alert wpep-alert-danger wpep-alert-dismissable quantityCouponAlert"><a href="#" class="wpep-dismiss-coupon wpep-alert-close">Ã—</a>${response.message_failed}</div>`);
				
			}

		}
	)

}

var quantity_waiting = false;
jQuery('.wpep_increaseValue').on('click', function(){
	var current_form_id = jQuery( 'form.wpep_payment_form' ).data( 'id' );
	if ( jQuery(`#theForm-${current_form_id} input[name="wpep-discount"]`).length > 0 ) { 
		jQuery(`#theForm-${current_form_id} input[name="wpep-discount"]`).remove();
		jQuery(`#wpep-coupons-${current_form_id}`).children().show();
	}

	if ( jQuery(`#theForm-${current_form_id} .wpep-alert-coupon`).length > 0 ) {
		jQuery(`#theForm-${current_form_id} .wpep-alert-coupon`).remove();
	}

	var quantity_id = 'wpep_quantity_' + current_form_id;
	
	var value       = parseInt( document.getElementById( quantity_id ).value, 10 );
	value = isNaN( value ) ? 0 : value;
	value++;
	document.getElementById( quantity_id ).value = value;
	if (!quantity_waiting) {
        quantity_waiting = true;
        setTimeout(function() {
            var value = document.getElementById( quantity_id ).value;
            wpep_update_amount_with_quantity( current_form_id, value );
            quantity_waiting = false;  // Reset the flag to allow next operation
        }, 1000); // 1 second delayy
    }
	jQuery(`#wpep_coupon_applied_${current_form_id}`).hide();

	if (jQuery('input[name="wpep-coupon"]').length > 0 && jQuery('input[name="wpep-coupon"]').val().trim() !== '') {
		jQuery( '.cp-apply' ).trigger("click");
	}
	
})

jQuery('.wpep_decreaseValue').on('click', function(){

	if ( jQuery(`#theForm-${current_form_id} input[name="wpep-discount"]`).length > 0 ) { 
		jQuery(`#theForm-${current_form_id} input[name="wpep-discount"]`).remove();
		jQuery(`#wpep-coupons-${current_form_id}`).children().show();
	}

	if ( jQuery(`#theForm-${current_form_id} .wpep-alert-coupon`).length > 0 ) {
		jQuery(`#theForm-${current_form_id} .wpep-alert-coupon`).remove();
	}
	
	var quantity_id = 'wpep_quantity_' + current_form_id;
	var value       = parseInt( document.getElementById( quantity_id ).value, 10 );

	if (1 !== value) {
		value             = isNaN( value ) ? 0 : value;
		value < 1 ? value = 1 : '';
		value--;
		document.getElementById( quantity_id ).value = value;
		setTimeout(function() {
			var value       = parseInt( document.getElementById( quantity_id ).value, 10 );
			wpep_update_amount_with_quantity( current_form_id, value );
		}, 1000);
	}
	jQuery(`#wpep_coupon_applied_${current_form_id}`).hide();
});

function calculate(form_id, currency) {

	if ( jQuery(`#theForm-${form_id} input[name="wpep-discount"]`).length > 0 ) { 
		jQuery(`#theForm-${form_id} input[name="wpep-discount"]`).remove();
		jQuery(`#wpep-coupons-${form_id}`).children().show();
	}
	if ( jQuery(`#theForm-${form_id} .wpep-alert-coupon`).length > 0 ) {
		jQuery(`#theForm-${form_id} .wpep-alert-coupon`).remove();
	}

	var currency_codes = ['CAD', 'GBP', 'AUD', 'JPY', 'EUR', 'USD'];
	var currency_symbol = ['C$', 'Â£', 'A$', 'Â¥', 'â‚¬', '$'];

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
		
		if(wpep_local_vars.currencySymbolType == 'symbol'){
			jQuery(`#amount_display_${form_id}`).text( currency + ' ' +total );
			jQuery('input[name="wpep-selected-amount"]' ).val( total ).trigger('change');
		} else if(wpep_local_vars.currencySymbolType == 'code'){
			jQuery(`#amount_display_${form_id}`).text( total + ' ' + currency );
			jQuery('input[name="wpep-selected-amount"]' ).val( total ).trigger('change');
		} else {
			jQuery(`#amount_display_${form_id}`).text( total );
		}
		
		var selected_payment_tab = jQuery( `ul.wpep_tabs-${form_id} li.tab-link.current` ).data( 'tab' );
		if(selected_payment_tab == 'giftcard-'+form_id){
			jQuery(`#amount_display_${form_id}`).hide();
			if(jQuery('#giftcard_text_'+form_id).length == 0){
				jQuery('.wpep-single-form-'+form_id+' span' ).append(`<small id="giftcard_text_${form_id}" class="giftcard_text" style="font-size: 100%;">with Gift Card</small>`);
			}
		} else {
			jQuery('#giftcard_text_'+form_id).remove();
			jQuery(`#amount_display_${form_id}`).show();
		}
		var layout = jQuery('#wpep_amount_layout').val();
		if (layout !== 'tabular_layout') {
			jQuery( 'form[data-id="' + form_id + '"] .display' ).next( 'input[name="wpep-selected-amount"]' ).val( currency ).trigger('change');
		}
		if (layout == 'tabular_layout_for_square') {
			jQuery( 'form[data-id="' + form_id + '"] .display' ).next( 'input[name="wpep-selected-amount"]' ).val( total ).trigger('change');
		}
		jQuery(`#wpep_coupon_applied_${form_id}`).hide();
	}
}

const filterNum = (str) => {
	const numericalChar = new Set([ ".",",","0","1","2","3","4","5","6","7","8","9" ]);
	str = str.split("").filter(char => numericalChar.has(char)).join("");
	return str;
}

jQuery(window).on('load', function() {
		
	jQuery('.wpep_payment_form').each(function() {
		var current_form_id = jQuery(this).attr('data-id');
		var currency        = jQuery(this).data( 'currency' );
		
		jQuery('ul.wpep_tabs-' + current_form_id + ' li').click(function () {
				var tab_id = jQuery( this ).attr( 'data-tab' );
				
				if ('cashapp-'+current_form_id === tab_id || 'achdebit-'+current_form_id === tab_id  || 'googlePay-'+current_form_id === tab_id ||
					'afterpay-'+current_form_id === tab_id ||  'applePayfather-'+current_form_id === tab_id ){
						jQuery('.wpep-single-form-' + current_form_id).attr('disabled', 'disabled');
						jQuery('.wpep-single-form-' + current_form_id).css('cursor', 'not-allowed');
						jQuery('.wpep-wizard-form-' + current_form_id).attr('disabled', 'disabled');
						jQuery('.wpep-wizard-form-' + current_form_id).css('cursor', 'not-allowed');
						jQuery('.cp-apply').prop('disabled', false);
						jQuery('.cp-apply').css('cursor', 'default');
				}else{
					jQuery('.wpep-single-form-' + current_form_id).prop('disabled', false);
					jQuery('.wpep-single-form-' + current_form_id).css('cursor', 'pointer');
					jQuery('.wpep-wizard-form-' + current_form_id).prop('disabled', false);
					jQuery('.wpep-wizard-form-' + current_form_id).css('cursor', 'pointer');
				}
				jQuery( 'ul.wpep_tabs-' + current_form_id + ' li' ).removeClass( 'current' );
				jQuery( '.tab-content-' + current_form_id ).removeClass( 'current' );
				jQuery( this ).find('.tab-content-'+current_form_id).addClass( 'current' );
				jQuery( this ).addClass( 'current' );
				jQuery( "#" + tab_id ).addClass( 'current' );
				
				if(tab_id != 'applePayfather-'+current_form_id){
				    jQuery('#applePayfather-'+current_form_id).css('display', 'none');
				} else {
				     jQuery('#applePayfather-' + current_form_id).css('display', 'block');
				}
				if(tab_id == 'giftcard-'+current_form_id){
					displayGiftcard( payments, current_form_id, currency );
					jQuery(`#amount_display_${current_form_id}`).hide();
					if(jQuery('#giftcard_text_'+current_form_id).length == 0){
						jQuery('.wpep-single-form-'+current_form_id+' span' ).append(`<small id="giftcard_text_${current_form_id}" class="giftcard_text" style="font-size: 100%;">with Gift Card</small>`);
					}
				} else {
					jQuery('#giftcard_text_'+current_form_id).remove();
					jQuery(`#amount_display_${current_form_id}`).show();
				}
			}
		);
 });
	 

    jQuery( 'input[name="wpep-selected-amount"]' ).on('change paste keyup', function() {	

		var form_id = jQuery(this).parents('form.wpep_payment_form').data('id');
		var discount = 0.00;
		var signup_total = 0.00;
		var amount = parseFloat( filterNum( jQuery(this).val() ) );
		if ( jQuery(`#theForm-${form_id}`).find('input[name="wpep-signup-amount"]').length > 0 ) {
			var signup_total = parseFloat(jQuery(`#theForm-${form_id}`).find('input[name="wpep-signup-amount"]').val());
		}
		var total_amount = amount + signup_total;
		if (isNaN(total_amount)) {
			if(!localStorage.getItem("alertDisplayed")) {
				
				localStorage.setItem("alertDisplayed", "true");
			}
			var total_amount = 0;
		}
		var unit_cost = 0;
		if ( jQuery( '#one_unit_cost' ).length > 0 ) {
			var one_unit_cost = jQuery( '#one_unit_cost' ).val().trim();
		    unit_cost = one_unit_cost.trim();
			unit_cost = unit_cost.split(' ');
			unit_cost = unit_cost[0];
			unit_cost = parseFloat( unit_cost.replace(wpep_local_vars.wpep_currency_symbol, "") ).toFixed(2);
		}
		if (isNaN(unit_cost)) {
		    var unit_cost = parseFloat( 0 ).toFixed(2);
		}
		if(wpep_local_vars.wpep_square_amount_type == 'payment_tabular'){
		    var unit_cost = parseFloat( amount ).toFixed(2);
		}
		jQuery(`#amount_display_${form_id}`).siblings('input[name="wpep-selected-amount"]').val(parseFloat( total_amount ).toFixed(2));
		if(wpep_local_vars.currencySymbolType == 'symbol'){
			var currency = wpep_local_vars.wpep_currency_symbol;
			jQuery(`#amount_display_${form_id}`).text(currency + parseFloat( total_amount ).toFixed(2));
    		jQuery(`#theForm-${form_id}`).find('.wpep-fee-subtotal .fee_value').text( currency + unit_cost );
    		jQuery(`#theForm-${form_id}`).find('.wpep-fee-total .fee_value').text( currency + parseFloat( total_amount ).toFixed(2) );
		} else if(wpep_local_vars.currencySymbolType == 'code'){
			var currency = wpep_local_vars.wpep_square_currency_new;
			jQuery(`#amount_display_${form_id}`).text(parseFloat( total_amount ).toFixed(2) + ' ' + currency);
    		jQuery(`#theForm-${form_id}`).find('.wpep-fee-subtotal .fee_value').text( unit_cost + ' ' + currency );
    		jQuery(`#theForm-${form_id}`).find('.wpep-fee-total .fee_value').text( parseFloat( total_amount ).toFixed(2) + ' ' + currency );
		}else{
			var currency = '';
			jQuery(`#amount_display_${form_id}`).text(parseFloat( total_amount ).toFixed(2));
    		jQuery(`#theForm-${form_id}`).find('.wpep-fee-subtotal .fee_value').text( unit_cost  );
    		jQuery(`#theForm-${form_id}`).find('.wpep-fee-total .fee_value').text( parseFloat( total_amount ).toFixed(2) );
		}
		var selected_payment_tab = jQuery( `ul.wpep_tabs-${form_id} li.tab-link.current` ).data( 'tab' );
		if(selected_payment_tab == 'giftcard-'+form_id){
			jQuery(`#amount_display_${form_id}`).hide();
			if(jQuery('#giftcard_text_'+form_id).length == 0){
				jQuery('.wpep-single-form-'+form_id+' span' ).append(`<small id="giftcard_text_${form_id}" class="giftcard_text" style="font-size: 100%;">with Gift Card</small>`);
			}
		} else {
			jQuery('#giftcard_text_'+form_id).remove();
			jQuery(`#amount_display_${form_id}`).show();
		}
		if ( jQuery(`#theForm-${form_id}`).find('input[name="gross_total"]').length > 0 ) {
			jQuery(`#theForm-${form_id}`).find('input[name="gross_total"]').val(amount);
		}
		if ( jQuery(`#theForm-${form_id}`).find('input[name="wpep-discount"]').length > 0 ) {
			discount = parseFloat(jQuery(`#theForm-${form_id}`).find('input[name="wpep-discount"]').val());
		}

		var extra_fee = jQuery(this).parents('form.wpep_payment_form').find('.is_extra_fee').val();
		if ( extra_fee == 1 || discount > 0) {
			wpep_calculate_fee_data_ajax(form_id, amount, discount);
		}
		
	});
})

let is_wpep_calculate_fee_data_ajax_InProgress = false;
function wpep_calculate_fee_data_ajax(form_id, amount, discount){
	if (is_wpep_calculate_fee_data_ajax_InProgress) {
		return; // Prevent duplicate requests
	}
	var data = {
		'action': 'wpep_calculate_fee_data',
		'dataType': 'html',
		'current_form_id': form_id,
		'total_amount': amount,
		'coupon_amount': jQuery('input[name="wpep-coupon-amount"]').val(),
		'coupon_type': jQuery('input[name="wpep-coupon-type"]').val(),
		'discount': discount,
		'currency': wpep_local_vars.wpep_square_currency_new,
	};
	is_wpep_calculate_fee_data_ajax_InProgress = true;
	jQuery.post(
		wpep_local_vars.ajax_url,
		data,
		function (response) {
			var extra_fee = jQuery('.is_extra_fee').val();
            if ( extra_fee == 1 || jQuery('input[name="wpep-signup-amount"]').length > 0 ) {
    			jQuery(`#wpep-payment-details-${form_id}`).html(response);
    			var get_total = jQuery(`#wpep-payment-details-${form_id}`).find('.wpep-fee-total span.fee_value').text();
    			get_total = get_total.trim();
    			get_total = get_total.split(' ');
    			get_total = get_total[0];
    			get_total = get_total.replace(wpep_local_vars.wpep_currency_symbol, "");
            } else {
				var rrr = JSON.parse(response);
				var get_total = rrr.subtotal;
			}
			if(wpep_local_vars.currencySymbolType == 'symbol'){
				jQuery(`#amount_display_${form_id}`).text(wpep_local_vars.wpep_currency_symbol + get_total);
			} else if(wpep_local_vars.currencySymbolType == 'code'){
				jQuery(`#amount_display_${form_id}`).text(get_total + ' ' + wpep_local_vars.wpep_square_currency_new);
			}else{
				jQuery(`#amount_display_${form_id}`).text(get_total);
			}
			var selected_payment_tab = jQuery( `ul.wpep_tabs-${form_id} li.tab-link.current` ).data( 'tab' );
			if(selected_payment_tab == 'giftcard-'+form_id){
				jQuery(`#amount_display_${form_id}`).hide();
				if(jQuery('#giftcard_text_'+form_id).length == 0){
					jQuery('.wpep-single-form-'+form_id+' span' ).append(`<small id="giftcard_text_${form_id}" class="giftcard_text" style="font-size: 100%;">with Gift Card</small>`);
				}
			} else {
				jQuery('#giftcard_text_'+form_id).remove();
				jQuery(`#amount_display_${form_id}`).show();
			}
			jQuery(`#amount_display_${form_id}`).siblings('input[name="wpep-selected-amount"]').val(get_total);	

			let current_form_id = form_id;
			let currency   = jQuery('#wpep_form_currency').val();

			if (typeof googlePay.destroy === "function") {}

			if (typeof afterpay.destroy === "function") {}

			if (typeof cashAppPay.destroy === "function") {}
			
			is_wpep_calculate_fee_data_ajax_InProgress = false;
			// console.clear();
		}
	);
}

function wpep_delete_cof(customer_id, card_on_file, current_form_id, delete_id) {

	var data = {
		'action': 'wpep_delete_cof',
		'customer_id': customer_id,
		'card_on_file': card_on_file,
		'current_form_id': current_form_id
	};

	jQuery.post(
		wpep_local_vars.ajax_url,
		data,
		function (response) {
			if ('success' == response) {
				jQuery( '#' + delete_id ).closest( "li" ).remove();
			}
		}
	).done(function () {});

}

function afterPaybuildPaymentRequest(payments, current_form_id, currency) {

	if ( jQuery('input[name="wpep-selected-amount"]').val().trim() != '' && wpep_local_vars.wpep_square_amount_type != 'payment_tabular' ) {
		var gross_amount = jQuery('#theForm-' + current_form_id).find('#gross_total-'+ current_form_id).val();
		// Don't trigger change event to prevent re-initialization loops
		jQuery(`#theForm-${current_form_id}`).find('input[name="wpep-selected-amount"]').val(gross_amount);
		amount = jQuery('input[name="wpep-selected-amount"]').val();
	} else {
		amount = jQuery('#amount_display_' + current_form_id).text();
	}
	
	coupon_apply = jQuery('#wpep-coupons-' + current_form_id).length;
	if(coupon_apply > 0 ){
		 jQuery('.cp-apply').click(function() {
			setTimeout(function() {
				if ( jQuery('input[name="wpep-selected-amount"]').val().trim() != '') {
					amount = jQuery('input[name="wpep-selected-amount"]').val();
				} else {
					amount = jQuery('#amount_display_' + current_form_id).text();
				}
			}, 1000);
		})
	}
	amount = String(amount).replace(/\s+/g, '').trim();

	if(wpep_local_vars.currencySymbolType == 'code'){
		amount 	= amount.replace(wpep_local_vars.wpep_square_currency_new, "");
	}else{
		amount 	= amount.replace(wpep_local_vars.wpep_currency_symbol, "");
	}
	var min          = parseFloat( jQuery( '.otherPayment' ).attr( 'min' ) );
	if(amount == 0){
		amount = min;
	}

	const req = payments.paymentRequest({
	  countryCode: 'US',
	  currencyCode: currency,
	  total: {
		amount: amount.replace(/[^0-9\.]/gi, '').toString("#0000\\.0000"),
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
			  amount: amount.replace(/[^0-9\.]/gi, '').toString("#0000\\.0000"),
			  label: 'total',
			},
		  },
		],
	  };
	});

	return req;
}
function cashAppbuildPaymentRequest(payments, current_form_id, currency) {
	var amount = jQuery('#amount_display_' + current_form_id).text();
	
	// Get amount from selected input if available
	if ( jQuery('input[name="wpep-selected-amount"]').val().trim() != '' ) {
		amount = jQuery('input[name="wpep-selected-amount"]').val();
	}
	
	// Clean amount - remove all non-numeric characters except decimal point
	amount = String(amount).trim();
	
	// Remove currency symbols and codes
	if(wpep_local_vars.currencySymbolType == 'code'){
		amount = amount.replace(wpep_local_vars.wpep_square_currency_new, "");
	} else {
		amount = amount.replace(wpep_local_vars.wpep_currency_symbol, "");
	}
	
	// Remove any remaining non-numeric characters except decimal point
	amount = amount.replace(/[^0-9.]/g, '');
	
	// Parse and validate
	amount = parseFloat(amount);
	var min = parseFloat( jQuery( '.otherPayment' ).attr( 'min' ) ) || 0;
	
	if (isNaN(amount) || amount <= 0) {
		amount = min;
	}
	
	// Ensure amount is at least minimum
	if (amount < min) {
		amount = min;
	}
	
	// Convert to string with proper formatting (remove trailing zeros if whole number)
	var amountString = amount % 1 === 0 ? amount.toString() : amount.toFixed(2);
  
	jQuery('#cashapp-amount').hide();
	
	try {
		var paymentRequest = payments.paymentRequest({
		  countryCode: 'US',
		  currencyCode: currency,
		  total: {
			amount: amountString,
			label: 'Total',
		  },
		});
		return paymentRequest;
	} catch (error) {
		console.error('Error creating Cash App payment request:', error);
		return null;
	}
}


function gpaybuildPaymentRequest(payments, current_form_id, currency, googlePay) {


var req = {};
	amount = jQuery('#amount_display_' + current_form_id).text();
	coupon_apply = jQuery('#wpep-coupons-' + current_form_id).length;
	if(coupon_apply > 0 ){
		 jQuery('.cp-apply').click(function() {
			setTimeout(function() {
				if ( jQuery('input[name="wpep-selected-amount"]').val().trim() != '') {
					amount = jQuery('input[name="wpep-selected-amount"]').val();
				} else {
					amount = jQuery('#amount_display_' + current_form_id).text();
				}
			}, 1000);
		})
	}
	amount = amount.trim();
	amount = amount.split(' ')[0]; 
	amount = amount.replace('$', '');  

	 req[current_form_id] = payments.paymentRequest({
	  countryCode: 'US',
	  currencyCode: currency,
	  total: {
		amount: amount.replace(/[^0-9\.]/gi, '').toString("#0000\\.0000"),
		label: 'Total',
	  },
	  requestShippingContact: true,
	});
	req[current_form_id].addEventListener('afterpay_shippingaddresschanged', function (_address) {
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
			  amount: amount.replace(/[^0-9\.]/gi, '').toString("#0000\\.0000"),
			  label: 'total',
			},
		  },
		],
	  };
	});
	
	return req[current_form_id];
  }

  async function initializeAfterpay(payments, current_form_id, currency) {
	// Prevent multiple simultaneous initializations
	if (afterpayInitializing) {
		return afterpay;
	}
	
	afterpayInitializing = true;
	
	try {
		// Destroy existing instance if it exists and is not already destroyed
		if (afterpay && typeof afterpay.destroy === 'function') {
			try {
				afterpay.destroy();
			} catch (e) {
				// Instance may already be destroyed, ignore error
			}
			afterpay = null;
		}
		
		var max          = parseFloat( jQuery( '.otherPayment' ).attr( 'max' ) );
		var min          = parseFloat( jQuery( '.otherPayment' ).attr( 'min' ) );
		var val          = jQuery(  '.otherPayment'  ).val();
		if('code' == wpep_local_vars.currencySymbolType) {
			currency = wpep_local_vars.wpep_square_currency_new;
		}
		var amount = jQuery( '#amount_display_' + current_form_id ).text();
		amount = String(amount).replace(/\s+/g, '').trim();

		if(wpep_local_vars.currencySymbolType == 'code'){
			amount 	= amount.replace(wpep_local_vars.wpep_square_currency_new, "");
		}else{
			amount 	= amount.replace(wpep_local_vars.wpep_currency_symbol, "");
		}
		if (val == '') {
			jQuery( '.otherPayment' ).val(min);
			val = min;
		}
		if (!isNaN(val) && val >= min && val <= max) {
			const paymentRequest = afterPaybuildPaymentRequest(payments, current_form_id, currency);
			afterpay = await payments.afterpayClearpay(paymentRequest);
			await afterpay.attach('#afterpay-button-'+current_form_id);
			return afterpay;
		}
		else if (!isNaN(amount) && wpep_local_vars.wpep_square_user_defined_amount != 'on') {
			const paymentRequest = afterPaybuildPaymentRequest(payments, current_form_id, currency);
			afterpay = await payments.afterpayClearpay(paymentRequest);
			// Attach immediately instead of waiting 2 seconds
			await afterpay.attach('#afterpay-button-'+current_form_id);
			return afterpay;
		} 
		else if (!isNaN(amount) 
			&& 
			wpep_local_vars.wpep_square_amount_type == 'payment_radio' 
			|| wpep_local_vars.wpep_square_amount_type == 'payment_drop' 
			|| wpep_local_vars.wpep_square_amount_type == 'payment_custom' 
			|| wpep_local_vars.wpep_square_amount_type == 'payment_tabular'
			|| wpep_local_vars.wpep_square_amount_type == 'tabular_layout_for_square'
		  ) {
			const paymentRequest = afterPaybuildPaymentRequest(payments, current_form_id, currency);
			afterpay = await payments.afterpayClearpay(paymentRequest);
			await afterpay.attach('#afterpay-button-'+current_form_id);
			return afterpay;
		}
		else{
			jQuery('#afterpay-amount').show(); 
		}
	} finally {
		afterpayInitializing = false;
	}
}
async function applepaybuildPaymentRequest( payments, current_form_id, currency, applePay ) {

var req = {};
	amount = jQuery('#amount_display_' + current_form_id).text();
	coupon_apply = jQuery('#wpep-coupons-' + current_form_id).length;
	if(coupon_apply > 0 ){
		 jQuery('.cp-apply').click(function() {
			setTimeout(function() {
				if ( jQuery('input[name="wpep-selected-amount"]').val().trim() != '') {
					amount = jQuery('input[name="wpep-selected-amount"]').val();
				} else {
					amount = jQuery('#amount_display_' + current_form_id).text();
				}
			}, 1000);
		})
	}
	amount = amount.trim();
	amount = amount.split(' ')[0]; 
	amount = amount.replace('$', '');

	 req[current_form_id] = payments.paymentRequest({
	  countryCode: 'US',
	  currencyCode: currency,
	  total: {
		amount: amount.replace(/[^0-9\.]/gi, '').toString("#0000\\.0000"),
		label: 'Total',
	  },
	  requestShippingContact: true,
	});
	

	return req[current_form_id];
  }
 async function initializeApplepay(payments, current_form_id, currency,applePay) {

    if(applePay == undefined){
		var applePay = {};
	}
	
	var max          = parseFloat( jQuery( '.otherPayment' ).attr( 'max' ) );
	var min          = parseFloat( jQuery( '.otherPayment' ).attr( 'min' ) );
	var val          = jQuery(  '.otherPayment'  ).val();

	var amount           = jQuery( '#amount_display_' + current_form_id ).text();
	amount               = amount.trim();
	amount               = amount.split(' ');
	amount               = amount[0];
	amount 				 = amount.replace(wpep_local_vars.wpep_currency_symbol, "");
	if (val == '') {
		jQuery( '.otherPayment' ).val(min);
		val = min;
	}
	

	 if ((val != '' && val >= min && val <= max) || (!isNaN(amount) && amount >= min && amount <= max) && wpep_local_vars.wpep_square_user_defined_amount == 'on') {
		
		const paymentRequest = await applepaybuildPaymentRequest(payments, current_form_id, currency, applePay);
		
		applePay = await payments.applePay(paymentRequest);
		
		jQuery('#applepay-amount').hide();
		
		return applePay;
	 }else if (!isNaN(amount) && wpep_local_vars.wpep_square_user_defined_amount != 'on') {
		
		const paymentRequest = await applepaybuildPaymentRequest(payments, current_form_id, currency, applePay);
	
		applePay = await payments.applePay(paymentRequest);

		jQuery('#applepay-amount').hide();
	
		return applePay;
	}else if (!isNaN(amount) && wpep_local_vars.wpep_square_amount_type == 'payment_radio' || wpep_local_vars.wpep_square_amount_type == 'payment_drop' || wpep_local_vars.wpep_square_amount_type == 'payment_tabular' || wpep_local_vars.wpep_square_amount_type == 'tabular_layout_for_square' ) {
		
		const paymentRequest = await applepaybuildPaymentRequest(payments, current_form_id, currency, applePay);
	
	    applePay = await payments.applePay(paymentRequest);
		
		jQuery('#applepay-amount').hide();
	
		return applePay;
	}else{
		jQuery('#applepay-amount').show();
		
	} 
  }

  async function initializeGooglepay(payments, current_form_id, currency,googlePay) {


	if(googlePay == undefined){
		var googlePay = {};
	}
	


	if (jQuery( '#google-pay-button-'+current_form_id).html().length > 1) {
		googlePay.destroy();
	}
	if (typeof googlePay.destroy === "function") { 
		googlePay.destroy(); 
	}
	var max          = parseFloat( jQuery( '.otherPayment' ).attr( 'max' ) );
	var min          = parseFloat( jQuery( '.otherPayment' ).attr( 'min' ) );
	var val          = jQuery(  '.otherPayment'  ).val();

	var amount           = jQuery( '#amount_display_' + current_form_id ).text();
	amount               = amount.trim();
	amount               = amount.split(' ');
	amount               = amount[0];
	amount 				 = amount.replace(wpep_local_vars.wpep_currency_symbol, "");

	if (val == '') {
		jQuery( '.otherPayment' ).val(min);
		val = min;
	}
	 if ((val != '' && val >= min && val <= max) || (!isNaN(amount) && amount >= min && amount <= max) && wpep_local_vars.wpep_square_user_defined_amount == 'on') {
		const paymentRequest = gpaybuildPaymentRequest(payments, current_form_id, currency, googlePay);
	
		googlePay[current_form_id] = await payments.googlePay(paymentRequest);
		
		
		await googlePay[current_form_id].attach('#google-pay-button-'+current_form_id);
		jQuery('#gpay-amount').hide();
		

		return googlePay[current_form_id];
	 }else if (!isNaN(amount) && wpep_local_vars.wpep_square_user_defined_amount != 'on') {
		const paymentRequest = gpaybuildPaymentRequest(payments, current_form_id, currency, googlePay);
		googlePay = await payments.googlePay(paymentRequest);
		await googlePay.attach('#google-pay-button-'+current_form_id);
		jQuery('#gpay-amount').hide();
		return googlePay;
	}else if (!isNaN(amount) && wpep_local_vars.wpep_square_amount_type == 'payment_radio' || wpep_local_vars.wpep_square_amount_type == 'payment_drop' || wpep_local_vars.wpep_square_amount_type == 'payment_tabular'  || wpep_local_vars.wpep_square_amount_type == 'tabular_layout_for_square' ) {
		const paymentRequest = gpaybuildPaymentRequest(payments, current_form_id, currency, googlePay);
		googlePay = await payments.googlePay(paymentRequest);
		await googlePay.attach('#google-pay-button-'+current_form_id);
		jQuery('#gpay-amount').hide();
		return googlePay;
	}else{
		jQuery('#gpay-amount').show();
	} 
  }

 async function initializeCashApp(payments, current_form_id, currency) {
	// Prevent multiple simultaneous initializations
	if (cashAppPayInitializing) {
		return cashAppPay;
	}
	
	cashAppPayInitializing = true;
	
	try {
		// Check if target element exists
		const targetElement = document.getElementById('cash-app-pay-'+current_form_id);
		if (!targetElement) {
			console.warn('Cash App Pay target element not found: #cash-app-pay-'+current_form_id);
			return null;
		}
		
		// Destroy existing instance if it exists and is not already destroyed
		if (cashAppPay && typeof cashAppPay.destroy === 'function') {
			try {
				cashAppPay.destroy();
			} catch (e) {
				// Instance may already be destroyed, ignore error
			}
			cashAppPay = null;
		}
		
		var max          = parseFloat( jQuery( '.otherPayment' ).attr( 'max' ) ) || 0;
		var min          = parseFloat( jQuery( '.otherPayment' ).attr( 'min' ) ) || 0;
		var val          = jQuery(  '.otherPayment'  ).val();

		var amount           = jQuery( '#amount_display_' + current_form_id ).text();
		amount               = amount.trim();
		amount 				 = amount.replace(currency+" ", "");
		
		// Clean amount - remove all non-numeric characters except decimal point
		amount = amount.replace(/[^0-9.]/g, '');
		
		if (val == '' || val == null) {
			jQuery( '.otherPayment' ).val(min);
			val = min;
		}
		
		// Validate amount
		amount = parseFloat(amount);
		if (isNaN(amount) || amount <= 0) {
			jQuery('#cashapp-amount').show();
			return null;
		}
		
		if ((val != '' && val >= min && val <= max) || (amount >= min && amount <= max) && wpep_local_vars.wpep_square_user_defined_amount == 'on') {
			const paymentRequest = cashAppbuildPaymentRequest(payments, current_form_id, currency);
			if (!paymentRequest) {
				jQuery('#cashapp-amount').show();
				return null;
			}
			cashAppPay = await payments.cashAppPay(paymentRequest,{
			  redirectURL: 'https://my.website/checkout',
			  referenceId: 'my-website-00000001',
			});
			await cashAppPay.attach('#cash-app-pay-'+current_form_id);
			return cashAppPay;
		} else if (amount >= min && amount <= max && wpep_local_vars.wpep_square_user_defined_amount != 'on') {
			const paymentRequest = cashAppbuildPaymentRequest(payments, current_form_id, currency);
			if (!paymentRequest) {
				jQuery('#cashapp-amount').show();
				return null;
			}
			cashAppPay = await payments.cashAppPay(paymentRequest,{
			  redirectURL: 'https://my.website/checkout',
			  referenceId: 'my-website-00000001',
			});
			await cashAppPay.attach('#cash-app-pay-'+current_form_id);
			return cashAppPay;
		} else if (
			wpep_local_vars.wpep_square_amount_type == 'payment_radio' 
			|| wpep_local_vars.wpep_square_amount_type == 'payment_custom' 
			|| wpep_local_vars.wpep_square_amount_type == 'payment_drop' 
			|| wpep_local_vars.wpep_square_amount_type == 'payment_tabular'
			|| wpep_local_vars.wpep_square_amount_type == 'tabular_layout_for_square'  
			) {
			const paymentRequest = cashAppbuildPaymentRequest(payments, current_form_id, currency);
			if (!paymentRequest) {
				jQuery('#cashapp-amount').show();
				return null;
			}
			cashAppPay = await payments.cashAppPay(paymentRequest,{
			  redirectURL: 'https://my.website/checkout',
			  referenceId: 'my-website-00000001',
			});
			await cashAppPay.attach('#cash-app-pay-'+current_form_id);
			return cashAppPay;
		}else{
			jQuery('#cashapp-amount').show();
			return null;
		}
	} catch (error) {
		console.error('Error initializing Cash App Pay:', error);
		jQuery('#cashapp-amount').show();
		return null;
	} finally {
		cashAppPayInitializing = false;
	}
  }

  function cleanUpDuplicateGiftCards() {
    jQuery('#sq-gift-card-coupen .sq-card-wrapper').slice(1).remove();
  }

 async function initializeGiftcard(payments, current_form_id, currency) {
	
	var max          = parseFloat( jQuery( '.otherPayment' ).attr( 'max' ) );
	var min          = parseFloat( jQuery( '.otherPayment' ).attr( 'min' ) );
	var val          = jQuery(  '.otherPayment'  ).val();

	var amount           = jQuery( '#amount_display_' + current_form_id ).text();
	amount               = amount.trim();
	amount 				 = amount.replace(currency+" ", "");
	if (val == '') {
		jQuery( '.otherPayment' ).val(min);
		val = min;
	}
	if ((val != '' && val >= min && val <= max) || (!isNaN(amount) && amount >= min && amount <= max) && wpep_local_vars.wpep_square_user_defined_amount == 'on') {
		const giftCard = await payments.giftCard();
		await giftCard.attach('#sq-gift-card-coupen');
		cleanUpDuplicateGiftCards();

		return giftCard;
	} else if (!isNaN(amount) && wpep_local_vars.wpep_square_user_defined_amount != 'on') {
		const giftCard = await payments.giftCard();
		await giftCard.attach('#sq-gift-card-coupen');
		cleanUpDuplicateGiftCards();

		return giftCard;
	} else if (
		wpep_local_vars.wpep_square_amount_type == 'payment_radio' 
		|| wpep_local_vars.wpep_square_amount_type == 'payment_custom' 
		|| wpep_local_vars.wpep_square_amount_type == 'payment_drop' 
		|| wpep_local_vars.wpep_square_amount_type == 'payment_tabular'
		|| wpep_local_vars.wpep_square_amount_type == 'tabular_layout_for_square'  
		) {
		const giftCard = await payments.giftCard();
		await giftCard.attach('#sq-gift-card-coupen');
		cleanUpDuplicateGiftCards();

		return giftCard;
	}else{
		jQuery('#giftcard-amount').show();
		
	} 
  }





function initializePayments( appId, locationId ) {
	return window.Square.payments(appId, locationId);
}
function destroyGooglePay() {
  if(jQuery('#google-pay-button').is(':visible')){
	googlePay.destroy();
  }
}

async function displayApplePay(payments, current_form_id, currency,applePay, app_btn) {
	
	var amount           = jQuery( '#amount_display_' + current_form_id ).text();
	amount               = amount.trim();
	amount 				 = amount.replace(currency, "");
	if(applePay == undefined){
		var applePay = {};
	}
	
	if(currency !== amount){
	    
	  
		try {
			/* const divElement = document.getElementById("testing_apple");

			// Convert the applePay object to a string
			const applePayString = JSON.stringify(applePay, null, 2);

			// Create a text node with the string representation of the applePay object
			const textNode = document.createTextNode(applePayString);

			// Append the text node to the div element
			divElement.appendChild(textNode); */
		
			applePay = await initializeApplepay(payments, current_form_id, currency,applePay[current_form_id]);
		
		  		


	} catch (e) {
			console.error('Initializing Applepay failed', e);
		}
	} else {
		jQuery('#applePay-'+current_form_id).html('<p>Please define amount from Admin</p>')
	}
	
	return applePay;

}

async function displayGooglePay(payments, current_form_id, currency,googlePay) {
	
	var amount           = jQuery( '#amount_display_' + current_form_id ).text();
	amount               = amount.trim();
	amount 				 = amount.replace(currency, "");
	if(googlePay == undefined){
		var googlePay = {};
	}
	if(currency !== amount){
		try {
			googlePay[current_form_id] = await initializeGooglepay(payments, current_form_id, currency,googlePay[current_form_id]);
			
			

			let timeoutpayId;

			function handleDOMChanges() {
			  clearTimeout(timeoutpayId);

			  timeoutpayId = setTimeout(async function() {
				try {
				  googlePay[current_form_id] = await initializeGooglepay(payments, current_form_id, currency, googlePay[current_form_id]);
				 
				 
				} catch (e) {
				  console.error('Initializing Googlepay failed', e);
				}
			  }, 2000);
			}

			// Use Mutation Observer to detect DOM changes
			const observer = new MutationObserver(handleDOMChanges);

			// Observe changes in the amount display element
			const amountDisplayElement = document.getElementById('amount_display_' + current_form_id);
			if (amountDisplayElement) {
			  observer.observe(amountDisplayElement, { subtree: true, childList: true });
			}
		  
			



	} catch (e) {
			console.error('Initializing Googlepay failed', e);
		}
	} else {
		jQuery('#google-pay-button-'+current_form_id).html('<p>Please define amount from Admin</p>')
	}



	return googlePay[current_form_id];

}

async function displayAfterPay(payments, current_form_id, currency) {
	
	var amount           = jQuery( '#amount_display_' + current_form_id ).text();
	amount  = amount.trim();
	if(wpep_local_vars.currencySymbolType == 'code'){
		amount 	= amount.replace(wpep_local_vars.wpep_square_currency_new, "");
	}else{
		amount 	= amount.replace(wpep_local_vars.wpep_currency_symbol, "");
	}

	
	if(currency !== amount){
	
		try {
			jQuery('.loader').show();
			afterpay = await initializeAfterpay(payments, current_form_id, currency);
			jQuery('.loader').hide();
		} catch (e) {
			console.error('Initializing Afterpay/Clearpay failed', e);
		}
	}else{
			jQuery('#afterpay-amount').show();
			
	} 
	
	jQuery('.other-'+current_form_id).click( function () {
	let timeoutId;
		jQuery('.other-'+current_form_id).on('input', function() {

				jQuery('#afterpay-amount').hide();
				
			clearTimeout(timeoutId);
			timeoutId = setTimeout(async function() {
			 if (afterpay && typeof afterpay.destroy === 'function') {	
					try {
						afterpay.destroy();
					} catch (e) {
						// Instance may already be destroyed, ignore error
					}
				}
				 
				try {
					jQuery('.loader').show();
					afterpay = await initializeAfterpay(payments, current_form_id, currency);
					jQuery('.loader').hide();
				} catch (e) {
					console.error('Initializing Afterpay/Clearpay failed', e);
				}
			  
			 
			}, 500); 
		  });
	});
  
	let timeoutQtyId;
	jQuery('#wpep_quantity_'+current_form_id).on('input', function() {

		jQuery('#afterpay-amount').hide();
		
	clearTimeout(timeoutQtyId);
    timeoutQtyId = setTimeout(async function() {
     
		if (afterpay && typeof afterpay.destroy === 'function') {	
			try {
				afterpay.destroy();
			} catch (e) {
				// Instance may already be destroyed, ignore error
			}
		}
		try {
			jQuery('.loader').show();
			afterpay = await initializeAfterpay(payments, current_form_id, currency);
			jQuery('.loader').hide();
		} catch (e) {
			console.error('Initializing Afterpay/Clearpay failed', e);
		}
	  
	 
    }, 500); 
  });
// Product Table

//Drop Down & Radio clicked event
	let timeoutChangeId;
	jQuery('input[name="wpep-selected-amount"]').on('change', function() {
	
	if ( wpep_local_vars.afterpay == 'on') {
		jQuery('#afterpay-amount').hide();
		if (jQuery( '#afterpay-button-'+current_form_id).html().length > 1 && afterpay && typeof afterpay.destroy === 'function') {	
			try {
				afterpay.destroy();
			} catch (e) {
				// Instance may already be destroyed, ignore error
			}
		}
		clearTimeout(timeoutChangeId);
		timeoutChangeId = setTimeout(async function() {
		 
			 
			try {
				jQuery('.loader').show();
				afterpay = await initializeAfterpay(payments, current_form_id, currency);
				jQuery('.loader').hide();
			} catch (e) {
				console.error('Initializing Afterpay/Clearpay failed', e);
			}
		  
		 
		}, 600);
	}
  }); 
	let timeoutpayId;
	jQuery('.payamount-'+current_form_id).on('click', function(event) {
		
		jQuery('#afterpay-amount').hide();
		
		clearTimeout(timeoutpayId);
		timeoutpayId = setTimeout(async function() {
			if (afterpay && typeof afterpay.destroy === 'function') {
				try {
					afterpay.destroy();
				} catch (e) {
					// Instance may already be destroyed, ignore error
				}
			}
			try {
				afterpay = await initializeAfterpay(payments, current_form_id, currency);
				jQuery('.loader').hide();
			} catch (e) {
				console.error('Initializing Afterpay/Clearpay failed', e);
			}
		  
		 
		}, 500); 
	  });	
	return afterpay;		
}
jQuery('.other-'+current_form_id).click( function () {
	let timeoutId;
	jQuery('.other-'+current_form_id).on('input', function() {
		if ( wpep_local_vars.cashapp == 'on') {
			jQuery('#cashapp-amount').hide();
			clearTimeout(timeoutId);
			timeoutId = setTimeout(async function() {
				if(cashAppPay && typeof cashAppPay.destroy === 'function'){
					try {
						cashAppPay.destroy();
					} catch (e) {
						// Instance may already be destroyed, ignore error
					}
				} 
				try {
					var current_form_id = jQuery(  'form.wpep_payment_form'  ).data( 'id' );
					var currency        = jQuery(  'form.wpep_payment_form'  ).data( 'currency' );
					jQuery('.loader').show();
					cashAppPay = await initializeCashApp(payments,current_form_id, currency);
					jQuery('.loader').hide();	
				} catch (e) {
					console.error('Initializing Cash App Pay failed', e);
				}
			
			}, 1000); 
		}
	});
	let timeoutQtyId;
	jQuery('#wpep_quantity_'+current_form_id).on('input', function() {
		if ( wpep_local_vars.cashapp == 'on') {
			jQuery('#cashapp-amount').hide();
			if(jQuery('#cash_app_pay_v1_element').is(':visible')){
				
			}  
			clearTimeout(timeoutId);
			timeoutId = setTimeout(async function() {
				if(cashAppPay && typeof cashAppPay.destroy === 'function'){
					try {
						cashAppPay.destroy();
					} catch (e) {
						// Instance may already be destroyed, ignore error
					}
				} 
				try {
					var current_form_id = jQuery(  'form.wpep_payment_form'  ).data( 'id' );
					var currency        = jQuery(  'form.wpep_payment_form'  ).data( 'currency' );
					jQuery('.loader').show();
					cashAppPay = await initializeCashApp(payments,current_form_id, currency);
					jQuery('.loader').hide();	
				} catch (e) {
					console.error('Initializing Cash App Pay failed', e);
				}
			
			}, 1000); 
		}
	});
});
jQuery(window).on('load', function() {
		if(wpep_local_vars.recaptcha_version == 'v3' && wpep_local_vars.enable_recaptcha == 'on'){
			grecaptcha.ready(function() {
				grecaptcha.execute(wpep_local_vars.recaptcha_site_key_v3, {action:'validate_captcha'})
						  .then(function(token) {
					
					document.getElementById('g-recaptcha-response').value = token;
					jQuery('.wpep_payment_form').append('<input type="hidden" id="wpep_recaptcha" name="wpep_recaptcha" value="'+token+'">');

				});
			});
		} 
		jQuery('.payamount-'+current_form_id).click( function (event) {
		if ( wpep_local_vars.cashapp == 'on') {
			let timeoutId;
			jQuery('#cashapp-amount').hide();
			if (cashAppPay && typeof cashAppPay.destroy === 'function') {
				try {
					cashAppPay.destroy();
				} catch (e) {
					// Instance may already be destroyed, ignore error
				}
			}
			clearTimeout(timeoutId);
			timeoutId = setTimeout(async function() {
				try {
					var current_form_id = jQuery(  'form.wpep_payment_form'  ).data( 'id' );
					var currency        = jQuery(  'form.wpep_payment_form'  ).data( 'currency' );
					cashAppPay = await initializeCashApp(payments,current_form_id, currency);
					jQuery('.loader').hide();	
				} catch (e) {
					console.error('Initializing Cash App Pay failed', e);
				}
				}, 1000); 
			}
		})
	jQuery('input[name="wpep-selected-amount"]').on('change', function() {
		if ( wpep_local_vars.cashapp == 'on') {
			let timeChangeId;
			jQuery('#cashapp-amount').hide();
			if (jQuery('#cash_app_pay_v1_element').is(":visible") && jQuery('#cash_app_pay_v1_element').html().length > 1 && cashAppPay && typeof cashAppPay.destroy === 'function') {
				try {
					cashAppPay.destroy();
				} catch (e) {
					// Instance may already be destroyed, ignore error
				}
			}
			clearTimeout(timeChangeId);
			timeChangeId = setTimeout(async function() {
				try {
					var current_form_id = jQuery(  'form.wpep_payment_form'  ).data( 'id' );
					var currency        = jQuery(  'form.wpep_payment_form'  ).data( 'currency' );
					cashAppPay = await initializeCashApp(payments,current_form_id, currency);
					jQuery('.loader').hide();	
				} catch (e) {
					console.error('Initializing Cash App Pay failed', e);
				}
				}, 1000);
			}
		})
	});

async function displayCashApp(payments, current_form_id, currency) {

	var amount           = jQuery( '#amount_display_' + current_form_id ).text();
	amount               = amount.trim();
	amount 				 = amount.replace(currency+" ", "");
	
	// Clean amount for comparison
	amount = amount.replace(/[^0-9.]/g, '');
	amount = parseFloat(amount);
	
	if(!isNaN(amount) && amount > 0 && currency !== amount){
		try {
			jQuery('.loader').show();
			cashAppPay = await initializeCashApp(payments,current_form_id, currency);
			jQuery('.loader').hide();
			
			if (!cashAppPay) {
				jQuery('#cashapp-amount').show();
			}
		} catch (e) {
			console.error('Initializing Cash App Pay failed', e);
			jQuery('#cashapp-amount').show();
		}
	} else {
		jQuery('#cashapp-amount').show();
	}
	
	return cashAppPay;	

	
}

async function displayGiftcard(payments, current_form_id, currency) {
	let giftcard = null;
	var amount           = jQuery( '#amount_display_' + current_form_id ).text();
	amount               = amount.trim();
	amount 				 = amount.replace(currency+" ", "");
	var selected_payment_tab = jQuery( `ul.wpep_tabs-${current_form_id} li.tab-link.current` ).data( 'tab' );
	// if(selected_payment_tab){
		if(currency !== amount){
			try {
				jQuery('.loader').show();
				
				giftcard = await initializeGiftcard(payments,current_form_id, currency);
				
				jQuery('.loader').hide();
			} catch (e) {
				console.error('Initializing Giftcard failed', e);
			}
		} else {
			jQuery('#giftcard-amount').show();
		}
		
		
		return giftcard;
	// }
}


