/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/js/admin/core/ajax.js"
/*!***********************************!*\
  !*** ./src/js/admin/core/ajax.js ***!
  \***********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SPLMSAdminAjax: () => (/* binding */ SPLMSAdminAjax)
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
 * SkillPulse LMS Admin AJAX Handler
 * Handles AJAX requests for admin functionality
 */
var SPLMSAdminAjax = /*#__PURE__*/function () {
  function SPLMSAdminAjax() {
    var _SPLMSCore_Data;
    _classCallCheck(this, SPLMSAdminAjax);
    this.ajaxUrl = ajaxurl || '/wp-admin/admin-ajax.php';
    this.nonce = ((_SPLMSCore_Data = SPLMSCore_Data) === null || _SPLMSCore_Data === void 0 ? void 0 : _SPLMSCore_Data.nonce) || '';
  }

  /**
      * Make AJAX request
      * @param {Object} options - AJAX options
      * @returns {Promise}
      */
  return _createClass(SPLMSAdminAjax, [{
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
        * Submit a form by ID using AJAX with TinyMCE support
        * @param {string} formId - The ID of the form to submit
        * @returns {Promise}
        */
  }, {
    key: "submitFormById",
    value: function submitFormById(formId) {
      var formData = new FormData(jQuery("#".concat(formId))[0]);

      // Find the textarea connected to TinyMCE dynamically within the form
      jQuery("#".concat(formId, " textarea.wp-editor-area")).each(function () {
        var textareaId = jQuery(this).attr('id');
        var editor = tinymce.get(textareaId);
        if (editor) {
          var content = editor.getContent();
          formData.append(textareaId, content);
        } else {
          var _window$console;
          (_window$console = window.console) === null || _window$console === void 0 || _window$console.error('TinyMCE editor not found for textarea ID: ' + textareaId);
        }
      });
      return this.request({
        data: formData,
        processData: false,
        contentType: false
      });
    }

    /**
        * Get signup details for admin review
        * @param {number} signupId - The signup ID
        * @returns {Promise}
        */
  }, {
    key: "getSignupDetails",
    value: function getSignupDetails(signupId) {
      var _SPLMSCore_Data$nonce;
      return this.request({
        type: 'GET',
        data: {
          action: 'splms_get_signup_details',
          signup_id: signupId,
          nonce: ((_SPLMSCore_Data$nonce = SPLMSCore_Data.nonces) === null || _SPLMSCore_Data$nonce === void 0 ? void 0 : _SPLMSCore_Data$nonce.review) || this.nonce
        }
      });
    }

    /**
        * Process signup action (activate/delete/resend)
        * @param {number} signupId - The signup ID  
        * @param {string} action - The action to perform (activate/delete/resend)
        * @returns {Promise}
        */
  }, {
    key: "processSignup",
    value: function processSignup(signupId, action) {
      var _SPLMSCore_Data$nonce2;
      return this.request({
        data: {
          action: 'splms_process_signup',
          signup_id: signupId,
          process_action: action,
          nonce: ((_SPLMSCore_Data$nonce2 = SPLMSCore_Data.nonces) === null || _SPLMSCore_Data$nonce2 === void 0 ? void 0 : _SPLMSCore_Data$nonce2.process) || this.nonce
        }
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
      var _window$console2;
      (_window$console2 = window.console) === null || _window$console2 === void 0 || _window$console2.error('Admin AJAX Error:', {
        status: status,
        error: error,
        response: xhr.responseText
      });

      // Show user-friendly error message
      window.SPLMSCore.helper.toastNotice('Something went wrong. Please try again.', 'error');
    }
  }]);
}();

// Export is already done in class declaration above

/***/ },

/***/ "./src/js/admin/core/base-modal.js"
/*!*****************************************!*\
  !*** ./src/js/admin/core/base-modal.js ***!
  \*****************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SPLMSBaseModal: () => (/* binding */ SPLMSBaseModal)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
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
 * Base Modal Class for Admin - Reusable admin modal system
 * 
 * This is the admin version of SPLMSBaseModal, optimized for WordPress admin interface.
 * It provides the same functionality as the frontend version but with admin-specific styling
 * and integration with WordPress admin UI patterns.
 *
 * @since 1.0.0
 * @abstract
 */
