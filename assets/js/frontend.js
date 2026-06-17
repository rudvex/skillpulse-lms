/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/js/frontend/api/notifications.js"
/*!**********************************************!*\
  !*** ./src/js/frontend/api/notifications.js ***!
  \**********************************************/
(module) {

function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * SkillPulse LMS Notifications REST API Helper
 * 
 * JavaScript helper for interacting with the Notifications REST API
 * Useful for mobile apps, third-party integrations, or custom implementations
 * 
 * @package SkillPulse_LMS
 * @since 1.0.0
 */
var SPLMSNotificationsAPI = /*#__PURE__*/function () {
  function SPLMSNotificationsAPI() {
    _classCallCheck(this, SPLMSNotificationsAPI);
    this.namespace = 'splms/v1';
    this.restBase = 'notifications';
    this.baseURL = window.SPLMSCore.helper.getRestApiUrl("".concat(this.namespace, "/").concat(this.restBase));
  }

  /**
      * Get authentication headers
      * @returns {Object} Headers object
      */
  return _createClass(SPLMSNotificationsAPI, [{
    key: "getHeaders",
    value: function getHeaders() {
      var _window$wpApiSettings;
      var headers = {
        'Content-Type': 'application/json'
      };

      // Add nonce if available
      if ((_window$wpApiSettings = window.wpApiSettings) !== null && _window$wpApiSettings !== void 0 && _window$wpApiSettings.nonce) {
        headers['X-WP-Nonce'] = window.wpApiSettings.nonce;
      }
      return headers;
    }

    /**
        * Make API request
        * @param {string} endpoint - API endpoint
        * @param {Object} options - Request options
        * @returns {Promise} Promise resolving to response data
        */
  }, {
    key: "request",
    value: (function () {
      var _request = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(endpoint) {
        var options,
          url,
          config,
          response,
          data,
          _args = arguments;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.n) {
            case 0:
              options = _args.length > 1 && _args[1] !== undefined ? _args[1] : {};
              url = endpoint.startsWith('http') ? endpoint : "".concat(this.baseURL).concat(endpoint);
              config = _objectSpread({
                method: options.method || 'GET',
                headers: this.getHeaders()
              }, options);
              if (options.body && _typeof(options.body) === 'object') {
                config.body = JSON.stringify(options.body);
              }
              _context.n = 1;
              return fetch(url, config);
            case 1:
              response = _context.v;
              _context.n = 2;
              return response.json();
            case 2:
              data = _context.v;
              if (response.ok) {
                _context.n = 3;
                break;
              }
              throw new Error(data.message || "HTTP error! status: ".concat(response.status));
            case 3:
              return _context.a(2, data);
          }
        }, _callee, this);
      }));
      function request(_x) {
        return _request.apply(this, arguments);
      }
      return request;
    }()
    /**
        * Get notifications for current user
        * @param {Object} params - Query parameters
        * @returns {Promise} Promise resolving to notifications array
        */
    )
  }, {
    key: "getNotifications",
    value: (function () {
      var _getNotifications = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2() {
        var params,
          queryString,
          endpoint,
          _args2 = arguments;
        return _regenerator().w(function (_context2) {
          while (1) switch (_context2.n) {
            case 0:
              params = _args2.length > 0 && _args2[0] !== undefined ? _args2[0] : {};
              queryString = new URLSearchParams(params).toString();
              endpoint = queryString ? "?".concat(queryString) : '';
              return _context2.a(2, this.request(endpoint));
          }
        }, _callee2, this);
      }));
      function getNotifications() {
        return _getNotifications.apply(this, arguments);
      }
      return getNotifications;
    }()
    /**
        * Get single notification
        * @param {number|string} id - Notification ID
        * @returns {Promise} Promise resolving to notification object
        */
    )
  }, {
    key: "getNotification",
    value: (function () {
      var _getNotification = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee3(id) {
        return _regenerator().w(function (_context3) {
          while (1) switch (_context3.n) {
            case 0:
              return _context3.a(2, this.request("/".concat(id)));
          }
        }, _callee3, this);
      }));
      function getNotification(_x2) {
        return _getNotification.apply(this, arguments);
      }
      return getNotification;
    }()
    /**
        * Get unread count
        * @returns {Promise} Promise resolving to unread count
        */
    )
  }, {
    key: "getUnreadCount",
    value: (function () {
      var _getUnreadCount = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee4() {
        var _response$data;
        var response;
        return _regenerator().w(function (_context4) {
          while (1) switch (_context4.n) {
            case 0:
              _context4.n = 1;
              return this.request('/unread-count');
            case 1:
              response = _context4.v;
              return _context4.a(2, ((_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.unread_count) || 0);
          }
        }, _callee4, this);
      }));
      function getUnreadCount() {
        return _getUnreadCount.apply(this, arguments);
      }
      return getUnreadCount;
    }()
    /**
        * Mark notification as read
        * @param {number|string} id - Notification ID
        * @returns {Promise} Promise resolving to updated notification
        */
    )
  }, {
    key: "markAsRead",
    value: (function () {
      var _markAsRead = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee5(id) {
        return _regenerator().w(function (_context5) {
          while (1) switch (_context5.n) {
            case 0:
              return _context5.a(2, this.request("/".concat(id, "/read"), {
                method: 'PATCH'
              }));
          }
        }, _callee5, this);
      }));
      function markAsRead(_x3) {
        return _markAsRead.apply(this, arguments);
      }
      return markAsRead;
    }()
    /**
        * Mark notification as unread
        * @param {number|string} id - Notification ID
        * @returns {Promise} Promise resolving to updated notification
        */
    )
  }, {
    key: "markAsUnread",
    value: (function () {
      var _markAsUnread = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee6(id) {
        return _regenerator().w(function (_context6) {
          while (1) switch (_context6.n) {
            case 0:
              return _context6.a(2, this.request("/".concat(id, "/unread"), {
                method: 'PATCH'
              }));
          }
        }, _callee6, this);
      }));
      function markAsUnread(_x4) {
        return _markAsUnread.apply(this, arguments);
      }
      return markAsUnread;
    }()
    /**
        * Mark all notifications as read
        * @param {string} eventKey - Optional event key filter
        * @returns {Promise} Promise resolving to result
        */
    )
  }, {
    key: "markAllAsRead",
    value: (function () {
      var _markAllAsRead = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee7() {
        var eventKey,
          body,
          _args7 = arguments;
        return _regenerator().w(function (_context7) {
          while (1) switch (_context7.n) {
            case 0:
              eventKey = _args7.length > 0 && _args7[0] !== undefined ? _args7[0] : null;
              body = eventKey ? {
                event_key: eventKey
              } : {};
              return _context7.a(2, this.request('/mark-all-read', {
                method: 'POST',
                body: body
              }));
          }
        }, _callee7, this);
      }));
      function markAllAsRead() {
        return _markAllAsRead.apply(this, arguments);
      }
      return markAllAsRead;
    }()
    /**
        * Delete notification
        * @param {number|string} id - Notification ID
        * @returns {Promise} Promise resolving to deletion result
        */
    )
  }, {
    key: "deleteNotification",
    value: (function () {
      var _deleteNotification = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee8(id) {
        return _regenerator().w(function (_context8) {
          while (1) switch (_context8.n) {
            case 0:
              return _context8.a(2, this.request("/".concat(id), {
                method: 'DELETE'
              }));
          }
        }, _callee8, this);
      }));
      function deleteNotification(_x5) {
        return _deleteNotification.apply(this, arguments);
      }
      return deleteNotification;
    }()
    /**
        * Perform bulk action on notifications
        * @param {Array<number|string>} notificationIds - Array of notification IDs
        * @param {string} action - Action to perform: 'mark_read', 'mark_unread', or 'delete'
        * @returns {Promise} Promise resolving to result
        */
    )
  }, {
    key: "bulkAction",
    value: (function () {
      var _bulkAction = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee9(notificationIds, action) {
        var validActions;
        return _regenerator().w(function (_context9) {
          while (1) switch (_context9.n) {
            case 0:
              if (!(!Array.isArray(notificationIds) || notificationIds.length === 0)) {
                _context9.n = 1;
                break;
              }
              throw new Error('notificationIds must be a non-empty array');
            case 1:
              validActions = ['mark_read', 'mark_unread', 'delete'];
              if (validActions.includes(action)) {
                _context9.n = 2;
                break;
              }
              throw new Error("action must be one of: ".concat(validActions.join(', ')));
            case 2:
              return _context9.a(2, this.request('/bulk-action', {
                method: 'POST',
                body: {
                  notification_ids: notificationIds,
                  action: action
                }
              }));
          }
        }, _callee9, this);
      }));
      function bulkAction(_x6, _x7) {
        return _bulkAction.apply(this, arguments);
      }
      return bulkAction;
    }())
  }]);
}(); // Export for use in modules
if ( true && module.exports) {
  module.exports = SPLMSNotificationsAPI;
}

// Make available globally
window.SPLMSNotificationsAPI = SPLMSNotificationsAPI;

/***/ },

/***/ "./src/js/frontend/components/avatar-upload.js"
/*!*****************************************************!*\
  !*** ./src/js/frontend/components/avatar-upload.js ***!
  \*****************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SPLMSAvatarUpload: () => (/* binding */ SPLMSAvatarUpload)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Avatar Upload Component
 * Handles avatar upload functionality for  profiles
 */

var SPLMSAvatarUpload = /*#__PURE__*/function () {
  function SPLMSAvatarUpload() {
    _classCallCheck(this, SPLMSAvatarUpload);
    this.init();
  }
  return _createClass(SPLMSAvatarUpload, [{
    key: "init",
    value: function init() {
      this.bindEvents();
    }
  }, {
    key: "bindEvents",
    value: function bindEvents() {
      var _this = this;
      // Handle file input change
      jQuery(document).on('change', '#avatar-upload', function (e) {
        _this.handleFileSelect(e);
      });

      // Handle click on avatar container (support both class names)
      jQuery(document).on('click', '.avatar-upload-container, .splms-avatar-upload-container', function (e) {
        // Only trigger if clicking on the container itself, not the file input
        if (e.target === e.currentTarget || e.target.classList.contains('camera-icon') || e.target.closest('.camera-icon')) {
          jQuery('#avatar-upload').click();
        }
      });

      // Handle remove button click (support both class names)
      jQuery(document).on('click', '.avatar-remove-btn, .splms-avatar-remove-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        _this.showRemoveConfirmation();
      });

      // Handle preview confirm/cancel
      jQuery(document).on('click', '.avatar-preview-confirm', function (e) {
        e.preventDefault();
        _this.confirmUpload();
      });
      jQuery(document).on('click', '.avatar-preview-cancel', function (e) {
        e.preventDefault();
        _this.cancelPreview();
      });

      // Handle remove confirmation
      jQuery(document).on('click', '.avatar-remove-confirm', function (e) {
        e.preventDefault();
        _this.confirmRemove();
      });
      jQuery(document).on('click', '.avatar-remove-cancel', function (e) {
        e.preventDefault();
        _this.cancelRemove();
      });
    }
  }, {
    key: "handleFileSelect",
    value: function handleFileSelect(e) {
      var file = e.target.files[0];
      if (!file) return;

      // Validate file type
      var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
      if (!allowedTypes.includes(file.type)) {
        this.showNotification('Please select a valid image file (JPG, PNG, or GIF).', 'error');
        return;
      }

      // Validate file size (5MB max)
      var maxSize = 5 * 1024 * 1024; // 5MB
      if (file.size > maxSize) {
        this.showNotification('File size too large. Please select an image smaller than 5MB.', 'error');
        return;
      }

      // Show preview
      this.showPreview(file);
    }
  }, {
    key: "showPreview",
    value: function showPreview(file) {
      var _this2 = this;
      var reader = new FileReader();
      reader.onload = function (e) {
        var previewUrl = e.target.result;
        _this2.createPreviewModal(previewUrl, file);
      };
      reader.readAsDataURL(file);
    }
  }, {
    key: "createPreviewModal",
    value: function createPreviewModal(previewUrl, file) {
      var _this3 = this;
      // Remove existing preview modal
      jQuery('.avatar-preview-modal').remove();
      var modal = "\n            <div class=\"avatar-preview-modal\">\n                <div class=\"avatar-preview-overlay\"></div>\n                <div class=\"avatar-preview-content\">\n                    <div class=\"avatar-preview-header\">\n                        <h3>Preview Your New Avatar</h3>\n                        <button class=\"avatar-preview-close\">&times;</button>\n                    </div>\n                    <div class=\"avatar-preview-body\">\n                        <div class=\"avatar-preview-image\">\n                            <img src=\"".concat(previewUrl, "\" alt=\"Avatar Preview\">\n                        </div>\n                        <div class=\"avatar-preview-info\">\n                            <p><strong>File:</strong> ").concat(file.name, "</p>\n                            <p><strong>Size:</strong> ").concat(this.formatFileSize(file.size), "</p>\n                            <p><strong>Type:</strong> ").concat(file.type, "</p>\n                        </div>\n                    </div>\n                    <div class=\"avatar-preview-actions\">\n                        <button class=\"avatar-preview-cancel btn btn-secondary\">Cancel</button>\n                        <button class=\"avatar-preview-confirm btn btn-primary\">Upload Avatar</button>\n                    </div>\n                </div>\n            </div>\n        ");
      jQuery('body').append(modal);

      // Store file for upload
      this.pendingFile = file;

      // Handle close button
      jQuery('.avatar-preview-close').on('click', function () {
        _this3.cancelPreview();
      });

      // Handle overlay click
      jQuery('.avatar-preview-overlay').on('click', function () {
        _this3.cancelPreview();
      });

      // Handle escape key
      jQuery(document).on('keydown.avatarPreview', function (e) {
        if (e.key === 'Escape') {
          _this3.cancelPreview();
        }
      });
    }
  }, {
    key: "confirmUpload",
    value: function confirmUpload() {
      if (!this.pendingFile) return;

      // Show loading state
      this.showLoadingState();

      // Upload the file
      this.uploadAvatar(this.pendingFile);
    }
  }, {
    key: "cancelPreview",
    value: function cancelPreview() {
      // Remove preview modal
      jQuery('.avatar-preview-modal').remove();

      // Clear file input
      jQuery('#avatar-upload').val('');

      // Remove escape key handler
      jQuery(document).off('keydown.avatarPreview');

      // Clear pending file
      this.pendingFile = null;
    }
  }, {
    key: "uploadAvatar",
    value: function uploadAvatar(file) {
      var _this4 = this;
      var frontend = window.splms_frontend;
      var formData = new FormData();
      formData.append('action', 'splms_upload_avatar');
      formData.append('avatar', file);
      formData.append('nonce', frontend.nonces.splms_nonce);
      jQuery.ajax({
        url: frontend.ajax_url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function success(response) {
          _this4.handleUploadSuccess(response);
        },
        error: function error(xhr, status, _error) {
          _this4.handleUploadError(xhr, status, _error);
        },
        complete: function complete() {
          _this4.hideLoadingState();
        }
      });
    }
  }, {
    key: "handleUploadSuccess",
    value: function handleUploadSuccess(response) {
      if (response.success) {
        // Remove preview modal
        jQuery('.avatar-preview-modal').remove();

        // Update the avatar image
        this.updateAvatarImage(response.data.avatar_url);

        // Show success message
        this.showNotification(response.data.message, 'success');

        // Clear the file input
        jQuery('#avatar-upload').val('');

        // Clear pending file
        this.pendingFile = null;

        // Remove escape key handler
        jQuery(document).off('keydown.avatarPreview');

        // Trigger a page reload to update all avatar instances
        setTimeout(function () {
          window.location.reload();
        }, 1500);
      } else {
        this.showNotification(response.data || 'Upload failed. Please try again.', 'error');
      }
    }
  }, {
    key: "handleUploadError",
    value: function handleUploadError() {
      this.showNotification('Network error. Please try again.', 'error');
    }
  }, {
    key: "updateAvatarImage",
    value: function updateAvatarImage(avatarUrl) {
      // Update the avatar image in the profile (support both class names)
      var $avatarContainer = jQuery('.avatar-upload-container img, .splms-avatar-upload-container img, .splms-avatar-image');
      if ($avatarContainer.length) {
        $avatarContainer.attr('src', avatarUrl);
      }

      // Update any other avatar instances on the page
      jQuery('img[src*="avatar"], .splms-avatar-image').each(function () {
        var currentSrc = jQuery(this).attr('src');
        if (currentSrc && (currentSrc.includes('avatar') || currentSrc.includes('profile-picture'))) {
          if (!currentSrc.includes('gravatar') && !currentSrc.includes('mystery')) {
            jQuery(this).attr('src', avatarUrl);
          }
        }
      });
    }
  }, {
    key: "showLoadingState",
    value: function showLoadingState() {
      var $container = jQuery('.avatar-upload-container, .splms-avatar-upload-container');
      $container.addClass('uploading');
      jQuery('.avatar-preview-confirm').prop('disabled', true);
      jQuery('.avatar-remove-confirm').prop('disabled', true);

      // Add loading overlay
      if (!$container.find('.upload-loading').length) {
        $container.append("\n                <div class=\"upload-loading\">\n                    <div class=\"loading-spinner\"></div>\n                    <span>Uploading...</span>\n                </div>\n            ");
      }
    }
  }, {
    key: "hideLoadingState",
    value: function hideLoadingState() {
      var $container = jQuery('.avatar-upload-container, .splms-avatar-upload-container');
      $container.removeClass('uploading');
      $container.find('.upload-loading').remove();
      jQuery('.avatar-preview-confirm').prop('disabled', false);
      jQuery('.avatar-remove-confirm').prop('disabled', false);
    }
  }, {
    key: "formatFileSize",
    value: function formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      var k = 1024;
      var sizes = ['Bytes', 'KB', 'MB', 'GB'];
      var i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
  }, {
    key: "showNotification",
    value: function showNotification(message) {
      var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'info';
      window.SPLMSCore.helper.showNotification(message, type);
    }
  }, {
    key: "showRemoveConfirmation",
    value: function showRemoveConfirmation() {
      var _this5 = this;
      // Remove existing modals
      jQuery('.avatar-preview-modal, .avatar-remove-modal').remove();
      var modal = "\n            <div class=\"avatar-remove-modal\">\n                <div class=\"avatar-preview-overlay\"></div>\n                <div class=\"avatar-preview-content\">\n                    <div class=\"avatar-preview-header\">\n                        <h3>Remove Avatar</h3>\n                        <button class=\"avatar-preview-close\">&times;</button>\n                    </div>\n                    <div class=\"avatar-preview-body\">\n                        <div class=\"avatar-preview-image\">\n                            <svg width=\"80\" height=\"80\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n                                <path d=\"M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21\" stroke=\"var(--splms-danger, #ef4444)\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n                                <circle cx=\"12\" cy=\"7\" r=\"4\" stroke=\"var(--splms-danger, #ef4444)\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n                            </svg>\n                        </div>\n                        <div class=\"avatar-preview-info\">\n                            <p><strong>Are you sure?</strong></p>\n                            <p>This will remove your current profile picture and revert to the default avatar. This action cannot be undone.</p>\n                        </div>\n                    </div>\n                    <div class=\"avatar-preview-actions\">\n                        <button class=\"avatar-remove-cancel btn btn-secondary\">Cancel</button>\n                        <button class=\"avatar-remove-confirm btn btn-danger\">Remove Avatar</button>\n                    </div>\n                </div>\n            </div>\n        ";
      jQuery('body').append(modal);

      // Handle close button
      jQuery('.avatar-preview-close').on('click', function () {
        _this5.cancelRemove();
      });

      // Handle overlay click
      jQuery('.avatar-preview-overlay').on('click', function () {
        _this5.cancelRemove();
      });

      // Handle escape key
      jQuery(document).on('keydown.avatarRemove', function (e) {
        if (e.key === 'Escape') {
          _this5.cancelRemove();
        }
      });
    }
  }, {
    key: "confirmRemove",
    value: function confirmRemove() {
      var _this6 = this;
      // Show loading state
      this.showLoadingState();

      // Send remove request
      var frontend = window.splms_frontend;
      jQuery.ajax({
        url: frontend.ajax_url,
        type: 'POST',
        data: {
          action: 'splms_remove_avatar',
          nonce: frontend.nonces.splms_nonce
        },
        success: function success(response) {
          _this6.handleRemoveSuccess(response);
        },
        error: function error(xhr, status, _error2) {
          _this6.handleRemoveError(xhr, status, _error2);
        },
        complete: function complete() {
          _this6.hideLoadingState();
        }
      });
    }
  }, {
    key: "cancelRemove",
    value: function cancelRemove() {
      // Remove remove modal
      jQuery('.avatar-remove-modal').remove();

      // Remove escape key handler
      jQuery(document).off('keydown.avatarRemove');
    }
  }, {
    key: "handleRemoveSuccess",
    value: function handleRemoveSuccess(response) {
      if (response.success) {
        // Remove remove modal
        jQuery('.avatar-remove-modal').remove();

        // Update the avatar image to default
        this.updateAvatarImage(response.data.avatar_url || '');

        // Show success message
        this.showNotification(response.data.message || 'Avatar removed successfully.', 'success');

        // Remove escape key handler
        jQuery(document).off('keydown.avatarRemove');

        // Trigger a page reload to update all avatar instances
        setTimeout(function () {
          window.location.reload();
        }, 1500);
      } else {
        this.showNotification(response.data || 'Failed to remove avatar. Please try again.', 'error');
      }
    }
  }, {
    key: "handleRemoveError",
    value: function handleRemoveError() {
      this.showNotification('Network error. Please try again.', 'error');
    }
  }]);
}();

/***/ },

/***/ "./src/js/frontend/components/header-interactions.js"
/*!***********************************************************!*\
  !*** ./src/js/frontend/components/header-interactions.js ***!
  \***********************************************************/
() {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Header Interactions JavaScript
 * 
 * Handles mobile menu, user dropdown, notifications, and scroll effects
 * 
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

(function ($) {
  'use strict';

  /**
      * Header Interactions Class
      */
  var HeaderInteractions = /*#__PURE__*/function () {
    function HeaderInteractions() {
      _classCallCheck(this, HeaderInteractions);
      this.header = $('.splms-header');
      this.mobileToggle = $('.splms-mobile-menu-toggle');
      this.mobileMenu = $('.splms-mobile-menu');
      this.userMenuToggle = $('.splms-user-menu-toggle');
      this.userDropdown = $('.splms-user-dropdown');
      this.notificationBtn = $('.splms-notification-btn');
      this.notificationDropdown = $('.splms-notifications-dropdown');
      this.body = $('body');
      this.isScrolled = false;
      this.scrollThreshold = 50;
      this.init();
    }

    /**
           * Initialize all header interactions
           */
    return _createClass(HeaderInteractions, [{
      key: "init",
      value: function init() {
        this.bindEvents();
        this.handleScrollEffect();
        this.setupAccessibility();
      }

      /**
             * Bind all event listeners
             */
    }, {
      key: "bindEvents",
      value: function bindEvents() {
        var _this = this;
        // Mobile menu toggle
        this.mobileToggle.on('click', function (e) {
          e.preventDefault();
          _this.toggleMobileMenu();
        });

        // User menu toggle
        this.userMenuToggle.on('click', function (e) {
          e.preventDefault();
          _this.toggleUserMenu();
        });

        // Notification toggle - now handled by SPLMSNotifications class

        // Close dropdowns when clicking outside
        $(document).on('click', function (e) {
          _this.handleOutsideClick(e);
        });

        // Handle escape key
        $(document).on('keydown', function (e) {
          if (e.key === 'Escape') {
            _this.handleEscapeKey();
          }
        });

        // Handle scroll for header effects
        $(window).on('scroll', function () {
          _this.handleScrollEffect();
        });

        // Handle window resize
        $(window).on('resize', function () {
          _this.handleResize();
        });
      }

      /**
             * Toggle mobile menu
             */
    }, {
      key: "toggleMobileMenu",
      value: function toggleMobileMenu() {
        var _this2 = this;
        var isOpen = this.mobileToggle.attr('aria-expanded') === 'true';
        this.mobileToggle.attr('aria-expanded', !isOpen);
        this.mobileMenu.toggleClass('is-open', !isOpen);
        this.body.toggleClass('mobile-menu-open', !isOpen);

        // Prevent body scroll when menu is open
        if (!isOpen) {
          this.body.css('overflow', 'hidden');
        } else {
          this.body.css('overflow', '');
        }

        // Focus management
        if (!isOpen) {
          setTimeout(function () {
            _this2.mobileMenu.find('a').first().focus();
          }, 100);
        }
      }

      /**
             * Toggle user dropdown menu
             */
    }, {
      key: "toggleUserMenu",
      value: function toggleUserMenu() {
        var _this3 = this;
        var isOpen = this.userMenuToggle.attr('aria-expanded') === 'true';

        // Close notifications if open
        this.closeNotifications();
        this.userMenuToggle.attr('aria-expanded', !isOpen);
        this.userDropdown.toggleClass('is-open', !isOpen);

        // Focus management
        if (!isOpen) {
          setTimeout(function () {
            _this3.userDropdown.find('a').first().focus();
          }, 100);
        }
      }

      /**
             * Notification functions moved to SPLMSNotifications class for unified handling
             */

      /**
             * Notification count functions moved to SPLMSNotifications class
             */

      /**
             * Close user menu
             */
    }, {
      key: "closeUserMenu",
      value: function closeUserMenu() {
        this.userMenuToggle.attr('aria-expanded', 'false');
        this.userDropdown.removeClass('is-open');
      }

      /**
             * Close notifications - delegated to SPLMSNotifications class
             */
    }, {
      key: "closeNotifications",
      value: function closeNotifications() {
        if (window.splmsNotifications) {
          window.splmsNotifications.closeDropdown();
        }
      }

      /**
             * Close mobile menu
             */
    }, {
      key: "closeMobileMenu",
      value: function closeMobileMenu() {
        this.mobileToggle.attr('aria-expanded', 'false');
        this.mobileMenu.removeClass('is-open');
        this.body.removeClass('mobile-menu-open').css('overflow', '');
      }

      /**
             * Handle clicks outside dropdowns
             */
    }, {
      key: "handleOutsideClick",
      value: function handleOutsideClick(e) {
        var target = $(e.target);

        // Check if click is outside user menu
        if (!target.closest('.splms-user-menu').length) {
          this.closeUserMenu();
        }

        // Check if click is outside mobile menu
        if (!target.closest('.splms-mobile-menu, .splms-mobile-menu-toggle').length) {
          this.closeMobileMenu();
        }

        // Notifications are handled by SPLMSNotifications class
      }

      /**
             * Handle escape key press
             */
    }, {
      key: "handleEscapeKey",
      value: function handleEscapeKey() {
        this.closeUserMenu();
        this.closeNotifications();
        this.closeMobileMenu();
      }

      /**
             * Handle scroll effect on header
             */
    }, {
      key: "handleScrollEffect",
      value: function handleScrollEffect() {
        var scrollTop = $(window).scrollTop();
        var shouldAddClass = scrollTop > this.scrollThreshold;
        if (shouldAddClass !== this.isScrolled) {
          this.isScrolled = shouldAddClass;
          this.header.toggleClass('is-scrolled', this.isScrolled);
        }
      }

      /**
             * Handle window resize
             */
    }, {
      key: "handleResize",
      value: function handleResize() {
        var windowWidth = $(window).width();

        // Close mobile menu on larger screens
        if (windowWidth > 1024) {
          this.closeMobileMenu();
        }

        // Close dropdowns on mobile
        if (windowWidth <= 768) {
          this.closeUserMenu();
          this.closeNotifications();
        }
      }

      /**
             * Setup accessibility features
             */
    }, {
      key: "setupAccessibility",
      value: function setupAccessibility() {
        // Add ARIA labels and roles
        this.userDropdown.attr({
          'role': 'menu',
          'aria-labelledby': this.userMenuToggle.attr('id') || 'user-menu-toggle'
        });
        this.notificationDropdown.attr({
          'role': 'menu',
          'aria-labelledby': this.notificationBtn.attr('id') || 'notification-btn'
        });

        // Add keyboard navigation for dropdowns
        this.setupKeyboardNavigation();
      }

      /**
             * Setup keyboard navigation
             */
    }, {
      key: "setupKeyboardNavigation",
      value: function setupKeyboardNavigation() {
        var _this4 = this;
        // User dropdown navigation
        this.userDropdown.on('keydown', 'a', function (e) {
          _this4.handleDropdownKeyNavigation(e, _this4.userDropdown);
        });

        // Notification dropdown navigation
        this.notificationDropdown.on('keydown', 'a, button', function (e) {
          _this4.handleDropdownKeyNavigation(e, _this4.notificationDropdown);
        });

        // Mobile menu navigation
        this.mobileMenu.on('keydown', 'a', function (e) {
          if (e.key === 'Tab') {
            _this4.handleMobileMenuTabNavigation(e);
          }
        });
      }

      /**
             * Handle keyboard navigation in dropdowns
             */
    }, {
      key: "handleDropdownKeyNavigation",
      value: function handleDropdownKeyNavigation(e, dropdown) {
        var focusableElements = dropdown.find('a, button').not(':disabled');
        var currentIndex = focusableElements.index(e.target);
        switch (e.key) {
          case 'ArrowDown':
            {
              e.preventDefault();
              var nextIndex = (currentIndex + 1) % focusableElements.length;
              focusableElements.eq(nextIndex).focus();
              break;
            }
          case 'ArrowUp':
            {
              e.preventDefault();
              var prevIndex = (currentIndex - 1 + focusableElements.length) % focusableElements.length;
              focusableElements.eq(prevIndex).focus();
              break;
            }
          case 'Home':
            e.preventDefault();
            focusableElements.first().focus();
            break;
          case 'End':
            e.preventDefault();
            focusableElements.last().focus();
            break;
        }
      }

      /**
             * Handle tab navigation in mobile menu
             */
    }, {
      key: "handleMobileMenuTabNavigation",
      value: function handleMobileMenuTabNavigation(e) {
        var focusableElements = this.mobileMenu.find('a, button').not(':disabled');
        var firstElement = focusableElements.first();
        var lastElement = focusableElements.last();
        if (e.shiftKey && e.target === firstElement[0]) {
          e.preventDefault();
          lastElement.focus();
        } else if (!e.shiftKey && e.target === lastElement[0]) {
          e.preventDefault();
          firstElement.focus();
        }
      }

      /**
             * Trigger modal (for footer links and dashboard)
             */
      // triggerModal removed
    }]);
  }();
  /**
      * Initialize header interactions when DOM is ready
      */
  $(document).ready(function () {
    new HeaderInteractions();
  });
})(jQuery);

/***/ },

/***/ "./src/js/frontend/components/notifications.js"
/*!*****************************************************!*\
  !*** ./src/js/frontend/components/notifications.js ***!
  \*****************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _react_core_utility_url__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../react-core/utility/url */ "./src/js/react-core/utility/url.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }


/**
 * SkillPulse LMS Unified Notifications System
 * 
 * Handles notifications for nav menu, header, and standalone usage
 * Single source of truth for all notification functionality
 * 
 * @package SkillPulse_LMS
 * @since 1.0.0
 */
var SPLMSNotifications = /*#__PURE__*/function () {
  function SPLMSNotifications() {
    _classCallCheck(this, SPLMSNotifications);
    this.notifications = [];
    this.unreadCount = 0;
    this.isLoading = false;
    this.isOpen = false;

    // Pagination
    this.currentPage = 1;
    this.perPage = 20;
    this.totalPages = 1;
    this.hasMore = false;

    // Filtering
    this.currentFilter = 'all'; // 'all', 'unread', 'read', 'type', 'event'
    this.filterType = null;
    this.filterEvent = null;

    // Lazy loading
    this.loadedPages = new Set();
    this.cache = {
      notifications: [],
      timestamp: null,
      ttl: 60000 // 1 minute cache
    };

    // Element selectors for different contexts
    this.selectors = {
      // Nav menu elements (primary)
      navBell: '.splms-nav-notification-btn',
      navDropdown: '#splms-nav-notifications-dropdown',
      navCount: '.splms-nav-notification-count',
      // Header elements
      headerBell: '.splms-notification-btn',
      headerDropdown: '.splms-notifications-dropdown',
      headerCount: '.splms-notification-count',
      // Generic elements
      anyBell: '.splms-notification-btn, .splms-nav-notification-btn',
      anyDropdown: '.splms-notifications-dropdown, #splms-nav-notifications-dropdown',
      anyCount: '.splms-notification-count, .splms-nav-notification-count'
    };
    this.init();
  }
  return _createClass(SPLMSNotifications, [{
    key: "init",
    value: function init() {
      var _this = this;
      jQuery(document).ready(function () {
        return _this.setup();
      });
    }
  }, {
    key: "setup",
    value: function setup() {
      this.findElements();
      this.setupEventListeners();
    }
  }, {
    key: "findElements",
    value: function findElements() {
      // Find notification elements in any context
      this.$bellElement = jQuery(this.selectors.anyBell);
      this.$dropdownElement = jQuery(this.selectors.anyDropdown);
      this.$countElement = jQuery(this.selectors.anyCount);
    }
  }, {
    key: "setupEventListeners",
    value: function setupEventListeners() {
      var _this2 = this;
      if (!this.$bellElement || this.$bellElement.length === 0) return;

      // Handle notification bell clicks (both nav menu and header)
      jQuery(document).on('click', this.selectors.anyBell, function (e) {
        e.preventDefault();
        e.stopPropagation();
        _this2.toggleDropdown();
      });

      // Handle mark all as read
      jQuery(document).on('click', '.splms-mark-all-read', function (e) {
        e.preventDefault();
        _this2.markAllAsRead();
      });

      // Close dropdown when clicking outside
      jQuery(document).on('click', function (e) {
        if (!jQuery(e.target).closest('.splms-nav-notifications-trigger, .splms-notifications-trigger').length) {
          _this2.closeDropdown();
        }
      });

      // Close dropdown on escape key
      jQuery(document).on('keydown', function (e) {
        if (e.key === 'Escape' && _this2.isOpen) {
          _this2.closeDropdown();
        }
      });

      // Handle notification item clicks
      jQuery(document).on('click', '.splms-notification-link', function (e) {
        var $item = jQuery(e.currentTarget).closest('.splms-notification-item');
        var notificationId = $item.data('notification-id');
        var url = jQuery(e.currentTarget).data('url');
        if (!$item.hasClass('is-read')) {
          _this2.markNotificationAsRead(notificationId, $item);
        }
        if (url && url !== '#') {
          window.location.href = url;
        }
      });
    }
  }, {
    key: "toggleDropdown",
    value: function toggleDropdown() {
      if (this.isOpen) {
        this.closeDropdown();
      } else {
        this.openDropdown();
      }
    }
  }, {
    key: "openDropdown",
    value: function openDropdown() {
      if (!this.$dropdownElement || this.$dropdownElement.length === 0) return;

      // Handle both nav menu and header dropdowns
      this.$dropdownElement.addClass('is-open').show();
      this.isOpen = true;

      // Load notifications if not already loaded or cache expired
      var cacheValid = this.cache.timestamp && Date.now() - this.cache.timestamp < this.cache.ttl;
      if (!this.$dropdownElement.data('loaded') || !cacheValid) {
        this.fetchNotifications();
      } else {
        // Use cached data
        this.renderNotifications();
      }
    }
  }, {
    key: "closeDropdown",
    value: function closeDropdown() {
      if (!this.$dropdownElement || this.$dropdownElement.length === 0) return;
      this.$dropdownElement.removeClass('is-open').hide();
      this.isOpen = false;
    }
  }, {
    key: "fetchNotifications",
    value: function fetchNotifications() {
      var _frontend$nonces,
        _this3 = this;
      var page = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 1;
      var append = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
      this.isLoading = true;
      if (!append) {
        this.showLoadingState();
      }

      // Use available AJAX data from frontend object
      var frontend = window.splms_frontend;
      if (!frontend || !frontend.ajax_url || !((_frontend$nonces = frontend.nonces) !== null && _frontend$nonces !== void 0 && _frontend$nonces.splms_frontend_nonce)) {
        var _window$console;
        (_window$console = window.console) === null || _window$console === void 0 || _window$console.error('SPLMS: No AJAX data available');
        this.showError('Unable to load notifications. Please refresh the page.');
        this.isLoading = false;
        return;
      }

      // Build request data
      var requestData = {
        action: 'splms_get_notifications',
        nonce: frontend.nonces.splms_frontend_nonce,
        limit: this.perPage,
        offset: (page - 1) * this.perPage
      };

      // Add filters
      if (this.currentFilter === 'unread') {
        requestData.is_read = '0';
      } else if (this.currentFilter === 'read') {
        requestData.is_read = '1';
      }
      if (this.filterType) {
        requestData.type = this.filterType;
      }
      if (this.filterEvent) {
        requestData.event_key = this.filterEvent;
      }
      jQuery.ajax({
        url: frontend.ajax_url,
        type: 'POST',
        data: requestData,
        success: function success(response) {
          if (response.success && response.data) {
            var newNotifications = response.data.notifications || [];
            if (append) {
              // Append to existing notifications
              _this3.notifications = [].concat(_toConsumableArray(_this3.notifications), _toConsumableArray(newNotifications));
            } else {
              // Replace notifications
              _this3.notifications = newNotifications;
            }
            _this3.unreadCount = response.data.unread_count || 0;
            _this3.currentPage = page;
            _this3.totalPages = response.data.pages || 1;
            _this3.hasMore = page < _this3.totalPages;

            // Update cache
            _this3.cache.notifications = _this3.notifications;
            _this3.cache.timestamp = Date.now();
            _this3.renderNotifications();
            _this3.updateBadge();
            _this3.$dropdownElement.data('loaded', true);
            _this3.loadedPages.add(page);
          } else {
            var _window$console2;
            (_window$console2 = window.console) === null || _window$console2 === void 0 || _window$console2.error('Failed to fetch notifications:', response.data || response);
            var errorMessage = response.data && typeof response.data === 'string' ? response.data : 'Failed to load notifications';
            _this3.showError(errorMessage);
          }
        },
        error: function error(xhr, status, _error) {
          var _window$console3;
          (_window$console3 = window.console) === null || _window$console3 === void 0 || _window$console3.error('Error fetching notifications:', _error, xhr);
          var errorMessage = 'Network error occurred';
          if (xhr.responseJSON && xhr.responseJSON.data) {
            errorMessage = xhr.responseJSON.data;
          } else if (xhr.status === 0) {
            errorMessage = 'Connection error. Please check your internet connection.';
          }
          _this3.showError(errorMessage);
        },
        complete: function complete() {
          _this3.isLoading = false;
        }
      });
    }

    /**
        * Load more notifications (pagination)
        */
  }, {
    key: "loadMore",
    value: function loadMore() {
      if (this.isLoading || !this.hasMore) return;
      var nextPage = this.currentPage + 1;
      this.fetchNotifications(nextPage, true);
    }

    /**
        * Set filter and reload notifications
        * @param {string} filter - Filter type: 'all', 'unread', 'read'
        * @param {string} type - Optional notification type filter
        * @param {string} event - Optional event key filter
        */
  }, {
    key: "setFilter",
    value: function setFilter(filter) {
      var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
      var event = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
      this.currentFilter = filter;
      this.filterType = type;
      this.filterEvent = event;
      this.currentPage = 1;
      this.loadedPages.clear();
      this.fetchNotifications(1, false);
    }
  }, {
    key: "markNotificationAsRead",
    value: function markNotificationAsRead(notificationId) {
      var _frontend$nonces2,
        _this4 = this;
      var $item = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
      var frontend = window.splms_frontend;
      if (!frontend || !frontend.ajax_url || !((_frontend$nonces2 = frontend.nonces) !== null && _frontend$nonces2 !== void 0 && _frontend$nonces2.splms_frontend_nonce)) {
        var _window$console4;
        (_window$console4 = window.console) === null || _window$console4 === void 0 || _window$console4.error('SPLMS: No AJAX data available');
        return;
      }
      if (!notificationId) {
        var _window$console5;
        (_window$console5 = window.console) === null || _window$console5 === void 0 || _window$console5.error('SPLMS: Notification ID is required');
        return;
      }

      // Convert notificationId to string for comparison (PHP returns IDs as strings)
      var notificationIdStr = String(notificationId);
      jQuery.ajax({
        url: frontend.ajax_url,
        type: 'POST',
        data: {
          action: 'splms_mark_notification_read',
          nonce: frontend.nonces.splms_frontend_nonce,
          notification_id: notificationId
        },
        success: function success(response) {
          if (response.success) {
            // Update local state - handle both string and number IDs
            _this4.notifications = _this4.notifications.map(function (notification) {
              var notifId = String(notification.id);
              return notifId === notificationIdStr ? _objectSpread(_objectSpread({}, notification), {}, {
                is_read: true
              }) : notification;
            });
            _this4.unreadCount = Math.max(0, _this4.unreadCount - 1);
            _this4.updateBadge();

            // Update UI if item element provided
            if ($item) {
              $item.removeClass('is-unread').addClass('is-read');
              $item.find('.splms-notification-status-dot').remove();
            }
          } else {
            var _window$console6;
            (_window$console6 = window.console) === null || _window$console6 === void 0 || _window$console6.error('Failed to mark notification as read:', response.data || response);
          }
        },
        error: function error(xhr, status, _error2) {
          var _window$console7;
          (_window$console7 = window.console) === null || _window$console7 === void 0 || _window$console7.error('Error marking notification as read:', _error2, xhr);
        }
      });
    }
  }, {
    key: "markAllAsRead",
    value: function markAllAsRead() {
      var _frontend$nonces3,
        _this5 = this;
      if (this.unreadCount === 0) return;
      var frontend = window.splms_frontend;
      if (!frontend || !frontend.ajax_url || !((_frontend$nonces3 = frontend.nonces) !== null && _frontend$nonces3 !== void 0 && _frontend$nonces3.splms_frontend_nonce)) {
        var _window$console8;
        (_window$console8 = window.console) === null || _window$console8 === void 0 || _window$console8.error('SPLMS: No AJAX data available');
        return;
      }

      // Disable button during request
      var $button = jQuery('.splms-mark-all-read');
      var originalText = $button.text();
      $button.prop('disabled', true).text('Marking...');
      jQuery.ajax({
        url: frontend.ajax_url,
        type: 'POST',
        data: {
          action: 'splms_mark_all_notifications_read',
          nonce: frontend.nonces.splms_frontend_nonce
        },
        success: function success(response) {
          if (response.success) {
            // Update all notifications to read
            _this5.notifications = _this5.notifications.map(function (n) {
              return _objectSpread(_objectSpread({}, n), {}, {
                is_read: true
              });
            });
            _this5.unreadCount = 0;
            _this5.updateBadge();
            _this5.renderNotifications();
          } else {
            var _window$console9;
            (_window$console9 = window.console) === null || _window$console9 === void 0 || _window$console9.error('Failed to mark all notifications as read:', response.data || response);
            $button.prop('disabled', false).text(originalText);
          }
        },
        error: function error(xhr, status, _error3) {
          var _window$console0;
          (_window$console0 = window.console) === null || _window$console0 === void 0 || _window$console0.error('Error marking all notifications as read:', _error3, xhr);
          $button.prop('disabled', false).text(originalText);
        }
      });
    }
  }, {
    key: "renderNotifications",
    value: function renderNotifications() {
      var _this6 = this;
      if (!this.$dropdownElement || this.$dropdownElement.length === 0) return;
      var html = "\n            <div class=\"splms-notifications-header\">\n                <div class=\"splms-notifications-title\">\n                    <h3>Notifications</h3>\n                    ".concat(this.unreadCount > 0 ? "<span class=\"splms-unread-count\">".concat(this.unreadCount, "</span>") : '', "\n                </div>\n                <div class=\"splms-notifications-actions\">\n                    ").concat(this.unreadCount > 0 ? '<button class="splms-mark-all-read">Mark all read</button>' : '', "\n                </div>\n            </div>\n            <div class=\"splms-notifications-filters\">\n                <button class=\"splms-filter-btn ").concat(this.currentFilter === 'all' ? 'is-active' : '', "\" data-filter=\"all\">All</button>\n                <button class=\"splms-filter-btn ").concat(this.currentFilter === 'unread' ? 'is-active' : '', "\" data-filter=\"unread\">Unread</button>\n                <button class=\"splms-filter-btn ").concat(this.currentFilter === 'read' ? 'is-active' : '', "\" data-filter=\"read\">Read</button>\n            </div>\n            <div class=\"splms-notifications-list\">\n        ");
      if (this.notifications.length > 0) {
        this.notifications.forEach(function (notification) {
          var timeAgo = _this6.formatTimeAgo(notification.created_at);
          var typeIcon = _this6.getNotificationIcon(notification.type);
          var typeClass = _this6.getNotificationTypeClass(notification.type);
          // Generate course URL - check meta first, then build from course_id
          var courseUrl = '#';
          if (notification.meta && notification.meta.course_url) {
            courseUrl = notification.meta.course_url;
          } else if (notification.course_id) {
            // Try to get URL from window if available, otherwise use simple pattern
            var baseUrl = (0,_react_core_utility_url__WEBPACK_IMPORTED_MODULE_0__.getSiteUrl)() || '';
            courseUrl = "".concat(baseUrl, "/course/").concat(notification.course_id, "/");
          }
          html += "\n                    <div class=\"splms-notification-item ".concat(!notification.is_read ? 'is-unread' : 'is-read', "\" data-notification-id=\"").concat(notification.id, "\">\n                        <div class=\"splms-notification-link\" data-url=\"").concat(courseUrl, "\">\n                            <div class=\"splms-notification-icon-wrapper\">\n                                <div class=\"splms-notification-icon ").concat(typeClass, "\">\n                                    ").concat(typeIcon, "\n                                </div>\n                            </div>\n                            <div class=\"splms-notification-content\">\n                                <div class=\"splms-notification-main\">\n                                    <h4 class=\"splms-notification-title\">").concat(notification.title, "</h4>\n                                    <p class=\"splms-notification-message\">").concat(notification.message, "</p>\n                                </div>\n                                <div class=\"splms-notification-meta\">\n                                    <span class=\"splms-notification-time\">").concat(timeAgo, "</span>\n                                    <span class=\"splms-notification-type\">").concat(_this6.getNotificationTypeLabel(notification.type), "</span>\n                                </div>\n                            </div>\n                            ").concat(!notification.is_read ? '<div class="splms-notification-status-dot"></div>' : '', "\n                        </div>\n                    </div>\n                ");
        });
      } else {
        html += "\n                <div class=\"splms-notifications-empty\">\n                    <div class=\"splms-notifications-empty-icon\">\n                        <svg width=\"48\" height=\"48\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n                            <path d=\"M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n                            <path d=\"M13.73 21a2 2 0 0 1-3.46 0\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n                        </svg>\n                    </div>\n                    <h4>No notifications yet</h4>\n                    <p>We'll notify you when something important happens.</p>\n                </div>\n            ";
      }
      html += '</div>';

      // Add pagination controls if there are more pages
      if (this.hasMore) {
        html += "\n                <div class=\"splms-notifications-footer\">\n                    <button class=\"splms-load-more-btn\" ".concat(this.isLoading ? 'disabled' : '', ">\n                        ").concat(this.isLoading ? 'Loading...' : 'Load More', "\n                    </button>\n                </div>\n            ");
      }
      this.$dropdownElement.html(html);

      // Setup filter button handlers
      this.setupFilterHandlers();

      // Setup load more handler
      if (this.hasMore) {
        this.setupLoadMoreHandler();
      }
    }
  }, {
    key: "setupFilterHandlers",
    value: function setupFilterHandlers() {
      var _this7 = this;
      jQuery(document).off('click', '.splms-filter-btn').on('click', '.splms-filter-btn', function (e) {
        e.preventDefault();
        var filter = jQuery(e.currentTarget).data('filter');
        if (filter && filter !== _this7.currentFilter) {
          jQuery('.splms-filter-btn').removeClass('is-active');
          jQuery(e.currentTarget).addClass('is-active');
          _this7.setFilter(filter);
        }
      });
    }
  }, {
    key: "setupLoadMoreHandler",
    value: function setupLoadMoreHandler() {
      var _this8 = this;
      jQuery(document).off('click', '.splms-load-more-btn').on('click', '.splms-load-more-btn', function (e) {
        e.preventDefault();
        if (!_this8.isLoading && _this8.hasMore) {
          _this8.loadMore();
        }
      });
    }
  }, {
    key: "getNotificationIcon",
    value: function getNotificationIcon(type) {
      var icons = {
        'system': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 2C13.1 2 14 2.9 14 4C14 4.74 13.6 5.39 13 5.73V7C13 10.76 15.15 14 18 16V17H6V16C8.85 14 11 10.76 11 7V5.73C10.4 5.39 10 4.74 10 4C10 2.9 10.9 2 12 2ZM10 21C10 22.1 10.9 23 12 23S14 22.1 14 21H10Z" fill="currentColor"/></svg>',
        'success': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" fill="currentColor"/><path d="M9 12l2 2 4-4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'course': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M22 10v6M2 10l10-5 10 5-10 5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 12v5c3 3 9 3 12 0v-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'info': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 16v-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 8h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
        'warning': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" stroke="currentColor" stroke-width="2"/><line x1="12" y1="9" x2="12" y2="13" stroke="currentColor" stroke-width="2"/><line x1="12" y1="17" x2="12.01" y2="17" stroke="currentColor" stroke-width="2"/></svg>',
        'error': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2"/><line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2"/></svg>'
      };
      return icons[type] || icons['info'];
    }
  }, {
    key: "getNotificationTypeClass",
    value: function getNotificationTypeClass(type) {
      var classes = {
        'system': 'system-update',
        'success': 'course-completed',
        'course': 'course-notification',
        'info': 'info-notification',
        'warning': 'warning-notification',
        'error': 'error-notification'
      };
      return classes[type] || 'info-notification';
    }
  }, {
    key: "getNotificationTypeLabel",
    value: function getNotificationTypeLabel(type) {
      var labels = {
        'system': 'Info',
        'success': 'Success',
        'course': 'Course',
        'info': 'Info',
        'warning': 'Warning',
        'error': 'Error'
      };
      return labels[type] || 'Info';
    }
  }, {
    key: "formatTimeAgo",
    value: function formatTimeAgo(dateString) {
      var now = new Date();
      var date = new Date(dateString);
      var diff = Math.floor((now - date) / 1000);
      if (diff < 60) return 'Just now';
      if (diff < 3600) return "".concat(Math.floor(diff / 60), " minutes ago");
      if (diff < 86400) return "".concat(Math.floor(diff / 3600), " hours ago");
      if (diff < 604800) return "".concat(Math.floor(diff / 86400), " days ago");
      return date.toLocaleDateString();
    }
  }, {
    key: "updateBadge",
    value: function updateBadge() {
      if (!this.$countElement || this.$countElement.length === 0) return;
      if (this.unreadCount > 0) {
        this.$countElement.text(this.unreadCount).show();
      } else {
        this.$countElement.hide();
      }
    }

    /**
        * Refresh unread count badge without fetching all notifications
        * Useful for periodic updates or after external changes
        */
  }, {
    key: "refreshUnreadCount",
    value: function refreshUnreadCount() {
      var _frontend$nonces4,
        _this9 = this;
      var frontend = window.splms_frontend;
      if (!frontend || !frontend.ajax_url || !((_frontend$nonces4 = frontend.nonces) !== null && _frontend$nonces4 !== void 0 && _frontend$nonces4.splms_frontend_nonce)) {
        return;
      }

      // Fetch only unread count by requesting unread notifications with limit 1
      jQuery.ajax({
        url: frontend.ajax_url,
        type: 'POST',
        data: {
          action: 'splms_get_notifications',
          nonce: frontend.nonces.splms_frontend_nonce,
          limit: 1,
          offset: 0,
          is_read: '0'
        },
        success: function success(response) {
          if (response.success && response.data) {
            // The unread_count is included in the response
            _this9.unreadCount = response.data.unread_count || 0;
            _this9.updateBadge();
          }
        },
        error: function error(xhr, status, _error4) {
          var _window$console1;
          // Silently fail for background refresh
          (_window$console1 = window.console) === null || _window$console1 === void 0 || _window$console1.debug('Failed to refresh unread count:', _error4);
        }
      });
    }
  }, {
    key: "showLoadingState",
    value: function showLoadingState() {
      if (!this.$dropdownElement || this.$dropdownElement.length === 0) return;
      this.$dropdownElement.html("\n            <div class=\"splms-notification-loading\">\n                <div class=\"loading-spinner\"></div>\n                <span>Loading notifications...</span>\n            </div>\n        ");
    }
  }, {
    key: "showError",
    value: function showError(message) {
      if (!this.$dropdownElement || this.$dropdownElement.length === 0) return;
      this.$dropdownElement.html("\n            <div class=\"splms-notification-error\">\n                <span>".concat(message, "</span>\n            </div>\n        "));
    }
  }]);
}(); // Initialize notifications when DOM is ready
jQuery(document).ready(function () {
  window.splmsNotifications = new SPLMSNotifications();
});

// Re-initialize when content is dynamically loaded
jQuery(document).on('splms_content_loaded', function () {
  if (window.splmsNotifications) {
    window.splmsNotifications.setup();
  }
});

/***/ },

/***/ "./src/js/frontend/components/ui-enhancements.js"
/*!*******************************************************!*\
  !*** ./src/js/frontend/components/ui-enhancements.js ***!
  \*******************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SPLMSUIEnhancements: () => (/* binding */ SPLMSUIEnhancements)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * SkillPulse LMS UI Enhancements
 * Handles lazy loading, smooth scroll, responsive features, and other UI improvements
 */

var SPLMSUIEnhancements = /*#__PURE__*/function () {
  function SPLMSUIEnhancements() {
    _classCallCheck(this, SPLMSUIEnhancements);
    this.init();
  }
  return _createClass(SPLMSUIEnhancements, [{
    key: "init",
    value: function init() {
      this.bindEvents();
      this.initLazyLoading();
      this.initSmoothScroll();
      this.initLoadingStates();
      this.initResponsiveNav();
      this.initSidebarEnhancements();
      this.initCollapsibleSections();
      this.addNotificationStyles();
      this.initMobileStickyBar();
    }
  }, {
    key: "bindEvents",
    value: function bindEvents() {
      // Form submissions
      // jQuery('form').on('submit', this.handleFormSubmit.bind(this));

      // AJAX buttons
      // jQuery(document).on('click', '[data-ajax="true"]', this.handleAjaxButton.bind(this));

      // Sidebar enhancements
      jQuery('.course-info-widget .info-item').on('mouseenter', this.handleInfoItemHover.bind(this));
      jQuery('.course-info-widget .info-item').on('mouseleave', this.handleInfoItemLeave.bind(this));
      jQuery('.course-categories-widget .category-item').on('click', this.handleCategoryClick.bind(this));
      jQuery('.course-tags-widget .tag-link').on('click', this.handleTagClick.bind(this));
    }
  }, {
    key: "initLazyLoading",
    value: function initLazyLoading() {
      if ('IntersectionObserver' in window) {
        var imageObserver = new IntersectionObserver(function (entries, observer) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              var img = entry.target;
              var src = img.dataset.src;
              if (src) {
                img.src = src;
                img.classList.remove('lazy');
                img.classList.add('loaded');
                observer.unobserve(img);
              }
            }
          });
        });
        document.querySelectorAll('img[data-src]').forEach(function (img) {
          imageObserver.observe(img);
        });
      }
    }
  }, {
    key: "initSmoothScroll",
    value: function initSmoothScroll() {
      jQuery('a[href^="#"]').on('click', function (e) {
        e.preventDefault();
        var target = jQuery(this.getAttribute('href'));
        if (target.length) {
          jQuery('html, body').animate({
            scrollTop: target.offset().top - 100
          }, 800);
        }
      });
    }
  }, {
    key: "initLoadingStates",
    value: function initLoadingStates() {
      // Loading states are handled in bindEvents
    }
  }, {
    key: "initResponsiveNav",
    value: function initResponsiveNav() {
      var navTabs = jQuery('.nav-tabs');
      if (navTabs.length && window.innerWidth <= 768) {
        navTabs.wrap('<div class="nav-tabs-wrapper"></div>');

        // Add swipe functionality for mobile
        var startX = 0;
        var scrollLeft = 0;
        navTabs.on('touchstart', function (e) {
          startX = e.touches[0].pageX - navTabs.offset().left;
          scrollLeft = navTabs.scrollLeft();
        });
        navTabs.on('touchmove', function (e) {
          e.preventDefault();
          var x = e.touches[0].pageX - navTabs.offset().left;
          var walk = (x - startX) * 2;
          navTabs.scrollLeft(scrollLeft - walk);
        });
      }
    }
  }, {
    key: "initSidebarEnhancements",
    value: function initSidebarEnhancements() {
      // Enhanced sidebar functionality is handled in bindEvents
    }
  }, {
    key: "initCollapsibleSections",
    value: function initCollapsibleSections() {
      // Handle collapsible lesson attachments
      jQuery(document).on('click', '.splms-lesson-attachments__toggle', function (e) {
        e.preventDefault();
        var $toggle = jQuery(this);
        var $content = $toggle.siblings('.splms-lesson-attachments__content');
        var isExpanded = $toggle.attr('aria-expanded') === 'true';
        if (isExpanded) {
          $toggle.attr('aria-expanded', 'false');
          $content.removeClass('is-expanded');
        } else {
          $toggle.attr('aria-expanded', 'true');
          $content.addClass('is-expanded');
        }
      });
    }

    // handleFormSubmit(e) {
    //     jQuery(e.currentTarget).addClass('loading');
    // }

    // handleAjaxButton(e) {
    //     jQuery(e.currentTarget).addClass('loading');
    // }
  }, {
    key: "handleInfoItemHover",
    value: function handleInfoItemHover(e) {
      jQuery(e.currentTarget).find('.info-icon').addClass('pulse');
    }
  }, {
    key: "handleInfoItemLeave",
    value: function handleInfoItemLeave(e) {
      jQuery(e.currentTarget).find('.info-icon').removeClass('pulse');
    }
  }, {
    key: "handleCategoryClick",
    value: function handleCategoryClick(e) {
      e.preventDefault();
      var $item = jQuery(e.currentTarget);
      var href = $item.find('.category-link').attr('href');

      // Add loading state
      $item.addClass('loading');

      // Navigate after animation
      setTimeout(function () {
        window.location.href = href;
      }, 200);
    }
  }, {
    key: "handleTagClick",
    value: function handleTagClick(e) {
      var $tag = jQuery(e.currentTarget);

      // Add click animation
      $tag.addClass('clicked');
      setTimeout(function () {
        $tag.removeClass('clicked');
      }, 200);
    }
  }, {
    key: "addNotificationStyles",
    value: function addNotificationStyles() {
      if (!document.querySelector('#splms-notifications-css')) {
        var style = document.createElement('style');
        style.id = 'splms-notifications-css';
        style.textContent = "\n                .splms-notification {\n                    position: fixed;\n                    top: 45px;\n                    right: 24px;\n                    z-index: 10000;\n                    min-width: 320px;\n                    max-width: 420px;\n                    padding: 16px 20px;\n                    border-radius: 12px;\n                    color: white;\n                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;\n                    font-size: 14px;\n                    font-weight: 500;\n                    line-height: 1.4;\n                    display: flex;\n                    align-items: flex-start;\n                    gap: 12px;\n                    transform: translateX(450px) scale(0.95);\n                    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);\n                    box-shadow: var(--splms-shadow-xl, 0 8px 32px rgba(0, 0, 0, 0.12)), var(--splms-shadow-sm, 0 2px 8px rgba(0, 0, 0, 0.08));\n                    backdrop-filter: blur(8px);\n                    border: 1px solid var(--splms-border-alpha, rgba(255, 255, 255, 0.1));\n                }\n                \n                .splms-notification::before {\n                    content: '';\n                    position: absolute;\n                    top: 0;\n                    left: 0;\n                    right: 0;\n                    bottom: 0;\n                    border-radius: 12px;                    \n                    pointer-events: none;\n                }\n                \n                .splms-notification.show {\n                    transform: translateX(0) scale(1);\n                    animation: slideInBounce 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;\n                }\n                \n                .splms-notification.success {\n                    background: var(--splms-success, #10b981);\n                    border-color: var(--splms-success-alpha-3, rgba(16, 185, 129, 0.3));\n                }\n                \n                .splms-notification.success::before {\n                    content: '\u2713';\n                    position: absolute;\n                    left: 16px;\n                    top: 50%;\n                    transform: translateY(-50%);\n                    font-size: 16px;\n                    font-weight: bold;\n                    color: white;\n                    z-index: 1;\n                }\n                \n                .splms-notification.success .notification-message {\n                    margin-left: 24px;\n                }\n                \n                .splms-notification.error {\n                    background: var(--splms-danger, #ef4444);\n                    border-color: var(--splms-danger-alpha-3, rgba(239, 68, 68, 0.3));\n                }\n                \n                .splms-notification.error::before {\n                    content: '\u2715';\n                    position: absolute;\n                    left: 16px;\n                    top: 50%;\n                    transform: translateY(-50%);\n                    font-size: 16px;\n                    font-weight: bold;\n                    color: white;\n                    z-index: 1;\n                }\n                \n                .splms-notification.error .notification-message {\n                    margin-left: 24px;\n                }\n                \n                .splms-notification.info {\n                    background: var(--splms-info, #3b82f6);\n                    border-color: var(--splms-info-alpha-3, rgba(59, 130, 246, 0.3));\n                }\n                \n                .splms-notification.info::before {\n                    content: '\u24D8';\n                    position: absolute;\n                    left: 16px;\n                    top: 50%;\n                    transform: translateY(-50%);\n                    font-size: 16px;\n                    font-weight: bold;\n                    color: white;\n                    z-index: 1;\n                }\n                \n                .splms-notification.info .notification-message {\n                    margin-left: 24px;\n                }\n                \n                .splms-notification.warning {\n                    background: var(--splms-warning, #f59e0b);\n                    border-color: var(--splms-warning-alpha-3, rgba(245, 158, 11, 0.3));\n                }\n                \n                .splms-notification.warning::before {\n                    content: '\u26A0';\n                    position: absolute;\n                    left: 16px;\n                    top: 50%;\n                    transform: translateY(-50%);\n                    font-size: 16px;\n                    font-weight: bold;\n                    color: white;\n                    z-index: 1;\n                }\n                \n                .splms-notification.warning .notification-message {\n                    margin-left: 24px;\n                }\n                \n                .notification-message {\n                    flex: 1;\n                    position: relative;\n                    z-index: 1;\n                    text-shadow: var(--splms-text-shadow, 0 1px 2px rgba(0, 0, 0, 0.1));\n                }\n                \n                .notification-close {\n                    background: var(--splms-white-alpha-2, rgba(255, 255, 255, 0.2));\n                    border: none;\n                    color: white;\n                    font-size: 16px;\n                    cursor: pointer;\n                    padding: 4px;\n                    width: 24px;\n                    height: 24px;\n                    border-radius: 6px;\n                    display: flex;\n                    align-items: center;\n                    justify-content: center;\n                    transition: all 0.2s ease;\n                    position: relative;\n                    z-index: 10;\n                    flex-shrink: 0;\n                    margin-top: -2px;\n                    pointer-events: auto;\n                }\n                \n                .notification-close:hover {\n                    background: var(--splms-white-alpha-3, rgba(255, 255, 255, 0.3));\n                    transform: scale(1.1);\n                }\n                \n                .notification-close:active {\n                    transform: scale(0.95);\n                }\n                \n                @keyframes slideInBounce {\n                    0% {\n                        transform: translateX(450px) scale(0.95);\n                        opacity: 0;\n                    }\n                    60% {\n                        transform: translateX(-8px) scale(1.02);\n                        opacity: 1;\n                    }\n                    100% {\n                        transform: translateX(0) scale(1);\n                        opacity: 1;\n                    }\n                }\n                \n                .loading {\n                    opacity: 0.7;\n                    pointer-events: none;\n                }\n                \n                /* Progress bar for auto-dismiss */\n                .splms-notification::after {\n                    content: '';\n                    position: absolute;\n                    bottom: 0;\n                    left: 0;\n                    height: 3px;\n                    background: var(--splms-white-alpha-3, rgba(255, 255, 255, 0.3));\n                    border-radius: 0 0 12px 12px;\n                    animation: progressBar 5s linear;\n                }\n                \n                @keyframes progressBar {\n                    0% { width: 100%; }\n                    100% { width: 0%; }\n                }\n                \n                @media (max-width: 768px) {\n                    .splms-notification {\n                        right: 16px;\n                        left: 16px;\n                        min-width: auto;\n                        max-width: none;\n                        top: 20px;\n                        transform: translateY(-100px) scale(0.95);\n                        font-size: 13px;\n                        padding: 14px 16px;\n                    }\n                    \n                    .splms-notification.show {\n                        transform: translateY(0) scale(1);\n                    }\n                    \n                    .splms-notification::before {\n                        left: 14px;\n                        font-size: 14px;\n                    }\n                    \n                    .splms-notification.success .notification-message,\n                    .splms-notification.error .notification-message,\n                    .splms-notification.info .notification-message,\n                    .splms-notification.warning .notification-message {\n                        margin-left: 20px;\n                    }\n                }\n                \n                @media (max-width: 480px) {\n                    .splms-notification {\n                        right: 12px;\n                        left: 12px;\n                        top: 16px;\n                        padding: 12px 14px;\n                        font-size: 12px;\n                    }\n                    \n                    .notification-close {\n                        width: 20px;\n                        height: 20px;\n                        font-size: 14px;\n                    }\n                }\n            ";
        document.head.appendChild(style);
      }
    }

    /**
     * Initialize mobile sticky purchase bar toggle.
     * Shows/hides the sticky bar based on whether the purchase card is visible.
     */
  }, {
    key: "initMobileStickyBar",
    value: function initMobileStickyBar() {
      var stickyBar = document.querySelector('.splms-mobile-purchase-bar');
      var purchaseCard = document.querySelector('.splms-section-purchase-card');
      if (stickyBar && purchaseCard && window.innerWidth <= 1024) {
        var observer = new IntersectionObserver(function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              stickyBar.style.display = 'none';
            } else {
              stickyBar.style.display = 'block';
            }
          });
        }, {
          threshold: 0.1
        });
        observer.observe(purchaseCard);
      }
    }
  }]);
}();

/***/ },

/***/ "./src/js/frontend/core/ajax.js"
/*!**************************************!*\
  !*** ./src/js/frontend/core/ajax.js ***!
  \**************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SPLMSFrontendAjax: () => (/* binding */ SPLMSFrontendAjax)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * SkillPulse LMS Frontend AJAX Handler
 * Handles AJAX requests for frontend functionality
 */

var SPLMSFrontendAjax = /*#__PURE__*/function () {
  function SPLMSFrontendAjax() {
    var _frontend$nonces;
    _classCallCheck(this, SPLMSFrontendAjax);
    var frontend = window.splms_frontend || {};
    this.ajaxUrl = frontend.ajax_url;
    this.nonce = ((_frontend$nonces = frontend.nonces) === null || _frontend$nonces === void 0 ? void 0 : _frontend$nonces.splms_nonce) || '';
  }

  /**
      * Make AJAX request
      * @param {Object} options - AJAX options
      * @returns {Promise}
      */
  return _createClass(SPLMSFrontendAjax, [{
    key: "request",
    value: function request(options) {
      var defaults = {
        url: this.ajaxUrl,
        type: 'POST',
        dataType: 'json',
        data: {
          nonce: this.nonce
        }
      };
      var settings = jQuery.extend({}, defaults, options);

      // Add nonce to data if not already present
      if (settings.data && !settings.data.nonce) {
        settings.data.nonce = this.nonce;
      }
      return jQuery.ajax(settings);
    }

    /**
        * Toggle bookmark (for lessons and quizzes)
        * @param {number} itemId - Lesson or Quiz ID
        * @param {string} type - 'lesson' or 'quiz'
        * @returns {Promise}
        */
  }, {
    key: "toggleBookmark",
    value: function toggleBookmark(itemId) {
      var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'lesson';
      var dataKey = type === 'quiz' ? 'quiz_id' : 'lesson_id';
      return this.request({
        data: _defineProperty({
          action: 'splms_toggle_bookmark'
        }, dataKey, itemId)
      });
    }

    /**
        * Enroll in course
        * @param {number} courseId - Course ID
        * @returns {Promise}
        */
  }, {
    key: "enrollCourse",
    value: function enrollCourse(courseId) {
      return this.request({
        data: {
          action: 'splms_enroll_course',
          course_id: courseId
        }
      });
    }

    /**
        * Unenroll from course
        * @param {number} courseId - Course ID
        * @returns {Promise}
        */
  }, {
    key: "unenrollCourse",
    value: function unenrollCourse(courseId) {
      return this.request({
        data: {
          action: 'splms_unenroll_course',
          course_id: courseId
        }
      });
    }

    /**
        * Toggle course wishlist
        * @param {number} courseId - Course ID
        * @returns {Promise}
        */
  }, {
    key: "toggleWishlist",
    value: function toggleWishlist(courseId) {
      return this.request({
        data: {
          action: 'splms_toggle_wishlist',
          course_id: courseId
        }
      });
    }

    /**
        * Get course purchase URL
        * @param {number} courseId - Course ID
        * @returns {Promise}
        */
  }, {
    key: "getCoursePurchaseUrl",
    value: function getCoursePurchaseUrl(courseId) {
      return this.request({
        data: {
          action: 'splms_get_course_purchase_url',
          course_id: courseId
        }
      });
    }

    /**
        * Update user profile
        * @param {FormData} formData - Form data
        * @returns {Promise}
        */
  }, {
    key: "updateProfile",
    value: function updateProfile(formData) {
      formData.append('action', 'splms_update_profile');
      formData.append('nonce', this.nonce);
      return this.request({
        data: formData,
        processData: false,
        contentType: false
      });
    }

    /**
        * Generic form submission
        * @param {string} action - AJAX action
        * @param {FormData|Object} data - Form data or object
        * @returns {Promise}
        */
  }, {
    key: "submitForm",
    value: function submitForm(action, data) {
      if (data instanceof FormData) {
        data.append('action', action);
        data.append('nonce', this.nonce);
        return this.request({
          data: data,
          processData: false,
          contentType: false
        });
      } else {
        return this.request({
          data: _objectSpread({
            action: action
          }, data)
        });
      }
    }

    /**
        * Handle AJAX error
        * @param {Object} xhr - XMLHttpRequest object
        * @param {string} status - Status text
        * @param {string} error - Error message
        */
  }, {
    key: "handleError",
    value: function handleError(xhr, status, error) {
      var _window$console;
      (_window$console = window.console) === null || _window$console === void 0 || _window$console.error('AJAX Error:', {
        status: status,
        error: error,
        response: xhr.responseText
      });

      // Show user-friendly error message
      window.SPLMSCore.helper.showNotification('Something went wrong. Please try again.', 'error');
    }

    /**
        * Signup user
        * @param {FormData} formData - Form data
        * @returns {Promise}
        */
  }, {
    key: "signupUser",
    value: function signupUser(formData) {
      formData.append('action', 'splms_create_signup');
      return this.request({
        data: formData,
        processData: false,
        contentType: false
      });
    }

    /**
        * Forget password
        * @param {FormData} formData - Form data
        * @returns {Promise}
        */
  }, {
    key: "forgotPassword",
    value: function forgotPassword(formData) {
      formData.append('action', 'splms_forgot_password');
      return this.request({
        data: formData,
        processData: false,
        contentType: false
      });
    }
  }, {
    key: "resendActivation",
    value: function resendActivation(formData) {
      formData.append('action', 'splms_resend_activation');
      return this.request({
        data: formData,
        processData: false,
        contentType: false
      });
    }

    /**
        * Reset password
        * @param {FormData} formData - Form data
        * @returns {Promise}
        */
  }, {
    key: "resetPassword",
    value: function resetPassword(formData) {
      formData.append('action', 'splms_reset_password');
      return this.request({
        data: formData,
        processData: false,
        contentType: false
      });
    }

    /**
        * Activate signup
        * @param {FormData} formData - Form data
        * @returns {Promise}
        */
  }, {
    key: "activateSignup",
    value: function activateSignup(formData) {
      formData.append('action', 'splms_activate_signup');
      return this.request({
        data: formData,
        processData: false,
        contentType: false
      });
    }
  }]);
}();

/***/ },

/***/ "./src/js/frontend/core/base-modal.js"
/*!********************************************!*\
  !*** ./src/js/frontend/core/base-modal.js ***!
  \********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SPLMSBaseModal: () => (/* binding */ SPLMSBaseModal)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Base Modal Class - Abstract class for reusable modals
 * 
 * This abstract class provides common modal functionality that can be extended
 * by specific modal implementations. It uses WordPress wp.template() system
 * for rendering and provides a standardized API for modal management.
 *
 * @since 1.0.0
 * @abstract
 */
var SPLMSBaseModal = /*#__PURE__*/function () {
  /**
      * Constructor
      * 
      * @param {Object} config Modal configuration
      * @param {string} config.templateId - The ID for wp.template() (without 'tmpl-' prefix)
      * @param {string} config.modalId - Unique modal identifier for CSS classes
      * @param {Object} config.defaultData - Default data structure for the modal
      */
  function SPLMSBaseModal() {
    var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
    _classCallCheck(this, SPLMSBaseModal);
    if (this.constructor === SPLMSBaseModal) {
      throw new Error('SPLMSBaseModal is abstract and cannot be instantiated directly');
    }
    this.config = _objectSpread({
      templateId: '',
      modalId: '',
      defaultData: {},
      closeOnBackdrop: true,
      closeOnEscape: true
    }, config);
    this.template = null;
    this.modalElement = null;
    this.isOpen = false;
    this.eventHandlers = new Map();
    this.init();
  }

  /**
      * Initialize the modal
      * @protected
      */
  return _createClass(SPLMSBaseModal, [{
    key: "init",
    value: function init() {
      // Initialize wp.template
      this.initTemplate();

      // Setup global event listeners
      this.setupGlobalEvents();

      // Call child class initialization
      if (typeof this.onInit === 'function') {
        this.onInit();
      }
    }

    /**
        * Initialize WordPress template
        * @protected
        */
  }, {
    key: "initTemplate",
    value: function initTemplate() {
      if (!this.config.templateId) {
        throw new Error('templateId is required for modal');
      }
      if (typeof wp === 'undefined' || typeof wp.template === 'undefined') {
        throw new Error('WordPress wp.template is not available');
      }
      this.template = wp.template(this.config.templateId);
    }

    /**
        * Setup global event listeners
        * @protected
        */
  }, {
    key: "setupGlobalEvents",
    value: function setupGlobalEvents() {
      var _this = this;
      // Escape key handler
      if (this.config.closeOnEscape) {
        this.addEventHandler('keydown', function (e) {
          if (e.key === 'Escape' && _this.isOpen) {
            _this.close();
          }
        }, document);
      }
    }

    /**
        * Open the modal
        * 
        * @param {Object} data Data to pass to the template
        * @returns {Promise<void>}
        */
  }, {
    key: "open",
    value: (function () {
      var _open = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
        var data,
          modalData,
          validation,
          modalHtml,
          _window$console,
          _args = arguments,
          _t;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.p = _context.n) {
            case 0:
              data = _args.length > 0 && _args[0] !== undefined ? _args[0] : {};
              _context.p = 1;
              // Merge with default data
              modalData = _objectSpread(_objectSpread({}, this.config.defaultData), data); // Validate data if child class provides validation
              if (!(typeof this.validateData === 'function')) {
                _context.n = 2;
                break;
              }
              validation = this.validateData(modalData);
              if (validation.isValid) {
                _context.n = 2;
                break;
              }
              throw new Error(validation.message || 'Invalid modal data');
            case 2:
              // Generate modal HTML
              modalHtml = this.template(modalData); // Remove existing modal if any
              this.destroy();

              // Create and append modal
              this.createModal(modalHtml);

              // Setup modal-specific events
              this.setupModalEvents();

              // Show modal
              this.show();

              // Call child class hook
              if (!(typeof this.onOpen === 'function')) {
                _context.n = 3;
                break;
              }
              _context.n = 3;
              return this.onOpen(modalData);
            case 3:
              _context.n = 5;
              break;
            case 4:
              _context.p = 4;
              _t = _context.v;
              (_window$console = window.console) === null || _window$console === void 0 || _window$console.error('Error opening modal:', _t);
              this.handleError(_t);
            case 5:
              return _context.a(2);
          }
        }, _callee, this, [[1, 4]]);
      }));
      function open() {
        return _open.apply(this, arguments);
      }
      return open;
    }()
    /**
        * Create modal element and append to DOM
        * @protected
        */
    )
  }, {
    key: "createModal",
    value: function createModal(html) {
      // Remove any existing modals first
      jQuery('.' + this.config.modalId).remove();
      jQuery('.splms-modal').remove(); // Remove all old modals

      // Create modal container
      this.modalElement = jQuery(html);

      // Add base modal classes
      this.modalElement.addClass('splms-modal');
      if (this.config.modalId) {
        this.modalElement.addClass(this.config.modalId);
      }

      // Append to body
      jQuery('body').append(this.modalElement);
    }

    /**
        * Setup modal-specific event handlers
        * @protected
        */
  }, {
    key: "setupModalEvents",
    value: function setupModalEvents() {
      var _this2 = this;
      if (!this.modalElement) return;

      // Close button handler
      this.addEventHandler('click', function () {
        _this2.close();
      }, this.modalElement.find('.splms-modal-close, .modal-close'));

      // Backdrop click handler
      if (this.config.closeOnBackdrop) {
        this.addEventHandler('click', function (e) {
          if (jQuery(e.target).hasClass('modal-overlay') || jQuery(e.target).hasClass('splms-modal')) {
            _this2.close();
          }
        }, this.modalElement);
      }

      // Form submission handler (if modal contains forms)
      this.addEventHandler('submit', function (e) {
        _this2.handleFormSubmit(e);
      }, this.modalElement.find('form'));

      // Call child class event setup
      if (typeof this.setupEvents === 'function') {
        this.setupEvents();
      }
    }

    /**
        * Show the modal with animation
        * @protected
        */
  }, {
    key: "show",
    value: function show() {
      var _this3 = this;
      if (!this.modalElement) return;
      this.modalElement.css('display', 'block');
      jQuery('body').addClass('splms-modal-open');
      this.isOpen = true;

      // Add animation class after display
      requestAnimationFrame(function () {
        _this3.modalElement.addClass('splms-modal-visible is-open');
      });
    }

    /**
        * Close the modal
        * 
        * @returns {Promise<void>}
        */
  }, {
    key: "close",
    value: (function () {
      var _close = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2() {
        var _this4 = this;
        var shouldClose, _window$console2, _t2;
        return _regenerator().w(function (_context2) {
          while (1) switch (_context2.p = _context2.n) {
            case 0:
              if (!(!this.isOpen || !this.modalElement)) {
                _context2.n = 1;
                break;
              }
              return _context2.a(2);
            case 1:
              _context2.p = 1;
              if (!(typeof this.onClose === 'function')) {
                _context2.n = 3;
                break;
              }
              _context2.n = 2;
              return this.onClose();
            case 2:
              shouldClose = _context2.v;
              if (!(shouldClose === false)) {
                _context2.n = 3;
                break;
              }
              return _context2.a(2);
            case 3:
              // Hide modal with animation
              this.modalElement.fadeOut(300, function () {
                _this4.hide();
              });
              _context2.n = 5;
              break;
            case 4:
              _context2.p = 4;
              _t2 = _context2.v;
              (_window$console2 = window.console) === null || _window$console2 === void 0 || _window$console2.error('Error closing modal:', _t2);
              this.hide(); // Force close on error
            case 5:
              return _context2.a(2);
          }
        }, _callee2, this, [[1, 4]]);
      }));
      function close() {
        return _close.apply(this, arguments);
      }
      return close;
    }()
    /**
        * Hide the modal and cleanup
        * @protected
        */
    )
  }, {
    key: "hide",
    value: function hide() {
      if (this.modalElement) {
        this.modalElement.css('display', 'none').removeClass('splms-modal-visible is-open');
      }
      jQuery('body').removeClass('splms-modal-open');
      this.isOpen = false;
    }

    /**
        * Handle form submissions in the modal
        * @protected
        */
  }, {
    key: "handleFormSubmit",
    value: (function () {
      var _handleFormSubmit = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee3(e) {
        return _regenerator().w(function (_context3) {
          while (1) switch (_context3.n) {
            case 0:
              e.preventDefault();
              if (!(typeof this.onFormSubmit === 'function')) {
                _context3.n = 1;
                break;
              }
              _context3.n = 1;
              return this.onFormSubmit(e);
            case 1:
              return _context3.a(2);
          }
        }, _callee3, this);
      }));
      function handleFormSubmit(_x) {
        return _handleFormSubmit.apply(this, arguments);
      }
      return handleFormSubmit;
    }()
    /**
        * Add event handler and track it for cleanup
        * @protected
        */
    )
  }, {
    key: "addEventHandler",
    value: function addEventHandler(event, handler) {
      var element = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : this.modalElement;
      if (!element || !element.length) return;
      var $element = jQuery(element);
      $element.on(event, handler);

      // Track for cleanup
      if (!this.eventHandlers.has($element[0])) {
        this.eventHandlers.set($element[0], []);
      }
      this.eventHandlers.get($element[0]).push({
        event: event,
        handler: handler
      });
    }
    /**
        * Handle errors
        * @protected
        */
  }, {
    key: "handleError",
    value: function handleError(error) {
      var message = error.message || 'An unexpected error occurred';
      window.SPLMSCore.helper.showNotification(message, 'error');
    }

    /**
        * Destroy the modal and cleanup
        */
  }, {
    key: "destroy",
    value: function destroy() {
      // Remove event handlers
      this.eventHandlers.forEach(function (handlers, element) {
        var $element = jQuery(element);
        handlers.forEach(function (_ref) {
          var event = _ref.event,
            handler = _ref.handler;
          $element.off(event, handler);
        });
      });
      this.eventHandlers.clear();

      // Remove modal element
      if (this.modalElement) {
        this.modalElement.remove();
        this.modalElement = null;
      }

      // Reset state
      this.isOpen = false;
      jQuery('body').removeClass('splms-modal-open');

      // Call child class cleanup
      if (typeof this.onDestroy === 'function') {
        this.onDestroy();
      }
    }

    /**
        * Get current modal data from form inputs
        * @protected
        */
  }, {
    key: "getFormData",
    value: function getFormData() {
      if (!this.modalElement) return {};
      var form = this.modalElement.find('form').first();
      if (!form.length) return {};
      var formData = new FormData(form[0]);
      var data = {};
      var _iterator = _createForOfIteratorHelper(formData.entries()),
        _step;
      try {
        for (_iterator.s(); !(_step = _iterator.n()).done;) {
          var _step$value = _slicedToArray(_step.value, 2),
            key = _step$value[0],
            value = _step$value[1];
          data[key] = value;
        }
      } catch (err) {
        _iterator.e(err);
      } finally {
        _iterator.f();
      }
      return data;
    }

    /**
        * Update modal loading state
        * @protected
        */
  }, {
    key: "setLoading",
    value: function setLoading(isLoading) {
      var message = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'Loading...';
      if (!this.modalElement) return;
      var submitBtn = this.modalElement.find('button[type="submit"]');
      if (isLoading) {
        submitBtn.prop('disabled', true);
        if (!submitBtn.data('original-text')) {
          submitBtn.data('original-text', submitBtn.text());
        }
        submitBtn.text(message);
      } else {
        submitBtn.prop('disabled', false);
        var originalText = submitBtn.data('original-text');
        if (originalText) {
          submitBtn.text(originalText);
        }
      }
    }

    // ==========================================
    // ABSTRACT METHODS - Override in child classes
    // ==========================================

    /**
        * Called after modal initialization
        * Override in child classes for specific setup
        * @abstract
        */
  }, {
    key: "onInit",
    value: function onInit() {
      // Override in child classes
    }

    /**
        * Called when modal is opened
        * Override in child classes for specific logic
        * @abstract
        * @param {Object} data Modal data
        */
  }, {
    key: "onOpen",
    value: (function () {
      var _onOpen = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee4() {
        return _regenerator().w(function (_context4) {
          while (1) switch (_context4.n) {
            case 0:
              return _context4.a(2);
          }
        }, _callee4);
      }));
      function onOpen() {
        return _onOpen.apply(this, arguments);
      }
      return onOpen;
    }()
    /**
        * Called when modal is closing
        * Override in child classes for validation
        * Return false to prevent closing
        * @abstract
        * @returns {boolean|Promise<boolean>}
        */
    )
  }, {
    key: "onClose",
    value: (function () {
      var _onClose = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee5() {
        return _regenerator().w(function (_context5) {
          while (1) switch (_context5.n) {
            case 0:
              return _context5.a(2, true);
          }
        }, _callee5);
      }));
      function onClose() {
        return _onClose.apply(this, arguments);
      }
      return onClose;
    }()
    /**
        * Called when modal is destroyed
        * Override in child classes for cleanup
        * @abstract
        */
    )
  }, {
    key: "onDestroy",
    value: function onDestroy() {
      // Override in child classes
    }

    /**
        * Handle form submission
        * Override in child classes for specific form handling
        * @abstract
        * @param {Event} e Form submit event
        */
  }, {
    key: "onFormSubmit",
    value: (function () {
      var _onFormSubmit = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee6() {
        return _regenerator().w(function (_context6) {
          while (1) switch (_context6.n) {
            case 0:
              return _context6.a(2);
          }
        }, _callee6);
      }));
      function onFormSubmit() {
        return _onFormSubmit.apply(this, arguments);
      }
      return onFormSubmit;
    }()
    /**
        * Setup additional event handlers
        * Override in child classes for specific events
        * @abstract
        */
    )
  }, {
    key: "setupEvents",
    value: function setupEvents() {
      // Override in child classes
    }

    /**
        * Validate modal data
        * Override in child classes for data validation
        * @abstract
        * @param {Object} data Data to validate
        * @returns {Object} {isValid: boolean, message?: string}
        */
  }, {
    key: "validateData",
    value: function validateData() {
      // Override in child classes
      return {
        isValid: true
      };
    }
  }]);
}();

/***/ },

/***/ "./src/js/frontend/core/helper.js"
/*!****************************************!*\
  !*** ./src/js/frontend/core/helper.js ***!
  \****************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SPLMSHelper: () => (/* binding */ SPLMSHelper)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * SkillPulse LMS Frontend Helper
 * Common utility functions for frontend functionality
 */

var SPLMSHelper = /*#__PURE__*/function () {
  function SPLMSHelper() {
    _classCallCheck(this, SPLMSHelper);
  } // Helper class doesn't need initialization

  /**
      * Get dynamic REST API URL with fallback logic
      * @param {string} endpoint - Optional endpoint path to append
      * @returns {string} Full REST API URL
      */
  return _createClass(SPLMSHelper, [{
    key: "getRestApiUrl",
    value: function getRestApiUrl() {
      var _window, _window2, _window3;
      var endpoint = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      var apiRoot = ((_window = window) === null || _window === void 0 || (_window = _window.wpApiSettings) === null || _window === void 0 ? void 0 : _window.root) || ((_window2 = window) === null || _window2 === void 0 || (_window2 = _window2.splms_frontend) === null || _window2 === void 0 ? void 0 : _window2.rest_url) || ((_window3 = window) === null || _window3 === void 0 || (_window3 = _window3.SPLMSCore_Data) === null || _window3 === void 0 ? void 0 : _window3.rest_url) || '/wp-json/';
      var base = apiRoot.endsWith('/') ? apiRoot : "".concat(apiRoot, "/");
      var path = endpoint.startsWith('/') ? endpoint.substring(1) : endpoint;
      return "".concat(base).concat(path);
    }

    /**
        * Show notification message
        * @param {string} message - The message to display
        * @param {string} type - The type of notification (success, error, info)
        */
  }, {
    key: "showNotification",
    value: function showNotification(message) {
      var _this = this;
      var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'success';
      // Remove existing notifications
      jQuery('.splms-notification').remove();
      var notification = jQuery("\n            <div class=\"splms-notification ".concat(type, "\">\n                <span class=\"notification-message\">").concat(message, "</span>\n                <button class=\"notification-close\">&times;</button>\n            </div>\n        "));
      jQuery('body').append(notification);
      setTimeout(function () {
        notification.addClass('show');
      }, 100);

      // Auto-hide after 5 seconds
      setTimeout(function () {
        _this.hideNotification(notification);
      }, 5000);

      // Close button
      notification.find('.notification-close').on('click', function () {
        _this.hideNotification(notification);
      });
    }

    /**
        * Hide notification
        * @param {jQuery} $notification - The notification element to hide
        */
  }, {
    key: "hideNotification",
    value: function hideNotification($notification) {
      $notification.removeClass('show');
      setTimeout(function () {
        $notification.remove();
      }, 300);
    }

    /**
        * Copy text to clipboard
        * @param {string} text - Text to copy
        * @returns {Promise} Promise that resolves when copy is complete
        */
  }, {
    key: "copyToClipboard",
    value: function copyToClipboard(text) {
      if (navigator.clipboard) {
        return navigator.clipboard.writeText(text);
      } else {
        // For older browsers
        return new Promise(function (resolve, reject) {
          var textArea = document.createElement('textarea');
          textArea.value = text;
          textArea.style.position = 'fixed';
          textArea.style.opacity = '0';
          document.body.appendChild(textArea);
          textArea.select();
          try {
            document.execCommand('copy');
            document.body.removeChild(textArea);
            resolve();
          } catch (err) {
            document.body.removeChild(textArea);
            reject(err);
          }
        });
      }
    }

    /**
        * Share content using Web Share API
        * @param {Object} options - Share options
        * @param {string} options.title - Share title
        * @param {string} options.text - Share text
        * @param {string} options.url - URL to share
        * @param {Function} options.onSuccess - Callback when share succeeds
        * @param {Function} options.onError - Callback when share fails
        */
  }, {
    key: "shareContent",
    value: function shareContent(options) {
      var _this2 = this;
      var title = options.title,
        text = options.text,
        url = options.url,
        onSuccess = options.onSuccess,
        onError = options.onError;

      // Try Web Share API first
      if (navigator.share) {
        navigator.share({
          title: title || '',
          text: text || '',
          url: url || ''
        }).then(function () {
          if (onSuccess) onSuccess();
        })["catch"](function () {
          // User cancelled or error occurred, use clipboard
          _this2.shareViaClipboard(url, title, onSuccess, onError);
        });
      } else {
        // Use clipboard
        this.shareViaClipboard(url, title, onSuccess, onError);
      }
    }

    /**
        * Share content via clipboard with modal
        * @param {string} url - URL to share
        * @param {string} title - Optional title
        * @param {Function} onSuccess - Success callback
        * @param {Function} onError - Error callback
        */
  }, {
    key: "shareViaClipboard",
    value: function shareViaClipboard(url) {
      var _this3 = this;
      var title = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
      var onSuccess = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
      var onError = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
      this.copyToClipboard(url).then(function () {
        _this3.showNotification('Link copied to clipboard!', 'success');
        if (onSuccess) onSuccess();
      })["catch"](function () {
        // Clipboard failed, show share modal
        _this3.showShareModal(url, title);
        if (onError) onError();
      });
    }

    /**
        * Show share modal with copy and social share options
        * @param {string} url - URL to share
        * @param {string} title - Optional title for sharing
        */
  }, {
    key: "showShareModal",
    value: function showShareModal(url) {
      var _this4 = this;
      var title = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
      // Remove existing modals
      jQuery('.splms-share-modal').remove();
      var modal = jQuery("\n            <div class=\"splms-share-modal\" style=\"position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: var(--splms-overlay-bg, rgba(0,0,0,0.5)); z-index: 9999; display: flex; align-items: center; justify-content: center;\">\n                <div style=\"background: var(--splms-background, white); padding: 20px; border-radius: var(--splms-border-radius-lg, 8px); max-width: 400px; width: 90%;\">\n                    <h3 style=\"margin-top: 0;\">Share ".concat(title ? title : 'Link', "</h3>\n                    <p>Share this link with others:</p>\n                    <input type=\"text\" class=\"splms-share-url-input\" value=\"").concat(url, "\" readonly style=\"width: 100%; padding: 8px; margin: 10px 0; border: 1px solid var(--splms-border-color-light, #ddd); border-radius: var(--splms-border-radius, 4px); box-sizing: border-box;\">\n                    <div style=\"display: flex; gap: 10px; margin-top: 15px;\">\n                        <button class=\"splms-share-copy-btn\" style=\"flex: 1; padding: 8px; background: var(--splms-primary, #007cba); color: white; border: none; border-radius: var(--splms-border-radius, 4px); cursor: pointer;\">Copy Link</button>\n                        <button class=\"splms-share-close-btn\" style=\"flex: 1; padding: 8px; background: var(--splms-gray-500, #6c757d); color: white; border: none; border-radius: var(--splms-border-radius, 4px); cursor: pointer;\">Close</button>\n                    </div>\n                    <div class=\"splms-social-share\" style=\"margin-top: 15px; display: flex; gap: 10px;\">\n                        <a href=\"https://www.linkedin.com/sharing/share-offsite/?url=").concat(encodeURIComponent(url), "\" target=\"_blank\" style=\"flex: 1; padding: 8px; background: var(--splms-social-linkedin, #0077b5); color: white; text-align: center; border-radius: var(--splms-border-radius, 4px); text-decoration: none;\">LinkedIn</a>\n                        <a href=\"https://twitter.com/intent/tweet?url=").concat(encodeURIComponent(url), "&text=").concat(encodeURIComponent(title || 'Check this out!'), "\" target=\"_blank\" style=\"flex: 1; padding: 8px; background: var(--splms-social-twitter, #1da1f2); color: white; text-align: center; border-radius: var(--splms-border-radius, 4px); text-decoration: none;\">Twitter</a>\n                    </div>\n                </div>\n            </div>\n        "));
      jQuery('body').append(modal);

      // Handle copy button
      modal.find('.splms-share-copy-btn').on('click', function () {
        var input = modal.find('.splms-share-url-input')[0];
        input.select();
        _this4.copyToClipboard(url).then(function () {
          _this4.showNotification('Link copied to clipboard!', 'success');
          modal.remove();
        })["catch"](function () {
          // Use execCommand for older browsers
          try {
            document.execCommand('copy');
            _this4.showNotification('Link copied to clipboard!', 'success');
            modal.remove();
          } catch (err) {
            _this4.showNotification('Failed to copy link. Please select and copy manually.', 'error');
          }
        });
      });

      // Handle close button and overlay click
      modal.find('.splms-share-close-btn, .splms-share-modal').on('click', function (e) {
        if (e.target === e.currentTarget || jQuery(e.target).hasClass('splms-share-close-btn')) {
          modal.remove();
        }
      });

      // Auto-select URL input
      setTimeout(function () {
        modal.find('.splms-share-url-input').focus().select();
      }, 100);
    }

    /**
        * Debounce function
        * @param {Function} func - Function to debounce
        * @param {number} wait - Wait time in milliseconds
        * @param {boolean} immediate - Execute immediately
        */
  }, {
    key: "debounce",
    value: function debounce(func, wait, immediate) {
      var timeout;
      return function executedFunction() {
        var _this5 = this;
        for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
          args[_key] = arguments[_key];
        }
        var later = function later() {
          timeout = null;
          if (!immediate) func.apply(_this5, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(this, args);
      };
    }

    /**
        * Throttle function
        * @param {Function} func - Function to throttle
        * @param {number} limit - Limit in milliseconds
        */
  }, {
    key: "throttle",
    value: function throttle(func, limit) {
      var inThrottle;
      return function () {
        if (!inThrottle) {
          for (var _len2 = arguments.length, args = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
            args[_key2] = arguments[_key2];
          }
          func.apply(this, args);
          inThrottle = true;
          setTimeout(function () {
            return inThrottle = false;
          }, limit);
        }
      };
    }

    /**
        * Check if element is in viewport
        * @param {HTMLElement} element - Element to check
        * @returns {boolean}
        */
  }, {
    key: "isInViewport",
    value: function isInViewport(element) {
      var rect = element.getBoundingClientRect();
      return rect.top >= 0 && rect.left >= 0 && rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && rect.right <= (window.innerWidth || document.documentElement.clientWidth);
    }

    /**
        * Sanitize HTML string
        * @param {string} str - String to sanitize
        * @returns {string}
        */
  }, {
    key: "sanitizeHTML",
    value: function sanitizeHTML(str) {
      var temp = document.createElement('div');
      temp.textContent = str;
      return temp.innerHTML;
    }

    /**
        * Format currency
        * @param {number} amount - Amount to format
        * @param {string} currency - Currency code
        * @returns {string}
        */
  }, {
    key: "formatCurrency",
    value: function formatCurrency(amount) {
      var currency = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'USD';
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
      }).format(amount);
    }

    /**
        * Format date
        * @param {Date|string} date - Date to format
        * @param {string} format - Format string
        * @returns {string}
        */
  }, {
    key: "formatDate",
    value: function formatDate(date) {
      var format = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'short';
      var dateObj = new Date(date);
      return dateObj.toLocaleDateString('en-US', {
        year: 'numeric',
        month: format === 'long' ? 'long' : 'short',
        day: 'numeric'
      });
    }

    /**
        * Get URL parameter
        * @param {string} name - Parameter name
        * @returns {string|null}
        */
  }, {
    key: "getUrlParameter",
    value: function getUrlParameter(name) {
      var urlParams = new URLSearchParams(window.location.search);
      return urlParams.get(name);
    }

    /**
        * Set URL parameter
        * @param {string} name - Parameter name
        * @param {string} value - Parameter value
        */
  }, {
    key: "setUrlParameter",
    value: function setUrlParameter(name, value) {
      var url = new URL(window.location);
      url.searchParams.set(name, value);
      window.history.pushState({}, '', url);
    }

    /**
        * Remove URL parameter
        * @param {string} name - Parameter name
        */
  }, {
    key: "removeUrlParameter",
    value: function removeUrlParameter(name) {
      var url = new URL(window.location);
      url.searchParams["delete"](name);
      window.history.pushState({}, '', url);
    }

    /**
        * Validate email address
        * @param {string} email - Email to validate
        * @returns {boolean}
        */
  }, {
    key: "isValidEmail",
    value: function isValidEmail(email) {
      var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return emailRegex.test(email);
    }

    /**
        * Generate random string
        * @param {number} length - Length of string
        * @returns {string}
        */
  }, {
    key: "generateRandomString",
    value: function generateRandomString() {
      var length = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 10;
      var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
      var result = '';
      for (var i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
      }
      return result;
    }

    /**
        * Storage helper methods
        */
  }, {
    key: "storage",
    get: function get() {
      return {
        set: function set(key, value) {
          try {
            localStorage.setItem(key, JSON.stringify(value));
          } catch (e) {
            var _window$console;
            (_window$console = window.console) === null || _window$console === void 0 || _window$console.warn('Failed to save to localStorage:', e);
          }
        },
        get: function get(key) {
          var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
          try {
            var item = localStorage.getItem(key);
            return item ? JSON.parse(item) : defaultValue;
          } catch (e) {
            var _window$console2;
            (_window$console2 = window.console) === null || _window$console2 === void 0 || _window$console2.warn('Failed to read from localStorage:', e);
            return defaultValue;
          }
        },
        remove: function remove(key) {
          try {
            localStorage.removeItem(key);
          } catch (e) {
            var _window$console3;
            (_window$console3 = window.console) === null || _window$console3 === void 0 || _window$console3.warn('Failed to remove from localStorage:', e);
          }
        },
        clear: function clear() {
          try {
            localStorage.clear();
          } catch (e) {
            var _window$console4;
            (_window$console4 = window.console) === null || _window$console4 === void 0 || _window$console4.warn('Failed to clear localStorage:', e);
          }
        }
      };
    }
  }]);
}();

/***/ },

/***/ "./src/js/frontend/core/user.js"
/*!**************************************!*\
  !*** ./src/js/frontend/core/user.js ***!
  \**************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SPLMSUser: () => (/* binding */ SPLMSUser)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * SkillPulse LMS User Module
 * Handles user authentication state and user-related functionality
 */

var SPLMSUser = /*#__PURE__*/function () {
  function SPLMSUser() {
    _classCallCheck(this, SPLMSUser);
    this.isLoggedIn = false;
    this.userId = null;
    this.userData = null;
    this.loginUrl = '';
    this.init();
  }
  return _createClass(SPLMSUser, [{
    key: "init",
    value: function init() {
      this.checkLoginStatus();
      this.setLoginUrl();
    }

    /**
        * Check if user is logged in by looking for WordPress user data
        */
  }, {
    key: "checkLoginStatus",
    value: function checkLoginStatus() {
      // Check if WordPress user data is available
      if (typeof window.splmsUserData !== 'undefined') {
        this.isLoggedIn = window.splmsUserData.isLoggedIn || false;
        this.userId = window.splmsUserData.userId || null;
        this.userData = window.splmsUserData.userData || null;
      } else {
        // Check for common WordPress indicators
        this.isLoggedIn = this.detectLoginStatus();
      }
    }

    /**
        * Detect login status using common WordPress indicators
        */
  }, {
    key: "detectLoginStatus",
    value: function detectLoginStatus() {
      // Check for WordPress admin bar
      if (document.getElementById('wpadminbar')) {
        return true;
      }

      // Check for logged-in class on body
      if (document.body && document.body.classList.contains('logged-in')) {
        return true;
      }

      // Check for user-specific elements
      if (document.querySelector('.user-menu, .user-profile, .user-avatar')) {
        return true;
      }
      return false;
    }

    /**
        * Set login URL
        */
  }, {
    key: "setLoginUrl",
    value: function setLoginUrl() {
      if (typeof window.splmsUserData !== 'undefined' && window.splmsUserData.loginUrl) {
        this.loginUrl = window.splmsUserData.loginUrl;
      } else {
        // Default WordPress login URL
        this.loginUrl = '/wp-login.php';
      }
    }

    /**
        * Get user ID
        */
  }, {
    key: "getUserId",
    value: function getUserId() {
      return this.userId;
    }

    /**
        * Get user data
        */
  }, {
    key: "getUserData",
    value: function getUserData() {
      return this.userData;
    }

    /**
        * Check if user is logged in
        */
  }, {
    key: "isUserLoggedIn",
    value: function isUserLoggedIn() {
      return this.isLoggedIn;
    }

    /**
        * Get login URL
        */
  }, {
    key: "getLoginUrl",
    value: function getLoginUrl() {
      return this.loginUrl;
    }

    /**
        * Redirect to login page with return URL
        */
  }, {
    key: "redirectToLogin",
    value: function redirectToLogin() {
      var returnUrl = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      if (!returnUrl) {
        returnUrl = encodeURIComponent(window.location.href);
      }
      window.location.href = "".concat(this.loginUrl, "?redirect_to=").concat(returnUrl);
    }

    /**
        * Refresh user data
        */
  }, {
    key: "refresh",
    value: function refresh() {
      this.checkLoginStatus();
      this.setLoginUrl();
    }
  }]);
}();

/***/ },

/***/ "./src/js/frontend/modules/auth/auth.js"
/*!**********************************************!*\
  !*** ./src/js/frontend/modules/auth/auth.js ***!
  \**********************************************/
(module) {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * SkillPulse LMS Authentication
 * Handles login, signup, password reset, and activation functionality
 */
var SPLMSAuth = /*#__PURE__*/function () {
  function SPLMSAuth() {
    _classCallCheck(this, SPLMSAuth);
    this.init();
  }
  return _createClass(SPLMSAuth, [{
    key: "init",
    value: function init() {
      this.bindEvents();
      this.initPasswordToggles();
      this.initPasswordStrength();
      this.initRealTimeValidation();
      this.disableNativeValidation();
      this.initInputClearErrors();
      this.initCleanState();
    }
  }, {
    key: "bindEvents",
    value: function bindEvents() {
      // Signup form submission
      jQuery(document).on('submit', '#splms-signup-form', this.handleSignup.bind(this));

      // Activation form submission
      jQuery(document).on('submit', '#splms-activation-form', this.handleActivation.bind(this));

      // Resend activation form submission
      jQuery(document).on('submit', '#splms-resend-form', this.handleResendActivation.bind(this));

      // Password toggle events
      jQuery(document).on('click', '.splms-password-toggle', this.togglePassword.bind(this));
    }
  }, {
    key: "initPasswordToggles",
    value: function initPasswordToggles() {
      // Initialize password toggle functionality
      jQuery('.splms-password-toggle').each(function () {
        var $toggle = jQuery(this);
        var target = $toggle.data('target');
        var $field = jQuery('#' + target);
        if ($field.length === 0) {
          console.warn('Password toggle target not found:', target);
        }
      });
    }
  }, {
    key: "initPasswordStrength",
    value: function initPasswordStrength() {
      // Password strength indicator
      jQuery(document).on('input', '#password', this.checkPasswordStrength.bind(this));
    }
  }, {
    key: "initRealTimeValidation",
    value: function initRealTimeValidation() {
      // Real-time username validation
      //jQuery(document).on('blur', '#username', this.validateUsername.bind(this));

      // Real-time email validation
      jQuery(document).on('blur', '#email', this.validateEmail.bind(this));

      // Password match validation
      jQuery(document).on('blur', '#confirm_password', this.validatePasswordMatch.bind(this));
    }
  }, {
    key: "disableNativeValidation",
    value: function disableNativeValidation() {
      // Disable native HTML5 validation to prevent browser styling conflicts
      jQuery('#splms-signup-form').attr('novalidate', 'novalidate');
    }
  }, {
    key: "initCleanState",
    value: function initCleanState() {
      // Ensure form starts with clean state - no errors or styling
      this.clearFieldErrors();
      this.clearMessages();

      // Make sure all form controls start without error styling
      jQuery('.splms-form-control').removeClass('error');

      // Hide all error containers
      jQuery('.splms-form-error').hide();

      // Clear any stuck loading states
      this.clearAllLoadingStates();
    }
  }, {
    key: "clearAllLoadingStates",
    value: function clearAllLoadingStates() {
      var _this = this;
      // Find all buttons with loading class and clear their state
      jQuery('.splms-auth-btn.loading').each(function (index, element) {
        var $button = jQuery(element);
        _this.setButtonLoading($button, false);
      });
    }
  }, {
    key: "initInputClearErrors",
    value: function initInputClearErrors() {
      // Clear errors when user starts typing
      jQuery(document).on('input focus', '.splms-form-control', function () {
        var $field = jQuery(this);
        var fieldName = $field.attr('name') || $field.attr('id');
        var $errorElement = jQuery('#' + fieldName + '_error');
        if ($errorElement.length) {
          $errorElement.empty().hide();
        }
        $field.removeClass('error');
      });
    }
  }, {
    key: "handleSignup",
    value: function handleSignup(e) {
      var _this2 = this;
      e.preventDefault();
      var $form = jQuery(e.currentTarget);
      var $submitBtn = $form.find('button[type="submit"].splms-auth-btn-primary');

      // Add show-validation class to enable red borders on invalid fields (only after submission)
      $form.addClass('show-validation');

      // Clear previous errors
      this.clearFieldErrors();
      this.clearMessages();

      // Validate form before sending
      if (!this.validateForm($form)) {
        return; // Stop here if validation fails
      }

      // Show loading state
      this.setButtonLoading($submitBtn, true);

      // Serialize form data
      var formData = new FormData($form[0]);
      SPLMSCore.frontendAjax.signupUser(formData).done(function (response) {
        _this2.handleSignupResponse(response);
      }).fail(function (xhr, status, error) {
        console.error('Signup AJAX Error:', status, error);
        var frontend = window.splms_frontend;
        _this2.showError(frontend.strings.error);
        _this2.setButtonLoading($submitBtn, false);
      }).always(function () {
        // Always stop loading state regardless of success/error
        _this2.setButtonLoading($submitBtn, false);
      });
    }
  }, {
    key: "handleSignupResponse",
    value: function handleSignupResponse(response) {
      var _this3 = this;
      if (response.success) {
        this.showSuccess(response.data.message);

        // Redirect after success
        setTimeout(function () {
          if (response.data.redirect) {
            window.location.href = response.data.redirect;
          }
        }, 5000);
      } else {
        // Show field-specific errors
        if (response.data.errors) {
          jQuery.each(response.data.errors, function (field, error) {
            _this3.showFieldError(field, error);
          });
        }
        this.showError(response.data.message);
      }
    }
  }, {
    key: "handleActivation",
    value: function handleActivation(e) {
      var _this4 = this;
      e.preventDefault();
      var $form = jQuery(e.currentTarget);
      var $submitBtn = $form.find('button[type="submit"].splms-auth-btn-primary');

      // Clear previous messages
      this.clearMessages();
      this.clearFieldErrors();

      // Show loading state
      this.setButtonLoading($submitBtn, true);

      // Serialize form data
      var formData = new FormData($form[0]);
      SPLMSCore.frontendAjax.activateSignup(formData).done(function (response) {
        _this4.handleActivationResponse(response);
      }).fail(function (xhr, status, error) {
        console.error('Activation AJAX Error:', status, error);
        var frontend = window.splms_frontend;
        _this4.showError(frontend.strings.error);
        _this4.setButtonLoading($submitBtn, false);
      }).always(function () {
        // Always stop loading state regardless of success/error
        _this4.setButtonLoading($submitBtn, false);
      });
    }
  }, {
    key: "handleActivationResponse",
    value: function handleActivationResponse(response) {
      if (response.success) {
        this.showSuccess(response.data.message);

        // Redirect after success
        setTimeout(function () {
          if (response.data.redirect) {
            window.location.href = response.data.redirect;
          }
        }, 2000);
      } else {
        this.showError(response.data.message);
      }
    }
  }, {
    key: "handleResendActivation",
    value: function handleResendActivation(e) {
      var _this5 = this;
      e.preventDefault();
      var $form = jQuery(e.currentTarget);
      var $submitBtn = $form.find('button[type="submit"]');
      this.setButtonLoading($submitBtn, true);

      // Serialize form data
      var formData = new FormData($form[0]);
      SPLMSCore.frontendAjax.resendActivation(formData).done(function (response) {
        if (response.success) {
          _this5.showAlert(response.data.message, 'success');
          $form[0].reset();
        } else {
          _this5.showAlert(response.data.message, 'error');
        }
      }).fail(function (xhr, status, error) {
        var _frontend$strings;
        console.error('Resend Activation AJAX Error:', status, error);
        var frontend = window.splms_frontend || {};
        _this5.showAlert(((_frontend$strings = frontend.strings) === null || _frontend$strings === void 0 ? void 0 : _frontend$strings.error) || 'Something went wrong. Please try again.', 'error');
      }).always(function () {
        // Always stop loading state regardless of success/error
        _this5.setButtonLoading($submitBtn, false);
      });
    }
  }, {
    key: "togglePassword",
    value: function togglePassword(e) {
      e.preventDefault();
      var $toggle = jQuery(e.currentTarget);
      var target = $toggle.data('target');
      var $passwordField = jQuery('#' + target);
      var $showIcon = $toggle.find('.splms-toggle-show');
      var $hideIcon = $toggle.find('.splms-toggle-hide');
      if ($passwordField.attr('type') === 'password') {
        $passwordField.attr('type', 'text');
        $toggle.addClass('active');
        $showIcon.hide();
        $hideIcon.show().css('display', 'block');
      } else {
        $passwordField.attr('type', 'password');
        $toggle.removeClass('active');
        $showIcon.show().css('display', 'block');
        $hideIcon.hide();
      }
    }
  }, {
    key: "validateSignupForm",
    value: function validateSignupForm($form) {
      var isValid = true;

      // Check required fields
      $form.find('input[required]').each(function () {
        var $field = jQuery(this);
        var value = $field.val().trim();
        if (!value) {
          isValid = false;
          $field.addClass('error');
          var fieldName = $field.attr('name');
          var $errorDiv = jQuery('#' + fieldName + '_error');
          if ($errorDiv.length) {
            $errorDiv.text($field.data('required-message') || 'This field is required.');
          }
        } else {
          $field.removeClass('error');
        }

        // Special validation for email
        if ($field.attr('type') === 'email' && value) {
          var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRegex.test(value)) {
            isValid = false;
            $field.addClass('error');
            var _$errorDiv = jQuery('#email_error');
            if (_$errorDiv.length) {
              _$errorDiv.text('Please enter a valid email address.');
            }
          }
        }

        // Special validation for password confirmation
        if ($field.attr('name') === 'confirm_password' && value) {
          var password = $form.find('input[name="password"]').val();
          if (value !== password) {
            isValid = false;
            $field.addClass('error');
            var _$errorDiv2 = jQuery('#confirm_password_error');
            if (_$errorDiv2.length) {
              _$errorDiv2.text('Passwords do not match.');
            }
          }
        }
      });

      // Check terms checkbox
      var $termsCheckbox = $form.find('input[name="terms_accepted"]');
      if ($termsCheckbox.length && !$termsCheckbox.is(':checked')) {
        isValid = false;
        $termsCheckbox.addClass('error');
        var $errorDiv = jQuery('#terms_accepted_error');
        if ($errorDiv.length) {
          $errorDiv.text('You must agree to the Terms and Conditions.');
        }
      }
      return isValid;
    }
  }, {
    key: "checkPasswordStrength",
    value: function checkPasswordStrength(e) {
      var password = jQuery(e.target).val();
      var $strengthIndicator = jQuery('#password_strength');
      if (password.length === 0) {
        $strengthIndicator.hide();
        return;
      }
      var strength = 0;
      var strengthText = '';
      var strengthClass = '';

      // Check password criteria
      if (password.length >= 8) strength++;
      if (password.match(/[a-z]/)) strength++;
      if (password.match(/[A-Z]/)) strength++;
      if (password.match(/[0-9]/)) strength++;
      if (password.match(/[^a-zA-Z0-9]/)) strength++;
      switch (strength) {
        case 0:
        case 1:
          strengthText = 'Very Weak';
          strengthClass = 'very-weak';
          break;
        case 2:
          strengthText = 'Weak';
          strengthClass = 'weak';
          break;
        case 3:
          strengthText = 'Fair';
          strengthClass = 'fair';
          break;
        case 4:
          strengthText = 'Good';
          strengthClass = 'good';
          break;
        case 5:
          strengthText = 'Strong';
          strengthClass = 'strong';
          break;
      }
      $strengthIndicator.removeClass().addClass('splms-password-strength ' + strengthClass).html('<span>Strength: ' + strengthText + '</span>').show();
    }
  }, {
    key: "validateUsername",
    value: function validateUsername() {
      var username = jQuery('#username').val();
      var $error = jQuery('#username_error');
      if (username.length > 0) {
        if (!this.validateUsernameFormat(username)) {
          $error.text('Username can only contain letters, numbers, and underscores. No spaces allowed.').show();
          return false;
        } else {
          $error.empty().hide();
          return true;
        }
      }
      return true;
    }
  }, {
    key: "validateEmail",
    value: function validateEmail() {
      var email = jQuery('#email').val();
      var $error = jQuery('#email_error');
      if (email.length > 0) {
        if (!this.validateEmailFormat(email)) {
          $error.text('Please enter a valid email address.').show();
          return false;
        } else {
          $error.empty().hide();
          return true;
        }
      }
      return true;
    }

    // Helper validation methods
  }, {
    key: "validateUsernameFormat",
    value: function validateUsernameFormat(username) {
      // Username validation: only letters, numbers, underscores, no spaces, 3-20 chars
      var usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;

      // Additional checks for common invalid usernames
      var invalidUsernames = ['admin', 'administrator', 'test', 'user', 'guest', 'demo', 'test student', 'test-student', 'teststudent', 'student test', 'null', 'undefined', 'www', 'ftp', 'mail', 'email'];
      var lowerUsername = username.toLowerCase().replace(/[\s\-_.]/g, '');
      return usernameRegex.test(username) && !invalidUsernames.includes(lowerUsername);
    }
  }, {
    key: "validateEmailFormat",
    value: function validateEmailFormat(email) {
      var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return emailRegex.test(email);
    }
  }, {
    key: "validatePasswordMatch",
    value: function validatePasswordMatch() {
      // Find the form that contains the confirm password field
      var $confirmPasswordField = jQuery('#confirm_password');
      var $form = $confirmPasswordField.closest('form');
      if (!$form.length) {
        return true; // No form found, skip validation
      }

      // Handle signup form (#password)
      var $passwordField = $form.find('#password');
      var $error = jQuery('#confirm_password_error');

      // Get password value
      var password = $passwordField.length > 0 ? $passwordField.val() : '';
      var confirmPassword = $confirmPasswordField.val();
      if (confirmPassword.length > 0 && password !== confirmPassword) {
        $error.text('Passwords do not match.').show();
        $confirmPasswordField.addClass('error');
        return false;
      } else {
        $error.empty().hide();
        $confirmPasswordField.removeClass('error');
        return true;
      }
    }
  }, {
    key: "setButtonLoading",
    value: function setButtonLoading($button, isLoading) {
      var _this6 = this;
      // Ensure we have a valid button
      if (!$button || $button.length === 0) {
        console.warn('SkillPulse Auth: Button not found for loading state');
        return;
      }
      var $btnText = $button.find('.splms-btn-text');
      var $btnLoader = $button.find('.splms-btn-loader');

      // Check if required elements exist
      if ($btnText.length === 0 || $btnLoader.length === 0) {
        console.warn('SkillPulse Auth: Button text or loader elements not found');
        return;
      }
      if (isLoading) {
        $button.prop('disabled', true).addClass('loading');
        $btnText.hide();
        $btnLoader.show();

        // Failsafe: Clear loading after 35 seconds if not already cleared
        var buttonId = $button.attr('id') || 'unknown';
        var timeoutId = setTimeout(function () {
          if ($button.hasClass('loading')) {
            console.warn('SkillPulse Auth: Failsafe clearing stuck loading state for button:', buttonId);
            _this6.setButtonLoading($button, false);
          }
        }, 35000);

        // Store timeout ID on button for cleanup
        $button.data('loading-timeout', timeoutId);
      } else {
        // Clear any existing timeout
        var _timeoutId = $button.data('loading-timeout');
        if (_timeoutId) {
          clearTimeout(_timeoutId);
          $button.removeData('loading-timeout');
        }
        $button.prop('disabled', false).removeClass('loading');
        $btnText.show();
        $btnLoader.hide();
      }
    }
  }, {
    key: "showSuccess",
    value: function showSuccess(message) {
      var $successMsg = jQuery('#success_message');
      $successMsg.find('.splms-message-text').text(message);
      $successMsg.show();
    }
  }, {
    key: "showError",
    value: function showError(message) {
      var $errorMsg = jQuery('#error_message');
      $errorMsg.text(message);
      $errorMsg.show();
    }
  }, {
    key: "showFieldError",
    value: function showFieldError(field, error) {
      var $fieldError = jQuery('#' + field + '_error');
      $fieldError.text(error).show();
    }
  }, {
    key: "clearMessages",
    value: function clearMessages() {
      jQuery('.splms-form-message').hide();
    }
  }, {
    key: "clearFieldErrors",
    value: function clearFieldErrors() {
      jQuery('.splms-form-error').empty().hide();
      jQuery('.splms-form-control').removeClass('error');
    }
  }, {
    key: "showAlert",
    value: function showAlert(message) {
      var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'info';
      // Simple alert for modals - you can enhance this with a better notification system
      if (type === 'success') {
        alert('✅ ' + message);
      } else if (type === 'error') {
        alert('❌ ' + message);
      } else {
        alert(message);
      }
    }

    // Utility method for form validation
  }, {
    key: "validatePasswordStrength",
    value: function validatePasswordStrength(password) {
      var errors = [];
      if (password.length < 8) {
        errors.push('Password must be at least 8 characters long.');
      }
      if (!/[A-Z]/.test(password)) {
        errors.push('Password must contain at least one uppercase letter.');
      }
      if (!/[a-z]/.test(password)) {
        errors.push('Password must contain at least one lowercase letter.');
      }
      if (!/[0-9]/.test(password)) {
        errors.push('Password must contain at least one number.');
      }
      if (!/[^a-zA-Z0-9]/.test(password)) {
        errors.push('Password must contain at least one special character.');
      }
      return errors;
    }
  }, {
    key: "validateForm",
    value: function validateForm($form) {
      var _this7 = this;
      var isValid = true;
      var requiredFields = $form.find('[required]');

      // Clear all previous errors
      this.clearFieldErrors();
      requiredFields.each(function (index, element) {
        var $field = jQuery(element);
        var value = $field.val().trim();
        var fieldName = $field.attr('name') || $field.attr('id');
        if (!value) {
          _this7.showFieldError(fieldName, 'This field is required.');
          _this7.addFieldErrorStyling($field);
          isValid = false;
        } else {
          _this7.removeFieldErrorStyling($field);
        }
      });

      // Additional signup form validation
      if ($form.attr('id') === 'splms-signup-form') {
        // Username validation
        var username = $form.find('#username').val().trim();
        if (username && !this.validateUsernameFormat(username)) {
          this.showFieldError('username', 'Username can only contain letters, numbers, and underscores. No spaces allowed.');
          this.addFieldErrorStyling($form.find('#username'));
          isValid = false;
        }

        // Email validation
        var email = $form.find('#email').val().trim();
        if (email && !this.validateEmailFormat(email)) {
          this.showFieldError('email', 'Please enter a valid email address.');
          this.addFieldErrorStyling($form.find('#email'));
          isValid = false;
        }

        // Password strength validation
        var password = $form.find('#password').val();
        if (password) {
          var passwordErrors = this.validatePasswordStrength(password);
          if (passwordErrors.length > 0) {
            this.showFieldError('password', passwordErrors[0]); // Show first error
            this.addFieldErrorStyling($form.find('#password'));
            isValid = false;
          }
        }

        // Password match validation
        var confirmPassword = $form.find('#confirm_password').val();
        if (password && confirmPassword && password !== confirmPassword) {
          this.showFieldError('confirm_password', 'Passwords do not match.');
          this.addFieldErrorStyling($form.find('#confirm_password'));
          isValid = false;
        }

        // Terms validation
        var termsAccepted = $form.find('#terms_accepted').is(':checked');
        if (!termsAccepted) {
          this.showFieldError('terms_accepted', 'You must accept the terms and conditions.');
          isValid = false;
        }
      }
      return isValid;
    }

    // Add visual error styling to field
  }, {
    key: "addFieldErrorStyling",
    value: function addFieldErrorStyling($field) {
      $field.addClass('error');
    }

    // Remove visual error styling from field
  }, {
    key: "removeFieldErrorStyling",
    value: function removeFieldErrorStyling($field) {
      $field.removeClass('error');
    }

    // Method to check if user is on mobile
  }, {
    key: "isMobile",
    value: function isMobile() {
      return window.innerWidth <= 768;
    }
  }]);
}(); // Initialize authentication when DOM is ready
jQuery(document).ready(function () {
  if (typeof window.splms_frontend !== 'undefined') {
    window.skillpulseAuth = new SPLMSAuth();
  }
});

// Export for potential external use
if ( true && module.exports) {
  module.exports = SPLMSAuth;
}

/***/ },

/***/ "./src/js/frontend/modules/auth/signup.validation.js"
/*!***********************************************************!*\
  !*** ./src/js/frontend/modules/auth/signup.validation.js ***!
  \***********************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SkillPulseSignupValidation: () => (/* binding */ SkillPulseSignupValidation)
/* harmony export */ });
/**
 * SkillPulse LMS Signup Validation JavaScript
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

// Main validation object - make it globally accessible
var SkillPulseSignupValidation = {
  // Validation strings
  strings: {
    required: 'This field is required.',
    usernameLength: 'Username must be at least 3 characters long.',
    passwordLength: 'Password must be at least 6 characters long.',
    nameLength: 'Name must be at least 2 characters long.',
    invalidEmail: 'Please enter a valid email address.',
    usernameInvalid: 'Username can only contain letters, numbers, and underscores.',
    passwordMismatch: 'Passwords do not match.',
    termsRequired: 'You must accept the terms and conditions.'
  },
  init: function init() {
    this.bindEvents();
    this.initValidationRules();
  },
  bindEvents: function bindEvents() {
    // Real-time validation on blur
    jQuery(document).on('blur', '#splms-signup-form input', this.handleFieldValidation.bind(this));

    // Clear errors on input
    jQuery(document).on('input', '#splms-signup-form input', this.handleFieldErrorClear.bind(this));

    // Password strength check
    jQuery(document).on('input', '#splms-signup-form #password', this.handlePasswordStrength.bind(this));

    // Confirm password validation - only for signup form
    jQuery(document).on('input', '#splms-signup-form #confirm_password', this.handleConfirmPassword.bind(this));
  },
  initValidationRules: function initValidationRules() {
    this.rules = {
      first_name: {
        required: true,
        minlength: 2
      },
      last_name: {
        required: true,
        minlength: 2
      },
      username: {
        required: true,
        minlength: 3,
        pattern: /^[a-zA-Z0-9_]+$/
      },
      email: {
        required: true,
        email: true
      },
      password: {
        required: true,
        minlength: 6
      },
      confirm_password: {
        required: true,
        equalTo: '#password'
      },
      user_type: {
        required: true
      },
      terms_accepted: {
        required: true
      },
      activation_key: {
        required: true,
        minlength: 32
      }
    };
  },
  validateForm: function validateForm($form) {
    var isValid = true;
    var errors = [];

    // Clear previous errors
    $form.find('.splms-form-error').empty().hide();
    $form.find('.field-error').remove();

    // Validate each field
    $form.find('input[required]').each(function () {
      var $field = jQuery(this);
      var fieldName = $field.attr('name');
      var fieldValue = $field.val() ? $field.val().trim() : '';
      if (!SkillPulseSignupValidation.validateField($field, fieldValue)) {
        isValid = false;
        $field.addClass('error');
        var errorMessage = SkillPulseSignupValidation.getFieldErrorMessage($field);
        if (errorMessage) {
          errors.push(errorMessage);
          SkillPulseSignupValidation.showFieldError($field, errorMessage);
        }
      } else {
        $field.removeClass('error');
      }
    });

    // Display form errors if any
    if (errors.length > 0) {
      var $errorMessage = $form.find('#error_message');
      var $errorText = $errorMessage.find('.splms-message-text');
      $errorText.text(errors.join(', '));
      $errorMessage.show();
    }
    return isValid;
  },
  validateField: function validateField($field, value) {
    var fieldName = $field.attr('name');
    var rules = this.rules[fieldName];
    if (!rules) {
      return true;
    }

    // Required validation
    if (rules.required && !value) {
      return false;
    }

    // Min length validation
    if (rules.minlength && value.length < rules.minlength) {
      return false;
    }

    // Email validation
    if (rules.email && !this.isValidEmail(value)) {
      return false;
    }

    // Pattern validation
    if (rules.pattern && !rules.pattern.test(value)) {
      return false;
    }

    // Equal to validation
    if (rules.equalTo) {
      var $targetField = jQuery(rules.equalTo);
      if (value !== $targetField.val()) {
        return false;
      }
    }
    return true;
  },
  isValidEmail: function isValidEmail(email) {
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  },
  getFieldErrorMessage: function getFieldErrorMessage($field) {
    var fieldName = $field.attr('name');
    var value = $field.val() ? $field.val().trim() : '';
    var rules = this.rules[fieldName];
    if (!rules) {
      return '';
    }
    if (rules.required && !value) {
      return SkillPulseSignupValidation.strings.required;
    }
    if (rules.minlength && value.length < rules.minlength) {
      if (fieldName === 'username') {
        return SkillPulseSignupValidation.strings.usernameLength;
      } else if (fieldName === 'password') {
        return SkillPulseSignupValidation.strings.passwordLength;
      } else if (fieldName === 'first_name' || fieldName === 'last_name') {
        return SkillPulseSignupValidation.strings.nameLength;
      }
    }
    if (rules.email && !this.isValidEmail(value)) {
      return SkillPulseSignupValidation.strings.invalidEmail;
    }
    if (rules.pattern && !rules.pattern.test(value)) {
      if (fieldName === 'username') {
        return SkillPulseSignupValidation.strings.usernameInvalid;
      }
    }
    if (rules.equalTo) {
      return SkillPulseSignupValidation.strings.passwordMismatch;
    }
    return '';
  },
  handleFieldValidation: function handleFieldValidation(e) {
    var $field = jQuery(e.target);
    var value = $field.val() ? $field.val().trim() : '';
    if (!SkillPulseSignupValidation.validateField($field, value)) {
      $field.addClass('error');
      var errorMessage = SkillPulseSignupValidation.getFieldErrorMessage($field);
      if (errorMessage) {
        SkillPulseSignupValidation.showFieldError($field, errorMessage);
      }
    } else {
      $field.removeClass('error');
      SkillPulseSignupValidation.clearFieldError($field);
    }
  },
  handleFieldErrorClear: function handleFieldErrorClear(e) {
    var $field = jQuery(e.target);
    $field.removeClass('error');
    SkillPulseSignupValidation.clearFieldError($field);
  },
  handlePasswordStrength: function handlePasswordStrength(e) {
    var $field = jQuery(e.target);
    var password = $field.val();
    var $form = $field.closest('#splms-signup-form');
    var $strengthIndicator = $form.find('#password_strength');
    if (!password) {
      $strengthIndicator.empty().hide();
      return;
    }
    var strength = this.calculatePasswordStrength(password);
    var strengthText = this.getPasswordStrengthText(strength);
    var strengthClass = this.getPasswordStrengthClass(strength);
    $strengthIndicator.html('<span class="' + strengthClass + '">' + strengthText + '</span>').show();
  },
  handleConfirmPassword: function handleConfirmPassword(e) {
    var $field = jQuery(e.target);
    var confirmPassword = $field.val();
    var $form = $field.closest('#splms-signup-form');
    var password = $form.find('#password').val();
    if (confirmPassword && password !== confirmPassword) {
      $field.addClass('error');
      SkillPulseSignupValidation.showFieldError($field, SkillPulseSignupValidation.strings.passwordMismatch);
    } else {
      $field.removeClass('error');
      SkillPulseSignupValidation.clearFieldError($field);
    }
  },
  calculatePasswordStrength: function calculatePasswordStrength(password) {
    var score = 0;
    if (password.length >= 6) score++;
    if (password.length >= 8) score++;
    if (/[a-z]/.test(password)) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;
    return score;
  },
  getPasswordStrengthText: function getPasswordStrengthText(strength) {
    if (strength <= 2) return 'Weak';
    if (strength <= 3) return 'Fair';
    if (strength <= 4) return 'Good';
    return 'Strong';
  },
  getPasswordStrengthClass: function getPasswordStrengthClass(strength) {
    if (strength <= 2) return 'password-weak';
    if (strength <= 3) return 'password-fair';
    if (strength <= 4) return 'password-good';
    return 'password-strong';
  },
  showFieldError: function showFieldError($field, message) {
    var fieldName = $field.attr('name') || $field.attr('id');
    var isPasswordField = fieldName === 'password' || fieldName === 'confirm_password';
    if (isPasswordField) {
      // For password fields, find error container in the parent input-group structure
      var $formGroup = $field.closest('.splms-form-group');
      var $inputGroup = $field.closest('.splms-input-group');
      var $errorContainer = $inputGroup.find('.splms-form-error#' + fieldName + '_error');

      // If not found in input-group, check form-group
      if (!$errorContainer.length) {
        $errorContainer = $formGroup.find('.splms-form-error#' + fieldName + '_error');
      }

      // If still not found, create it in the input-group
      if (!$errorContainer.length && $inputGroup.length) {
        $errorContainer = jQuery('<div class="splms-form-error" id="' + fieldName + '_error"></div>');
        $inputGroup.append($errorContainer);
      }
    } else {
      // For other fields, use sibling or form-group error container
      var $formGroup = $field.closest('.splms-form-group');
      var $errorContainer = $field.siblings('.splms-form-error');

      // If not found as sibling, look for error container by ID in form-group
      if (!$errorContainer.length) {
        $errorContainer = $formGroup.find('.splms-form-error#' + fieldName + '_error');
      }

      // If still not found, create after the field
      if (!$errorContainer.length) {
        $errorContainer = jQuery('<div class="splms-form-error" id="' + fieldName + '_error"></div>');
        $field.after($errorContainer);
      }
    }

    // Set error message and show
    if ($errorContainer.length) {
      $errorContainer.text(message).show();
    }
  },
  clearFieldError: function clearFieldError($field) {
    var fieldName = $field.attr('name') || $field.attr('id');
    var isPasswordField = fieldName === 'password' || fieldName === 'confirm_password';
    if (isPasswordField) {
      // For password fields, find error container in the parent input-group structure
      var $inputGroup = $field.closest('.splms-input-group');
      var $formGroup = $field.closest('.splms-form-group');
      var $errorContainer = $inputGroup.find('.splms-form-error#' + fieldName + '_error');

      // If not found in input-group, check form-group
      if (!$errorContainer.length) {
        $errorContainer = $formGroup.find('.splms-form-error#' + fieldName + '_error');
      }
    } else {
      // For other fields, use sibling or form-group error container
      var $formGroup = $field.closest('.splms-form-group');
      var $errorContainer = $field.siblings('.splms-form-error');

      // If not found as sibling, look for error container by ID in form-group
      if (!$errorContainer.length) {
        $errorContainer = $formGroup.find('.splms-form-error#' + fieldName + '_error');
      }
    }

    // Clear and hide error
    if ($errorContainer.length) {
      $errorContainer.empty().hide();
    }
  },
  // Remote validation for username and email availability
  validateRemote: function validateRemote($field, callback) {
    var fieldName = $field.attr('name');
    var fieldValue = $field.val() ? $field.val().trim() : '';
    var rules = this.rules[fieldName];
    if (!rules || !rules.remote) {
      callback(true);
      return;
    }
    var data = jQuery.extend({}, rules.remote.data, {
      value: fieldValue
    });
    jQuery.ajax({
      url: rules.remote.url,
      type: rules.remote.type,
      data: data,
      dataType: 'json',
      success: function success(response) {
        callback(response.success);
      },
      error: function error() {
        callback(false);
      }
    });
  }
};

// Initialize when document is ready
jQuery(document).ready(function () {
  SkillPulseSignupValidation.init();
});

// Make available globally
window.SkillPulseSignupValidation = SkillPulseSignupValidation;

// Export for use in other modules


/***/ },

/***/ "./src/js/frontend/modules/courses/course-actions.js"
/*!***********************************************************!*\
  !*** ./src/js/frontend/modules/courses/course-actions.js ***!
  \***********************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SPLMSCourseActions: () => (/* binding */ SPLMSCourseActions)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * SkillPulse LMS Course Actions
 * Handles enrollment, bookmarks, wishlist, and share functionality
 */

var SPLMSCourseActions = /*#__PURE__*/function () {
  function SPLMSCourseActions() {
    _classCallCheck(this, SPLMSCourseActions);
    this.init();
  }
  return _createClass(SPLMSCourseActions, [{
    key: "init",
    value: function init() {
      this.bindEvents();
      this.initUpNextCollapse();
    }
  }, {
    key: "bindEvents",
    value: function bindEvents() {
      // Enrollment button
      jQuery(document).on('click', '.enroll-btn', this.handleEnrollment.bind(this));

      // Unenrollment button
      jQuery(document).on('click', '.splms-btn-unenroll', this.handleUnenrollment.bind(this));

      // Start learning button (for public access courses)
      jQuery(document).on('click', '.start-learning-btn', this.handleStartLearning.bind(this));

      // Buy now button (for paid courses)
      jQuery(document).on('click', '.buy-now-btn', this.handleBuyNow.bind(this));

      // Wishlist button
      jQuery(document).on('click', '.wishlist-btn', this.handleWishlist.bind(this));
      jQuery(document).on('click', '.splms-filter-button,.splms-filter-close-btns', this.handleMobileFilter.bind(this));

      // Bookmark button - using delegated event binding for dynamically loaded content
      jQuery(document).on('click', '.bookmark-btn', this.handleBookmark.bind(this));

      // Copy link buttons - bind before share button to handle copy-link specifically
      jQuery(document).on('click', '.copy-link', this.handleCopyLink.bind(this));
      jQuery(document).on('click', '.splms-share-btn--copy', this.handleCopyLink.bind(this));

      // Share button (but not copy-link buttons)
      jQuery(document).on('click', '.share-btn:not(.copy-link)', this.handleShare.bind(this));

      // Course Info Modal
      jQuery(document).on('click', '.splms-course-info-btn', this.openCourseInfoModal.bind(this));
      jQuery(document).on('click', '.splms-modal-close, .splms-modal-overlay', this.closeCourseInfoModal.bind(this));

      // Close modal on Escape key
      jQuery(document).on('keydown', this.handleModalKeydown.bind(this));

      // Up Next collapse/expand toggle
      jQuery(document).on('click', '.splms-up-next__title', this.toggleUpNext.bind(this));
      jQuery(document).on('keydown', '.splms-up-next__title', this.handleUpNextKeydown.bind(this));
    }
  }, {
    key: "handleBookmark",
    value: function handleBookmark(e) {
      e.preventDefault();
      e.stopPropagation();
      var $button = jQuery(e.currentTarget);
      // Bookmarks support both lessons and quizzes
      // jQuery data() automatically converts kebab-case to camelCase
      var itemId = $button.data('lesson-id') || $button.data('lessonId') || $button.attr('data-lesson-id');
      var itemType = 'lesson';

      // Check for quiz ID if lesson ID not found
      if (!itemId) {
        itemId = $button.data('quiz-id') || $button.data('quizId') || $button.attr('data-quiz-id');
        itemType = 'quiz';
      }

      // Parse as integer if it's a string
      if (itemId) {
        itemId = parseInt(itemId, 10);
      }
      if (!itemId || isNaN(itemId)) {
        console.error('Bookmark button missing valid lesson-id or quiz-id attribute. Button classes:', $button.attr('class'), 'Button HTML:', $button[0].outerHTML);
        return;
      }

      // Check if user is logged in
      if (!SPLMSCore.user.isUserLoggedIn()) {
        SPLMSCore.user.redirectToLogin();
        return;
      }

      // Check if bookmarks are enabled (if settings available)
      var frontend = window.splms_frontend || {};
      if (frontend.settings && frontend.settings.enable_bookmarks === false) {
        window.SPLMSCore.helper.showNotification('Bookmarks are disabled by site admin.', 'error');
        return;
      }
      $button.addClass('loading').prop('disabled', true);
      SPLMSCore.frontendAjax.toggleBookmark(itemId, itemType).then(function (response) {
        if (response.success) {
          window.SPLMSCore.helper.showNotification(response.data.message || 'Bookmark updated successfully!', 'success');

          // Toggle button state
          $button.toggleClass('bookmarked active');

          // Update button text if exists
          var isBookmarked = $button.hasClass('bookmarked');
          var $text = $button.find('.bookmark-text');
          if ($text.length) {
            $text.text(isBookmarked ? 'Bookmarked' : 'Bookmark');
          }

          // Sync accessibility state
          $button.attr('aria-pressed', isBookmarked ? 'true' : 'false');

          // Update SVG fill
          $button.find('svg path').attr('fill', isBookmarked ? 'currentColor' : 'none');
        } else {
          window.SPLMSCore.helper.showNotification(response.data.message || 'Failed to update bookmark.', 'error');
        }
      })["catch"](function (error) {
        console.error(error);
        window.SPLMSCore.helper.showNotification('Something went wrong. Please try again.', 'error');
      }).always(function () {
        $button.removeClass('loading').prop('disabled', false);
      });
    }
  }, {
    key: "handleEnrollment",
    value: function handleEnrollment(e) {
      e.preventDefault();
      var $button = jQuery(e.currentTarget);
      var courseId = $button.data('course-id');
      if (!courseId) return;
      $button.addClass('loading').prop('disabled', true);
      SPLMSCore.frontendAjax.enrollCourse(courseId).then(function (response) {
        if (response.success) {
          window.SPLMSCore.helper.showNotification('Successfully enrolled in course!', 'success');

          // Redirect or update UI
          if (response.data.redirect_url) {
            window.location.href = response.data.redirect_url;
          } else {
            location.reload();
          }
        } else {
          window.SPLMSCore.helper.showNotification(response.data.message || 'Enrollment failed.', 'error');
        }
      })["catch"](function (error) {
        console.error(error);
        window.SPLMSCore.helper.showNotification('Something went wrong. Please try again.', 'error');
        $button.removeClass('loading').prop('disabled', false);
      }).always(function () {
        $button.removeClass('loading').prop('disabled', false);
      });
    }
  }, {
    key: "handleUnenrollment",
    value: function handleUnenrollment(e) {
      e.preventDefault();
      var $button = jQuery(e.currentTarget);
      var courseId = $button.data('course-id');
      if (!courseId) return;

      // Show confirmation dialog
      if (!confirm('Are you sure you want to unenroll from this course? You will lose access to all course materials.')) {
        return;
      }
      $button.addClass('loading').prop('disabled', true);
      SPLMSCore.frontendAjax.unenrollCourse(courseId).then(function (response) {
        if (response.success) {
          window.SPLMSCore.helper.showNotification(response.data.message || 'Successfully unenrolled from course.', 'success');

          // Reload page to update UI
          setTimeout(function () {
            location.reload();
          }, 1000);
        } else {
          window.SPLMSCore.helper.showNotification(response.data.message || 'Failed to unenroll from course.', 'error');
          $button.removeClass('loading').prop('disabled', false);
        }
      })["catch"](function (error) {
        console.error(error);
        window.SPLMSCore.helper.showNotification('Something went wrong. Please try again.', 'error');
        $button.removeClass('loading').prop('disabled', false);
      });
    }

    /**
     * Handle start learning button for public access courses
     */
  }, {
    key: "handleStartLearning",
    value: function handleStartLearning(e) {
      e.preventDefault();
      var $button = jQuery(e.currentTarget);
      var courseId = $button.data('course-id');
      if (!courseId) return;
      $button.addClass('loading').prop('disabled', true);

      // For public access courses, redirect to curriculum or first lesson
      // This could be enhanced to check if user is logged in and auto-enroll them
      if (SPLMSCore.user.isUserLoggedIn()) {
        // User is logged in, enroll them automatically
        SPLMSCore.frontendAjax.enrollCourse(courseId).then(function (response) {
          if (response.success) {
            window.SPLMSCore.helper.showNotification('Welcome to the course!', 'success');

            // Redirect to curriculum or first lesson
            if (response.data.redirect_url) {
              window.location.href = response.data.redirect_url;
            } else {
              // Redirect to curriculum tab
              var currentUrl = new URL(window.location.href);
              currentUrl.searchParams.set('tab', 'curriculum');
              window.location.href = currentUrl.toString();
            }
          } else {
            window.SPLMSCore.helper.showNotification(response.data.message || 'Failed to start course.', 'error');
          }
        })["catch"](function (error) {
          console.error(error);
          window.SPLMSCore.helper.showNotification('Something went wrong. Please try again.', 'error');
        }).always(function () {
          $button.removeClass('loading').prop('disabled', false);
        });
      } else {
        // User is not logged in, redirect to login with return URL
        SPLMSCore.user.redirectToLogin();
      }
    }

    /**
     * Handle buy now button for paid courses
     */
  }, {
    key: "handleBuyNow",
    value: function handleBuyNow(e) {
      e.preventDefault();
      var $button = jQuery(e.currentTarget);
      var courseId = $button.data('course-id');
      if (!courseId) return;

      // Check if user is logged in
      if (!SPLMSCore.user.isUserLoggedIn()) {
        // Redirect to login with return URL
        SPLMSCore.user.redirectToLogin();
        return;
      }
      $button.addClass('loading').prop('disabled', true);

      // Get the purchase URL from the href attribute
      var purchaseUrl = $button.attr('href');
      if (purchaseUrl) {
        // Redirect to purchase/checkout page
        window.location.href = purchaseUrl;
      } else {
        // Get purchase URL via AJAX
        SPLMSCore.frontendAjax.getCoursePurchaseUrl(courseId).then(function (response) {
          if (response.success && response.data.purchase_url) {
            window.location.href = response.data.purchase_url;
          } else {
            window.SPLMSCore.helper.showNotification('Unable to process purchase. Please try again.', 'error');
          }
        })["catch"](function (error) {
          console.error(error);
          window.SPLMSCore.helper.showNotification('Something went wrong. Please try again.', 'error');
        }).always(function () {
          $button.removeClass('loading').prop('disabled', false);
        });
      }
    }
  }, {
    key: "handleWishlist",
    value: function handleWishlist(e) {
      e.preventDefault();
      var $button = jQuery(e.currentTarget);
      var courseId = $button.data('course-id');
      if (!courseId) return;

      // Check if user is logged in
      if (!SPLMSCore.user.isUserLoggedIn()) {
        SPLMSCore.user.redirectToLogin();
        return;
      }

      // Check if wishlist is enabled (if settings available)
      var frontend = window.splms_frontend || {};
      if (frontend.settings && frontend.settings.enable_course_wishlist === false) {
        window.SPLMSCore.helper.showNotification('Wishlist is disabled by site admin.', 'error');
        return;
      }
      $button.addClass('loading').prop('disabled', true);
      SPLMSCore.frontendAjax.toggleWishlist(courseId).then(function (response) {
        if (response.success) {
          window.SPLMSCore.helper.showNotification(response.data.message || 'Wishlist updated successfully!', 'success');

          // Toggle button state
          $button.toggleClass('in-wishlist active');

          // Update button text and state
          var isWishlisted = $button.hasClass('in-wishlist');
          var $text = $button.find('.wishlist-text');
          if ($text.length) {
            $text.text(isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist');
          }
          // If no text element exists, only update title attribute (preserve SVG icon)
          else {
            $button.attr('title', isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist');
          }

          // Sync accessibility state
          $button.attr('aria-pressed', isWishlisted ? 'true' : 'false');

          // Toggle SVG heart fill to reflect state (filled when wishlisted)
          var $svg = $button.find('svg').first();
          if ($svg.length) {
            // Update SVG fill attribute
            $svg.attr('fill', isWishlisted ? 'currentColor' : 'none');
            // Also update path fill if it exists
            var $svgPath = $svg.find('path').first();
            if ($svgPath.length) {
              $svgPath.attr('fill', isWishlisted ? 'currentColor' : 'none');
            }
          }
        } else {
          window.SPLMSCore.helper.showNotification(response.data.message || 'Failed to update wishlist.', 'error');
        }
      })["catch"](function (error) {
        console.error(error);
        window.SPLMSCore.helper.showNotification('Something went wrong. Please try again.', 'error');
      }).always(function () {
        $button.removeClass('loading').prop('disabled', false);
      });
    }
  }, {
    key: "handleMobileFilter",
    value: function handleMobileFilter(e) {
      e.preventDefault();
      jQuery('body').toggleClass('splms-filter-open');
    }
  }, {
    key: "handleShare",
    value: function handleShare(e) {
      var $button = jQuery(e.currentTarget);
      var courseId = $button.data('course-id');

      // If button is a link with href (social media links), allow default behavior.
      if (!courseId && $button.is('a[href]')) {
        return;
      }
      e.preventDefault();
      if (!courseId) return;

      // Get course URL
      var courseUrl = window.location.href;
      var courseTitle = document.title;

      // Use unified share helper
      window.SPLMSCore.helper.shareContent({
        title: courseTitle,
        text: 'Check out this course!',
        url: courseUrl,
        onSuccess: function onSuccess() {},
        onError: function onError() {
          // Share modal is already shown by helper
        }
      });
    }
  }, {
    key: "handleCopyLink",
    value: function handleCopyLink(e) {
      var _this = this;
      e.preventDefault();
      var $button = jQuery(e.currentTarget);
      var copyText = $button.data('copy-url') || $button.data('copy-text') || window.location.href;
      var $message = $button.closest('.splms-course-share').find('.splms-course-share__message');

      // Use Clipboard API if available
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(copyText).then(function () {
          _this.showCopySuccess($button, $message);
        })["catch"](function () {
          // Fallback to older method
          _this.fallbackCopyText(copyText, $button, $message);
        });
      } else {
        // Fallback for older browsers
        this.fallbackCopyText(copyText, $button, $message);
      }
    }
  }, {
    key: "fallbackCopyText",
    value: function fallbackCopyText(text, $button, $message) {
      var textArea = document.createElement('textarea');
      textArea.value = text;
      textArea.style.position = 'fixed';
      textArea.style.left = '-999999px';
      textArea.style.top = '-999999px';
      document.body.appendChild(textArea);
      textArea.focus();
      textArea.select();
      try {
        var successful = document.execCommand('copy');
        if (successful) {
          this.showCopySuccess($button, $message);
        } else {
          window.SPLMSCore.helper.showNotification('Failed to copy link. Please copy manually.', 'error');
        }
      } catch (err) {
        window.SPLMSCore.helper.showNotification('Failed to copy link. Please copy manually.', 'error');
      } finally {
        document.body.removeChild(textArea);
      }
    }
  }, {
    key: "showCopySuccess",
    value: function showCopySuccess($button, $message) {
      if ($message.length) {
        $message.fadeIn(200).delay(2000).fadeOut(200);
      } else {
        window.SPLMSCore.helper.showNotification('Link copied to clipboard!', 'success');
      }

      // Temporarily update button text
      var $span = $button.find('span');
      var originalText = $span.text();
      $span.text('Copied!');
      setTimeout(function () {
        $span.text(originalText);
      }, 2000);
    }

    /**
     * Open Course Information Modal
     */
  }, {
    key: "openCourseInfoModal",
    value: function openCourseInfoModal(e) {
      e.preventDefault();
      var $modal = jQuery('#splms-course-info-modal');
      if (!$modal.length) {
        console.error('Course info modal not found');
        return;
      }

      // Show modal using class-based approach.
      $modal.addClass('modal-visible');
      $modal.attr('aria-hidden', 'false');

      // Prevent body scroll.
      jQuery('body').addClass('modal-open');

      // Focus on close button for accessibility.
      setTimeout(function () {
        $modal.find('.splms-modal-close').focus();
      }, 100);
    }

    /**
     * Close Course Information Modal
     */
  }, {
    key: "closeCourseInfoModal",
    value: function closeCourseInfoModal(e) {
      // Check if click is on overlay or close button.
      var $target = jQuery(e.target);
      var isOverlay = $target.hasClass('splms-modal-overlay');
      var isCloseButton = $target.closest('.splms-modal-close').length > 0;

      // Only close if clicking overlay or close button (or its children like SVG).
      // Don't close if clicking inside modal content (unless it's the close button).
      if (!isOverlay && !isCloseButton) {
        var isInsideModalContent = $target.closest('.splms-modal-content').length > 0;
        if (isInsideModalContent) {
          return;
        }
      }
      e === null || e === void 0 || e.preventDefault();
      var $modal = jQuery('#splms-course-info-modal');
      if (!$modal.length) return;

      // Hide modal using class-based approach.
      $modal.removeClass('modal-visible');
      $modal.attr('aria-hidden', 'true');

      // Restore body scroll.
      jQuery('body').removeClass('modal-open');
    }

    /**
     * Handle keyboard events for modal
     */
  }, {
    key: "handleModalKeydown",
    value: function handleModalKeydown(e) {
      var $modal = jQuery('#splms-course-info-modal');

      // Close modal on Escape key
      if (e.key === 'Escape' && $modal.is(':visible')) {
        this.closeCourseInfoModal(e);
      }
    }

    /**
     * Initialize Up Next section collapse/expand functionality
     */
  }, {
    key: "initUpNextCollapse",
    value: function initUpNextCollapse() {
      var $upNextSection = jQuery('.splms-up-next-section');
      if (!$upNextSection.length) {
        return;
      }

      // Load saved state from localStorage.
      try {
        var savedState = localStorage.getItem('splms_up_next_collapsed');
        if ('true' === savedState) {
          this.setUpNextCollapsed($upNextSection, true);
        }
      } catch (e) {
        // LocalStorage not available, continue with default expanded state.
      }
    }

    /**
     * Toggle Up Next section collapse/expand
     */
  }, {
    key: "toggleUpNext",
    value: function toggleUpNext(e) {
      e.preventDefault();
      var $title = jQuery(e.currentTarget);
      var $section = $title.closest('.splms-up-next-section');
      var isCollapsed = $section.hasClass('collapsed');
      this.setUpNextCollapsed($section, !isCollapsed);

      // Save state to localStorage.
      try {
        localStorage.setItem('splms_up_next_collapsed', !isCollapsed);
      } catch (e) {
        // LocalStorage not available, state will not persist.
      }
    }

    /**
     * Set Up Next section collapsed state
     */
  }, {
    key: "setUpNextCollapsed",
    value: function setUpNextCollapsed($section, collapsed) {
      var $title = $section.find('.splms-up-next__title');
      if (collapsed) {
        $section.addClass('collapsed');
        $title.attr('aria-expanded', 'false');
      } else {
        $section.removeClass('collapsed');
        $title.attr('aria-expanded', 'true');
      }
    }

    /**
     * Handle keyboard events for Up Next section
     */
  }, {
    key: "handleUpNextKeydown",
    value: function handleUpNextKeydown(e) {
      // Toggle on Enter or Space key.
      if ('Enter' === e.key || ' ' === e.key) {
        e.preventDefault();
        this.toggleUpNext(e);
      }
    }
  }]);
}();

/***/ },

/***/ "./src/js/frontend/modules/courses/course.js"
/*!***************************************************!*\
  !*** ./src/js/frontend/modules/courses/course.js ***!
  \***************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SPLMSCourseFilters: () => (/* binding */ SPLMSCourseFilters)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * SkillPulse LMS Course Filters
 * Handles course archive filters, search, and load more functionality
 */

var SPLMSCourseFilters = /*#__PURE__*/function () {
  function SPLMSCourseFilters() {
    _classCallCheck(this, SPLMSCourseFilters);
    this.init();
  }
  return _createClass(SPLMSCourseFilters, [{
    key: "init",
    value: function init() {
      this.bindEvents();
      this.initializeLoadMore();
      this.setupSearch();
      this.initializeLayoutManagement();
      this.initializeLayoutSwitcher();
      this.initializeSearchToggle();
    }
  }, {
    key: "bindEvents",
    value: function bindEvents() {
      // Auto-submit on filter change
      jQuery(document).on('change', '.sidebar-filters-form input[type="checkbox"], .sidebar-filters-form input[type="radio"]', this.handleFilterChange.bind(this));

      // Auto-submit on header filter change
      jQuery(document).on('change', '.course-filters-form select', this.handleHeaderFilterChange.bind(this));

      // Load more buttons
      jQuery(document).on('click', '.load-more-btn', this.handleLoadMore.bind(this));

      // Accordion toggle for filter sections
      jQuery(document).on('click', '.filter-accordion .filter-section-header', this.toggleAccordion.bind(this));

      // Keyboard support for accordions
      jQuery(document).on('keydown', '.filter-accordion .filter-section-header', this.handleAccordionKeydown.bind(this));
    }

    /**
     * Handle filter change with auto-submit
     */
  }, {
    key: "handleFilterChange",
    value: function handleFilterChange(e) {
      var $form = jQuery(e.currentTarget).closest('.sidebar-filters-form');
      if ($form.length) {
        // Small delay to allow for multiple selections
        setTimeout(function () {
          $form[0].submit();
        }, 300);
      }
    }

    /**
     * Handle header filter change with auto-submit
     */
  }, {
    key: "handleHeaderFilterChange",
    value: function handleHeaderFilterChange(e) {
      var $form = jQuery(e.currentTarget).closest('.course-filters-form');
      var $select = jQuery(e.currentTarget);
      if ($form.length) {
        // Add loading state.
        $select.prop('disabled', true);
        $form.find('.filter-select').addClass('loading');

        // Submit after brief delay to prevent rapid submissions.
        setTimeout(function () {
          $form[0].submit();
        }, 300);
      }
    }

    /**
     * Toggle accordion section
     */
  }, {
    key: "toggleAccordion",
    value: function toggleAccordion(e) {
      e.preventDefault();
      var $header = jQuery(e.currentTarget);
      var $section = $header.closest('.filter-accordion');
      var isExpanded = $header.attr('aria-expanded') === 'true';

      // Update aria-expanded
      $header.attr('aria-expanded', !isExpanded);

      // Toggle collapsed state
      if (isExpanded) {
        $section.attr('data-collapsed', 'true');
      } else {
        $section.removeAttr('data-collapsed');
      }
    }

    /**
     * Handle keyboard navigation for accordions
     */
  }, {
    key: "handleAccordionKeydown",
    value: function handleAccordionKeydown(e) {
      // Enter or Space to toggle
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        this.toggleAccordion(e);
      }
    }

    /**
     * Initialize search toggle functionality
     */
  }, {
    key: "initializeSearchToggle",
    value: function initializeSearchToggle() {
      // Auto-expand if search query exists on page load
      jQuery('.splms-search-form').each(function (index, form) {
        var $form = jQuery(form);
        var $input = $form.find('.splms-search-input');
        if ($input.val().trim()) {
          var $wrapper = $form.closest('.splms-search-toggle-wrapper');
          var $toggleBtn = $wrapper.find('.splms-search-toggle');
          var $panel = $wrapper.find('.splms-search-panel');

          // Expand after a short delay to allow layout to settle
          setTimeout(function () {
            $form.attr('data-expanded', 'true');
            $toggleBtn.attr('aria-expanded', 'true');
            $panel.attr('aria-hidden', 'false');
            $form.addClass('is-expanded');
            $panel.addClass('is-expanded');
          }, 100);
        }
      });
    }

    /**
     * Initialize load more functionality
     */
  }, {
    key: "initializeLoadMore",
    value: function initializeLoadMore() {
      var _this = this;
      var $containers = jQuery('.filter-scrollable-container');
      $containers.each(function (index, container) {
        var $container = jQuery(container);
        var itemsPerLoad = parseInt($container.data('items-per-load')) || 8;
        var $allItems = $container.find('.filter-checkbox-label, .filter-tag-label');

        // Hide items beyond initial load
        $allItems.each(function (itemIndex, item) {
          var $item = jQuery(item);
          if (itemIndex >= itemsPerLoad) {
            $item.addClass('hidden-item');
          } else {
            $item.removeClass('hidden-item');
          }
        });

        // Update load more button
        _this.updateLoadMoreButton($container, itemsPerLoad);
      });
    }

    /**
     * Update load more button text and visibility
     */
  }, {
    key: "updateLoadMoreButton",
    value: function updateLoadMoreButton($container, itemsPerLoad) {
      var $loadMoreBtn = $container.find('.load-more-btn');
      var $loadMoreCount = $container.find('.load-more-count');
      var $hiddenItems = $container.find('.hidden-item');
      if (!$loadMoreBtn.length) return;
      var remainingCount = $hiddenItems.length;
      if (remainingCount <= 0) {
        $loadMoreBtn.hide();
      } else {
        $loadMoreBtn.show();
        if ($loadMoreCount.length) {
          $loadMoreCount.text("(".concat(remainingCount, " more)"));
        }
      }
    }

    /**
     * Handle load more button click
     */
  }, {
    key: "handleLoadMore",
    value: function handleLoadMore(e) {
      var _this2 = this;
      e.preventDefault();
      var $button = jQuery(e.currentTarget);
      var targetId = $button.data('target');
      var $container = jQuery("#".concat(targetId));
      var itemsPerLoad = parseInt($container.data('items-per-load')) || 8;
      var $hiddenItems = $container.find('.hidden-item');

      // Add loading state
      $button.addClass('loading');
      $container.addClass('loading');

      // Simulate loading delay for better UX
      setTimeout(function () {
        // Show next batch of items with animation
        var itemsToShow = Math.min(itemsPerLoad, $hiddenItems.length);
        $hiddenItems.slice(0, itemsToShow).each(function (index, item) {
          var $item = jQuery(item);
          $item.removeClass('hidden-item');

          // Animate in with stagger
          setTimeout(function () {
            $item.css({
              'transition': 'all 0.3s ease',
              'opacity': '1',
              'transform': 'translateY(0)'
            });
          }, index * 50);
        });

        // Remove loading state
        setTimeout(function () {
          $button.removeClass('loading');
          $container.removeClass('loading');

          // Update button
          _this2.updateLoadMoreButton($container, itemsPerLoad);
        }, itemsToShow * 50 + 200);
      }, 300);
    }

    /**
     * Setup search functionality
     */
  }, {
    key: "setupSearch",
    value: function setupSearch() {
      // Search functionality is handled in bindEvents
    }

    /**
     * Initialize layout management for archive pages
     */
  }, {
    key: "initializeLayoutManagement",
    value: function initializeLayoutManagement() {
      var $archive = jQuery('.splms-archive');
      var $archiveControls = jQuery('.splms-archive-controls');
      var $sidebar = jQuery('.splms-sidebar-filters');
      var $coursesMain = jQuery('.splms-courses-main');
      if ($archive.length) {
        // Check for empty controls
        if ($archiveControls.length && $archiveControls.children().length === 0) {
          $archive.addClass('no-controls');
        }

        // Check for empty sidebar
        if (!$sidebar.length || $sidebar.children().length === 0) {
          $archive.addClass('no-sidebar');
          if ($coursesMain.length) {
            $coursesMain.addClass('no-sidebar');
          }
        } else {
          // Check if sidebar has minimal content
          var filterSections = $sidebar.find('.filter-section');
          if (filterSections.length <= 2) {
            $archive.addClass('minimal-sidebar');
            if ($coursesMain.length) {
              $coursesMain.addClass('minimal-sidebar');
            }
          }
        }

        // Handle dynamic control count
        var controlCount = 0;
        if ($archiveControls.length) {
          var $searchForm = $archiveControls.find('.splms-search-form');
          var $courseFilters = $archiveControls.find('.splms-course-filters');
          var $layoutSwitcher = $archiveControls.find('.splms-layout-switcher');
          if ($searchForm.length) controlCount++;
          if ($courseFilters.length) controlCount++;
          if ($layoutSwitcher.length) controlCount++;
          if (controlCount <= 1) {
            $archive.addClass('minimal-controls');
          }
        }
      }
    }

    /**
     * Initialize layout switcher functionality
     */
  }, {
    key: "initializeLayoutSwitcher",
    value: function initializeLayoutSwitcher() {
      var $layoutSwitcher = jQuery('.splms-layout-switcher');
      var $coursesGrid = jQuery('.splms-courses-grid');
      if ($layoutSwitcher.length && $coursesGrid.length) {
        var $buttons = $layoutSwitcher.find('.layout-switch-btn');

        // Set initial layout
        var currentLayout = $layoutSwitcher.data('current-layout') || 'grid';
        $coursesGrid.attr('data-layout', currentLayout);

        // Bind click events
        $buttons.on('click', function (e) {
          e.preventDefault();
          var $button = jQuery(e.currentTarget);
          var layout = $button.data('layout');

          // Update active button
          $buttons.removeClass('active');
          $button.addClass('active');

          // Update corresponding radio button
          var $radioButton = jQuery("#layout-".concat(layout));
          if ($radioButton.length) {
            $radioButton.prop('checked', true);
          }

          // Update grid layout
          $coursesGrid.attr('data-layout', layout);

          // Save to localStorage for persistence
          try {
            localStorage.setItem('splms_layout', layout);
          } catch (e) {
            // Use cookie if localStorage is not available
            document.cookie = "splms_layout=".concat(layout, "; path=/; max-age=31536000");
          }

          // Update switcher data
          $layoutSwitcher.attr('data-current-layout', layout);
        });

        // Load saved layout preference on page load
        try {
          var savedLayout = localStorage.getItem('splms_layout');
          if (savedLayout && savedLayout !== currentLayout) {
            var $savedButton = $layoutSwitcher.find("[data-layout=\"".concat(savedLayout, "\"]"));
            if ($savedButton.length) {
              $savedButton.trigger('click');
            }
          }
        } catch (e) {
          // localStorage not available, continue with default
        }
      }
    }

    /**
     * Re-initialize filters (useful for AJAX content updates)
     */
  }, {
    key: "reinitialize",
    value: function reinitialize() {
      this.initializeLoadMore();
      this.initializeLayoutManagement();
      this.initializeLayoutSwitcher();
    }
  }]);
}();

/***/ },

/***/ "./src/js/frontend/modules/curriculum/curriculum.js"
/*!**********************************************************!*\
  !*** ./src/js/frontend/modules/curriculum/curriculum.js ***!
  \**********************************************************/
() {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * SkillPulse LMS Curriculum Handler
 * Handles curriculum interactions, enrollment, and progress tracking
 */
var SPLMSCurriculum = /*#__PURE__*/function () {
  function SPLMSCurriculum() {
    _classCallCheck(this, SPLMSCurriculum);
    this.init();
  }
  return _createClass(SPLMSCurriculum, [{
    key: "init",
    value: function init() {
      this.bindEvents();
      this.initSectionToggles();
      this.initProgressBars();
    }
  }, {
    key: "bindEvents",
    value: function bindEvents() {
      // Prevent section toggle when clicking link icons.
      jQuery(document).on('click', '.section-link-icon, .section-price-link', function (e) {
        e.stopPropagation();
      });

      // Section toggle functionality - bind to main header area and toggle button.
      jQuery(document).on('click', '.section-header-main, .section-header-minimal, .section-toggle, .section-toggle-minimal', this.handleSectionToggle.bind(this));

      // Note: Lesson completion is now handled by LessonCompletion.js in lesson-viewer.
      // Listen for completion events to update curriculum UI.
      jQuery(document).on('lesson_completed', this.handleLessonCompleted.bind(this));
    }
  }, {
    key: "initSectionToggles",
    value: function initSectionToggles() {
      // Initialize all sections as collapsed by default
      jQuery('.curriculum-section, .curriculum-section-minimal').each(function () {
        var $section = jQuery(this);
        var $toggle = $section.find('.section-toggle, .section-toggle-minimal');
        var $content = $section.find('.section-content');

        // Set initial state to collapsed
        $toggle.attr('aria-expanded', 'false');
        $content.addClass('collapsed').hide();
      });
    }
  }, {
    key: "initProgressBars",
    value: function initProgressBars() {
      // Animate progress bars on load
      setTimeout(function () {
        jQuery('.progress-bar-fill').each(function () {
          var $bar = jQuery(this);
          var width = $bar.data('width') || $bar.css('width');
          $bar.css('width', '0').animate({
            width: width
          }, 1000);
        });
      }, 500);
    }
  }, {
    key: "handleSectionToggle",
    value: function handleSectionToggle(e) {
      e.preventDefault();
      e.stopPropagation();
      var $clicked = jQuery(e.currentTarget);
      var $section = $clicked.closest('.curriculum-section, .curriculum-section-minimal');
      var $toggleBtn = $section.find('.section-toggle, .section-toggle-minimal');
      var $content = $section.find('.section-content');
      var isExpanded = $toggleBtn.attr('aria-expanded') === 'true';
      if (isExpanded) {
        // Collapse section
        $toggleBtn.attr('aria-expanded', 'false').removeClass('expanded');
        $content.slideUp(300, function () {
          $content.addClass('collapsed');
        });
      } else {
        // Expand section
        $toggleBtn.attr('aria-expanded', 'true').addClass('expanded');
        $content.removeClass('collapsed').slideDown(300);
      }
    }

    /**
     * Handle lesson completion event (triggered by LessonCompletion.js)
     */
  }, {
    key: "handleLessonCompleted",
    value: function handleLessonCompleted(e, data) {
      var lessonId = data.lessonId,
        progressData = data.progressData;

      // Update sidebar curriculum item
      this.markItemAsComplete(lessonId);

      // Update progress if provided
      if (progressData) {
        this.updateProgress(progressData);
      }

      // Update lesson meta to show completion (for lesson pages)
      this.updateLessonMeta(true);
    }
  }, {
    key: "markItemAsComplete",
    value: function markItemAsComplete(itemId) {
      // Find curriculum item by data attribute
      var $item = jQuery("[data-item-id=\"".concat(itemId, "\"]"));

      // Find by lesson ID data attribute if not found
      if (!$item.length) {
        $item = jQuery("[data-lesson-id=\"".concat(itemId, "\"]"));
      }

      // Find by URL if data attributes not found
      if (!$item.length) {
        $item = jQuery('.curriculum-item').filter(function () {
          var href = jQuery(this).attr('href');
          return href && (href.includes("p=".concat(itemId)) || href.includes("/".concat(itemId, "/")));
        });
      }
      if ($item.length) {
        $item.addClass('completed').removeClass('current');

        // Update status icon to completed state
        var $statusIcon = $item.find('.item-status .status-icon');
        if ($statusIcon.length) {
          $statusIcon.removeClass('incomplete locked').addClass('completed');
          $statusIcon.html("\n                    <svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n                        <path d=\"M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n                    </svg>\n                ");
        }

        // Update simple status text (for lesson template)
        var $status = $item.find('.item-status');
        if ($status.length && !$statusIcon.length) {
          $status.html('✓');
        }

        // Update action button to show completion badge
        var $actionBtn = $item.find('.item-actions .start-btn');
        if ($actionBtn.length) {
          $actionBtn.removeClass('start-btn').addClass('completion-badge').text('Completed');
        }

        // Add completion animation
        $item.addClass('completion-animation');
        setTimeout(function () {
          $item.removeClass('completion-animation');
        }, 1000);
      }
    }
  }, {
    key: "updateLessonButton",
    value: function updateLessonButton($button, isCompleted) {
      if (isCompleted) {
        $button.removeClass('mark-lesson-complete loading').addClass('lesson-completed').prop('disabled', true).html('Completed');
      }
    }
  }, {
    key: "updateLessonMeta",
    value: function updateLessonMeta(isCompleted) {
      if (isCompleted) {
        var $lessonMeta = jQuery('.splms-lesson-meta');
        if ($lessonMeta.length && !$lessonMeta.find('.completion-indicator').length) {
          $lessonMeta.append('<div class="splms-lesson-meta-item completion-indicator">Completed</div>');
        }
      }
    }
  }, {
    key: "updateProgress",
    value: function updateProgress(progressData) {
      if (!progressData) return;

      // Update progress bar (multiple selectors)
      var $progressBar = jQuery('.progress-bar-fill, .progress-fill');
      if ($progressBar.length && progressData.percentage !== undefined) {
        $progressBar.animate({
          width: progressData.percentage + '%'
        }, 500);
      }

      // Update progress text (multiple selectors)
      var $progressText = jQuery('.progress-text, .course-progress');
      if ($progressText.length && progressData.completed_lessons !== undefined && progressData.total_lessons !== undefined) {
        $progressText.text("".concat(progressData.completed_lessons, " of ").concat(progressData.total_lessons, " items completed"));
      }

      // Update progress stat
      var $progressStat = jQuery('.progress-stat .stat-number');
      if ($progressStat.length) {
        $progressStat.text(Math.round(progressData.percentage) + '%');
      }
    }

    // Utility method to check enrollment status
  }, {
    key: "isEnrolled",
    value: function isEnrolled() {
      return jQuery('.enrollment-cta').length === 0;
    }

    // Utility method to get course progress
  }, {
    key: "getCourseProgress",
    value: function getCourseProgress() {
      var $progressBar = jQuery('.progress-bar-fill');
      if ($progressBar.length) {
        var width = $progressBar.css('width');
        var containerWidth = $progressBar.parent().width();
        return parseFloat(width) / containerWidth * 100;
      }
      return 0;
    }
  }]);
}(); // Initialize when document is ready
jQuery(document).ready(function () {
  if (jQuery('.course-curriculum, .splms-course-curriculum, .curriculum-overview').length) {
    window.SPLMSCurriculum = new SPLMSCurriculum();
  }
});

// Additional CSS for notifications (inject into head)
jQuery(document).ready(function () {
  // Add curriculum-specific animations only
  if (!jQuery('#splms-curriculum-styles').length) {
    jQuery('head').append("\n            <style id=\"splms-curriculum-styles\">\n                .curriculum-item.completion-animation {\n                    animation: completionPulse 1s ease;\n                }\n                \n                @keyframes completionPulse {\n                    0% { background-color: transparent; }\n                    50% { background-color: var(--splms-success-alpha-1, rgba(16, 185, 129, 0.1)); }\n                    100% { background-color: transparent; }\n                }\n            </style>\n        ");
  }
});

/***/ },

/***/ "./src/js/frontend/modules/question-matching.js"
/*!******************************************************!*\
  !*** ./src/js/frontend/modules/question-matching.js ***!
  \******************************************************/
(module) {

function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * SkillPulse LMS Matching Question Handler
 * Handles drag-and-drop matching interface with drop zones for matching-type quiz questions
 */
var SPLMSQuestionMatching = /*#__PURE__*/function () {
  function SPLMSQuestionMatching(container, questionData, quizInstance) {
    _classCallCheck(this, SPLMSQuestionMatching);
    this.container = jQuery(container);
    this.questionId = questionData.id;
    this.pairs = Array.isArray(questionData.pairs) ? questionData.pairs : [];
    this.selectedAnswer = questionData.selectedAnswer || [];
    this.randomize = questionData.randomize_options || false;
    this.quiz = quizInstance || null;

    // Store matches: { leftIndex: rightItemText }
    this.matches = {};
    this.availableItems = []; // Items not yet matched
    this.draggedItem = null;
    this.init();
  }

  /**
      * Initialize the matching interface
      */
  return _createClass(SPLMSQuestionMatching, [{
    key: "init",
    value: function init() {
      if (!this.container.length || this.pairs.length === 0) {
        return;
      }

      // Check if we're in review/results mode
      this.isReviewMode = this.container.closest('.splms-quiz-results').length > 0;

      // Prepare items
      this.prepareItems();

      // Render the interface
      this.render();

      // Initialize drag and drop only if not in review mode
      if (!this.isReviewMode) {
        this.initDragAndDrop();
        this.initMobileClickToMatch();
      }

      // Update hidden input with initial state
      this.updateAnswer();
    }

    /**
        * Prepare left and right items from pairs
        */
  }, {
    key: "prepareItems",
    value: function prepareItems() {
      var _this = this;
      // Extract right-side items
      var rightItems = this.pairs.map(function (pair, index) {
        return {
          originalIndex: index,
          text: pair && _typeof(pair) === 'object' && pair.right ? pair.right : ''
        };
      }).filter(function (item) {
        return item.text;
      });

      // Shuffle right items if randomize is enabled
      if (this.randomize && !this.isReviewMode) {
        rightItems = this.shuffleArray(_toConsumableArray(rightItems));
      }
      this.availableItems = _toConsumableArray(rightItems);

      // Restore saved matches if available
      if (Array.isArray(this.selectedAnswer) && this.selectedAnswer.length > 0) {
        // selectedAnswer is an array of right-side values in order
        // We need to map them to left indices
        this.selectedAnswer.forEach(function (rightText, leftIndex) {
          if (rightText && _this.pairs[leftIndex]) {
            var normalizedRight = _this.normalizeText(rightText);
            var foundItem = _this.availableItems.find(function (item) {
              return _this.normalizeText(item.text) === normalizedRight;
            });
            if (foundItem) {
              _this.matches[leftIndex] = foundItem.text;
              // Remove from available items
              _this.availableItems = _this.availableItems.filter(function (item) {
                return item.text !== foundItem.text;
              });
            }
          }
        });
      }
    }

    /**
        * Render the matching interface
        */
  }, {
    key: "render",
    value: function render() {
      var _this2 = this;
      var leftItems = this.pairs.map(function (pair, index) {
        var leftText = pair && _typeof(pair) === 'object' && pair.left ? pair.left : '';
        var matchedText = _this2.matches[index] || '';
        var hasMatch = !!matchedText;
        return "\n                <div class=\"splms-matching-row\" data-left-index=\"".concat(index, "\">\n                    <div class=\"splms-matching-left-item\">\n                        <span class=\"splms-matching-left-label\">").concat(_this2.escapeHtml(leftText), "</span>\n                    </div>\n                    <div class=\"splms-matching-drop-zone ").concat(hasMatch ? 'splms-matching-has-match' : '', "\" \n                         data-drop-index=\"").concat(index, "\"\n                         ").concat(!_this2.isReviewMode ? 'droppable="true"' : '', ">\n                        ").concat(hasMatch ? "\n                            <div class=\"splms-matching-chip\" data-matched-index=\"".concat(index, "\">\n                                <span class=\"splms-matching-chip-text\">").concat(_this2.escapeHtml(matchedText), "</span>\n                                ").concat(!_this2.isReviewMode ? '<button type="button" class="splms-matching-chip-remove" data-remove-index="${index}" aria-label="Remove match">×</button>' : '', "\n                            </div>\n                        ") : "\n                            <span class=\"splms-matching-drop-hint\">".concat(_this2.getLocalizedText('Drop here', 'Drop here'), "</span>\n                        "), "\n                    </div>\n                </div>\n            ");
      }).join('');
      var availableChips = this.availableItems.map(function (item, index) {
        return "\n            <div class=\"splms-matching-chip splms-matching-chip-available ".concat(_this2.isReviewMode ? 'splms-matching-review-mode' : '', "\" \n                 draggable=\"").concat(_this2.isReviewMode ? 'false' : 'true', "\"\n                 data-item-text=\"").concat(_this2.escapeHtml(item.text), "\"\n                 data-item-index=\"").concat(index, "\">\n                <span class=\"splms-matching-chip-handle\">\u2630</span>\n                <span class=\"splms-matching-chip-text\">").concat(_this2.escapeHtml(item.text), "</span>\n            </div>\n        ");
      }).join('');
      var html = "\n            <div class=\"splms-matching-interface\">\n                <div class=\"splms-matching-pairs\">\n                    ".concat(leftItems, "\n                </div>\n                <div class=\"splms-matching-available-section\">\n                    <div class=\"splms-matching-section-header\">\n                        <strong>").concat(this.getLocalizedText('Available Items', 'Available Items'), "</strong>\n                    </div>\n                    <div class=\"splms-matching-available-chips\">\n                        ").concat(availableChips, "\n                    </div>\n                </div>\n            </div>\n            <input type=\"hidden\" \n                   name=\"question_").concat(this.questionId, "\" \n                   id=\"matching-answer-").concat(this.questionId, "\"\n                   data-question-id=\"").concat(this.questionId, "\"\n                   value=\"\">\n        ");
      this.container.html(html);
    }

    /**
        * Initialize drag and drop functionality
        */
  }, {
    key: "initDragAndDrop",
    value: function initDragAndDrop() {
      var self = this;

      // Make available chips draggable
      this.container.find('.splms-matching-chip-available').each(function () {
        var $chip = jQuery(this);

        // Desktop drag events
        $chip.on('dragstart', function (e) {
          self.draggedItem = {
            text: $chip.data('item-text'),
            element: $chip
          };
          e.originalEvent.dataTransfer.effectAllowed = 'move';
          e.originalEvent.dataTransfer.setData('text/html', this.outerHTML);
          $chip.addClass('splms-matching-dragging');
        });
        $chip.on('dragend', function () {
          $chip.removeClass('splms-matching-dragging');
          self.draggedItem = null;
        });

        // Touch events for mobile
        $chip.on('touchstart', function (e) {
          e.preventDefault();
          self.draggedItem = {
            text: $chip.data('item-text'),
            element: $chip
          };
          $chip.addClass('splms-matching-dragging splms-matching-touch-dragging');

          // Store initial touch position
          var touch = e.originalEvent.touches[0];
          self.touchStartX = touch.clientX;
          self.touchStartY = touch.clientY;
        });
        $chip.on('touchmove', function (e) {
          if (!self.draggedItem) return;
          e.preventDefault();
          var touch = e.originalEvent.touches[0];
          var elementBelow = document.elementFromPoint(touch.clientX, touch.clientY);

          // Remove previous hover effects
          self.container.find('.splms-matching-drop-zone').removeClass('splms-matching-touch-over');

          // Add hover effect if over a drop zone
          var $dropZone = jQuery(elementBelow).closest('.splms-matching-drop-zone');
          if ($dropZone.length) {
            $dropZone.addClass('splms-matching-touch-over');
          }
        });
        $chip.on('touchend', function (e) {
          if (!self.draggedItem) return;
          e.preventDefault();
          var touch = e.originalEvent.changedTouches[0];
          var elementBelow = document.elementFromPoint(touch.clientX, touch.clientY);
          var $dropZone = jQuery(elementBelow).closest('.splms-matching-drop-zone');

          // Clean up visual states
          $chip.removeClass('splms-matching-dragging splms-matching-touch-dragging');
          self.container.find('.splms-matching-drop-zone').removeClass('splms-matching-touch-over');

          // Handle drop if over a valid drop zone
          if ($dropZone.length && !self.isReviewMode) {
            var dropIndex = parseInt($dropZone.data('drop-index'), 10);
            self.handleDrop(dropIndex);
          } else {
            self.draggedItem = null;
          }
        });
      });

      // Make drop zones droppable
      this.container.find('.splms-matching-drop-zone').each(function () {
        var $dropZone = jQuery(this);
        var dropIndex = parseInt($dropZone.data('drop-index'), 10);
        $dropZone.on('dragover', function (e) {
          e.preventDefault();
          e.originalEvent.dataTransfer.dropEffect = 'move';
          if (!self.isReviewMode) {
            $dropZone.addClass('splms-matching-drag-over');
          }
        });
        $dropZone.on('dragleave', function () {
          $dropZone.removeClass('splms-matching-drag-over');
        });
        $dropZone.on('drop', function (e) {
          e.preventDefault();
          $dropZone.removeClass('splms-matching-drag-over');
          if (self.isReviewMode || !self.draggedItem) {
            return false;
          }
          self.handleDrop(dropIndex);
          return false;
        });
      });

      // Handle remove button clicks
      this.container.find('.splms-matching-chip-remove').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var removeIndex = parseInt(jQuery(this).data('remove-index'), 10);
        var matchedText = self.matches[removeIndex];
        if (matchedText) {
          // Move back to available items
          self.availableItems.push({
            text: matchedText,
            originalIndex: -1
          });
          delete self.matches[removeIndex];

          // Re-render
          self.render();
          self.initDragAndDrop();
          self.updateAnswer();
        }
      });
    }

    /**
        * Initialize mobile click-to-match functionality
        */
  }, {
    key: "initMobileClickToMatch",
    value: function initMobileClickToMatch() {
      var self = this;
      var selectedChip = null;

      // Click on available chips to select them (mobile alternative)
      this.container.find('.splms-matching-chip-available').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $chip = jQuery(this);

        // Clear previous selection
        self.container.find('.splms-matching-chip-available').removeClass('splms-matching-selected');
        self.container.find('.splms-matching-drop-zone').removeClass('splms-matching-active-for-match');

        // Select this chip
        $chip.addClass('splms-matching-selected');
        selectedChip = {
          text: $chip.data('item-text'),
          element: $chip
        };

        // Highlight available drop zones
        self.container.find('.splms-matching-drop-zone').addClass('splms-matching-active-for-match');

        // Show instruction
        self.showMobileInstruction('Tap a box on the left to place your selection');
      });

      // Click on drop zones to match selected chip
      this.container.find('.splms-matching-drop-zone').on('click', function (e) {
        if (!selectedChip) return;
        e.preventDefault();
        e.stopPropagation();
        var dropIndex = parseInt(jQuery(this).data('drop-index'), 10);

        // Set up dragged item for handleDrop to work
        self.draggedItem = selectedChip;

        // Handle the drop
        self.handleDrop(dropIndex);

        // Clear selection state
        selectedChip = null;
        self.container.find('.splms-matching-chip-available').removeClass('splms-matching-selected');
        self.container.find('.splms-matching-drop-zone').removeClass('splms-matching-active-for-match');
        self.hideMobileInstruction();
      });

      // Click outside to deselect
      jQuery(document).on('click', function (e) {
        if (!self.container.is(e.target) && self.container.has(e.target).length === 0) {
          selectedChip = null;
          self.container.find('.splms-matching-chip-available').removeClass('splms-matching-selected');
          self.container.find('.splms-matching-drop-zone').removeClass('splms-matching-active-for-match');
          self.hideMobileInstruction();
        }
      });
    }

    /**
        * Show mobile instruction
        */
  }, {
    key: "showMobileInstruction",
    value: function showMobileInstruction(text) {
      var $instruction = this.container.find('.splms-mobile-instruction');
      if (!$instruction.length) {
        $instruction = jQuery('<div class="splms-mobile-instruction"></div>');
        this.container.prepend($instruction);
      }
      $instruction.text(text).show();
    }

    /**
        * Hide mobile instruction
        */
  }, {
    key: "hideMobileInstruction",
    value: function hideMobileInstruction() {
      this.container.find('.splms-mobile-instruction').hide();
    }

    /**
        * Handle drop operation for both drag and touch
        */
  }, {
    key: "handleDrop",
    value: function handleDrop(dropIndex) {
      var _this3 = this;
      if (this.isReviewMode || !this.draggedItem) {
        return;
      }

      // If this drop zone already has a match, move it back to available
      if (this.matches[dropIndex]) {
        var oldText = this.matches[dropIndex];
        this.availableItems.push({
          text: oldText,
          originalIndex: -1
        });
        delete this.matches[dropIndex];
      }

      // Add new match
      this.matches[dropIndex] = this.draggedItem.text;

      // Remove from available items
      this.availableItems = this.availableItems.filter(function (item) {
        return item.text !== _this3.draggedItem.text;
      });

      // Clean up
      this.draggedItem = null;

      // Re-render
      this.render();
      this.initDragAndDrop();
      this.updateAnswer();
    }

    /**
        * Update the hidden input with current answer
        */
  }, {
    key: "updateAnswer",
    value: function updateAnswer() {
      var _this4 = this;
      // Build answer array: [rightText for leftIndex 0, rightText for leftIndex 1, ...]
      var answerArray = this.pairs.map(function (pair, leftIndex) {
        return _this4.matches[leftIndex] || '';
      });
      var $hiddenInput = this.container.find("#matching-answer-".concat(this.questionId));
      var answerJson = JSON.stringify(answerArray);
      $hiddenInput.val(answerJson);

      // Also update quiz answers directly
      if (this.quiz && this.quiz.answers) {
        this.quiz.answers[this.questionId] = answerArray;
        if (typeof this.quiz.saveQuizState === 'function') {
          this.quiz.saveQuizState();
        }
      }

      // Trigger change event
      var hiddenInputElement = $hiddenInput[0];
      if (hiddenInputElement) {
        var nativeEvent = new Event('change', {
          bubbles: true
        });
        hiddenInputElement.dispatchEvent(nativeEvent);
      }
      $hiddenInput.trigger('change');
    }

    /**
        * Get current answer as array
        */
  }, {
    key: "getAnswer",
    value: function getAnswer() {
      var _this5 = this;
      return this.pairs.map(function (pair, leftIndex) {
        return _this5.matches[leftIndex] || '';
      });
    }

    /**
        * Shuffle array
        */
  }, {
    key: "shuffleArray",
    value: function shuffleArray(array) {
      var shuffled = _toConsumableArray(array);
      for (var i = shuffled.length - 1; i > 0; i--) {
        var j = Math.floor(Math.random() * (i + 1));
        var _ref = [shuffled[j], shuffled[i]];
        shuffled[i] = _ref[0];
        shuffled[j] = _ref[1];
      }
      return shuffled;
    }

    /**
        * Escape HTML
        */
  }, {
    key: "escapeHtml",
    value: function escapeHtml(text) {
      var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        '\'': '&#039;'
      };
      return String(text).replace(/[&<>"']/g, function (m) {
        return map[m];
      });
    }

    /**
        * Normalize text for comparison
        */
  }, {
    key: "normalizeText",
    value: function normalizeText(text) {
      return String(text || '').toLowerCase().trim();
    }

    /**
        * Get localized text (placeholder for i18n)
        */
  }, {
    key: "getLocalizedText",
    value: function getLocalizedText(key, fallback) {
      // TODO: Implement proper i18n if needed
      return fallback;
    }
  }]);
}(); // Export for module systems
if ( true && module.exports) {
  module.exports = SPLMSQuestionMatching;
}

// Make available globally
if (typeof window !== 'undefined') {
  window.SPLMSQuestionMatching = SPLMSQuestionMatching;
}

/***/ },

/***/ "./src/js/frontend/modules/question-ordering.js"
/*!******************************************************!*\
  !*** ./src/js/frontend/modules/question-ordering.js ***!
  \******************************************************/
(module) {

function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * SkillPulse LMS Ordering Question Handler
 * Handles drag-and-drop ordering interface for ordering-type quiz questions
 */
var SPLMSQuestionOrdering = /*#__PURE__*/function () {
  function SPLMSQuestionOrdering(container, questionData, quizInstance) {
    _classCallCheck(this, SPLMSQuestionOrdering);
    this.container = jQuery(container);
    this.questionId = questionData.id;
    this.items = Array.isArray(questionData.items) ? questionData.items : [];
    this.selectedAnswer = questionData.selectedAnswer || [];
    this.quiz = quizInstance || null;

    // Store correct order from question data (array of option IDs)
    this.correctOrder = [];
    if (Array.isArray(questionData.correct_answer) && questionData.correct_answer.length > 0) {
      // Use correct_answer if provided (array of option IDs)
      this.correctOrder = questionData.correct_answer.map(function (id) {
        return String(id).trim();
      }).filter(function (id) {
        return id !== '';
      });
    } else {
      // Use items array order as correct order
      this.correctOrder = this.items.map(function (item) {
        return item && _typeof(item) === 'object' && item.text ? String(item.text).trim() : '';
      }).filter(function (text) {
        return text !== '';
      });
    }

    // Store current order: array of option IDs (not indices)
    this.currentOrder = [];
    this.draggedElement = null;

    // Check if randomize is enabled (from settings)
    var settings = questionData.settings || {};
    this.randomize = settings.randomize_options || false;
    this.init();
  }

  /**
      * Initialize the ordering interface
      */
  return _createClass(SPLMSQuestionOrdering, [{
    key: "init",
    value: function init() {
      if (!this.container.length || this.items.length === 0) {
        return;
      }

      // Check if we're in review/results mode
      this.isReviewMode = this.container.closest('.splms-quiz-results').length > 0;

      // Prepare initial order
      this.prepareOrder();

      // Render the interface
      this.render();

      // Initialize drag and drop only if not in review mode
      if (!this.isReviewMode) {
        this.initDragAndDrop();
        this.initMobileSupport();
      }

      // Update hidden input with initial state
      this.updateAnswer();
    }

    /**
        * Shuffle array using Fisher-Yates algorithm
        */
  }, {
    key: "shuffleArray",
    value: function shuffleArray(array) {
      var shuffled = _toConsumableArray(array);
      for (var i = shuffled.length - 1; i > 0; i--) {
        var j = Math.floor(Math.random() * (i + 1));
        var _ref = [shuffled[j], shuffled[i]];
        shuffled[i] = _ref[0];
        shuffled[j] = _ref[1];
      }
      return shuffled;
    }

    /**
        * Prepare initial order from saved answer or randomize
        * Returns array of option IDs in the order to display
        */
  }, {
    key: "prepareOrder",
    value: function prepareOrder() {
      var _this = this;
      if (Array.isArray(this.selectedAnswer) && this.selectedAnswer.length === this.items.length) {
        // Restore saved user order
        var firstAnswer = this.selectedAnswer[0];
        var hasIds = this.items.some(function (item) {
          var itemId = item && _typeof(item) === 'object' && item.id ? String(item.id).trim() : '';
          return itemId === firstAnswer;
        });
        if (hasIds) {
          // Restore saved order (already option IDs)
          this.currentOrder = this.selectedAnswer.map(function (id) {
            return String(id).trim();
          }).filter(function (id) {
            return id !== '';
          });
        } else {
          // Convert indices to option IDs
          this.currentOrder = this.selectedAnswer.map(function (index) {
            var item = _this.items[index];
            return item && _typeof(item) === 'object' && item.id ? String(item.id).trim() : '';
          }).filter(function (id) {
            return id !== '';
          });
        }
      } else {
        // No saved answer - always randomize on frontend (unless in review mode)
        if (!this.isReviewMode) {
          // Randomize the order for display so students must figure out correct sequence
          this.currentOrder = this.shuffleArray(_toConsumableArray(this.correctOrder));
        } else {
          // In review mode, show correct order
          this.currentOrder = _toConsumableArray(this.correctOrder);
        }
      }
    }

    /**
        * Render the ordering interface
        */
  }, {
    key: "render",
    value: function render() {
      var _this2 = this;
      // Get items in current order (by option ID)
      var orderedItems = this.currentOrder.map(function (optionId) {
        return _this2.items.find(function (item) {
          var itemId = item && _typeof(item) === 'object' && item.id ? item.id : '';
          return itemId === optionId;
        });
      }).filter(function (item) {
        return item !== undefined;
      });
      var itemsHtml = orderedItems.map(function (item, displayIndex) {
        var itemId = item && _typeof(item) === 'object' && item.id ? item.id : '';
        var itemText = item && _typeof(item) === 'object' && item.text ? item.text : typeof item === 'string' ? item : '';
        var positionNumber = displayIndex + 1;
        return "\n                <li class=\"splms-ordering-item ".concat(_this2.isReviewMode ? 'splms-ordering-review-mode' : '', "\" \n                    draggable=\"").concat(_this2.isReviewMode ? 'false' : 'true', "\"\n                    data-option-id=\"").concat(_this2.escapeHtml(itemId), "\"\n                    data-display-index=\"").concat(displayIndex, "\">\n                    <span class=\"splms-ordering-position\">").concat(positionNumber, "</span>\n                    <span class=\"splms-ordering-handle\">\n                        <svg width=\"20\" height=\"20\" viewBox=\"0 0 20 20\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n                            <path d=\"M7 5H13M7 10H13M7 15H13\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\"/>\n                        </svg>\n                    </span>\n                    <span class=\"splms-ordering-text\">").concat(_this2.escapeHtml(itemText), "</span>\n                    <span class=\"splms-ordering-arrow\">\u2192</span>\n                </li>\n            ");
      }).join('');
      var html = "\n            <ul class=\"splms-ordering-list\" id=\"ordering_list_".concat(this.questionId, "\">\n                ").concat(itemsHtml, "\n            </ul>\n            <input type=\"hidden\" \n                   name=\"question_").concat(this.questionId, "\" \n                   id=\"ordering_input_").concat(this.questionId, "\"\n                   data-question-id=\"").concat(this.questionId, "\"\n                   value=\"\">\n        ");
      this.container.html(html);
    }

    /**
        * Initialize drag and drop functionality
        */
  }, {
    key: "initDragAndDrop",
    value: function initDragAndDrop() {
      var self = this;
      var $list = this.container.find('.splms-ordering-list');

      // Make items draggable
      $list.find('.splms-ordering-item').each(function () {
        var $item = jQuery(this);
        $item.on('dragstart', function (e) {
          self.draggedElement = this;
          var displayIndex = parseInt($item.data('display-index'), 10);
          e.originalEvent.dataTransfer.effectAllowed = 'move';
          e.originalEvent.dataTransfer.setData('text/html', this.outerHTML);
          e.originalEvent.dataTransfer.setData('text/plain', displayIndex.toString());
          $item.addClass('splms-ordering-dragging');
        });
        $item.on('dragend', function () {
          $item.removeClass('splms-ordering-dragging');
          self.draggedElement = null;
        });

        // Touch events for mobile - mirror desktop behavior
        $item.on('touchstart', function () {
          self.draggedElement = this;
          $item.addClass('splms-ordering-dragging');
        });
        $item.on('touchend', function () {
          $item.removeClass('splms-ordering-dragging');
          self.draggedElement = null;
        });
      });

      // Handle drop zones
      $list.on('dragover', '.splms-ordering-item', function (e) {
        e.preventDefault();
        e.originalEvent.dataTransfer.dropEffect = 'move';
        if (this !== self.draggedElement) {
          jQuery(this).addClass('splms-ordering-drag-over');
        }
      });
      $list.on('dragleave', '.splms-ordering-item', function () {
        jQuery(this).removeClass('splms-ordering-drag-over');
      });
      $list.on('drop', '.splms-ordering-item', function (e) {
        e.preventDefault();
        jQuery(this).removeClass('splms-ordering-drag-over');
        if (self.isReviewMode || !self.draggedElement) {
          return false;
        }
        var draggedDisplayIndex = parseInt(jQuery(self.draggedElement).data('display-index'), 10);
        var droppedDisplayIndex = parseInt(jQuery(this).data('display-index'), 10);
        if (draggedDisplayIndex !== droppedDisplayIndex) {
          self.handleReorder(draggedDisplayIndex, droppedDisplayIndex);
        }
        return false;
      });

      // Touch drop zones - exactly like desktop
      $list.on('touchstart', '.splms-ordering-item', function () {
        if (this !== self.draggedElement) {
          jQuery(this).addClass('splms-ordering-drag-over');
        }
      });
      $list.on('touchend', '.splms-ordering-item', function () {
        jQuery(this).removeClass('splms-ordering-drag-over');
        if (self.isReviewMode || !self.draggedElement || this === self.draggedElement) {
          return;
        }
        var draggedDisplayIndex = parseInt(jQuery(self.draggedElement).data('display-index'), 10);
        var droppedDisplayIndex = parseInt(jQuery(this).data('display-index'), 10);
        if (draggedDisplayIndex !== droppedDisplayIndex) {
          self.handleReorder(draggedDisplayIndex, droppedDisplayIndex);
        }
      });
    }

    /**
     * Handle reordering logic (shared between drag and click interactions)
     */
  }, {
    key: "handleReorder",
    value: function handleReorder(fromIndex, toIndex) {
      if (fromIndex === toIndex || this.isReviewMode) {
        return;
      }

      // Reorder the currentOrder array (which contains option IDs)
      var _this$currentOrder$sp = this.currentOrder.splice(fromIndex, 1),
        _this$currentOrder$sp2 = _slicedToArray(_this$currentOrder$sp, 1),
        removed = _this$currentOrder$sp2[0];
      this.currentOrder.splice(toIndex, 0, removed);

      // Re-render with updated positions
      this.render();
      this.initDragAndDrop();
      this.initMobileSupport();
      this.updateAnswer();

      // Add a subtle animation to show the reorder happened
      var $list = this.container.find('.splms-ordering-list');
      $list.addClass('splms-ordering-reordered');
      setTimeout(function () {
        $list.removeClass('splms-ordering-reordered');
      }, 300);
    }

    /**
     * Initialize mobile click-to-select functionality
     */
  }, {
    key: "initMobileSupport",
    value: function initMobileSupport() {
      var self = this;
      var selectedItem = null;
      var selectedIndex = -1;

      // Click on items to select them for mobile
      this.container.find('.splms-ordering-item').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $item = jQuery(this);
        var itemIndex = parseInt($item.data('display-index'), 10);

        // If no item selected, select this one
        if (selectedItem === null) {
          selectedItem = this;
          selectedIndex = itemIndex;
          $item.addClass('splms-ordering-selected');
          self.showMobileInstruction('Tap another item to swap positions');
          return;
        }

        // If clicking the same item, deselect it
        if (selectedItem === this) {
          selectedItem = null;
          selectedIndex = -1;
          $item.removeClass('splms-ordering-selected');
          self.hideMobileInstruction();
          return;
        }

        // Swap positions with selected item
        var targetIndex = itemIndex;
        if (selectedIndex !== targetIndex) {
          self.handleReorder(selectedIndex, targetIndex);
        }

        // Clear selection
        selectedItem = null;
        selectedIndex = -1;
        self.container.find('.splms-ordering-item').removeClass('splms-ordering-selected');
        self.hideMobileInstruction();
      });

      // Click outside to deselect
      jQuery(document).on('click', function (e) {
        if (!self.container.is(e.target) && self.container.has(e.target).length === 0) {
          selectedItem = null;
          selectedIndex = -1;
          self.container.find('.splms-ordering-item').removeClass('splms-ordering-selected');
          self.hideMobileInstruction();
        }
      });
    }

    /**
     * Show mobile instruction
     */
  }, {
    key: "showMobileInstruction",
    value: function showMobileInstruction(text) {
      var $instruction = this.container.find('.splms-mobile-instruction');
      if (!$instruction.length) {
        $instruction = jQuery('<div class="splms-mobile-instruction"></div>');
        this.container.prepend($instruction);
      }
      $instruction.text(text).show();
    }

    /**
     * Hide mobile instruction
     */
  }, {
    key: "hideMobileInstruction",
    value: function hideMobileInstruction() {
      this.container.find('.splms-mobile-instruction').hide();
    }

    /**
        * Update the hidden input with current answer order
        * Stores array of option IDs in the order they appear
        */
  }, {
    key: "updateAnswer",
    value: function updateAnswer() {
      var $hiddenInput = this.container.find("#ordering_input_".concat(this.questionId));
      // Store array of option IDs in current order
      var answerJson = JSON.stringify(this.currentOrder);
      $hiddenInput.val(answerJson);

      // Also update quiz answers directly
      if (this.quiz && this.quiz.answers) {
        this.quiz.answers[this.questionId] = this.currentOrder;
        if (typeof this.quiz.saveQuizState === 'function') {
          this.quiz.saveQuizState();
        }
      }

      // Trigger change event
      var hiddenInputElement = $hiddenInput[0];
      if (hiddenInputElement) {
        var nativeEvent = new Event('change', {
          bubbles: true
        });
        hiddenInputElement.dispatchEvent(nativeEvent);
      }
      $hiddenInput.trigger('change');
    }

    /**
        * Get current answer as array
        */
  }, {
    key: "getAnswer",
    value: function getAnswer() {
      return _toConsumableArray(this.currentOrder);
    }

    /**
        * Escape HTML
        */
  }, {
    key: "escapeHtml",
    value: function escapeHtml(text) {
      var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        '\'': '&#039;'
      };
      return String(text).replace(/[&<>"']/g, function (m) {
        return map[m];
      });
    }
  }]);
}(); // Export for module systems
if ( true && module.exports) {
  module.exports = SPLMSQuestionOrdering;
}

// Make available globally
if (typeof window !== 'undefined') {
  window.SPLMSQuestionOrdering = SPLMSQuestionOrdering;
}

/***/ },

/***/ "./src/js/frontend/modules/quizzes/question-renderer.js"
/*!**************************************************************!*\
  !*** ./src/js/frontend/modules/quizzes/question-renderer.js ***!
  \**************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   QuestionRenderer: () => (/* binding */ QuestionRenderer),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./quiz-utils.js */ "./src/js/frontend/modules/quizzes/quiz-utils.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Question Renderer
 * Single source of truth for question type templates and rendering
 * @module question-renderer
 */



/**
 * QuestionRenderer class - handles all question rendering logic
 * Centralizes template mapping and rendering to eliminate duplication
 */
var QuestionRenderer = /*#__PURE__*/function () {
  function QuestionRenderer() {
    _classCallCheck(this, QuestionRenderer);
    this.templates = {};
    this.initTemplates();
  }

  /**
   * Initialize WordPress templates
   * Single mapping of question types to templates
   */
  return _createClass(QuestionRenderer, [{
    key: "initTemplates",
    value: function initTemplates() {
      if (typeof wp === 'undefined' || !wp.template) {
        console.warn('WordPress templates not available');
        return;
      }

      // Main question wrapper template
      this.questionTemplate = wp.template('quiz-question');
      this.loadingTemplate = wp.template('quiz-loading');

      // Question type-specific templates - SINGLE SOURCE OF TRUTH
      this.templates = {
        'multiple_choice': wp.template('quiz-question-multiple-choice'),
        'multiple_select': wp.template('quiz-question-multiple-select'),
        'true_false': wp.template('quiz-question-true-false'),
        'short_answer': wp.template('quiz-question-short-answer'),
        'fill_blank': wp.template('quiz-question-short-answer'),
        // Explicit alias
        'essay': wp.template('quiz-question-essay'),
        'long_answer': wp.template('quiz-question-essay'),
        // Explicit alias
        'matching': wp.template('quiz-question-matching'),
        'ordering': wp.template('quiz-question-ordering'),
        'file_upload': wp.template('quiz-question-file-upload')
      };
    }

    /**
     * Get template for question type
     * @param {string} questionType - Question type
     * @returns {Function|null} Template function or null
     */
  }, {
    key: "getTemplate",
    value: function getTemplate(questionType) {
      return this.templates[questionType] || null;
    }

    /**
     * Render question options HTML using appropriate template
     * @param {Object} questionData - Question data object
     * @returns {string} Rendered HTML
     */
  }, {
    key: "renderOptions",
    value: function renderOptions(questionData) {
      var template = this.getTemplate(questionData.type);
      if (template) {
        return template(questionData);
      }

      // Template not found - show simple message
      return "<div class=\"splms-quiz-template-not-found\">Template not found for question type: ".concat((0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(questionData.type), "</div>");
    }

    /**
     * Render complete question HTML
     * @param {Object} questionData - Question data
     * @param {*} selectedAnswer - Current selected answer
     * @returns {string} Complete question HTML
     */
  }, {
    key: "render",
    value: function render(questionData) {
      var selectedAnswer = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
      // Prepare question data for template
      var data = {
        id: questionData.id,
        type: questionData.type,
        question: questionData.question || questionData.question_text,
        description: questionData.description || '',
        explanation: questionData.explanation || '',
        points: questionData.points || 1,
        options: Array.isArray(questionData.options) ? questionData.options : [],
        pairs: Array.isArray(questionData.pairs) ? questionData.pairs : [],
        items: Array.isArray(questionData.items) ? questionData.items : [],
        selectedAnswer: selectedAnswer || questionData.selectedAnswer || '',
        questionNumber: questionData.questionNumber || 1,
        totalQuestions: questionData.totalQuestions || 1,
        showExplanation: questionData.showExplanation || false
      };

      // Get options HTML
      var optionsHtml = this.renderOptions(data);

      // Use main question template if available
      if (this.questionTemplate) {
        return this.questionTemplate(_objectSpread(_objectSpread({}, data), {}, {
          optionsHtml: optionsHtml
        }));
      }

      // Template not found - show simple message
      return "<div class=\"splms-quiz-template-not-found\">Question template not found. Please ensure templates are loaded.</div>";
    }

    /**
     * Format answer for display in results
     * @param {*} answer - Answer value
     * @param {string} questionType - Question type
     * @param {Object} question - Full question object with options
     * @returns {string} Formatted answer HTML
     */
  }, {
    key: "formatAnswerForDisplay",
    value: function formatAnswerForDisplay(answer, questionType) {
      var question = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      if (!answer || answer === '' || Array.isArray(answer) && answer.length === 0) {
        return 'No answer';
      }
      var getOptionText = function getOptionText(optionId, options) {
        if (!options || !Array.isArray(options)) return String(optionId);
        var option = options.find(function (opt) {
          return opt.id === String(optionId) || opt.id === optionId;
        });
        return option ? option.text || option.option_text || String(optionId) : String(optionId);
      };
      if (questionType === 'multiple_choice' || questionType === 'true_false') {
        if (question.options && Array.isArray(question.options)) {
          return (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(getOptionText(answer, question.options));
        }
        return (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(String(answer));
      }
      if (questionType === 'multiple_select') {
        var answers = Array.isArray(answer) ? answer : [answer];
        if (answers.length === 0) return 'No answer';
        if (question.options && Array.isArray(question.options)) {
          var answerTexts = answers.map(function (optionId) {
            return getOptionText(optionId, question.options);
          });
          return answerTexts.map(function (a) {
            return (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(a);
          }).join(', ');
        }
        return answers.map(function (a) {
          return (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(String(a));
        }).join(', ');
      }
      if (questionType === 'ordering') {
        // Handle null, undefined, or empty values
        if (!answer || answer === null || answer === undefined) {
          return 'No answer';
        }

        // Parse JSON string if needed
        var parsedAnswer = answer;
        if (typeof answer === 'string' && answer.trim() !== '') {
          // Check if it's a JSON array string
          if (answer.trim().startsWith('[') && answer.trim().endsWith(']')) {
            try {
              parsedAnswer = JSON.parse(answer);
            } catch (e) {
              // If parsing fails, treat as regular string
              parsedAnswer = answer;
            }
          }
        }

        // Answer is array of text values (not IDs)
        if (Array.isArray(parsedAnswer) && parsedAnswer.length > 0) {
          // Answer already contains text values, just format them
          var _answerTexts = parsedAnswer.filter(function (v) {
            return v !== null && v !== undefined && v !== '';
          }).map(function (v) {
            // Handle different answer formats
            if (_typeof(v) === 'object' && v !== null && v.text) {
              return String(v.text);
            }
            return String(v);
          });
          return _answerTexts.length > 0 ? _answerTexts.map(function (text) {
            return (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(text);
          }).join(' → ') : 'No answer';
        }

        // Handle string format (already formatted with arrows or single value)
        if (typeof parsedAnswer === 'string' && parsedAnswer.trim() !== '') {
          return (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(parsedAnswer);
        }
        return 'No answer';
      }
      if (questionType === 'file_upload') {
        if (typeof answer === 'number' || typeof answer === 'string' && /^\d+$/.test(answer)) {
          var attachmentId = parseInt(answer);
          return "<a href=\"#\" target=\"_blank\" class=\"file-upload-link\">View Uploaded File (ID: ".concat(attachmentId, ")</a>");
        } else if (typeof answer === 'string' && answer.startsWith('http')) {
          return "<a href=\"".concat((0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(answer), "\" target=\"_blank\" class=\"file-upload-link\">").concat((0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(answer), "</a>");
        }
        return (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(String(answer));
      }
      if (Array.isArray(answer)) {
        return answer.map(function (a) {
          return (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(String(a));
        }).join(', ');
      }
      return (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(String(answer));
    }
  }]);
}();

// Export singleton instance
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (new QuestionRenderer());

/***/ },

/***/ "./src/js/frontend/modules/quizzes/questions.js"
/*!******************************************************!*\
  !*** ./src/js/frontend/modules/quizzes/questions.js ***!
  \******************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./quiz-utils.js */ "./src/js/frontend/modules/quizzes/quiz-utils.js");
/* harmony import */ var _question_renderer_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./question-renderer.js */ "./src/js/frontend/modules/quizzes/question-renderer.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * SkillPulse LMS Questions Handler (Refactored)
 * Handles question rendering, display, and answer management
 * Works in conjunction with the main Quiz handler
 * 
 * @class SPLMSQuestions
 * @module quizzes/questions
 */


var SPLMSQuestions = /*#__PURE__*/function () {
  /**
   * @param {Object} quizInstance - Reference to main quiz instance
   */
  function SPLMSQuestions(quizInstance) {
    _classCallCheck(this, SPLMSQuestions);
    this.quiz = quizInstance;
    this.questionRenderer = _question_renderer_js__WEBPACK_IMPORTED_MODULE_1__["default"];
    this.loadingTemplate = null;
    this.initTemplates();
  }

  /**
   * Initialize WordPress templates if available
   * @private
   */
  return _createClass(SPLMSQuestions, [{
    key: "initTemplates",
    value: function initTemplates() {
      if (typeof wp !== 'undefined' && wp.template) {
        this.loadingTemplate = wp.template('quiz-loading');
        this.questionReviewTemplate = wp.template('quiz-question-review');
      }
    }

    /**
     * Get options HTML for a specific question type using QuestionRenderer
     * @param {Object} questionData - Question data object
     * @returns {string} Rendered options HTML
     */
  }, {
    key: "getQuestionOptionsHtml",
    value: function getQuestionOptionsHtml(questionData) {
      return this.questionRenderer.renderOptions(questionData);
    }

    /**
     * Render the current question
     */
  }, {
    key: "renderCurrentQuestion",
    value: function renderCurrentQuestion() {
      if (!this.quiz.questionsData || this.quiz.questionsData.length === 0) {
        return;
      }
      var currentQuestionIndex = this.quiz.currentQuestion;
      var question = this.quiz.questionsData[currentQuestionIndex];
      if (!question) {
        console.error('Question not found at index:', currentQuestionIndex);
        return;
      }

      // Prepare question data for template
      var questionSettings = question.settings || {};
      // Ensure pairs and items are arrays
      var pairs = Array.isArray(question.pairs) ? question.pairs : [];
      var items = Array.isArray(question.items) ? question.items : [];
      var options = Array.isArray(question.options) ? question.options : [];

      // For ordering questions, build items from options if items not available
      if (question.type === 'ordering' && items.length === 0 && options.length > 0) {
        items = options.map(function (option) {
          return {
            id: option.text || option.option_text || '',
            text: option.text || option.option_text || ''
          };
        });
      }
      var questionData = {
        id: question.id,
        type: question.type,
        question: question.question,
        description: question.description || '',
        explanation: question.explanation || '',
        points: question.points || 1,
        options: options,
        pairs: pairs,
        items: items,
        allowed_types: question.allowed_types || 'pdf,docx,jpg,png',
        max_file_size: question.max_file_size || 10,
        randomize_options: questionSettings.randomize_options || false,
        questionNumber: currentQuestionIndex + 1,
        totalQuestions: this.quiz.questionsData.length,
        selectedAnswer: this.quiz.answers[question.id] || '',
        showExplanation: false // Only show during review
      };
      // Ensure selectedAnswer is properly formatted for template
      if (questionData.selectedAnswer && typeof questionData.selectedAnswer === 'string') {
        // Trim whitespace for text-based answers
        questionData.selectedAnswer = questionData.selectedAnswer.trim();
      }

      // Render question using template
      var questionHtml = this.buildQuestionHtml(questionData);
      // Insert into container
      var $container = jQuery('#splms-quiz-interface .splms-quiz-questions-container');
      $container.html(questionHtml);

      // Initialize matching question handler if needed
      if (questionData.type === 'matching') {
        this.initMatchingQuestion($container, questionData);
      }

      // Initialize ordering question handler if needed
      if (questionData.type === 'ordering') {
        this.initOrderingQuestion($container, questionData);
      }

      // Bind events for this question
      this.bindQuestionEvents();
    }

    /**
     * Build question HTML using QuestionRenderer
     * @param {Object} questionData - Question data object
     * @returns {string} Complete question HTML
     */
  }, {
    key: "buildQuestionHtml",
    value: function buildQuestionHtml(questionData) {
      // Use QuestionRenderer for rendering (single source of truth)
      // QuestionRenderer handles template-based rendering
      return this.questionRenderer.render(questionData, questionData.selectedAnswer);
    }

    /**
     * Bind events for question interactions
     */
    /**
     * Initialize matching question drag-and-drop interface
     */
  }, {
    key: "initOrderingQuestion",
    value: function initOrderingQuestion($container, questionData) {
      // Find the ordering container
      var $orderingContainer = $container.find('.splms-ordering-container');
      if ($orderingContainer.length === 0) {
        return;
      }

      // Import and initialize ordering handler
      if (typeof SPLMSQuestionOrdering !== 'undefined') {
        // Parse selectedAnswer if it's a JSON string
        var selectedAnswer = questionData.selectedAnswer;
        if (typeof selectedAnswer === 'string' && selectedAnswer.trim().startsWith('[')) {
          try {
            selectedAnswer = JSON.parse(selectedAnswer);
          } catch (e) {
            selectedAnswer = [];
          }
        }
        questionData.selectedAnswer = Array.isArray(selectedAnswer) ? selectedAnswer : [];

        // Initialize ordering handler with quiz instance reference
        this.orderingHandler = new SPLMSQuestionOrdering($orderingContainer, questionData, this.quiz);
      } else {
        console.warn('SPLMSQuestionOrdering not available. Make sure question-ordering.js is loaded.');
      }
    }
  }, {
    key: "initMatchingQuestion",
    value: function initMatchingQuestion($container, questionData) {
      // Find the matching container
      var $matchingContainer = $container.find('.splms-matching-container');
      if ($matchingContainer.length === 0) {
        return;
      }

      // Import and initialize matching handler
      // Note: This assumes question-matching.js is loaded
      if (typeof SPLMSQuestionMatching !== 'undefined') {
        // Parse selectedAnswer if it's a JSON string
        var selectedAnswer = questionData.selectedAnswer;
        if (typeof selectedAnswer === 'string' && selectedAnswer.trim().startsWith('[')) {
          try {
            selectedAnswer = JSON.parse(selectedAnswer);
          } catch (e) {
            selectedAnswer = [];
          }
        }
        questionData.selectedAnswer = Array.isArray(selectedAnswer) ? selectedAnswer : [];

        // Initialize matching handler with quiz instance reference
        this.matchingHandler = new SPLMSQuestionMatching($matchingContainer, questionData, this.quiz);
      } else {
        console.warn('SPLMSQuestionMatching not available. Make sure question-matching.js is loaded.');
      }
    }
  }, {
    key: "bindQuestionEvents",
    value: function bindQuestionEvents() {
      var self = this;

      // Bind radio button change events (single select)
      jQuery('#splms-quiz-interface input[type="radio"]').off('change').on('change', function () {
        var questionId = jQuery(this).data('question-id');
        var value = jQuery(this).val(); // This is now the option ID

        // Update selected state visually
        var $option = jQuery(this).closest('.answer-option');
        $option.addClass('selected').siblings('.answer-option').removeClass('selected');

        // Store answer as option ID
        self.quiz.answers[questionId] = value;
        self.quiz.saveQuizState();
      });

      // Bind checkbox change events (multiple select)
      jQuery('#splms-quiz-interface input[type="checkbox"]').off('change').on('change', function () {
        var questionId = jQuery(this).data('question-id');
        var $question = jQuery(this).closest('.splms-quiz-question-card');
        var $checkboxes = $question.find('input[type="checkbox"][data-question-id="' + questionId + '"]');

        // Update selected state visually
        var $option = jQuery(this).closest('.answer-option');
        if (jQuery(this).is(':checked')) {
          $option.addClass('selected');
        } else {
          $option.removeClass('selected');
        }
        // Collect all selected option IDs (checkbox value is now option ID)
        var selectedValues = [];
        $checkboxes.filter(':checked').each(function () {
          // Use option ID from value attribute (which is now set to option ID)
          var optionId = jQuery(this).val();
          if (optionId) {
            selectedValues.push(optionId);
          }
        });

        // Store answer as array of option IDs for multiple select
        // Only update if we have selected values, or if user explicitly unchecked (to allow clearing)
        // But preserve existing answer if checkboxes aren't found (might be on different page)
        if ($checkboxes.length > 0) {
          self.quiz.answers[questionId] = selectedValues.length > 0 ? selectedValues : '';
          self.quiz.saveQuizState();
        }
      });

      // Bind textarea change events for essay questions
      jQuery('#splms-quiz-interface textarea').off('input').on('input', function () {
        var questionId = jQuery(this).data('question-id');
        var value = jQuery(this).val().trim();
        self.quiz.answers[questionId] = value;
        self.quiz.saveQuizState();
      });

      // Bind text input events for short answer questions
      jQuery('#splms-quiz-interface input[type="text"]').off('input').on('input', function () {
        var questionId = jQuery(this).data('question-id');
        var value = jQuery(this).val().trim();
        self.quiz.answers[questionId] = value;
        self.quiz.saveQuizState();
      });

      // Bind ordering question answer change events (drag-and-drop)
      // Use event delegation to catch dynamically created inputs
      jQuery('#splms-quiz-interface').off('change', 'input[name^="question_"][id^="ordering_input_"]').on('change', 'input[name^="question_"][id^="ordering_input_"]', function () {
        var questionId = jQuery(this).data('question-id');
        var answerValue = jQuery(this).val();

        // Parse JSON string to array
        var answerArray = [];
        if (answerValue) {
          try {
            answerArray = JSON.parse(answerValue);
          } catch (e) {
            answerArray = [];
          }
        }

        // Update quiz answers
        self.quiz.answers[questionId] = answerArray;

        // Save quiz state
        if (typeof self.quiz.saveQuizState === 'function') {
          self.quiz.saveQuizState();
        }
      });

      // Bind matching question answer change events (drag-and-drop)
      // Use event delegation to catch dynamically created inputs
      jQuery('#splms-quiz-interface').off('change', 'input[name^="question_"][id^="matching-answer-"]').on('change', 'input[name^="question_"][id^="matching-answer-"]', function () {
        var questionId = jQuery(this).data('question-id');
        var answerValue = jQuery(this).val();

        // Parse JSON string to array
        var answerArray = [];
        if (answerValue) {
          try {
            answerArray = JSON.parse(answerValue);
          } catch (e) {
            // If not JSON, treat as string
            answerArray = answerValue;
          }
        }

        // Update quiz answers
        self.quiz.answers[questionId] = answerArray;

        // Save quiz state
        if (typeof self.quiz.saveQuizState === 'function') {
          self.quiz.saveQuizState();
        }
      });

      // Initialize sortable for ordering questions (requires jQuery UI Sortable)
      if (typeof jQuery.fn.sortable !== 'undefined') {
        jQuery('#splms-quiz-interface .ordering-list').each(function () {
          var $list = jQuery(this);
          var questionId = $list.closest('.quiz-question-card').data('question-id');
          if (!$list.hasClass('ui-sortable')) {
            $list.sortable({
              handle: '.ordering-handle',
              update: function update() {
                var order = [];
                $list.find('.ordering-item').each(function () {
                  order.push(parseInt(jQuery(this).data('item-index')));
                });
                self.quiz.answers[questionId] = order;
                jQuery('#ordering_input_' + questionId).val(JSON.stringify(order));
                self.quiz.saveQuizState();
              }
            });
          }
        });
      } else {
        // Manual reordering with up/down buttons
        jQuery('#splms-quiz-interface .ordering-item').each(function () {
          var $item = jQuery(this);
          if (!$item.find('.ordering-controls').length) {
            $item.append("\n                        <div class=\"ordering-controls\">\n                            <button type=\"button\" class=\"ordering-up\">\u2191</button>\n                            <button type=\"button\" class=\"ordering-down\">\u2193</button>\n                        </div>\n                    ");
          }
        });
        jQuery('#splms-quiz-interface .ordering-up').off('click').on('click', function () {
          var $item = jQuery(this).closest('.ordering-item');
          var $prev = $item.prev();
          if ($prev.length) {
            $item.insertBefore($prev);
            self.updateOrderingAnswer($item.closest('.ordering-container'));
          }
        });
        jQuery('#splms-quiz-interface .ordering-down').off('click').on('click', function () {
          var $item = jQuery(this).closest('.ordering-item');
          var $next = $item.next();
          if ($next.length) {
            $item.insertAfter($next);
            self.updateOrderingAnswer($item.closest('.ordering-container'));
          }
        });
      }

      // Bind file upload change events
      jQuery('#splms-quiz-interface input[type="file"]').off('change').on('change', function () {
        var questionId = jQuery(this).data('question-id');
        var file = this.files[0];
        var $label = jQuery(this).siblings('.file-upload-label').find('.file-upload-text');
        if (file) {
          $label.text(file.name);

          // Validate file size
          var maxSize = parseInt(jQuery(this).data('max-size'));
          if (file.size > maxSize) {
            alert('File size exceeds maximum allowed size.');
            jQuery(this).val('');
            $label.text('No file chosen');
            // Remove from answers and fileObjects
            delete self.quiz.answers[questionId];
            delete self.quiz.fileObjects[questionId];
            self.quiz.saveQuizState();
            return;
          }

          // Store file name in answers (for state saving)
          self.quiz.answers[questionId] = file.name;
          // Store File object in memory (for final submission)
          self.quiz.fileObjects[questionId] = file;
        } else {
          $label.text('No file chosen');
          // Remove from answers and fileObjects
          delete self.quiz.answers[questionId];
          delete self.quiz.fileObjects[questionId];
        }

        // Save state on file selection/removal
        self.quiz.saveQuizState();
      });

      // Bind remove file button
      jQuery('#splms-quiz-interface .remove-file-btn').off('click').on('click', function () {
        var questionId = jQuery(this).data('question-id');
        var $container = jQuery(this).closest('.file-upload-container');
        var $input = $container.find('input[type="file"]');
        $input.val('');
        $container.find('.file-upload-text').text('No file chosen');
        $container.find('.file-upload-preview').remove();
        self.quiz.answers[questionId] = '';
        self.quiz.saveQuizState();
      });
    }

    /**
     * Helper to update ordering answer
     */
  }, {
    key: "updateOrderingAnswer",
    value: function updateOrderingAnswer($container) {
      var questionId = $container.data('question-id');
      var $list = $container.find('.ordering-list');
      var order = [];
      $list.find('.ordering-item').each(function () {
        order.push(parseInt(jQuery(this).data('item-index')));
      });
      this.quiz.answers[questionId] = order;
      jQuery('#ordering_input_' + questionId).val(JSON.stringify(order));
      this.quiz.saveQuizState();
    }

    /**
     * Restore answers in the UI from saved state
     */
  }, {
    key: "restoreAnswersInUI",
    value: function restoreAnswersInUI() {
      var _this = this;
      // Ensure we have answers to restore
      if (!this.quiz.answers || Object.keys(this.quiz.answers).length === 0) {
        return;
      }
      Object.keys(this.quiz.answers).forEach(function (questionId) {
        var answer = _this.quiz.answers[questionId];

        // Skip empty answers
        if (!answer || answer === '' || Array.isArray(answer) && answer.length === 0) {
          return;
        }

        // Try multiple selectors to find the question card
        var $question = jQuery(".splms-quiz-question-card[data-question-id=\"".concat(questionId, "\"]"));
        if ($question.length === 0) {
          return;
        }
        var questionType = $question.data('question-type');
        if (questionType === 'multiple_select') {
          // Handle multiple select - array of option IDs
          var selectedAnswers = Array.isArray(answer) ? answer : answer ? [answer] : [];
          // Store the answer before restoring to prevent change handler from clearing it
          var savedAnswer = _this.quiz.answers[questionId];
          selectedAnswers.forEach(function (selectedValue) {
            if (!selectedValue) return;
            var normalizedValue = String(selectedValue).trim();

            // Match by checkbox value (option ID)
            var $checkbox = $question.find("input[type=\"checkbox\"][value=\"".concat((0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(normalizedValue), "\"]"));
            if ($checkbox.length > 0) {
              $checkbox.prop('checked', true);
              $checkbox.closest('.answer-option, .option-label').addClass('selected');
              // Don't trigger change event during restoration - it can clear answers
              // The checked state is already set, so the visual state is correct
            }
          });

          // Ensure the answer is preserved after restoration
          if (savedAnswer && (Array.isArray(savedAnswer) ? savedAnswer.length > 0 : savedAnswer !== '')) {
            _this.quiz.answers[questionId] = savedAnswer;
          }
        } else if (questionType === 'multiple_choice' || questionType === 'true_false') {
          // Handle single select - radio buttons
          var answerValue = Array.isArray(answer) ? answer[0] : answer;
          if (!answerValue) return;
          var normalizedAnswer = String(answerValue).trim();

          // Match by radio value (option ID)
          var $radio = $question.find("input[type=\"radio\"][value=\"".concat((0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(normalizedAnswer), "\"]"));
          if ($radio.length > 0) {
            // Store the answer before restoring to prevent change handler from clearing it
            var _savedAnswer = _this.quiz.answers[questionId];
            $radio.prop('checked', true);
            $radio.closest('.answer-option, .option-label').addClass('selected');
            $question.find("input[type=\"radio\"][name=\"".concat($radio.attr('name'), "\"]")).not($radio).prop('checked', false);
            // Don't trigger change event during restoration - it can clear answers
            // The checked state is already set, so the visual state is correct

            // Ensure the answer is preserved after restoration
            if (_savedAnswer) {
              _this.quiz.answers[questionId] = _savedAnswer;
            }
          }
        } else if (questionType === 'short_answer' || questionType === 'fill_blank') {
          // Handle text input
          var _answerValue = Array.isArray(answer) ? answer[0] : answer;
          var $input = $question.find('input[type="text"]');
          if ($input.length > 0) {
            $input.val(_answerValue || '');
          }
        } else if (questionType === 'essay' || questionType === 'long_answer') {
          // Handle textarea
          var _answerValue2 = Array.isArray(answer) ? answer[0] : answer;
          var $textarea = $question.find('textarea');
          if ($textarea.length > 0) {
            $textarea.val(_answerValue2 || '');
          }
        } else if (questionType === 'matching') {
          // Handle matching - restore selected pairs
          if (_typeof(answer) === 'object' && answer !== null) {
            Object.keys(answer).forEach(function (pairIndex) {
              var $select = $question.find(".matching-select[data-pair-index=\"".concat(pairIndex, "\"]"));
              if ($select.length > 0) {
                $select.val(answer[pairIndex]);
              }
            });
          }
        } else if (questionType === 'ordering') {
          // Handle ordering - restore item order using option IDs
          if (Array.isArray(answer) && answer.length > 0) {
            var $list = $question.find('.splms-ordering-list, .ordering-list');
            var items = [];
            $list.find('.splms-ordering-item, .ordering-item').each(function () {
              var $item = jQuery(this);
              var optionId = $item.data('option-id') || $item.attr('data-option-id');
              if (optionId) {
                items.push({
                  optionId: optionId,
                  element: $item
                });
              }
            });

            // Sort items by answer order (answer contains option IDs)
            items.sort(function (a, b) {
              var aPos = answer.indexOf(a.optionId);
              var bPos = answer.indexOf(b.optionId);
              return aPos - bPos;
            });

            // Reorder DOM elements
            items.forEach(function (item) {
              $list.append(item.element);
            });

            // Update hidden input with option IDs
            jQuery('#ordering_input_' + questionId).val(JSON.stringify(answer));
          }
        } else if (questionType === 'file_upload') {
          // Handle file upload - show uploaded file name
          var _answerValue3 = Array.isArray(answer) ? answer[0] : answer;
          if (_answerValue3) {
            var $container = $question.find('.file-upload-container');
            if (!$container.find('.file-upload-preview').length) {
              $container.find('.file-upload-field').before("\n                            <div class=\"file-upload-preview\">\n                                <p>Current file: <strong>".concat((0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(_answerValue3), "</strong></p>\n                                <button type=\"button\" class=\"splms-btn splms-btn-secondary remove-file-btn\" data-question-id=\"").concat(questionId, "\">\n                                    Remove File\n                                </button>\n                            </div>\n                        "));
            }
            $container.find('.file-upload-text').text(_answerValue3);
          }
        }
      });
    }

    /**
     * Populate detailed results for question review
     */
  }, {
    key: "populateDetailedResults",
    value: function populateDetailedResults($container) {
      var _this2 = this;
      if (!this.quiz.currentResults) {
        console.error('No current results available');
        $container.html('<div class="no-detailed-results">No quiz results available.</div>');
        return;
      }
      if (!this.quiz.questionsData || this.quiz.questionsData.length === 0) {
        console.error('No questions data available');
        $container.html('<div class="no-detailed-results">No questions data available.</div>');
        return;
      }
      var resultsHtml = '';

      // Use server-provided detailed results
      if (!this.quiz.currentResults.detailed_results || !Array.isArray(this.quiz.currentResults.detailed_results)) {
        $container.html('<div class="no-detailed-results">No detailed results available.</div>');
        return;
      }
      this.quiz.currentResults.detailed_results.forEach(function (result, index) {
        var questionNumber = index + 1;
        // Match question by ID (handle both string and number types)
        var question = _this2.quiz.questionsData.find(function (q) {
          var qId = parseInt(q.id);
          var rId = parseInt(result.question_id);
          return qId === rId || String(q.id) === String(result.question_id);
        });
        if (!question) {
          console.warn('Question not found for result:', result.question_id, 'Available questions:', _this2.quiz.questionsData.map(function (q) {
            return q.id;
          }));
          return;
        }
        var isCorrect = result.is_correct;
        // Get user answer - handle different formats
        var userAnswer = result.user_answer;
        if (userAnswer === null || userAnswer === undefined || userAnswer === '') {
          userAnswer = null; // Will be formatted as 'No answer'
        } else if (typeof userAnswer === 'string' && userAnswer.trim().startsWith('[')) {
          // Parse JSON string for ordering/matching questions
          try {
            userAnswer = JSON.parse(userAnswer);
          } catch (e) {
            // Keep as string if parsing fails
          }
        }
        var correctAnswer = result.correct_answer || 'N/A';

        // Pass needs_manual_review flag from result to question object
        if (result.needs_manual_review !== undefined) {
          question.needs_manual_review = result.needs_manual_review;
        }

        // Pass feedback from result to question object
        if (result.feedback !== undefined) {
          question.feedback = result.feedback;
        }
        resultsHtml += _this2.buildResultHtml(questionNumber, question, userAnswer, correctAnswer, isCorrect);
      });
      if (resultsHtml.length === 0) {
        resultsHtml = '<div class="no-detailed-results">No detailed results could be generated.</div>';
      }
      $container.html(resultsHtml);
    }

    /**
     * Helper function to build result HTML for a single question
     * Uses wp.template for consistent templating
     */
  }, {
    key: "buildResultHtml",
    value: function buildResultHtml(questionNumber, question, userAnswer, correctAnswer, isCorrect) {
      var _this$quiz$currentRes;
      var userAnswerDisplay = this.formatAnswerForDisplay(userAnswer, question.type, question);
      var correctAnswerDisplay = this.formatAnswerForDisplay(correctAnswer, question.type, question);

      // Special handling for matching questions - show pairs with correct/incorrect highlighting
      if (question.type === 'matching') {
        var pairs = Array.isArray(question.pairs) ? question.pairs : [];

        // Parse matching answer to array format
        var parseMatchingAnswer = function parseMatchingAnswer(answer) {
          // Backend returns comma-separated string (e.g., "Server, Browser, Database")
          if (typeof answer === 'string') {
            return answer.split(',').map(function (val) {
              return val.trim();
            }).filter(function (val) {
              return val !== '';
            });
          }
          // Object format {left_text: right_text}
          if (_typeof(answer) === 'object' && answer !== null) {
            return pairs.map(function (pair) {
              var leftText = (pair === null || pair === void 0 ? void 0 : pair.left) || '';
              return answer[leftText] || '';
            });
          }
          // Array format
          if (Array.isArray(answer)) {
            return answer;
          }
          return [];
        };
        var userAnswerArray = parseMatchingAnswer(userAnswer);

        // Build matching pairs data for template
        var matchingPairs = pairs.map(function (pair, index) {
          var _question$settings;
          var leftText = (pair === null || pair === void 0 ? void 0 : pair.left) || '';
          var correctRight = (pair === null || pair === void 0 ? void 0 : pair.right) || '';
          var userRight = userAnswerArray[index] || '';

          // Compare answers
          var normalize = function normalize(text) {
            return String(text || '').toLowerCase().trim();
          };
          var caseSensitive = ((_question$settings = question.settings) === null || _question$settings === void 0 ? void 0 : _question$settings.case_sensitive) || false;
          var isMatchCorrect = caseSensitive ? String(userRight).trim() === String(correctRight).trim() : normalize(userRight) === normalize(correctRight);
          return {
            leftText: leftText,
            userRight: userRight,
            correctRight: correctRight,
            isCorrect: isMatchCorrect
          };
        });

        // Use template for matching display
        if (typeof wp !== 'undefined' && wp.template) {
          var matchingTemplate = wp.template('quiz-matching-results');
          userAnswerDisplay = matchingTemplate({
            pairs: matchingPairs
          });
        } else {
          // Fallback if template not available
          userAnswerDisplay = '<div class="splms-matching-results-container">Matching results unavailable</div>';
        }
        correctAnswerDisplay = ''; // Don't show separate correct answer for matching
      }

      // Special handling for essay questions
      if (question.type === 'essay' || question.type === 'long_answer') {
        var _needsReview = question.needs_manual_review !== false; // Default to true for essays
        if (_needsReview) {
          correctAnswerDisplay = 'Pending Review';
          // Show a special status indicator for pending review
          isCorrect = false; // Essays are not marked correct until reviewed
        }
      }

      // Special handling for file upload
      if (question.type === 'file_upload') {
        if (userAnswer) {
          // Check if we have a URL from server (from detailed_results)
          if (question.user_answer_url) {
            userAnswerDisplay = "<a href=\"".concat((0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(question.user_answer_url), "\" target=\"_blank\" class=\"file-upload-link\">View Uploaded File</a>");
          } else if (typeof userAnswer === 'string' && (userAnswer.startsWith('http') || userAnswer.startsWith('/'))) {
            // It's already a URL
            userAnswerDisplay = "<a href=\"".concat((0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(userAnswer), "\" target=\"_blank\" class=\"file-upload-link\">View Uploaded File</a>");
          } else if (typeof userAnswer === 'number' || typeof userAnswer === 'string' && /^\d+$/.test(userAnswer)) {
            // It's an attachment ID
            var attachmentId = parseInt(userAnswer);
            userAnswerDisplay = "File uploaded (Attachment ID: ".concat(attachmentId, ")");
          } else {
            userAnswerDisplay = this.formatAnswerForDisplay(userAnswer, question.type, question);
          }
        } else {
          userAnswerDisplay = 'No file uploaded';
        }
        var _needsReview2 = question.needs_manual_review !== false; // Default to true for file uploads
        correctAnswerDisplay = _needsReview2 ? 'Pending Review' : 'Requires manual grading';
      }

      // Check if question needs manual review
      var needsReview = question.needs_manual_review === true || question.type === 'essay' || question.type === 'long_answer' || question.type === 'file_upload';
      var reviewIcon = needsReview ? '⏳' : isCorrect ? '✅' : '❌';

      // Check if feedback should be shown (default to false if not set)
      var feedbackEnabled = ((_this$quiz$currentRes = this.quiz.currentResults) === null || _this$quiz$currentRes === void 0 ? void 0 : _this$quiz$currentRes.feedback_enabled) === true;
      var showFeedback = feedbackEnabled && question.feedback;

      // Use wp.template
      var templateData = {
        questionId: question.id,
        questionNumber: questionNumber,
        questionText: question.question || question.question_text || "Question ".concat(questionNumber),
        points: question.points || 1,
        isCorrect: isCorrect,
        needsReview: needsReview,
        reviewIcon: reviewIcon,
        userAnswerDisplay: userAnswerDisplay,
        correctAnswerDisplay: correctAnswerDisplay,
        explanation: question.explanation || '',
        questionType: question.type,
        feedback: question.feedback || '',
        showFeedback: showFeedback
      };
      return this.questionReviewTemplate(templateData);
    }

    /**
     * Format answer for display based on question type
     * Maps option IDs to option texts when needed
     * @param {*} answer - Answer value
     * @param {string} questionType - Question type
     * @param {Object} question - Full question object with options
     * @returns {string} Formatted answer HTML
     */
  }, {
    key: "formatAnswerForDisplay",
    value: function formatAnswerForDisplay(answer, questionType, question) {
      // Use QuestionRenderer for consistent formatting
      return this.questionRenderer.formatAnswerForDisplay(answer, questionType, question);
    }

    /**
     * Handle review item click to navigate to specific question
     */
  }, {
    key: "handleReviewItemClick",
    value: function handleReviewItemClick(e) {
      e.preventDefault();
      if (!this.quiz.settings.allowNavigation) return;
      var $item = jQuery(e.currentTarget);
      var questionNum = parseInt($item.data('question'));

      // Calculate which page this question is on
      var targetPage = Math.ceil(questionNum / this.quiz.settings.questionsPerPage);

      // Go back to quiz and navigate to that page
      this.quiz.handleBackToQuiz(e);
      if (targetPage !== this.quiz.currentPage) {
        this.quiz.currentPage = targetPage;
        this.quiz.currentQuestion = questionNum - 1;
        this.renderCurrentQuestion();
        this.quiz.updateProgress();
        this.quiz.updateNavigationButtons();
      }
    }
  }]);
}(); // Export for use by quiz.js
window.SPLMSQuestions = SPLMSQuestions;

/***/ },

/***/ "./src/js/frontend/modules/quizzes/quiz-utils.js"
/*!*******************************************************!*\
  !*** ./src/js/frontend/modules/quizzes/quiz-utils.js ***!
  \*******************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   clearStorage: () => (/* binding */ clearStorage),
/* harmony export */   debounce: () => (/* binding */ debounce),
/* harmony export */   dispatchEvent: () => (/* binding */ dispatchEvent),
/* harmony export */   escapeHtml: () => (/* binding */ escapeHtml),
/* harmony export */   formatTime: () => (/* binding */ formatTime),
/* harmony export */   formatTimerTime: () => (/* binding */ formatTimerTime),
/* harmony export */   getStorageKey: () => (/* binding */ getStorageKey),
/* harmony export */   loadFromStorage: () => (/* binding */ loadFromStorage),
/* harmony export */   saveToStorage: () => (/* binding */ saveToStorage),
/* harmony export */   validateAnswer: () => (/* binding */ validateAnswer)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Quiz Utilities
 * Shared helper functions for quiz functionality
 * @module quiz-utils
 */

/**
 * Format seconds into human-readable time string
 * @param {number} seconds - Time in seconds
 * @returns {string} Formatted time string (e.g., "1h 30m 15s")
 */
function formatTime(seconds) {
  var hrs = Math.floor(seconds / 3600);
  var mins = Math.floor(seconds % 3600 / 60);
  var secs = seconds % 60;
  if (hrs > 0) {
    return "".concat(hrs, "h ").concat(mins, "m ").concat(secs, "s");
  } else if (mins > 0) {
    return "".concat(mins, "m ").concat(secs, "s");
  } else {
    return "".concat(secs, "s");
  }
}

/**
 * Format time for timer display (MM:SS)
 * @param {number} seconds - Time in seconds
 * @returns {string} Formatted time string (e.g., "05:30")
 */
function formatTimerTime(seconds) {
  var mins = Math.floor(seconds / 60);
  var secs = seconds % 60;
  return "".concat(mins.toString().padStart(2, '0'), ":").concat(secs.toString().padStart(2, '0'));
}

/**
 * Debounce function to limit how often a function can be called
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {Function} Debounced function
 */
function debounce(func, wait) {
  var timeout;
  return function executedFunction() {
    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }
    var later = function later() {
      clearTimeout(timeout);
      func.apply(void 0, args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

/**
 * Get namespaced storage key for quiz state
 * @param {number|string} quizId - Quiz ID
 * @returns {string} Storage key
 */
function getStorageKey(quizId) {
  return "splms_quiz_".concat(quizId);
}

/**
 * Save quiz state to localStorage
 * Uses SPLMSCore.helper.storage for consistent storage handling
 * @param {number|string} quizId - Quiz ID
 * @param {Object} state - State object to save
 * @returns {boolean} Success status
 */
function saveToStorage(quizId, state) {
  try {
    var key = getStorageKey(quizId);
    // Use global SPLMSCore.helper storage with quiz-specific timestamp
    if (window.SPLMSCore && window.SPLMSCore.helper && window.SPLMSCore.helper.storage) {
      window.SPLMSCore.helper.storage.set(key, _objectSpread(_objectSpread({}, state), {}, {
        savedAt: Date.now()
      }));
      return true;
    }
    // Fallback to direct localStorage if SPLMSCore not available
    localStorage.setItem(key, JSON.stringify(_objectSpread(_objectSpread({}, state), {}, {
      savedAt: Date.now()
    })));
    return true;
  } catch (e) {
    console.warn('Failed to save quiz state to localStorage:', e);
    return false;
  }
}

/**
 * Load quiz state from localStorage
 * Uses SPLMSCore.helper.storage for consistent storage handling
 * @param {number|string} quizId - Quiz ID
 * @returns {Object|null} Saved state or null
 */
function loadFromStorage(quizId) {
  try {
    var key = getStorageKey(quizId);
    var state = null;

    // Use global SPLMSCore.helper storage
    if (window.SPLMSCore && window.SPLMSCore.helper && window.SPLMSCore.helper.storage) {
      state = window.SPLMSCore.helper.storage.get(key, null);
    } else {
      // Fallback to direct localStorage if SPLMSCore not available
      var data = localStorage.getItem(key);
      state = data ? JSON.parse(data) : null;
    }
    if (!state) return null;

    // Check if state is expired (older than 7 days)
    var maxAge = 7 * 24 * 60 * 60 * 1000; // 7 days
    if (state.savedAt && Date.now() - state.savedAt > maxAge) {
      if (window.SPLMSCore && window.SPLMSCore.helper && window.SPLMSCore.helper.storage) {
        window.SPLMSCore.helper.storage.remove(key);
      } else {
        localStorage.removeItem(key);
      }
      return null;
    }
    return state;
  } catch (e) {
    console.warn('Failed to load quiz state from localStorage:', e);
    return null;
  }
}

/**
 * Clear quiz state from localStorage
 * Uses SPLMSCore.helper.storage for consistent storage handling
 * @param {number|string} quizId - Quiz ID
 */
function clearStorage(quizId) {
  try {
    var key = getStorageKey(quizId);
    // Use global SPLMSCore.helper storage
    if (window.SPLMSCore && window.SPLMSCore.helper && window.SPLMSCore.helper.storage) {
      window.SPLMSCore.helper.storage.remove(key);
    } else {
      // Fallback to direct localStorage if SPLMSCore not available
      localStorage.removeItem(key);
    }
  } catch (e) {
    console.warn('Failed to clear quiz state from localStorage:', e);
  }
}

/**
 * Escape HTML to prevent XSS
 * @param {string} text - Text to escape
 * @returns {string} Escaped HTML
 */
function escapeHtml(text) {
  if (!text) return '';
  var div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

/**
 * Dispatch custom event
 * @param {string} eventName - Event name
 * @param {Object} detail - Event detail data
 */
function dispatchEvent(eventName) {
  var detail = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  var event = new CustomEvent(eventName, {
    detail: detail,
    bubbles: true,
    cancelable: true
  });
  document.dispatchEvent(event);
}

/**
 * Validate answer based on question type
 * @param {*} answer - User answer
 * @param {string} questionType - Question type
 * @returns {boolean} Whether answer is valid
 */
function validateAnswer(answer, questionType) {
  if (!answer) return false;
  switch (questionType) {
    case 'multiple_select':
      return Array.isArray(answer) && answer.length > 0;
    case 'short_answer':
    case 'fill_blank':
    case 'essay':
    case 'long_answer':
      return typeof answer === 'string' && answer.trim().length > 0;
    case 'file_upload':
      return typeof answer === 'string' && answer.length > 0;
    case 'matching':
      return _typeof(answer) === 'object' && answer !== null;
    case 'ordering':
      return Array.isArray(answer) && answer.length > 0;
    default:
      return answer !== '' && answer !== null && answer !== undefined;
  }
}

/***/ },

/***/ "./src/js/frontend/modules/quizzes/quiz.js"
/*!*************************************************!*\
  !*** ./src/js/frontend/modules/quizzes/quiz.js ***!
  \*************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./quiz-utils.js */ "./src/js/frontend/modules/quizzes/quiz-utils.js");
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * SkillPulse LMS Quiz Handler (Refactored)
 * Handles quiz taking, timer management, and quiz completion
 * Works with SPLMSQuestions class for question-specific functionality
 * 
 * @class SPLMSQuiz
 * @module quizzes/quiz
 */

var SPLMSQuiz = /*#__PURE__*/function () {
  function SPLMSQuiz() {
    _classCallCheck(this, SPLMSQuiz);
    this.currentPage = 1;
    this.currentQuestion = 0;
    this.questions = [];
    this.questionsData = [];
    this.answers = {};
    this.fileObjects = {}; // Store File objects in memory (questionId => File object)
    this.timeRemaining = 0;
    this.timerInterval = null;
    this.isQuizActive = false;
    this.quizData = {};
    this.startTime = null;
    this.settings = {};
    this.attemptId = null;
    this.currentResults = null;
    this.autoSaveInterval = null;
    this.isSubmitting = false; // Prevent duplicate submissions

    // Initialize questions handler
    this.questionsHandler = new SPLMSQuestions(this);
    this.init();
  }
  return _createClass(SPLMSQuiz, [{
    key: "init",
    value: function init() {
      this.bindEvents();
      this.loadQuestionsData();
    }
  }, {
    key: "bindEvents",
    value: function bindEvents() {
      // Start quiz button
      jQuery(document).on('click', '.start-quiz-btn', this.handleStartQuiz.bind(this));

      // Resume and restart quiz buttons
      jQuery(document).on('click', '.resume-quiz-btn', this.handleResumeQuiz.bind(this));
      jQuery(document).on('click', '.restart-quiz-btn', this.handleRestartQuiz.bind(this));

      // Quiz navigation buttons  
      jQuery(document).on('click', '#prev-question-btn', this.handlePreviousPage.bind(this));
      jQuery(document).on('click', '#next-question-btn', this.handleNextPage.bind(this));
      jQuery(document).on('click', '#submit-quiz-btn', this.handleSubmitQuiz.bind(this));
      jQuery(document).on('click', '#final-submit-btn', this.handleFinalSubmit.bind(this));

      // Page navigation dots
      jQuery(document).on('click', '.page-dot', this.handlePageDotClick.bind(this));

      // Review functionality
      jQuery(document).on('click', '.review-item', this.questionsHandler.handleReviewItemClick.bind(this.questionsHandler));
      jQuery(document).on('click', '#back-to-quiz-btn', this.handleBackToQuiz.bind(this));

      // Results actions
      // Note: Retake quiz functionality is now handled in handleRetakeQuizFromPHPResults()
      jQuery(document).on('click', '.view-answers-btn', this.handleViewAnswers.bind(this));
      jQuery(document).on('click', '.next-btn', this.handleNext.bind(this));
      jQuery(document).on('click', '.view-course-btn', this.handleViewCourse.bind(this));

      // Keyboard navigation
      jQuery(document).on('keydown', this.handleKeyboardEvents.bind(this));

      // Prevent accidental page leave during quiz
      jQuery(window).on('beforeunload', this.handlePageLeave.bind(this));
    }
  }, {
    key: "loadQuestionsData",
    value: function loadQuestionsData() {
      // Load questions data from the script tag
      var questionsScript = document.getElementById('quiz-questions-data');
      if (questionsScript && questionsScript.textContent) {
        try {
          this.questionsData = JSON.parse(questionsScript.textContent);
        } catch (e) {
          console.error('Failed to parse quiz questions data:', e);
          this.questionsData = [];
        }
      }
    }
  }, {
    key: "handleStartQuiz",
    value: function handleStartQuiz(e) {
      var _this = this;
      e.preventDefault();
      var $button = jQuery(e.currentTarget);
      var quizId = $button.data('quiz-id');
      var courseId = $button.data('course-id');
      // HTML data attributes are strings, so check for string 'true' or boolean true.
      var previewModeValue = $button.data('preview-mode');
      var isPreviewMode = previewModeValue === true || previewModeValue === 'true';
      if (!quizId) {
        console.error('Quiz ID not found on button');
        SPLMSCore.helper.showNotification('Quiz ID not found', 'error');
        return;
      }

      // Add loading state
      $button.addClass('loading').prop('disabled', true);
      var originalText = $button.text();
      $button.text(isPreviewMode ? '🚀 Starting Preview...' : '🚀 Starting Quiz...');

      // Load quiz data via AJAX
      this.loadQuizData(quizId, courseId, isPreviewMode).then(function () {
        _this.startQuiz();
        $button.removeClass('loading').prop('disabled', false).text(originalText);
      })["catch"](function (error) {
        console.error('Failed to load quiz data:', error);
        // Check if error is due to missing question type handler
        var errorMessage = 'Failed to load quiz. Please try again.';
        if (error && error.message && error.message.includes('question type')) {
          errorMessage = 'Unsupported question type detected. Please contact support.';
        } else if (error && error.responseJSON && error.responseJSON.data) {
          errorMessage = error.responseJSON.data;
        }
        SPLMSCore.helper.showNotification(errorMessage, 'error');
        $button.removeClass('loading').prop('disabled', false).text(originalText);
      });
    }
  }, {
    key: "handleResumeQuiz",
    value: function handleResumeQuiz(e) {
      var _this2 = this;
      e.preventDefault();
      var $button = jQuery(e.currentTarget);
      var quizId = $button.data('quiz-id');
      var courseId = $button.data('course-id');
      if (!quizId) {
        SPLMSCore.helper.showNotification('Quiz ID not found', 'error');
        return;
      }

      // Add loading state
      $button.addClass('loading').prop('disabled', true);
      var originalText = $button.text();
      $button.text('▶️ Resuming Quiz...');

      // Load quiz data and resume from saved state
      Promise.all([this.loadQuizData(quizId, courseId), this.loadSavedQuizState(quizId)]).then(function (_ref) {
        var _ref2 = _slicedToArray(_ref, 2),
          quizData = _ref2[0],
          savedState = _ref2[1];
        _this2.resumeQuiz(savedState);
        $button.removeClass('loading').prop('disabled', false).text(originalText);
      })["catch"](function (error) {
        console.error('Failed to resume quiz:', error);
        SPLMSCore.helper.showNotification('Failed to resume quiz. Please try again.', 'error');
        $button.removeClass('loading').prop('disabled', false).text(originalText);
      });
    }
  }, {
    key: "handleRestartQuiz",
    value: function handleRestartQuiz(e) {
      var _this3 = this;
      e.preventDefault();
      var $button = jQuery(e.currentTarget);
      var quizId = $button.data('quiz-id');
      var courseId = $button.data('course-id');
      if (!quizId) {
        SPLMSCore.helper.showNotification('Quiz ID not found', 'error');
        return;
      }

      // Confirm restart
      if (!confirm('Are you sure you want to start a new attempt? Your current progress will be lost.')) {
        return;
      }

      // Add loading state
      $button.addClass('loading').prop('disabled', true);
      var originalText = $button.text();
      $button.text('🔄 Starting New Attempt...');

      // Clear saved state and start fresh
      this.clearSavedQuizState(quizId).then(function () {
        return _this3.loadQuizData(quizId, courseId);
      }).then(function () {
        _this3.startQuiz();
        $button.removeClass('loading').prop('disabled', false).text(originalText);
      })["catch"](function (error) {
        console.error('Failed to restart quiz:', error);
        SPLMSCore.helper.showNotification('Failed to start new attempt. Please try again.', 'error');
        $button.removeClass('loading').prop('disabled', false).text(originalText);
      });
    }
  }, {
    key: "loadQuizData",
    value: function () {
      var _loadQuizData = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(quizId, courseId) {
        var isPreviewMode,
          _frontend$nonces,
          _frontend$nonces2,
          frontend,
          response,
          quizData,
          settings,
          _args = arguments,
          _t;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.p = _context.n) {
            case 0:
              isPreviewMode = _args.length > 2 && _args[2] !== undefined ? _args[2] : false;
              _context.p = 1;
              if (quizId) {
                _context.n = 2;
                break;
              }
              throw new Error('Quiz ID is required');
            case 2:
              // Get frontend data
              frontend = window.splms_frontend;
              if (!(!frontend || !frontend.ajax_url || !((_frontend$nonces = frontend.nonces) !== null && _frontend$nonces !== void 0 && _frontend$nonces.splms_nonce))) {
                _context.n = 3;
                break;
              }
              throw new Error('SkillPulse LMS frontend object not found. Please refresh the page.');
            case 3:
              _context.n = 4;
              return jQuery.ajax({
                url: frontend.ajax_url,
                type: 'POST',
                data: {
                  action: 'splms_start_quiz',
                  quiz_id: quizId,
                  course_id: courseId,
                  preview_mode: isPreviewMode ? 'true' : 'false',
                  nonce: ((_frontend$nonces2 = frontend.nonces) === null || _frontend$nonces2 === void 0 ? void 0 : _frontend$nonces2.splms_nonce) || frontend.nonce || ''
                }
              });
            case 4:
              response = _context.v;
              if (response.success) {
                _context.n = 5;
                break;
              }
              throw new Error(response.data || 'Failed to load quiz data');
            case 5:
              quizData = response.data; // Validate response structure
              if (!(!quizData || !Array.isArray(quizData.questions))) {
                _context.n = 6;
                break;
              }
              throw new Error('Invalid quiz data structure received');
            case 6:
              // Store loaded data
              this.questionsData = quizData.questions;

              // Get quiz settings
              settings = quizData.quiz_settings || {};
              this.settings = {
                quizId: quizId,
                courseId: courseId,
                timeLimit: settings.time_limit || 0,
                timeLimitEnabled: settings.time_limit_enabled || false,
                totalQuestions: quizData.questions.length,
                questionsPerPage: settings.question_per_page || 1,
                totalPages: Math.ceil(quizData.questions.length / (settings.question_per_page || 1)),
                allowNavigation: settings.allow_navigation !== false,
                allowReview: settings.allow_review !== false,
                passingGrade: settings.passing_grade || 70,
                isPreviewMode: quizData.is_preview_mode || false
              };
              this.attemptId = quizData.attempt_id;

              // Populate quiz interface with questions
              this.populateQuizInterface();
              _context.n = 8;
              break;
            case 7:
              _context.p = 7;
              _t = _context.v;
              console.error('Error loading quiz data:', _t);
              throw _t;
            case 8:
              return _context.a(2);
          }
        }, _callee, this, [[1, 7]]);
      }));
      function loadQuizData(_x, _x2) {
        return _loadQuizData.apply(this, arguments);
      }
      return loadQuizData;
    }()
  }, {
    key: "populateQuizInterface",
    value: function populateQuizInterface() {
      if (!this.questionsData || this.questionsData.length === 0) {
        var $interface = jQuery('#splms-quiz-interface');
        $interface.find('.splms-quiz-questions-container').html('<div class="no-questions-notice">' + '<h3>No Questions Available</h3>' + '<p>This quiz does not have any questions yet.</p>' + '</div>');
        return;
      }

      // Initialize template functions
      this.navigationTemplate = wp.template('quiz-navigation');
      this.timerTemplate = wp.template('quiz-timer');

      // Render the current question using questions handler
      this.questionsHandler.renderCurrentQuestion();

      // Render navigation
      this.renderNavigation();

      // Render timer if enabled
      if (this.settings.timeLimitEnabled && this.timeRemaining > 0) {
        this.renderTimer();
      }
    }
  }, {
    key: "renderNavigation",
    value: function renderNavigation() {
      var questionsPerPage = this.settings.questionsPerPage || 1;
      var totalPages = Math.ceil(this.questionsData.length / questionsPerPage);
      var currentPage = this.currentPage;
      var progressPercent = Math.round(currentPage / totalPages * 100);
      var navigationData = {
        currentPage: currentPage,
        totalPages: totalPages,
        progressPercent: progressPercent
      };

      // Render navigation using template
      if (this.navigationTemplate) {
        var navigationHtml = this.navigationTemplate(navigationData);

        // Insert into container
        var $container = jQuery('#splms-quiz-interface .splms-quiz-navigation-container');
        if ($container.length === 0) {
          jQuery('#splms-quiz-interface').append('<div class="splms-quiz-navigation-container"></div>');
        }
        jQuery('#splms-quiz-interface .splms-quiz-navigation-container').html(navigationHtml);
      }

      // Bind navigation events
      this.bindNavigationEvents();
    }

    /**
     * Render timer display
     * Uses utility function for consistent time formatting
     */
  }, {
    key: "renderTimer",
    value: function renderTimer() {
      if (!this.settings.timeLimitEnabled || this.timeRemaining <= 0) {
        return;
      }

      // Use utility function for consistent formatting
      var timeDisplay = (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.formatTimerTime)(this.timeRemaining);
      var timerData = {
        timeDisplay: timeDisplay
      };

      // Render timer using template
      if (this.timerTemplate) {
        var timerHtml = this.timerTemplate(timerData);

        // Insert into container
        var $container = jQuery('#splms-quiz-interface .splms-quiz-timer-container');
        if ($container.length === 0) {
          jQuery('#splms-quiz-interface').prepend('<div class="splms-quiz-timer-container"></div>');
        }
        jQuery('#splms-quiz-interface .splms-quiz-timer-container').html(timerHtml);
      }
    }
  }, {
    key: "bindNavigationEvents",
    value: function bindNavigationEvents() {
      // Navigation events are already bound in bindEvents() using document delegation
      // We don't need to bind them again here to avoid double-firing
    }

    /**
     * Navigate to previous question
     * Saves current answer and dispatches question change event
     */
    /**
     * Navigate to previous question
     * Saves current state before navigation
     */
  }, {
    key: "previousQuestion",
    value: function previousQuestion() {
      // Save current state before navigating
      this.saveCurrentAnswer();
      if (this.currentQuestion > 0) {
        var _this$questionsData$t;
        this.saveCurrentAnswer();
        this.currentQuestion--;
        this.updateCurrentPage();
        this.questionsHandler.renderCurrentQuestion();
        this.renderNavigation();

        // Dispatch custom event
        (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.dispatchEvent)('splms:quiz:questionChanged', {
          quizId: this.settings.quizId,
          questionIndex: this.currentQuestion,
          questionId: (_this$questionsData$t = this.questionsData[this.currentQuestion]) === null || _this$questionsData$t === void 0 ? void 0 : _this$questionsData$t.id,
          direction: 'previous'
        });
      }
    }

    /**
     * Navigate to next question
     * Saves current answer and dispatches question change event
     */
  }, {
    key: "nextQuestion",
    value: function nextQuestion() {
      if (this.currentQuestion < this.questionsData.length - 1) {
        var _this$questionsData$t2;
        this.saveCurrentAnswer();
        this.currentQuestion++;
        this.updateCurrentPage();
        this.questionsHandler.renderCurrentQuestion();
        this.renderNavigation();

        // Dispatch custom event
        (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.dispatchEvent)('splms:quiz:questionChanged', {
          quizId: this.settings.quizId,
          questionIndex: this.currentQuestion,
          questionId: (_this$questionsData$t2 = this.questionsData[this.currentQuestion]) === null || _this$questionsData$t2 === void 0 ? void 0 : _this$questionsData$t2.id,
          direction: 'next'
        });
      }
    }

    /**
     * Save current question answer before navigation
     * @private
     */
  }, {
    key: "saveCurrentAnswer",
    value: function saveCurrentAnswer() {
      // Answers are saved automatically via event handlers
      // Trigger debounced save to server
      if (this.debouncedServerSave) {
        this.debouncedServerSave();
      }
      // Also save to localStorage immediately
      var timeTaken = this.startTime ? Math.floor((Date.now() - this.startTime) / 1000) : 0;
      var state = {
        answers: this.answers,
        currentQuestion: this.currentQuestion,
        timeRemaining: this.timeRemaining,
        startTime: this.startTime,
        timeTaken: timeTaken,
        attemptId: this.attemptId
      };
      (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.saveToStorage)(this.settings.quizId, state);
    }
  }, {
    key: "loadSavedQuizState",
    value: function () {
      var _loadSavedQuizState = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(quizId) {
        var frontend, response, _t2;
        return _regenerator().w(function (_context2) {
          while (1) switch (_context2.p = _context2.n) {
            case 0:
              frontend = window.splms_frontend;
              _context2.p = 1;
              _context2.n = 2;
              return jQuery.ajax({
                url: frontend.ajax_url,
                type: 'POST',
                data: {
                  action: 'splms_get_quiz_state',
                  quiz_id: quizId,
                  nonce: frontend.nonces.splms_nonce
                }
              });
            case 2:
              response = _context2.v;
              if (!response.success) {
                _context2.n = 3;
                break;
              }
              return _context2.a(2, response.data);
            case 3:
              throw new Error(response.data || 'Failed to load saved quiz state');
            case 4:
              _context2.n = 6;
              break;
            case 5:
              _context2.p = 5;
              _t2 = _context2.v;
              console.error('Error loading saved quiz state:', _t2);
              throw _t2;
            case 6:
              return _context2.a(2);
          }
        }, _callee2, null, [[1, 5]]);
      }));
      function loadSavedQuizState(_x3) {
        return _loadSavedQuizState.apply(this, arguments);
      }
      return loadSavedQuizState;
    }()
    /**
     * Clear saved quiz state from both server and localStorage
     * @param {number|string} quizId - Quiz ID
     * @returns {Promise} Promise that resolves when state is cleared
     */
  }, {
    key: "clearSavedQuizState",
    value: (function () {
      var _clearSavedQuizState = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee3(quizId) {
        var frontend, response, _t3;
        return _regenerator().w(function (_context3) {
          while (1) switch (_context3.p = _context3.n) {
            case 0:
              // Clear localStorage
              (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.clearStorage)(quizId);
              frontend = window.splms_frontend;
              _context3.p = 1;
              _context3.n = 2;
              return jQuery.ajax({
                url: frontend.ajax_url,
                type: 'POST',
                data: {
                  action: 'splms_clear_quiz_state',
                  quiz_id: quizId,
                  nonce: frontend.nonces.splms_nonce
                }
              });
            case 2:
              response = _context3.v;
              if (response.success) {
                _context3.n = 3;
                break;
              }
              throw new Error(response.data || 'Failed to clear saved quiz state');
            case 3:
              _context3.n = 5;
              break;
            case 4:
              _context3.p = 4;
              _t3 = _context3.v;
              console.error('Error clearing saved quiz state:', _t3);
              throw _t3;
            case 5:
              return _context3.a(2);
          }
        }, _callee3, null, [[1, 4]]);
      }));
      function clearSavedQuizState(_x4) {
        return _clearSavedQuizState.apply(this, arguments);
      }
      return clearSavedQuizState;
    }()
    /**
     * Resume quiz from saved state
     * Loads state from server and localStorage, restores UI
     * @param {Object} savedState - Saved quiz state from server
     */
    )
  }, {
    key: "resumeQuiz",
    value: function resumeQuiz(savedState) {
      var _ref3,
        _localState$currentQu,
        _localState$startTime,
        _this4 = this;
      // Try to load from localStorage first (faster, more recent)
      var localState = (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.loadFromStorage)(this.settings.quizId);

      // Merge server state with localStorage state (localStorage takes precedence for answers)
      var answers = (localState === null || localState === void 0 ? void 0 : localState.answers) || savedState.answers || {};
      if (typeof answers === 'string') {
        try {
          answers = JSON.parse(answers);
        } catch (e) {
          console.error('Failed to parse saved answers:', e);
          answers = {};
        }
      }

      // Initialize quiz state from saved data
      this.answers = answers;
      this.fileObjects = {}; // Reset fileObjects on resume (files need to be re-selected after page refresh)
      this.attemptId = savedState.attempt_id || (localState === null || localState === void 0 ? void 0 : localState.attemptId);
      this.currentQuestion = (_ref3 = (_localState$currentQu = localState === null || localState === void 0 ? void 0 : localState.currentQuestion) !== null && _localState$currentQu !== void 0 ? _localState$currentQu : savedState.currentQuestion) !== null && _ref3 !== void 0 ? _ref3 : 0;
      this.updateCurrentPage();
      this.isQuizActive = true;
      this.startTime = (_localState$startTime = localState === null || localState === void 0 ? void 0 : localState.startTime) !== null && _localState$startTime !== void 0 ? _localState$startTime : savedState.start_time ? new Date(savedState.start_time).getTime() : Date.now();

      // Set up timer if applicable
      if (this.settings.timeLimitEnabled && this.settings.timeLimit > 0) {
        var _ref4, _localState$timeTaken, _localState$timeRemai;
        var elapsed = (_ref4 = (_localState$timeTaken = localState === null || localState === void 0 ? void 0 : localState.timeTaken) !== null && _localState$timeTaken !== void 0 ? _localState$timeTaken : savedState.time_taken) !== null && _ref4 !== void 0 ? _ref4 : 0;
        this.timeRemaining = (_localState$timeRemai = localState === null || localState === void 0 ? void 0 : localState.timeRemaining) !== null && _localState$timeRemai !== void 0 ? _localState$timeRemai : Math.max(0, this.settings.timeLimit * 60 - elapsed);
        if (this.timeRemaining > 0) {
          this.startTimer();
        } else {
          // Time's up - auto submit
          SPLMSCore.helper.showNotification('Time is up! Quiz will be auto-submitted.', 'warning');
          setTimeout(function () {
            _this4.handleFinalSubmit();
          }, 1000);
          return;
        }
      }

      // Hide specific quiz content sections but keep the interface visible
      jQuery('.splms-quiz-stats').hide();
      jQuery('.splms-quiz-description').hide();
      jQuery('.splms-quiz-actions').hide();

      // Show quiz interface
      jQuery('#splms-quiz-interface').show();

      // Render the current question first (so DOM elements exist)
      this.questionsHandler.renderCurrentQuestion();

      // Then restore answers in the UI (after rendering)
      // Use setTimeout to ensure DOM is ready
      setTimeout(function () {
        _this4.questionsHandler.restoreAnswersInUI();
      }, 100);

      // Render navigation
      this.renderNavigation();

      // Start auto-saving
      this.startAutoSave();

      // Dispatch custom event
      (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.dispatchEvent)('splms:quiz:resumed', {
        quizId: this.settings.quizId,
        attemptId: this.attemptId,
        currentQuestion: this.currentQuestion,
        timeRemaining: this.timeRemaining
      });
      SPLMSCore.helper.showNotification('Quiz resumed successfully!', 'success');
    }

    /**
     * Save quiz state to both localStorage and server
     * Uses debounced save for localStorage to reduce writes
     */
  }, {
    key: "saveQuizState",
    value: function saveQuizState() {
      // Skip saving in preview mode
      if (this.settings.isPreviewMode) {
        return;
      }

      // Save current quiz state to database
      if (!this.isQuizActive || !this.settings.quizId || !this.attemptId) {
        return;
      }
      var timeTaken = this.startTime ? Math.floor((Date.now() - this.startTime) / 1000) : 0;

      // Save to localStorage for fast resume
      var state = {
        answers: this.answers,
        currentQuestion: this.currentQuestion,
        timeRemaining: this.timeRemaining,
        startTime: this.startTime,
        timeTaken: timeTaken,
        attemptId: this.attemptId
      };
      (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.saveToStorage)(this.settings.quizId, state);

      // Save to server (debounced if method exists, otherwise direct save)
      if (this.debouncedServerSave) {
        this.debouncedServerSave();
      } else {
        // Direct save to server
        var frontend = window.splms_frontend;
        jQuery.ajax({
          url: frontend.ajax_url,
          type: 'POST',
          data: {
            action: 'splms_save_quiz_state',
            quiz_id: this.settings.quizId,
            attempt_id: this.attemptId,
            answers: this.answers,
            time_taken: timeTaken,
            nonce: frontend.nonces.splms_nonce
          },
          success: function success(response) {
            // Silent save - don't show notifications
          },
          error: function error(xhr, status, _error) {
            console.error('Failed to save quiz state:', _error);
          }
        });
      }
    }

    /**
     * Start the quiz
     * Initializes quiz state, starts timer, and dispatches start event
     */
  }, {
    key: "startQuiz",
    value: function startQuiz() {
      // Initialize quiz state
      this.answers = {};
      this.currentQuestion = 0;
      this.updateCurrentPage();
      this.isQuizActive = true;
      this.startTime = Date.now();

      // Set up timer if applicable
      if (this.settings.timeLimitEnabled && this.settings.timeLimit > 0) {
        this.timeRemaining = this.settings.timeLimit * 60; // Convert minutes to seconds
        this.startTimer();
      }

      // Hide specific quiz content sections but keep the interface visible
      jQuery('.splms-quiz-stats').hide();
      jQuery('.splms-quiz-description').hide();
      jQuery('.splms-quiz-actions').hide();

      // Show quiz interface
      jQuery('#splms-quiz-interface').show();

      // Render the first question
      this.questionsHandler.renderCurrentQuestion();

      // Render navigation
      this.renderNavigation();

      // Start auto-saving quiz state
      this.startAutoSave();

      // Dispatch custom event
      (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.dispatchEvent)('splms:quiz:started', {
        quizId: this.settings.quizId,
        attemptId: this.attemptId,
        courseId: this.settings.courseId
      });
    }

    /**
     * Start auto-saving quiz state
     * Uses debounced save to reduce server calls
     */
  }, {
    key: "startAutoSave",
    value: function startAutoSave() {
      var _this5 = this;
      // Clear existing interval
      if (this.autoSaveInterval) {
        clearInterval(this.autoSaveInterval);
      }

      // Create debounced server save function
      var frontend = window.splms_frontend;
      this.debouncedServerSave = (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.debounce)(function () {
        if (!_this5.settings.isPreviewMode && _this5.isQuizActive && _this5.settings.quizId && _this5.attemptId) {
          var timeTaken = _this5.startTime ? Math.floor((Date.now() - _this5.startTime) / 1000) : 0;
          jQuery.ajax({
            url: frontend.ajax_url,
            type: 'POST',
            data: {
              action: 'splms_save_quiz_state',
              quiz_id: _this5.settings.quizId,
              attempt_id: _this5.attemptId,
              answers: _this5.answers,
              time_taken: timeTaken,
              nonce: frontend.nonces.splms_nonce
            },
            success: function success(response) {
              // Silent save - don't show notifications
            },
            error: function error(xhr, status, _error2) {
              console.error('Failed to save quiz state:', _error2);
            }
          });
        }
      }, 2000); // Debounce to 2 seconds

      // Auto-save quiz state every 30 seconds (server save)
      this.autoSaveInterval = setInterval(function () {
        _this5.saveQuizState();
      }, 30000); // 30 seconds
    }
  }, {
    key: "startTimer",
    value: function startTimer() {
      var _this6 = this;
      this.timerInterval = setInterval(function () {
        _this6.timeRemaining--;
        _this6.renderTimer();

        // Check if time is running out
        if (_this6.timeRemaining <= 60) {
          // Last minute
          jQuery('.quiz-timer').addClass('danger');
        }

        // Auto-submit when time runs out
        if (_this6.timeRemaining <= 0) {
          SPLMSCore.helper.showNotification('Time is up! Quiz will be auto-submitted.', 'warning');
          setTimeout(function () {
            _this6.handleFinalSubmit();
          }, 1000);
        }
      }, 1000);
    }
  }, {
    key: "handlePreviousPage",
    value: function handlePreviousPage(e) {
      e.preventDefault();
      this.previousQuestion();
    }
  }, {
    key: "handleNextPage",
    value: function handleNextPage(e) {
      e.preventDefault();
      this.nextQuestion();
    }
  }, {
    key: "handlePageDotClick",
    value: function handlePageDotClick(e) {
      e.preventDefault();
      // Page dots navigation implementation
    }
  }, {
    key: "handleSubmitQuiz",
    value: function handleSubmitQuiz(e) {
      e.preventDefault();
      this.handleFinalSubmit(e);
    }
  }, {
    key: "handleFinalSubmit",
    value: function handleFinalSubmit(e) {
      var _this7 = this;
      if (e) e.preventDefault();

      // Prevent duplicate submissions
      if (this.isSubmitting) {
        console.warn('Quiz submission already in progress. Ignoring duplicate submit.');
        return;
      }
      this.isSubmitting = true;
      var $submitBtn = jQuery('#submit-quiz-btn, #final-submit-btn');

      // Add loading state
      $submitBtn.addClass('loading').prop('disabled', true);
      var originalText = $submitBtn.text();
      $submitBtn.text(this.settings.isPreviewMode ? '📤 Calculating Results...' : '📤 Submitting...');

      // Calculate time taken
      var timeTaken = this.startTime ? Math.floor((Date.now() - this.startTime) / 1000) : 0;

      // Handle preview mode differently
      if (this.settings.isPreviewMode) {
        this.handlePreviewSubmission(timeTaken, $submitBtn, originalText);
        return;
      }

      // Prepare FormData for file uploads
      var formData = new FormData();
      var fileUploads = {};

      // Simple approach: Collect files from fileObjects (stored in memory when files were selected)
      // This ensures we get all files regardless of which page they're on
      Object.keys(this.fileObjects).forEach(function (questionId) {
        var file = _this7.fileObjects[questionId];
        if (file && file instanceof File) {
          formData.append('file_' + questionId, file);
          fileUploads[questionId] = file.name;
        }
      });

      // Also check current file inputs (in case fileObjects is missing some - fallback)
      // This handles edge cases where file might have been selected but not stored in fileObjects
      var self = this;
      jQuery('#splms-quiz-interface input[type="file"]').each(function () {
        var questionId = jQuery(this).data('question-id');
        var file = this.files && this.files.length > 0 ? this.files[0] : null;
        if (file && questionId) {
          var qId = String(questionId);
          // Only add if not already in fileUploads (fileObjects takes precedence)
          if (!fileUploads[qId]) {
            formData.append('file_' + qId, file);
            fileUploads[qId] = file.name;
            // Also store in fileObjects for consistency
            self.fileObjects[qId] = file;
          }
        }
      });

      // Clean and validate answers before submission
      // Ensure answers are in the correct format expected by the evaluator
      var cleanedAnswers = this.cleanAnswersForSubmission(this.answers);

      // Validate that we have answers for all questions
      if (this.questionsData && this.questionsData.length > 0) {
        var unansweredQuestions = [];
        this.questionsData.forEach(function (q) {
          if (!cleanedAnswers[q.id] || cleanedAnswers[q.id] === '' || Array.isArray(cleanedAnswers[q.id]) && cleanedAnswers[q.id].length === 0) {
            unansweredQuestions.push({
              id: q.id,
              type: q.type
            });
          }
        });
        if (unansweredQuestions.length > 0) {
          console.warn('Unanswered questions:', unansweredQuestions);
        }
      }

      // Add other data to FormData
      var frontend = window.splms_frontend;
      formData.append('action', 'splms_submit_quiz_final');
      formData.append('quiz_id', this.settings.quizId);
      formData.append('answers', JSON.stringify(cleanedAnswers));
      formData.append('file_uploads', JSON.stringify(fileUploads));
      formData.append('time_taken', timeTaken);
      formData.append('nonce', frontend.nonces.splms_nonce);
      formData.append('attempt_id', this.attemptId);

      // Submit quiz via AJAX
      jQuery.ajax({
        url: frontend.ajax_url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function success(response) {
          if (response.success && response.data) {
            // Verify attempt_id is in response (confirms database save)
            if (response.data.attempt_id) {
              // Mark submission as complete
              _this7.isSubmitting = false;

              // IMPORTANT: Do NOT call clearSavedQuizState() after successful submission
              // This would trigger splms_clear_quiz_state which could delete the attempt
              // Only clear browser localStorage (client-side only)
              (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.clearStorage)(_this7.settings.quizId);

              // Stop auto-saving
              if (_this7.autoSaveInterval) {
                clearInterval(_this7.autoSaveInterval);
                _this7.autoSaveInterval = null;
              }

              // Dispatch custom event
              (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.dispatchEvent)('splms:quiz:submitted', {
                quizId: _this7.settings.quizId,
                attemptId: response.data.attempt_id || _this7.attemptId,
                results: response.data
              });

              // Show results (this will load attempt history after a delay to ensure DB commit)
              _this7.showQuizResults(response.data);

              // If quiz is passed, mark it as complete in the curriculum
              if (response.data.passed) {
                _this7.markQuizAsComplete(response.data.progress);
              }
            } else {
              _this7.isSubmitting = false; // Reset on failure
              console.warn('Quiz submission response missing attempt_id. Attempt may not have been saved.');
              // Don't clear localStorage if we can't verify the save
              SPLMSCore.helper.showNotification('Warning: Could not verify quiz submission. Please check your attempt history.', 'warning');
            }
          } else {
            // Handle specific error types
            if (response.data && _typeof(response.data) === 'object' && response.data.time_limit_exceeded) {
              SPLMSCore.helper.showNotification('⏰ Time limit exceeded! Your quiz submission has been rejected. Please try again if attempts remain.', 'error');
              // Force clear the timer
              if (_this7.timerInterval) {
                clearInterval(_this7.timerInterval);
                _this7.timerInterval = null;
              }
              // Show quiz results with failed status due to time limit
              _this7.showQuizResults({
                passed: false,
                percentage: 0,
                correct_answers: 0,
                total_questions: _this7.questionsData.length,
                time_taken: response.data.time_taken || 0,
                passing_grade: _this7.settings.passingGrade,
                attempts_remaining: response.data.attempts_remaining || 0,
                show_answers: false,
                time_limit_exceeded: true
              });
            } else {
              // Extract error message properly (handle both string and object responses)
              var errorMessage = 'Failed to submit quiz';
              if (response.data) {
                if (typeof response.data === 'string') {
                  errorMessage = response.data;
                } else if (_typeof(response.data) === 'object' && response.data.message) {
                  errorMessage = response.data.message;
                } else if (_typeof(response.data) === 'object') {
                  // Try to stringify if it's an object without message property
                  errorMessage = JSON.stringify(response.data);
                }
              }
              SPLMSCore.helper.showNotification(errorMessage, 'error');
            }
          }
        },
        error: function error(xhr) {
          _this7.isSubmitting = false; // Reset on error
          var errorMessage = 'Something went wrong. Please try again.';
          if (xhr.responseJSON && xhr.responseJSON.data) {
            var errorData = xhr.responseJSON.data;
            if (typeof errorData === 'string') {
              errorMessage = errorData;
            } else if (_typeof(errorData) === 'object' && errorData.message) {
              errorMessage = errorData.message;
            } else if (_typeof(errorData) === 'object') {
              errorMessage = JSON.stringify(errorData);
            }
          }
          SPLMSCore.helper.showNotification(errorMessage, 'error');
        },
        complete: function complete() {
          // Only reset submitting flag if not successful (success is handled above)
          if (!_this7.isSubmitting) {
            // Already handled in success
          } else {
            _this7.isSubmitting = false;
          }
          $submitBtn.removeClass('loading').prop('disabled', false).text(originalText);
        }
      });
    }
  }, {
    key: "handlePreviewSubmission",
    value: function handlePreviewSubmission(timeTaken, $submitBtn, originalText) {
      var _this8 = this;
      // Calculate score locally for preview mode
      var correctAnswers = 0;
      var totalQuestions = this.questionsData.length;
      this.questionsData.forEach(function (question, index) {
        var userAnswer = _this8.answers[index];
        if (userAnswer && userAnswer === question.correct_answer) {
          correctAnswers++;
        }
      });
      var score = totalQuestions > 0 ? Math.round(correctAnswers / totalQuestions * 100) : 0;
      var passed = score >= this.settings.passingGrade;

      // Option 1: Store as guest attempt in database
      this.submitGuestAttempt(score, correctAnswers, totalQuestions, passed, timeTaken, $submitBtn, originalText);
    }
  }, {
    key: "submitGuestAttempt",
    value: function submitGuestAttempt(score, correctAnswers, totalQuestions, passed, timeTaken, $submitBtn, originalText) {
      var _this9 = this;
      // Submit guest attempt to server for server-side evaluation
      // Server will evaluate answers and respect Guest Quiz Storage Mode setting

      // Clean and format answers for submission (same as logged-in users)
      var cleanedAnswers = this.cleanAnswersForSubmission(this.answers);
      var frontend = window.splms_frontend;
      jQuery.ajax({
        url: frontend.ajax_url,
        type: 'POST',
        data: {
          action: 'splms_submit_guest_quiz_attempt',
          quiz_id: this.settings.quizId,
          course_id: this.settings.courseId,
          answers: JSON.stringify(cleanedAnswers),
          // Send as JSON string
          time_taken: timeTaken,
          nonce: frontend.nonces.splms_nonce
        },
        success: function success(response) {
          // Stop auto-saving
          if (_this9.autoSaveInterval) {
            clearInterval(_this9.autoSaveInterval);
            _this9.autoSaveInterval = null;
          }
          if (response.success && response.data) {
            // Use server-evaluated results
            var previewResults = {
              score: response.data.points_earned || response.data.score || 0,
              percentage: response.data.percentage || 0,
              total_questions: response.data.total_questions || 0,
              correct_answers: response.data.correct_answers || 0,
              passed: response.data.passed || false,
              time_taken: response.data.time_taken || timeTaken,
              is_preview_mode: true,
              is_guest_attempt: true,
              attempt_id: response.data.attempt_id || null,
              passing_grade: response.data.passing_grade || _this9.settings.passingGrade || 70,
              show_answers: response.data.show_answers !== false,
              detailed_results: response.data.detailed_results || [],
              pending_review: response.data.pending_review || false,
              has_manual_review: response.data.has_manual_review || false,
              message: response.data.message || 'This was a preview attempt. Results have been saved as guest attempt.'
            };

            // Show results
            _this9.showQuizResults(previewResults);
          } else {
            // Fallback to client-side results if server response is invalid
            var _previewResults = {
              score: score,
              percentage: score,
              total_questions: totalQuestions,
              correct_answers: correctAnswers,
              passed: passed,
              time_taken: timeTaken,
              is_preview_mode: true,
              message: 'This was a preview attempt. Results are not saved.'
            };
            _this9.showQuizResults(_previewResults);
          }

          // Reset button
          $submitBtn.removeClass('loading').prop('disabled', false).text(originalText);
        },
        error: function error(xhr, status, _error3) {
          console.warn('Failed to save guest attempt, showing results anyway:', _error3);

          // Stop auto-saving
          if (_this9.autoSaveInterval) {
            clearInterval(_this9.autoSaveInterval);
            _this9.autoSaveInterval = null;
          }

          // Create preview results object (without database storage)
          var previewResults = {
            score: score,
            percentage: score,
            total_questions: totalQuestions,
            correct_answers: correctAnswers,
            passed: passed,
            time_taken: timeTaken,
            is_preview_mode: true,
            message: 'This was a preview attempt. Results are not saved.'
          };

          // Show results
          _this9.showQuizResults(previewResults);

          // Reset button
          $submitBtn.removeClass('loading').prop('disabled', false).text(originalText);
        }
      });
    }

    // Storage mode is now handled server-side in handle_submit_guest_quiz_attempt
  }, {
    key: "showPreviewResultsOnly",
    value: function showPreviewResultsOnly(score, correctAnswers, totalQuestions, passed, timeTaken, $submitBtn, originalText) {
      // Stop auto-saving
      if (this.autoSaveInterval) {
        clearInterval(this.autoSaveInterval);
        this.autoSaveInterval = null;
      }

      // Create preview results object (no database storage)
      var previewResults = {
        score: score,
        total_questions: totalQuestions,
        correct_answers: correctAnswers,
        passed: passed,
        time_taken: timeTaken,
        is_preview_mode: true,
        message: 'This was a preview attempt. Results are not saved.',
        is_guest_attempt: false
      };

      // Show results
      this.showQuizResults(previewResults);

      // Reset button
      $submitBtn.removeClass('loading').prop('disabled', false).text(originalText);
    }
  }, {
    key: "handleBackToQuiz",
    value: function handleBackToQuiz(e) {
      e.preventDefault();

      // Hide review and show quiz interface
      jQuery('.quiz-review').hide();
      jQuery('.splms-quiz-questions-container, .splms-quiz-navigation').show();
    }

    /**
     * Show quiz results
     * Stops timer, hides quiz interface, displays results
     * @param {Object} results - Quiz results data
     */
  }, {
    key: "showQuizResults",
    value: function showQuizResults(results) {
      // Stop timer
      if (this.timerInterval) {
        clearInterval(this.timerInterval);
        this.timerInterval = null;
      }
      this.isQuizActive = false;

      // Hide quiz interface, quiz actions, and attempt history, show results
      jQuery('#splms-quiz-interface').hide();
      jQuery('.splms-quiz-actions').hide();

      // Note: Attempt history will be populated dynamically by loadAttemptHistoryWithRetry()

      // Show results
      var $resultsContainer = jQuery('#splms-quiz-results');
      $resultsContainer.show();

      // Store results data for view answers functionality
      this.currentResults = results;

      // Check if results should be shown based on results_timing setting
      var resultsTiming = results.results_timing || 'after_passing';
      var shouldShow = results.should_show_results === true; // Only show if explicitly true

      if (!shouldShow) {
        // Show message based on timing
        var message = 'Your quiz results are not available yet.';
        if (resultsTiming === 'after_passing') {
          message = 'Your quiz results will be shown after you pass the quiz.';
        } else if (resultsTiming === 'manual') {
          message = 'Your quiz results will be available after instructor review.';
        }
        this.showResultsPlaceholder(message);
        return;
      }

      // Populate results using the template
      this.populateResults(results);

      // Hide the detailed results section initially
      var $detailedResults = $resultsContainer.find('.splms-quiz-detailed-results');
      if ($detailedResults.length > 0) {
        $detailedResults.hide();
      }

      // Scroll to results
      if ($resultsContainer.length > 0) {
        $resultsContainer[0].scrollIntoView({
          behavior: 'smooth'
        });
      }

      // Dispatch custom event
      (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.dispatchEvent)('splms:quiz:resultShown', {
        quizId: this.settings.quizId,
        results: results
      });
    }
  }, {
    key: "showResultsPlaceholder",
    value: function showResultsPlaceholder(message) {
      var $container = jQuery('#splms-quiz-results');
      $container.html("\n            <div class=\"splms-quiz-results-placeholder\">\n                <div class=\"splms-quiz-results-icon\">\u23F3</div>\n                <h3>Results Pending</h3>\n                <p>".concat(message, "</p>\n            </div>\n        "));
    }
  }, {
    key: "shouldShowAnswers",
    value: function shouldShowAnswers(timing, results) {
      if (!results) return false;
      if (timing === 'after_completion') {
        return true;
      } else if (timing === 'after_passing') {
        return results.passed === true;
      } else if (timing === 'after_all_attempts') {
        return (results.attempts_remaining || 0) === 0;
      }
      return false;
    }
  }, {
    key: "populateResults",
    value: function populateResults(results) {
      var $container = jQuery('#splms-quiz-results');

      // Get quiz type from results
      var quizType = results.quiz_type || 'graded';

      // Map API response to template data - use exact field names from API response
      var resultsData = {
        score_percentage: results.percentage || 0,
        correct_answers: results.correct_answers || 0,
        total_questions: results.total_questions || 0,
        time_taken: results.time_taken || 0,
        passed: results.passed !== undefined ? results.passed : false,
        passing_grade: results.passing_grade || 70,
        attempts_used: results.attempts_used || 0,
        attempts_remaining: results.attempts_remaining || 0,
        max_attempts: results.max_attempts || 0,
        show_answers: results.show_answers !== false,
        detailed_results: results.detailed_results || [],
        pending_review: results.pending_review || false,
        has_manual_review: results.has_manual_review || false,
        manual_review_points: results.manual_review_points || 0,
        quiz_type: quizType,
        is_practice: quizType === 'practice',
        is_survey: quizType === 'survey',
        is_graded: quizType === 'graded'
      };

      // Format time taken for display
      var formattedTime = (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.formatTime)(resultsData.time_taken);

      // Check if quiz has manual review questions (essay, file upload)
      var hasManualReview = resultsData.has_manual_review || resultsData.pending_review;

      // Calculate derived values for template
      // If there are manual review questions, use "pending" status instead of "failed"
      var statusClass, statusIcon, heading, subtitle, statusMessage;
      if (hasManualReview && !resultsData.passed) {
        // Quiz has essay/file upload questions pending review
        statusClass = 'pending';
        statusIcon = '<svg class="splms-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        heading = 'Quiz Submitted';
        subtitle = 'Your quiz has been submitted. Essay questions are pending instructor review.';
        statusMessage = '<span class="splms-icon-clock"><svg class="splms-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span> Pending Review';
      } else {
        statusClass = resultsData.passed ? 'passed' : 'failed';
        if (results.is_preview_mode) {
          statusIcon = '<svg class="splms-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 12S5 4 12 4S23 12 23 12S19 20 12 20S1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
          heading = 'Preview Results';
          if (results.is_guest_attempt) {
            subtitle = 'This was a preview attempt. Results have been saved as guest attempt.';
          } else {
            subtitle = 'This was a preview attempt. Results are not saved.';
          }
          statusMessage = resultsData.passed ? '<span class="splms-icon-check"><svg class="splms-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span> Would Pass!' : '<span class="splms-icon-close"><svg class="splms-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M15 9L9 15M9 9L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span> Would Not Pass';
        } else {
          statusIcon = resultsData.passed ? '<svg class="splms-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 11L12 14L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 12V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' : '<svg class="splms-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8V12M12 16H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
          heading = resultsData.passed ? 'Congratulations!' : 'Quiz Complete';
          subtitle = resultsData.passed ? 'You have passed the quiz!' : 'Keep studying and try again.';
          statusMessage = resultsData.passed ? '<span class="splms-icon-check"><svg class="splms-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span> You Passed!' : '<span class="splms-icon-close"><svg class="splms-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M15 9L9 15M9 9L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span> You Did Not Pass';
        }
      }

      // Calculate retake availability
      // For practice quizzes, always allow retake (encourage practice)
      var retakeAvailable;
      if (resultsData.is_practice) {
        retakeAvailable = true; // Practice quizzes encourage retaking
      } else {
        retakeAvailable = resultsData.attempts_remaining > 0 && !resultsData.passed;
      }

      // Prepare template data object - merge API response data with derived values
      var templateData = _objectSpread(_objectSpread({}, resultsData), {}, {
        // Include all API response fields
        statusClass: statusClass,
        statusIcon: statusIcon,
        heading: heading,
        subtitle: subtitle,
        statusMessage: statusMessage,
        time_taken: formattedTime,
        // Override with formatted time
        retake_available: retakeAvailable
      });

      // Store results data for view answers functionality
      this.currentResults = results;

      // Use WordPress template if available
      var resultsHtml;
      if (typeof wp !== 'undefined' && wp.template) {
        var template = wp.template('splms-quiz-results');
        resultsHtml = template(templateData);
      }

      // Set container classes and inject HTML
      $container.addClass('splms-quiz-results splms-quiz-animation-enter').html(resultsHtml);

      // Show action buttons based on results (using new selectors)
      this.updateResultActions($container, results, resultsData.passed, resultsData.score_percentage);

      // Load attempt history with retry mechanism for database transaction completion
      this.loadAttemptHistoryWithRetry($container, results, 0);

      // Update quiz status badge if quiz was passed
      if (resultsData.passed) {
        this.updateQuizStatusBadge('completed', 'Passed');
      }
    }
  }, {
    key: "handleViewAnswers",
    value: function handleViewAnswers(e) {
      e.preventDefault();

      // If we don't have currentResults (e.g., results loaded from PHP template after refresh),
      // fetch the results via AJAX first
      if (!this.currentResults) {
        this.fetchQuizResultsForAnswerView(e);
        return;
      }

      // Security: Check quiz type first - never show answers for graded quizzes
      var quizType = this.currentResults.quiz_type || 'graded';
      if (quizType !== 'practice') {
        if (typeof SPLMSCore !== 'undefined' && SPLMSCore.helper && SPLMSCore.helper.showNotification) {
          SPLMSCore.helper.showNotification('Answers are not available for graded quizzes.', 'info');
        } else {
          alert('Answers are not available for graded quizzes.');
        }
        return;
      }

      // Check if answers are enabled and timing setting before allowing view
      var showAnswers = this.currentResults.show_answers !== false;
      if (!showAnswers) {
        if (typeof SPLMSCore !== 'undefined' && SPLMSCore.helper && SPLMSCore.helper.showNotification) {
          SPLMSCore.helper.showNotification('Answers are not available for this quiz.', 'info');
        } else {
          alert('Answers are not available for this quiz.');
        }
        return;
      }
      var showAnswersTiming = this.currentResults.show_answers_timing || 'after_completion';
      var shouldShow = this.shouldShowAnswers(showAnswersTiming, this.currentResults);
      if (!shouldShow) {
        if (typeof SPLMSCore !== 'undefined' && SPLMSCore.helper && SPLMSCore.helper.showNotification) {
          SPLMSCore.helper.showNotification('Answers are not available yet based on quiz settings.', 'info');
        } else {
          alert('Answers are not available yet based on quiz settings.');
        }
        return;
      }
      var $container = jQuery('#splms-quiz-results');
      var $button = jQuery(e.currentTarget);
      var $detailedResults = $container.find('.splms-quiz-detailed-results');

      // Ensure detailed results section exists
      if ($detailedResults.length === 0) {
        var detailedHtml = "\n                <div class=\"splms-quiz-detailed-results\" style=\"display: none;\">\n                    <h3>Question Review</h3>\n                    <div class=\"splms-quiz-questions-review\"></div>\n                </div>\n            ";
        $container.find('.splms-quiz-result-actions').before(detailedHtml);
        $detailedResults = $container.find('.splms-quiz-detailed-results');
      }
      var $questionsReview = $detailedResults.find('.splms-quiz-questions-review');

      // Check if container is truly empty (no meaningful content)
      var hasQuestionItems = $questionsReview.find('.splms-quiz-question-review-item, .question-review-item, .question-result-item').length > 0;

      // Force populate if we don't have actual question items
      if ($questionsReview.length > 0 && !hasQuestionItems) {
        this.questionsHandler.populateDetailedResults($questionsReview);
      }

      // Toggle visibility
      if ($detailedResults.is(':visible')) {
        $detailedResults.slideUp(300);
        $button.html('<svg class="splms-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 12S5 4 12 4S23 12 23 12S19 20 12 20S1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> View Answers');
      } else {
        // ALWAYS populate detailed results when showing - regardless of previous checks
        this.questionsHandler.populateDetailedResults($questionsReview);
        $detailedResults.slideDown(300);
        $button.html('<svg class="splms-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17 14L12 9L7 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Hide Answers');
      }
    }

    /**
     * Fetch quiz results via AJAX for answer view when currentResults is not available
     */
  }, {
    key: "fetchQuizResultsForAnswerView",
    value: function fetchQuizResultsForAnswerView(originalEvent) {
      var _window$splms_fronten,
        _window$splms_fronten2,
        _this0 = this;
      var $button = jQuery(originalEvent.currentTarget);
      var quizId = this.settings.quizId || jQuery('[data-quiz-id]').first().data('quiz-id');
      if (!quizId) {
        console.error('Quiz ID not found for fetching results');
        if (typeof SPLMSCore !== 'undefined' && SPLMSCore.helper && SPLMSCore.helper.showNotification) {
          SPLMSCore.helper.showNotification('Unable to load quiz answers.', 'error');
        }
        return;
      }

      // Show loading state
      var originalButtonHtml = $button.html();
      $button.prop('disabled', true).html('<svg class="splms-icon splms-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 2V6" stroke="currentColor" stroke-width="2"/></svg> Loading...');

      // Fetch quiz attempts with detailed answers
      jQuery.ajax({
        url: ((_window$splms_fronten = window.splms_frontend) === null || _window$splms_fronten === void 0 ? void 0 : _window$splms_fronten.ajax_url) || '/wp-admin/admin-ajax.php',
        type: 'POST',
        data: {
          action: 'splms_get_quiz_attempts',
          quiz_id: quizId,
          include_answers: true,
          nonce: ((_window$splms_fronten2 = window.splms_frontend) === null || _window$splms_fronten2 === void 0 || (_window$splms_fronten2 = _window$splms_fronten2.nonces) === null || _window$splms_fronten2 === void 0 ? void 0 : _window$splms_fronten2.splms_frontend_nonce) || ''
        },
        success: function success(response) {
          if (response.success && response.attempts && response.attempts.length > 0) {
            // Use the most recent attempt
            var latestAttempt = response.attempts[0];

            // Convert attempt data to currentResults format
            _this0.currentResults = {
              passed: latestAttempt.passed || false,
              percentage: latestAttempt.percentage || 0,
              score: latestAttempt.score || 0,
              max_score: latestAttempt.max_score || 0,
              time_taken: latestAttempt.time_taken || 0,
              detailed_results: latestAttempt.answers || [],
              show_answers: latestAttempt.quiz_type === 'practice',
              // Security: Only practice quizzes
              show_answers_timing: 'after_completion',
              attempt_id: latestAttempt.id,
              quiz_type: latestAttempt.quiz_type || 'graded'
            };

            // Restore button and trigger handleViewAnswers again
            $button.prop('disabled', false).html(originalButtonHtml);
            _this0.handleViewAnswers(originalEvent);
          } else {
            console.error('Failed to fetch quiz attempts:', response);
            $button.prop('disabled', false).html(originalButtonHtml);
            if (typeof SPLMSCore !== 'undefined' && SPLMSCore.helper && SPLMSCore.helper.showNotification) {
              SPLMSCore.helper.showNotification('No quiz results found.', 'info');
            } else {
              alert('No quiz results found.');
            }
          }
        },
        error: function error(xhr, status, _error4) {
          console.error('AJAX error fetching quiz results:', _error4);
          $button.prop('disabled', false).html(originalButtonHtml);
          if (typeof SPLMSCore !== 'undefined' && SPLMSCore.helper && SPLMSCore.helper.showNotification) {
            SPLMSCore.helper.showNotification('Failed to load quiz answers.', 'error');
          } else {
            alert('Failed to load quiz answers.');
          }
        }
      });
    }

    // Result action handlers
    // NOTE: handleRetakeQuiz is deprecated - retake functionality now handled in handleRetakeQuizFromPHPResults()
  }, {
    key: "handleRetakeQuiz",
    value: function handleRetakeQuiz(e) {
      e.preventDefault();
      console.warn('⚠️ Deprecated handleRetakeQuiz called - retake functionality moved to handleRetakeQuizFromPHPResults()');
      // Fallback to old behavior if new handler fails
      if (confirm('Are you sure you want to retake this quiz? Your current results will remain in your history.')) {
        window.location.reload();
      }
    }
  }, {
    key: "handleNext",
    value: function handleNext(e) {
      e.preventDefault();
      // Simple next action - go to course overview or next section
      var courseUrl = jQuery('.splms-quiz-breadcrumb a').last().attr('href');
      if (courseUrl) {
        window.location.href = courseUrl;
      } else {
        window.location.reload();
      }
    }
  }, {
    key: "handleViewCourse",
    value: function handleViewCourse(e) {
      e.preventDefault();
      // Navigate to course overview/main page
      var courseUrl = jQuery('.splms-quiz-breadcrumb a').last().attr('href');
      if (courseUrl) {
        window.location.href = courseUrl;
      } else {
        window.location.reload();
      }
    }

    // Utility methods
  }, {
    key: "updateResultActions",
    value: function updateResultActions($container, results, passed, percentage) {
      var attemptsRemaining = results.attempts_remaining || 0;
      var attemptsUsed = results.attempts_used || 0;
      var maxAttempts = results.max_attempts || 3;
      var showAnswers = results.show_answers !== false;
      var passingGrade = results.passing_grade || this.settings.passingGrade || 70;
      var quizType = results.quiz_type || 'graded';
      var isPractice = quizType === 'practice';
      var isSurvey = quizType === 'survey';

      // Get button references
      var $retakeBtn = $container.find('.retake-quiz-btn');
      var $viewAnswersBtn = $container.find('.view-answers-btn');
      var $nextBtn = $container.find('.next-btn');
      var $viewCourseBtn = $container.find('.view-course-btn');

      // Hide all buttons first
      $retakeBtn.hide();
      $viewAnswersBtn.hide();
      $nextBtn.hide();
      $viewCourseBtn.hide();

      // Show retake button logic
      if (isPractice) {
        // Practice quizzes: Always show retake button (encourage practice)
        $retakeBtn.show().text('🔄 Practice Again').removeClass('disabled');
      } else if (attemptsRemaining > 0 && !passed) {
        // Graded quizzes: Show retake if attempts remaining and failed
        $retakeBtn.show().text('🔄 Retake Quiz');
      }

      // Security: Only show view answers button for practice quizzes
      // For graded/assessment quizzes, hide answers to maintain academic integrity
      if (showAnswers && isPractice) {
        var showAnswersTiming = results.show_answers_timing || 'after_completion';
        var shouldShow = this.shouldShowAnswers(showAnswersTiming, results);
        if (shouldShow) {
          $viewAnswersBtn.show().text('👁️ View Answers');
        }
      }
      // Note: View Answers button remains hidden for graded quizzes (security)

      // Show navigation buttons when quiz is completed (passed) OR no attempts remaining
      if (passed || attemptsRemaining === 0) {
        $nextBtn.show();
        $viewCourseBtn.show();
      }
    }

    /**
     * Update the quiz status badge in the header
     */
  }, {
    key: "updateQuizStatusBadge",
    value: function updateQuizStatusBadge(statusClass, statusText) {
      var $statusBadge = jQuery('.splms-status-badge');
      if ($statusBadge.length > 0) {
        // Remove existing status classes
        $statusBadge.removeClass('splms-status-in-progress splms-status-completed');

        // Add new status class
        $statusBadge.addClass('splms-status-' + statusClass);

        // Update status text
        $statusBadge.text(statusText);
      }
    }

    /**
     * Load attempt history with retry mechanism for database transaction completion
     */
  }, {
    key: "loadAttemptHistoryWithRetry",
    value: function loadAttemptHistoryWithRetry($container, results) {
      var _this1 = this;
      var retryCount = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 0;
      var maxRetries = 3;
      var baseDelay = 500;
      var retryDelay = baseDelay + retryCount * 500; // Progressive delay: 500ms, 1s, 1.5s

      setTimeout(function () {
        _this1.loadAttemptHistory($container, results, function (attempts) {
          // Check if the current attempt is included in the results
          var currentAttemptId = results.attempt_id || results.id;
          var attemptFound = currentAttemptId && attempts && attempts.find(function (a) {
            return a.id == currentAttemptId;
          });
          if (!attemptFound && retryCount < maxRetries && currentAttemptId) {
            _this1.loadAttemptHistoryWithRetry($container, results, retryCount + 1);
          } else if (retryCount > 0) {}
        });
      }, retryDelay);
    }

    /**
     * Load and display attempt history
     */
  }, {
    key: "loadAttemptHistory",
    value: function loadAttemptHistory($container, results) {
      var _this10 = this;
      var callback = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
      // Try multiple selectors to find the attempt history container
      var $attemptHistory = $container.find('.splms-quiz-attempt-history .splms-quiz-attempts-list');
      if ($attemptHistory.length === 0) {
        $attemptHistory = $container.find('.splms-quiz-attempts-list');
      }
      if ($attemptHistory.length === 0) {
        $attemptHistory = $container.find('.attempt-history .attempts-list');
      }
      if ($attemptHistory.length === 0) {
        // Try to find or create the attempt history section
        var $attemptSection = $container.find('.splms-quiz-attempt-history');
        if ($attemptSection.length === 0) {
          // Create the section if it doesn't exist - insert before result actions
          $attemptSection = jQuery("\n                    <div class=\"splms-quiz-attempt-history\">\n                        <h4>Attempt History</h4>\n                        <div class=\"splms-quiz-attempts-list\"></div>\n                    </div>\n                ");
          // Insert before result actions or at the end
          var $actions = $container.find('.splms-quiz-result-actions');
          if ($actions.length > 0) {
            $actions.before($attemptSection);
          } else {
            $container.append($attemptSection);
          }
        }
        $attemptHistory = $attemptSection.find('.splms-quiz-attempts-list');
      }
      if ($attemptHistory.length === 0) {
        console.warn('Could not find or create attempt history container');
        return;
      }

      // Get attempt history from server
      // Try multiple sources for quiz ID
      var quizId = this.settings.quizId;
      if (!quizId && results) {
        quizId = results.quiz_id || results.quizId;
      }
      if (!quizId && this.quizData) {
        quizId = this.quizData.quiz_id || this.quizData.quizId;
      }
      if (!quizId) {
        console.warn('No quiz ID available for loading attempt history. Settings:', this.settings, 'Results:', results);
        $attemptHistory.html('<div class="no-attempts-message">Unable to load attempt history.</div>');
        return;
      }
      var frontend = window.splms_frontend;
      jQuery.ajax({
        url: frontend.ajax_url,
        type: 'POST',
        data: {
          action: 'splms_get_quiz_attempts',
          quiz_id: quizId,
          nonce: frontend.nonces.splms_nonce
        },
        success: function success(response) {
          var attempts = [];
          if (response.success && response.data) {
            attempts = Array.isArray(response.data) ? response.data : [];
            if (attempts.length > 0) {
              _this10.renderAttemptHistory($container, attempts);
            } else {
              // Show message if no attempts found
              $attemptHistory.html('<div class="no-attempts-message">No previous attempts found.</div>');
            }
          } else {
            console.warn('Attempt history response error:', response);
            $attemptHistory.html('<div class="no-attempts-message">Unable to load attempt history.</div>');
          }

          // Call callback if provided for retry mechanism
          if (callback && typeof callback === 'function') {
            callback(attempts);
          }
        },
        error: function error(xhr, status, _error5) {
          $attemptHistory.html('<div class="no-attempts-message">Failed to load attempt history. Please refresh the page.</div>');

          // Call callback with empty array if provided for retry mechanism
          if (callback && typeof callback === 'function') {
            callback([]);
          }
        }
      });
    }

    /**
     * Render attempt history list
     */
  }, {
    key: "renderAttemptHistory",
    value: function renderAttemptHistory($container, attempts) {
      // Try multiple selectors to find the attempts list
      var $attemptsList = $container.find('.splms-quiz-attempts-list');
      if ($attemptsList.length === 0) {
        $attemptsList = $container.find('.attempts-list');
      }
      if ($attemptsList.length === 0) {
        $attemptsList = $container.find('.splms-quiz-attempt-history .splms-quiz-attempts-list');
      }
      if ($attemptsList.length === 0) {
        console.warn('Could not find attempts list container in renderAttemptHistory');
        return;
      }
      if (!Array.isArray(attempts) || attempts.length === 0) {
        $attemptsList.html("\n                <div class=\"splms-no-attempts-message\">\n                    <svg width=\"48\" height=\"48\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n                        <circle cx=\"12\" cy=\"12\" r=\"10\" stroke=\"currentColor\" stroke-width=\"2\"/>\n                        <line x1=\"12\" y1=\"8\" x2=\"12\" y2=\"12\" stroke=\"currentColor\" stroke-width=\"2\"/>\n                        <line x1=\"12\" y1=\"16\" x2=\"12.01\" y2=\"16\" stroke=\"currentColor\" stroke-width=\"2\"/>\n                    </svg>\n                    <p>No previous attempts found.</p>\n                </div>\n            ");
        return;
      }
      var attemptsHtml = '';
      attempts.forEach(function (attempt, index) {
        var attemptNumber = attempts.length - index; // Reverse order (latest first)
        var passed = attempt.passed === 1 || attempt.passed === true || attempt.passed === '1';
        var score = attempt.score !== undefined ? Math.round(parseFloat(attempt.score)) : 0;
        var maxScore = attempt.max_score !== undefined ? parseFloat(attempt.max_score) : 100;
        var percentage = maxScore > 0 ? Math.round(score / maxScore * 100) : score;

        // Check if this attempt is pending review
        var pendingReview = attempt.pending_review === true || attempt.pending_review === 1 || attempt.pending_review === '1';
        var hasManualReview = attempt.has_manual_review === true || attempt.has_manual_review === 1 || attempt.has_manual_review === '1';

        // Try multiple date fields and format them properly
        var date = attempt.attempt_time || attempt.attempt_date || attempt.date || attempt.created_at || '';
        var formattedDate = '';
        if (date) {
          try {
            var dateObj = new Date(date);
            if (!isNaN(dateObj.getTime())) {
              // Format to match PHP template: "Jan 22, 2026 at 11:50 AM"
              formattedDate = dateObj.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
              }) + ' at ' + dateObj.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
              });
            } else {
              formattedDate = date; // Use as-is if parsing fails
            }
          } catch (e) {
            formattedDate = date;
          }
        }

        // Determine status classes and icons to match PHP template
        var historyStatusClass, historyStatusText, historyStatusIcon;
        if (pendingReview || hasManualReview && !passed && score === 0) {
          historyStatusClass = 'attempt-pending';
          historyStatusText = 'Pending Review';
          historyStatusIcon = '⏳';
        } else if (passed) {
          historyStatusClass = 'attempt-passed';
          historyStatusText = 'Passed';
          historyStatusIcon = '✅';
        } else {
          historyStatusClass = 'attempt-failed';
          historyStatusText = 'Failed';
          historyStatusIcon = '❌';
        }

        // Calculate time taken display
        var timeTaken = attempt.time_taken || 0;
        var timeDisplay = timeTaken > 0 ? "".concat(Math.floor(timeTaken / 60), ":").concat(String(timeTaken % 60).padStart(2, '0')) : '0:00';

        // Generate HTML structure matching PHP template exactly
        attemptsHtml += "\n                <div class=\"splms-quiz-attempt-item ".concat(historyStatusClass, "\">\n                    <div class=\"splms-attempt-header\">\n                        <div class=\"splms-attempt-number\">\n                            <span class=\"splms-attempt-badge\">#").concat(attemptNumber, "</span>\n                        </div>\n                        <div class=\"splms-attempt-status-badge ").concat(historyStatusClass, "\">\n                            <span class=\"splms-status-icon\">").concat(historyStatusIcon, "</span>\n                            <span class=\"splms-status-text\">").concat(historyStatusText, "</span>\n                        </div>\n                    </div>\n                    <div class=\"splms-attempt-details\">\n                        <div class=\"splms-attempt-detail-item\">\n                            <svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n                                <rect x=\"3\" y=\"4\" width=\"18\" height=\"18\" rx=\"2\" ry=\"2\" stroke=\"currentColor\" stroke-width=\"2\"/>\n                                <line x1=\"16\" y1=\"2\" x2=\"16\" y2=\"6\" stroke=\"currentColor\" stroke-width=\"2\"/>\n                                <line x1=\"8\" y1=\"2\" x2=\"8\" y2=\"6\" stroke=\"currentColor\" stroke-width=\"2\"/>\n                                <line x1=\"3\" y1=\"10\" x2=\"21\" y2=\"10\" stroke=\"currentColor\" stroke-width=\"2\"/>\n                            </svg>\n                            <span class=\"splms-attempt-date\">").concat(formattedDate, "</span>\n                        </div>\n                        <div class=\"splms-attempt-detail-item\">\n                            <svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n                                <path d=\"M9 11H15M9 15H15M17 21L12 16L7 21V5C7 3.89543 7.89543 3 9 3H15C16.1046 3 17 3.89543 17 5V21Z\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n                            </svg>\n                            <span class=\"splms-attempt-score\">").concat(percentage, "% (").concat(score, "/").concat(maxScore, ")</span>\n                        </div>\n                        <div class=\"splms-attempt-detail-item\">\n                            <svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n                                <circle cx=\"12\" cy=\"12\" r=\"10\" stroke=\"currentColor\" stroke-width=\"2\"/>\n                                <polyline points=\"12,6 12,12 16,14\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n                            </svg>\n                            <span class=\"splms-attempt-time\">").concat(timeDisplay, "</span>\n                        </div>\n                    </div>\n                </div>\n            ");
      });
      $attemptsList.html(attemptsHtml);
    }
  }, {
    key: "markQuizAsComplete",
    value: function markQuizAsComplete(progressData) {
      // Find the current quiz item in the curriculum
      var quizId = this.settings.quizId;
      var $quizItem = jQuery("a[data-lesson-id=\"".concat(quizId, "\"][data-item-type=\"quiz\"]")).closest('.curriculum-item');

      // Alternative selectors if first one doesn't work
      if (!$quizItem.length) {
        $quizItem = jQuery("a[data-lesson-id=\"".concat(quizId, "\"]")).closest('.curriculum-item');
      }
      if (!$quizItem.length) {
        $quizItem = jQuery("a[href*=\"post=".concat(quizId, "\"]")).closest('.curriculum-item');
      }
      if (!$quizItem.length) {
        $quizItem = jQuery('.curriculum-item.current').filter(function () {
          return jQuery(this).find('a').attr('href') && jQuery(this).find('a').attr('href').includes(quizId);
        });
      }
      if ($quizItem.length) {
        // Add completed class and remove current class
        $quizItem.addClass('completed').removeClass('current');

        // Update status text/icon
        var $statusElement = $quizItem.find('.item-status');
        if ($statusElement.length) {
          $statusElement.html('✓');
        } else {
          $quizItem.append('<span class="completion-check">✓</span>');
        }

        // Add completion animation
        $quizItem.addClass('completion-animation');
        setTimeout(function () {
          $quizItem.removeClass('completion-animation');
        }, 1000);

        // Update progress bar using server-side calculated progress
        if (window.SPLMSCurriculum && window.SPLMSCurriculum.updateProgress && progressData) {
          window.SPLMSCurriculum.updateProgress(progressData);
        }

        // Show notification
        SPLMSCore.helper.showNotification('Quiz completed! 🎉', 'success');
      }
    }
  }, {
    key: "handleKeyboardEvents",
    value: function handleKeyboardEvents(e) {
      if (!this.isQuizActive) return;

      // Escape key to close (with confirmation)
      if (e.keyCode === 27) {
        this.handleCloseQuiz(e);
      }

      // Arrow keys for navigation
      if (e.keyCode === 37) {
        // Left arrow
        this.handlePreviousPage(e);
      } else if (e.keyCode === 39) {
        // Right arrow
        this.handleNextPage(e);
      }
    }
  }, {
    key: "handlePageLeave",
    value: function handlePageLeave(e) {
      if (this.isQuizActive) {
        var message = 'You have an active quiz. Are you sure you want to leave?';
        e.returnValue = message;
        return message;
      }
    }
  }, {
    key: "handleCloseQuiz",
    value: function handleCloseQuiz(e) {
      e.preventDefault();
      if (this.isQuizActive) {
        var confirmClose = confirm('Are you sure you want to close the quiz? Your progress will be lost.');
        if (!confirmClose) {
          return;
        }
      }
      this.hideQuizModal();
    }
  }, {
    key: "hideQuizModal",
    value: function hideQuizModal() {
      var $modal = jQuery('#splms-quiz-modal');
      $modal.fadeOut(300);
      jQuery('body').removeClass('quiz-modal-open');

      // Clear timer
      if (this.timerInterval) {
        clearInterval(this.timerInterval);
        this.timerInterval = null;
      }
      this.isQuizActive = false;
    }

    // Public method to check if quiz is active
  }, {
    key: "isActive",
    value: function isActive() {
      return this.isQuizActive;
    }

    // Public method to get current quiz data
  }, {
    key: "getCurrentQuizData",
    value: function getCurrentQuizData() {
      return this.quizData;
    }

    // Public method to get current answers
  }, {
    key: "getCurrentAnswers",
    value: function getCurrentAnswers() {
      return this.answers;
    }

    // Helper method to calculate current page based on current question
  }, {
    key: "calculateCurrentPage",
    value: function calculateCurrentPage() {
      var questionsPerPage = this.settings.questionsPerPage || 1;
      return Math.ceil((this.currentQuestion + 1) / questionsPerPage);
    }

    // Helper method to update current page consistently
  }, {
    key: "updateCurrentPage",
    value: function updateCurrentPage() {
      this.currentPage = this.calculateCurrentPage();
    }

    /**
     * Clean and validate answers before submission
     * Ensures answers are in the format expected by the evaluator:
     * - multiple_choice/true_false: option ID string (not array, not object)
     * - multiple_select: array of option IDs (not empty array, not object)
     * - short_answer/fill_blank/essay: text string
     * - ordering: array of option IDs
     * - matching: object with leftId: rightId pairs
     * - file_upload: file name string
     * 
     * @param {Object} rawAnswers - Raw answers object from this.answers
     * @returns {Object} Cleaned answers object
     */
  }, {
    key: "cleanAnswersForSubmission",
    value: function cleanAnswersForSubmission(rawAnswers) {
      var cleaned = {};

      // Get question data to determine question types
      var questionsMap = {};
      if (this.questionsData && Array.isArray(this.questionsData)) {
        this.questionsData.forEach(function (q) {
          questionsMap[q.id] = q;
        });
      }
      Object.keys(rawAnswers).forEach(function (questionId) {
        var answer = rawAnswers[questionId];
        var question = questionsMap[questionId];
        var questionType = question ? question.type : 'unknown';

        // Skip if answer is null or undefined
        if (answer === null || answer === undefined) {
          return; // Don't include in cleaned answers
        }

        // Handle different question types
        switch (questionType) {
          case 'multiple_choice':
          case 'true_false':
            // Single select - must be option text string
            if (Array.isArray(answer)) {
              // If it's an array, take the first element (should be option text)
              cleaned[questionId] = answer.length > 0 ? String(answer[0]) : '';
            } else if (_typeof(answer) === 'object' && answer !== null) {
              // If it's an object, try to extract text
              cleaned[questionId] = answer.text ? String(answer.text) : '';
            } else {
              // Should be option text string
              cleaned[questionId] = answer ? String(answer) : '';
            }
            break;
          case 'multiple_select':
            // Multiple select - must be array of option text values
            if (Array.isArray(answer)) {
              // Filter out empty values and convert to strings
              var optionTexts = answer.filter(function (v) {
                return v !== null && v !== undefined && v !== '';
              }).map(function (v) {
                // If array element is an object, extract text
                if (_typeof(v) === 'object' && v !== null && v.text) {
                  return String(v.text);
                }
                return String(v);
              });
              cleaned[questionId] = optionTexts.length > 0 ? optionTexts : '';
            } else if (_typeof(answer) === 'object' && answer !== null) {
              // If it's an object, try to extract texts
              cleaned[questionId] = '';
            } else {
              // Single value - convert to array
              cleaned[questionId] = answer ? [String(answer)] : '';
            }
            break;
          case 'short_answer':
          case 'fill_blank':
          case 'essay':
          case 'long_answer':
            // Text input - must be string
            if (Array.isArray(answer)) {
              cleaned[questionId] = answer.length > 0 ? String(answer[0]) : '';
            } else if (_typeof(answer) === 'object' && answer !== null) {
              cleaned[questionId] = '';
            } else {
              cleaned[questionId] = answer ? String(answer).trim() : '';
            }
            break;
          case 'ordering':
            // Ordering - must be array of option text values (not indices)
            if (Array.isArray(answer)) {
              // Convert all elements to strings (option text values)
              var _optionTexts = answer.filter(function (v) {
                return v !== null && v !== undefined && v !== '';
              }).map(function (v) {
                if (_typeof(v) === 'object' && v !== null && v.text) {
                  return String(v.text);
                }
                return String(v);
              });
              cleaned[questionId] = _optionTexts;
            } else if (_typeof(answer) === 'object' && answer !== null) {
              cleaned[questionId] = [];
            } else {
              cleaned[questionId] = answer ? [String(answer)] : [];
            }
            break;
          case 'matching':
            // Matching - must be object with leftText: rightText pairs
            // Backend expects: {left_text: right_text}
            if (_typeof(answer) === 'object' && answer !== null && !Array.isArray(answer)) {
              // Already in object format - clean it (assume keys and values are text)
              var cleanedPairs = {};
              Object.keys(answer).forEach(function (key) {
                var value = answer[key];
                if (value !== null && value !== undefined && value !== '') {
                  cleanedPairs[String(key)] = String(value);
                }
              });
              cleaned[questionId] = Object.keys(cleanedPairs).length > 0 ? cleanedPairs : '';
            } else if (Array.isArray(answer)) {
              // Array format: [rightText0, rightText1, ...] where index = pair index
              // Convert to: {left_text: right_text} using question pairs
              var _cleanedPairs = {};
              var _question = questionsMap[questionId];
              if (_question && _question.pairs && Array.isArray(_question.pairs) && _question.pairs.length > 0) {
                var pairs = _question.pairs;
                answer.forEach(function (rightText, index) {
                  if (rightText && pairs[index]) {
                    var pair = pairs[index];
                    var leftText = pair.left ? String(pair.left) : '';
                    var rightValue = String(rightText);
                    if (leftText && rightValue) {
                      _cleanedPairs[leftText] = rightValue;
                    }
                  }
                });
              }
              cleaned[questionId] = Object.keys(_cleanedPairs).length > 0 ? _cleanedPairs : '';
            } else {
              cleaned[questionId] = '';
            }
            break;
          case 'file_upload':
            // File upload - must be file name string (or URL after upload)
            if (Array.isArray(answer)) {
              cleaned[questionId] = answer.length > 0 ? String(answer[0]) : '';
            } else if (_typeof(answer) === 'object' && answer !== null) {
              cleaned[questionId] = '';
            } else {
              cleaned[questionId] = answer ? String(answer) : '';
            }
            break;
          default:
            // Unknown type - try to clean as best we can
            if (Array.isArray(answer)) {
              cleaned[questionId] = answer.length > 0 ? answer : '';
            } else if (_typeof(answer) === 'object' && answer !== null) {
              cleaned[questionId] = '';
            } else {
              cleaned[questionId] = answer ? String(answer) : '';
            }
        }
      });
      return cleaned;
    }

    /**
     * Comprehensive reset for retaking quiz
     * Clears all quiz state and prepares for fresh start
     */
  }, {
    key: "resetQuizForRetake",
    value: function resetQuizForRetake() {
      var _this$settings;
      // 1. Clear browser storage
      if (this.settings && this.settings.quizId) {
        (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.clearStorage)(this.settings.quizId);
      }

      // 2. Reset instance properties
      this.attemptId = null;
      this.currentResults = null;
      this.savedAnswers = {};
      this.isQuizActive = false;
      this.isSubmitting = false;
      this.questionsData = null;
      this.currentQuestionIndex = 0;

      // 3. Clear timer state
      if (this.timerInterval) {
        clearInterval(this.timerInterval);
        this.timerInterval = null;
      }
      if (this.autoSaveInterval) {
        clearInterval(this.autoSaveInterval);
        this.autoSaveInterval = null;
      }

      // 4. Reset UI elements
      jQuery('#splms-quiz-interface').hide();
      jQuery('#splms-quiz-results').hide();

      // Clear any error messages or notifications
      jQuery('.splms-quiz-error, .splms-quiz-notification').remove();

      // Reset form elements in quiz interface
      jQuery('#splms-quiz-interface input, #splms-quiz-interface textarea, #splms-quiz-interface select').val('');
      jQuery('#splms-quiz-interface input[type="radio"], #splms-quiz-interface input[type="checkbox"]').prop('checked', false);

      // 5. Reset navigation and progress
      this.pagination = {
        currentPage: 1,
        totalPages: 1,
        questionsPerPage: 1
      };

      // 6. Show quiz interface elements
      jQuery('.splms-quiz-stats').show();
      jQuery('.splms-quiz-description').show();
      jQuery('.splms-quiz-actions').show();

      // 7. Reset and enable start/retake buttons
      var startButtons = jQuery('.start-quiz-btn, .restart-quiz-btn');
      startButtons.prop('disabled', false).removeClass('loading').each(function () {
        var $btn = jQuery(this);
        if ($btn.hasClass('restart-quiz-btn')) {
          $btn.text($btn.data('original-text') || 'Start New Attempt');
        } else {
          $btn.text($btn.data('original-text') || 'Start Quiz');
        }
      });

      // 8. Verify reset state
      this.verifyResetState();

      // 9. Dispatch reset event for any listeners
      (0,_quiz_utils_js__WEBPACK_IMPORTED_MODULE_0__.dispatchEvent)('splms:quiz:reset', {
        quizId: (_this$settings = this.settings) === null || _this$settings === void 0 ? void 0 : _this$settings.quizId
      });
    }

    /**
     * Verify that quiz state has been properly reset
     */
  }, {
    key: "verifyResetState",
    value: function verifyResetState() {
      var checks = {
        'Attempt ID cleared': this.attemptId === null,
        'Current results cleared': this.currentResults === null,
        'Saved answers cleared': Object.keys(this.savedAnswers).length === 0,
        'Quiz not active': this.isQuizActive === false,
        'Not submitting': this.isSubmitting === false,
        'Questions data cleared': this.questionsData === null,
        'Question index reset': this.currentQuestionIndex === 0,
        'Timer intervals cleared': this.timerInterval === null && this.autoSaveInterval === null,
        'Results container hidden': jQuery('#splms-quiz-results').is(':hidden'),
        'Quiz interface hidden': jQuery('#splms-quiz-interface').is(':hidden'),
        'Stats visible': jQuery('.splms-quiz-stats').is(':visible'),
        'Description visible': jQuery('.splms-quiz-description').is(':visible'),
        'Actions visible': jQuery('.splms-quiz-actions').is(':visible')
      };
      var allPassed = true;
      Object.entries(checks).forEach(function (_ref5) {
        var _ref6 = _slicedToArray(_ref5, 2),
          checkName = _ref6[0],
          passed = _ref6[1];
        var status = passed ? '✅' : '❌';
        if (!passed) allPassed = false;
      });
      if (allPassed) {} else {
        console.warn('⚠️ Some reset state checks failed - quiz may not be fully reset');
      }
      return allPassed;
    }
  }]);
}(); // Initialize when document is ready
jQuery(document).ready(function () {
  // Only initialize on quiz pages
  if (jQuery('.splms-quiz-container').length || jQuery('#splms-quiz-modal').length) {
    window.SPLMSQuiz = new SPLMSQuiz();

    // Handle retake quiz functionality with PHP-rendered results
    handleRetakeQuizFromPHPResults();
  }
});

/**
 * Handle retake quiz functionality when results are rendered by PHP
 */
function handleRetakeQuizFromPHPResults() {
  // Listen for retake quiz button clicks in PHP-rendered results
  jQuery(document).on('click', '.retake-quiz-btn', function (e) {
    e.preventDefault();

    // Show confirmation dialog
    if (!confirm('Are you sure you want to retake this quiz? Your current results will remain in your history.')) {
      return;
    }

    // Get quiz and course IDs
    var quizId = jQuery(this).data('quiz-id');
    var courseId = jQuery(this).data('course-id');
    if (!quizId) {
      console.error('❌ Quiz ID not found for retake');
      return;
    }

    // Show loading state on the retake button
    var $retakeBtn = jQuery(this);
    var originalText = $retakeBtn.text();
    $retakeBtn.text('Resetting Quiz...').prop('disabled', true);

    // Get the SPLMSQuiz instance
    if (!window.SPLMSQuiz) {
      console.error('❌ SPLMSQuiz instance not found');
      $retakeBtn.text(originalText).prop('disabled', false);
      return;
    }

    // Set quiz settings if needed
    if (!window.SPLMSQuiz.settings || !window.SPLMSQuiz.settings.quizId) {
      window.SPLMSQuiz.settings = window.SPLMSQuiz.settings || {};
      window.SPLMSQuiz.settings.quizId = quizId;
      window.SPLMSQuiz.settings.courseId = courseId;
    }

    // Perform comprehensive reset
    try {
      window.SPLMSQuiz.resetQuizForRetake();

      // Smooth scroll to quiz actions after reset
      setTimeout(function () {
        var actionsElement = document.querySelector('.splms-quiz-actions');
        if (actionsElement) {
          actionsElement.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
          });
        }

        // Show success message briefly
        var startButton = jQuery('.start-quiz-btn');
        if (startButton.length) {
          startButton.text('Ready to Start New Attempt!').addClass('pulse-animation');
          setTimeout(function () {
            startButton.text('Start Quiz').removeClass('pulse-animation');
          }, 2000);
        }
      }, 500);
    } catch (error) {
      console.error('❌ Error during quiz reset:', error);

      // Fallback - simple show/hide if reset fails
      jQuery('#splms-quiz-results').hide();
      jQuery('.splms-quiz-stats, .splms-quiz-description, .splms-quiz-actions').show();
    }

    // Re-enable retake button
    $retakeBtn.text(originalText).prop('disabled', false);
  });
}

// Additional styles for quiz modal and detailed results
jQuery(document).ready(function () {
  if (!jQuery('#splms-quiz-styles').length) {
    jQuery('head').append("\n            <style id=\"splms-quiz-styles\">\n                body.quiz-modal-open {\n                    overflow: hidden;\n                }\n\n                /* Pulse animation for retake success feedback */\n                .pulse-animation {\n                    animation: pulseGlow 2s ease-in-out;\n                }\n\n                @keyframes pulseGlow {\n                    0% { box-shadow: 0 0 0 0 var(--splms-primary-alpha-7, rgba(126, 117, 255, 0.7)); }\n                    50% { box-shadow: 0 0 0 10px var(--splms-primary-alpha-0, rgba(126, 117, 255, 0)); }\n                    100% { box-shadow: 0 0 0 0 var(--splms-primary-alpha-0, rgba(126, 117, 255, 0)); }\n                }\n                \n                .quiz-modal-open .splms-quiz-modal {\n                    display: flex !important;\n                }\n                \n                .option.selected {\n                    background-color: var(--splms-warning-bg, #fef3c7) !important;\n                    border-color: var(--splms-warning, #f59e0b) !important;\n                }\n                \n                .quiz-timer.danger {\n                    animation: timerPulse 1s infinite;\n                    color: var(--splms-danger, #ef4444) !important;\n                }\n                \n                @keyframes timerPulse {\n                    0% { opacity: 1; }\n                    50% { opacity: 0.6; }\n                    100% { opacity: 1; }\n                }\n                \n                .quiz-nav-btn.loading,\n                .quiz-submit-btn.loading,\n                .start-quiz-btn.loading {\n                    opacity: 0.7;\n                    cursor: not-allowed;\n                }\n                \n                /* Detailed Results Styles */\n                .detailed-results {\n                    margin: 1.5rem 0;\n                    padding: 1rem;\n                    border: 1px solid var(--splms-border-color, #e5e7eb);\n                    border-radius: var(--splms-border-radius, 0.5rem);\n                    background-color: var(--splms-background-secondary, #f9fafb);\n                }\n                \n                .detailed-results-header h3 {\n                    margin: 0 0 1rem 0;\n                    color: var(--splms-text-color, #374151);\n                    font-size: 1.1rem;\n                }\n                \n                .question-result-item {\n                    margin-bottom: 1rem;\n                    padding: 1rem;\n                    border-radius: 0.375rem;\n                    background-color: white;\n                    border-left: 4px solid var(--splms-border-color, #e5e7eb);\n                }\n                \n                .question-result-item.correct {\n                    border-left-color: var(--splms-success, #10b981);\n                    background-color: var(--splms-success-bg, #f0fdf4);\n                }\n                \n                .question-result-item.incorrect {\n                    border-left-color: var(--splms-danger, #ef4444);\n                    background-color: var(--splms-danger-bg, #fef2f2);\n                }\n                \n                .question-result-header {\n                    display: flex;\n                    align-items: center;\n                    margin-bottom: 0.75rem;\n                    gap: 0.5rem;\n                }\n                \n                .question-number {\n                    font-weight: 600;\n                    color: var(--splms-text-color, #374151);\n                }\n                \n                .result-icon {\n                    font-size: 1.1rem;\n                }\n                \n                .question-points {\n                    margin-left: auto;\n                    font-size: 0.875rem;\n                    color: var(--splms-text-muted, #6b7280);\n                    background-color: var(--splms-background-tertiary, #f3f4f6);\n                    padding: 0.25rem 0.5rem;\n                    border-radius: 0.25rem;\n                }\n                \n                .question-text {\n                    font-weight: 500;\n                    margin-bottom: 0.75rem;\n                    color: var(--splms-text-color, #374151);\n                }\n                \n                .answer-comparison {\n                    margin-bottom: 0.75rem;\n                }\n                \n                .user-answer, .correct-answer {\n                    margin-bottom: 0.5rem;\n                }\n                \n                .answer-value {\n                    display: inline-block;\n                    padding: 0.25rem 0.5rem;\n                    border-radius: 0.25rem;\n                    font-weight: 500;\n                    margin-left: 0.5rem;\n                }\n                \n                .answer-value.correct-answer {\n                    background-color: var(--splms-success-light, #d1fae5);\n                    color: var(--splms-success-dark, #065f46);\n                }\n                \n                .answer-value.incorrect-answer {\n                    background-color: var(--splms-danger-light, #fee2e2);\n                    color: var(--splms-danger-dark, #991b1b);\n                }\n                \n                .question-explanation {\n                    margin-top: 0.75rem;\n                    padding: 0.75rem;\n                    background-color: var(--splms-info-bg, #f0f9ff);\n                    border-radius: var(--splms-border-radius-sm, 0.375rem);\n                    border-left: 3px solid var(--splms-info, #0ea5e9);\n                }\n                \n                .question-explanation p {\n                    margin: 0.5rem 0 0 0;\n                    color: var(--splms-text-color, #374151);\n                    line-height: 1.5;\n                }\n                \n                .no-detailed-results {\n                    text-align: center;\n                    padding: 2rem;\n                    color: var(--splms-text-muted, #6b7280);\n                    font-style: italic;\n                }\n                \n                @media (max-width: 768px) {\n                    .splms-quiz-modal {\n                        padding: 0.5rem;\n                    }\n                    \n                    .quiz-modal-content {\n                        margin: 0;\n                        border-radius: 0.5rem;\n                    }\n                    \n                    .detailed-results {\n                        padding: 0.75rem;\n                    }\n                    \n                    .question-result-item {\n                        padding: 0.75rem;\n                    }\n                    \n                    .question-result-header {\n                        flex-wrap: wrap;\n                        gap: 0.25rem;\n                    }\n                }\n            </style>\n        ");
  }
});

/***/ },

/***/ "./src/js/frontend/modules/search/search-filter.js"
/*!*********************************************************!*\
  !*** ./src/js/frontend/modules/search/search-filter.js ***!
  \*********************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SPLMSSearchFilter: () => (/* binding */ SPLMSSearchFilter)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * SkillPulse LMS Search & Filter
 * Handles course search, filtering, and layout switching
 */

var SPLMSSearchFilter = /*#__PURE__*/function () {
  function SPLMSSearchFilter() {
    _classCallCheck(this, SPLMSSearchFilter);
    this.searchTimeout = null;
    this.init();
  }
  return _createClass(SPLMSSearchFilter, [{
    key: "init",
    value: function init() {
      this.bindEvents();
      this.initLayoutSwitcher();
      this.loadSavedLayout();
    }
  }, {
    key: "bindEvents",
    value: function bindEvents() {
      // Search toggle functionality
      jQuery(document).on('click', '.splms-search-toggle', this.handleSearchToggle.bind(this));
      jQuery(document).on('click', '.splms-search-close', this.handleSearchClose.bind(this));

      // Keyboard support
      jQuery(document).on('keydown', this.handleKeyboard.bind(this));

      // Close search when clicking outside
      jQuery(document).on('click', this.handleClickOutsideSearch.bind(this));

      // Search functionality
      jQuery('.splms-search-input').on('focus', this.handleSearchFocus.bind(this));
      jQuery('.splms-search-input').on('blur', this.handleSearchBlur.bind(this));
      jQuery('.splms-search-input').on('input', this.handleSearchInput.bind(this));

      // Clear search
      jQuery(document).on('click', '.search-clear-btn', this.handleSearchClear.bind(this));

      // Filter changes
      jQuery('.sidebar-filters-form').on('change', 'input', this.handleFilterChange.bind(this));

      // Clear all filters
      jQuery('.clear-all-filters').on('click', this.handleClearAllFilters.bind(this));

      // Layout switcher
      jQuery('.layout-switcher').on('change', 'input[type="radio"]', this.handleLayoutChange.bind(this));

      // Auto-expand if search query exists
      this.checkAutoExpand();
    }
  }, {
    key: "handleSearchToggle",
    value: function handleSearchToggle(e) {
      e.preventDefault();
      e.stopPropagation();
      var $toggleBtn = jQuery(e.currentTarget);
      var $wrapper = $toggleBtn.closest('.splms-search-toggle-wrapper');
      var $panel = $wrapper.find('.splms-search-panel');
      var $form = $panel.find('.splms-search-form');
      var $input = $form.find('.splms-search-input');

      // Toggle expanded state
      var isExpanded = $form.attr('data-expanded') === 'true';
      if (isExpanded) {
        this.collapseSearch($form, $toggleBtn, $panel);
      } else {
        this.expandSearch($form, $toggleBtn, $panel, $input);
      }
    }
  }, {
    key: "expandSearch",
    value: function expandSearch($form, $toggleBtn, $panel, $input) {
      $form.attr('data-expanded', 'true');
      $toggleBtn.attr('aria-expanded', 'true');
      $panel.attr('aria-hidden', 'false');
      $form.addClass('is-expanded');
      $panel.addClass('is-expanded');

      // Focus input after animation
      setTimeout(function () {
        $input.focus();
        $input.select(); // Select existing text if any
      }, 150);
    }
  }, {
    key: "collapseSearch",
    value: function collapseSearch($form, $toggleBtn, $panel) {
      $form.attr('data-expanded', 'false');
      $toggleBtn.attr('aria-expanded', 'false');
      $panel.attr('aria-hidden', 'true');
      $form.removeClass('is-expanded');
      $panel.removeClass('is-expanded');

      // Return focus to toggle button
      $toggleBtn.focus();
    }
  }, {
    key: "handleSearchClose",
    value: function handleSearchClose(e) {
      e.preventDefault();
      e.stopPropagation();
      var $closeBtn = jQuery(e.currentTarget);
      var $form = $closeBtn.closest('.splms-search-form');
      var $wrapper = $form.closest('.splms-search-toggle-wrapper');
      var $toggleBtn = $wrapper.find('.splms-search-toggle');
      var $panel = $wrapper.find('.splms-search-panel');
      this.collapseSearch($form, $toggleBtn, $panel);
    }
  }, {
    key: "handleKeyboard",
    value: function handleKeyboard(e) {
      var _this = this;
      // ESC key to close search
      if (e.key === 'Escape' || e.keyCode === 27) {
        var $expandedForms = jQuery('.splms-search-form[data-expanded="true"]');
        if ($expandedForms.length > 0) {
          e.preventDefault();
          e.stopPropagation();
          $expandedForms.each(function (index, form) {
            var $form = jQuery(form);
            var $wrapper = $form.closest('.splms-search-toggle-wrapper');
            var $toggleBtn = $wrapper.find('.splms-search-toggle');
            var $panel = $wrapper.find('.splms-search-panel');
            _this.collapseSearch($form, $toggleBtn, $panel);
          });
        }
      }
    }
  }, {
    key: "handleClickOutsideSearch",
    value: function handleClickOutsideSearch(e) {
      var _this2 = this;
      var $target = jQuery(e.target);
      var $wrapper = $target.closest('.splms-search-toggle-wrapper');

      // Don't close if clicking inside the search wrapper
      if ($wrapper.length > 0) {
        return;
      }

      // Close all expanded search forms
      jQuery('.splms-search-form[data-expanded="true"]').each(function (index, form) {
        var $form = jQuery(form);
        var $wrapper = $form.closest('.splms-search-toggle-wrapper');
        var $toggleBtn = $wrapper.find('.splms-search-toggle');
        var $panel = $wrapper.find('.splms-search-panel');

        // Only close if not submitting
        if (!$target.closest('.splms-search-submit').length) {
          _this2.collapseSearch($form, $toggleBtn, $panel);
        }
      });
    }
  }, {
    key: "checkAutoExpand",
    value: function checkAutoExpand() {
      var _this3 = this;
      // Auto-expand if search query exists
      jQuery('.splms-search-form').each(function (index, form) {
        var $form = jQuery(form);
        var $input = $form.find('.splms-search-input');
        if ($input.val().trim()) {
          var $wrapper = $form.closest('.splms-search-toggle-wrapper');
          var $toggleBtn = $wrapper.find('.splms-search-toggle');
          var $panel = $wrapper.find('.splms-search-panel');
          _this3.expandSearch($form, $toggleBtn, $panel, $input);
        }
      });
    }
  }, {
    key: "handleSearchInput",
    value: function handleSearchInput(e) {
      var $input = jQuery(e.currentTarget);
      var query = $input.val().trim();

      // Toggle clear button (if implemented)
      // this.toggleClearButton($input, query);
    }
  }, {
    key: "handleSearchFocus",
    value: function handleSearchFocus(e) {
      jQuery(e.currentTarget).closest('.splms-search-input-wrapper').addClass('focused');
    }
  }, {
    key: "handleSearchBlur",
    value: function handleSearchBlur(e) {
      jQuery(e.currentTarget).closest('.splms-search-input-wrapper').removeClass('focused');
    }
  }, {
    key: "handleSearchClear",
    value: function handleSearchClear(e) {
      var $clearBtn = jQuery(e.currentTarget);
      var $input = $clearBtn.siblings('.splms-search-input');
      var searchForm = $input.closest('.splms-search-form');
      $input.val('').focus();

      // Auto-submit to show all courses
      setTimeout(function () {
        searchForm.submit();
      }, 100);
    }
  }, {
    key: "handleFilterChange",
    value: function handleFilterChange(e) {
      var $form = jQuery(e.currentTarget).closest('form');
      var $content = jQuery('.splms-courses-content');

      // Add loading state
      $content.addClass('loading');

      // Small delay for better UX
      setTimeout(function () {
        $form.submit();
      }, 300);
    }
  }, {
    key: "handleClearAllFilters",
    value: function handleClearAllFilters(e) {
      e.preventDefault();
      var $form = jQuery('.sidebar-filters-form');
      $form.find('input[type="checkbox"], input[type="radio"]').prop('checked', false);
      $form.submit();
    }
  }, {
    key: "initLayoutSwitcher",
    value: function initLayoutSwitcher() {
      var layoutSwitcher = jQuery('.layout-switcher');
      var coursesGrid = jQuery('.splms-courses-grid');
      if (layoutSwitcher.length && coursesGrid.length) {
        // Set initial layout from saved preference
        var savedLayout = localStorage.getItem('splms_layout');
        if (savedLayout) {
          layoutSwitcher.find("input[value=\"".concat(savedLayout, "\"]")).prop('checked', true);
          coursesGrid.attr('data-layout', savedLayout);
        }
      }
    }
  }, {
    key: "handleLayoutChange",
    value: function handleLayoutChange(e) {
      var layout = jQuery(e.currentTarget).val();
      var coursesGrid = jQuery('.splms-courses-grid');
      coursesGrid.attr('data-layout', layout);

      // Save preference to localStorage
      localStorage.setItem('splms_layout', layout);
    }
  }, {
    key: "loadSavedLayout",
    value: function loadSavedLayout() {
      var savedLayout = localStorage.getItem('splms_layout');
      if (savedLayout) {
        var layoutSwitcher = jQuery('.layout-switcher');
        var coursesGrid = jQuery('.splms-courses-grid');
        layoutSwitcher.find("input[value=\"".concat(savedLayout, "\"]")).prop('checked', true);
        coursesGrid.attr('data-layout', savedLayout);
      }
    }
  }]);
}();

/***/ },

/***/ "./src/js/react-core/utility/url.js"
/*!******************************************!*\
  !*** ./src/js/react-core/utility/url.js ***!
  \******************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
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
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
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
// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
/*!**********************************!*\
  !*** ./src/js/frontend/index.js ***!
  \**********************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _core_helper__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./core/helper */ "./src/js/frontend/core/helper.js");
/* harmony import */ var _core_ajax__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./core/ajax */ "./src/js/frontend/core/ajax.js");
/* harmony import */ var _core_base_modal__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./core/base-modal */ "./src/js/frontend/core/base-modal.js");
/* harmony import */ var _core_user__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./core/user */ "./src/js/frontend/core/user.js");
/* harmony import */ var _modules_courses_course_actions__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./modules/courses/course-actions */ "./src/js/frontend/modules/courses/course-actions.js");
/* harmony import */ var _modules_courses_course__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./modules/courses/course */ "./src/js/frontend/modules/courses/course.js");
/* harmony import */ var _modules_search_search_filter__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./modules/search/search-filter */ "./src/js/frontend/modules/search/search-filter.js");
/* harmony import */ var _components_ui_enhancements__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./components/ui-enhancements */ "./src/js/frontend/components/ui-enhancements.js");
/* harmony import */ var _components_avatar_upload__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./components/avatar-upload */ "./src/js/frontend/components/avatar-upload.js");
/* harmony import */ var _modules_curriculum_curriculum__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./modules/curriculum/curriculum */ "./src/js/frontend/modules/curriculum/curriculum.js");
/* harmony import */ var _modules_curriculum_curriculum__WEBPACK_IMPORTED_MODULE_9___default = /*#__PURE__*/__webpack_require__.n(_modules_curriculum_curriculum__WEBPACK_IMPORTED_MODULE_9__);
/* harmony import */ var _modules_quizzes_questions__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./modules/quizzes/questions */ "./src/js/frontend/modules/quizzes/questions.js");
/* harmony import */ var _modules_quizzes_quiz__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./modules/quizzes/quiz */ "./src/js/frontend/modules/quizzes/quiz.js");
/* harmony import */ var _modules_question_matching__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ./modules/question-matching */ "./src/js/frontend/modules/question-matching.js");
/* harmony import */ var _modules_question_matching__WEBPACK_IMPORTED_MODULE_12___default = /*#__PURE__*/__webpack_require__.n(_modules_question_matching__WEBPACK_IMPORTED_MODULE_12__);
/* harmony import */ var _modules_question_ordering__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! ./modules/question-ordering */ "./src/js/frontend/modules/question-ordering.js");
/* harmony import */ var _modules_question_ordering__WEBPACK_IMPORTED_MODULE_13___default = /*#__PURE__*/__webpack_require__.n(_modules_question_ordering__WEBPACK_IMPORTED_MODULE_13__);
/* harmony import */ var _modules_auth_auth__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! ./modules/auth/auth */ "./src/js/frontend/modules/auth/auth.js");
/* harmony import */ var _modules_auth_auth__WEBPACK_IMPORTED_MODULE_14___default = /*#__PURE__*/__webpack_require__.n(_modules_auth_auth__WEBPACK_IMPORTED_MODULE_14__);
/* harmony import */ var _modules_auth_signup_validation__WEBPACK_IMPORTED_MODULE_15__ = __webpack_require__(/*! ./modules/auth/signup.validation */ "./src/js/frontend/modules/auth/signup.validation.js");
/* harmony import */ var _components_notifications__WEBPACK_IMPORTED_MODULE_16__ = __webpack_require__(/*! ./components/notifications */ "./src/js/frontend/components/notifications.js");
/* harmony import */ var _api_notifications__WEBPACK_IMPORTED_MODULE_17__ = __webpack_require__(/*! ./api/notifications */ "./src/js/frontend/api/notifications.js");
/* harmony import */ var _api_notifications__WEBPACK_IMPORTED_MODULE_17___default = /*#__PURE__*/__webpack_require__.n(_api_notifications__WEBPACK_IMPORTED_MODULE_17__);
/* harmony import */ var _components_header_interactions__WEBPACK_IMPORTED_MODULE_18__ = __webpack_require__(/*! ./components/header-interactions */ "./src/js/frontend/components/header-interactions.js");
/* harmony import */ var _components_header_interactions__WEBPACK_IMPORTED_MODULE_18___default = /*#__PURE__*/__webpack_require__.n(_components_header_interactions__WEBPACK_IMPORTED_MODULE_18__);









//import './templates/instructor-template';







 // Unified notification system
 // REST API helper for notifications (available globally as SPLMSNotificationsAPI)
 // Mobile menu and user dropdown only
// Removed: notifications-page.js (functionality merged into notifications.js)
// Removed: nav-menu-profile.js (notification functionality moved to notifications.js)

var SPLMSCore = {};
SPLMSCore.helper = new _core_helper__WEBPACK_IMPORTED_MODULE_0__.SPLMSHelper();
SPLMSCore.frontendAjax = new _core_ajax__WEBPACK_IMPORTED_MODULE_1__.SPLMSFrontendAjax();
SPLMSCore.user = new _core_user__WEBPACK_IMPORTED_MODULE_3__.SPLMSUser();
SPLMSCore.BaseModal = _core_base_modal__WEBPACK_IMPORTED_MODULE_2__.SPLMSBaseModal;
jQuery(document).ready(function () {
  'use strict';

  // Initialize all frontend modules
  SPLMSCore.courseActions = new _modules_courses_course_actions__WEBPACK_IMPORTED_MODULE_4__.SPLMSCourseActions();

  // Initialize course filters only on archive pages
  SPLMSCore.courseFilters = new _modules_courses_course__WEBPACK_IMPORTED_MODULE_5__.SPLMSCourseFilters();
  SPLMSCore.searchFilter = new _modules_search_search_filter__WEBPACK_IMPORTED_MODULE_6__.SPLMSSearchFilter();
  SPLMSCore.uiEnhancements = new _components_ui_enhancements__WEBPACK_IMPORTED_MODULE_7__.SPLMSUIEnhancements();
  SPLMSCore.avatarUpload = new _components_avatar_upload__WEBPACK_IMPORTED_MODULE_8__.SPLMSAvatarUpload();

  // Initialize certificate display
  // Re-initialize on AJAX content load
  jQuery(document).on('splms_content_loaded', function () {
    // Re-initialize modules that need to bind to new content
    if (SPLMSCore.courseActions) {
      SPLMSCore.courseActions.init();
    }
    if (SPLMSCore.courseFilters) {
      SPLMSCore.courseFilters.reinitialize();
    }
    if (SPLMSCore.searchFilter) {
      SPLMSCore.searchFilter.init();
    }
  });
});

// Make SPLMSCore globally available
window.SPLMSCore = SPLMSCore;
})();

/******/ })()
;
//# sourceMappingURL=frontend.js.map