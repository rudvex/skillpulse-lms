/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/js/frontend/modules/dashboard/notifications.js"
/*!************************************************************!*\
  !*** ./src/js/frontend/modules/dashboard/notifications.js ***!
  \************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _react_core_utility_url__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../react-core/utility/url */ "./src/js/react-core/utility/url.js");


/**
 * Dashboard Notifications Tab
 *
 * Handles notification loading, rendering, filtering, and actions
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

(function ($) {
  'use strict';

  var DashboardNotifications = {
    currentFilter: 'unread',
    init: function init() {
      // Only initialize if notifications tab is present
      if ($('.splms-notifications-tab').length === 0) {
        return;
      }

      // Ensure wp.template is available
      if (typeof wp === 'undefined' || typeof wp.template === 'undefined') {
        console.error('WordPress wp.template is required for dashboard notifications');
        return;
      }
      this.bindEvents();
      this.loadNotifications();
    },
    bindEvents: function bindEvents() {
      var self = this;

      // Handle filter tabs
      $(document).on('click', '.splms-filter-tab', function (e) {
        e.preventDefault();
        var $tab = $(this);
        var filter = $tab.data('filter');

        // Update active state
        $('.splms-filter-tab').removeClass('active');
        $tab.addClass('active');

        // Update current filter
        self.currentFilter = filter;

        // Reload notifications with filter
        self.loadNotifications();
      });

      // Handle "Mark all as read" button
      $('#splms-mark-all-notifications-read').on('click', function (e) {
        e.preventDefault();
        self.handleMarkAllAsRead();
      });

      // Setup menu handlers (only called once, uses event delegation for dynamic elements)
      self.setupMenuHandlers();
    },
    setupMenuHandlers: function setupMenuHandlers() {
      var self = this;

      // Remove any existing handlers first to prevent duplicates
      $(document).off('click.splms-notification-menu');

      // Close all menus when clicking outside
      $(document).on('click.splms-notification-menu', function (e) {
        if (!$(e.target).closest('.splms-dropdown').length) {
          $('.splms-dropdown-menu').removeClass('show');
        }
      });

      // Toggle dropdown menu on button click
      $(document).on('click.splms-notification-menu', '.splms-dropdown-toggle', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        var $dropdown = $btn.closest('.splms-dropdown');
        var $menu = $dropdown.find('.splms-dropdown-menu');

        // Close all other menus
        $('.splms-dropdown-menu').not($menu).removeClass('show');

        // Toggle current menu
        $menu.toggleClass('show');
      });

      // Handle dropdown item clicks
      $(document).on('click.splms-notification-menu', '.splms-dropdown-item', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $item = $(this);
        var action = $item.data('action');
        var notificationId = $item.data('id');
        var $notificationItem = $('.splms-notification-item[data-notification-id="' + notificationId + '"]');

        // Close menu
        $('.splms-dropdown-menu').removeClass('show');
        if (action === 'mark-read') {
          self.markNotificationAsRead(notificationId, $notificationItem);
        } else if (action === 'mark-unread') {
          self.markNotificationAsUnread(notificationId, $notificationItem);
        } else if (action === 'delete') {
          self.deleteNotification(notificationId, $notificationItem);
        }
      });
    },
    loadNotifications: function loadNotifications() {
      var _dashboard$nonces;
      var self = this;
      var $container = $('#splms-dashboard-notifications-content');
      $container.html('<div class="splms-spinner"></div>');

      // Get dashboard object
      var dashboard = window.splms_dashboard;
      if (!dashboard || !dashboard.ajax_url || !((_dashboard$nonces = dashboard.nonces) !== null && _dashboard$nonces !== void 0 && _dashboard$nonces.splms_frontend_nonce)) {
        console.error('SPLMS: No valid AJAX data available for notifications.');
        self.renderError(self.getLocalizedString('failed_to_load_ajax_missing') || 'Failed to load notifications: AJAX data missing.');
        return;
      }

      // Get filter parameter
      var isReadParam = null;
      if (self.currentFilter === 'unread') {
        isReadParam = '0';
      } else if (self.currentFilter === 'read') {
        isReadParam = '1';
      }
      $.ajax({
        url: dashboard.ajax_url,
        type: 'POST',
        data: {
          action: 'splms_get_notifications',
          nonce: dashboard.nonces.splms_frontend_nonce,
          limit: 100,
          offset: 0,
          is_read: isReadParam
        },
        success: function success(response) {
          if (response.success && response.data && response.data.notifications && response.data.notifications.length > 0) {
            self.renderNotifications(response.data.notifications);
          } else {
            self.renderEmptyState();
          }
        },
        error: function error(xhr, status, _error) {
          console.error('Notifications load error:', {
            xhr: xhr,
            status: status,
            error: _error
          });
          console.error('Response text:', xhr.responseText);
          self.renderError(self.getLocalizedString('network_error') || 'Network error. Please try again.');
        }
      });
    },
    renderNotifications: function renderNotifications(notifications) {
      var self = this;
      var $container = $('#splms-dashboard-notifications-content');

      // Get current user avatar
      var userAvatar = this.getUserAvatar();

      // Format notifications for template
      var formattedNotifications = notifications.map(function (notification) {
        // Combine title and message for cleaner display
        var fullMessage = '';
        if (notification.title) {
          fullMessage = notification.title;
          if (notification.message) {
            fullMessage += ' ' + notification.message;
          }
        } else {
          fullMessage = notification.message || '';
        }
        return {
          id: notification.id,
          message: fullMessage,
          is_read: notification.is_read || false,
          time_ago: self.formatTimeAgo(notification.created_at),
          url: self.getNotificationUrl(notification),
          avatar: userAvatar
        };
      });

      // All notifications are returned from server, client-side filtering is handled by template
      var filteredNotifications = formattedNotifications;
      if (filteredNotifications.length === 0) {
        self.renderEmptyState();
        return;
      }
      var notificationsTemplate = wp.template('splms-dashboard-notifications');
      var templateData = {
        notifications: filteredNotifications
      };
      var html = notificationsTemplate(templateData);
      $container.html(html);

      // Setup click handlers for notifications
      self.setupNotificationHandlers();

      // Update filter button visibility
      self.updateFilterButtons();
    },
    renderEmptyState: function renderEmptyState() {
      var $container = $('#splms-dashboard-notifications-content');
      var emptyTemplate = wp.template('splms-dashboard-notifications-empty');
      var html = emptyTemplate({});
      $container.html(html);
    },
    renderError: function renderError(message) {
      var $container = $('#splms-dashboard-notifications-content');
      var html = '<div class="splms-alert splms-alert-error">' + message + '</div>';
      $container.html(html);
    },
    formatTimeAgo: function formatTimeAgo(dateString) {
      if (!dateString) return '';
      var date = new Date(dateString);
      var now = new Date();
      var diff = Math.floor((now - date) / 1000); // difference in seconds

      if (diff < 60) {
        return this.getLocalizedString('just_now') || 'Just now';
      } else if (diff < 3600) {
        var minutes = Math.floor(diff / 60);
        return minutes + ' ' + (this.getLocalizedString('minutes_ago') || 'minutes ago');
      } else if (diff < 86400) {
        var hours = Math.floor(diff / 3600);
        return hours + ' ' + (this.getLocalizedString('hours_ago') || 'hours ago');
      } else if (diff < 604800) {
        var days = Math.floor(diff / 86400);
        return days + ' ' + (this.getLocalizedString('days_ago') || 'days ago');
      } else {
        return date.toLocaleDateString();
      }
    },
    getNotificationUrl: function getNotificationUrl(notification) {
      // Check if meta has course_url
      if (notification.meta && notification.meta.course_url) {
        return notification.meta.course_url;
      }

      // Build URL from course_id or related_id
      if (notification.course_id) {
        return this.getBaseUrl() + '/courses/' + notification.course_id;
      } else if (notification.related_id && notification.related_type === 'course') {
        return this.getBaseUrl() + '/courses/' + notification.related_id;
      }
      return '#';
    },
    getBaseUrl: function getBaseUrl() {
      // Try to get base URL from localized data or use getSiteUrl()
      var dashboard = window.splms_dashboard;
      if (dashboard && dashboard.base_url) {
        return dashboard.base_url;
      }
      return (0,_react_core_utility_url__WEBPACK_IMPORTED_MODULE_0__.getSiteUrl)();
    },
    getUserAvatar: function getUserAvatar() {
      // Try to get avatar from localized data or use default
      var dashboard = window.splms_dashboard;
      if (dashboard && dashboard.user_avatar) {
        return dashboard.user_avatar;
      }
      // Fallback to default avatar
      return '';
    },
    getLocalizedString: function getLocalizedString(key) {
      // Try to get localized strings from various sources
      var dashboard = window.splms_dashboard;
      if (dashboard && dashboard.strings && dashboard.strings[key]) {
        return dashboard.strings[key];
      }
      return null;
    },
    setupNotificationHandlers: function setupNotificationHandlers() {
      // No specific handlers needed since we removed the action buttons
    },
    markNotificationAsRead: function markNotificationAsRead(notificationId, $item) {
      var _dashboard$nonces2;
      var self = this;
      var dashboard = window.splms_dashboard;
      if (!dashboard || !dashboard.ajax_url || !((_dashboard$nonces2 = dashboard.nonces) !== null && _dashboard$nonces2 !== void 0 && _dashboard$nonces2.splms_frontend_nonce)) {
        return;
      }
      $.ajax({
        url: dashboard.ajax_url,
        type: 'POST',
        data: {
          action: 'splms_mark_notification_read',
          nonce: dashboard.nonces.splms_frontend_nonce,
          notification_id: notificationId
        },
        success: function success(response) {
          if (response.success) {
            $item.addClass('read').removeClass('unread');
            // If filtering by unread, remove the item
            if (self.currentFilter === 'unread') {
              $item.fadeOut(200, function () {
                $(this).remove();
                if ($('.splms-notification-item').length === 0) {
                  self.renderEmptyState();
                }
              });
            }
            self.updateFilterButtons();
          }
        },
        error: function error() {
          console.error('Failed to mark notification as read');
        }
      });
    },
    markNotificationAsUnread: function markNotificationAsUnread(notificationId, $item) {
      var _dashboard$nonces3;
      var self = this;
      var dashboard = window.splms_dashboard;
      if (!dashboard || !dashboard.ajax_url || !((_dashboard$nonces3 = dashboard.nonces) !== null && _dashboard$nonces3 !== void 0 && _dashboard$nonces3.splms_frontend_nonce)) {
        return;
      }
      $.ajax({
        url: dashboard.ajax_url,
        type: 'POST',
        data: {
          action: 'splms_mark_notification_unread',
          nonce: dashboard.nonces.splms_frontend_nonce,
          notification_id: notificationId
        },
        success: function success(response) {
          if (response.success) {
            $item.removeClass('read').addClass('unread');
            // If filtering by read, remove the item
            if (self.currentFilter === 'read') {
              $item.fadeOut(200, function () {
                $(this).remove();
                if ($('.splms-notification-item').length === 0) {
                  self.renderEmptyState();
                }
              });
            }
            self.updateFilterButtons();
          }
        },
        error: function error() {
          console.error('Failed to mark notification as unread');
        }
      });
    },
    deleteNotification: function deleteNotification(notificationId, $item) {
      var _dashboard$nonces4;
      var self = this;
      var confirmMessage = this.getLocalizedString('confirm_delete_notification') || 'Are you sure you want to delete this notification?';
      if (!confirm(confirmMessage)) {
        return;
      }
      var dashboard = window.splms_dashboard;
      if (!dashboard || !dashboard.ajax_url || !((_dashboard$nonces4 = dashboard.nonces) !== null && _dashboard$nonces4 !== void 0 && _dashboard$nonces4.splms_frontend_nonce)) {
        return;
      }
      $.ajax({
        url: dashboard.ajax_url,
        type: 'POST',
        data: {
          action: 'splms_delete_notification',
          nonce: dashboard.nonces.splms_frontend_nonce,
          notification_id: notificationId
        },
        success: function success(response) {
          if (response.success) {
            $item.fadeOut(200, function () {
              $(this).remove();
              if ($('.splms-notification-item').length === 0) {
                self.renderEmptyState();
              }
              self.updateFilterButtons();
            });
          }
        },
        error: function error() {
          console.error('Failed to delete notification');
        }
      });
    },
    handleMarkAllAsRead: function handleMarkAllAsRead() {
      var _dashboard$nonces5;
      var self = this;
      var $button = $('#splms-mark-all-notifications-read');
      var originalText = $button.text();
      var markingText = this.getLocalizedString('marking') || 'Marking...';
      $button.prop('disabled', true).text(markingText);
      var dashboard = window.splms_dashboard;
      if (!dashboard || !dashboard.ajax_url || !((_dashboard$nonces5 = dashboard.nonces) !== null && _dashboard$nonces5 !== void 0 && _dashboard$nonces5.splms_frontend_nonce)) {
        $button.prop('disabled', false).text(originalText);
        return;
      }
      $.ajax({
        url: dashboard.ajax_url,
        type: 'POST',
        data: {
          action: 'splms_mark_all_notifications_read',
          nonce: dashboard.nonces.splms_frontend_nonce
        },
        success: function success(response) {
          if (response.success) {
            // Update all notifications to read state
            $('.splms-notification-item').addClass('read').removeClass('unread');

            // Reload notifications to reflect changes
            self.loadNotifications();
          }
        },
        error: function error() {
          console.error('Failed to mark all notifications as read');
        },
        complete: function complete() {
          $button.prop('disabled', false).text(originalText);
        }
      });
    },
    updateFilterButtons: function updateFilterButtons() {
      var unreadCount = $('.splms-notification-item.unread').length;
      if (unreadCount === 0) {
        $('#splms-mark-all-notifications-read').hide();
      } else {
        $('#splms-mark-all-notifications-read').show();
      }
    }
  };

  // Initialize on document ready
  $(document).ready(function () {
    if ($('.splms-notifications-tab').length) {
      DashboardNotifications.init();
    }
  });

  // Make DashboardNotifications available globally
  window.SPLMSDashboardNotifications = DashboardNotifications;
})(jQuery);

/***/ },

