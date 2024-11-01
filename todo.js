jQuery( document ).ready(function() {
	// Get widget options via AJAX
	var todo_opts = { action: 'todo_get_options' };
	jQuery.post( todo_object.ajax_url, todo_opts ).done( function( data ) {
		todo_options = JSON.parse(data);
		check_limit(jQuery('#todo-list li').length);
	})

	jQuery('#todo-widget input[name="item"]').keypress(function (e) {
		var todo_item = {};
		todo_item.text = jQuery(this).val();
		todo_item.timestamp = Date.now();
		todo_item.completed = false;
		todo_item.human_time = 'Just now';
		if( e.which == 13 && jQuery.trim(todo_item.text).length > 0 ) {
			var data = {
				action: 'todo_add_item',
				post_var: todo_item
			};
			jQuery.post( todo_object.ajax_url, data).done(function( data ) {
				data = JSON.parse(data);	// Converts the json_encoded string to an object
				var show_age = (todo_options['todo_show_age']) ? '<span class="todo-timestamp">'+todo_item.human_time+'</span>' : '';
				var show_completed = (todo_options['todo_show_completed']) ? '<i class="fa fa-check-circle fa-lg"></i>': '';
				jQuery('#todo-list').prepend('<li data-timestamp="'+todo_item.timestamp+'" data-completed="false" class=""><span class="todo-item-text">'+todo_item.text+
					'</span>'+show_completed+'<i class="fa fa-times-circle fa-lg"></i>'+show_age+'</li>');
				jQuery('#todo-widget input[name="item"]').val('');
				jQuery('#todo-list li:first').css('opacity',0).slideUp(0,function(){jQuery(this).slideDown( function(){ jQuery(this).animate({'opacity':1}, 250) } )});
				check_limit(data.length);
			});
		}
	});

	jQuery('#todo-list').sortable({
		axis: "y",
		containment: jQuery("#todo-list").parent(),
		opacity: .85,
		update: function() {
			todo_update();
		}
	});

	jQuery('#color_wrapper').sortable({
		axis: "y",
		containment: jQuery("#color_wrapper"),
		opacity: .85
	});

	jQuery(document).on('click', '#todo-list .fa.fa-check-circle', function(){
		jQuery(this).closest('li').data('completed', !jQuery(this).closest('li').data('completed')).addBack().toggleClass('completed');
		todo_update();
	}).on('dblclick', '.todo-item-text', function(){
		jQuery('#todo-list').sortable('disable');
		jQuery(this).prop('contenteditable',true).focus().addClass('editing');
	}).on('blur', '#todo-list .editing', function(){
		jQuery('#todo-list').sortable('enable');
		jQuery(this).prop('contenteditable',false).removeClass('editing');
		todo_update();
	})

	jQuery(document).on('click', '#todo-list .fa.fa-times-circle', function(){
		var that = jQuery(this);
		if(confirm('Delete item? This action cannot be undone.\nHold onto your butts...')){
			that.closest('li').animate({opacity:0},500,function(){
				that.closest('li').animate({
					height:0,
					padding:0,
					border:0
				},250, function(){
					that.closest('li').remove();
					todo_update();
				});
			});
		}
	})

	jQuery(document).on('click', '#todo-widget .fa.fa-plus-circle', function(){
		jQuery('<p class="cp"><input type="number" min="1" name="todo_age_number[]" value="1" />' +
			'<select name="todo_age_period[]"><option>minutes</option><option>hours</option><option>days</option><option>weeks</option>' +
			'<option>months</option></select><input type="color" class="color-picker" name="todo_age_color[]" value="#e4f3e6" /> <i class="fa fa-minus-circle fa-lg"></i><i class="fa fa-bars fa-lg"></i></p>'
		).appendTo('#color_wrapper');
		jQuery('#color_wrapper').sortable('refresh');
	})

	jQuery(document).on('click', '#todo-widget p.cp .fa.fa-minus-circle', function(){
		if(confirm('Delete item? This action cannot be undone.\nHold onto your butts...')){
			jQuery(this).closest('p').remove();
		}
	})
});

/**
* Update database after sorting, marking an item completed, or deleting an item
*/
function todo_update(){
	var items = [];
	jQuery('#todo-list li').each(function(){
		var todo_item = {};
		todo_item.text = jQuery(this).find('span.todo-item-text').html();
		todo_item.timestamp = jQuery(this).data('timestamp');
		todo_item.completed = jQuery(this).data('completed');
		items.push(todo_item);
	})
	var data = {
		action: 'todo_update',
		post_var: items
	};
	jQuery.post( todo_object.ajax_url, data);
	check_limit(items.length)
}

/**
* Enable/disable the text field based on the item limit set (0=unlimited)
*/
function check_limit(items){
	if( items < todo_options['todo_item_limit'] || todo_options['todo_item_limit'] == 0 ) {
		jQuery('#todo_add_item').prop({'disabled':false,'readonly':false,'placeholder':''});
	} else {
		jQuery('#todo_add_item').prop({'disabled':true,'readonly':true,'placeholder':'To-do limit reached. Delete item(s) to enable.'});
	}
}
