<?php

/**
 * Display the plugin note as well as options to edit or delete it.
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://jamiebergen.com/
 * @since      1.0.0
 *
 * @package    Plugin_Notes_Plus
 * @subpackage Plugin_Notes_Plus/admin/partials
 */

if ( current_user_can('activate_plugins') ) {
	?>
	<div class="pnp-wrapper" id="<?php echo $plugin_unique_id; ?>">

        <?php
        if ( $the_plugin_notes ) {
	        foreach ( $the_plugin_notes as $note_index => $the_plugin_note ) { ?>
                <div class="pnp-show-note-wrapper" id="<?php echo $plugin_unique_id . '-' . $note_index; ?>">
                    <div class="pnp-plugin-note">
                        <span class="dashicons <?php echo $the_plugin_note['icon'] ?>"></span><?php echo nl2br( $the_plugin_note[ 'note' ] ); ?>
                    </div>
                    <a href="#" class="pnp-edit-note"><?php esc_html_e( 'edit', $this->plugin_name ) ?></a> |
                    <a href="#" class="pnp-delete-note"><?php esc_html_e( 'delete', $this->plugin_name ) ?></a>
                </div>
                <script>
                    registerPluginNote( "<?php echo $plugin_unique_id; ?>",
                                        "<?php echo $note_index; ?>",
                                        "<?php echo str_replace("\n", '\n', $the_plugin_note['note']); ?>",
                                        "<?php echo $the_plugin_note['icon']; ?>",
                    );

                </script>
		        <?php
            }
        }
        ?>
		<div class="pnp-add-note-wrapper">
			<a href="#" class="pnp-add-note"><?php esc_html_e( '+ Add plugin note', $this->plugin_name ) ?></a>
			<div class="pnp-note-form-wrapper">

                <label>
	                <?php esc_html_e( 'Note type:', $this->plugin_name ) ?>
                    <span class="view-icon"></span>
                    <select id="<?php echo $plugin_unique_id; ?>" class="select-dashicon-for-note">
                        <option value="dashicons-clipboard"><?php esc_html_e( 'Note', $this->plugin_name ) ?></option>
                        <option value="dashicons-info"><?php esc_html_e( 'Info', $this->plugin_name ) ?></option>
                        <option value="dashicons-admin-links"><?php esc_html_e( 'Link', $this->plugin_name ) ?></option>
                        <option value="dashicons-warning"><?php esc_html_e( 'Warning', $this->plugin_name ) ?></option>
                        <option value="dashicons-admin-network"><?php esc_html_e( 'Key', $this->plugin_name ) ?></option>
                        <option value="dashicons-yes"><?php esc_html_e( 'Checkmark', $this->plugin_name ) ?></option>
                    </select>
                </label>
                <textarea class="pnp-note-form"></textarea>
				<a href="#" class="pnp-save-note"><?php esc_html_e( 'Save note', $this->plugin_name ) ?></a> |
                <a href="#" class="pnp-cancel-note"><?php esc_html_e( 'Cancel', $this->plugin_name ) ?></a>
				<div id="pnp_form_feedback"></div>
			</div>
		</div>

	</div>

	<?php
}
else {
	?>
	<p> <?php esc_html__("You are not authorized to perform this operation.", $this->plugin_name) ?> </p>
	<?php
}