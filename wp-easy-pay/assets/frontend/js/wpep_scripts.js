jQuery( document ).ready(
	function(){


		jQuery( '.wpep-popup-btn' ).click(
			function(e){			
				e.preventDefault();
				var form_id = jQuery( this ).data( 'btn-id' );

				jQuery( '#wpep_popup-' + form_id ).fadeIn();

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