var SPLMSBaseModal = /*#__PURE__*/function () {
  /**
      * Constructor
      * 
      * @param {Object} config Modal configuration
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
      this.initTemplate();
      this.setupGlobalEvents();
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

      // Check if the template exists
      var templateElement = document.getElementById('tmpl-' + this.config.templateId);
      if (!templateElement) {
        throw new Error('Template not found: tmpl-' + this.config.templateId);
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
              modalData = _objectSpread(_objectSpread({}, this.config.defaultData), data);
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
              modalHtml = this.template(modalData);
              this.destroy();
              this.createModal(modalHtml);
              this.setupModalEvents();
              this.show();
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
      this.modalElement = jQuery(html);
      this.modalElement.addClass('splms-admin-modal');
      if (this.config.modalId) {
        this.modalElement.addClass(this.config.modalId);
      }
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
          if (jQuery(e.target).hasClass('modal-overlay') || jQuery(e.target).hasClass('splms-admin-modal')) {
            _this2.close();
          }
        }, this.modalElement);
      }

      // Form submission handler
      this.addEventHandler('submit', function (e) {
        _this2.handleFormSubmit(e);
      }, this.modalElement.find('form'));
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
      jQuery('body').addClass('splms-admin-modal-open');
      this.isOpen = true;
      requestAnimationFrame(function () {
        _this3.modalElement.addClass('splms-modal-visible is-open');
      });
    }

    /**
        * Close the modal
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
              this.modalElement.removeClass('splms-modal-visible');
              setTimeout(function () {
                _this4.hide();
              }, 300);
              _context2.n = 5;
              break;
            case 4:
              _context2.p = 4;
              _t2 = _context2.v;
              (_window$console2 = window.console) === null || _window$console2 === void 0 || _window$console2.error('Error closing modal:', _t2);
              this.hide();
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
      jQuery('body').removeClass('splms-admin-modal-open');
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
      if (!this.eventHandlers.has($element[0])) {
        this.eventHandlers.set($element[0], []);
      }
      this.eventHandlers.get($element[0]).push({
        event: event,
        handler: handler
      });
    }

    /**
        * Show admin notification
        * @protected
        */
  }, {
    key: "showNotification",
    value: function showNotification(message) {
      var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'info';
      // Use WordPress admin notices style
      var noticeClass = type === 'error' ? 'notice-error' : type === 'success' ? 'notice-success' : type === 'warning' ? 'notice-warning' : 'notice-info';
      var notification = jQuery("\n            <div class=\"notice ".concat(noticeClass, " is-dismissible splms-admin-notification\">\n                <p>").concat(message, "</p>\n                <button type=\"button\" class=\"notice-dismiss\">\n                    <span class=\"screen-reader-text\">Dismiss this notice.</span>\n                </button>\n            </div>\n        "));

      // Position at top of admin content
      var adminNotices = jQuery('.wp-header-end');
      if (adminNotices.length) {
        adminNotices.after(notification);
      } else {
        jQuery('#wpbody-content').prepend(notification);
      }

      // Auto-hide after 5 seconds
      setTimeout(function () {
        notification.fadeOut(300, function () {
          jQuery(this).remove();
        });
      }, 5000);

      // Manual dismiss handler
      notification.find('.notice-dismiss').on('click', function () {
        notification.fadeOut(300, function () {
          jQuery(this).remove();
        });
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
      this.showNotification(message, 'error');
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
      var submitBtns = this.modalElement.find('button[type="submit"], .button-primary, .button-secondary');
      if (isLoading) {
        submitBtns.each(function () {
          var $btn = jQuery(this);
          $btn.prop('disabled', true);
          if (!$btn.data('original-text')) {
            $btn.data('original-text', $btn.text());
          }
          $btn.text(message);
        });
      } else {
        submitBtns.each(function () {
          var $btn = jQuery(this);
          $btn.prop('disabled', false);
          var originalText = $btn.data('original-text');
          if (originalText) {
            $btn.text(originalText);
          }
        });
      }
    }

    /**
        * Destroy the modal and cleanup
        */
  }, {
    key: "destroy",
    value: function destroy() {
      this.eventHandlers.forEach(function (handlers, element) {
        var $element = jQuery(element);
        handlers.forEach(function (_ref) {
          var event = _ref.event,
            handler = _ref.handler;
          $element.off(event, handler);
        });
      });
      this.eventHandlers.clear();
      if (this.modalElement) {
        this.modalElement.remove();
        this.modalElement = null;
      }
      this.isOpen = false;
      jQuery('body').removeClass('splms-admin-modal-open');
      if (typeof this.onDestroy === 'function') {
        this.onDestroy();
      }
    }

    // ==========================================
    // ABSTRACT METHODS - Override in child classes
    // ==========================================
  }, {
    key: "onInit",
    value: function onInit() {
      // Override in child classes
    }
  }, {
    key: "onOpen",
    value: function () {
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
  }, {
    key: "onClose",
    value: function () {
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
  }, {
    key: "onDestroy",
    value: function onDestroy() {
      // Override in child classes
    }
  }, {
    key: "onFormSubmit",
    value: function () {
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
  }, {
    key: "setupEvents",
    value: function setupEvents() {
      // Override in child classes
    }
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

/***/ "./src/js/admin/core/helper.js"
/*!*************************************!*\
  !*** ./src/js/admin/core/helper.js ***!
  \*************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SPLMSHelper: () => (/* binding */ SPLMSHelper)
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
var SPLMSHelper = /*#__PURE__*/function () {
  function SPLMSHelper() {
    _classCallCheck(this, SPLMSHelper);
    this.init();
  }
  return _createClass(SPLMSHelper, [{
    key: "init",
    value: function init() {
      this.accordion();
    }
  }, {
    key: "initNotice",
    value: function initNotice(id, type, message) {
      var actions = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : [];
      var notice = {
        id: id,
        // prevent duplicates
        isDismissible: true,
        type: 'snackbar',
        actions: actions
      };

      // Handle the response from the server
      wp.data.dispatch('core/notices').createNotice(type,
      // Can be one of: success, info, warning, error
      message,
      // message
      notice);
    }
  }, {
    key: "toastNotice",
    value: function toastNotice(message) {
      var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'success';
      var autoClose = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;
      var noticeActions = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : [];
      var noticeActionsCallback = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : null;
      if (!jQuery('.splms-toast-parent').length) {
        jQuery('body').append('<div class="splms-toast-parent splms-toast-right"></div>');
      }
      var alert = type === 'success' ? 'success' : type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'primary';
      var hasMessage = message !== undefined && message !== null && message.trim() !== '';
      var content = jQuery("\n        <div class=\"splms-notification splms-is-".concat(alert, "\">\n            <div class=\"splms-notification-content\">\n            <p class=\"").concat(!hasMessage ? 'splms-d-none' : '', "\">").concat(message, "</p>\n            ").concat(noticeActions.length > 0 ? '<div class="splms-notification-actions"></div>' : '', "\n            </div>\n            <button class=\"splms-notification-close\">\n                <span class=\"dashicons dashicons-dismiss\"></span>\n            </button>\n        </div>\n    "));
      content.find('.splms-notification-close').click(function () {
        content.remove();
      });
      if (noticeActions.length > 0) {
        noticeActions.forEach(function (action) {
          var actionButton = jQuery('<button></button>');
          actionButton.text(action.label);
          actionButton.addClass('splms-notification-action');
          actionButton.on('click', function () {
            if (noticeActionsCallback) {
              noticeActionsCallback(action);
            }
          });
          content.find('.splms-notification-actions').append(actionButton);
        });
      }
      jQuery('.splms-toast-parent').append(content);
      if (autoClose) {
        setTimeout(function () {
          if (content) {
            content.fadeOut('fast', function () {
              jQuery(this).remove();
            });
          }
        }, 5000);
      }
    }
  }, {
    key: "accordion",
    value: function accordion() {
      jQuery(document).on('click', '.splms-accordion', function (e) {
        e.preventDefault();
        jQuery(this).next().slideToggle();
        jQuery(this).toggleClass('active');
        jQuery(this).find('span.splms-accordion-arrow').toggleClass('dashicons-arrow-down-alt2').toggleClass('dashicons-arrow-up-alt2');
      });
    }

    /**
        * Modal default events
        *
        * @param {string} modalMode Modal mode
        */
  }, {
    key: "modalDefaultEvents",
    value: function modalDefaultEvents(modalMode) {
      if ('get_lesson_modal' === modalMode) {
        this.selectFeaturedImage();
      }
      if ('get_quiz_modal' === modalMode) {
        if ('step-2' === jQuery('.splms-form-step').attr('id')) {
          /**
                       * Sortable list for questions.
                       */
          SPLMSCore.helper.sortableList('.splms-questions-list', '.splms-questions-list__question', function (questions_order) {
            if (jQuery('.splms-form-step').find('#questions_order').length === 0) {
              jQuery('.splms-form-step').append('<input type="hidden" name="questions_order" id="questions_order" value="">');
            }
            jQuery('#questions_order').val(questions_order);
          }, 'toArray');
        }
      }
      if ('get_question_modal' === modalMode) {
        /**
                  * Sortable list for lesson/quiz and other.
                  */
        SPLMSCore.helper.sortableList('.splms-modal__window__content__body__form__answer_wrapper__items', '.splms-modal__window__content__body__form__answer_wrapper__item');
      }
    }
  }, {
    key: "selectFeaturedImage",
    value: function selectFeaturedImage() {
      jQuery('.splms-upload-image button').on('click', function (e) {
        e.preventDefault();
        var frame = wp.media({
          title: 'Select or Upload Media',
          button: {
            text: 'Use this media'
          },
          multiple: false
        });
        frame.on('select', function () {
          var attachment = frame.state().get('selection').first().toJSON();
          var imageWrap = jQuery('.splms-upload-empty-image-wrap');
          imageWrap.addClass('has-image');
          imageWrap.css('background-image', 'url(' + attachment.url + ')');
          imageWrap.find('.inner-image').hide();
          imageWrap.find('.inner-p').hide();
          imageWrap.closest('.splms-upload-image').find('.splms-btn--label').text('Change Image');
          imageWrap.find('#lesson_feature_image').val(attachment.id);
          if (imageWrap.find('.splms-remove-image').length === 0) {
            imageWrap.append('<span class="dashicons dashicons-dismiss splms-remove-image"></span>');
          }
        });
        frame.open();
      });
      jQuery(document).on('click', '.splms-remove-image', function () {
        var imageWrap = jQuery('.splms-upload-empty-image-wrap');
        imageWrap.removeClass('has-image');
        imageWrap.css('background-image', 'none');
        imageWrap.find('.inner-image').show();
        imageWrap.find('.inner-p').show();
        imageWrap.closest('.splms-upload-image').find('.splms-btn--label').text('Upload Image');
        imageWrap.find('#lesson_feature_image').val('');
        jQuery(this).remove();
      });
    }

    /**
        * Sortable list
        *
        * @param {string} listId Selector ID or Class
        * @param {string} listItems Item selector ID or Class
        * @param {function} updateCallback  Callback function
        * @param {string} sortableOrder Order of sortable
        * @param {object} sortableOptions Options for sortable
        */
  }, {
    key: "sortableList",
    value: function sortableList(listId, listItems) {
      var updateCallback = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
      var sortableOrder = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 'serialize';
      var sortableOptions = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : {};
      jQuery(listId).sortable(_objectSpread({
        items: listItems,
        placeholder: 'sortable-placeholder',
        tolerance: 'pointer',
        distance: 1,
        forcePlaceholderSize: true,
        opacity: 0.6,
        helper: 'clone',
        cursor: 'move',
        update: function update(event, ui) {
          if (updateCallback) {
            var item_order = jQuery(this).sortable(sortableOrder, {
              attribute: 'data-order-id'
            });
            updateCallback(item_order, event, ui);
          }
        }
      }, sortableOptions));
    }
  }, {
    key: "initTinyMCE",
    value: function initTinyMCE(selector) {
      if (typeof tinymce !== 'undefined') {
        tinymce.remove(selector); // Remove any existing TinyMCE instances
        // Initialize TinyMCE
        tinymce.init({
          selector: selector,
          menubar: false,
          toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | code' // Define the toolbar buttons
        });
      }
    }
  }]);
}();


/***/ },

/***/ "./src/js/admin/modals/signup-review-modal.js"
/*!****************************************************!*\
  !*** ./src/js/admin/modals/signup-review-modal.js ***!
  \****************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SPLMSSignupReviewModal: () => (/* binding */ SPLMSSignupReviewModal)
/* harmony export */ });
/* harmony import */ var _core_base_modal_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../core/base-modal.js */ "./src/js/admin/core/base-modal.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _callSuper(t, o, e) { return o = _getPrototypeOf(o), _possibleConstructorReturn(t, _isNativeReflectConstruct() ? Reflect.construct(o, e || [], _getPrototypeOf(t).constructor) : o.apply(t, e)); }
function _possibleConstructorReturn(t, e) { if (e && ("object" == _typeof(e) || "function" == typeof e)) return e; if (void 0 !== e) throw new TypeError("Derived constructors may only return object or undefined"); return _assertThisInitialized(t); }
function _assertThisInitialized(e) { if (void 0 === e) throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); return e; }
function _isNativeReflectConstruct() { try { var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); } catch (t) {} return (_isNativeReflectConstruct = function _isNativeReflectConstruct() { return !!t; })(); }
function _getPrototypeOf(t) { return _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function (t) { return t.__proto__ || Object.getPrototypeOf(t); }, _getPrototypeOf(t); }
function _inherits(t, e) { if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function"); t.prototype = Object.create(e && e.prototype, { constructor: { value: t, writable: !0, configurable: !0 } }), Object.defineProperty(t, "prototype", { writable: !1 }), e && _setPrototypeOf(t, e); }
function _setPrototypeOf(t, e) { return _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function (t, e) { return t.__proto__ = e, t; }, _setPrototypeOf(t, e); }
/**
 * Signup Review Modal - Admin Interface
 * 
 * Extends SPLMSBaseModal for reviewing pending signups in the admin area.
 * Provides a comprehensive interface for administrators to activate, delete, or resend
 * activation emails for pending user signups.
 *
 * @since 1.0.0
 */

var SPLMSSignupReviewModal = /*#__PURE__*/function (_SPLMSBaseModal) {
  function SPLMSSignupReviewModal() {
    var _this;
    _classCallCheck(this, SPLMSSignupReviewModal);
    _this = _callSuper(this, SPLMSSignupReviewModal, [{
      templateId: 'splms-admin-signup-review',
      modalId: 'splms-admin-signup-review-modal',
      defaultData: {
        signup_id: 0,
        user_login: '',
        user_email: '',
        display_name: '',
        registered: '',
        activation_key: '',
        meta: {},
        status: 'pending'
      },
      closeOnBackdrop: true,
      closeOnEscape: true
    }]);
    _this.currentSignupData = null;
    _this.isProcessing = false;
    return _this;
  }

  /**
      * Initialize the modal
      */
  _inherits(SPLMSSignupReviewModal, _SPLMSBaseModal);
  return _createClass(SPLMSSignupReviewModal, [{
    key: "onInit",
    value: function onInit() {
      // Any additional initialization can go here
    }

    /**
        * Open modal with signup ID to load signup data
        */
  }, {
    key: "openForSignup",
    value: (function () {
      var _openForSignup = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(signupId) {
        var signupData, _t;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.p = _context.n) {
            case 0:
              _context.p = 0;
              _context.n = 1;
              return this.open({
                loading: true
              });
            case 1:
              _context.n = 2;
              return this.loadSignupData(signupId);
            case 2:
              signupData = _context.v;
              _context.n = 3;
              return this.updateModalContent(signupData);
            case 3:
              _context.n = 5;
              break;
            case 4:
              _context.p = 4;
              _t = _context.v;
              this.handleError(_t);
              this.close();
            case 5:
              return _context.a(2);
          }
        }, _callee, this, [[0, 4]]);
      }));
      function openForSignup(_x) {
        return _openForSignup.apply(this, arguments);
      }
      return openForSignup;
    }()
    /**
        * Load signup data via AJAX
        */
    )
  }, {
    key: "loadSignupData",
    value: (function () {
      var _loadSignupData = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(signupId) {
        var response, _t2;
        return _regenerator().w(function (_context2) {
          while (1) switch (_context2.p = _context2.n) {
            case 0:
              _context2.p = 0;
              _context2.n = 1;
              return SPLMSCore.adminAjax.getSignupDetails(signupId);
            case 1:
              response = _context2.v;
              if (!response.success) {
                _context2.n = 2;
                break;
              }
              return _context2.a(2, response.data);
            case 2:
              throw new Error(response.data || 'Failed to load signup data');
            case 3:
              _context2.n = 5;
              break;
            case 4:
              _context2.p = 4;
              _t2 = _context2.v;
              throw new Error('Network error loading signup');
            case 5:
              return _context2.a(2);
          }
        }, _callee2, null, [[0, 4]]);
      }));
      function loadSignupData(_x2) {
        return _loadSignupData.apply(this, arguments);
      }
      return loadSignupData;
    }()
    /**
        * Update modal content with signup data
        */
    )
  }, {
    key: "updateModalContent",
    value: (function () {
      var _updateModalContent = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee3(data) {
        var formattedData, modalHtml;
        return _regenerator().w(function (_context3) {
          while (1) switch (_context3.n) {
            case 0:
              this.currentSignupData = data;

              // Prepare data with proper formatting
              formattedData = _objectSpread(_objectSpread(_objectSpread({}, this.config.defaultData), data), {}, {
                registered_formatted: this.formatDate(data.registered),
                loading: false
              }); // Regenerate modal with real data
              modalHtml = this.template(formattedData); // Replace modal content
              this.modalElement.find('.modal-content').replaceWith(jQuery(modalHtml).find('.modal-content'));

              // Re-setup events for new content
              this.setupModalEvents();
            case 1:
              return _context3.a(2);
          }
        }, _callee3, this);
      }));
      function updateModalContent(_x3) {
        return _updateModalContent.apply(this, arguments);
      }
      return updateModalContent;
    }()
    /**
        * Setup modal-specific events
        */
    )
  }, {
    key: "setupEvents",
    value: function setupEvents() {
      var _this2 = this;
      if (!this.modalElement) return;

      // Modal activate button
      this.addEventHandler('click', /*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee4() {
        return _regenerator().w(function (_context4) {
          while (1) switch (_context4.n) {
            case 0:
              _context4.n = 1;
              return _this2.processSignupAction('activate');
            case 1:
              return _context4.a(2);
          }
        }, _callee4);
      })), this.modalElement.find('.activate-signup-btn'));

      // Modal delete button
      this.addEventHandler('click', /*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee5() {
        return _regenerator().w(function (_context5) {
          while (1) switch (_context5.n) {
            case 0:
              _context5.n = 1;
              return _this2.processSignupAction('delete');
            case 1:
              return _context5.a(2);
          }
        }, _callee5);
      })), this.modalElement.find('.delete-signup-btn'));

      // Modal resend activation button
      this.addEventHandler('click', /*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee6() {
        return _regenerator().w(function (_context6) {
          while (1) switch (_context6.n) {
            case 0:
              _context6.n = 1;
              return _this2.processSignupAction('resend');
            case 1:
              return _context6.a(2);
          }
        }, _callee6);
      })), this.modalElement.find('.resend-activation-btn'));
    }

    /**
        * Process signup action (activate/delete/resend)
        */
  }, {
    key: "processSignupAction",
    value: (function () {
      var _processSignupAction = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee7(action) {
        var _this3 = this;
        var confirmMessage, result, _t3;
        return _regenerator().w(function (_context7) {
          while (1) switch (_context7.p = _context7.n) {
            case 0:
              if (!this.isProcessing) {
                _context7.n = 1;
                break;
              }
              return _context7.a(2);
            case 1:
              confirmMessage = '';
              if (action === 'activate') {
                confirmMessage = "Are you sure you want to activate the signup for ".concat(this.currentSignupData.user_email, "?");
              } else if (action === 'delete') {
                confirmMessage = "Are you sure you want to delete the signup for ".concat(this.currentSignupData.user_email, "? This cannot be undone.");
              } else if (action === 'resend') {
                confirmMessage = "Are you sure you want to resend the activation email to ".concat(this.currentSignupData.user_email, "?");
              }

              // Use custom confirmation instead of browser confirm()
              _context7.n = 2;
              return this.showCustomConfirm(confirmMessage);
            case 2:
              if (_context7.v) {
                _context7.n = 3;
                break;
              }
              return _context7.a(2);
            case 3:
              this.isProcessing = true;
              this.setLoading(true, 'Processing...');
              _context7.p = 4;
              _context7.n = 5;
              return this.submitSignupAction(action);
            case 5:
              result = _context7.v;
              this.showNotification(result.message || 'Signup processed successfully', 'success');

              // Close modal and refresh page after short delay
              setTimeout(function () {
                _this3.close();
                setTimeout(function () {
                  window.location.reload();
                }, 500);
              }, 1000);
              _context7.n = 7;
              break;
            case 6:
              _context7.p = 6;
              _t3 = _context7.v;
              this.showNotification(_t3.message || 'Error processing signup', 'error');
            case 7:
              _context7.p = 7;
              this.isProcessing = false;
              this.setLoading(false);
              return _context7.f(7);
            case 8:
              return _context7.a(2);
          }
        }, _callee7, this, [[4, 6, 7, 8]]);
      }));
      function processSignupAction(_x4) {
        return _processSignupAction.apply(this, arguments);
      }
      return processSignupAction;
    }()
    /**
        * Show custom confirmation dialog instead of browser confirm()
        */
    )
  }, {
    key: "showCustomConfirm",
    value: function showCustomConfirm(message) {
      return new Promise(function (resolve) {
        // Create confirmation modal HTML
        var confirmModal = jQuery("\n                <div class=\"splms-confirm-modal\" style=\"\n                    position: fixed;\n                    top: 0;\n                    left: 0;\n                    width: 100%;\n                    height: 100%;\n                    background: var(--splms-overlay-bg, rgba(0,0,0,0.5));\n                    z-index: 999999;\n                    display: flex;\n                    align-items: center;\n                    justify-content: center;\n                \">\n                    <div style=\"\n                        background: var(--splms-background, white);\n                        padding: 30px;\n                        border-radius: var(--splms-border-radius-lg, 8px);\n                        max-width: 400px;\n                        text-align: center;\n                        box-shadow: var(--splms-shadow-xl, 0 10px 30px rgba(0,0,0,0.3));\n                    \">\n                        <h3 style=\"margin: 0 0 20px 0; color: var(--splms-text-color, #333);\">Confirm Action</h3>\n                        <p style=\"margin: 0 0 25px 0; color: var(--splms-text-secondary, #666); line-height: 1.5;\">".concat(message, "</p>\n                        <div style=\"display: flex; gap: 15px; justify-content: center;\">\n                            <button class=\"button button-secondary\" style=\"min-width: 80px;\">Cancel</button>\n                            <button class=\"button button-primary\" style=\"min-width: 80px;\">Confirm</button>\n                        </div>\n                    </div>\n                </div>\n            "));

        // Add event listeners
        confirmModal.find('.button-secondary').on('click', function () {
          confirmModal.remove();
          resolve(false);
        });
        confirmModal.find('.button-primary').on('click', function () {
          confirmModal.remove();
          resolve(true);
        });

        // Add to body
        jQuery('body').append(confirmModal);
      });
    }

    /**
        * Validate modal data
        */
  }, {
    key: "validateData",
    value: function validateData(data) {
      if (data.loading) {
        return {
          isValid: true
        }; // Loading state is always valid
      }
      if (!data.signup_id || !data.user_email) {
        return {
          isValid: false,
          message: 'Required signup data is missing'
        };
      }
      return {
        isValid: true
      };
    }

    /**
        * Called when modal is closing
        */
  }, {
    key: "onClose",
    value: (function () {
      var _onClose = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee8() {
        return _regenerator().w(function (_context8) {
          while (1) switch (_context8.n) {
            case 0:
              if (!this.isProcessing) {
                _context8.n = 1;
                break;
              }
              return _context8.a(2, false);
            case 1:
              this.currentSignupData = null;
              return _context8.a(2, true);
          }
        }, _callee8, this);
      }));
      function onClose() {
        return _onClose.apply(this, arguments);
      }
      return onClose;
    }()
    /**
        * Called when modal is destroyed
        */
    )
  }, {
    key: "onDestroy",
    value: function onDestroy() {
      this.currentSignupData = null;
      this.isProcessing = false;
    }

    /**
        * Process quick action without opening modal (called from table quick actions)
        */
  }, {
    key: "processQuickAction",
    value: (function () {
      var _processQuickAction = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee9(signupId, action, userEmail) {
        var confirmMessage, result, _t4;
        return _regenerator().w(function (_context9) {
          while (1) switch (_context9.p = _context9.n) {
            case 0:
              confirmMessage = '';
              if (action === 'activate') {
                confirmMessage = "".concat(SPLMSCore_Data.strings.quick_activate_confirm, " ").concat(userEmail, "?");
              } else if (action === 'delete') {
                confirmMessage = "".concat(SPLMSCore_Data.strings.quick_delete_confirm, " ").concat(userEmail, "?");
              } else if (action === 'resend') {
                confirmMessage = "".concat(SPLMSCore_Data.strings.quick_resend_confirm, " ").concat(userEmail, "?");
              }

              // Use custom confirmation instead of browser confirm()
              _context9.n = 1;
              return this.showCustomConfirm(confirmMessage);
            case 1:
              if (_context9.v) {
                _context9.n = 2;
                break;
              }
              return _context9.a(2);
            case 2:
              _context9.p = 2;
              _context9.n = 3;
              return this.submitSignupAction(action, signupId);
            case 3:
              result = _context9.v;
              this.showNotification(result.message || 'Signup processed successfully', 'success');

              // Refresh page to update table
              setTimeout(function () {
                window.location.reload();
              }, 1000);
              _context9.n = 5;
              break;
            case 4:
              _context9.p = 4;
              _t4 = _context9.v;
              this.showNotification(_t4.message || 'Error processing signup', 'error');
            case 5:
              return _context9.a(2);
          }
        }, _callee9, this, [[2, 4]]);
      }));
      function processQuickAction(_x5, _x6, _x7) {
        return _processQuickAction.apply(this, arguments);
      }
      return processQuickAction;
    }()
    /**
        * Submit signup action with custom signup ID (for quick actions)
        */
    )
  }, {
    key: "submitSignupAction",
    value: (function () {
      var _submitSignupAction = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee0(action) {
        var signupId,
          targetSignupId,
          response,
          _args0 = arguments,
          _t5;
        return _regenerator().w(function (_context0) {
          while (1) switch (_context0.p = _context0.n) {
            case 0:
              signupId = _args0.length > 1 && _args0[1] !== undefined ? _args0[1] : null;
              targetSignupId = signupId || this.currentSignupData.signup_id;
              _context0.p = 1;
              _context0.n = 2;
              return SPLMSCore.adminAjax.processSignup(targetSignupId, action);
            case 2:
              response = _context0.v;
              if (!response.success) {
                _context0.n = 3;
                break;
              }
              return _context0.a(2, response.data);
            case 3:
              throw new Error(response.data || 'Failed to process signup');
            case 4:
              _context0.n = 6;
              break;
            case 5:
              _context0.p = 5;
              _t5 = _context0.v;
              throw new Error('Network error processing signup');
            case 6:
              return _context0.a(2);
          }
        }, _callee0, this, [[1, 5]]);
      }));
      function submitSignupAction(_x8) {
        return _submitSignupAction.apply(this, arguments);
      }
      return submitSignupAction;
    }()
    /**
        * Format date for display
        */
    )
  }, {
    key: "formatDate",
    value: function formatDate(dateString) {
      if (!dateString) return '';
      try {
        var date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
      } catch (error) {
        return dateString;
      }
    }

    /**
        * Static method to initialize signup admin page functionality
        * This replaces any inline JavaScript in the PHP file
        */
  }], [{
    key: "initializeSignupAdminPage",
    value: function initializeSignupAdminPage() {
      // Check if we're on the signup admin page
      if (typeof SPLMSCore_Data === 'undefined' || !SPLMSCore_Data.is_signup_admin_page) {
        return; // Not on the signup admin page
      }
      var reviewModal = null;

      // Helper function to get or create modal instance
      var getModalInstance = function getModalInstance() {
        if (!reviewModal) {
          reviewModal = new SPLMSSignupReviewModal();
        }
        return reviewModal;
      };

      // Event listeners
      jQuery(document).ready(function ($) {
        // Review signup links - opens the detailed modal
        $('.review-signup').on('click', function (e) {
          e.preventDefault();
          var signupId = $(this).data('signup-id');
          getModalInstance().openForSignup(signupId);
        });

        // Quick actions are handled by the modal system for consistency
        $('.quick-activate, .quick-delete, .quick-resend').on('click', function (e) {
          e.preventDefault();
          var signupId = $(this).data('signup-id');
          var userEmail = $(this).data('user-email');
          var action = '';
          if ($(this).hasClass('quick-activate')) {
            action = 'activate';
          } else if ($(this).hasClass('quick-delete')) {
            action = 'delete';
          } else if ($(this).hasClass('quick-resend')) {
            action = 'resend';
          }

          // Process quick action through modal system
          getModalInstance().processQuickAction(signupId, action, userEmail);
        });
      });
    }
  }]);
}(_core_base_modal_js__WEBPACK_IMPORTED_MODULE_0__.SPLMSBaseModal);