/***/ "./src/js/react-core/utility/url.js"
/*!******************************************!*\
  !*** ./src/js/react-core/utility/url.js ***!
  \******************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getAdminUrl: () => (/* binding */ getAdminUrl),
/* harmony export */   getPostTypeCreateUrl: () => (/* binding */ getPostTypeCreateUrl),
/* harmony export */   getPostTypeEditUrl: () => (/* binding */ getPostTypeEditUrl),
/* harmony export */   getSiteUrl: () => (/* binding */ getSiteUrl),
/* harmony export */   getTaxonomyEditUrl: () => (/* binding */ getTaxonomyEditUrl),
/* harmony export */   getUserEditUrl: () => (/* binding */ getUserEditUrl)
/* harmony export */ });
/**
 * Global utility for retrieving application URLs.
 */

/**
 * Retrieves the base site/home URL, preserving subdirectories.
 */
var getSiteUrl = function getSiteUrl() {
  var _window$SPLMSCore_Dat, _window$SPLMSCore_Dat2, _window$splms_fronten;
  return ((_window$SPLMSCore_Dat = window.SPLMSCore_Data) === null || _window$SPLMSCore_Dat === void 0 ? void 0 : _window$SPLMSCore_Dat.siteUrl) || ((_window$SPLMSCore_Dat2 = window.SPLMSCore_Data) === null || _window$SPLMSCore_Dat2 === void 0 ? void 0 : _window$SPLMSCore_Dat2.homeUrl) || ((_window$splms_fronten = window.splms_frontend) === null || _window$splms_fronten === void 0 ? void 0 : _window$splms_fronten.homeUrl);
};

