<?php

add_action( 'upfront_init', 'upfront_define_constant', 1 );

function upfront_define_constant() {
	
	/* UPFRONTROCKET_VERSION, UPFRONTROCKET_PATH, UPFRONTROCKET_URL are defined by the butler_load_component_once() function */
	define( 'UPFRONTROCKET_FUNCTIONS_PATH', UPFRONTROCKET_PATH . 'functions/' );
	define( 'UPFRONTROCKET_COMPONENTS_PATH', UPFRONTROCKET_PATH . 'components/' );
	define( 'UPFRONTROCKET_ADMIN_PATH', UPFRONTROCKET_PATH . 'admin/' );
	define( 'UPFRONTROCKET_ADMIN_CLASSES_PATH', UPFRONTROCKET_ADMIN_PATH . 'classes/' );
	define( 'UPFRONTROCKET_ADMIN_MODELS_PATH', UPFRONTROCKET_ADMIN_PATH . 'models/' );
	define( 'UPFRONTROCKET_ADMIN_PAGES_PATH', UPFRONTROCKET_ADMIN_PATH . 'pages/' );
	
	/* define url */
	define( 'UPFRONTROCKET_ADMIN_ASSETS_URL', UPFRONTROCKET_URL . 'admin/assets/' );
	define( 'UPFRONTROCKET_ADMIN_CSS_URL', UPFRONTROCKET_ADMIN_ASSETS_URL . 'css/' );
	define( 'UPFRONTROCKET_ADMIN_JS_URL', UPFRONTROCKET_ADMIN_ASSETS_URL . 'js/' );
	define( 'UPFRONTROCKET_ADMIN_IMAGES_URL', UPFRONTROCKET_ADMIN_ASSETS_URL . 'images/' );
	
	/* menu */
	define( 'UPFRONTROCKET_PARENT_MENU', 'pur-dashboard' );
	
}


add_action( 'upfront_init', 'upfront_do_maintenance', 2 );

function upfront_do_maintenance() {

	require_once( UPFRONTROCKET_PATH . 'maintenance.php' );
	
	$option = get_option( 'upfront_framework_upgrade' );
	
	if ( empty( $option ) )
		$option = array();	
	
	/* set the tasks and callback function */
	$tasks = array(
		'dashboard_data' => 'upfront_framework_maintenance_dashboard_data',
		'admin_options_group_name' => 'upfront_framework_maintenance_admin_options_group_name'
	);
	
	foreach ( $tasks as $task => $callback ) {
	
		if ( isset( $option[$task] ) && $option[$task] )
			continue;
			
		call_user_func( $callback );
			
		$option[$task] = true;
		
		update_option( 'upfront_framework_upgrade', $option );
	
	}
	
}


add_action( 'upfront_init', 'upfront_load_components' );

function upfront_load_components() {

	butler_load_components( array( 'options' ) );
	
	butler_load( UPFRONTROCKET_ADMIN_CLASSES_PATH . 'admin', 'PUR_Admin', true );
	
	require_once( UPFRONTROCKET_FUNCTIONS_PATH . 'utils.php' );

}


/* we register the framework assets to make it available for users */
add_action( 'admin_enqueue_scripts', 'register_UpFrontroket_assets' );

function register_UpFrontroket_assets(  ) {

	$global_css_path = version_compare( get_bloginfo( 'version' ), '3.8', '>=' ) ? 'global' : 'depreciate-global' ;
	
	/* css */
	wp_register_style( 'pur-global', UPFRONTROCKET_ADMIN_CSS_URL . $global_css_path . BUTLER_MIN_CSS . '.css', false, UPFRONTROCKET_VERSION );
	
}


do_action( 'upfront_init' );