// Auto-initialization removed - now called from admin/index.js

/***/ },

/***/ "./src/js/admin/modules/courses/course-editor.js"
/*!*******************************************************!*\
  !*** ./src/js/admin/modules/courses/course-editor.js ***!
  \*******************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SPLMSCourseEditor: () => (/* binding */ SPLMSCourseEditor)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
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
var SPLMSCourseEditor = /*#__PURE__*/function () {
  function SPLMSCourseEditor() {
    _classCallCheck(this, SPLMSCourseEditor);
    this.init();
  }
  return _createClass(SPLMSCourseEditor, [{
    key: "init",
    value: function init() {}
  }, {
    key: "setHeaderHeight",
    value: function setHeaderHeight() {
      var h = jQuery("#splms-course-header").outerHeight(true) || 0;
      jQuery(".edit-post-layout").css("padding-top", h + "px");
    }
  }, {
    key: "maybeHideHeader",
    value: function maybeHideHeader(sidebarIsOpen, isTabletOrSmaller) {
      if (sidebarIsOpen && isTabletOrSmaller) {
        jQuery("#splms-course-header-wrapper").hide();
      } else {
        jQuery("#splms-course-header-wrapper").show();
      }
    }
  }, {
    key: "maybeHideBlockInserter",
    value: function maybeHideBlockInserter(tabIndex) {
      var blockInserterBtn = jQuery('.edit-post-header-toolbar__inserter-toggle');
      if (tabIndex === 'course') {
        blockInserterBtn.show();
      } else {
        blockInserterBtn.hide();
      }
    }
  }, {
    key: "updateFullscreenLogoLink",
    value: function updateFullscreenLogoLink() {
      jQuery(".edit-post-fullscreen-mode-close").attr("href", SPLMSCore_Data.coursesUrl);
    }
  }, {
    key: "addCSS",
    value: function addCSS(selector, rule) {
      jQuery(selector).css(rule);
    }
  }, {
    key: "toggleVisualEditor",
    value: function toggleVisualEditor() {
      var visibility = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "hide";
      if (visibility === "show") {
        jQuery(".edit-post-visual-editor, .edit-post-text-editor").show();
      } else {
        jQuery(".edit-post-visual-editor, .edit-post-text-editor").hide();
      }
    }
  }, {
    key: "toggleMetaBoxes",
    value: function toggleMetaBoxes(action) {
      var elements = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
      var metaboxes = wp.data.select("core/edit-post").getAllMetaBoxes();
      console.log("metaboxes", metaboxes);
      Object.entries(metaboxes).map(function (_ref) {
        var _ref2 = _slicedToArray(_ref, 2),
          key = _ref2[0],
          metabox = _ref2[1];
        var metaboxEl = jQuery("#" + metabox.id);
        if (action === "hide") {
          if (elements === "" || elements.includes(metabox.id)) {
            metaboxEl.hide();
            return false;
          }
          if (!metaboxEl.hasClass("is-hidden")) metaboxEl.show();
        } else {
          if (elements === "" || elements.includes(metabox.id)) {
            if (metaboxEl.hasClass('closed')) {
              metaboxEl.removeClass('closed');
            }
            metaboxEl.show();
            return false;
          }
          metaboxEl.hide();
        }
      });
    }
  }, {
    key: "togglePanels",
    value: function togglePanels(action) {
      var element = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "";
      jQuery(".components-panel .components-panel__body").each(function () {
        if (action === "hide") {
          if (element === "" || this.className.includes(element)) {
            jQuery(this).hide();
          } else {
            jQuery(this).show();
          }
        } else {
          if (element === "" || this.className.includes(element)) {
            jQuery(this).show();
            jQuery(this).addClass("is-opened");
          } else {
            jQuery(this).hide();
          }
        }
      });
    }
  }, {
    key: "showDefaultView",
    value: function showDefaultView() {
      this.toggleVisualEditor("show");
      this.toggleMetaBoxes("hide", ["splms-course-curriculum", "splms-course-settings"]);
    }
  }, {
    key: "showCurriculumView",
    value: function showCurriculumView() {
      this.toggleVisualEditor();
      console.log(111);
      this.toggleMetaBoxes("show", ["splms-course-curriculum"]); // Show selected
    }
  }, {
    key: "showSettingsView",
    value: function showSettingsView() {
      this.toggleVisualEditor();
      this.toggleMetaBoxes("show", ["splms-course-settings"]); // Show selected
      this.togglePanels("hide", "");
    }
  }]);
}();