/**
 * Retrieves the WordPress admin URL.
 */
var getAdminUrl = function getAdminUrl() {
  var _window$SPLMSCore_Dat3;
  return ((_window$SPLMSCore_Dat3 = window.SPLMSCore_Data) === null || _window$SPLMSCore_Dat3 === void 0 ? void 0 : _window$SPLMSCore_Dat3.adminUrl) || "".concat(getSiteUrl(), "/wp-admin");
};

/**
 * Retrieves the URL to create a new post of a specific type.
 */
var getPostTypeCreateUrl = function getPostTypeCreateUrl(postType) {
  return "".concat(getAdminUrl(), "/post-new.php?post_type=").concat(postType);
};

/**
 * Retrieves the URL to edit a specific post.
 */
var getPostTypeEditUrl = function getPostTypeEditUrl(postType, postId) {
  return "".concat(getAdminUrl(), "/post.php?post=").concat(postId, "&action=edit");
};

/**
 * Retrieves the URL to edit a taxonomy term.
 */
var getTaxonomyEditUrl = function getTaxonomyEditUrl(taxonomy, postType) {
  return "".concat(getAdminUrl(), "/edit-tags.php?taxonomy=").concat(taxonomy, "&post_type=").concat(postType);
};

/**
 * Retrieves the URL to edit a user profile.
 */
