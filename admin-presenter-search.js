/**
 * admin-presenter-search.js
 * Handles the jQuery UI Autocomplete for linking Presenters to Events.
 */
jQuery( document ).ready( function( $ ) {
    
    // Target the search input
    var searchField = $( '#evmpr_presenter_search' );
    var hiddenIdField = $( '#evmpr_select_presenter_id' );

    // Only run if the field exists on the current page
    if ( searchField.length === 0 ) {
        return;
    }

    searchField.autocomplete({
        source: function( request, response ) {
            $.ajax({
                url: evmpr_ajax_obj.ajax_url,
                dataType: 'json',
                data: {
                    action: 'evmpr_search_presenters',
                    term: request.term,
                    security: evmpr_ajax_obj.nonce
                },
                success: function( data ) {
                    response( data );
                }
            });
        },
        minLength: 2,
        select: function( event, ui ) {
            // Apply the selected ID to the hidden input
            hiddenIdField.val( ui.item.id );
        }
    });

    // Clear the hidden ID if the user manually empties the text input
    searchField.on( 'input', function() {
        if ( $( this ).val() === '' ) {
            hiddenIdField.val( '' );
        }
    });
    
});
