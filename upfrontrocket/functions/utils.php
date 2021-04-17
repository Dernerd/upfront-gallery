<?php

function upfront_block_maintenance( $id ) {
	
	require_once( UPFRONTROCKET_COMPONENTS_PATH . 'maintenance/class.php' );
	
	$class = new UpFrontRocketBlockMaintenance( $id );
		
	return $class;

}


function upfront_load_block_options_assets( $block_id, $callback = null ) {

	if( !$block_id )
		return;

	$block_options_js_path = UPFRONTROCKET_ADMIN_JS_URL . 'block-options.js';

	if ( version_compare('3.7', UPFRONT_VERSION, '<=') )
		$block_options_css_path = UPFRONTROCKET_ADMIN_CSS_URL . 'block-options.css';
	else
		$block_options_css_path = UPFRONTROCKET_ADMIN_CSS_URL . 'depreciate-block-options.css';

	return '
		$("#block-' . $block_id . '-tab").hide();
		
		if ( pur.assetsLoaded["' . $block_options_js_path . '"] !== true ) {

			$.getScript( "' . $block_options_js_path . '", function() { 

				pur.assetsLoaded["' . $block_options_js_path . '"] = true;
				
				pur.blockOptionsApi.loadStyle( "' .$block_options_css_path . '" );
				' . $callback . '

				$("#block-' . $block_id . '-tab").fadeIn();

			});

		} else {

			' . $callback . '

			$("#block-' . $block_id . '-tab").fadeIn();

		}
	';

}