/***/ },

/***/ "./src/js/admin/modules/courses/lesson-editor.js"
/*!*******************************************************!*\
  !*** ./src/js/admin/modules/courses/lesson-editor.js ***!
  \*******************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SPLMSLessonEditor: () => (/* binding */ SPLMSLessonEditor)
/* harmony export */ });
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
var SPLMSLessonEditor = /*#__PURE__*/function () {
  function SPLMSLessonEditor() {
    _classCallCheck(this, SPLMSLessonEditor);
    this.init();
  }

  /**
   * Initialize lesson editor
   */
  return _createClass(SPLMSLessonEditor, [{
    key: "init",
    value: function init() {
      // Initialize lesson editor functionality
    }

    /**
     * Set header height
     */
  }, {
    key: "setHeaderHeight",
    value: function setHeaderHeight() {
      var h = jQuery("#splms-lesson-header").outerHeight(true) || 0;
      jQuery(".edit-post-layout").css("padding-top", h + "px");
    }
  }, {
    key: "maybeHideHeader",
    value: function maybeHideHeader(sidebarIsOpen, isTabletOrSmaller) {
      if (sidebarIsOpen && isTabletOrSmaller) {
        jQuery("#splms-lesson-header-wrapper").hide();
      } else {
        jQuery("#splms-lesson-header-wrapper").show();
      }
    }
  }, {
    key: "maybeHideBlockInserter",
    value: function maybeHideBlockInserter(tabIndex) {
      var blockInserterBtn = jQuery('.edit-post-header-toolbar__inserter-toggle');
      if (tabIndex === 'lesson') {
        blockInserterBtn.show();
      } else {
        blockInserterBtn.hide();
      }
    }

    /**
     * Add CSS to element
     */
  }, {
    key: "addCSS",
    value: function addCSS(selector, rule) {
      if (_typeof(rule) === 'object') {
        jQuery(selector).css(rule);
      }
    }

    /**
     * Toggle visual editor visibility
     */
  }, {
    key: "toggleVisualEditor",
    value: function toggleVisualEditor() {
      var visibility = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "hide";
      if (visibility === "show") {
        jQuery(".edit-post-visual-editor, .edit-post-text-editor").show();
      } else {
        jQuery(".edit-post-visual-editor, .edit-post-text-editor").hide();
      }
    }

    /**
     * Toggle metaboxes visibility
     */
  }, {
    key: "toggleMetaBoxes",
    value: function toggleMetaBoxes(action) {
      var elements = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
      var metaboxes = wp.data.select("core/edit-post").getAllMetaBoxes();
      Object.entries(metaboxes).map(function (_ref) {
        var _ref2 = _slicedToArray(_ref, 2),
          key = _ref2[0],
          metabox = _ref2[1];
        var metaboxEl = jQuery("#" + metabox.id);
        if (action === "hide") {
          if (elements === "" || elements.includes(metabox.id)) {
            metaboxEl.hide();
            return false;
          }
          if (!metaboxEl.hasClass("is-hidden")) metaboxEl.show();
        } else {
          if (elements === "" || elements.includes(metabox.id)) {
            if (metaboxEl.hasClass('closed')) {
              metaboxEl.removeClass('closed');
            }
            metaboxEl.show();
            return false;
          }
          metaboxEl.hide();
        }
      });
    }

    /**
     * Toggle panels visibility
     */
  }, {
    key: "togglePanels",
    value: function togglePanels(action) {
      var element = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "";
      jQuery(".components-panel .components-panel__body").each(function () {
        if (action === "hide") {
          if (element === "" || this.className.includes(element)) {
            jQuery(this).hide();
          } else {
            jQuery(this).show();
          }
        } else {
          if (element === "" || this.className.includes(element)) {
            jQuery(this).show();
            jQuery(this).addClass("is-opened");
          } else {
            jQuery(this).hide();
          }
        }
      });
    }

    /**
     * Show default view (lesson page editing)
     */
  }, {
    key: "showDefaultView",
    value: function showDefaultView() {
      this.toggleVisualEditor("show");
      this.toggleMetaBoxes("hide", ["splms-lesson-settings"]);
    }

    /**
     * Show settings view
     */
  }, {
    key: "showSettingsView",
    value: function showSettingsView() {
      this.toggleVisualEditor();
      this.toggleMetaBoxes("show", ["splms-lesson-settings"]);
      this.togglePanels("hide", "");
    }
  }]);
}();


