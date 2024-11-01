<?php
// Widget configuration options. These are global for all widget users and only configurable by an admin.

/**
 * Update the dashboard widget options when the config screen form is submitted. Since there's only
 * one key in the options table for all dashboard options we need to get any existing options first
 * and then merge ours with what's there now. Note that upon activating this widget that we
 * automatically add default values for this widget, so the key should exist by this stage.
 */
if( isset( $_POST['todo_postback'] ) ){
	$dashboard_options = get_option( 'dashboard_widget_options' );
	$todo_opts = array( 'todo_options' => array( 
		'todo_show_age' => isset( $_POST['todo_show_age'] ),
		'todo_show_completed' => isset( $_POST['todo_show_completed'] ),
		'todo_item_limit' => $_POST['todo_item_limit'],
		'todo_role_limit' => $_POST['todo_role_limit'],
		'todo_cleanup' => isset( $_POST['todo_cleanup'] ),
		'todo_allowed_html' => $_POST['todo_allowed_html']
	) );
	if( isset( $_POST['todo_age_number'] ) ) $todo_opts['todo_options']['todo_age_colors'] = array('number'=>$_POST['todo_age_number'],'period'=>$_POST['todo_age_period'],'color'=>$_POST['todo_age_color']);
	update_option( 'dashboard_widget_options', array_merge( $dashboard_options, $todo_opts ) );
}

/**
 * Create the configuration form.
 */
function todo_config() {
	$dashboard_options = get_option( 'dashboard_widget_options' );
	$period_array = array('minutes','hours','days','weeks','months');
	echo "<input type=\"hidden\" name=\"todo_postback\" />";

	echo "<p><label><input type=\"checkbox\" name=\"todo_show_age\" ";
	if($dashboard_options['todo_options']['todo_show_age']) { echo 'checked '; }
	echo "/>Display age for each to-do item (e.g. 2 weeks ago)? </label></p>";

	echo "<p><label><input type=\"checkbox\" name=\"todo_show_completed\" ";
	if($dashboard_options['todo_options']['todo_show_completed']) { echo 'checked ';}
	echo "/>Display completed option? </label></p>";

	echo "<p><label><input type=\"checkbox\" name=\"todo_cleanup\" ";
	if($dashboard_options['todo_options']['todo_cleanup']) { echo 'checked ';}
	echo "/>Remove all items and settings when deleting plugin? </label></p>";

	echo "<p id=\"color_text\"><b>Color item background by age:</b> <i class=\"fa fa-plus-circle fa-lg\"></i></p><div id=\"color_wrapper\">\n";
	if( isset( $dashboard_options['todo_options']['todo_age_colors'] ) ) {
		for($i = 0; $i < count( $dashboard_options['todo_options']['todo_age_colors']['number'] ); $i++){
			echo "<p class=\"cp\"><input type=\"number\" min=\"1\" name=\"todo_age_number[]\" value=\"".$dashboard_options['todo_options']['todo_age_colors']['number'][$i]."\">";
			echo "<select name=\"todo_age_period[]\">\n";
			foreach($period_array as $period){
				$selected = ($dashboard_options['todo_options']['todo_age_colors']['period'][$i] == $period) ? 'selected':'';
				echo "<option $selected>$period</option>";
			}
			echo "</select>";
			echo "<input type=\"color\" class=\"color-picker\" name=\"todo_age_color[]\" value=\"".$dashboard_options['todo_options']['todo_age_colors']['color'][$i]."\"> <i class=\"fa fa-minus-circle fa-lg\"></i><i class=\"fa fa-bars fa-lg\"></i></p>\n";
		}
	}

	echo "</div>\n<p id=\"item-limit\"><label><b>Number of items per user</b> (0 = unlimited) <input type=\"number\" min=\"0\" name=\"todo_item_limit\" value=\"";
	echo $dashboard_options['todo_options']['todo_item_limit'];
	echo "\"/></label></p>";

	echo "<p><b>Allowed HTML tags</b><br />Elements separated by commas, attributes in parenthesis separated by slashes (No spaces!). Example: <code>em,i,b,strong,a(href/title)</code>.<br />";
	echo "<textarea name=\"todo_allowed_html\">".$dashboard_options['todo_options']['todo_allowed_html']."</textarea></p>";

	$editable_roles = get_editable_roles();
	unset($editable_roles['administrator']); // Admin always will have access so remove from array
	echo "<p><label><b>Allow the following roles to access the widget:</b><br /><select multiple=\"multiple\" name=\"todo_role_limit[]\" >";
	foreach ($editable_roles as $role_name => $role_value){
		$selected = ( in_array( $role_name, $dashboard_options['todo_options']['todo_role_limit']) )?'selected':'';
		echo "<option value=\"$role_name\" $selected>".$role_value['name']."</option>\n";
	}
	echo "</select></label></p>";
}

/**
 * Upon activating the widget, add default values to the dashboard_widget_options key in the wp_options table.
 */
function todo_activate() {
	$dashboard_options = get_option( 'dashboard_widget_options' );
	$todo_opts = array( 'todo_options' => array( 
		'todo_show_age' => true,
		'todo_show_completed' => true,
		'todo_item_limit' => 0,
		'todo_role_limit' => array('administrator'),
		'todo_cleanup' => true,
		'todo_allowed_html' => 'em,i,b,strong,a(href,title)'
	) );
	if($dashboard_options != false) {
		update_option( 'dashboard_widget_options', array_merge( $dashboard_options, $todo_opts) );
	} else {
		update_option( 'dashboard_widget_options', $todo_opts );
	}
}
?>
