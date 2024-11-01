<?php
//if uninstall not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
	exit();

// clean up the dashboard_widget_options key in the options table
$dashboard_options = get_option( 'dashboard_widget_options' );
unset($dashboard_options['todo_options']);
update_option( 'dashboard_widget_options', $dashboard_options );

// remove all todo user meta keys
$users = get_users();
foreach ($users as $user) {
	delete_user_meta($user->ID, 'todo_items');
}
?>
