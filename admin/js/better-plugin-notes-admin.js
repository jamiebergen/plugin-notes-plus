jQuery( document ).ready( function( $ ) {

    "use strict";
    /**
     * The file is enqueued from admin/class-better-plugin-notes-admin.php.
     */

    // if there is a plugin note, show it and edit | delete
    // if there is not a plugin note, show Add plugin note +
    $.each( $('.bpn-wrapper'), function() {
        var pluginId = $(this).attr('id');
        var note = $('#' + pluginId + ' .bpn-plugin-note').text().trim();

        if ( 0 === note.length ) {
            $('#' + pluginId + ' .bpn-add-note-wrapper').show();
            $('#' + pluginId + ' .bpn-show-note-wrapper').hide();
        }

    });

    // Preview dashicon corresponding to selected note type
    $.each( $('.select-dashicon-for-note'), function() {
        $(this).change(function(){
            var icon = $(this).val();
            $(this).prev().html('<span class="dashicons '+icon+'"></span>');
        });

        $(this).change();
    });

    // Add target="_blank" to all links
    $('.bpn-plugin-note a').each(function(){
        $(this).attr( 'target', '_blank' );
    });


    $('.bpn-add-note').click( function( event ) {

        event.preventDefault();

        $(this).hide();
        $(this).siblings('.bpn-note-form-wrapper').show();
    });

    $('.bpn-save-note').click( function( event ) {

        event.preventDefault();

        var pluginId = $(this).closest('.bpn-wrapper').attr('id');
        var noteContent = $(this).siblings('.bpn-note-form').val();
        var noteIcon = $(this).siblings().find('.select-dashicon-for-note').val();

        // This does the ajax request
        $.ajax({
            url: params.ajaxurl,
            data: {
                'action': 'bpn_form_response',
                'note' : noteContent,
                'icon' : noteIcon,
                'noteId' : pluginId,
				'security' : params.ajax_nonce
            },
            success:function( response ) {
                // This outputs the result of the ajax request

                $('#' + pluginId + ' .bpn-add-note-wrapper').hide();
                $('#' + pluginId + ' .bpn-plugin-note').html( response );
                $('#' + pluginId + ' .bpn-show-note-wrapper').show();
                // add target blank to a tags
                $('#' + pluginId + ' .bpn-plugin-note a').attr( 'target', '_blank' );;

            },
            error: function( errorThrown ){
                console.log( errorThrown );
            }
        });
    });

    $('.bpn-edit-note').click( function( event ) {

        event.preventDefault();

        var pluginId = $(this).attr('id');

    });

    $('.bpn-cancel-note').click( function( event ) {

        event.preventDefault();



    });

    $('.bpn-delete-note').click( function( event ) {

        event.preventDefault();

        var pluginId = $(this).closest('.bpn-wrapper').attr('id');

        // This does the ajax request
        $.ajax({
            url: params.ajaxurl,
            data: {
                'action': 'bpn_delete_response',
                'noteId' : pluginId
            },
            success:function( data ) {
                // This outputs the result of the ajax request
                console.log( data );

                $('#' + pluginId + ' .bpn-show-note-wrapper').hide();
                $('#' + pluginId + ' .bpn-add-note-wrapper').show();

                $('#' + pluginId + ' .bpn-add-note').show();
                $('#' + pluginId + ' .bpn-note-form-wrapper').hide();
                
            },
            error: function( errorThrown ){
                console.log( errorThrown );
            }
        });

    });

});