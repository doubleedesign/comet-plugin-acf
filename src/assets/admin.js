/* global acf */

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


	/**
	 * Add focal point picker to image module on load
	 * and initialise the aspect ratio handler
	 */
	acf.addAction('load_field/type=image', function(field) {
		const moduleArea = field.$el.closest('.layout')[0];
		if(moduleArea && moduleArea.dataset.layout === 'image') {
			new ImagePreviewEnhancer(moduleArea).init();
		}
	});

	/**
	 * Add focal point picker to image module when a new one is added
	 * and initialise the aspect ratio handler
	 */
	acf.addAction('append', function(maybeModule) {
		if(maybeModule[0].dataset.layout === 'image') {
			new ImagePreviewEnhancer(maybeModule[0]).init();
		}
	});
});

class ImagePreviewEnhancer {
	constructor(moduleElement) {
		this.container = moduleElement;
	}
	init() {
		// Get the image preview field
		this.preview = this.container.querySelector('.image-wrap');
		// Add the focal point indicator element
		const indicator = document.createElement('div');
		indicator.className = 'focal-point-indicator';
		this.preview.appendChild(indicator);
		this.indicator = indicator;
		// Get the focal point input fields
		this.xField = this.container.querySelector('[data-key="field__image__focal-point__x"] input');
		this.yField = this.container.querySelector('[data-key="field__image__focal-point__y"] input');
		// Get the aspect ratio field and set up the handler
		this.aspectRatioField = this.container.querySelector('[data-name="aspect_ratio"] select');

		// Set the initial values
		const initialX = this.xField.value ? parseFloat(this.xField.value) : 50;
		const initialY = this.yField.value ? parseFloat(this.yField.value) : 50;
		this.setX(initialX);
		this.setY(initialY);
		this.setIndicatorPosition(initialX, initialY);
		const initialAspectRatio = this.aspectRatioField.value || '4:3';
		this.handleAspectRatioChange({ target: { value: initialAspectRatio } }, (value) => {
			this.setAspectRatio(value);
		});

		// Add click event listener to the image preview
		this.preview.addEventListener('click', (event) => {
			this.handlePreviewClick(event, (xValue, yValue) => {
				this.setX(xValue);
				this.setY(yValue);
				this.setIndicatorPosition(xValue, yValue);
				this.xField.value = xValue;
				this.yField.value = yValue;
			});
		});

		// Add event listeners to the input fields
		this.xField.addEventListener('change', (event) => {
			this.handleFocalPointInputChange(event, (xValue) => this.setX(xValue));
		});
		this.yField.addEventListener('change', (event) => {
			this.handleFocalPointInputChange(event, (yValue) => this.setY(yValue));
		});

		// Add event listener to the aspect ratio field
		this.aspectRatioField.addEventListener('change', (event) => {
			this.handleAspectRatioChange(event, (value) => {
				this.setAspectRatio(value);
			});
		});
	}
	setX(x) {
		this.x = x;
		this.preview.style.setProperty('--focal-point-x', `${x}%`);
	}
	setY(y) {
		this.y = y;
		this.preview.style.setProperty('--focal-point-y', `${y}%`);
	}
	setAspectRatio(ratio) {
		this.aspectRatio = ratio;
		this.preview.style.setProperty('--aspect-ratio', ratio.replace(':', '/'));
	}
	handleFocalPointInputChange(event, callback) {
		let value = parseFloat(event.target.value);
		if(isNaN(value) || value < 0 || value > 100) {
			value = 50; // Reset to default if invalid
		}
		callback(value);
	}
	handleAspectRatioChange(event, callback) {
		const value = event.target.value;
		callback(value);
	}
	handlePreviewClick(event, callback) {
		const rect = this.preview.getBoundingClientRect();
		const x = ((event.clientX - rect.left) / rect.width) * 100;
		const y = ((event.clientY - rect.top) / rect.height) * 100;
		callback(Math.round(x), Math.round(y));
	}
	setIndicatorPosition(x, y) {
		this.indicator.style.left = `calc(${x}% - 1rem)`;
		this.indicator.style.top = `calc(${y}% - 1rem)`;
	}
}
