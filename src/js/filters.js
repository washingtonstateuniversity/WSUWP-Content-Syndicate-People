jQuery( document ).ready( function( $ ) {

	"use strict";

	// Toggles filter options visibility.
	$( ".wsuwp-people-filter-label" ).on( "click", function() {
		var expanded = ( "false" === $( this ).attr( "aria-expanded" ) ) ? "true" : "false";

		$( this ).attr( "aria-expanded", expanded ).next( "ul" ).slideToggle( 250 );
	} );

	// Shows/hides profiles based on selected filter options.
	$( ".wsuwp-people-filter-terms" ).on( "change", "input:checkbox", function() {
		var classes = [],
			profiles = $( this ).closest( ".wsuwp-people-wrapper" ).find( ".wsuwp-person-container" );

		$( ".wsuwp-people-filter-terms input:checkbox:checked" ).each( function() {
			classes.push( "." + $( this ).val() );
		} );

		if ( classes.length > 0 ) {
			profiles.not( classes.join( "," ) ).addClass( "hidden" );
			profiles.filter( classes.join( "," ) ).removeClass( "hidden" );
		} else {
			profiles.removeClass( "hidden" );
		}
	} );

	// Shows/hides profiles based on text entered into the search input.
	$( ".wsuwp-people-filters .search" ).on( "keyup", "input", function() {
		var	search_value = $( this ).val(),
			profiles = $( this ).closest( ".wsuwp-people-wrapper" ).find( ".wsuwp-person-container" );

		if ( search_value.length > 0 ) {
			profiles.each( function() {
				var person = $( this );

				if ( person.text().toLowerCase().indexOf( search_value.toLowerCase() ) === -1 ) {
					person.addClass( "hidden" );
				} else {
					person.removeClass( "hidden" );
				}
			} );
		} else {
			profiles.removeClass( "hidden" );
		}
	} );
} );
