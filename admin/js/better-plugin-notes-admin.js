jQuery( document ).ready( function( $ ) {

    "use strict";
    /**
     * The file is enqueued from admin/class-better-plugin-notes-admin.php.
     */

    function deleteNote( pluginId, noteId, noteIndex ) {

        // This does the ajax request
        $.ajax({
            url: params.ajaxurl,
            data: {
                'action': 'bpn_delete_response',
                'pluginId' : pluginId,
                'noteIndex' : noteIndex
            },
            success:function( data ) {
                // This outputs the result of the ajax request

                $('#' + noteId + '.bpn-show-note-wrapper').remove();

            },
            error: function( errorThrown ){
                console.log( errorThrown );
            }
        });
    }

    function editNote( noteContent, noteIcon, noteToEdit ) {

        noteToEdit.hide();

        // Show form with existing content
        noteToEdit.closest('.bpn-wrapper').find('.bpn-note-form-wrapper').last().clone(true).insertAfter(noteToEdit).show();

        var editNoteForm = noteToEdit.next('.bpn-note-form-wrapper');

        editNoteForm.find('.bpn-note-form').val(noteContent);
        editNoteForm.find('.select-dashicon-for-note').val(noteIcon);
        editNoteForm.find('.view-icon').html('<span class="dashicons ' + noteIcon + '"></span>');

    }

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

        var newNoteForm = $(this).siblings('.bpn-note-form-wrapper');

        // Clear previous form content, reset icon, show form
        newNoteForm.find('.bpn-note-form').val('');
        newNoteForm.find('.select-dashicon-for-note').val('dashicons-clipboard');
        newNoteForm.find('.view-icon').html('<span class="dashicons dashicons-clipboard"></span>');

        newNoteForm.show();

    });

    $('.bpn-save-note').click( function( event ) {

        event.preventDefault();

        // Get plugin ID and basic info for new note
        var pluginId = $(this).closest('.bpn-wrapper').attr('id');
        var noteContent = $(this).siblings('.bpn-note-form').val();
        var noteIcon = $(this).siblings().find('.select-dashicon-for-note').val();

        // Get existing note index if available (for case of edit)
        var noteIndex = '';
        var noteToEdit = $(this).closest('.bpn-note-form-wrapper').prev('.bpn-show-note-wrapper');

        if (noteToEdit.length) {
            var noteId = noteToEdit.attr('id');
            var start = noteId.lastIndexOf('-') + 1;
            noteIndex = noteId.substr(start);
        }

        // Get handle on form for after the request
        var noteForm = $(this).closest('.bpn-note-form-wrapper');

        // This does the ajax request
        $.ajax({
            url: params.ajaxurl,
            data: {
                'action': 'bpn_add_response',
                'note' : noteContent,
                'icon' : noteIcon,
                'index' : noteIndex,
                'pluginId' : pluginId,
				'security' : params.ajax_nonce
            },
            success:function( response ) {

                noteForm.hide();

                // Case where user creates new note
                var addNoteLink = noteForm.siblings('.bpn-add-note');
                if (addNoteLink.length){
                    // Add new note to end of notes list
                    $(singleNoteMarkup( response.processed_note, pluginId, response.new_note_index )).insertBefore('#' + pluginId + ' .bpn-add-note-wrapper');
                    addNoteLink.show();
                }
                // Case where user edits existing note
                var existingNote = noteForm.prev('.bpn-show-note-wrapper');
                if (existingNote.length){
                    existingNote.replaceWith(singleNoteMarkup( response.processed_note, pluginId, response.new_note_index )); // !!! need to show updated markup
                }

                // Attach delete and edit event handlers to new note
                var noteId = pluginId + '-' + response.new_note_index;

                $('#' + noteId + ' .bpn-delete-note').click( function( event ) {

                    event.preventDefault();

                    var noteIndex = response.new_note_index;
                    deleteNote(pluginId, noteId, noteIndex);
                });

                $('#' + noteId + ' .bpn-edit-note').click( function( event ) {

                    event.preventDefault();

                    var noteToEdit = $(this).closest('.bpn-show-note-wrapper');
                    editNote(noteContent, noteIcon, noteToEdit);
                });

                // Add target blank to a tags
                $('#' + pluginId + ' .bpn-plugin-note a').attr( 'target', '_blank' );

            },
            error: function( errorThrown ){
                console.log( errorThrown );
            }
        });
    });

    function singleNoteMarkup( note, pluginId, index ) {

        var markup = '';
        markup += '<div class="bpn-show-note-wrapper" id="' + pluginId + '-' + index + '">';
        markup += '<div class="bpn-plugin-note">' + note + '</div>';
        markup += '<a href="#" class="bpn-edit-note">edit</a> | ';
        markup += '<a href="#" class="bpn-delete-note">delete</a>';
        markup += '</div>';

        return markup;
    }

    $('.bpn-edit-note').click( function( event ) {

        event.preventDefault();

        // Get existing content - note, icon, and index
        var noteContent = $(this).siblings('.bpn-plugin-note').text().trim();
        var iconClassArray = $(this).siblings('.bpn-plugin-note').find('.dashicons')[0].className.split(/\s+/);
        var noteIcon = '';
        $.each(iconClassArray, function(index, item) {
            if (item !== 'dashicons') {
                noteIcon = item;
            }
        });
        var noteToEdit = $(this).closest('.bpn-show-note-wrapper');

        editNote( noteContent, noteIcon, noteToEdit );

    });

    $('.bpn-cancel-note').click( function( event ) {

        event.preventDefault();

        $(this).closest('.bpn-note-form-wrapper').hide();

        // Case where user cancels new note
        var addNoteLink = $(this).closest('.bpn-note-form-wrapper').siblings('.bpn-add-note');
        if (addNoteLink.length){
            addNoteLink.show();
        }

        // Case where user cancels existing note
        var existingNote = $(this).closest('.bpn-note-form-wrapper').prev('.bpn-show-note-wrapper');
        if (existingNote.length){
            existingNote.show();
        }

    });

    $('.bpn-delete-note').click( function( event ) {

        event.preventDefault();

        var pluginId = $(this).closest('.bpn-wrapper').attr('id');
        var noteId = $(this).closest('.bpn-show-note-wrapper').attr('id');
        var start = noteId.lastIndexOf('-') + 1;
        var noteIndex = noteId.substr(start);

        deleteNote(pluginId, noteId, noteIndex);

    });


});