<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-base-template"><br></div>
	<h2><?php _e( "Remote plugin page", 'vobase' ); ?></h2>

	<p><?php _e( "Performing side activities - AJAX and HTTP fetch", 'vobase' ); ?></p>
	<div id="vo_page_messages"></div>

	<?php
		$vo_ajax_value = get_option( 'vo_option_from_ajax', '' );
	?>

	<h3><?php _e( 'Store a Database option with AJAX', 'vobase' ); ?></h3>
	<form id="vo-plugin-base-ajax-form" action="options.php" method="POST">
			<input type="text" id="vo_option_from_ajax" name="vo_option_from_ajax" value="<?php echo $vo_ajax_value; ?>" />

			<input type="submit" value="<?php _e( "Save with AJAX", 'vobase' ); ?>" />
	</form> <!-- end of #vo-plugin-base-ajax-form -->

	<h3><?php _e( 'Fetch a title from URL with HTTP call through AJAX', 'vobase' ); ?></h3>
	<form id="vo-plugin-base-http-form" action="options.php" method="POST">
			<input type="text" id="vo_url_for_ajax" name="vo_url_for_ajax" value="http://wordpress.org" />

			<input type="submit" value="<?php _e( "Fetch URL title with AJAX", 'vobase' ); ?>" />
	</form> <!-- end of #vo-plugin-base-http-form -->

	<div id="resource-window">
	</div>

</div>
