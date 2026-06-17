/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/js/frontend/core/ajax.js"
/*!**************************************!*\
  !*** ./src/js/frontend/core/ajax.js ***!
  \**************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

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

/***/ "./src/js/frontend/modules/lesson-viewer/CompletionModal.js"
/*!******************************************************************!*\
  !*** ./src/js/frontend/modules/lesson-viewer/CompletionModal.js ***!
  \******************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   CompletionModal: () => (/* binding */ CompletionModal)
/* harmony export */ });
/* harmony import */ var _core_base_modal_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../core/base-modal.js */ "./src/js/frontend/core/base-modal.js");
/* harmony import */ var _react_core_utility_url__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../../react-core/utility/url */ "./src/js/react-core/utility/url.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
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
 * Course Completion Modal
 *
 * Extends SPLMSBaseModal to show course completion celebration
 * with proper integration into the existing modal system.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */



var CompletionModal = /*#__PURE__*/function (_SPLMSBaseModal) {
  function CompletionModal() {
    var _this;
    _classCallCheck(this, CompletionModal);
    _this = _callSuper(this, CompletionModal, [{
      templateId: 'splms-completion-modal',
      modalId: 'splms-completion-modal',
      closeOnBackdrop: true,
      closeOnEscape: true,
      defaultData: {
        courseTitle: 'Your Course',
        totalLessons: 0,
        completedLessons: 0,
        completionDate: '',
        hasCertificate: false,
        certificateUrl: ''
      }
    }]);
    _this.lessonId = null;
    _this.courseId = null;
    _this.certificateData = null;
    return _this;
  }

  /**
   * Show completion modal with course data
   */
  _inherits(CompletionModal, _SPLMSBaseModal);
  return _createClass(CompletionModal, [{
    key: "showCompletion",
    value: (function () {
      var _showCompletion = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(lessonId, courseId, data) {
        var options,
          courseTitle,
          completionStats,
          hasCertificate,
          modalData,
          _args = arguments;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.n) {
            case 0:
              options = _args.length > 3 && _args[3] !== undefined ? _args[3] : {};
              this.lessonId = lessonId;
              this.courseId = courseId;
              this.certificateData = data;

              // Get course information
              courseTitle = this.getCourseTitle();
              completionStats = this.getCompletionStats();
              hasCertificate = data.certificate_generated || data.certificate_id; // Prepare modal data
              modalData = {
                courseTitle: courseTitle,
                totalLessons: completionStats.totalLessons,
                completedLessons: completionStats.completedLessons,
                completionDate: completionStats.completionDate,
                hasCertificate: hasCertificate,
                certificateUrl: this.getCertificateUrl(),
                showActions: options.showActions !== false // Default to true, can be overridden
              }; // Open modal with data
              _context.n = 1;
              return this.open(modalData);
            case 1:
              return _context.a(2);
          }
        }, _callee, this);
      }));
      function showCompletion(_x, _x2, _x3) {
        return _showCompletion.apply(this, arguments);
      }
      return showCompletion;
    }()
    /**
     * Get course title from page context
     */
    )
  }, {
    key: "getCourseTitle",
    value: function getCourseTitle() {
      // Try to find course title from various sources
      var titleElement = document.querySelector('h1.splms-course-title, .splms-lesson-header h1, .course-title');
      if (titleElement) {
        return titleElement.textContent.trim();
      }

      // Fallback to breadcrumb or page title
      var breadcrumb = document.querySelector('.splms-breadcrumb a');
      if (breadcrumb) {
        return breadcrumb.textContent.trim();
      }
      return 'Your Course'; // Final fallback
    }

    /**
     * Get completion statistics
     */
  }, {
    key: "getCompletionStats",
    value: function getCompletionStats() {
      var allItems = document.querySelectorAll('.splms-curriculum-item');
      var completedItems = document.querySelectorAll('.splms-curriculum-item.is-completed');
      return {
        totalLessons: allItems.length,
        completedLessons: completedItems.length,
        completionDate: new Date().toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        })
      };
    }

    /**
     * Get certificate URL if available
     */
  }, {
    key: "getCertificateUrl",
    value: function getCertificateUrl() {
      // Use backend-provided certificate URL (new hash format)
      if (this.certificateData && this.certificateData.certificate_url) {
        return this.certificateData.certificate_url;
      }

      // No fallback - rely on backend generation
      return null;
    }

    /**
     * Get course URL for navigation
     */
  }, {
    key: "getCourseUrl",
    value: function getCourseUrl() {
      // First, try to get course URL from frontend data if available
      if (typeof splms_frontend !== 'undefined' && splms_frontend.course_url) {
        return splms_frontend.course_url;
      }

      // Get course ID from lesson container data attribute
      var lessonContainer = document.getElementById('splms-lesson-fullscreen-container');
      if (lessonContainer) {
        var courseId = lessonContainer.dataset.courseId;
        if (courseId) {
          // Construct course URL using WordPress structure
          var baseUrl = (0,_react_core_utility_url__WEBPACK_IMPORTED_MODULE_1__.getSiteUrl)();
          return "".concat(baseUrl, "/?p=").concat(courseId);
        }
      }

      // Fallback: try to find course link in existing navigation
      var backButton = document.querySelector('.splms-back-button[href]');
      if (backButton && backButton.href && !backButton.href.includes((0,_react_core_utility_url__WEBPACK_IMPORTED_MODULE_1__.getSiteUrl)() + '/' + '?')) {
        return backButton.href;
      }
      return null;
    }

    /**
     * Setup additional event handlers
     */
  }, {
    key: "setupEvents",
    value: function setupEvents() {
      var _this2 = this;
      if (!this.modalElement) return;

      // Handle action button clicks
      this.addEventHandler('click', function (e) {
        var action = e.target.closest('[data-action]');
        if (!action) return;
        e.preventDefault();
        switch (action.dataset.action) {
          case 'download-certificate':
            _this2.handleCertificateDownload();
            break;
          case 'back-to-course':
            _this2.handleBackToCourse();
            break;
          case 'goto-dashboard':
            _this2.handleGotoDashboard();
            break;
        }
      });
    }

    /**
     * Handle certificate download
     */
  }, {
    key: "handleCertificateDownload",
    value: function handleCertificateDownload() {
      var certificateUrl = this.getCertificateUrl();

      // Try to open certificate in new tab
      if (certificateUrl) {
        // Check if this is a proper certificate URL (has nonce)
        if (certificateUrl.includes('cert-nonce=') || certificateUrl.includes('/dashboard/')) {
          window.open(certificateUrl, '_blank');
          window.SPLMSCore.helper.showNotification('Opening certificate...', 'success');
        } else {
          // Fallback URL without proper authentication
          window.SPLMSCore.helper.showNotification('Redirecting to dashboard for certificate access...', 'info');
          window.open('/dashboard/?tab=certificates', '_blank');
        }
      } else {
        window.SPLMSCore.helper.showNotification('Certificate is being generated. Please check your dashboard in a few moments.', 'info');
        // Open dashboard as fallback
        setTimeout(function () {
          window.open('/dashboard/?tab=certificates', '_blank');
        }, 1000);
      }
    }

    /**
     * Handle back to course navigation
     */
  }, {
    key: "handleBackToCourse",
    value: function handleBackToCourse() {
      var courseUrl = this.getCourseUrl();
      if (courseUrl) {
        window.location.href = courseUrl;
      } else {
        // Fallback: go up one level in URL
        var currentUrl = window.location.pathname;
        var _courseUrl = currentUrl.replace(/\/[^\/]+\/?$/, '/');
        window.location.href = _courseUrl;
      }
    }

    /**
     * Handle dashboard navigation
     */
  }, {
    key: "handleGotoDashboard",
    value: function handleGotoDashboard() {
      window.location.href = '/dashboard/';
    }

    /**
     * Called when modal is opened - trigger celebration
     */
  }, {
    key: "onOpen",
    value: (function () {
      var _onOpen = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(data) {
        var _this3 = this;
        return _regenerator().w(function (_context2) {
          while (1) switch (_context2.n) {
            case 0:
              // Trigger celebration animation
              this.triggerCelebration();

              // Add bounce animation to trophy after modal is visible
              setTimeout(function () {
                var trophy = _this3.modalElement.find('.celebration-icon');
                if (trophy.length) {
                  trophy.addClass('celebration-bounce');
                }
              }, 300);
            case 1:
              return _context2.a(2);
          }
        }, _callee2, this);
      }));
      function onOpen(_x4) {
        return _onOpen.apply(this, arguments);
      }
      return onOpen;
    }()
    /**
     * Trigger celebration confetti animation
     */
    )
  }, {
    key: "triggerCelebration",
    value: function triggerCelebration() {
      // Create celebration overlay
      var celebration = document.createElement('div');
      celebration.style.cssText = "\n            position: fixed;\n            top: 0;\n            left: 0;\n            width: 100%;\n            height: 100%;\n            pointer-events: none;\n            z-index: 10002;\n        ";

      // Add floating emojis
      var emojis = ['🎉', '🎊', '🏆', '⭐', '🥳'];
      for (var i = 0; i < 15; i++) {
        var emoji = document.createElement('div');
        emoji.textContent = emojis[Math.floor(Math.random() * emojis.length)];
        emoji.style.cssText = "\n                position: absolute;\n                font-size: 24px;\n                top: -50px;\n                left: ".concat(Math.random() * 100, "%;\n                animation: fallDown 3s ease-in-out forwards;\n                animation-delay: ").concat(Math.random() * 2, "s;\n            ");
        celebration.appendChild(emoji);
      }

      // Add CSS animation for falling emojis
      var style = document.createElement('style');
      style.textContent = "\n            @keyframes fallDown {\n                to {\n                    transform: translateY(100vh) rotate(360deg);\n                    opacity: 0;\n                }\n            }\n        ";
      document.head.appendChild(style);
      document.body.appendChild(celebration);

      // Remove celebration after animation
      setTimeout(function () {
        if (celebration.parentNode) {
          celebration.remove();
        }
        if (style.parentNode) {
          style.remove();
        }
      }, 5000);
    }

    /**
     * Validate modal data
     */
  }, {
    key: "validateData",
    value: function validateData(data) {
      if (!data.courseTitle || data.courseTitle.trim() === '') {
        return {
          isValid: false,
          message: 'Course title is required'
        };
      }
      if (data.totalLessons <= 0) {
        return {
          isValid: false,
          message: 'Invalid lesson count'
        };
      }
      return {
        isValid: true
      };
    }
  }]);
}(_core_base_modal_js__WEBPACK_IMPORTED_MODULE_0__.SPLMSBaseModal);