var getUserEditUrl = function getUserEditUrl(userId) {
  return "".concat(getAdminUrl(), "/user-edit.php?user_id=").concat(userId);
};

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Check if module exists (development only)
/******/ 		if (__webpack_modules__[moduleId] === undefined) {
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!****************************************************!*\
  !*** ./src/js/frontend/modules/dashboard/index.js ***!
  \****************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _notifications__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./notifications */ "./src/js/frontend/modules/dashboard/notifications.js");
/**
 * Dashboard JavaScript
 *
 * Handles dashboard interactions using jQuery
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

// Import tab-specific modules

(function ($) {
  'use strict';

  var Dashboard = {
    init: function init() {
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
    toggleSidebar: function toggleSidebar() {
      $('.splms-dashboard-container').on('click', '.splms-dashboard-sidebar-toggle', function (e) {
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
    handleTabs: function handleTabs() {
      $('.splms-dashboard-container').on('click', '.splms-dashboard-nav-item', function (e) {
        e.preventDefault();

        // Instructor modal removed

        var $this = $(this);
        var tab = $this.data('tab');
        if (!tab) {
          return;
        }

        // Update active state
        $('.splms-dashboard-nav-item').removeClass('is-active');
        $this.addClass('is-active');

        // Reload page with tab parameter
        var url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.location.href = url.toString();
      });
    },
    handleMobileMenu: function handleMobileMenu() {
      // Close sidebar when clicking overlay
      $('.splms-dashboard-overlay').on('click', function () {
        $('.splms-dashboard-sidebar').removeClass('is-open');
        $('body').removeClass('sidebar-open');
      });

      // Close sidebar when clicking nav item on mobile
      $('.splms-dashboard-container').on('click', '.splms-dashboard-nav-item', function () {
        if ($(window).width() <= 768) {
          setTimeout(function () {
            $('.splms-dashboard-sidebar').removeClass('is-open');
            $('body').removeClass('sidebar-open');
          }, 300);
        }
      });

      // Close sidebar on escape key
      $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $('.splms-dashboard-sidebar').hasClass('is-open')) {
          $('.splms-dashboard-sidebar').removeClass('is-open');
          $('body').removeClass('sidebar-open');
        }
      });
    },
    handleSettingsForms: function handleSettingsForms() {
      var self = this;

      // Profile form handler
      $('.splms-dashboard-container').on('submit', '#splms-profile-form', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $button = $form.find('button[type="submit"]');
        var originalText = $button.text();
        $button.prop('disabled', true).text(SPLMSDashboard.strings.saving || 'Saving...');
        var dashboard = window.splms_dashboard;
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
          success: function success(response) {
            if (response.success) {
              var message = typeof response.data === 'string' ? response.data : response.data.message || SPLMSDashboard.strings.success || 'Profile updated successfully!';
              window.SPLMSCore.helper.showNotification(message, 'success');

              // Reload after success to show updated values
              setTimeout(function () {
                location.reload();
              }, 1500);
            } else {
              var errorMsg = typeof response.data === 'string' ? response.data : response.data.message || SPLMSDashboard.strings.error || 'Error updating profile.';
              window.SPLMSCore.helper.showNotification(errorMsg, 'error');
            }
          },
          error: function error(xhr, status, _error) {
            var _window$console;
            (_window$console = window.console) === null || _window$console === void 0 || _window$console.error('Profile update error:', {
              xhr: xhr,
              status: status,
              error: _error
            });
            window.SPLMSCore.helper.showNotification(SPLMSDashboard.strings.error || 'Network error. Please try again.', 'error');
          },
          complete: function complete() {
            $button.prop('disabled', false).text(originalText);
          }
        });
      });

      // Password form handler
      $('.splms-dashboard-container').on('submit', '#splms-password-form', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $button = $form.find('button[type="submit"]');
        var originalText = $button.text();
        var newPassword = $form.find('[name="new_password"]').val();
        var confirmPassword = $form.find('[name="confirm_password"]').val();

        // Validate password match
        if (newPassword !== confirmPassword) {
          window.SPLMSCore.helper.showNotification(SPLMSDashboard.strings.passwords_mismatch || 'Passwords do not match.', 'error');
          return;
        }

        // Client-side password strength validation
        var passwordErrors = [];
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
          var errorMsg = 'Password must contain: ' + passwordErrors.join(', ') + '.';
          window.SPLMSCore.helper.showNotification(errorMsg, 'error');
          return;
        }
        var dashboard = window.splms_dashboard;
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
          success: function success(response) {
            if (response.success) {
              var message = typeof response.data === 'string' ? response.data : response.data.message || SPLMSDashboard.strings.success || 'Password changed successfully!';
              window.SPLMSCore.helper.showNotification(message, 'success');
              $form[0].reset();
            } else {
              var _errorMsg = typeof response.data === 'string' ? response.data : response.data.message || SPLMSDashboard.strings.error || 'Error changing password.';
              window.SPLMSCore.helper.showNotification(_errorMsg, 'error');
            }
          },
          error: function error(xhr, status, _error2) {
            var _window$console2;
            (_window$console2 = window.console) === null || _window$console2 === void 0 || _window$console2.error('Password change error:', {
              xhr: xhr,
              status: status,
              error: _error2
            });
            window.SPLMSCore.helper.showNotification(SPLMSDashboard.strings.error || 'Network error. Please try again.', 'error');
          },
          complete: function complete() {
            $button.prop('disabled', false).text(originalText);
          }
        });
      });

      // Notification preferences form handler
      $('.splms-dashboard-container').on('submit', '#splms-notification-preferences-form', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $button = $form.find('button[type="submit"]');
        var originalText = $button.text();
        $button.prop('disabled', true).text(SPLMSDashboard.strings.saving || 'Saving...');

        // Collect form data
        var dashboard = window.splms_dashboard;
        var formData = {
          action: 'splms_save_notification_preferences',
          nonce: dashboard.nonce,
          email_enabled: $form.find('#all_email_notifications').is(':checked') ? '1' : '0',
          in_app_enabled: $form.find('#all_in_app_notifications').is(':checked') ? '1' : '0',
          events: {}
        };

        // Collect event preferences
        $form.find('[name^="events["]').each(function () {
          var $input = $(this);
          var name = $input.attr('name');
          var match = name.match(/events\[([^\]]+)\]\[([^\]]+)\]/);
          if (match) {
            var eventKey = match[1];
            var method = match[2];
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
          success: function success(response) {
            if (response.success) {
              var message = typeof response.data === 'string' ? response.data : response.data.message || SPLMSDashboard.strings.success || 'Notification preferences saved successfully!';
              window.SPLMSCore.helper.showNotification(message, 'success');
            } else {
              var errorMsg = typeof response.data === 'string' ? response.data : response.data.message || SPLMSDashboard.strings.error || 'Error saving notification preferences.';
              window.SPLMSCore.helper.showNotification(errorMsg, 'error');
            }
          },
          error: function error(xhr, status, _error3) {
            var _window$console3;
            (_window$console3 = window.console) === null || _window$console3 === void 0 || _window$console3.error('Notification preferences save error:', {
              xhr: xhr,
              status: status,
              error: _error3
            });
            window.SPLMSCore.helper.showNotification(SPLMSDashboard.strings.error || 'Network error. Please try again.', 'error');
          },
          complete: function complete() {
            $button.prop('disabled', false).text(originalText);
          }
        });
      });

      // Smart notification disable/enable logic
      this.handleSmartNotificationToggle();
    },
    handleSmartNotificationToggle: function handleSmartNotificationToggle() {
      var self = this;

      // Function to update master toggle states based on individual checkboxes
      function updateMasterToggles() {
        var $container = $('.splms-dashboard-container');

        // Update All Email master toggle
        var emailCheckboxes = $container.find('input[name*="[email]"]');
        var allEmailMaster = $container.find('#all_email_notifications');
        if (emailCheckboxes.length > 0 && allEmailMaster.length > 0) {
          var checkedEmailCount = emailCheckboxes.filter(':checked').length;
          var isAllEmailChecked = checkedEmailCount === emailCheckboxes.length;
          var isPartialEmailChecked = checkedEmailCount > 0 && checkedEmailCount < emailCheckboxes.length;
          allEmailMaster.prop('checked', isAllEmailChecked);
          allEmailMaster.prop('indeterminate', isPartialEmailChecked);
        }

        // Update All In-App master toggle
        var inAppCheckboxes = $container.find('input[name*="[in_app]"]');
        var allInAppMaster = $container.find('#all_in_app_notifications');
        if (inAppCheckboxes.length > 0 && allInAppMaster.length > 0) {
          var checkedInAppCount = inAppCheckboxes.filter(':checked').length;
          var isAllInAppChecked = checkedInAppCount === inAppCheckboxes.length;
          var isPartialInAppChecked = checkedInAppCount > 0 && checkedInAppCount < inAppCheckboxes.length;
          allInAppMaster.prop('checked', isAllInAppChecked);
          allInAppMaster.prop('indeterminate', isPartialInAppChecked);
        }
      }

      // Function to update individual checkboxes based on master toggles
      function updateIndividualCheckboxes(masterCheckbox, selector) {
        var $container = $('.splms-dashboard-container');
        var isChecked = masterCheckbox.is(':checked');
        var individualCheckboxes = $container.find(selector);
        individualCheckboxes.each(function () {
          var $checkbox = $(this);
          var $label = $checkbox.closest('.splms-checkbox-label');
          var $option = $checkbox.closest('.splms-checkbox-option');
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
      $('.splms-dashboard-container').on('change', '#all_email_notifications', function () {
        var $this = $(this);
        updateIndividualCheckboxes($this, 'input[name*="[email]"]');
      });
      $('.splms-dashboard-container').on('change', '#all_in_app_notifications', function () {
        var $this = $(this);
        updateIndividualCheckboxes($this, 'input[name*="[in_app]"]');
      });

      // Handle individual checkbox changes
      $('.splms-dashboard-container').on('change', 'input[name*="[email]"], input[name*="[in_app]"]', function () {
        // Small delay to ensure DOM is updated
        setTimeout(updateMasterToggles, 10);
      });

      // Initial state update
      setTimeout(function () {
        updateMasterToggles();
      }, 100);
    },
    handleWishlistRemoval: function handleWishlistRemoval() {
      $('.splms-dashboard-container').on('click', '.splms-remove-wishlist', function (e) {
        e.preventDefault();
        var $button = $(this);
        var $item = $button.closest('.splms-course-card');
        var courseId = $button.data('course-id');
        if (!courseId) {
          var _window$console4;
          (_window$console4 = window.console) === null || _window$console4 === void 0 || _window$console4.error('Course ID not found');
          return;
        }

        // Disable button during request
        $button.prop('disabled', true).addClass('loading');
        window.SPLMSCore.frontendAjax.toggleWishlist(courseId).then(function (response) {
          if (response.success && response.data.action === 'removed') {
            // Show notification
            var message = response.data.message || 'Removed successfully';
            if (window.SPLMSCore && window.SPLMSCore.helper) {
              window.SPLMSCore.helper.showNotification(message, 'success');
            }

            // Remove item with animation
            $item.slideUp(200, function () {
              $(this).remove();

              // Check if wishlist is now empty and show message
              var $grid = $('.splms-course-list');
              if ($grid.find('.splms-course-card').length === 0) {
                var emptyHtml = '<div class="splms-alert splms-alert-info">' + (window.SPLMSDashboard && window.SPLMSDashboard.strings && window.SPLMSDashboard.strings.wishlist_empty ? window.SPLMSDashboard.strings.wishlist_empty : 'Your wishlist is empty.') + '</div>';
                $grid.replaceWith(emptyHtml);
              }
            });
          } else {
            var errorMsg = response.data && response.data.message ? response.data.message : 'Failed to remove item.';
            if (window.SPLMSCore && window.SPLMSCore.helper) {
              window.SPLMSCore.helper.showNotification(errorMsg, 'error');
            }
            $button.prop('disabled', false).removeClass('loading');
          }
        })["catch"](function (error) {
          var _window$console5;
          (_window$console5 = window.console) === null || _window$console5 === void 0 || _window$console5.error('Wishlist removal error:', error);
          if (window.SPLMSCore && window.SPLMSCore.helper) {
            window.SPLMSCore.helper.showNotification('Something went wrong. Please try again.', 'error');
          }
          $button.prop('disabled', false).removeClass('loading');
        });
      });
    },
    handleBookmarkRemoval: function handleBookmarkRemoval() {
      $('.splms-dashboard-container').on('click', '.splms-remove-bookmark', function (e) {
        e.preventDefault();
        var $button = $(this);
        var $item = $button.closest('.splms-course-card');
        var lessonId = $button.data('lesson-id');
        var quizId = $button.data('quiz-id');
        var itemId = lessonId || quizId;
        var itemType = quizId ? 'quiz' : 'lesson';
        if (!itemId) {
          var _window$console6;
          (_window$console6 = window.console) === null || _window$console6 === void 0 || _window$console6.error('Item ID not found');
          return;
        }

        // Disable button during request
        $button.prop('disabled', true).addClass('loading');
        window.SPLMSCore.frontendAjax.toggleBookmark(itemId, itemType).then(function (response) {
          if (response.success && response.data.action === 'removed') {
            // Show notification
            var message = response.data.message || 'Removed successfully';
            if (window.SPLMSCore && window.SPLMSCore.helper) {
              window.SPLMSCore.helper.showNotification(message, 'success');
            }

            // Remove item with animation
            $item.slideUp(200, function () {
              $(this).remove();

              // Check if bookmarks list is now empty and show message
              var $list = $('.splms-course-list');
              if ($list.find('.splms-course-card').length === 0) {
                var emptyHtml = '<div class="splms-alert splms-alert-info">' + (window.SPLMSDashboard && window.SPLMSDashboard.strings && window.SPLMSDashboard.strings.bookmarks_empty ? window.SPLMSDashboard.strings.bookmarks_empty : 'You have no bookmarks yet.') + '</div>';
                $list.replaceWith(emptyHtml);
              }
            });
          } else {
            var errorMsg = response.data && response.data.message ? response.data.message : 'Failed to remove item.';
            if (window.SPLMSCore && window.SPLMSCore.helper) {
              window.SPLMSCore.helper.showNotification(errorMsg, 'error');
            }
            $button.prop('disabled', false).removeClass('loading');
          }
        })["catch"](function (error) {
          var _window$console7;
          (_window$console7 = window.console) === null || _window$console7 === void 0 || _window$console7.error('Bookmark removal error:', error);
          if (window.SPLMSCore && window.SPLMSCore.helper) {
            window.SPLMSCore.helper.showNotification('Something went wrong. Please try again.', 'error');
          }
          $button.prop('disabled', false).removeClass('loading');
        });
      });
    }
  };

  // Initialize on document ready
  $(document).ready(function () {
    if ($('.splms-dashboard-container').length) {
      // Expose localized strings if available
      var dashboard = window.splms_dashboard;
      if (dashboard && dashboard.strings) {
        Dashboard.strings = dashboard.strings;
      }
      Dashboard.init();
    }
  });

  // Make Dashboard available globally
  window.SPLMSDashboard = Dashboard;
})(jQuery);
})();

/******/ })()
;
//# sourceMappingURL=dashboard.js.map