<?php


/* we exit if uninstall not called from WordPress exit */
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

/* we remove the api data set in the db */
delete_option( 'upfront_api_request_products' );

/* we remove the framework option group */
delete_option( 'upfront_framework' );

/* we remove upgrade flags */
delete_option( 'upfront_framework_upgrade' );