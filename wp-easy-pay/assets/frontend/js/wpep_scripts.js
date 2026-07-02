jQuery( document ).ready(
	function(){


		jQuery( '.wpep-popup-btn' ).click(
			function(e){			
				e.preventDefault();
				var form_id = jQuery( this ).data( 'btn-id' );

				jQuery( '#wpep_popup-' + form_id ).fadeIn(
					function () {
						if ( typeof window.wpepScheduleTurnstileInit === 'function' ) {
							window.wpepScheduleTurnstileInit( form_id );
						}
					}
				);

			}
		);

		jQuery( '.wpep-close' ).click(
			function(e){
				e.preventDefault();

				var form_id = jQuery( this ).data( 'btn-id' );
				// console.log('#wpep_popup_'+form_id+' .popup');
				jQuery( '#wpep_popup-' + form_id ).fadeOut();
			}
		);

	}
);
