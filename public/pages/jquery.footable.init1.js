/**
 * Theme: Frogetor - Responsive Bootstrap 4 Admin Dashboard
 * Author: Mannatthemes
 * Footable Js
 */


$(function () {
	"use strict";

	/*Init FooTable*/
	$('#footable-1,#footable-2').footable();

	/*Editing FooTable*/
	var $modal = $('#editor-modal'),
		$editor = $('#editor'),
		$editorTitle = $('#editor-title'),
		ft = FooTable.init('#footable-3', {
			editing: {
				enabled: true,
				/*addRow: function(){
					$modal.removeData('row');
					$editor[0].reset();
					$editorTitle.text('Add a new row');
					$modal.modal('show');
				},*/
				editRow: function (row) {
					var values = row.val();
					$editor.find('#sku').val(values.sku);
					$editor.find('#name').val(values.name);
					$editor.find('#price').val(values.price);
					$editor.find('#qty').val(values.qty);
					$editor.find('#prod').val(values.prod);


					$modal.data('row', row);
					$editorTitle.text('Modifier la quantité en stock #' + values.sku);
					$modal.modal('show');
				},
				/*deleteRow: function(row){
					if (confirm('Are you sure you want to delete the row?')){
						row.delete();
					}
				}*/
			}
		}),
		uid = 10;

	// $editor.on('submit', function (e) {
	// 	if (this.checkValidity && !this.checkValidity()) return;
	// 	e.preventDefault();
	// 	var row = $modal.data('row'),
	// 		values = {
	// 			id: $editor.find('#id').val(),
	// 			firstName: $editor.find('#firstName').val(),
	// 			lastName: $editor.find('#lastName').val(),
	// 			jobTitle: $editor.find('#jobTitle').val(),
	// 			startedOn: moment($editor.find('#startedOn').val(), 'YYYY-MM-DD'),
	// 			dob: moment($editor.find('#dob').val(), 'YYYY-MM-DD')
	// 		};

	// 	if (row instanceof FooTable.Row) {
	// 		row.val(values);
	// 	} else {
	// 		values.id = uid++;
	// 		ft.rows.add(values);
	// 	};
	// 	$modal.modal('hide');
	// });
});