/***/ },

/***/ "./src/js/frontend/modules/lesson-viewer/LessonCompletion.js"
/*!*******************************************************************!*\
  !*** ./src/js/frontend/modules/lesson-viewer/LessonCompletion.js ***!
  \*******************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _core_ajax_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../core/ajax.js */ "./src/js/frontend/core/ajax.js");
/* harmony import */ var _CompletionModal_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./CompletionModal.js */ "./src/js/frontend/modules/lesson-viewer/CompletionModal.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Lesson Completion Handler
 *
 * Handles marking lessons as complete via AJAX.
 * Validates video completion requirements before allowing completion.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */



var SPLMSLessonCompletion = /*#__PURE__*/function () {
  function SPLMSLessonCompletion(lessonId, courseId) {
    var videoTracker = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
    _classCallCheck(this, SPLMSLessonCompletion);
    this.lessonId = lessonId;
    this.courseId = courseId;
    this.videoTracker = videoTracker;
    this.completeButton = document.getElementById('splms-mark-complete-btn');
    this.isProcessing = false;
    this.courseCompletionTriggered = false;
    this.ajax = new _core_ajax_js__WEBPACK_IMPORTED_MODULE_0__.SPLMSFrontendAjax();
    this.completionModal = new _CompletionModal_js__WEBPACK_IMPORTED_MODULE_1__.CompletionModal();
    this.init();
  }
  return _createClass(SPLMSLessonCompletion, [{
    key: "init",
    value: function init() {
      var _this = this;
      if (!this.completeButton) {
        return;
      }

      // Check if lesson is locked.
      if (this.isLessonLocked()) {
        this.disableForLockedLesson();
        return;
      }

      // Check if already completed.
      var isCompleted = this.completeButton.classList.contains('is-completed');
      if (isCompleted) {
        this.completeButton.disabled = true;
        return;
      }

      // Listen for video completion if video tracker exists.
      if (this.videoTracker) {
        document.addEventListener('splms:videoCompleted', function () {
          _this.enableCompleteButton();
        });
      }

      // Handle complete button click.
      this.completeButton.addEventListener('click', function (e) {
        e.preventDefault();
        _this.markComplete();
      });
    }

    /**
     * Check if the current lesson is locked
     */
  }, {
    key: "isLessonLocked",
    value: function isLessonLocked() {
      // Check for locked content indicator in the page
      var lockedContent = document.querySelector('.splms-locked-content');
      var lockedMessage = document.querySelector('[data-locked="true"]');

      // Check if the locked content section exists and contains the "This lesson is locked" message
      if (lockedContent) {
        var lockText = lockedContent.textContent || '';
        return lockText.includes('This lesson is locked') || lockText.includes('You need to purchase');
      }

      // Alternative check for locked indicators
      if (lockedMessage) {
        return true;
      }

      // Check if main content area shows purchase button (using standard DOM methods)
      var purchaseButtons = document.querySelectorAll('a[href*="course"]');
      var _iterator = _createForOfIteratorHelper(purchaseButtons),
        _step;
      try {
        for (_iterator.s(); !(_step = _iterator.n()).done;) {
          var button = _step.value;
          var buttonText = button.textContent || '';
          if (buttonText.includes('View Course') && buttonText.includes('Purchase')) {
            return true;
          }
        }

        // Check for other locked lesson indicators
      } catch (err) {
        _iterator.e(err);
      } finally {
        _iterator.f();
      }
      var lockTexts = ['This lesson is locked', 'You need to purchase', 'Access denied', 'Complete previous lessons'];
      for (var _i = 0, _lockTexts = lockTexts; _i < _lockTexts.length; _i++) {
        var text = _lockTexts[_i];
        if (document.body.textContent.includes(text)) {
          return true;
        }
      }
      return false;
    }

    /**
     * Disable the completion button for locked lessons
     */
  }, {
    key: "disableForLockedLesson",
    value: function disableForLockedLesson() {
      if (!this.completeButton) {
        return;
      }
      this.completeButton.disabled = true;
      this.completeButton.classList.add('is-locked');

      // Update button text and styling for locked state
      this.completeButton.innerHTML = "\n\t\t\t<svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n\t\t\t\t<rect x=\"5\" y=\"11\" width=\"14\" height=\"10\" rx=\"2\" stroke=\"currentColor\" stroke-width=\"2\"/>\n\t\t\t\t<path d=\"M7 11V7C7 5.67392 7.52678 4.40215 8.46447 3.46447C9.40215 2.52678 10.6739 2 12 2C13.3261 2 14.5979 2.52678 15.5355 3.46447C16.4732 4.40215 17 5.67392 17 7V11\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\"/>\n\t\t\t\t<circle cx=\"12\" cy=\"16\" r=\"1\" fill=\"currentColor\"/>\n\t\t\t</svg>\n\t\t\t<span>Lesson Locked</span>\n\t\t";

      // Add tooltip explaining why it's locked
      this.completeButton.setAttribute('title', 'Complete previous lessons or purchase this section to unlock');
      this.completeButton.setAttribute('aria-label', 'This lesson is locked and cannot be completed');
    }
  }, {
    key: "enableCompleteButton",
    value: function enableCompleteButton() {
      if (this.completeButton && this.completeButton.classList.contains('is-disabled')) {
        this.completeButton.classList.remove('is-disabled');
        this.completeButton.disabled = false;
      }
    }
  }, {
    key: "markComplete",
    value: function () {
      var _markComplete = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
        var completionRequired, watchPercentage, response, _response$data, errorMessage, _t;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.p = _context.n) {
            case 0:
              if (!this.isProcessing) {
                _context.n = 1;
                break;
              }
              return _context.a(2);
            case 1:
              if (!this.isLessonLocked()) {
                _context.n = 2;
                break;
              }
              this.showError('This lesson is locked. Please purchase the section or complete prerequisites to access it.');
              return _context.a(2);
            case 2:
              if (!(this.videoTracker && !this.videoTracker.isVideoCompleted())) {
                _context.n = 3;
                break;
              }
              completionRequired = this.videoTracker.completionRequired;
              watchPercentage = this.videoTracker.getWatchPercentage();
              this.showError("Please watch at least ".concat(completionRequired, "% of the video to complete this lesson. You have watched ").concat(Math.round(watchPercentage), "%."));
              return _context.a(2);
            case 3:
              this.isProcessing = true;
              this.setButtonLoading(true);
              _context.p = 4;
              _context.n = 5;
              return this.ajax.request({
                data: {
                  action: 'splms_mark_lesson_complete',
                  lesson_id: this.lessonId,
                  course_id: this.courseId,
                  video_progress: this.videoTracker ? this.videoTracker.getWatchPercentage() : 100
                }
              });
            case 5:
              response = _context.v;
              if (response.success) {
                this.handleSuccess(response.data);
              } else {
                // Handle specific access denied errors
                errorMessage = ((_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.message) || response.data || 'Failed to mark lesson as complete.';
                if (typeof errorMessage === 'string' && errorMessage.toLowerCase().includes('access denied')) {
                  this.showError('Access denied. This lesson may be locked or you may not have the required permissions.');
                } else {
                  this.showError(errorMessage);
                }
              }
              _context.n = 7;
              break;
            case 6:
              _context.p = 6;
              _t = _context.v;
              console.error('Error marking lesson complete:', _t);
              this.showError('An error occurred. Please try again.');
            case 7:
              _context.p = 7;
              this.isProcessing = false;
              this.setButtonLoading(false);
              return _context.f(7);
            case 8:
              return _context.a(2);
          }
        }, _callee, this, [[4, 6, 7, 8]]);
      }));
      function markComplete() {
        return _markComplete.apply(this, arguments);
      }
      return markComplete;
    }()
  }, {
    key: "handleSuccess",
    value: function handleSuccess(data) {
      // Update button state.
      this.completeButton.classList.add('is-completed');
      this.completeButton.disabled = true;
      this.completeButton.innerHTML = "\n\t\t\t<svg width=\"20\" height=\"20\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n\t\t\t\t<path d=\"M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n\t\t\t\t<path d=\"M22 4L12 14.01L9 11.01\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n\t\t\t</svg>\n\t\t\t<span>Completed</span>\n\t\t";

      // Update status badge in header.
      var statusBadge = document.querySelector('.splms-status-badge');
      if (statusBadge) {
        statusBadge.classList.remove('splms-status-in-progress');
        statusBadge.classList.add('splms-status-completed');
        statusBadge.textContent = 'Completed';
      }

      // Update sidebar curriculum item first (marks current item as completed).
      this.updateSidebarItem();

      // Update progress bar and sidebar progress.
      if (data.progress_percentage !== undefined) {
        this.updateProgressBar(data.progress_percentage);
      } else {
        // Calculate progress if not provided by server
        var calculatedProgress = this.calculateProgressFromDOM();
        if (calculatedProgress > 0) {
          this.updateProgressBar(calculatedProgress);
        }
      }

      // Dispatch completion event.
      var event = new CustomEvent('splms:lessonCompleted', {
        detail: {
          lessonId: this.lessonId,
          courseId: this.courseId,
          data: data
        }
      });
      document.dispatchEvent(event);

      // Check if course is completed (100%) and this is the final item
      var progress = this.calculateCourseProgress(data);
      var isFinal = this.isFinalCourseItem();
      if (progress >= 100 && isFinal) {
        this.handleCourseCompletion(data);
      } else {
        // Show regular lesson completion message.
        this.showSuccess('Lesson marked as complete!');
      }
    }

    /**
     * Calculate course progress percentage
     */
  }, {
    key: "calculateCourseProgress",
    value: function calculateCourseProgress(data) {
      // Priority 1: Use progress from server response
      if (data.progress_percentage !== undefined && data.progress_percentage !== null) {
        return parseFloat(data.progress_percentage);
      }

      // Priority 2: Calculate from DOM curriculum items
      return this.calculateProgressFromDOM();
    }

    /**
     * Calculate progress from DOM curriculum items
     */
  }, {
    key: "calculateProgressFromDOM",
    value: function calculateProgressFromDOM() {
      var allItems = document.querySelectorAll('.splms-curriculum-item');
      if (allItems.length > 0) {
        var completedItems = document.querySelectorAll('.splms-curriculum-item.is-completed');
        var calculatedProgress = completedItems.length / allItems.length * 100;
        return calculatedProgress;
      }

      // Priority 3: Check progress bars in UI
      var progressElement = document.querySelector('.splms-progress-value, .splms-progress-percentage');
      if (progressElement) {
        var progressText = progressElement.textContent.replace('%', '');
        var progressFromUI = parseFloat(progressText);
        if (!isNaN(progressFromUI)) {
          return progressFromUI;
        }
      }
      return 0;
    }
  }, {
    key: "updateProgressBar",
    value: function updateProgressBar(percentage) {
      // Update progress bar fills
      var progressFills = document.querySelectorAll('.splms-progress-fill');
      progressFills.forEach(function (fill) {
        fill.style.width = percentage + '%';
      });

      // Update progress percentage displays
      var progressValues = document.querySelectorAll('.splms-progress-value');
      progressValues.forEach(function (value) {
        value.textContent = Math.round(percentage) + '%';
      });
      var progressPercentages = document.querySelectorAll('.splms-progress-percentage');
      progressPercentages.forEach(function (el) {
        el.textContent = Math.round(percentage) + '%';
      });

      // Update sidebar progress summary
      this.updateSidebarProgress(percentage);
    }
  }, {
    key: "updateSidebarProgress",
    value: function updateSidebarProgress(percentage) {
      // Calculate completed items count (current item should already be marked as completed at this point)
      var allItems = document.querySelectorAll('.splms-curriculum-item');
      var completedItems = document.querySelectorAll('.splms-curriculum-item.is-completed');
      var totalCompleted = completedItems.length;
      var totalItems = allItems.length;

      // Update progress count displays (e.g., "1/3 items")
      var progressCounts = document.querySelectorAll('.splms-progress-count');
      progressCounts.forEach(function (count) {
        count.textContent = "".concat(totalCompleted, "/").concat(totalItems, " items");
      });

      // Update sidebar summary completion status
      var summaryCards = document.querySelectorAll('.splms-progress-summary-card');
      summaryCards.forEach(function (card) {
        // Update percentage in summary card
        var summaryValue = card.querySelector('.splms-progress-value');
        if (summaryValue) {
          summaryValue.textContent = Math.round(percentage) + '%';
        }

        // Update item count in summary card
        var summaryCount = card.querySelector('.splms-progress-count');
        if (summaryCount) {
          summaryCount.textContent = "".concat(totalCompleted, "/").concat(totalItems, " items");
        }

        // Update completion label
        var progressLabel = card.querySelector('.splms-progress-label');
        if (progressLabel) {
          if (percentage >= 100) {
            progressLabel.textContent = 'Complete';
          } else {
            progressLabel.textContent = 'Complete';
          }
        }
      });

      // Also update any standalone progress displays in sidebar
      var sidebarProgressValues = document.querySelectorAll('.splms-fullscreen-sidebar .splms-progress-value');
      sidebarProgressValues.forEach(function (value) {
        value.textContent = Math.round(percentage) + '%';
      });
    }
  }, {
    key: "updateSidebarItem",
    value: function updateSidebarItem() {
      var currentItem = document.querySelector('.splms-curriculum-item.is-current');
      if (currentItem) {
        currentItem.classList.add('is-completed');

        // Add checkmark if not exists.
        if (!currentItem.querySelector('.splms-check-icon')) {
          var checkmark = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
          checkmark.classList.add('splms-check-icon');
          checkmark.setAttribute('width', '20');
          checkmark.setAttribute('height', '20');
          checkmark.setAttribute('viewBox', '0 0 24 24');
          checkmark.setAttribute('fill', 'none');
          checkmark.innerHTML = '<path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
          currentItem.appendChild(checkmark);
        }
      }
    }
  }, {
    key: "setButtonLoading",
    value: function setButtonLoading(loading) {
      if (loading) {
        this.completeButton.disabled = true;
        this.completeButton.classList.add('is-loading');
        this.completeButton.innerHTML = "\n\t\t\t\t<svg class=\"splms-spinner\" width=\"20\" height=\"20\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n\t\t\t\t\t<circle cx=\"12\" cy=\"12\" r=\"10\" stroke=\"currentColor\" stroke-width=\"2\" opacity=\"0.25\"/>\n\t\t\t\t\t<path d=\"M12 2C6.47715 2 2 6.47715 2 12\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\"/>\n\t\t\t\t</svg>\n\t\t\t\t<span>Processing...</span>\n\t\t\t";
      } else {
        // Only reset if not already completed or locked
        if (!this.completeButton.classList.contains('is-completed') && !this.completeButton.classList.contains('is-locked')) {
          this.completeButton.disabled = false;
          this.completeButton.classList.remove('is-loading');
          this.completeButton.innerHTML = "\n\t\t\t\t\t<svg width=\"20\" height=\"20\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n\t\t\t\t\t\t<path d=\"M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n\t\t\t\t\t\t<path d=\"M22 4L12 14.01L9 11.01\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n\t\t\t\t\t</svg>\n\t\t\t\t\t<span>Mark as Complete</span>\n\t\t\t\t";
        } else {
          // Just remove loading state but preserve completed/locked state
          this.completeButton.classList.remove('is-loading');
        }
      }
    }
  }, {
    key: "showSuccess",
    value: function showSuccess(message) {
      // Use existing notification system
      if (window.SPLMSCore && window.SPLMSCore.helper) {
        window.SPLMSCore.helper.showNotification(message, 'success');
      } else {
        console.log('Success:', message);
      }
    }
  }, {
    key: "showError",
    value: function showError(message) {
      // Use existing notification system
      if (window.SPLMSCore && window.SPLMSCore.helper) {
        window.SPLMSCore.helper.showNotification(message, 'error');
      } else {
        console.error('Error:', message);
      }
    }

    /**
     * Check if this is the final lesson/quiz in the course
     */
  }, {
    key: "isFinalCourseItem",
    value: function isFinalCourseItem() {
      // Check if we're on the last item in the curriculum
      var currentItem = document.querySelector('.splms-curriculum-item.is-current');
      if (!currentItem) {
        return false;
      }

      // Look for next items that are not completed
      var allItems = document.querySelectorAll('.splms-curriculum-item');
      var currentIndex = Array.from(allItems).indexOf(currentItem);

      // If this is the last item in the list, it's the final item
      if (currentIndex === allItems.length - 1) {
        return true;
      }

      // Check if all remaining items are already completed
      for (var i = currentIndex + 1; i < allItems.length; i++) {
        if (!allItems[i].classList.contains('is-completed')) {
          return false;
        }
      }
      return true;
    }

    /**
     * Handle course completion with special celebration
     */
  }, {
    key: "handleCourseCompletion",
    value: function handleCourseCompletion(data) {
      // Prevent duplicate course completion events
      if (this.courseCompletionTriggered) {
        return;
      }
      this.courseCompletionTriggered = true;

      // Dispatch course completion event
      var courseEvent = new CustomEvent('splms:courseCompleted', {
        detail: {
          lessonId: this.lessonId,
          courseId: this.courseId,
          data: data
        }
      });
      document.dispatchEvent(courseEvent);

      // Show course completion celebration
      this.showCourseCompletion(data);

      // Update course status indicators
      this.updateCourseCompletionStatus();
    }

    /**
     * Show course completion modal with celebration
     */
  }, {
    key: "showCourseCompletion",
    value: (function () {
      var _showCourseCompletion = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(data) {
        return _regenerator().w(function (_context2) {
          while (1) switch (_context2.n) {
            case 0:
              _context2.n = 1;
              return this.completionModal.showCompletion(this.lessonId, this.courseId, data);
            case 1:
              return _context2.a(2);
          }
        }, _callee2, this);
      }));
      function showCourseCompletion(_x) {
        return _showCourseCompletion.apply(this, arguments);
      }
      return showCourseCompletion;
    }()
    /**
     * Update course completion status indicators
     */
    )
  }, {
    key: "updateCourseCompletionStatus",
    value: function updateCourseCompletionStatus() {
      // Update status badge in header
      var statusBadges = document.querySelectorAll('.splms-status-badge');
      statusBadges.forEach(function (badge) {
        badge.classList.remove('splms-status-in-progress');
        badge.classList.add('splms-status-completed');
        badge.textContent = 'Completed';
      });

      // Update course completion indicators
      var courseWrappers = document.querySelectorAll('.splms-course-wrapper');
      courseWrappers.forEach(function (wrapper) {
        wrapper.classList.add('course-completed');
      });

      // Hide next button for final lesson and show completion actions
      var nextButton = document.querySelector('.splms-btn-next');
      if (nextButton) {
        nextButton.style.display = 'none';
      }
    }
  }, {
    key: "destroy",
    value: function destroy() {
      if (this.completeButton) {
        this.completeButton.removeEventListener('click', this.markComplete);
      }
    }
  }]);
}();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (SPLMSLessonCompletion);

