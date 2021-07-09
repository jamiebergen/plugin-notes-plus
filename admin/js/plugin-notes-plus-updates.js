/**
 * This file is enqueued from admin/class-plugin-notes-plus-admin.php
 * on the update-core.php page only.
 */

(function($) {
    $(document).ready(function () {

        $('#update-plugins-table thead tr, #update-plugins-table tfoot tr').append( '<td id="pnp_plugin_notes_col">'+ labels.col_title +'</td>' );

        var notes_for_plugin_updates = JSON.parse(updates);

        var i = 0;

        $('#update-plugins-table .plugins tr').each(function() {

            var plugin_notes = notes_for_plugin_updates[i];

            if (plugin_notes === undefined || plugin_notes.length === 0) {

                $(this).append( '<td>'+ labels.no_note +'</td>' );

            } else {

                var values = Object.values(plugin_notes);

                var noteMarkup = '<div class="pnp-wrapper">';

                for (const value of values) {

                    // Convert time to readable format
                    var d = new Date(value.time * 1000);
                    var month = ("0" + (d.getMonth() + 1)).slice(-2);
                    var date = ("0" + d.getDate()).slice(-2);
                    var year = d.getFullYear();
                    var formattedDate = year + '-' + month + '-' + date;

                    noteMarkup += '<div class="pnp-plugin-note">';
                    noteMarkup += '<span class="dashicons '+ value.icon +'"></span>';
                    noteMarkup += value.note;
                    noteMarkup += '<p class="pnp-note-meta">'+ value.user +' | <span class="pnp-note-time">'+ formattedDate +'</span></p>';
                    noteMarkup += '</div>';

                }
                noteMarkup += '</div>';

                $(this).append( '<td>'+ noteMarkup +'</td>' );
            }

            i++;
        });

        // Add target="_blank" to all links
        $('.pnp-plugin-note a').each(function(){
            $(this).attr( 'target', '_blank' );
        });

    });
})(jQuery);