<?php
/*
Plugin Name: To-Do Dashboard Widget
Description: An intuitive, easy-to-use dashboard widget to store your to-do items.
Version: 1.1
Author: admin@catchmyfame.com
License: GPLv2 or later
*/

add_action( 'wp_dashboard_setup', 'todo_dashboard_widget_setup' );	// Hook into the dashboard setup so we can add ours
add_action( 'admin_enqueue_scripts', 'todo_js' );			// Hook into the script loader to load our JS and CSS
add_action( 'wp_ajax_todo_add_item', 'todo_ajax_add_item' );		// Hook for our add item AJAX handler
add_action( 'wp_ajax_todo_update', 'todo_ajax_update' );		// Hook for our update item(s) AJAX handler
add_action( 'wp_ajax_todo_get_options', 'todo_ajax_get_options' );	// Hook for our get options AJAX handler
register_activation_hook( __FILE__, 'todo_activate' );			// Hook to run on plugin activation. Inits the options.
include( plugin_dir_path( __FILE__ ) . 'config.php');			// Include the config file config.php

/**
 * Load the CSS and JS filse and WP's AJAX script handler.
 */
function todo_js($hook) {
	if( $hook == 'index.php'){
		wp_enqueue_style( 'todo-list-css', plugins_url('todo.css', __FILE__) );
		wp_enqueue_style( 'todo-list-fontawesome-css', '//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css' );
		wp_enqueue_script( 'todo_js', plugin_dir_url( __FILE__ ) . 'todo.js', array('jquery','jquery-ui-sortable') );
		wp_localize_script( 'todo_js', 'todo_object', array( 'ajax_url' => admin_url( 'admin-ajax.php') ) );
	}
}

/**
 * Add todo widget to the dashboard.
 *
 * This function is hooked into the 'wp_dashboard_setup' action below. Only will display for the selected roles and always the admin.
 */
function todo_dashboard_widget_setup() {
	$dashboard_options = get_option( 'dashboard_widget_options' );
	global $current_user;
	if( !empty( $dashboard_options['todo_options']['todo_role_limit'] ) ) $new_array = array_intersect( $current_user->roles, $dashboard_options['todo_options']['todo_role_limit'] );
	if( ( !empty( $dashboard_options['todo_options']['todo_role_limit'] ) AND !empty( $new_array ) ) OR current_user_can( 'manage_options' ) ) {
		wp_add_dashboard_widget(
			'todo-widget',			// Widget slug (handle). This will be used as its css ID and its key in the array of widgets.
			'To-Do List',			// Title to display on widget in dashboard.
			'todo_dashboard_widget',	// Function to create and display the widget.
			'todo_config'			// Config page for admin
		);	
	}
}

/**
 * Create the To-Do Dashboard Widget.
 */
function todo_dashboard_widget() {
	echo "<input name=\"item\" id=\"todo_add_item\" type=\"text\">\n<ul id=\"todo-list\">\n";

	$prev_todo = get_user_meta(get_current_user_id(), 'todo_items', true);
	$dashboard_options = get_option( 'dashboard_widget_options' );

	if( isset( $dashboard_options['todo_options']['todo_age_colors'] ) ){
		$age_colors = array();
		for($i=0; $i < count($dashboard_options['todo_options']['todo_age_colors']['number']); $i++){
			$age_timestamp = strtotime($dashboard_options['todo_options']['todo_age_colors']['number'][$i] . ' '. $dashboard_options['todo_options']['todo_age_colors']['period'][$i] . ' ago') + (get_option('gmt_offset')*3600);
			$age_colors[$age_timestamp] = $dashboard_options['todo_options']['todo_age_colors']['color'][$i];
		}
		krsort($age_colors);
	}

	if(!empty($prev_todo)) {
		for($i=0; $i<count( $prev_todo ); $i++){
			$timestamp = $prev_todo[$i]['timestamp']/1000 + (get_option('gmt_offset')*3600);
			$completed = ($prev_todo[$i]['completed'] == "true") ? "completed" : "";

			$style = '';
			if( !empty( $age_colors ) ) foreach( $age_colors as $age=>$color ){ if($timestamp <= $age) $style = $color; }

			echo "<li style=\"background-color:$style\" data-timestamp=\"".$prev_todo[$i]['timestamp']."\" data-completed=\"".$prev_todo[$i]['completed']."\" class=\"$completed\">";
			echo "<span class=\"todo-item-text\">".$prev_todo[$i]['text']."</span>";
			if($dashboard_options['todo_options']['todo_show_completed']) { echo "<i title=\"Mark as completed\" class=\"fa fa-check-circle fa-lg $completed\"></i>"; }
			echo "<i title=\"Delete\" class=\"fa fa-times-circle fa-lg\"></i>";
			if($dashboard_options['todo_options']['todo_show_age']) { echo "<span class=\"todo-timestamp\" title=\"".date('F j, Y, g:i a',$timestamp)."\">".human_time_diff( $timestamp, current_time('timestamp') )." ago</span>\n"; }
			echo "</li>\n";
		}
	}
	echo "</ul>\n";
}

/**
 * Add a new to-do item
 */
function todo_ajax_add_item(){
	$user_id = get_current_user_id();
	$dashboard_options = get_option( 'dashboard_widget_options' );

	// This next block takes the allowed HTML option and parses into a format that wp_kses can understand
	$html_array = explode(',', $dashboard_options['todo_options']['todo_allowed_html']);
	$temp_array = array();
	foreach($html_array as $k=>$v){
		preg_match('/(.*)\((.*?)\)/', $v, $match);
		if(count($match) > 0){
			$att_array = explode('/',$match[2]);
			foreach($att_array as $att){
				$new_att_array[$att] = array();
			}
			$temp_array[$match[1]] = $new_att_array;
		} else {
 			$temp_array[$v] = array();
		}
	}
	$allowed_html = $temp_array;

	if( isset( $_POST['post_var'] ) ) {

		// Existing items
		$prev_todo = get_user_meta($user_id, 'todo_items', true);

		// New item to add
		$todo_item = $_POST['post_var'];

		// Sanitize permitted HTML
		$todo_item['text'] = wp_kses( $todo_item['text'], $allowed_html );

		// If we have existing items, update the human readable time for each and merge with the new item. Insert in usermeta table.
		if(!empty($prev_todo)) {
			foreach($prev_todo as $key => $prev_item){
				$prev_todo[$key]['human_time'] = human_time_diff( $prev_item['timestamp']/1000 + get_option('gmt_offset')*3600, current_time('timestamp') ) . ' ago'; // Insert human time while we're at it
			}
			update_user_meta( $user_id, 'todo_items', array_merge( array( $todo_item ), $prev_todo ) );
		} else {
			update_user_meta( $user_id, 'todo_items', array( $todo_item ) );
		}
		echo json_encode(get_user_meta($user_id, 'todo_items', true));
		wp_die();	// Need die here otherwise the admin-ajax.php file will return its own zero
	}
}

/**
 * Update database after sorting, marking an item completed, or deleting an item.
 */
function todo_ajax_update(){
//	if( isset( $_POST['post_var'] ) ) { update_user_meta( get_current_user_id(), 'todo_items', $_POST['post_var'] ); }
	update_user_meta( get_current_user_id(), 'todo_items', $_POST['post_var'] );
	wp_die();
}

/**
 * Get configuration options.
 */
function todo_ajax_get_options() {
	$dashboard_options = get_option( 'dashboard_widget_options' );
	echo json_encode( $dashboard_options['todo_options'] );
	wp_die();
}
?>
