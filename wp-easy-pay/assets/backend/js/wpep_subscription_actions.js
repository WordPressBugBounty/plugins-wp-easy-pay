jQuery(document).ready(function(){

	var $originalSearchBox = jQuery('#posts-filter .search-box');
	var originalSearchBoxHTML = $originalSearchBox.length ? $originalSearchBox.html() : null;

	function putSearchBoxInTablenav() {
		var $secondActions = jQuery('.post-type-wpep_subscriptions #posts-filter .tablenav .alignleft.actions').eq(1);
		if ($secondActions.length === 0) {
			var $topTablenav = jQuery('.post-type-wpep_subscriptions .tablenav.top');
			var $firstActions = $topTablenav.find('.alignleft.actions').first();
			if ($firstActions.length > 0) {
				$secondActions = jQuery('<div class="alignleft actions"></div>');
				$firstActions.after($secondActions);
			}
		}
		if ($secondActions.length > 0) {
			if ($secondActions.find('input#post-search-input').length === 0 && originalSearchBoxHTML) {
				$secondActions.append(originalSearchBoxHTML);
			}
			$originalSearchBox.hide();
		}
	}

	var $secondActions = jQuery('.post-type-wpep_subscriptions #posts-filter .tablenav .alignleft.actions').eq(1);
	if ($secondActions.length > 0) {
		$secondActions.append($originalSearchBox.children().clone());
		$originalSearchBox.hide();
	} else {
		putSearchBoxInTablenav();
	}
	setTimeout(putSearchBoxInTablenav, 150);

	jQuery( '.wpep_subscription_action' ).click(
		function() {
	
			var subscription_action = jQuery( this ).data( 'action' );
			var subscription_id     = jQuery( this ).data( 'subscription' );
	
			var data = {
	
				'action': 'wpep_subscription_action_update',
				'subscription_action': subscription_action,
				'subscription_id': subscription_id
	
			};
	
			jQuery.post(
				ajaxurl,
				data,
				function(response) {
					if ( response == 'success' || response == 'success_renew' || response == 'success_cancel' ) {
						location.reload();
					}else {
						var errorObj = JSON.parse(response);
						var errorMessage =  errorObj.errorDetail+'. Customer might have been deleted in Square.';
						showErrorModal(errorMessage);
					}
	
				}
			);
	
		}
	);

});
function showErrorModal(message) {
  jQuery('#custom-errorMessage').text(message);
  jQuery('#custom-errorModal').show();
}

// Hide the modal when the close button is clicked
jQuery('.close').click(function() {
  jQuery('#custom-errorModal').hide();
});