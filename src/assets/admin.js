/* global acf */

jQuery(document).ready(function ($) {
	/**
	 * Limit the block formats available on a per-module basis
	 * to control heading hierarchy as much as possible
	 */
	acf.addFilter('wysiwyg_tinymce_settings', function (mceInit, id, field) {
		// We assume fields with basic WYSIWYGs are inside modules with heading fields that would be output as H2s, so we start at H3
		if (field.data.toolbar === 'basic') {
			mceInit.block_formats = 'Paragraph=p;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6';
		}
		if (field.data.toolbar === 'full') {
			mceInit.block_formats = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6';
		}

		return mceInit;
	});

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
		if(field.name === 'column_content') {
			field.$el.closest('.acf-row')?.find('[data-event="remove-row"]').attr('title', 'Delete column');
			field.$el.closest('.acf-row')?.find('[data-event="add-row"]').remove();
			field.$el.closest('.acf-row')?.find('[data-event="duplicate-row"]').remove();
		}
	});
});
