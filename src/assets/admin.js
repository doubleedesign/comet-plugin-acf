/**
 * global acf
 */

jQuery(document).ready(function($) {
	/**
	 * Limit the block formats available on a per-module basis
	 * to control heading hierarchy as much as possible
	 */
	acf.addFilter('wysiwyg_tinymce_settings', function (mceInit, id, field) {
		// We assume fields with basic WYSIWYGs are inside modules with heading fields that would be output as H2s
		if(field.data.toolbar === 'basic') {
			mceInit.block_formats = 'Paragraph=p;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6';
		}

		return mceInit;
	});
});
