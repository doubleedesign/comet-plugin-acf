/* global acf */

jQuery(document).ready(function ($) {
	/**
	 * Limit the block formats available on a per-module basis
	 * to control heading hierarchy as much as possible
	 */
	acf.addFilter('wysiwyg_tinymce_settings', function (mceInit, id, field) {
		// We assume fields with basic WYSIWYGs are inside modules with heading fields that would be output as H2s
		if (field.data.toolbar === 'basic') {
			mceInit.block_formats = 'Paragraph=p;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6';
		}

		return mceInit;
	});


	/**
	 * Add focal point picker to image module on load
	 * and initialise the aspect ratio handler
	 */
	acf.addAction('load_field/type=image', function (field) {
		const moduleArea = field.$el.closest('.layout')[0];
		if (moduleArea && moduleArea.dataset.layout === 'image') {
			new ImagePreviewEnhancer(moduleArea).init();
		}
	});

	/**
	 * Add focal point picker to image module when a new one is added
	 * and initialise the aspect ratio handler
	 */
	acf.addAction('append', function (maybeModule) {
		if (maybeModule[0].dataset.layout === 'image') {
			new ImagePreviewEnhancer(maybeModule[0]).init();
		}
	});
});

class ImagePreviewEnhancer {
	constructor(moduleElement) {
		this.module = moduleElement;
		this.container = this.module.querySelector('.acf-image-uploader');
		this.preview = this.module.querySelector('.image-wrap');
	}

	init() {
		// Add the focal point indicator element
		const indicator = document.createElement('div');
		indicator.className = 'focal-point-indicator';
		this.preview.appendChild(indicator);
		this.indicator = indicator;
		// Get the input fields
		this.xField = this.module.querySelector('[data-key="field__image__options__focal-point__x"] input');
		this.yField = this.module.querySelector('[data-key="field__image__options__focal-point__y"] input');
		this.offsetXfield = this.module.querySelector('[data-key="field__image__options__offset__x"] input');
		this.offsetYfield = this.module.querySelector('[data-key="field__image__options__offset__y"] input');
		this.aspectRatioField = this.module.querySelector('[data-name="aspect_ratio"] select');
		// Set the initial values
		this.setX(this.xField.value ? parseFloat(this.xField.value) : 50);
		this.setY(this.yField.value ? parseFloat(this.yField.value) : 50);
		this.setAspectRatio(this.aspectRatioField.value || '4:3');
		this.setOffsets(
			this.offsetXfield.value ? parseFloat(this.offsetXfield.value) : 0,
			this.offsetYfield.value ? parseFloat(this.offsetYfield.value) : 0
		);

		// Add event listeners
		// Note: The offset fields are readonly so should not have input change event handlers
		this.preview.addEventListener('click', this.handlePreviewClick.bind(this));
		this.xField.addEventListener('change', (event) => this.setX(event.target.value));
		this.yField.addEventListener('change', (event) => this.setY(event.target.value));
		this.aspectRatioField.addEventListener('change', (event) => this.setAspectRatio(event.target.value));

		// Add a resize observer to handle container size changes
		// This responds to both aspect ratio setting changes and viewport resize events
		this.resizeObserver = new ResizeObserver((entries) => this.handlePreviewSizeChange(entries[0]));
		this.resizeObserver.observe(this.container);
	}

	setX(x) {
		this.x = x;
		this.xField.value = x;
		this.container.style.setProperty('--focal-point-x', x);
	}

	setY(y) {
		this.y = y;
		this.yField.value = y;
		this.container.style.setProperty('--focal-point-y', y);
	}

	setAspectRatio(ratio) {
		this.aspectRatio = ratio;
		this.container.style.setProperty('--aspect-ratio', ratio.replace(':', '/'));
	}

	setOffsets(x, y) {
		this.offsetXfield.value = x;
		this.offsetYfield.value = y;
		this.container.style.setProperty('--image-offset-x', `${x}%`);
		this.container.style.setProperty('--image-offset-y', `${y}%`);
	}

	handlePreviewClick(event) {
		const rect = this.preview.getBoundingClientRect();
		const x = ((event.clientX - rect.left) / rect.width) * 100;
		const y = ((event.clientY - rect.top) / rect.height) * 100;
		this.setX(Math.round(x));
		this.setY(Math.round(y));
		this.repositionImage();
	}

