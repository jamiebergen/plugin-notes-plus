<?php

/**
 * Display the plugin note as well as options to edit or delete it.
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://jamiebergen.com/
 * @since      1.0.0
 *
 * @package    Better_Plugin_Notes
 * @subpackage Better_Plugin_Notes/admin/partials
 */

if ( current_user_can('activate_plugins') ) {
	?>
	<div class="bpn-wrapper" id="<?php echo $plugin_unique_id; ?>">

        <?php
        if ( $the_plugin_notes ) { // Show any existing plugin notes !!! Need to sort to ensure correct order
	        foreach ( $the_plugin_notes as $note_index => $the_plugin_note ) { ?>
                <div class="bpn-show-note-wrapper" id="<?php echo $plugin_unique_id . '-' . $note_index; ?>">
                    <div class="bpn-plugin-note">
				        <?php echo $the_plugin_note; ?>
                    </div>
                    <a href="#" class="bpn-edit-note"><?php esc_html_e( 'edit', $this->plugin_name ) ?></a> |
                    <a href="#" class="bpn-delete-note"><?php esc_html_e( 'delete', $this->plugin_name ) ?></a>
                </div>
		        <?php
            }
        }
        ?>

		<div class="bpn-add-note-wrapper">
			<a href="#" class="bpn-add-note"><?php esc_html_e( '+ Add plugin note', $this->plugin_name ) ?></a>
			<div class="bpn-note-form-wrapper">

                <label>
                    Note type:
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
                <textarea class="bpn-note-form"></textarea>
				<a href="#" class="bpn-save-note"><?php esc_html_e( 'Save note', $this->plugin_name ) ?></a> |
                <a href="#" class="bpn-cancel-note"><?php esc_html_e( 'Cancel', $this->plugin_name ) ?></a>
				<div id="bpn_form_feedback"></div>
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