/***/ },

/***/ "./src/js/frontend/modules/lesson-viewer/SidebarToggle.js"
/*!****************************************************************!*\
  !*** ./src/js/frontend/modules/lesson-viewer/SidebarToggle.js ***!
  \****************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Sidebar Toggle
 *
 * Handles sidebar collapse/expand functionality for distraction-free learning.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */
var SPLMSSidebarToggle = /*#__PURE__*/function () {
  function SPLMSSidebarToggle() {
    _classCallCheck(this, SPLMSSidebarToggle);
    this.sidebar = document.querySelector('.splms-fullscreen-sidebar');
    this.toggleButton = document.querySelector('.splms-sidebar-collapse-toggle');
    this.container = document.querySelector('.splms-fullscreen-layout');
    this.isCollapsed = false;

    // Check localStorage for saved state.
    var savedState = localStorage.getItem('splms_sidebar_collapsed');
    if (savedState === 'true') {
      this.isCollapsed = true;
    }
    this.init();
  }
  return _createClass(SPLMSSidebarToggle, [{
    key: "init",
    value: function init() {
      var _this = this;
      if (!this.sidebar || !this.toggleButton) {
        return;
      }

      // Apply saved state.
      if (this.isCollapsed) {
        this.collapse(false);
      }

      // Handle toggle button click.
      this.toggleButton.addEventListener('click', function () {
        _this.toggle();
      });

      // Handle keyboard shortcut (Ctrl + B or Cmd + B).
      document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
          e.preventDefault();
          _this.toggle();
        }
      });
    }
  }, {
    key: "toggle",
    value: function toggle() {
      if (this.isCollapsed) {
        this.expand();
      } else {
        this.collapse();
      }
    }
  }, {
    key: "collapse",
    value: function collapse() {
      var _this$container,
        _this$toggleButton,
        _this$toggleButton2,
        _this2 = this;
      var animate = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
      if (!this.sidebar) return;
      this.isCollapsed = true;
      if (!animate) {
        this.sidebar.style.transition = 'none';
      }
      this.sidebar.classList.add('is-collapsed');
      (_this$container = this.container) === null || _this$container === void 0 || _this$container.classList.add('sidebar-collapsed');
      (_this$toggleButton = this.toggleButton) === null || _this$toggleButton === void 0 || _this$toggleButton.setAttribute('aria-expanded', 'false');

      // Update button icon direction.
      var icon = (_this$toggleButton2 = this.toggleButton) === null || _this$toggleButton2 === void 0 ? void 0 : _this$toggleButton2.querySelector('svg');
      if (icon) {
        icon.style.transform = 'rotate(0deg)';
      }

      // Save state to localStorage.
      localStorage.setItem('splms_sidebar_collapsed', 'true');
      if (!animate) {
        setTimeout(function () {
          if (_this2.sidebar) {
            _this2.sidebar.style.transition = '';
          }
        }, 0);
      }

      // Dispatch event.
      this.dispatchToggleEvent('collapsed');
    }
  }, {
    key: "expand",
    value: function expand() {
      var _this$container2,
        _this$toggleButton3,
        _this$toggleButton4,
        _this3 = this;
      var animate = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
      if (!this.sidebar) return;
      this.isCollapsed = false;
      if (!animate) {
        this.sidebar.style.transition = 'none';
      }
      this.sidebar.classList.remove('is-collapsed');
      (_this$container2 = this.container) === null || _this$container2 === void 0 || _this$container2.classList.remove('sidebar-collapsed');
      (_this$toggleButton3 = this.toggleButton) === null || _this$toggleButton3 === void 0 || _this$toggleButton3.setAttribute('aria-expanded', 'true');

      // Update button icon direction.
      var icon = (_this$toggleButton4 = this.toggleButton) === null || _this$toggleButton4 === void 0 ? void 0 : _this$toggleButton4.querySelector('svg');
      if (icon) {
        icon.style.transform = 'rotate(180deg)';
      }

      // Save state to localStorage.
      localStorage.setItem('splms_sidebar_collapsed', 'false');
      if (!animate) {
        setTimeout(function () {
          if (_this3.sidebar) {
            _this3.sidebar.style.transition = '';
          }
        }, 0);
      }

      // Dispatch event.
      this.dispatchToggleEvent('expanded');
    }
  }, {
    key: "dispatchToggleEvent",
    value: function dispatchToggleEvent(state) {
      var event = new CustomEvent('splms:sidebarToggle', {
        detail: {
          state: state
        }
      });
      document.dispatchEvent(event);
    }
  }, {
    key: "destroy",
    value: function destroy() {
      // Remove event listeners if needed (button will be destroyed with DOM).
    }
  }]);
}();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (SPLMSSidebarToggle);