	handlePreviewSizeChange(containerResizeObserverEntry) {
		if (this.timeoutId) {
			clearTimeout(this.timeoutId);
		}

		this.timeoutId = setTimeout(() => {
			this.resizeImage(containerResizeObserverEntry.contentRect.height, containerResizeObserverEntry.contentRect.width);
			this.repositionImage();
		}, 200);
	}

	resizeImage(containerHeight, containerWidth) {
		/**
		 * Use the dimensions of the preview area to adjust the image size.
		 * This, combined with the CSS defined for these elements in the main admin stylesheet,
		 * enables the image to visually fit the space without actually being cropped,
		 * which enables movement of the image within the space to show focal-point-based cropping previews on-the-fly.
		 */
		const img = this.preview.querySelector('img');
		const isPortraitImage = img.naturalHeight > img.naturalWidth;
		const isPortraitContainer = containerHeight > containerWidth;

		// In a portrait container, take up available vertical space and crop horizontally
		if (isPortraitContainer) {
			img.style.minWidth = '100%';
			img.style.width = 'auto'; // prevent distortion
			img.style.height = `${containerHeight}px`;
		}
		// Square container
		else if (containerHeight === containerWidth) {
			// note: object-fit:cover makes the focal point appear in the wrong spot
			if (isPortraitImage) {
				img.style.width = `${containerWidth}px`;
				img.style.height = 'auto';
				img.style.maxHeight = 'none';
			} else {
				img.style.width = 'auto';
				img.style.height = `${containerHeight}px`;
			}
		}
		// Landscape container
		else {
			// Portrait-orientation image
			if (isPortraitImage) {
				// take up available horizontal space and crop vertically
				img.style.width = `${containerWidth}px`;
				img.style.height = 'auto';
			}
			// Landscape or square image
			else {
				img.style.minHeight = '100%';
				img.style.width = '100%';
				img.style.height = 'auto'; // prevent distortion
			}
		}
	}

	repositionImage() {
		// The focal point is relative to the image wrapper, which may be overflowing the container (this is intentional)
		const containerData = this.container.getBoundingClientRect();
		const indicatorData = this.indicator.getBoundingClientRect();
		const previewData = this.preview.getBoundingClientRect();

		// Get position of indicator relative to container in pixels
		const rawRelativePosition = {
			x: indicatorData.left + (indicatorData.width / 2) - containerData.left,
			y: indicatorData.top + (indicatorData.height / 2) - containerData.top
		};
		// Transform to a percentage of the container dimensions
		const relativePositionOfIndicator = {
			x: (rawRelativePosition.x / containerData.width) * 100,
			y: (rawRelativePosition.y / containerData.height) * 100
		};
		// Get current position of the preview box relative to the container
		const currentX = previewData.left - containerData.left;
		const currentY = previewData.top - containerData.top;


		// Move the preview element so that the focal point is shown as close as possible to 50/50 without showing empty space
		const {x, y} = relativePositionOfIndicator;

		// Get dimensions
		const containerWidth = containerData.width;
		const containerHeight = containerData.height;
		const previewWidth = previewData.width;
		const previewHeight = previewData.height;

		// Calculate how much we need to move the preview to center the indicator
		const targetX = 50;
		const targetY = 50;
		const moveXPixels = (targetX - x) * containerWidth / 100;
		const moveYPixels = (targetY - y) * containerHeight / 100;

		// Calculate new centered position
		const newX = currentX + moveXPixels;
		const newY = currentY + moveYPixels;

		// Calculate movement boundaries to prevent empty space
		const maxMoveRight = 0; // preview's left edge can't go past container's left edge
		const maxMoveLeft = containerWidth - previewWidth; // preview's right edge can't go past container's right edge
		const maxMoveDown = 0; // preview's top edge can't go past container's top edge
		const maxMoveUp = containerHeight - previewHeight; // preview's bottom edge can't go past container's bottom edge

		// Clamp to boundaries
		const clampedX = Math.max(maxMoveLeft, Math.min(maxMoveRight, newX));
		const clampedY = Math.max(maxMoveUp, Math.min(maxMoveDown, newY));

		// Convert to percentage relative to container size
		const clampedXPercent = Math.round((clampedX / containerWidth) * 100);
		const clampedYPercent = Math.round((clampedY / containerHeight) * 100);

		// Set the values that will update the image position in CSS
		this.setOffsets(clampedXPercent, clampedYPercent);
	}
}
