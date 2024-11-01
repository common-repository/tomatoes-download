<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit;

// Delete all the Plugin Options
delete_option( TOMATOES_SLUG );