/***/ },

/***/ "./src/js/frontend/modules/lesson-viewer/VideoTracker.js"
/*!***************************************************************!*\
  !*** ./src/js/frontend/modules/lesson-viewer/VideoTracker.js ***!
  \***************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Video Tracker
 *
 * Tracks video playback progress for YouTube, Vimeo, and HTML5 videos.
 * Updates the watch percentage and dispatches events when completion requirements are met.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */
var SPLMSVideoTracker = /*#__PURE__*/function () {
  function SPLMSVideoTracker(container, lessonId) {
    _classCallCheck(this, SPLMSVideoTracker);
    this.container = container;
    this.lessonId = lessonId;
    this.videoType = container.dataset.videoType;
    this.videoId = container.dataset.videoId;
    this.completionRequired = parseInt(container.dataset.completionRequired) || 100;
    this.player = null;
    this.duration = 0;
    this.watchedSegments = new Set();
    this.watchPercentage = 0;
    this.isCompleted = false;
    this.totalWatchTime = 0;
    this.lastPosition = 0;
    this.updateInterval = null;
    this.saveInterval = null;
    this.progressDisplay = container.querySelector('.splms-video-watch-percentage');

    // Cookie and database integration.
    this.cookieKey = "splms_video_progress_".concat(this.lessonId);
    this.lastSaveTime = 0;
    this.courseId = this.getCourseId();
    this.restBase = window.SPLMSCore.helper.getRestApiUrl().replace(/\/$/, '');
    this.init();
  }
  return _createClass(SPLMSVideoTracker, [{
    key: "init",
    value: function () {
      var _init = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
        var _t;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.n) {
            case 0:
              _context.n = 1;
              return this.loadProgress();
            case 1:
              _t = this.videoType;
              _context.n = _t === 'youtube' ? 2 : _t === 'vimeo' ? 3 : _t === 'html5' ? 4 : 5;
              break;
            case 2:
              this.initYouTube();
              return _context.a(3, 6);
            case 3:
              this.initVimeo();
              return _context.a(3, 6);
            case 4:
              this.initHTML5();
              return _context.a(3, 6);
            case 5:
              console.warn('Unknown video type:', this.videoType);
            case 6:
              return _context.a(2);
          }
        }, _callee, this);
      }));
      function init() {
        return _init.apply(this, arguments);
      }
      return init;
    }()
  }, {
    key: "initYouTube",
    value: function initYouTube() {
      var _this = this;
      // Load YouTube IFrame API if not already loaded.
      if (!window.YT) {
        var tag = document.createElement('script');
        tag.src = 'https://www.youtube.com/iframe_api';
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
      }

      // Wait for API to be ready.
      var initPlayer = function initPlayer() {
        var playerElement = document.getElementById("splms-youtube-player-".concat(_this.lessonId));
        if (!playerElement) return;
        _this.player = new YT.Player("splms-youtube-player-".concat(_this.lessonId), {
          height: '100%',
          width: '100%',
          videoId: _this.videoId,
          playerVars: {
            rel: 0,
            modestbranding: 1
          },
          events: {
            onReady: _this.onYouTubeReady.bind(_this),
            onStateChange: _this.onYouTubeStateChange.bind(_this)
          }
        });
      };
      if (window.YT && window.YT.Player) {
        initPlayer();
      } else {
        window.onYouTubeIframeAPIReady = initPlayer;
      }
    }
  }, {
    key: "onYouTubeReady",
    value: function onYouTubeReady(event) {
      var _this2 = this;
      this.duration = event.target.getDuration();
      this.startTracking();
      // Resume from last position after a short delay
      setTimeout(function () {
        _this2.resumeFromLastPosition();
      }, 1000);
    }
  }, {
    key: "onYouTubeStateChange",
    value: function onYouTubeStateChange(event) {
      // YT.PlayerState.PLAYING = 1
      if (event.data === 1) {
        this.startTracking();
      } else {
        this.stopTracking();
      }
    }
  }, {
    key: "initVimeo",
    value: function initVimeo() {
      var _this3 = this;
      // Load Vimeo Player API if not already loaded.
      if (!window.Vimeo) {
        var script = document.createElement('script');
        script.src = 'https://player.vimeo.com/api/player.js';
        script.onload = function () {
          return _this3.createVimeoPlayer();
        };
        document.head.appendChild(script);
      } else {
        this.createVimeoPlayer();
      }
    }
  }, {
    key: "createVimeoPlayer",
    value: function createVimeoPlayer() {
      var _this4 = this;
      var iframe = document.getElementById("splms-vimeo-player-".concat(this.lessonId));
      if (!iframe) return;
      this.player = new Vimeo.Player(iframe);
      this.player.getDuration().then(function (duration) {
        _this4.duration = duration;
        // Resume from last position after getting duration
        setTimeout(function () {
          _this4.resumeFromLastPosition();
        }, 1000);
      });
      this.player.on('play', function () {
        _this4.startTracking();
      });
      this.player.on('pause', function () {
        _this4.stopTracking();
      });
      this.player.on('ended', function () {
        _this4.stopTracking();
      });
    }
  }, {
    key: "initHTML5",
    value: function initHTML5() {
      var _this5 = this;
      this.player = document.getElementById("splms-html5-player-".concat(this.lessonId));
      if (!this.player) return;
      this.player.addEventListener('loadedmetadata', function () {
        _this5.duration = _this5.player.duration;
        // Resume from last position after metadata is loaded
        setTimeout(function () {
          _this5.resumeFromLastPosition();
        }, 500);
      });
      this.player.addEventListener('play', function () {
        _this5.startTracking();
      });
      this.player.addEventListener('pause', function () {
        _this5.stopTracking();
      });
      this.player.addEventListener('ended', function () {
        _this5.stopTracking();
      });
    }
  }, {
    key: "startTracking",
    value: function startTracking() {
      var _this6 = this;
      if (this.updateInterval) return;

      // Track progress every second
      this.updateInterval = setInterval(function () {
        _this6.updateProgress();
      }, 1000);

      // Save progress every 30 seconds
      if (!this.saveInterval) {
        this.saveInterval = setInterval(function () {
          _this6.saveProgress();
        }, 30000);
      }
    }
  }, {
    key: "stopTracking",
    value: function stopTracking() {
      if (this.updateInterval) {
        clearInterval(this.updateInterval);
        this.updateInterval = null;
      }
      if (this.saveInterval) {
        clearInterval(this.saveInterval);
        this.saveInterval = null;
      }

      // Save progress one final time when stopping
      this.saveProgress();
    }
  }, {
    key: "updateProgress",
    value: function () {
      var _updateProgress = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2() {
        var currentTime, segment, totalSegments, _t2, _t3;
        return _regenerator().w(function (_context2) {
          while (1) switch (_context2.p = _context2.n) {
            case 0:
              currentTime = 0;
              _context2.p = 1;
              _t2 = this.videoType;
              _context2.n = _t2 === 'youtube' ? 2 : _t2 === 'vimeo' ? 5 : _t2 === 'html5' ? 8 : 9;
              break;
            case 2:
              if (!(this.player && this.player.getCurrentTime)) {
                _context2.n = 4;
                break;
              }
              _context2.n = 3;
              return this.player.getCurrentTime();
            case 3:
              currentTime = _context2.v;
            case 4:
              return _context2.a(3, 9);
            case 5:
              if (!(this.player && this.player.getCurrentTime)) {
                _context2.n = 7;
                break;
              }
              _context2.n = 6;
              return this.player.getCurrentTime();
            case 6:
              currentTime = _context2.v;
            case 7:
              return _context2.a(3, 9);
            case 8:
              currentTime = this.player.currentTime;
              return _context2.a(3, 9);
            case 9:
              if (this.duration > 0) {
                // Track watched segments (each second).
                segment = Math.floor(currentTime);
                this.watchedSegments.add(segment);

                // Calculate watch percentage.
                totalSegments = Math.floor(this.duration);
                this.watchPercentage = this.watchedSegments.size / totalSegments * 100;

                // Update last position and total watch time.
                this.lastPosition = currentTime;
                this.totalWatchTime = this.watchedSegments.size;

                // Update display.
                if (this.progressDisplay) {
                  this.progressDisplay.textContent = Math.round(this.watchPercentage) + '%';
                }

                // Check if completion requirement is met.
                if (!this.isCompleted && this.watchPercentage >= this.completionRequired) {
                  this.isCompleted = true;
                  this.dispatchCompletionEvent();
                }
              }
              _context2.n = 11;
              break;
            case 10:
              _context2.p = 10;
              _t3 = _context2.v;
              console.error('Error updating video progress:', _t3);
            case 11:
              return _context2.a(2);
          }
        }, _callee2, this, [[1, 10]]);
      }));
      function updateProgress() {
        return _updateProgress.apply(this, arguments);
      }
      return updateProgress;
    }()
  }, {
    key: "dispatchCompletionEvent",
    value: function dispatchCompletionEvent() {
      var event = new CustomEvent('splms:videoCompleted', {
        detail: {
          lessonId: this.lessonId,
          watchPercentage: this.watchPercentage,
          completionRequired: this.completionRequired
        }
      });
      document.dispatchEvent(event);
    }
  }, {
    key: "getWatchPercentage",
    value: function getWatchPercentage() {
      return this.watchPercentage;
    }
  }, {
    key: "isVideoCompleted",
    value: function isVideoCompleted() {
      return this.isCompleted;
    }
  }, {
    key: "destroy",
    value: function destroy() {
      this.stopTracking();
      if (this.videoType === 'youtube' && this.player && this.player.destroy) {
        this.player.destroy();
      }
      this.player = null;
    }

    // ========== Progress Persistence Methods ==========

    /**
     * Get course ID from DOM or container data
     */
  }, {
    key: "getCourseId",
    value: function getCourseId() {
      // Try to get from lesson viewer container
      var lessonContainer = document.getElementById('splms-lesson-fullscreen-container');
      if (lessonContainer && lessonContainer.dataset.courseId) {
        return parseInt(lessonContainer.dataset.courseId);
      }

      // Fallback: try to get from body class or other sources
      var bodyClasses = document.body.className;
      var courseMatch = bodyClasses.match(/course-(\d+)/);
      if (courseMatch) {
        return parseInt(courseMatch[1]);
      }

      // Last fallback: check for course ID in URL or meta
      var urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('course_id')) {
        return parseInt(urlParams.get('course_id'));
      }
      return 0;
    }

    /**
     * Load existing progress from server and cookie
     */
  }, {
    key: "loadProgress",
    value: (function () {
      var _loadProgress = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee3() {
        var serverProgress, cookieProgress, progress, _cookieProgress, _t4;
        return _regenerator().w(function (_context3) {
          while (1) switch (_context3.p = _context3.n) {
            case 0:
              _context3.p = 0;
              _context3.n = 1;
              return this.fetchServerProgress();
            case 1:
              serverProgress = _context3.v;
              // Load from cookie as fallback
              cookieProgress = this.loadFromCookie(); // Use the most recent data
              progress = this.selectMostRecent(serverProgress, cookieProgress);
              if (progress) {
                this.restoreProgress(progress);
              }
              _context3.n = 3;
              break;
            case 2:
              _context3.p = 2;
              _t4 = _context3.v;
              console.warn('Error loading video progress:', _t4);
              // Try cookie fallback
              _cookieProgress = this.loadFromCookie();
              if (_cookieProgress) {
                this.restoreProgress(_cookieProgress);
              }
            case 3:
              return _context3.a(2);
          }
        }, _callee3, this, [[0, 2]]);
      }));
      function loadProgress() {
        return _loadProgress.apply(this, arguments);
      }
      return loadProgress;
    }()
    /**
     * Fetch progress from server via REST API
     */
    )
  }, {
    key: "fetchServerProgress",
    value: (function () {
      var _fetchServerProgress = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee4() {
        var response, data, _t5;
        return _regenerator().w(function (_context4) {
          while (1) switch (_context4.p = _context4.n) {
            case 0:
              if (this.lessonId) {
                _context4.n = 1;
                break;
              }
              return _context4.a(2, null);
            case 1:
              _context4.p = 1;
              _context4.n = 2;
              return fetch("".concat(this.restBase, "/splms/v1/lessons/").concat(this.lessonId, "/video-progress"), {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                  'Content-Type': 'application/json'
                }
              });
            case 2:
              response = _context4.v;
              if (!response.ok) {
                _context4.n = 4;
                break;
              }
              _context4.n = 3;
              return response.json();
            case 3:
              data = _context4.v;
              return _context4.a(2, data && data.lastUpdated ? data : null);
            case 4:
              _context4.n = 6;
              break;
            case 5:
              _context4.p = 5;
              _t5 = _context4.v;
              console.warn('Failed to fetch server progress:', _t5);
            case 6:
              return _context4.a(2, null);
          }
        }, _callee4, this, [[1, 5]]);
      }));
      function fetchServerProgress() {
        return _fetchServerProgress.apply(this, arguments);
      }
      return fetchServerProgress;
    }()
    /**
     * Load progress from cookie
     */
    )
  }, {
    key: "loadFromCookie",
    value: function loadFromCookie() {
      try {
        var cookieValue = this.getCookie(this.cookieKey);
        if (cookieValue) {
          return JSON.parse(cookieValue);
        }
      } catch (error) {
        console.warn('Error loading progress from cookie:', error);
      }
      return null;
    }

    /**
     * Select the most recent progress data
     */
  }, {
    key: "selectMostRecent",
    value: function selectMostRecent(serverProgress, cookieProgress) {
      if (!serverProgress && !cookieProgress) return null;
      if (!serverProgress) return cookieProgress;
      if (!cookieProgress) return serverProgress;

      // Compare timestamps
      var serverTime = new Date(serverProgress.lastUpdated || 0).getTime();
      var cookieTime = cookieProgress.timestamp || 0;
      return serverTime > cookieTime ? serverProgress : cookieProgress;
    }

    /**
     * Restore progress data to tracker state
     */
  }, {
    key: "restoreProgress",
    value: function restoreProgress(progressData) {
      if (!progressData) return;

      // Restore watched segments
      if (progressData.segments && Array.isArray(progressData.segments)) {
        this.watchedSegments = new Set(progressData.segments);
      }

      // Restore percentages and position
      this.watchPercentage = progressData.percentage || 0;
      this.lastPosition = progressData.lastPosition || 0;
      this.totalWatchTime = progressData.totalWatchTime || this.watchedSegments.size;

      // Update display
      if (this.progressDisplay) {
        this.progressDisplay.textContent = Math.round(this.watchPercentage) + '%';
      }

      // Check completion status
      if (this.watchPercentage >= this.completionRequired) {
        this.isCompleted = true;
      }
    }

    /**
     * Save progress to both cookie and server
     */
  }, {
    key: "saveProgress",
    value: (function () {
      var _saveProgress = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee5() {
        var progressData;
        return _regenerator().w(function (_context5) {
          while (1) switch (_context5.n) {
            case 0:
              progressData = {
                segments: Array.from(this.watchedSegments),
                percentage: this.watchPercentage,
                lastPosition: this.lastPosition,
                totalWatchTime: this.totalWatchTime,
                timestamp: Date.now()
              }; // Save to cookie immediately (fast fallback)
              this.saveToCookie(progressData);

              // Save to server (background)
              if (this.courseId) {
                this.saveToServer(progressData);
              }
            case 1:
              return _context5.a(2);
          }
        }, _callee5, this);
      }));
      function saveProgress() {
        return _saveProgress.apply(this, arguments);
      }
      return saveProgress;
    }()
    /**
     * Save progress to cookie
     */
    )
  }, {
    key: "saveToCookie",
    value: function saveToCookie(progressData) {
      try {
        var cookieValue = JSON.stringify(progressData);
        this.setCookie(this.cookieKey, cookieValue, 7); // 7 days
      } catch (error) {
        console.warn('Error saving progress to cookie:', error);
      }
    }

    /**
     * Save progress to server via REST API
     */
  }, {
    key: "saveToServer",
    value: (function () {
      var _saveToServer = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee6(progressData) {
        var videoData, response, _t6;
        return _regenerator().w(function (_context6) {
          while (1) switch (_context6.p = _context6.n) {
            case 0:
              if (!(!this.lessonId || !this.courseId)) {
                _context6.n = 1;
                break;
              }
              return _context6.a(2);
            case 1:
              _context6.p = 1;
              // Add video metadata
              videoData = {
                course_id: this.courseId,
                segments: progressData.segments,
                percentage: progressData.percentage,
                lastPosition: progressData.lastPosition,
                totalWatchTime: progressData.totalWatchTime,
                videoType: this.videoType,
                videoId: this.videoId,
                duration: this.duration
              };
              _context6.n = 2;
              return fetch("".concat(this.restBase, "/splms/v1/lessons/").concat(this.lessonId, "/video-progress"), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                  'Content-Type': 'application/json'
                },
                body: JSON.stringify(videoData)
              });
            case 2:
              response = _context6.v;
              if (!response.ok) {
                console.warn('Failed to save video progress to server:', response.statusText);
              }
              _context6.n = 4;
              break;
            case 3:
              _context6.p = 3;
              _t6 = _context6.v;
              console.warn('Error saving progress to server:', _t6);
            case 4:
              return _context6.a(2);
          }
        }, _callee6, this, [[1, 3]]);
      }));
      function saveToServer(_x) {
        return _saveToServer.apply(this, arguments);
      }
      return saveToServer;
    }()
    /**
     * Resume video from last position
     */
    )
  }, {
    key: "resumeFromLastPosition",
    value: (function () {
      var _resumeFromLastPosition = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee7() {
        var _t7, _t8;
        return _regenerator().w(function (_context7) {
          while (1) switch (_context7.p = _context7.n) {
            case 0:
              if (!(!this.lastPosition || this.lastPosition < 10)) {
                _context7.n = 1;
                break;
              }
              return _context7.a(2);
            case 1:
              _context7.p = 1;
              _t7 = this.videoType;
              _context7.n = _t7 === 'youtube' ? 2 : _t7 === 'vimeo' ? 3 : _t7 === 'html5' ? 4 : 5;
              break;
            case 2:
              if (this.player && this.player.seekTo) {
                this.player.seekTo(this.lastPosition, true);
              }
              return _context7.a(3, 5);
            case 3:
              if (this.player && this.player.setCurrentTime) {
                this.player.setCurrentTime(this.lastPosition);
              }
              return _context7.a(3, 5);
            case 4:
              if (this.player) {
                this.player.currentTime = this.lastPosition;
              }
              return _context7.a(3, 5);
            case 5:
              _context7.n = 7;
              break;
            case 6:
              _context7.p = 6;
              _t8 = _context7.v;
              console.warn('Error resuming from last position:', _t8);
            case 7:
              return _context7.a(2);
          }
        }, _callee7, this, [[1, 6]]);
      }));
      function resumeFromLastPosition() {
        return _resumeFromLastPosition.apply(this, arguments);
      }
      return resumeFromLastPosition;
    }() // ========== Cookie Utility Methods ==========
    /**
     * Set a cookie
     */
    )
  }, {
    key: "setCookie",
    value: function setCookie(name, value, days) {
      var expires = new Date();
      expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
      document.cookie = "".concat(name, "=").concat(value, ";expires=").concat(expires.toUTCString(), ";path=/;SameSite=Strict");
    }

    /**
     * Get a cookie value
     */
  }, {
    key: "getCookie",
    value: function getCookie(name) {
      var nameEQ = name + '=';
      var ca = document.cookie.split(';');
      for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
      }
      return null;
    }
  }]);
}();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (SPLMSVideoTracker);

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
/*!***************************************************************!*\
  !*** ./src/js/frontend/modules/lesson-viewer/LessonViewer.js ***!
  \***************************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _VideoTracker_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./VideoTracker.js */ "./src/js/frontend/modules/lesson-viewer/VideoTracker.js");
