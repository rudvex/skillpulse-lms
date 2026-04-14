/**
 * Dashboard JavaScript
 *
 * Handles dashboard interactions using jQuery
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

// Import tab-specific modules
import './notifications';

(function($) {
	'use strict';

	const Dashboard = {

	init: function() {
		this.toggleSidebar();
		this.handleTabs();
		this.handleMobileMenu();
		this.handleSettingsForms();
		this.handleWishlistRemoval();
		this.handleBookmarkRemoval();
		// Initialize tab-specific modules
		if (window.SPLMSDashboardCourses) {
			window.SPLMSDashboardCourses.init();
		}
		if (window.SPLMSDashboardCertificates) {
			window.SPLMSDashboardCertificates.init();
		}
		if (window.SPLMSDashboardOverview) {
			window.SPLMSDashboardOverview.init();
		}
		if (window.SPLMSDashboardNotifications) {
			window.SPLMSDashboardNotifications.init();
		}
		if (window.SPLMSDashboardOrders) {
			window.SPLMSDashboardOrders.init();
		}
		if (window.SPLMSDashboardProgress) {
			window.SPLMSDashboardProgress.init();
		}
	},

	toggleSidebar: function() {
		$('.splms-dashboard-container').on('click', '.splms-dashboard-sidebar-toggle', function(e) {
			e.preventDefault();
			e.stopPropagation();
			
			// Different behavior for mobile vs desktop
			if ($(window).width() <= 768) {
				// Mobile: toggle overlay sidebar
				$('.splms-dashboard-sidebar').toggleClass('is-open');
				$('body').toggleClass('sidebar-open');
			} else {
				// Desktop: toggle collapsed state
				$('.splms-dashboard-sidebar').toggleClass('is-collapsed');
				$('.splms-dashboard-sidebar').removeClass('is-open'); // Remove mobile state if it exists
			}
		});
	},

		handleTabs: function() {
			$('.splms-dashboard-container').on('click', '.splms-dashboard-nav-item', function(e) {
				e.preventDefault();

			// Instructor modal removed
				
			const $this = $(this);
				const tab = $this.data('tab');
				
				if (!tab) {
					return;
				}

				// Update active state
				$('.splms-dashboard-nav-item').removeClass('is-active');
				$this.addClass('is-active');

				// Reload page with tab parameter
				const url = new URL(window.location);
				url.searchParams.set('tab', tab);
				window.location.href = url.toString();
			});
		},

	handleMobileMenu: function() {
		// Close sidebar when clicking overlay
		$('.splms-dashboard-overlay').on('click', function() {
			$('.splms-dashboard-sidebar').removeClass('is-open');
			$('body').removeClass('sidebar-open');
		});

		// Close sidebar when clicking nav item on mobile
		$('.splms-dashboard-container').on('click', '.splms-dashboard-nav-item', function() {
			if ($(window).width() <= 768) {
				setTimeout(function() {
					$('.splms-dashboard-sidebar').removeClass('is-open');
					$('body').removeClass('sidebar-open');
				}, 300);
			}
		});

		// Close sidebar on escape key
		$(document).on('keydown', function(e) {
			if (e.key === 'Escape' && $('.splms-dashboard-sidebar').hasClass('is-open')) {
				$('.splms-dashboard-sidebar').removeClass('is-open');
				$('body').removeClass('sidebar-open');
			}
		});
	},

	handleSettingsForms: function() {
		const self = this;

		// Profile form handler
		$('.splms-dashboard-container').on('submit', '#splms-profile-form', function(e) {
			e.preventDefault();
			const $form = $(this);
			const $button = $form.find('button[type="submit"]');
			const originalText = $button.text();

			$button.prop('disabled', true).text(SPLMSDashboard.strings.saving || 'Saving...');

			const dashboard = window.splms_dashboard;
			$.ajax({
				url: dashboard.ajax_url,
				type: 'POST',
				data: {
					action: 'splms_update_profile',
					nonce: dashboard.nonce,
					display_name: $form.find('[name="display_name"]').val(),
					user_email: $form.find('[name="email"]').val(),
					first_name: $form.find('[name="first_name"]').val(),
					last_name: $form.find('[name="last_name"]').val(),
					description: $form.find('[name="bio"]').val(),
					user_url: '',
					billing_address_1: $form.find('[name="billing_address_1"]').val(),
					billing_address_2: $form.find('[name="billing_address_2"]').val(),
					billing_city: $form.find('[name="billing_city"]').val(),
					billing_state: $form.find('[name="billing_state"]').val(),
					billing_postcode: $form.find('[name="billing_postcode"]').val(),
					billing_country: $form.find('[name="billing_country"]').val(),
					billing_phone: $form.find('[name="billing_phone"]').val(),
					billing_company: $form.find('[name="billing_company"]').val()
				},
				success: function(response) {
					if (response.success) {
						const message = typeof response.data === 'string' 
							? response.data 
							: (response.data.message || SPLMSDashboard.strings.success || 'Profile updated successfully!');
						
						window.SPLMSCore.helper.showNotification(message, 'success');
						
						// Reload after success to show updated values
						setTimeout(function() {
							location.reload();
						}, 1500);
					} else {
						const errorMsg = typeof response.data === 'string' 
							? response.data 
							: (response.data.message || SPLMSDashboard.strings.error || 'Error updating profile.');
						
						window.SPLMSCore.helper.showNotification(errorMsg, 'error');
					}
				},
				error: function(xhr, status, error) {
					window.console?.error('Profile update error:', { xhr, status, error });
					window.SPLMSCore.helper.showNotification(SPLMSDashboard.strings.error || 'Network error. Please try again.', 'error');
				},
				complete: function() {
					$button.prop('disabled', false).text(originalText);
				}
			});
		});

		// Password form handler
		$('.splms-dashboard-container').on('submit', '#splms-password-form', function(e) {
			e.preventDefault();
			const $form = $(this);
			const $button = $form.find('button[type="submit"]');
			const originalText = $button.text();
			const newPassword = $form.find('[name="new_password"]').val();
			const confirmPassword = $form.find('[name="confirm_password"]').val();

			// Validate password match
			if (newPassword !== confirmPassword) {
				window.SPLMSCore.helper.showNotification(SPLMSDashboard.strings.passwords_mismatch || 'Passwords do not match.', 'error');
				return;
			}

			// Client-side password strength validation
			const passwordErrors = [];
			
			if (newPassword.length < 8) {
				passwordErrors.push('at least 8 characters long');
			}
			
			if (!/[a-z]/.test(newPassword)) {
				passwordErrors.push('one lowercase letter');
			}
			
			if (!/[A-Z]/.test(newPassword)) {
				passwordErrors.push('one uppercase letter');
			}
			
			if (!/[0-9]/.test(newPassword)) {
				passwordErrors.push('one number');
			}
			
			if (!/[^a-zA-Z0-9]/.test(newPassword)) {
				passwordErrors.push('one special character');
			}

			if (passwordErrors.length > 0) {
				const errorMsg = 'Password must contain: ' + passwordErrors.join(', ') + '.';
				window.SPLMSCore.helper.showNotification(errorMsg, 'error');
				return;
			}

			const dashboard = window.splms_dashboard;
			$button.prop('disabled', true).text(SPLMSDashboard.strings.changing || 'Changing...');

			$.ajax({
				url: dashboard.ajax_url,
				type: 'POST',
				data: {
					action: 'splms_change_password',
					nonce: dashboard.nonce,
					current_password: $form.find('[name="current_password"]').val(),
					new_password: $form.find('[name="new_password"]').val()
				},
				success: function(response) {
					if (response.success) {
						const message = typeof response.data === 'string'
							? response.data
							: (response.data.message || SPLMSDashboard.strings.success || 'Password changed successfully!');
						
						window.SPLMSCore.helper.showNotification(message, 'success');
						$form[0].reset();
					} else {
						const errorMsg = typeof response.data === 'string'
							? response.data
							: (response.data.message || SPLMSDashboard.strings.error || 'Error changing password.');
						
						window.SPLMSCore.helper.showNotification(errorMsg, 'error');
					}
				},
				error: function(xhr, status, error) {
					window.console?.error('Password change error:', { xhr, status, error });
					window.SPLMSCore.helper.showNotification(SPLMSDashboard.strings.error || 'Network error. Please try again.', 'error');
				},
				complete: function() {
					$button.prop('disabled', false).text(originalText);
				}
			});
		});

		// Notification preferences form handler
		$('.splms-dashboard-container').on('submit', '#splms-notification-preferences-form', function(e) {
			e.preventDefault();
			const $form = $(this);
			const $button = $form.find('button[type="submit"]');
			const originalText = $button.text();

			$button.prop('disabled', true).text(SPLMSDashboard.strings.saving || 'Saving...');

			// Collect form data
			const dashboard = window.splms_dashboard;
			const formData = {
				action: 'splms_save_notification_preferences',
				nonce: dashboard.nonce,
				email_enabled: $form.find('#all_email_notifications').is(':checked') ? '1' : '0',
				in_app_enabled: $form.find('#all_in_app_notifications').is(':checked') ? '1' : '0',
				events: {}
			};

			// Collect event preferences
			$form.find('[name^="events["]').each(function() {
				const $input = $(this);
				const name = $input.attr('name');
				const match = name.match(/events\[([^\]]+)\]\[([^\]]+)\]/);

				if (match) {
					const eventKey = match[1];
					const method = match[2];

					if (!formData.events[eventKey]) {
						formData.events[eventKey] = {};
					}

					formData.events[eventKey][method] = $input.is(':checked') ? '1' : '0';
				}
			});

			$.ajax({
				url: dashboard.ajax_url,
				type: 'POST',
				data: formData,
				success: function(response) {
					if (response.success) {
						const message = typeof response.data === 'string'
							? response.data
							: (response.data.message || SPLMSDashboard.strings.success || 'Notification preferences saved successfully!');

						window.SPLMSCore.helper.showNotification(message, 'success');
					} else {
						const errorMsg = typeof response.data === 'string'
							? response.data
							: (response.data.message || SPLMSDashboard.strings.error || 'Error saving notification preferences.');

						window.SPLMSCore.helper.showNotification(errorMsg, 'error');
					}
				},
				error: function(xhr, status, error) {
					window.console?.error('Notification preferences save error:', { xhr, status, error });
					window.SPLMSCore.helper.showNotification(SPLMSDashboard.strings.error || 'Network error. Please try again.', 'error');
				},
				complete: function() {
					$button.prop('disabled', false).text(originalText);
				}
			});
		});

		// Smart notification disable/enable logic
		this.handleSmartNotificationToggle();
	},

	handleSmartNotificationToggle: function() {
		const self = this;

		// Function to update master toggle states based on individual checkboxes
		function updateMasterToggles() {
			const $container = $('.splms-dashboard-container');

			// Update All Email master toggle
			const emailCheckboxes = $container.find('input[name*="[email]"]');
			const allEmailMaster = $container.find('#all_email_notifications');
			if (emailCheckboxes.length > 0 && allEmailMaster.length > 0) {
				const checkedEmailCount = emailCheckboxes.filter(':checked').length;
				const isAllEmailChecked = checkedEmailCount === emailCheckboxes.length;
				const isPartialEmailChecked = checkedEmailCount > 0 && checkedEmailCount < emailCheckboxes.length;

				allEmailMaster.prop('checked', isAllEmailChecked);
				allEmailMaster.prop('indeterminate', isPartialEmailChecked);
			}

			// Update All In-App master toggle
			const inAppCheckboxes = $container.find('input[name*="[in_app]"]');
			const allInAppMaster = $container.find('#all_in_app_notifications');
			if (inAppCheckboxes.length > 0 && allInAppMaster.length > 0) {
				const checkedInAppCount = inAppCheckboxes.filter(':checked').length;
				const isAllInAppChecked = checkedInAppCount === inAppCheckboxes.length;
				const isPartialInAppChecked = checkedInAppCount > 0 && checkedInAppCount < inAppCheckboxes.length;

				allInAppMaster.prop('checked', isAllInAppChecked);
				allInAppMaster.prop('indeterminate', isPartialInAppChecked);
			}
		}

		// Function to update individual checkboxes based on master toggles
		function updateIndividualCheckboxes(masterCheckbox, selector) {
			const $container = $('.splms-dashboard-container');
			const isChecked = masterCheckbox.is(':checked');
			const individualCheckboxes = $container.find(selector);

			individualCheckboxes.each(function() {
				const $checkbox = $(this);
				const $label = $checkbox.closest('.splms-checkbox-label');
				const $option = $checkbox.closest('.splms-checkbox-option');

				$checkbox.prop('checked', isChecked);

				// Update visual states
				if (isChecked) {
					$label.removeClass('disabled');
					$option.removeClass('disabled');
				}
			});

			// Clear indeterminate state
			masterCheckbox.prop('indeterminate', false);
		}

		// Handle master toggle changes
		$('.splms-dashboard-container').on('change', '#all_email_notifications', function() {
			const $this = $(this);
			updateIndividualCheckboxes($this, 'input[name*="[email]"]');
		});

		$('.splms-dashboard-container').on('change', '#all_in_app_notifications', function() {
			const $this = $(this);
			updateIndividualCheckboxes($this, 'input[name*="[in_app]"]');
		});

		// Handle individual checkbox changes
		$('.splms-dashboard-container').on('change', 'input[name*="[email]"], input[name*="[in_app]"]', function() {
			// Small delay to ensure DOM is updated
			setTimeout(updateMasterToggles, 10);
		});

		// Initial state update
		setTimeout(function() {
			updateMasterToggles();
		}, 100);
	},


	handleWishlistRemoval: function() {
		$('.splms-dashboard-container').on('click', '.splms-remove-wishlist', function(e) {
			e.preventDefault();
			
			const $button = $(this);
			const $item = $button.closest('.splms-course-card');
			const courseId = $button.data('course-id');
			
			if (!courseId) {
				window.console?.error('Course ID not found');
				return;
			}
			
			// Disable button during request
			$button.prop('disabled', true).addClass('loading');
			
			window.SPLMSCore.frontendAjax.toggleWishlist(courseId).then(function(response) {
				if (response.success && response.data.action === 'removed') {
					// Show notification
					const message = response.data.message || 'Removed successfully';
					if (window.SPLMSCore && window.SPLMSCore.helper) {
						window.SPLMSCore.helper.showNotification(message, 'success');
					}
					
					// Remove item with animation
					$item.slideUp(200, function() {
						$(this).remove();
						
						// Check if wishlist is now empty and show message
						const $grid = $('.splms-course-list');
						if ($grid.find('.splms-course-card').length === 0) {
							const emptyHtml = '<div class="splms-alert splms-alert-info">' + 
								(window.SPLMSDashboard && window.SPLMSDashboard.strings && window.SPLMSDashboard.strings.wishlist_empty 
									? window.SPLMSDashboard.strings.wishlist_empty 
									: 'Your wishlist is empty.') + 
								'</div>';
							$grid.replaceWith(emptyHtml);
						}
					});
				} else {
					const errorMsg = response.data && response.data.message 
						? response.data.message 
						: 'Failed to remove item.';
					
					if (window.SPLMSCore && window.SPLMSCore.helper) {
						window.SPLMSCore.helper.showNotification(errorMsg, 'error');
					}
					
					$button.prop('disabled', false).removeClass('loading');
				}
			}).catch(function(error) {
				window.console?.error('Wishlist removal error:', error);
				if (window.SPLMSCore && window.SPLMSCore.helper) {
					window.SPLMSCore.helper.showNotification('Something went wrong. Please try again.', 'error');
				}
				$button.prop('disabled', false).removeClass('loading');
			});
		});
	},

	handleBookmarkRemoval: function() {
		$('.splms-dashboard-container').on('click', '.splms-remove-bookmark', function(e) {
			e.preventDefault();
			
			const $button = $(this);
			const $item = $button.closest('.splms-course-card');
			const lessonId = $button.data('lesson-id');
			const quizId = $button.data('quiz-id');
			const itemId = lessonId || quizId;
			const itemType = quizId ? 'quiz' : 'lesson';
			
			if (!itemId) {
				window.console?.error('Item ID not found');
				return;
			}
			
			// Disable button during request
			$button.prop('disabled', true).addClass('loading');
			
			window.SPLMSCore.frontendAjax.toggleBookmark(itemId, itemType).then(function(response) {
				if (response.success && response.data.action === 'removed') {
					// Show notification
					const message = response.data.message || 'Removed successfully';
					if (window.SPLMSCore && window.SPLMSCore.helper) {
						window.SPLMSCore.helper.showNotification(message, 'success');
					}
					
					// Remove item with animation
					$item.slideUp(200, function() {
						$(this).remove();
						
						// Check if bookmarks list is now empty and show message
						const $list = $('.splms-course-list');
						if ($list.find('.splms-course-card').length === 0) {
							const emptyHtml = '<div class="splms-alert splms-alert-info">' + 
								(window.SPLMSDashboard && window.SPLMSDashboard.strings && window.SPLMSDashboard.strings.bookmarks_empty 
									? window.SPLMSDashboard.strings.bookmarks_empty 
									: 'You have no bookmarks yet.') + 
								'</div>';
							$list.replaceWith(emptyHtml);
						}
					});
				} else {
					const errorMsg = response.data && response.data.message 
						? response.data.message 
						: 'Failed to remove item.';
					
					if (window.SPLMSCore && window.SPLMSCore.helper) {
						window.SPLMSCore.helper.showNotification(errorMsg, 'error');
					}
					
					$button.prop('disabled', false).removeClass('loading');
				}
			}).catch(function(error) {
				window.console?.error('Bookmark removal error:', error);
				if (window.SPLMSCore && window.SPLMSCore.helper) {
					window.SPLMSCore.helper.showNotification('Something went wrong. Please try again.', 'error');
				}
				$button.prop('disabled', false).removeClass('loading');
			});
		});
	},

};

	// Initialize on document ready
	$(document).ready(function() {
		if ($('.splms-dashboard-container').length) {
			// Expose localized strings if available
			const dashboard = window.splms_dashboard;
			if (dashboard && dashboard.strings) {
				Dashboard.strings = dashboard.strings;
			}
			Dashboard.init();
		}
	});

	// Make Dashboard available globally
	window.SPLMSDashboard = Dashboard;

})(jQuery);