/***/ },

/***/ "./src/js/admin/modules/courses/quiz-editor.js"
/*!*****************************************************!*\
  !*** ./src/js/admin/modules/courses/quiz-editor.js ***!
  \*****************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SPLMSQuizEditor: () => (/* binding */ SPLMSQuizEditor)
/* harmony export */ });
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
var SPLMSQuizEditor = /*#__PURE__*/function () {
  function SPLMSQuizEditor() {
    _classCallCheck(this, SPLMSQuizEditor);
    this.init();
  }

  /**
   * Initialize quiz editor
   */
  return _createClass(SPLMSQuizEditor, [{
    key: "init",
    value: function init() {
      // Initialize quiz editor functionality
    }

    /**
     * Set header height
     */
  }, {
    key: "setHeaderHeight",
    value: function setHeaderHeight() {
      var h = jQuery("#splms-quiz-header").outerHeight(true) || 0;
      jQuery(".edit-post-layout").css("padding-top", h + "px");
    }
  }, {
    key: "maybeHideHeader",
    value: function maybeHideHeader(sidebarIsOpen, isTabletOrSmaller) {
      if (sidebarIsOpen && isTabletOrSmaller) {
        jQuery("#splms-quiz-header-wrapper").hide();
      } else {
        jQuery("#splms-quiz-header-wrapper").show();
      }
    }
  }, {
    key: "maybeHideBlockInserter",
    value: function maybeHideBlockInserter(tabIndex) {
      var blockInserterBtn = jQuery('.edit-post-header-toolbar__inserter-toggle');
      if (tabIndex === 'quiz') {
        blockInserterBtn.show();
      } else {
        blockInserterBtn.hide();
      }
    }

    /**
     * Add CSS to element
     */
  }, {
    key: "addCSS",
    value: function addCSS(selector, rule) {
      if (_typeof(rule) === 'object') {
        jQuery(selector).css(rule);
      }
    }

    /**
     * Toggle visual editor visibility
     */
  }, {
    key: "toggleVisualEditor",
    value: function toggleVisualEditor() {
      var visibility = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "hide";
      if (visibility === "show") {
        jQuery(".edit-post-visual-editor, .edit-post-text-editor").show();
      } else {
        jQuery(".edit-post-visual-editor, .edit-post-text-editor").hide();
      }
    }

    /**
     * Toggle metaboxes visibility
     */
  }, {
    key: "toggleMetaBoxes",
    value: function toggleMetaBoxes(action) {
      var elements = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
      var metaboxes = wp.data.select("core/edit-post").getAllMetaBoxes();
      Object.entries(metaboxes).map(function (_ref) {
        var _ref2 = _slicedToArray(_ref, 2),
          key = _ref2[0],
          metabox = _ref2[1];
        var metaboxEl = jQuery("#" + metabox.id);
        if (action === "hide") {
          if (elements === "" || elements.includes(metabox.id)) {
            metaboxEl.hide();
            return false;
          }
          if (!metaboxEl.hasClass("is-hidden")) metaboxEl.show();
        } else {
          if (elements === "" || elements.includes(metabox.id)) {
            if (metaboxEl.hasClass('closed')) {
              metaboxEl.removeClass('closed');
            }
            metaboxEl.show();
            return false;
          }
          metaboxEl.hide();
        }
      });
    }

    /**
     * Toggle panels visibility
     */
  }, {
    key: "togglePanels",
    value: function togglePanels(action) {
      var element = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "";
      jQuery(".components-panel .components-panel__body").each(function () {
        if (action === "hide") {
          if (element === "" || this.className.includes(element)) {
            jQuery(this).hide();
          } else {
            jQuery(this).show();
          }
        } else {
          if (element === "" || this.className.includes(element)) {
            jQuery(this).show();
            jQuery(this).addClass("is-opened");
          } else {
            jQuery(this).hide();
          }
        }
      });
    }

    /**
     * Show default view (quiz page editing)
     */
  }, {
    key: "showDefaultView",
    value: function showDefaultView() {
      this.toggleVisualEditor("show");
      this.toggleMetaBoxes("hide", ["splms-quiz-questions", "splms-quiz-settings"]);
    }

    /**
     * Show question builder view
     */
  }, {
    key: "showQuestionBuilderView",
    value: function showQuestionBuilderView() {
      this.toggleVisualEditor();
      this.toggleMetaBoxes("show", ["splms-quiz-questions"]);
      this.togglePanels("hide", "");
    }

    /**
     * Show settings view
     */
  }, {
    key: "showSettingsView",
    value: function showSettingsView() {
      this.toggleVisualEditor();
      this.toggleMetaBoxes("show", ["splms-quiz-settings"]);
      this.togglePanels("hide", "");
    }
  }]);
}();


