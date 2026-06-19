/**
 * Transactions list: filter by payment form via GET param (same as All Forms count link).
 */
(function () {
	'use strict';

	var sel = document.getElementById( 'wpep-transactions-form-filter' );
	if ( ! sel || typeof wpepTransactionsFormFilter === 'undefined' ) {
		return;
	}

	/* Top tablenav: first .alignleft.actions is bulk actions; filters row is the other one. */
	var row = document.querySelector( '.tablenav.top .alignleft.actions:not(.bulkactions)' ) || document.querySelectorAll( '.tablenav.top .alignleft.actions' )[1];
	var submit = row && row.querySelector( 'input[name="filter_action"]' );
	var wrap = document.querySelector( '.wpep-transactions-form-filter-wrap' );
	if ( wrap && submit && submit.parentNode ) {
		submit.insertAdjacentElement( 'afterend', wrap );
	}

	var param = wpepTransactionsFormFilter.param;

	sel.addEventListener( 'change', function () {
		var url = new URL( window.location.href );
		var v = sel.value;

		if ( v ) {
			url.searchParams.set( param, v );
		} else {
			url.searchParams.delete( param );
		}

		url.searchParams.delete( 'paged' );
		window.location.href = url.toString();
	} );
} )();
