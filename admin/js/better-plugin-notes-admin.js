// Store all notes in a global variable for easy access
var pluginNotes = {};
function registerPluginNote( pluginId, noteIndex, noteContent, noteIcon ) {
    uniqueId = pluginId + '-' + noteIndex;
    pluginNotes[uniqueId] = {
        note: noteContent,
        icon: noteIcon
    };
}

jQuery( document ).ready( function( $ ) {

    "use strict";
    /**
     * The file is enqueued from admin/class-better-plugin-notes-admin.php.
     */

    function deleteNote( pluginId, noteId, noteIndex ) {

        if (confirm(params.confirm_delete)) {
            // This does the ajax request
            $.ajax({
                url: params.ajaxurl,
                data: {
                    'action': 'bpn_delete_response',
                    'pluginId' : pluginId,
                    'noteIndex' : noteIndex,
                    'security' : params.ajax_nonce
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
        return false;
    }

    function editNote( noteToEdit, pluginId, noteIndex ) {

        noteToEdit.hide();

        // Show form with existing content
        noteToEdit.closest('.bpn-wrapper').find('.bpn-note-form-wrapper').last().clone(true).insertAfter(noteToEdit).show();

        var editNoteForm = noteToEdit.next('.bpn-note-form-wrapper');

        var noteContent = pluginNotes[pluginId + "-" + noteIndex].note;
        var noteIcon = pluginNotes[pluginId + "-" + noteIndex].icon;

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

        if ($.trim(noteContent) === "") {
            alert(params.needs_content);
            return false;
        }

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
                    $(singleNoteMarkup( response.processed_note, response.note_icon, pluginId, response.new_note_index )).insertBefore('#' + pluginId + ' .bpn-add-note-wrapper');
                    addNoteLink.show();
                }
                // Case where user edits existing note
                var existingNote = noteForm.prev('.bpn-show-note-wrapper');
                if (existingNote.length){
                    existingNote.replaceWith(singleNoteMarkup( response.processed_note, response.note_icon, pluginId, response.new_note_index ));
                }

                // Attach delete and edit event handlers to new note
                var noteId = pluginId + '-' + response.new_note_index;

                $('#' + noteId + ' .bpn-delete-note').click( function( event ) {

                    event.preventDefault();
                    deleteNote(pluginId, noteId, response.new_note_index);
                });

                $('#' + noteId + ' .bpn-edit-note').click( function( event ) {

                    event.preventDefault();
                    var noteToEdit = $(this).closest('.bpn-show-note-wrapper');
                    editNote( noteToEdit, pluginId, response.new_note_index );
                });

                // Add target blank to a tags
                $('#' + pluginId + ' .bpn-plugin-note a').attr( 'target', '_blank' );

                // Add new note to dictionary
                registerPluginNote( pluginId, response.new_note_index, response.processed_note, response.note_icon );

            },
            error: function( errorThrown ){
                console.log( errorThrown );
            }
        });
    });

    function singleNoteMarkup( note, icon, pluginId, index ) {

        note = note.replace(/\n/g, "<br />"); // maintain line breaks

        var markup = '';
        markup += '<div class="bpn-show-note-wrapper" id="' + pluginId + '-' + index + '">';
        markup += '<div class="bpn-plugin-note">';
        markup += '<span class="dashicons ' + icon + '"></span>';
        markup += note;
        markup += '</div>';
        markup += '<a href="#" class="bpn-edit-note">' + params.edit_text + '</a> | ';
        markup += '<a href="#" class="bpn-delete-note">' + params.delete_text + '</a>';
        markup += '</div>';

        return markup;
    }

    $('.bpn-edit-note').click( function( event ) {

        event.preventDefault();

        var noteToEdit = $(this).closest('.bpn-show-note-wrapper');

        var pluginId = $(this).closest('.bpn-wrapper').attr('id');
        var noteId = noteToEdit.attr('id');
        var start = noteId.lastIndexOf('-') + 1;
        var noteIndex = noteId.substr(start);

        editNote( noteToEdit, pluginId, noteIndex );

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