/***/ },

/***/ "./src/js/admin/modules/courses/section-editor.js"
/*!********************************************************!*\
  !*** ./src/js/admin/modules/courses/section-editor.js ***!
  \********************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SPLMSSectionEditor: () => (/* binding */ SPLMSSectionEditor)
/* harmony export */ });
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
var SPLMSSectionEditor = /*#__PURE__*/function () {
  function SPLMSSectionEditor() {
    _classCallCheck(this, SPLMSSectionEditor);
    this.init();
  }

  /**
   * Initialize section editor
   */
  return _createClass(SPLMSSectionEditor, [{
    key: "init",
    value: function init() {
      // Initialize section editor functionality.
    }

    /**
     * Set header height
     */
  }, {
    key: "setHeaderHeight",
    value: function setHeaderHeight() {
      var h = jQuery("#splms-section-header").outerHeight(true) || 0;
      jQuery(".edit-post-layout").css("padding-top", h + "px");
    }
  }, {
    key: "maybeHideHeader",
    value: function maybeHideHeader(sidebarIsOpen, isTabletOrSmaller) {
      if (sidebarIsOpen && isTabletOrSmaller) {
        jQuery("#splms-section-header-wrapper").hide();
      } else {
        jQuery("#splms-section-header-wrapper").show();
      }
    }
  }, {
    key: "maybeHideBlockInserter",
    value: function maybeHideBlockInserter(tabIndex) {
      var blockInserterBtn = jQuery('.edit-post-header-toolbar__inserter-toggle');
      if (tabIndex === 'section') {
        blockInserterBtn.show();
      } else {
        blockInserterBtn.hide();
      }
    }

    /**
     * Add CSS to element
     */
  }, {
    key: "addCSS",
    value: function addCSS(selector, rule) {
      if (_typeof(rule) === 'object') {
        jQuery(selector).css(rule);
      }
    }

    /**
     * Toggle visual editor visibility
     */
  }, {
    key: "toggleVisualEditor",
    value: function toggleVisualEditor() {
      var visibility = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "hide";
      if (visibility === "show") {
        jQuery(".edit-post-visual-editor, .edit-post-text-editor").show();
      } else {
        jQuery(".edit-post-visual-editor, .edit-post-text-editor").hide();
      }
    }

    /**
     * Toggle metaboxes visibility
     */
  }, {
    key: "toggleMetaBoxes",
    value: function toggleMetaBoxes(action) {
      var elements = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
      var metaboxes = wp.data.select("core/edit-post").getAllMetaBoxes();
      Object.entries(metaboxes).map(function (_ref) {
        var _ref2 = _slicedToArray(_ref, 2),
          key = _ref2[0],
          metabox = _ref2[1];
        var metaboxEl = jQuery("#" + metabox.id);
        if (action === "hide") {
          if (elements === "" || elements.includes(metabox.id)) {
            metaboxEl.hide();
            return false;
          }
          if (!metaboxEl.hasClass("is-hidden")) metaboxEl.show();
        } else {
          if (elements === "" || elements.includes(metabox.id)) {
            if (metaboxEl.hasClass('closed')) {
              metaboxEl.removeClass('closed');
            }
            metaboxEl.show();
            return false;
          }
          metaboxEl.hide();
        }
      });
    }

    /**
     * Toggle panels visibility
     */
  }, {
    key: "togglePanels",
    value: function togglePanels(action) {
      var element = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "";
      jQuery(".components-panel .components-panel__body").each(function () {
        if (action === "hide") {
          if (element === "" || this.className.includes(element)) {
            jQuery(this).hide();
          } else {
            jQuery(this).show();
          }
        } else {
          if (element === "" || this.className.includes(element)) {
            jQuery(this).show();
            jQuery(this).addClass("is-opened");
          } else {
            jQuery(this).hide();
          }
        }
      });
    }

    /**
     * Show default view (section page editing)
     */
  }, {
    key: "showDefaultView",
    value: function showDefaultView() {
      this.toggleVisualEditor("show");
      this.toggleMetaBoxes("hide", ["splms-section-settings", "splms-section-pricing"]);
    }

    /**
     * Show settings view
     */
  }, {
    key: "showSettingsView",
    value: function showSettingsView() {
      this.toggleVisualEditor();
      this.toggleMetaBoxes("show", ["splms-section-settings"]);
      this.togglePanels("hide", "");
    }

    /**
     * Show pricing view
     */
  }, {
    key: "showPricingView",
    value: function showPricingView() {
      this.toggleVisualEditor();
      this.toggleMetaBoxes("show", ["splms-section-pricing"]);
      this.togglePanels("hide", "");
    }
  }]);
}();


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
/*!*******************************!*\
  !*** ./src/js/admin/index.js ***!
  \*******************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _core_base_modal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./core/base-modal */ "./src/js/admin/core/base-modal.js");
