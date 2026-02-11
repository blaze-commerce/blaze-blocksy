/**
 * Admin Product Custom Tabs
 *
 * Handles the repeater field functionality for custom product tabs:
 * - Add new tabs
 * - Delete tabs
 * - Drag and drop reordering
 * - WYSIWYG editor initialization
 *
 * @package BlazeBlocksy
 * @since 1.65.0
 */

(function ($) {
	'use strict';

	var BlazeProductCustomTabs = {
		/**
		 * Track tab index counter
		 */
		tabCounter: 0,

		/**
		 * Track initialized editors
		 */
		editors: {},

		/**
		 * Initialize
		 */
		init: function () {
			this.cacheElements();
			this.bindEvents();
			this.initSortable();
			this.initExistingEditors();
			this.updateTabNumbers();
		},

		/**
		 * Cache DOM elements
		 */
		cacheElements: function () {
			this.$wrapper = $('.blaze-custom-tabs-wrapper');
			this.$container = $('#blaze-custom-tabs-container');
			this.$addButton = $('#blaze-add-custom-tab');
			this.$template = $('#blaze-tab-template');
		},

		/**
		 * Bind event handlers
		 */
		bindEvents: function () {
			var self = this;

			// Add new tab
			this.$addButton.on('click', function (e) {
				e.preventDefault();
				self.addNewTab();
			});

			// Delete tab
			this.$container.on('click', '.blaze-tab-delete', function (e) {
				e.preventDefault();
				self.deleteTab($(this).closest('.blaze-custom-tab-item'));
			});

			// Toggle tab content
			this.$container.on('click', '.blaze-tab-toggle', function (e) {
				e.preventDefault();
				self.toggleTab($(this).closest('.blaze-custom-tab-item'));
			});
		},

		/**
		 * Initialize sortable for drag and drop
		 */
		initSortable: function () {
			var self = this;

			this.$container.sortable({
				handle: '.blaze-tab-drag-handle',
				items: '.blaze-custom-tab-item',
				axis: 'y',
				cursor: 'grabbing',
				placeholder: 'blaze-tab-sortable-placeholder',
				tolerance: 'pointer',
				start: function (event, ui) {
					// Store editor content before sorting
					var $item = ui.item;
					var $textarea = $item.find('.blaze-tab-content-textarea');
					var editorId = $textarea.attr('id');

					if (editorId && typeof tinymce !== 'undefined') {
						var editor = tinymce.get(editorId);
						if (editor) {
							// Save content and remove editor
							$textarea.val(editor.getContent());
							wp.editor.remove(editorId);
							delete self.editors[editorId];
						}
					}

					// Add dragging class
					$item.addClass('is-dragging');

					// Set placeholder height
					ui.placeholder.height(ui.item.outerHeight());
				},
				stop: function (event, ui) {
					var $item = ui.item;
					$item.removeClass('is-dragging');

					// Re-initialize editor after sorting
					var $textarea = $item.find('.blaze-tab-content-textarea');
					var editorId = $textarea.attr('id');

					if (editorId) {
						self.initEditor(editorId);
					}

					// Update field names and numbers
					self.reindexTabs();
				},
			});
		},

		/**
		 * Initialize existing WYSIWYG editors
		 */
		initExistingEditors: function () {
			var self = this;

			this.$container.find('.blaze-tab-content-textarea').each(function () {
				var editorId = $(this).attr('id');
				if (editorId && editorId.indexOf('{{INDEX}}') === -1) {
					self.initEditor(editorId);
				}
			});

			// Count existing tabs for counter
			this.tabCounter = this.$container.find('.blaze-custom-tab-item').length;
		},

		/**
		 * Initialize a single WYSIWYG editor
		 *
		 * @param {string} editorId Editor element ID
		 */
		initEditor: function (editorId) {
			var self = this;

			// Check if editor already exists
			if (this.editors[editorId]) {
				return;
			}

			// Initialize WordPress editor
			if (typeof wp !== 'undefined' && wp.editor) {
				wp.editor.initialize(editorId, {
					tinymce: {
						wpautop: true,
						plugins:
							'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
						toolbar1:
							'formatselect bold italic underline strikethrough | bullist numlist | blockquote | alignleft aligncenter alignright | link unlink | wp_adv',
						toolbar2:
							'pastetext removeformat charmap outdent indent | undo redo | wp_help',
						height: 200,
						setup: function (editor) {
							editor.on('change', function () {
								editor.save();
							});
						},
					},
					quicktags: true,
					mediaButtons: true,
				});

				this.editors[editorId] = true;
			}
		},

		/**
		 * Remove a WYSIWYG editor
		 *
		 * @param {string} editorId Editor element ID
		 */
		removeEditor: function (editorId) {
			if (typeof wp !== 'undefined' && wp.editor) {
				wp.editor.remove(editorId);
			}
			delete this.editors[editorId];
		},

		/**
		 * Add a new tab
		 */
		addNewTab: function () {
			var self = this;
			var templateHtml = this.$template.html();
			var newIndex = this.tabCounter++;

			// Replace placeholder with actual index
			var newTabHtml = templateHtml.replace(/\{\{INDEX\}\}/g, newIndex);
			var $newTab = $(newTabHtml);

			// Append to container
			this.$container.append($newTab);

			// Initialize editor for new tab
			var editorId = 'blaze_tab_content_' + newIndex;
			setTimeout(function () {
				self.initEditor(editorId);
			}, 100);

			// Update tab numbers
			this.updateTabNumbers();

			// Scroll to new tab
			$('html, body').animate(
				{
					scrollTop: $newTab.offset().top - 100,
				},
				300
			);

			// Focus on title input
			$newTab.find('.blaze-tab-title-input').focus();
		},

		/**
		 * Delete a tab
		 *
		 * @param {jQuery} $tab Tab element to delete
		 */
		deleteTab: function ($tab) {
			var self = this;

			if (!confirm(blazeProductTabs.i18n.confirmDelete)) {
				return;
			}

			// Remove editor first
			var editorId = $tab.find('.blaze-tab-content-textarea').attr('id');
			if (editorId) {
				this.removeEditor(editorId);
			}

			// Animate removal
			$tab.slideUp(300, function () {
				$(this).remove();
				self.reindexTabs();
				self.updateTabNumbers();
			});
		},

		/**
		 * Toggle tab content visibility
		 *
		 * @param {jQuery} $tab Tab element to toggle
		 */
		toggleTab: function ($tab) {
			var $contentWrapper = $tab.find('.blaze-tab-content-wrapper');
			var $toggleBtn = $tab.find('.blaze-tab-toggle .dashicons');

			$contentWrapper.slideToggle(200);
			$tab.toggleClass('is-collapsed');

			if ($tab.hasClass('is-collapsed')) {
				$toggleBtn
					.removeClass('dashicons-arrow-down-alt2')
					.addClass('dashicons-arrow-up-alt2');
			} else {
				$toggleBtn
					.removeClass('dashicons-arrow-up-alt2')
					.addClass('dashicons-arrow-down-alt2');
			}
		},

		/**
		 * Reindex tabs after sorting or deletion
		 */
		reindexTabs: function () {
			var self = this;

			this.$container.find('.blaze-custom-tab-item').each(function (index) {
				var $tab = $(this);
				var oldIndex = $tab.data('index');

				// Update data attribute
				$tab.attr('data-index', index);
				$tab.data('index', index);

				// Update field names
				$tab.find('input, textarea').each(function () {
					var $field = $(this);
					var name = $field.attr('name');
					var id = $field.attr('id');

					if (name) {
						$field.attr(
							'name',
							name.replace(
								/blaze_custom_tabs\[\d+\]/,
								'blaze_custom_tabs[' + index + ']'
							)
						);
					}

					if (id) {
						var newId = id.replace(/blaze_tab_content_\d+/, 'blaze_tab_content_' + index);
						if (newId !== id) {
							// Remove old editor
							self.removeEditor(id);

							// Update ID
							$field.attr('id', newId);

							// Re-initialize editor with new ID
							setTimeout(function () {
								self.initEditor(newId);
							}, 100);
						}
					}
				});
			});

			this.updateTabNumbers();
		},

		/**
		 * Update tab numbers display
		 */
		updateTabNumbers: function () {
			this.$container.find('.blaze-custom-tab-item').each(function (index) {
				$(this)
					.find('.blaze-tab-number')
					.text(index + 1);
			});
		},
	};

	// Initialize on document ready
	$(document).ready(function () {
		if ($('.blaze-custom-tabs-wrapper').length) {
			BlazeProductCustomTabs.init();
		}
	});
})(jQuery);
