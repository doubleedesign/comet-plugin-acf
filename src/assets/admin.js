/* global acf */

jQuery(document).ready(function ($) {
	/**
	 * Change "remove row" tooltip on column repeaters to "delete column" because ACF doesn't provide a better way to do this
	 * and remove the "add row" and "duplicate row" buttons while we're at it
	 */
	acf.addAction('load_field/name=column_content', function (field) {
		field.$el.closest('.acf-row')?.find('[data-event="remove-row"]').attr('title', 'Delete column');
		field.$el.closest('.acf-row')?.find('[data-event="add-row"]').remove();
		field.$el.closest('.acf-row')?.find('[data-event="duplicate-row"]').remove();
	});
	acf.addAction('append', function (field) {
		if (field.name === 'column_content') {
			field.$el.closest('.acf-row')?.find('[data-event="remove-row"]').attr('title', 'Delete column');
			field.$el.closest('.acf-row')?.find('[data-event="add-row"]').remove();
			field.$el.closest('.acf-row')?.find('[data-event="duplicate-row"]').remove();
		}
	});
});