/* harmony import */ var _LessonCompletion_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./LessonCompletion.js */ "./src/js/frontend/modules/lesson-viewer/LessonCompletion.js");
/* harmony import */ var _SidebarToggle_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./SidebarToggle.js */ "./src/js/frontend/modules/lesson-viewer/SidebarToggle.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Lesson Viewer - Main Controller
 *
 * Initializes and coordinates all fullscreen lesson viewer components.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */




var SPLMSLessonViewer = /*#__PURE__*/function () {
  function SPLMSLessonViewer() {
    _classCallCheck(this, SPLMSLessonViewer);
    this.container = document.getElementById('splms-lesson-fullscreen-container');
    if (!this.container) {
      return;
    }
    this.lessonId = this.container.dataset.lessonId;
    this.courseId = this.container.dataset.courseId;

    // Initialize components.
    this.videoTracker = null;
    this.lessonCompletion = null;
    this.sidebarToggle = null;
    this.init();
  }
  return _createClass(SPLMSLessonViewer, [{
    key: "init",
    value: function init() {
      // Initialize video tracker if video player exists.
      var videoContainer = document.querySelector('.splms-video-player-container');
      if (videoContainer) {
        this.videoTracker = new _VideoTracker_js__WEBPACK_IMPORTED_MODULE_0__["default"](videoContainer, this.lessonId);
      }

      // Initialize lesson completion handler.
      this.lessonCompletion = new _LessonCompletion_js__WEBPACK_IMPORTED_MODULE_1__["default"](this.lessonId, this.courseId, this.videoTracker);

      // Initialize sidebar toggle.
      this.sidebarToggle = new _SidebarToggle_js__WEBPACK_IMPORTED_MODULE_2__["default"]();

      // Handle mobile menu toggle.
      this.initMobileMenu();

      // Handle section collapse/expand.
      this.initSectionToggle();

      // Update next button state based on completion.
      this.updateNextButtonState();
    }
  }, {
    key: "initMobileMenu",
    value: function initMobileMenu() {
      var menuToggle = document.querySelector('.splms-mobile-menu-toggle');
      var sidebar = document.querySelector('.splms-fullscreen-sidebar');
      var menuIcon = menuToggle === null || menuToggle === void 0 ? void 0 : menuToggle.querySelector('.splms-menu-icon');
      var closeIcon = menuToggle === null || menuToggle === void 0 ? void 0 : menuToggle.querySelector('.splms-close-icon');
      if (!menuToggle || !sidebar) {
        return;
      }
      menuToggle.addEventListener('click', function () {
        var isOpen = sidebar.classList.contains('is-mobile-open');
        if (isOpen) {
          sidebar.classList.remove('is-mobile-open');
          menuToggle.setAttribute('aria-expanded', 'false');
          if (menuIcon) menuIcon.style.display = '';
          if (closeIcon) closeIcon.style.display = 'none';
        } else {
          sidebar.classList.add('is-mobile-open');
          menuToggle.setAttribute('aria-expanded', 'true');
          if (menuIcon) menuIcon.style.display = 'none';
          if (closeIcon) closeIcon.style.display = '';
        }
      });

      // Close sidebar when clicking outside on mobile.
      document.addEventListener('click', function (e) {
        if (window.innerWidth <= 768 && sidebar.classList.contains('is-mobile-open') && !sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
          sidebar.classList.remove('is-mobile-open');
          menuToggle.setAttribute('aria-expanded', 'false');
          if (menuIcon) menuIcon.style.display = '';
          if (closeIcon) closeIcon.style.display = 'none';
        }
      });
    }
  }, {
    key: "initSectionToggle",
    value: function initSectionToggle() {
      var sectionHeaders = document.querySelectorAll('.splms-section-header');
      sectionHeaders.forEach(function (header) {
        header.addEventListener('click', function (e) {
          e.preventDefault();
          var section = header.closest('.splms-curriculum-section');
          var items = section.querySelector('.splms-section-items');
          var isExpanded = header.getAttribute('aria-expanded') === 'true';
          if (isExpanded) {
            header.setAttribute('aria-expanded', 'false');
            items.style.display = 'none';
            section.classList.remove('is-expanded');
          } else {
            header.setAttribute('aria-expanded', 'true');
            items.style.display = 'flex';
            section.classList.add('is-expanded');
          }
        });
      });
    }
  }, {
    key: "updateNextButtonState",
    value: function updateNextButtonState() {
      var _this = this;
      // Listen for lesson completion events.
      document.addEventListener('splms:lessonCompleted', function () {
        var nextButton = document.querySelector('.splms-btn-next.is-disabled');
        if (nextButton) {
          var url = nextButton.dataset.url;
          if (url) {
            nextButton.classList.remove('is-disabled');
            nextButton.removeAttribute('disabled');
            nextButton.setAttribute('href', url);

            // Remove locked badge.
            var lockedBadge = nextButton.querySelector('.splms-locked-badge');
            if (lockedBadge) {
              lockedBadge.remove();
            }
          }
        }
      });

      // Listen for course completion events.
      document.addEventListener('splms:courseCompleted', function (event) {
        _this.handleCourseCompletion(event.detail);
      });
    }

    /**
     * Handle course completion navigation and UI updates
     */
  }, {
    key: "handleCourseCompletion",
    value: function handleCourseCompletion(data) {
      // Hide next button since course is complete
      var nextButton = document.querySelector('.splms-btn-next');
      if (nextButton) {
        nextButton.style.display = 'none';
      }

      // Add course completion actions
      this.addCourseCompletionActions(data);

      // Update sidebar to show course as completed
      var courseSidebar = document.querySelector('.splms-course-sidebar');
      if (courseSidebar) {
        courseSidebar.classList.add('course-completed');
      }
    }

    /**
     * Add completion actions like certificate download, back to dashboard
     */
  }, {
    key: "addCourseCompletionActions",
    value: function addCourseCompletionActions(data) {
      var buttonContainer = document.querySelector('.lesson-navigation, .splms-lesson-footer');
      if (!buttonContainer) return;

      // Create completion actions container
      var actionsContainer = document.createElement('div');
      actionsContainer.className = 'course-completion-actions';
      actionsContainer.style.cssText = "\n\t\t\tdisplay: flex;\n\t\t\tgap: 12px;\n\t\t\talign-items: center;\n\t\t\tmargin-top: 20px;\n\t\t\tpadding: 20px;\n\t\t\tbackground: var(--splms-background-secondary, #f8f9fa);\n\t\t\tborder-radius: var(--splms-border-radius-lg, 8px);\n\t\t\tborder: 1px solid var(--splms-border-color, #e9ecef);\n\t\t";

      // Back to course overview button
      var courseButton = document.createElement('a');
      courseButton.href = window.location.pathname.replace(/\/[^\/]+\/?$/, '/');
      courseButton.className = 'splms-btn splms-btn-secondary';
      courseButton.textContent = '← Back to Course';
      courseButton.style.cssText = "\n\t\t\tpadding: 12px 20px;\n\t\t\ttext-decoration: none;\n\t\t\tborder-radius: 6px;\n\t\t\tfont-weight: 500;\n\t\t";

      // Dashboard button
      var dashboardButton = document.createElement('a');
      dashboardButton.href = '/dashboard/';
      dashboardButton.className = 'splms-btn splms-btn-primary';
      dashboardButton.textContent = '🏠 Dashboard';
      dashboardButton.style.cssText = "\n\t\t\tpadding: 12px 20px;\n\t\t\ttext-decoration: none;\n\t\t\tborder-radius: 6px;\n\t\t\tfont-weight: 500;\n\t\t";
      actionsContainer.appendChild(courseButton);

      // Add certificate button if certificate was generated
      if (data.data && (data.data.certificate_generated || data.data.certificate_id)) {
        var certButton = document.createElement('a');
        certButton.href = this.getCertificateUrl(data);
        certButton.target = '_blank';
        certButton.className = 'splms-btn splms-btn-success';
        certButton.textContent = '🏆 View Certificate';
        certButton.style.cssText = "\n\t\t\t\tpadding: 12px 20px;\n\t\t\t\ttext-decoration: none;\n\t\t\t\tborder-radius: 6px;\n\t\t\t\tfont-weight: 500;\n\t\t\t\tbackground-color: var(--splms-success, #10b981);\n\t\t\t\tcolor: white;\n\t\t\t";
        actionsContainer.appendChild(certButton);
      }
      actionsContainer.appendChild(dashboardButton);

      // Add completion text
      var completionText = document.createElement('div');
      completionText.style.cssText = "\n\t\t\tflex: 1;\n\t\t\ttext-align: center;\n\t\t\tcolor: var(--splms-text-muted, #6b7280);\n\t\t\tfont-style: italic;\n\t\t";
      completionText.textContent = 'Course completed! Choose your next step.';

      // Insert completion text in the middle
      actionsContainer.insertBefore(completionText, dashboardButton);

      // Insert into page
      buttonContainer.appendChild(actionsContainer);
    }

    /**
     * Get certificate URL for current course
     */
  }, {
    key: "getCertificateUrl",
    value: function getCertificateUrl(data) {
      // Try to build certificate URL from course data
      if (data.courseId) {
        return "/certificate/?course=".concat(data.courseId);
      }

      // Fallback to dashboard certificates
      return '/dashboard/?tab=certificates';
    }
  }, {
    key: "destroy",
    value: function destroy() {
      if (this.videoTracker) {
        this.videoTracker.destroy();
      }
      if (this.lessonCompletion) {
        this.lessonCompletion.destroy();
      }
      if (this.sidebarToggle) {
        this.sidebarToggle.destroy();
      }
    }
  }]);
}(); // Initialize when DOM is ready.
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function () {
    window.splmsLessonViewer = new SPLMSLessonViewer();
  });
} else {
  window.splmsLessonViewer = new SPLMSLessonViewer();
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (SPLMSLessonViewer);
})();

/******/ })()
;
//# sourceMappingURL=lesson-viewer.js.map