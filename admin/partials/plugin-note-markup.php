<?php

/**
 * Display the plugin note as well as options to edit or delete it.
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/jamiebergen
 * @since      1.0.0
 *
 * @package    Plugin_Notes_Plus
 * @subpackage Plugin_Notes_Plus/admin/partials
 */

if ( current_user_can('activate_plugins') ) {
    ?>
    <div class="pnp-wrapper" id="<?php echo esc_html( $plugin_unique_id_sanitized ) ?>" data-pluginfile="<?php echo esc_html( $plugin_unique_id ); ?>">

        <?php
        if ( $the_plugin_notes ) {
            foreach ( $the_plugin_notes as $note_index => $the_plugin_note ) { ?>
                <div class="pnp-show-note-wrapper" id="<?php echo esc_attr( $plugin_unique_id_sanitized . '_' . $note_index ); ?>">
                    <div class="pnp-plugin-note">
                        <span class="dashicons <?php echo esc_attr( $the_plugin_note['icon'] ) ?>"></span><?php echo nl2br( ( wp_kses( $the_plugin_note[ 'note' ], $allowed_tags ) ) ); ?>
                        <p class="pnp-note-meta"><?php echo esc_html( $the_plugin_note['user'] ) ?> | <span class="pnp-note-time"></span></p>
                    </div>
                    <a href="#" class="pnp-edit-note"><?php esc_html_e( 'Edit', $this->plugin->get_plugin_name() ) ?></a> |
                    <a href="#" class="pnp-delete-note"><?php esc_html_e( 'Delete', $this->plugin->get_plugin_name() ) ?></a>
                    <span class="pnp-spin dashicons dashicons-update"></span>
                </div>
                <script>
                    registerPluginNote( "<?php echo esc_js( $plugin_unique_id_sanitized ); ?>",
                                        "<?php echo esc_js( $note_index ); ?>",
                                        "<?php echo esc_js( wp_kses( $the_plugin_note[ 'note' ], $allowed_tags ) ); ?>",
                                        "<?php echo esc_js( $the_plugin_note['icon'] ); ?>",
                                        "<?php echo esc_js( $the_plugin_note['time'] ); ?>",
                    );

                </script>
                <?php
            }
        }
        ?>
        <div class="pnp-add-note-wrapper">
            <a href="#" class="pnp-add-note"><?php esc_html_e( '+ Add plugin note', $this->plugin->get_plugin_name() ) ?></a>
            <div class="pnp-note-form-wrapper">

                <label>
                    <?php esc_html_e( 'Note type:', $this->plugin->get_plugin_name() ) ?>
                    <span class="view-icon"></span>
                    <select id="<?php echo esc_attr( $plugin_unique_id_sanitized ); ?>" class="select-dashicon-for-note">
                        <?php foreach ( $icon_options_array as $icon_class => $icon_name ) {
                            echo '<option value="'. esc_attr( $icon_class ) . '">' . esc_html__( $icon_name, $this->plugin->get_plugin_name() ) . '</option>';
                        } ?>
                    </select>
                </label>
                <textarea class="pnp-note-form"></textarea>
                <a href="#" class="pnp-save-note"><?php esc_html_e( 'Save note', $this->plugin->get_plugin_name() ) ?></a>
                <span class="pnp-spin dashicons dashicons-update"></span>
                <span class="pnp-divider"> | </span>
                <a href="#" class="pnp-cancel-note"><?php esc_html_e( 'Cancel', $this->plugin->get_plugin_name() ) ?></a>
                <div id="pnp_form_feedback"></div>
            </div>
        </div>

    </div>

    <?php
}
else {
    ?>
    <p> <?php esc_html__( "You are not authorized to perform this operation.", $this->plugin->get_plugin_name() ) ?> </p>
    <?php
}