/* harmony import */ var _modals_signup_review_modal__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./modals/signup-review-modal */ "./src/js/admin/modals/signup-review-modal.js");
/* harmony import */ var _modules_courses_course_editor__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./modules/courses/course-editor */ "./src/js/admin/modules/courses/course-editor.js");
/* harmony import */ var _modules_courses_lesson_editor__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./modules/courses/lesson-editor */ "./src/js/admin/modules/courses/lesson-editor.js");
/* harmony import */ var _modules_courses_quiz_editor__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./modules/courses/quiz-editor */ "./src/js/admin/modules/courses/quiz-editor.js");
/* harmony import */ var _modules_courses_section_editor__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./modules/courses/section-editor */ "./src/js/admin/modules/courses/section-editor.js");
/* harmony import */ var _core_helper__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./core/helper */ "./src/js/admin/core/helper.js");
/* harmony import */ var _core_ajax__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./core/ajax */ "./src/js/admin/core/ajax.js");








var SPLMSCore = {};
SPLMSCore.helper = new _core_helper__WEBPACK_IMPORTED_MODULE_6__.SPLMSHelper();
SPLMSCore.BaseModal = _core_base_modal__WEBPACK_IMPORTED_MODULE_0__.SPLMSBaseModal;
jQuery(document).ready(function () {
  SPLMSCore.adminAjax = new _core_ajax__WEBPACK_IMPORTED_MODULE_7__.SPLMSAdminAjax();
  SPLMSCore.course_editor = new _modules_courses_course_editor__WEBPACK_IMPORTED_MODULE_2__.SPLMSCourseEditor();
  SPLMSCore.lesson_editor = new _modules_courses_lesson_editor__WEBPACK_IMPORTED_MODULE_3__.SPLMSLessonEditor();
  SPLMSCore.quiz_editor = new _modules_courses_quiz_editor__WEBPACK_IMPORTED_MODULE_4__.SPLMSQuizEditor();
  SPLMSCore.section_editor = new _modules_courses_section_editor__WEBPACK_IMPORTED_MODULE_5__.SPLMSSectionEditor();

  // Initialize review modals on their respective admin pages.
  _modals_signup_review_modal__WEBPACK_IMPORTED_MODULE_1__.SPLMSSignupReviewModal.initializeSignupAdminPage();
});
window.SPLMSCore = SPLMSCore;
})();

/******/ })()
;
//# sourceMappingURL=admin.js.map