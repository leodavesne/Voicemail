<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-base-template"><br></div>
	<h2><?php _e( "Base plugin page", 'vobase' ); ?></h2>

	<p><?php _e( "Sample base plugin page", 'vobase' ); ?></p>

	<form id="vo-plugin-base-form" action="options.php" method="POST">

			<?php settings_fields( 'vo_setting' ) ?>
			<?php do_settings_sections( 'vo-plugin-base' ) ?>

			<input type="submit" value="<?php _e( "Save", 'vobase' ); ?>" />
	</form> <!-- end of #votemplate-form -->
</div>
