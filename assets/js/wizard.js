/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/js/react-core/admin/pages/wizard/WizardPage.js"
/*!************************************************************!*\
  !*** ./src/js/react-core/admin/pages/wizard/WizardPage.js ***!
  \************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _components_WizardContainer__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./components/WizardContainer */ "./src/js/react-core/admin/pages/wizard/components/WizardContainer.js");
/* harmony import */ var _components_steps_WelcomeStep__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./components/steps/WelcomeStep */ "./src/js/react-core/admin/pages/wizard/components/steps/WelcomeStep.js");
/* harmony import */ var _components_steps_BasicSetupStep__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./components/steps/BasicSetupStep */ "./src/js/react-core/admin/pages/wizard/components/steps/BasicSetupStep.js");
/* harmony import */ var _components_steps_FinishStep__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./components/steps/FinishStep */ "./src/js/react-core/admin/pages/wizard/components/steps/FinishStep.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
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
/**
 * Main Wizard Page Component
 *
 * Main container for the SkillPulse LMS setup wizard.
 * Manages step navigation and coordinates with the license system.
 *
 * @since [SPLMS_VERSION]
 */









//import './styles/index.scss';

/**
 * Main Wizard Page Component
 */
var WizardPage = function WizardPage() {
  var _useState = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('welcome'),
    _useState2 = _slicedToArray(_useState, 2),
    currentStep = _useState2[0],
    setCurrentStep = _useState2[1];
  var _useState3 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false),
    _useState4 = _slicedToArray(_useState3, 2),
    loading = _useState4[0],
    setLoading = _useState4[1];
  var _useState5 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(null),
    _useState6 = _slicedToArray(_useState5, 2),
    error = _useState6[0],
    setError = _useState6[1];
  var _useState7 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)({}),
    _useState8 = _slicedToArray(_useState7, 2),
    stepData = _useState8[0],
    setStepData = _useState8[1];
  var _useState9 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false),
    _useState0 = _slicedToArray(_useState9, 2),
    transitioning = _useState0[0],
    setTransitioning = _useState0[1];

  // Get wizard data from localized script
  var _window = window,
    splmsWizardData = _window.splmsWizardData;

  // Steps configuration
  var steps = {
    welcome: {
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Welcome', 'skillpulse-lms'),
      component: _components_steps_WelcomeStep__WEBPACK_IMPORTED_MODULE_4__["default"]
    },
    'basic-setup': {
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Basic Setup', 'skillpulse-lms'),
      component: _components_steps_BasicSetupStep__WEBPACK_IMPORTED_MODULE_5__["default"]
    },
    finish: {
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Complete', 'skillpulse-lms'),
      component: _components_steps_FinishStep__WEBPACK_IMPORTED_MODULE_6__["default"]
    }
  };
  var stepOrder = ['welcome', 'basic-setup', 'finish'];

  /**
   * Initialize wizard state
   */
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    if (splmsWizardData !== null && splmsWizardData !== void 0 && splmsWizardData.currentStep) {
      setCurrentStep(splmsWizardData.currentStep);
    }
  }, []);

  /**
   * Navigate to next step
   */
  var nextStep = /*#__PURE__*/function () {
    var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
      var stepDataToSave,
        currentIndex,
        nextIndex,
        _args = arguments;
      return _regenerator().w(function (_context) {
        while (1) switch (_context.n) {
          case 0:
            stepDataToSave = _args.length > 0 && _args[0] !== undefined ? _args[0] : {};
            currentIndex = stepOrder.indexOf(currentStep);
            nextIndex = currentIndex + 1; // Save current step data if provided
            if (!(Object.keys(stepDataToSave).length > 0)) {
              _context.n = 1;
              break;
            }
            _context.n = 1;
            return _saveStepData(currentStep, stepDataToSave);
          case 1:
            if (!(nextIndex < stepOrder.length)) {
              _context.n = 2;
              break;
            }
            setCurrentStep(stepOrder[nextIndex]);
            _context.n = 3;
            break;
          case 2:
            _context.n = 3;
            return completeWizard();
          case 3:
            return _context.a(2);
        }
      }, _callee);
    }));
    return function nextStep() {
      return _ref.apply(this, arguments);
    };
  }();

  /**
   * Navigate to previous step
   */
  var prevStep = function prevStep() {
    var currentIndex = stepOrder.indexOf(currentStep);
    var prevIndex = currentIndex - 1;
    if (prevIndex >= 0) {
      setCurrentStep(stepOrder[prevIndex]);
    }
  };

  /**
   * Save step data via AJAX
   */
  var _saveStepData = /*#__PURE__*/function () {
    var _ref2 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(step, data) {
      var response, _t;
      return _regenerator().w(function (_context2) {
        while (1) switch (_context2.p = _context2.n) {
          case 0:
            setLoading(true);
            setError(null);
            _context2.p = 1;
            _context2.n = 2;
            return wp.ajax.post('splms_wizard_save_step', {
              step: step,
              data: data,
              nonce: splmsWizardData.nonce
            });
          case 2:
            response = _context2.v;
            // Update local state
            setStepData(function (prev) {
              return _objectSpread(_objectSpread({}, prev), {}, _defineProperty({}, step, data));
            });
            return _context2.a(2, response);
          case 3:
            _context2.p = 3;
            _t = _context2.v;
            setError(_t.message || (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Failed to save step data.', 'skillpulse-lms'));
            throw _t;
          case 4:
            _context2.p = 4;
            setLoading(false);
            return _context2.f(4);
          case 5:
            return _context2.a(2);
        }
      }, _callee2, null, [[1, 3, 4, 5]]);
    }));
    return function saveStepData(_x, _x2) {
      return _ref2.apply(this, arguments);
    };
  }();

  /**
   * Complete wizard
   */
  var completeWizard = /*#__PURE__*/function () {
    var _ref3 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee3() {
      var response, _t2;
      return _regenerator().w(function (_context3) {
        while (1) switch (_context3.p = _context3.n) {
          case 0:
            setLoading(true);
            setError(null);
            _context3.p = 1;
            _context3.n = 2;
            return wp.ajax.post('splms_wizard_complete', {
              nonce: splmsWizardData.nonce
            });
          case 2:
            response = _context3.v;
            // Redirect to dashboard
            if (response.redirect_url) {
              window.location.href = response.redirect_url;
            }
            _context3.n = 4;
            break;
          case 3:
            _context3.p = 3;
            _t2 = _context3.v;
            setError(_t2.message || (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Failed to complete wizard.', 'skillpulse-lms'));
          case 4:
            _context3.p = 4;
            setLoading(false);
            return _context3.f(4);
          case 5:
            return _context3.a(2);
        }
      }, _callee3, null, [[1, 3, 4, 5]]);
    }));
    return function completeWizard() {
      return _ref3.apply(this, arguments);
    };
  }();

  /**
   * Skip wizard
   */
  var skipWizard = /*#__PURE__*/function () {
    var _ref4 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee4() {
      var response, _t3;
      return _regenerator().w(function (_context4) {
        while (1) switch (_context4.p = _context4.n) {
          case 0:
            if (!confirm((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Are you sure you want to skip the setup wizard? You can access these settings later.', 'skillpulse-lms'))) {
              _context4.n = 5;
              break;
            }
            setLoading(true);
            setError(null);
            _context4.p = 1;
            _context4.n = 2;
            return wp.ajax.post('splms_wizard_skip', {
              nonce: splmsWizardData.nonce
            });
          case 2:
            response = _context4.v;
            // Redirect to dashboard
            if (response.redirect_url) {
              window.location.href = response.redirect_url;
            }
            _context4.n = 4;
            break;
          case 3:
            _context4.p = 3;
            _t3 = _context4.v;
            setError(_t3.message || (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Failed to skip wizard.', 'skillpulse-lms'));
          case 4:
            _context4.p = 4;
            setLoading(false);
            return _context4.f(4);
          case 5:
            return _context4.a(2);
        }
      }, _callee4, null, [[1, 3, 4, 5]]);
    }));
    return function skipWizard() {
      return _ref4.apply(this, arguments);
    };
  }();

  /**
   * Get current step component
   */
  var getCurrentStepComponent = function getCurrentStepComponent() {
    var _steps$currentStep;
    var StepComponent = (_steps$currentStep = steps[currentStep]) === null || _steps$currentStep === void 0 ? void 0 : _steps$currentStep.component;
    if (!StepComponent) {
      return /*#__PURE__*/React.createElement("div", {
        className: "splms-wizard-error"
      }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Notice, {
        status: "error",
        isDismissible: false
      }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Invalid wizard step.', 'skillpulse-lms')));
    }
    return /*#__PURE__*/React.createElement(StepComponent, {
      onNext: nextStep,
      onBack: prevStep,
      onSkip: skipWizard,
      stepData: stepData[currentStep] || {},
      saveStepData: function saveStepData(data) {
        return _saveStepData(currentStep, data);
      },
      loading: loading,
      error: error
    });
  };
  return /*#__PURE__*/React.createElement("div", {
    className: "skillpulse-lms-user-admin"
  }, /*#__PURE__*/React.createElement("div", {
    className: "splms-container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "splms-wizard-page"
  }, error && /*#__PURE__*/React.createElement("div", {
    className: "splms-wizard-error"
  }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Notice, {
    status: "error",
    onRemove: function onRemove() {
      return setError(null);
    }
  }, error)), loading && /*#__PURE__*/React.createElement("div", {
    className: "splms-loading-container"
  }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Spinner, null), /*#__PURE__*/React.createElement("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Processing...', 'skillpulse-lms'))), /*#__PURE__*/React.createElement(_components_WizardContainer__WEBPACK_IMPORTED_MODULE_3__["default"], {
    currentStep: currentStep,
    steps: stepOrder,
    stepTitles: steps,
    onSkip: skipWizard
  }, getCurrentStepComponent()))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (WizardPage);

/***/ },

/***/ "./src/js/react-core/admin/pages/wizard/components/WizardContainer.js"
/*!****************************************************************************!*\
  !*** ./src/js/react-core/admin/pages/wizard/components/WizardContainer.js ***!
  \****************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _components_SplmsIcon__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../components/SplmsIcon */ "./src/js/react-core/components/SplmsIcon/index.js");
/* harmony import */ var _components_BrandLogo__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../components/BrandLogo */ "./src/js/react-core/components/BrandLogo.js");
/**
 * Wizard Container Component
 *
 * Provides the main layout and navigation for the setup wizard.
 * Includes progress indicator, header, and step content area.
 *
 * @since [SPLMS_VERSION]
 */






/**
 * Wizard Container Component
 */
var WizardContainer = function WizardContainer(_ref) {
  var children = _ref.children,
    currentStep = _ref.currentStep,
    steps = _ref.steps,
    stepTitles = _ref.stepTitles,
    onSkip = _ref.onSkip;
  /**
   * Get current step index
   */
  var getCurrentStepIndex = function getCurrentStepIndex() {
    return steps.indexOf(currentStep) + 1;
  };

  /**
   * Get total steps count
   */
  var getTotalSteps = function getTotalSteps() {
    return steps.length;
  };

  /**
   * Render progress indicator
   */
  var renderProgressIndicator = function renderProgressIndicator() {
    var currentIndex = getCurrentStepIndex();
    var totalSteps = getTotalSteps();
    return /*#__PURE__*/React.createElement("div", {
      className: "splms-wizard-progress"
    }, /*#__PURE__*/React.createElement("div", {
      className: "splms-wizard-progress-bar",
      role: "list"
    }, steps.map(function (step, index) {
      var _stepTitles$step;
      var stepStatus = index < currentIndex - 1 ? 'completed' : index === currentIndex - 1 ? 'current' : 'pending';
      var stepTitle = ((_stepTitles$step = stepTitles[step]) === null || _stepTitles$step === void 0 ? void 0 : _stepTitles$step.title) || step;
      return /*#__PURE__*/React.createElement("div", {
        key: step,
        className: "splms-wizard-progress-step ".concat(stepStatus),
        role: "listitem",
        "aria-current": 'current' === stepStatus ? 'step' : undefined,
        "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.sprintf)(/* translators: 1: Step title, 2: Step status. */
        (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('%1$s - %2$s', 'skillpulse-lms'), stepTitle, stepStatus)
      }, /*#__PURE__*/React.createElement("div", {
        className: "splms-wizard-progress-circle",
        "aria-hidden": "true"
      }, 'completed' === stepStatus ? /*#__PURE__*/React.createElement(_components_SplmsIcon__WEBPACK_IMPORTED_MODULE_2__.SplmsIcon, {
        mode: "wp",
        name: "yes",
        size: 16
      }) : /*#__PURE__*/React.createElement("span", null, index + 1)), /*#__PURE__*/React.createElement("div", {
        className: "splms-wizard-progress-label"
      }, stepTitle));
    })), /*#__PURE__*/React.createElement("div", {
      className: "splms-wizard-progress-text",
      "aria-live": "polite"
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.sprintf)(/* translators: 1: Current step number, 2: Total steps. */
    (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Step %1$d of %2$d', 'skillpulse-lms'), currentIndex, totalSteps)));
  };
  return /*#__PURE__*/React.createElement("div", {
    className: "splms-wizard-container"
  }, /*#__PURE__*/React.createElement("header", {
    className: "splms-wizard-header"
  }, /*#__PURE__*/React.createElement("div", {
    className: "splms-wizard-header-content"
  }, /*#__PURE__*/React.createElement("div", {
    className: "splms-wizard-header-logo"
  }, /*#__PURE__*/React.createElement(_components_BrandLogo__WEBPACK_IMPORTED_MODULE_3__["default"], null), /*#__PURE__*/React.createElement("div", {
    className: "splms-wizard-header-separator"
  }), /*#__PURE__*/React.createElement("h1", {
    className: "splms-wizard-header-title"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Setup Wizard', 'skillpulse-lms'))), /*#__PURE__*/React.createElement("div", {
    className: "splms-wizard-header-actions"
  }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
    isLink: true,
    onClick: onSkip,
    className: "splms-wizard-skip-button"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Skip Setup', 'skillpulse-lms')), /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
    isLink: true,
    href: "https://skillpulselms.com/docs/setup",
    target: "_blank",
    rel: "noopener noreferrer",
    className: "splms-wizard-help-button"
  }, /*#__PURE__*/React.createElement(_components_SplmsIcon__WEBPACK_IMPORTED_MODULE_2__.SplmsIcon, {
    name: "help",
    size: 16
  }), (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Help', 'skillpulse-lms'))))), renderProgressIndicator(), /*#__PURE__*/React.createElement("main", {
    className: "splms-content"
  }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Card, null, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.CardBody, null, children))), /*#__PURE__*/React.createElement("footer", {
    className: "splms-wizard-footer"
  }, /*#__PURE__*/React.createElement("div", {
    className: "splms-wizard-footer-content"
  }, /*#__PURE__*/React.createElement("p", {
    className: "splms-wizard-footer-text"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Need help? Visit our', 'skillpulse-lms'), ' ', /*#__PURE__*/React.createElement("a", {
    href: "https://skillpulselms.com/docs",
    target: "_blank",
    rel: "noopener noreferrer"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('documentation', 'skillpulse-lms')), ' ', (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('or', 'skillpulse-lms'), ' ', /*#__PURE__*/React.createElement("a", {
    href: "https://skillpulselms.com/support",
    target: "_blank",
    rel: "noopener noreferrer"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('contact support', 'skillpulse-lms'))))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (WizardContainer);

/***/ },

/***/ "./src/js/react-core/admin/pages/wizard/components/steps/BasicSetupStep.js"
/*!*********************************************************************************!*\
  !*** ./src/js/react-core/admin/pages/wizard/components/steps/BasicSetupStep.js ***!
  \*********************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _components_SplmsIcon__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../components/SplmsIcon */ "./src/js/react-core/components/SplmsIcon/index.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
/**
 * Basic Setup Step Component
 *
 * Collects essential configuration settings for the LMS.
 * Simple form that saves basic site configuration.
 *
 * @since [SPLMS_VERSION]
 */






/**
 * Basic Setup Step Component
 */
var BasicSetupStep = function BasicSetupStep(_ref) {
  var onNext = _ref.onNext,
    onBack = _ref.onBack,
    stepData = _ref.stepData,
    loading = _ref.loading,
    error = _ref.error;
  var _useState = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)({
      site_name: '',
      admin_email: '',
      timezone: 'UTC'
    }),
    _useState2 = _slicedToArray(_useState, 2),
    formData = _useState2[0],
    setFormData = _useState2[1];
  var _useState3 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)({}),
    _useState4 = _slicedToArray(_useState3, 2),
    formErrors = _useState4[0],
    setFormErrors = _useState4[1];
  var _useState5 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false),
    _useState6 = _slicedToArray(_useState5, 2),
    isValid = _useState6[0],
    setIsValid = _useState6[1];

  // Get initial data from window object
  var _window = window,
    splmsWizardData = _window.splmsWizardData;

  /**
   * Initialize form data
   */
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    var initialData = {
      site_name: stepData.site_name || (splmsWizardData === null || splmsWizardData === void 0 ? void 0 : splmsWizardData.siteName) || '',
      admin_email: stepData.admin_email || (splmsWizardData === null || splmsWizardData === void 0 ? void 0 : splmsWizardData.adminEmail) || '',
      timezone: stepData.timezone || (splmsWizardData === null || splmsWizardData === void 0 ? void 0 : splmsWizardData.timezone) || 'UTC'
    };
    setFormData(initialData);

    // Validate the initial data to enable/disable continue button
    var errors = validateForm(initialData);
    setFormErrors(errors);
    setIsValid(Object.keys(errors).length === 0);
  }, [stepData, splmsWizardData]);

  /**
   * WordPress timezone options (from WordPress settings)
   */
  var timezones = (splmsWizardData === null || splmsWizardData === void 0 ? void 0 : splmsWizardData.timezones) || [{
    value: 'UTC',
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('UTC', 'skillpulse-lms')
  }, {
    value: 'America/New_York',
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Eastern Time (US)', 'skillpulse-lms')
  }, {
    value: 'America/Chicago',
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Central Time (US)', 'skillpulse-lms')
  }, {
    value: 'America/Denver',
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Mountain Time (US)', 'skillpulse-lms')
  }, {
    value: 'America/Los_Angeles',
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Pacific Time (US)', 'skillpulse-lms')
  }, {
    value: 'Europe/London',
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('London', 'skillpulse-lms')
  }, {
    value: 'Europe/Paris',
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Paris', 'skillpulse-lms')
  }, {
    value: 'Europe/Berlin',
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Berlin', 'skillpulse-lms')
  }, {
    value: 'Asia/Tokyo',
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Tokyo', 'skillpulse-lms')
  }, {
    value: 'Asia/Shanghai',
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Shanghai', 'skillpulse-lms')
  }, {
    value: 'Australia/Sydney',
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Sydney', 'skillpulse-lms')
  }];

  /**
   * Validate form
   */
  var validateForm = function validateForm(data) {
    var errors = {};
    if (!data.site_name.trim()) {
      errors.site_name = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Site name is required.', 'skillpulse-lms');
    }
    if (!data.admin_email.trim()) {
      errors.admin_email = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Admin email is required.', 'skillpulse-lms');
    } else if (!data.admin_email.includes('@')) {
      errors.admin_email = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Please enter a valid email address.', 'skillpulse-lms');
    }
    return errors;
  };

  /**
   * Handle form field changes
   */
  var handleFieldChange = function handleFieldChange(field, value) {
    var newFormData = _objectSpread(_objectSpread({}, formData), {}, _defineProperty({}, field, value));
    setFormData(newFormData);

    // Validate and update errors
    var errors = validateForm(newFormData);
    setFormErrors(errors);
    setIsValid(Object.keys(errors).length === 0);
  };

  /**
   * Handle form submission
   */
  var handleContinue = function handleContinue() {
    var errors = validateForm(formData);
    setFormErrors(errors);
    if (Object.keys(errors).length === 0) {
      onNext(_objectSpread(_objectSpread({}, formData), {}, {
        completed_at: Date.now()
      }));
    }
  };

  /**
   * Handle skip this step
   */
  var handleSkip = function handleSkip() {
    onNext({
      skipped: true,
      completed_at: Date.now()
    });
  };
  return /*#__PURE__*/React.createElement("div", {
    className: "splms-wizard-step splms-basic-setup-step"
  }, /*#__PURE__*/React.createElement("div", {
    className: "splms-basic-setup-header"
  }, /*#__PURE__*/React.createElement("h2", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Basic Configuration', 'skillpulse-lms')), /*#__PURE__*/React.createElement("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Let\'s configure your LMS basics. You can change these settings anytime later.', 'skillpulse-lms'))), error && /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Notice, {
    status: "error",
    isDismissible: false
  }, error), /*#__PURE__*/React.createElement("div", {
    className: "splms-basic-setup-content"
  }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Card, null, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.CardBody, null, /*#__PURE__*/React.createElement("div", {
    className: "splms-basic-setup-form"
  }, /*#__PURE__*/React.createElement("div", {
    className: "splms-form-field"
  }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('LMS Name', 'skillpulse-lms'),
    help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('This will be displayed throughout your learning management system.', 'skillpulse-lms'),
    value: formData.site_name,
    onChange: function onChange(value) {
      return handleFieldChange('site_name', value);
    },
    placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('My Learning Academy', 'skillpulse-lms'),
    className: formErrors.site_name ? 'has-error' : '',
    __next40pxDefaultSize: true
  }), formErrors.site_name && /*#__PURE__*/React.createElement("div", {
    className: "splms-form-error"
  }, formErrors.site_name)), /*#__PURE__*/React.createElement("div", {
    className: "splms-form-field"
  }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Admin Email', 'skillpulse-lms'),
    help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('This email will receive important notifications about your LMS.', 'skillpulse-lms'),
    type: "email",
    value: formData.admin_email,
    onChange: function onChange(value) {
      return handleFieldChange('admin_email', value);
    },
    placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('admin@example.com', 'skillpulse-lms'),
    className: formErrors.admin_email ? 'has-error' : '',
    __next40pxDefaultSize: true
  }), formErrors.admin_email && /*#__PURE__*/React.createElement("div", {
    className: "splms-form-error"
  }, formErrors.admin_email)), /*#__PURE__*/React.createElement("div", {
    className: "splms-form-field"
  }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Timezone', 'skillpulse-lms'),
    help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('This affects course schedules and notification timing.', 'skillpulse-lms'),
    value: formData.timezone,
    options: timezones,
    onChange: function onChange(value) {
      return handleFieldChange('timezone', value);
    },
    __next40pxDefaultSize: true
  }))))), /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Card, {
    className: "splms-basic-setup-info"
  }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.CardBody, null, /*#__PURE__*/React.createElement("div", {
    className: "splms-basic-setup-info-content"
  }, /*#__PURE__*/React.createElement(_components_SplmsIcon__WEBPACK_IMPORTED_MODULE_3__.SplmsIcon, {
    name: "info",
    size: 20
  }), /*#__PURE__*/React.createElement("div", {
    className: "splms-basic-setup-info-text"
  }, /*#__PURE__*/React.createElement("strong", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Don\'t worry!', 'skillpulse-lms')), /*#__PURE__*/React.createElement("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('You can change all of these settings later in the admin panel under Settings > General.', 'skillpulse-lms')))))), /*#__PURE__*/React.createElement("div", {
    className: "splms-wizard-step-actions"
  }, /*#__PURE__*/React.createElement("div", {
    className: "splms-wizard-step-actions-left"
  }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
    isLink: true,
    onClick: onBack,
    disabled: loading
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('← Back', 'skillpulse-lms'))), /*#__PURE__*/React.createElement("div", {
    className: "splms-wizard-step-actions-right"
  }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
    isLink: true,
    onClick: handleSkip,
    disabled: loading,
    className: "splms-wizard-skip-button"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Skip This Step', 'skillpulse-lms')), /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
    isPrimary: true,
    size: "large",
    onClick: handleContinue,
    disabled: loading || !isValid,
    className: "splms-wizard-continue-button"
  }, loading ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Saving...', 'skillpulse-lms') : /*#__PURE__*/React.createElement(React.Fragment, null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Continue', 'skillpulse-lms'), /*#__PURE__*/React.createElement(_components_SplmsIcon__WEBPACK_IMPORTED_MODULE_3__.SplmsIcon, {
    name: "arrowRight",
    size: 16
  })))))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (BasicSetupStep);

/***/ },

/***/ "./src/js/react-core/admin/pages/wizard/components/steps/FinishStep.js"
/*!*****************************************************************************!*\
  !*** ./src/js/react-core/admin/pages/wizard/components/steps/FinishStep.js ***!
  \*****************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _utility_url__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../../../../utility/url */ "./src/js/react-core/utility/url.js");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _components_SplmsIcon__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../components/SplmsIcon */ "./src/js/react-core/components/SplmsIcon/index.js");
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
/**
 * Finish Step Component
 *
 * Final step of the setup wizard. Shows completion message,
 * summary of what was accomplished, and clear next action.
 * Streamlined for better user experience and business focus.
 *
 * @since [SPLMS_VERSION]
 */






/**
 * Finish Step Component
 */
var FinishStep = function FinishStep(_ref) {
  var onNext = _ref.onNext,
    stepData = _ref.stepData,
    loading = _ref.loading,
    wizardData = _ref.wizardData;
  var _window = window,
    splmsWizardData = _window.splmsWizardData;

  /**
   * Get setup status and personalized messaging
   */
  var getSetupStatus = function getSetupStatus() {
    return {
      type: 'free',
      icon: 'yes-alt',
      color: 'primary',
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Setup Complete!', 'skillpulse-lms'),
      message: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Your LMS is ready. Start creating courses and enrolling students.', 'skillpulse-lms'),
      nextStep: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Create your first course', 'skillpulse-lms')
    };
  };

  /**
   * Handle going to dashboard
   */
  var handleGoToDashboard = /*#__PURE__*/function () {
    var _ref2 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
      var response, _ref3, siteUrl, mainUrl, _ref4, _siteUrl, _mainUrl, _t;
      return _regenerator().w(function (_context) {
        while (1) switch (_context.p = _context.n) {
          case 0:
            _context.p = 0;
            _context.n = 1;
            return wp.ajax.post('splms_wizard_complete', {
              nonce: splmsWizardData.nonce
            });
          case 1:
            response = _context.v;
            // Redirect to dashboard
            if (response.redirect_url) {
              window.location.href = response.redirect_url;
            } else {
              // Fallback redirect to dashboard
              _ref3 = splmsWizardData || {}, siteUrl = _ref3.siteUrl;
              mainUrl = splmsWizardData === null || splmsWizardData === void 0 ? void 0 : splmsWizardData.mainUrl;
              window.location.href = mainUrl;
            }
            _context.n = 3;
            break;
          case 2:
            _context.p = 2;
            _t = _context.v;
            console.error('Failed to complete wizard:', _t);
            // Fallback redirect even if completion fails
            _ref4 = splmsWizardData || {}, _siteUrl = _ref4.siteUrl;
            _mainUrl = splmsWizardData === null || splmsWizardData === void 0 ? void 0 : splmsWizardData.mainUrl;
            window.location.href = _mainUrl;
          case 3:
            return _context.a(2);
        }
      }, _callee, null, [[0, 2]]);
    }));
    return function handleGoToDashboard() {
      return _ref2.apply(this, arguments);
    };
  }();

  /**
   * Quick actions for next steps
   */
  var getQuickActions = function getQuickActions() {
    var _ref5 = splmsWizardData || {},
      siteUrl = _ref5.siteUrl;
    var baseUrl = siteUrl || (0,_utility_url__WEBPACK_IMPORTED_MODULE_1__.getSiteUrl)();
    return [{
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Manage Students', 'skillpulse-lms'),
      description: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Add students and manage enrollments', 'skillpulse-lms'),
      icon: 'groups',
      url: (splmsWizardData === null || splmsWizardData === void 0 ? void 0 : splmsWizardData.dashboardUrl) || "".concat(baseUrl, "/wp-admin/admin.php?page=skillpulse-lms"),
      primary: false
    }, {
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('View Documentation', 'skillpulse-lms'),
      description: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Learn more about SkillPulse LMS features', 'skillpulse-lms'),
      icon: 'book',
      url: 'https://skillpulselms.com/docs',
      external: true,
      primary: false
    }];
  };
  var setupStatus = getSetupStatus();
  var quickActions = getQuickActions();
  return /*#__PURE__*/React.createElement("div", {
    className: "splms-wizard-step splms-finish-step"
  }, /*#__PURE__*/React.createElement("div", {
    className: "splms-finish-hero"
  }, /*#__PURE__*/React.createElement("div", {
    className: "splms-finish-icon"
  }, /*#__PURE__*/React.createElement(_components_SplmsIcon__WEBPACK_IMPORTED_MODULE_3__.SplmsIcon, {
    name: "yes-alt",
    size: 64,
    className: "splms-finish-success-icon"
  })), /*#__PURE__*/React.createElement("h1", {
    className: "splms-finish-title"
  }, "\uD83C\uDF89 ", (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Setup Complete!', 'skillpulse-lms')), /*#__PURE__*/React.createElement("p", {
    className: "splms-finish-subtitle"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Your SkillPulse LMS is ready to go. Let\'s start building amazing learning experiences!', 'skillpulse-lms'))), /*#__PURE__*/React.createElement("div", {
    className: "splms-finish-status"
  }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Card, {
    className: "splms-status-card splms-status-".concat(setupStatus.type)
  }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.CardBody, null, /*#__PURE__*/React.createElement("div", {
    className: "splms-finish-status-content"
  }, /*#__PURE__*/React.createElement("div", {
    className: "splms-finish-status-icon"
  }, /*#__PURE__*/React.createElement(_components_SplmsIcon__WEBPACK_IMPORTED_MODULE_3__.SplmsIcon, {
    name: setupStatus.icon,
    size: 32
  })), /*#__PURE__*/React.createElement("div", {
    className: "splms-finish-status-details"
  }, /*#__PURE__*/React.createElement("h3", {
    className: "splms-finish-status-title"
  }, setupStatus.title), /*#__PURE__*/React.createElement("p", {
    className: "splms-finish-status-message"
  }, setupStatus.message), /*#__PURE__*/React.createElement("p", {
    className: "splms-finish-status-next"
  }, setupStatus.nextStep)))))), /*#__PURE__*/React.createElement("div", {
    className: "splms-finish-primary-action"
  }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
    isPrimary: true,
    size: "large",
    href: (0,_utility_url__WEBPACK_IMPORTED_MODULE_1__.getPostTypeCreateUrl)('sp-course'),
    className: "splms-finish-primary-button"
  }, /*#__PURE__*/React.createElement(_components_SplmsIcon__WEBPACK_IMPORTED_MODULE_3__.SplmsIcon, {
    name: "book",
    size: 16
  }), (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Create Your First Course', 'skillpulse-lms'))), /*#__PURE__*/React.createElement("div", {
    className: "splms-finish-actions"
  }, /*#__PURE__*/React.createElement("h2", {
    className: "splms-finish-actions-title"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Quick Actions', 'skillpulse-lms')), /*#__PURE__*/React.createElement("div", {
    className: "splms-finish-actions-grid"
  }, quickActions.map(function (action, index) {
    return /*#__PURE__*/React.createElement("div", {
      key: index,
      className: "splms-finish-action-item"
    }, /*#__PURE__*/React.createElement("div", {
      className: "splms-finish-action-icon"
    }, /*#__PURE__*/React.createElement(_components_SplmsIcon__WEBPACK_IMPORTED_MODULE_3__.SplmsIcon, {
      name: action.icon,
      size: 20
    })), /*#__PURE__*/React.createElement("div", {
      className: "splms-finish-action-content"
    }, /*#__PURE__*/React.createElement("h4", {
      className: "splms-finish-action-title"
    }, action.title), /*#__PURE__*/React.createElement("p", {
      className: "splms-finish-action-description"
    }, action.description)), /*#__PURE__*/React.createElement("div", {
      className: "splms-finish-action-button"
    }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
      isLink: true,
      href: action.url,
      target: action.external ? '_blank' : undefined,
      rel: action.external ? 'noopener noreferrer' : undefined
    }, action.external ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Learn More', 'skillpulse-lms') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Go', 'skillpulse-lms'), action.external && /*#__PURE__*/React.createElement(_components_SplmsIcon__WEBPACK_IMPORTED_MODULE_3__.SplmsIcon, {
      name: "link",
      size: 16
    }))));
  }))), /*#__PURE__*/React.createElement("div", {
    className: "splms-finish-secondary"
  }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
    isSecondary: true,
    size: "large",
    onClick: handleGoToDashboard,
    disabled: loading,
    className: "splms-finish-dashboard-button"
  }, /*#__PURE__*/React.createElement(_components_SplmsIcon__WEBPACK_IMPORTED_MODULE_3__.SplmsIcon, {
    mode: "wp",
    name: "dashboard",
    size: 16
  }), (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Go to Dashboard', 'skillpulse-lms')), /*#__PURE__*/React.createElement("p", {
    className: "splms-finish-help-text"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Need help getting started?', 'skillpulse-lms'), ' ', /*#__PURE__*/React.createElement("a", {
    href: "https://skillpulselms.com/docs/getting-started",
    target: "_blank",
    rel: "noopener noreferrer"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Check out our getting started guide', 'skillpulse-lms')))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (FinishStep);

/***/ },

/***/ "./src/js/react-core/admin/pages/wizard/components/steps/WelcomeStep.js"
/*!******************************************************************************!*\
  !*** ./src/js/react-core/admin/pages/wizard/components/steps/WelcomeStep.js ***!
  \******************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _components_SplmsIcon__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../components/SplmsIcon */ "./src/js/react-core/components/SplmsIcon/index.js");
/**
 * Welcome Step Component
 *
 * First step of the setup wizard. Provides introduction and overview
 * of what the wizard will accomplish.
 *
 * @since [SPLMS_VERSION]
 */





/**
 * Welcome Step Component
 */
var WelcomeStep = function WelcomeStep(_ref) {
  var onNext = _ref.onNext,
    loading = _ref.loading;
  /**
   * Handle getting started
   */
  var handleGetStarted = function handleGetStarted() {
    // Save that welcome step was viewed
    onNext({
      viewed: true,
      viewed_at: Date.now()
    });
  };
  return /*#__PURE__*/React.createElement("div", {
    className: "splms-wizard-step splms-welcome-step"
  }, /*#__PURE__*/React.createElement("div", {
    className: "splms-welcome-hero"
  }, /*#__PURE__*/React.createElement("div", {
    className: "splms-welcome-icon"
  }, /*#__PURE__*/React.createElement(_components_SplmsIcon__WEBPACK_IMPORTED_MODULE_2__.SplmsIcon, {
    mode: "wp",
    name: "welcome-learn-more",
    size: 64
  })), /*#__PURE__*/React.createElement("h1", {
    className: "splms-welcome-title"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Welcome to SkillPulse LMS!', 'skillpulse-lms')), /*#__PURE__*/React.createElement("p", {
    className: "splms-welcome-subtitle"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Let\'s get your learning management system up and running in just a few quick steps.', 'skillpulse-lms'))), /*#__PURE__*/React.createElement("div", {
    className: "splms-welcome-features"
  }, /*#__PURE__*/React.createElement("h2", {
    className: "splms-welcome-features-title"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('This wizard will help you:', 'skillpulse-lms')), /*#__PURE__*/React.createElement("div", {
    className: "splms-welcome-features-grid"
  }, /*#__PURE__*/React.createElement("div", {
    className: "splms-welcome-feature"
  }, /*#__PURE__*/React.createElement("div", {
    className: "splms-welcome-feature-icon"
  }, /*#__PURE__*/React.createElement(_components_SplmsIcon__WEBPACK_IMPORTED_MODULE_2__.SplmsIcon, {
    mode: "wp",
    name: "admin-network",
    size: 24
  })), /*#__PURE__*/React.createElement("div", {
    className: "splms-welcome-feature-content"
  }, /*#__PURE__*/React.createElement("h3", {
    className: "splms-welcome-feature-title"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Create your first course', 'skillpulse-lms')), /*#__PURE__*/React.createElement("p", {
    className: "splms-welcome-feature-description"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Start building courses with lessons, quizzes, and certificates', 'skillpulse-lms')))), /*#__PURE__*/React.createElement("div", {
    className: "splms-welcome-feature"
  }, /*#__PURE__*/React.createElement("div", {
    className: "splms-welcome-feature-icon"
  }, /*#__PURE__*/React.createElement(_components_SplmsIcon__WEBPACK_IMPORTED_MODULE_2__.SplmsIcon, {
    name: "settings",
    size: 24
  })), /*#__PURE__*/React.createElement("div", {
    className: "splms-welcome-feature-content"
  }, /*#__PURE__*/React.createElement("h3", {
    className: "splms-welcome-feature-title"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Configure basic settings', 'skillpulse-lms')), /*#__PURE__*/React.createElement("p", {
    className: "splms-welcome-feature-description"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Set up your site name, currency, timezone, and other essential options', 'skillpulse-lms')))), /*#__PURE__*/React.createElement("div", {
    className: "splms-welcome-feature"
  }, /*#__PURE__*/React.createElement("div", {
    className: "splms-welcome-feature-icon"
  }, /*#__PURE__*/React.createElement(_components_SplmsIcon__WEBPACK_IMPORTED_MODULE_2__.SplmsIcon, {
    name: "adminTools",
    size: 24
  })), /*#__PURE__*/React.createElement("div", {
    className: "splms-welcome-feature-content"
  }, /*#__PURE__*/React.createElement("h3", {
    className: "splms-welcome-feature-title"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Review key features', 'skillpulse-lms')), /*#__PURE__*/React.createElement("p", {
    className: "splms-welcome-feature-description"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Learn about the powerful features available in your LMS', 'skillpulse-lms')))))), /*#__PURE__*/React.createElement("div", {
    className: "splms-welcome-time"
  }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Card, {
    className: "splms-welcome-time-card"
  }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.CardBody, null, /*#__PURE__*/React.createElement("div", {
    className: "splms-welcome-time-content"
  }, /*#__PURE__*/React.createElement("div", {
    className: "splms-welcome-time-icon"
  }, /*#__PURE__*/React.createElement(_components_SplmsIcon__WEBPACK_IMPORTED_MODULE_2__.SplmsIcon, {
    name: "clock",
    size: 20
  })), /*#__PURE__*/React.createElement("div", {
    className: "splms-welcome-time-text"
  }, /*#__PURE__*/React.createElement("strong", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Takes about 2-3 minutes', 'skillpulse-lms'))))))), /*#__PURE__*/React.createElement("div", {
    className: "splms-welcome-cta"
  }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
    isPrimary: true,
    size: "large",
    onClick: handleGetStarted,
    disabled: loading,
    className: "splms-welcome-get-started-btn"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Get Started', 'skillpulse-lms'), /*#__PURE__*/React.createElement(_components_SplmsIcon__WEBPACK_IMPORTED_MODULE_2__.SplmsIcon, {
    name: "arrowRight",
    size: 16
  })), /*#__PURE__*/React.createElement("p", {
    className: "splms-welcome-skip-note"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('You can skip this setup and configure these settings later in the admin panel.', 'skillpulse-lms'))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (WelcomeStep);

/***/ },

/***/ "./src/js/react-core/components/BrandLogo.js"
/*!***************************************************!*\
  !*** ./src/js/react-core/components/BrandLogo.js ***!
  \***************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ BrandLogo)
/* harmony export */ });
var _excluded = ["width", "height"];
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
function _objectWithoutProperties(e, t) { if (null == e) return {}; var o, r, i = _objectWithoutPropertiesLoose(e, t); if (Object.getOwnPropertySymbols) { var n = Object.getOwnPropertySymbols(e); for (r = 0; r < n.length; r++) o = n[r], -1 === t.indexOf(o) && {}.propertyIsEnumerable.call(e, o) && (i[o] = e[o]); } return i; }
function _objectWithoutPropertiesLoose(r, e) { if (null == r) return {}; var t = {}; for (var n in r) if ({}.hasOwnProperty.call(r, n)) { if (-1 !== e.indexOf(n)) continue; t[n] = r[n]; } return t; }
function BrandLogo(_ref) {
  var _ref$width = _ref.width,
    width = _ref$width === void 0 ? 200 : _ref$width,
    _ref$height = _ref.height,
    height = _ref$height === void 0 ? 50 : _ref$height,
    props = _objectWithoutProperties(_ref, _excluded);
  return /*#__PURE__*/React.createElement("svg", _extends({
    xmlns: "http://www.w3.org/2000/svg",
    xmlnsXlink: "http://www.w3.org/1999/xlink",
    viewBox: "0 0 300 330",
    width: width,
    height: height
  }, props), /*#__PURE__*/React.createElement("g", {
    clipPath: "url(#clip0_109_37)"
  }, /*#__PURE__*/React.createElement("path", {
    d: "M142.115 283.613C141.097 283.442 138.614 282.739 137.559 282.404C122.076 277.486 107.612 279.337 91.5236 278.678C66.8798 277.667 41.7432 266.941 25.2746 251.082C14.0498 240.235 6.20713 227.118 2.47726 212.958C-0.656112 201.1 0.0886914 188.967 0.107232 176.834L0.116207 141.869L0.0822972 100.968C0.0771825 93.5903 -0.172137 86.1781 0.222971 78.74C2.13458 43.2208 59.1573 36.3307 73.0232 69.4134C76.0945 76.7404 74.9949 91.2868 74.9847 99.4122L74.9245 163.157C74.9194 174.828 72.3551 198.226 81.2162 206.715C85.5912 210.87 91.7416 213.32 98.2577 213.507C119.253 214.139 124.12 197.324 129.924 183.697L146.052 145.692C150.023 136.313 153.355 127.734 158.615 118.767C178.332 85.1544 236.478 90.9043 246.855 127.44C247.497 129.701 247.974 132.558 248.065 134.874C248.716 151.315 248.263 167.811 248.421 184.263C248.436 194.742 248.727 205.616 248.215 216.062C244.992 208.249 250.718 167.311 243.141 162.794C238.545 163.2 230.067 172.196 226.627 172.009C225.843 170.91 226.076 140.889 225.867 136.411C225.712 138.46 225.402 144.037 224.833 145.685C224.801 130.55 221.317 116.997 199.51 117.195C181.262 117.359 175.964 133.94 170.811 146.026L153.981 185.51C143.638 209.578 136.35 232.278 101.011 233.833C73.1421 235.053 51.7411 216.117 51.8293 192.478C51.7916 157.579 51.6081 122.421 51.7845 87.4963C51.798 84.8461 51.9489 81.7277 51.5391 79.1093C51.0998 76.1585 49.5053 73.4242 47.0177 71.3558C44.4233 69.2023 40.9223 68.0478 37.3177 68.1572C20.2705 68.7485 23.4442 86.8792 23.457 96.8884L23.5279 129.992L23.5222 172.553C23.5209 180.169 23.2466 190.247 23.8751 197.632C25.1646 215.178 34.699 231.52 50.2885 242.906C64.9286 253.654 84.4839 259.577 103.755 258.226C106.29 258.055 108.818 257.814 111.333 257.495C113.413 258.292 114.831 258.451 117.074 258.638C129.426 259.682 141.08 260.583 152.226 265.644L150.524 266.523C149.67 269.782 144.171 273.535 141.907 276.26C141.327 276.958 142.545 278.096 140.514 280.079C139.009 280.343 137.502 280.601 135.994 280.843C138.454 282.008 141.711 282.101 142.664 283.08L142.115 283.613Z",
    fill: "var(--splms-primary, #1E3A8A)"
  }), /*#__PURE__*/React.createElement("path", {
    d: "M148.344 65.1658C130.824 57.4426 117.511 42.0489 115.99 24.9369C115.609 20.6436 114.26 6.98085 118.505 3.96629C132.925 -6.05386 139.941 4.61852 139.364 17.0367C138.247 41.0751 160.955 50.6447 185.278 51.7195C197.651 52.2663 210.03 51.4695 222.147 53.4851C239.514 56.4277 255.547 63.5613 268.366 74.0512C288.39 90.2912 299.714 112.479 299.887 136.177C300.093 171.816 299.951 207.49 299.969 243.13C299.975 254.818 299.816 263.687 289.523 272.661C282.168 279.073 273.256 282.596 262.756 282.65C252.712 282.727 243.064 279.293 236.022 273.139C224.787 263.38 224.637 254.06 224.745 241.218C224.814 233.031 224.774 224.711 224.795 216.513L224.834 145.685C225.403 144.037 225.712 138.46 225.867 136.41C226.076 140.888 225.843 170.91 226.627 172.009C230.067 172.195 238.545 163.2 243.141 162.793C250.718 167.31 244.992 208.248 248.215 216.062C249.02 224.772 247.302 247.526 249.066 253.313C249.753 255.643 251.273 257.731 253.412 259.286C256.351 261.385 260.142 262.39 263.947 262.083C280.193 260.703 276.48 241.663 276.435 231.569L276.297 195.654L276.323 155.084C276.327 146.845 276.672 136.775 275.571 128.777C273.319 111.993 263.348 96.6808 247.879 86.2551C232.722 76.0701 212.714 70.7581 193.51 72.8324C191.496 73.0555 189.485 73.3045 187.479 73.5792C184.648 71.9956 176.178 71.4917 172.44 71.0718C163.616 70.0811 156.655 67.9512 148.344 65.1658Z",
    fill: "var(--splms-primary, #1E3A8A)"
  }), /*#__PURE__*/React.createElement("path", {
    d: "M187.479 73.5791C176.704 75.6656 174.252 77.5565 165.085 80.2682C139.939 94.3261 125.536 110.679 124.041 137.405C123.475 147.513 127.626 155.056 115.45 160.507C106.808 161.271 100.604 157.787 100.037 149.94C97.5668 115.713 113.932 83.3092 148.344 65.1658C156.655 67.9512 163.616 70.081 172.44 71.0718C176.178 71.4916 184.648 71.9955 187.479 73.5791Z",
    fill: "var(--splms-primary, #1E3A8A)"
  }), /*#__PURE__*/React.createElement("path", {
    d: "M163.566 70.8841C170.415 72.0001 175.569 72.3155 182.28 73.1134C180.833 74.8119 167.48 76.5978 165.085 80.2679C139.939 94.3258 125.536 110.678 124.041 137.404C123.475 147.513 127.626 155.056 115.45 160.507C111.031 157.534 113.281 153.435 112.727 149.36C108.831 120.756 123.239 93.3653 150.609 76.7737C155.057 74.0773 158.813 73.0359 163.566 70.8841Z",
    fill: "var(--splms-primary, #1E3A8A)"
  }), /*#__PURE__*/React.createElement("path", {
    d: "M187.966 169.822C201.188 172.295 199.823 178.153 200.23 187.868C201.367 214.997 185.972 247.247 158.608 262.105C156.598 263.441 154.943 264.523 152.816 265.71L152.227 265.644C141.08 260.583 129.426 259.682 117.075 258.638C114.832 258.451 113.414 258.292 111.333 257.495C113.077 257.237 114.814 256.946 116.543 256.621C148.704 250.192 172.604 226.97 176.1 198.754C177.511 187.214 170.912 172.63 187.966 169.822Z",
    fill: "var(--splms-primary, #1E3A8A)"
  }), /*#__PURE__*/React.createElement("path", {
    d: "M187.965 169.822C188.203 215.326 184.429 231.614 141.24 260.776C143.275 262.787 146.403 261.743 150.901 264.089C154.737 264.067 155.527 262.699 158.607 262.105C156.597 263.441 154.942 264.523 152.816 265.71L152.226 265.644C141.079 260.583 129.426 259.682 117.074 258.638C114.831 258.451 113.413 258.292 111.332 257.495C113.076 257.237 114.813 256.946 116.542 256.621C148.703 250.192 172.604 226.97 176.099 198.754C177.51 187.214 170.911 172.63 187.965 169.822Z",
    fill: "var(--splms-primary, #1E3A8A)"
  }), /*#__PURE__*/React.createElement("path", {
    d: "M152.816 265.709C166.786 271.253 177.672 281.276 183.139 293.64C186.531 301.426 188.627 316.681 185.431 324.83C182.751 331.665 166.598 332.012 164.228 324.132C163.016 320.038 163.733 316.741 163.689 312.565C163.415 298.173 155.803 290.178 142.115 283.612L142.664 283.079C141.711 282.1 138.455 282.007 135.994 280.842C137.503 280.6 139.01 280.342 140.514 280.078C142.545 278.095 141.327 276.957 141.907 276.259C144.172 273.534 149.67 269.781 150.525 266.522L152.227 265.643L152.816 265.709Z",
    fill: "var(--splms-primary, #1E3A8A)"
  }), /*#__PURE__*/React.createElement("ellipse", {
    cx: "112.538",
    cy: "149.464",
    rx: "7.99274",
    ry: "8.83817",
    transform: "rotate(90 112.538 149.464)",
    fill: "white"
  }), /*#__PURE__*/React.createElement("ellipse", {
    cx: "174.87",
    cy: "319.709",
    rx: "7.99274",
    ry: "8.83817",
    transform: "rotate(90 174.87 319.709)",
    fill: "white"
  }), /*#__PURE__*/React.createElement("ellipse", {
    cx: "187.895",
    cy: "180.636",
    rx: "7.99274",
    ry: "8.83817",
    transform: "rotate(90 187.895 180.636)",
    fill: "white"
  }), /*#__PURE__*/React.createElement("ellipse", {
    cx: "127.423",
    cy: "11.1899",
    rx: "7.99274",
    ry: "8.83817",
    transform: "rotate(90 127.423 11.1899)",
    fill: "white"
  })));
}

/***/ },

/***/ "./src/js/react-core/components/SplmsIcon/icons/ActionIcons.js"
/*!*********************************************************************!*\
  !*** ./src/js/react-core/components/SplmsIcon/icons/ActionIcons.js ***!
  \*********************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   close: () => (/* binding */ close),
/* harmony export */   deleteIcon: () => (/* binding */ deleteIcon),
/* harmony export */   edit: () => (/* binding */ edit),
/* harmony export */   eyeOff: () => (/* binding */ eyeOff),
/* harmony export */   filter: () => (/* binding */ filter),
/* harmony export */   lock: () => (/* binding */ lock),
/* harmony export */   plus: () => (/* binding */ plus),
/* harmony export */   plusCircle: () => (/* binding */ plusCircle),
/* harmony export */   sort: () => (/* binding */ sort),
/* harmony export */   trash: () => (/* binding */ trash),
/* harmony export */   update: () => (/* binding */ update),
/* harmony export */   view: () => (/* binding */ view),
/* harmony export */   visibility: () => (/* binding */ visibility)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);


// Basic action icons
var view = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-eye"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "12",
  cy: "12",
  r: "3"
}));

// Eye off icon
var eyeOff = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-eye-off"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "12",
  cy: "12",
  r: "3"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "2",
  y1: "2",
  x2: "22",
  y2: "22"
}));
var edit = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-edit"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"
}));
var close = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-x-circle"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "12",
  cy: "12",
  r: "10"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "15",
  y1: "9",
  x2: "9",
  y2: "15"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "9",
  y1: "9",
  x2: "15",
  y2: "15"
}));
var deleteIcon = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-trash-2"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("polyline", {
  points: "3 6 5 6 21 6"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "10",
  y1: "11",
  x2: "10",
  y2: "17"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "14",
  y1: "11",
  x2: "14",
  y2: "17"
}));
var plus = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-plus"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "12",
  y1: "5",
  x2: "12",
  y2: "19"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "5",
  y1: "12",
  x2: "19",
  y2: "12"
}));
var plusCircle = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  width: "16",
  height: "16",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-plus-circle"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "12",
  cy: "12",
  r: "10"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "12",
  y1: "8",
  x2: "12",
  y2: "16"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "8",
  y1: "12",
  x2: "16",
  y2: "12"
}));
var update = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-refresh-cw"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("polyline", {
  points: "23 4 23 10 17 10"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("polyline", {
  points: "1 20 1 14 7 14"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"
}));
var filter = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-filter"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("polygon", {
  points: "22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"
}));
var sort = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-menu"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "3",
  y1: "6",
  x2: "21",
  y2: "6"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "3",
  y1: "12",
  x2: "21",
  y2: "12"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "3",
  y1: "18",
  x2: "21",
  y2: "18"
}));
var lock = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-lock"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("rect", {
  x: "3",
  y: "11",
  width: "18",
  height: "11",
  rx: "2",
  ry: "2"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M7 11V7a5 5 0 0 1 10 0v4"
}));

// Aliases for common usage
var trash = deleteIcon;
var visibility = view;

/***/ },

/***/ "./src/js/react-core/components/SplmsIcon/icons/ArrowIcons.js"
/*!********************************************************************!*\
  !*** ./src/js/react-core/components/SplmsIcon/icons/ArrowIcons.js ***!
  \********************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   arrowBack: () => (/* binding */ arrowBack),
/* harmony export */   arrowDown: () => (/* binding */ arrowDown),
/* harmony export */   arrowNext: () => (/* binding */ arrowNext),
/* harmony export */   arrowPrev: () => (/* binding */ arrowPrev),
/* harmony export */   arrowRight: () => (/* binding */ arrowRight),
/* harmony export */   arrowUp: () => (/* binding */ arrowUp)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);


// Arrow and navigation icons
var arrowDown = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 512 512"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("title", null, "ionicons-v5-b"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M98,190.06,237.78,353.18a24,24,0,0,0,36.44,0L414,190.06c13.34-15.57,2.28-39.62-18.22-39.62H116.18C95.68,150.44,84.62,174.49,98,190.06Z"
}));
var arrowRight = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 512 512"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("title", null, "ionicons-v5-b"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M190.06,414,353.18,274.22a24,24,0,0,0,0-36.44L190.06,98c-15.57-13.34-39.62-2.28-39.62,18.22V395.82C150.44,416.32,174.49,427.38,190.06,414Z"
}));
var arrowNext = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-arrow-right"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "5",
  y1: "12",
  x2: "19",
  y2: "12"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("polyline", {
  points: "12 5 19 12 12 19"
}));
var arrowPrev = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-arrow-left"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "19",
  y1: "12",
  x2: "5",
  y2: "12"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("polyline", {
  points: "12 19 5 12 12 5"
}));
var arrowBack = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  fill: "none",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  strokeWidth: "2",
  viewBox: "0 0 24 24",
  stroke: "currentColor"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M7 16l-4-4m0 0l4-4m-4 4h18"
}));
var arrowUp = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-arrow-up"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "12",
  y1: "19",
  x2: "12",
  y2: "5"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("polyline", {
  points: "5 12 12 5 19 12"
}));

// Named exports for consistency - removed invalid syntax

/***/ },

/***/ "./src/js/react-core/components/SplmsIcon/icons/ContentIcons.js"
/*!**********************************************************************!*\
  !*** ./src/js/react-core/components/SplmsIcon/icons/ContentIcons.js ***!
  \**********************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   book: () => (/* binding */ book),
/* harmony export */   category: () => (/* binding */ category),
/* harmony export */   lesson: () => (/* binding */ lesson),
/* harmony export */   listView: () => (/* binding */ listView),
/* harmony export */   quiz: () => (/* binding */ quiz),
/* harmony export */   tag: () => (/* binding */ tag)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);


// Content and educational icons
var quiz = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  className: "css-aoyurr"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  fillRule: "evenodd",
  clipRule: "evenodd",
  d: "M10.778 5a.972.972 0 0 0-.972.972v.862a1.611 1.611 0 0 1-1.611 1.61H5.61a.11.11 0 0 0-.11.112v2.583a.111.111 0 0 0 .11.11h.861a2.472 2.472 0 1 1 0 4.946h-.86a.11.11 0 0 0-.111.11v2.584a.111.111 0 0 0 .11.11h2.584a.111.111 0 0 0 .11-.11v-.861a2.472 2.472 0 1 1 4.945 0v.86a.111.111 0 0 0 .111.111h2.583a.11.11 0 0 0 .111-.11v-2.584a1.611 1.611 0 0 1 1.611-1.611h.861a.972.972 0 0 0 0-1.944h-.86a1.611 1.611 0 0 1-1.612-1.611V8.556a.11.11 0 0 0-.11-.111H13.36a1.611 1.611 0 0 1-1.611-1.611v-.862a.972.972 0 0 0-.972-.971ZM5.61 6.945A1.611 1.611 0 0 0 4 8.556v2.583a1.611 1.611 0 0 0 1.611 1.611h.861a.972.972 0 1 1 0 1.944h-.86A1.611 1.611 0 0 0 4 16.305v2.584A1.611 1.611 0 0 0 5.611 20.5h2.584a1.611 1.611 0 0 0 1.611-1.611v-.861a.972.972 0 0 1 1.944 0v.86A1.611 1.611 0 0 0 13.36 20.5h2.583a1.611 1.611 0 0 0 1.612-1.611v-2.584a.11.11 0 0 1 .11-.11h.861a2.472 2.472 0 1 0 0-4.945h-.86a.111.111 0 0 1-.111-.111V8.556a1.611 1.611 0 0 0-1.612-1.612h-2.583a.111.111 0 0 1-.11-.11v-.862a2.472 2.472 0 0 0-4.946 0v.862a.11.11 0 0 1-.11.11H5.61Z",
  fill: "currentColor"
}));
var lesson = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  className: "css-cnbbco"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  fillRule: "evenodd",
  clipRule: "evenodd",
  d: "M7.265 4.988a.044.044 0 0 0-.003.018v13.988c0 .009.001.014.003.018a.03.03 0 0 0 .006.01h10.57a.03.03 0 0 0 .007-.01.045.045 0 0 0 .003-.018V5.006a.045.045 0 0 0-.003-.018.029.029 0 0 0-.007-.01H7.271a.03.03 0 0 0-.006.01Zm-1.503.018c0-.824.654-1.527 1.505-1.527h10.578c.851 0 1.506.703 1.506 1.527v13.988c0 .824-.655 1.527-1.506 1.527H7.267c-.851 0-1.505-.703-1.505-1.527V5.006Z",
  fill: "currentColor"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  fillRule: "evenodd",
  clipRule: "evenodd",
  d: "M15.578 3.479a.75.75 0 0 1 .75.75v15.543a.75.75 0 0 1-1.5 0V4.229a.75.75 0 0 1 .75-.75ZM4.25 8.114a.75.75 0 0 1 .75-.75h3.022a.75.75 0 1 1 0 1.5H5a.75.75 0 0 1-.75-.75ZM4.25 12a.75.75 0 0 1 .75-.75h3.022a.75.75 0 0 1 0 1.5H5a.75.75 0 0 1-.75-.75ZM4.25 15.886a.75.75 0 0 1 .75-.75h3.022a.75.75 0 0 1 0 1.5H5a.75.75 0 0 1-.75-.75Z",
  fill: "currentColor"
}));
var book = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-book"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M4 19.5A2.5 2.5 0 0 1 6.5 17H20"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"
}));
var category = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-folder"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"
}));
var tag = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-tag"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "7",
  y1: "7",
  x2: "7.01",
  y2: "7"
}));
var listView = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-list"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "8",
  y1: "6",
  x2: "21",
  y2: "6"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "8",
  y1: "12",
  x2: "21",
  y2: "12"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "8",
  y1: "18",
  x2: "21",
  y2: "18"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "3",
  y1: "6",
  x2: "3.01",
  y2: "6"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "3",
  y1: "12",
  x2: "3.01",
  y2: "12"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "3",
  y1: "18",
  x2: "3.01",
  y2: "18"
}));

// Named exports for consistency - removed invalid syntax

/***/ },

/***/ "./src/js/react-core/components/SplmsIcon/icons/UIIcons.js"
/*!*****************************************************************!*\
  !*** ./src/js/react-core/components/SplmsIcon/icons/UIIcons.js ***!
  \*****************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   adminTools: () => (/* binding */ adminTools),
/* harmony export */   calendar: () => (/* binding */ calendar),
/* harmony export */   cart: () => (/* binding */ cart),
/* harmony export */   clock: () => (/* binding */ clock),
/* harmony export */   draggable: () => (/* binding */ draggable),
/* harmony export */   draggableSmall: () => (/* binding */ draggableSmall),
/* harmony export */   email: () => (/* binding */ email),
/* harmony export */   groups: () => (/* binding */ groups),
/* harmony export */   help: () => (/* binding */ help),
/* harmony export */   info: () => (/* binding */ info),
/* harmony export */   link: () => (/* binding */ link),
/* harmony export */   membership: () => (/* binding */ membership),
/* harmony export */   ravi: () => (/* binding */ ravi),
/* harmony export */   settings: () => (/* binding */ settings),
/* harmony export */   sidebarListView: () => (/* binding */ sidebarListView),
/* harmony export */   sidebarSettings: () => (/* binding */ sidebarSettings),
/* harmony export */   yesAlt: () => (/* binding */ yesAlt)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);


// UI and interface icons
var draggable = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 22 22",
  fill: "var(--splms-text-color, #333)",
  stroke: "var(--splms-text-color, #333)",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-more-vertical"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "12",
  cy: "11",
  r: "1.2"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "12",
  cy: "5",
  r: "1.2"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "12",
  cy: "17",
  r: "1.2"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "5",
  cy: "11",
  r: "1.2"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "5",
  cy: "5",
  r: "1.2"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "5",
  cy: "17",
  r: "1.2"
}));
var draggableSmall = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  width: "14",
  height: "18",
  viewBox: "0 0 18 22",
  fill: "var(--splms-text-muted, #666)",
  stroke: "var(--splms-text-muted, #666)",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-more-vertical"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "12",
  cy: "11",
  r: "1.2"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "12",
  cy: "5",
  r: "1.2"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "12",
  cy: "17",
  r: "1.2"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "5",
  cy: "11",
  r: "1.2"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "5",
  cy: "5",
  r: "1.2"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "5",
  cy: "17",
  r: "1.2"
}));
var link = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  fill: "none",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  strokeWidth: "2",
  viewBox: "0 0 24 24",
  stroke: "currentColor",
  className: "link-icon"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
}));
var settings = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  className: "css-cnbbco"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  fillRule: "evenodd",
  clipRule: "evenodd",
  d: "M7.265 4.988a.044.044 0 0 0-.003.018v13.988c0 .009.001.014.003.018a.03.03 0 0 0 .006.01h10.57a.03.03 0 0 0 .007-.01.045.045 0 0 0 .003-.018V5.006a.045.045 0 0 0-.003-.018.029.029 0 0 0-.007-.01H7.271a.03.03 0 0 0-.006.01Zm-1.503.018c0-.824.654-1.527 1.505-1.527h10.578c.851 0 1.506.703 1.506 1.527v13.988c0 .824-.655 1.527-1.506 1.527H7.267c-.851 0-1.505-.703-1.505-1.527V5.006Z",
  fill: "currentColor"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  fillRule: "evenodd",
  clipRule: "evenodd",
  d: "M15.578 3.479a.75.75 0 0 1 .75.75v15.543a.75.75 0 0 1-1.5 0V4.229a.75.75 0 0 1 .75-.75ZM4.25 8.114a.75.75 0 0 1 .75-.75h3.022a.75.75 0 1 1 0 1.5H5a.75.75 0 0 1-.75-.75ZM4.25 12a.75.75 0 0 1 .75-.75h3.022a.75.75 0 0 1 0 1.5H5a.75.75 0 0 1-.75-.75ZM4.25 15.886a.75.75 0 0 1 .75-.75h3.022a.75.75 0 0 1 0 1.5H5a.75.75 0 0 1-.75-.75Z",
  fill: "currentColor"
}));
var help = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  className: "css-cnbbco"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  fillRule: "evenodd",
  clipRule: "evenodd",
  d: "M7.265 4.988a.044.044 0 0 0-.003.018v13.988c0 .009.001.014.003.018a.03.03 0 0 0 .006.01h10.57a.03.03 0 0 0 .007-.01.045.045 0 0 0 .003-.018V5.006a.045.045 0 0 0-.003-.018.029.029 0 0 0-.007-.01H7.271a.03.03 0 0 0-.006.01Zm-1.503.018c0-.824.654-1.527 1.505-1.527h10.578c.851 0 1.506.703 1.506 1.527v13.988c0 .824-.655 1.527-1.506 1.527H7.267c-.851 0-1.505-.703-1.505-1.527V5.006Z",
  fill: "currentColor"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  fillRule: "evenodd",
  clipRule: "evenodd",
  d: "M15.578 3.479a.75.75 0 0 1 .75.75v15.543a.75.75 0 0 1-1.5 0V4.229a.75.75 0 0 1 .75-.75ZM4.25 8.114a.75.75 0 0 1 .75-.75h3.022a.75.75 0 1 1 0 1.5H5a.75.75 0 0 1-.75-.75ZM4.25 12a.75.75 0 0 1 .75-.750h3.022a.75.75 0 0 1 0 1.5H5a.75.75 0 0 1-.75-.75ZM4.25 15.886a.75.75 0 0 1 .75-.75h3.022a.75.75 0 0 1 0 1.5H5a.75.75 0 0 1-.75-.75Z",
  fill: "currentColor"
}));
var info = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-info"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "12",
  cy: "12",
  r: "10"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "12",
  y1: "16",
  x2: "12",
  y2: "12"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "12",
  y1: "8",
  x2: "12.01",
  y2: "8"
}));
var cart = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-shopping-cart"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "9",
  cy: "21",
  r: "1"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "20",
  cy: "21",
  r: "1"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"
}));
var groups = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-users"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "9",
  cy: "7",
  r: "4"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M23 21v-2a4 4 0 0 0-3-3.87"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M16 3.13a4 4 0 0 1 0 7.75"
}));
var yesAlt = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-check-circle"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M22 11.08V12a10 10 0 1 1-5.93-9.14"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("polyline", {
  points: "22 4 12 14.01 9 11.01"
}));
var clock = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-clock"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "12",
  cy: "12",
  r: "10"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("polyline", {
  points: "12 6 12 12 16 14"
}));
var email = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-mail"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("polyline", {
  points: "22,6 12,13 2,6"
}));
var adminTools = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-tool"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"
}));
var membership = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-award"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("circle", {
  cx: "12",
  cy: "8",
  r: "6"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("polyline", {
  points: "8.21 13.89 7 23 12 20 17 23 15.79 13.88"
}));
var calendar = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  strokeLinejoin: "round",
  className: "feather feather-calendar"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("rect", {
  x: "3",
  y: "4",
  width: "18",
  height: "18",
  rx: "2",
  ry: "2"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "16",
  y1: "2",
  x2: "16",
  y2: "6"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "8",
  y1: "2",
  x2: "8",
  y2: "6"
}), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("line", {
  x1: "3",
  y1: "10",
  x2: "21",
  y2: "10"
}));
var sidebarSettings = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  width: "24",
  height: "24",
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24",
  "aria-hidden": "true",
  focusable: "false"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  fillRule: "evenodd",
  clipRule: "evenodd",
  d: "M18 4H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-4 14.5H6c-.3 0-.5-.2-.5-.5V6c0-.3.2-.5.5-.5h8v13zm4.5-.5c0 .3-.2.5-.5.5h-2.5v-13H18c.3 0 .5.2.5.5v12z",
  fill: "currentColor"
}));
var sidebarListView = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", {
  viewBox: "0 0 24 24",
  xmlns: "http://www.w3.org/2000/svg",
  width: "24",
  height: "24",
  "aria-hidden": "true",
  focusable: "false"
}, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("path", {
  d: "M3 6h11v1.5H3V6Zm3.5 5.5h11V13h-11v-1.5ZM21 17H10v1.5h11V17Z",
  fill: "currentColor"
}));
var ravi = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement("svg", null);

/***/ },

/***/ "./src/js/react-core/components/SplmsIcon/icons/index.js"
/*!***************************************************************!*\
  !*** ./src/js/react-core/components/SplmsIcon/icons/index.js ***!
  \***************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   adminTools: () => (/* reexport safe */ _UIIcons__WEBPACK_IMPORTED_MODULE_3__.adminTools),
/* harmony export */   arrowBack: () => (/* reexport safe */ _ArrowIcons__WEBPACK_IMPORTED_MODULE_1__.arrowBack),
/* harmony export */   arrowDown: () => (/* reexport safe */ _ArrowIcons__WEBPACK_IMPORTED_MODULE_1__.arrowDown),
/* harmony export */   arrowNext: () => (/* reexport safe */ _ArrowIcons__WEBPACK_IMPORTED_MODULE_1__.arrowNext),
/* harmony export */   arrowPrev: () => (/* reexport safe */ _ArrowIcons__WEBPACK_IMPORTED_MODULE_1__.arrowPrev),
/* harmony export */   arrowRight: () => (/* reexport safe */ _ArrowIcons__WEBPACK_IMPORTED_MODULE_1__.arrowRight),
/* harmony export */   arrowUp: () => (/* reexport safe */ _ArrowIcons__WEBPACK_IMPORTED_MODULE_1__.arrowUp),
/* harmony export */   availableIcons: () => (/* binding */ availableIcons),
/* harmony export */   book: () => (/* reexport safe */ _ContentIcons__WEBPACK_IMPORTED_MODULE_2__.book),
/* harmony export */   calendar: () => (/* reexport safe */ _UIIcons__WEBPACK_IMPORTED_MODULE_3__.calendar),
/* harmony export */   cart: () => (/* reexport safe */ _UIIcons__WEBPACK_IMPORTED_MODULE_3__.cart),
/* harmony export */   category: () => (/* reexport safe */ _ContentIcons__WEBPACK_IMPORTED_MODULE_2__.category),
/* harmony export */   clock: () => (/* reexport safe */ _UIIcons__WEBPACK_IMPORTED_MODULE_3__.clock),
/* harmony export */   close: () => (/* reexport safe */ _ActionIcons__WEBPACK_IMPORTED_MODULE_0__.close),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   "delete": () => (/* reexport safe */ _ActionIcons__WEBPACK_IMPORTED_MODULE_0__.deleteIcon),
/* harmony export */   draggable: () => (/* reexport safe */ _UIIcons__WEBPACK_IMPORTED_MODULE_3__.draggable),
/* harmony export */   draggableSmall: () => (/* reexport safe */ _UIIcons__WEBPACK_IMPORTED_MODULE_3__.draggableSmall),
/* harmony export */   edit: () => (/* reexport safe */ _ActionIcons__WEBPACK_IMPORTED_MODULE_0__.edit),
/* harmony export */   email: () => (/* reexport safe */ _UIIcons__WEBPACK_IMPORTED_MODULE_3__.email),
/* harmony export */   filter: () => (/* reexport safe */ _ActionIcons__WEBPACK_IMPORTED_MODULE_0__.filter),
/* harmony export */   groups: () => (/* reexport safe */ _UIIcons__WEBPACK_IMPORTED_MODULE_3__.groups),
/* harmony export */   help: () => (/* reexport safe */ _UIIcons__WEBPACK_IMPORTED_MODULE_3__.help),
/* harmony export */   info: () => (/* reexport safe */ _UIIcons__WEBPACK_IMPORTED_MODULE_3__.info),
/* harmony export */   lesson: () => (/* reexport safe */ _ContentIcons__WEBPACK_IMPORTED_MODULE_2__.lesson),
/* harmony export */   link: () => (/* reexport safe */ _UIIcons__WEBPACK_IMPORTED_MODULE_3__.link),
/* harmony export */   listView: () => (/* reexport safe */ _ContentIcons__WEBPACK_IMPORTED_MODULE_2__.listView),
/* harmony export */   lock: () => (/* reexport safe */ _ActionIcons__WEBPACK_IMPORTED_MODULE_0__.lock),
/* harmony export */   membership: () => (/* reexport safe */ _UIIcons__WEBPACK_IMPORTED_MODULE_3__.membership),
/* harmony export */   plus: () => (/* reexport safe */ _ActionIcons__WEBPACK_IMPORTED_MODULE_0__.plus),
/* harmony export */   plusCircle: () => (/* reexport safe */ _ActionIcons__WEBPACK_IMPORTED_MODULE_0__.plusCircle),
/* harmony export */   quiz: () => (/* reexport safe */ _ContentIcons__WEBPACK_IMPORTED_MODULE_2__.quiz),
/* harmony export */   settings: () => (/* reexport safe */ _UIIcons__WEBPACK_IMPORTED_MODULE_3__.settings),
/* harmony export */   sidebarListView: () => (/* reexport safe */ _UIIcons__WEBPACK_IMPORTED_MODULE_3__.sidebarListView),
/* harmony export */   sidebarSettings: () => (/* reexport safe */ _UIIcons__WEBPACK_IMPORTED_MODULE_3__.sidebarSettings),
/* harmony export */   sort: () => (/* reexport safe */ _ActionIcons__WEBPACK_IMPORTED_MODULE_0__.sort),
/* harmony export */   tag: () => (/* reexport safe */ _ContentIcons__WEBPACK_IMPORTED_MODULE_2__.tag),
/* harmony export */   trash: () => (/* reexport safe */ _ActionIcons__WEBPACK_IMPORTED_MODULE_0__.trash),
/* harmony export */   update: () => (/* reexport safe */ _ActionIcons__WEBPACK_IMPORTED_MODULE_0__.update),
/* harmony export */   view: () => (/* reexport safe */ _ActionIcons__WEBPACK_IMPORTED_MODULE_0__.view),
/* harmony export */   visibility: () => (/* reexport safe */ _ActionIcons__WEBPACK_IMPORTED_MODULE_0__.visibility),
/* harmony export */   yesAlt: () => (/* reexport safe */ _UIIcons__WEBPACK_IMPORTED_MODULE_3__.yesAlt)
/* harmony export */ });
/* harmony import */ var _ActionIcons__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ActionIcons */ "./src/js/react-core/components/SplmsIcon/icons/ActionIcons.js");
/* harmony import */ var _ArrowIcons__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ArrowIcons */ "./src/js/react-core/components/SplmsIcon/icons/ArrowIcons.js");
/* harmony import */ var _ContentIcons__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ContentIcons */ "./src/js/react-core/components/SplmsIcon/icons/ContentIcons.js");
/* harmony import */ var _UIIcons__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./UIIcons */ "./src/js/react-core/components/SplmsIcon/icons/UIIcons.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
// Central export file for all icons
// This file imports all icon categories and re-exports them as a single object






// Export all icons from different categories





// Create aliases for icons with dash names and alternative names
var iconAliases = {
  'arrow-up': _ArrowIcons__WEBPACK_IMPORTED_MODULE_1__.arrowUp,
  'arrow-down': _ArrowIcons__WEBPACK_IMPORTED_MODULE_1__.arrowDown,
  'list-view': _ContentIcons__WEBPACK_IMPORTED_MODULE_2__.listView,
  'yes-alt': _UIIcons__WEBPACK_IMPORTED_MODULE_3__.yesAlt,
  // Additional aliases for common usage
  'remove': _ActionIcons__WEBPACK_IMPORTED_MODULE_0__.deleteIcon,
  'add': _ActionIcons__WEBPACK_IMPORTED_MODULE_0__.plus,
  'check': _UIIcons__WEBPACK_IMPORTED_MODULE_3__.yesAlt,
  'mail': _UIIcons__WEBPACK_IMPORTED_MODULE_3__.email,
  'users': _UIIcons__WEBPACK_IMPORTED_MODULE_3__.groups,
  'folder': _ContentIcons__WEBPACK_IMPORTED_MODULE_2__.category,
  'refresh': _ActionIcons__WEBPACK_IMPORTED_MODULE_0__.update,
  'eye': _ActionIcons__WEBPACK_IMPORTED_MODULE_0__.view,
  'eye-off': _ActionIcons__WEBPACK_IMPORTED_MODULE_0__.eyeOff,
  'pencil': _ActionIcons__WEBPACK_IMPORTED_MODULE_0__.edit,
  'x': _ActionIcons__WEBPACK_IMPORTED_MODULE_0__.close,
  'menu': _ActionIcons__WEBPACK_IMPORTED_MODULE_0__.sort,
  'sidebar-settings': _UIIcons__WEBPACK_IMPORTED_MODULE_3__.sidebarSettings,
  'settings-sidebar': _UIIcons__WEBPACK_IMPORTED_MODULE_3__.sidebarSettings,
  'sidebar-list-view': _UIIcons__WEBPACK_IMPORTED_MODULE_3__.sidebarListView,
  'list-view-sidebar': _UIIcons__WEBPACK_IMPORTED_MODULE_3__.sidebarListView,
  // Membership aliases
  'premium': _UIIcons__WEBPACK_IMPORTED_MODULE_3__.membership,
  'vip': _UIIcons__WEBPACK_IMPORTED_MODULE_3__.membership,
  'award': _UIIcons__WEBPACK_IMPORTED_MODULE_3__.membership
};

// Create a complete icons object for easy access
var allIcons = _objectSpread(_objectSpread(_objectSpread(_objectSpread(_objectSpread({}, _ActionIcons__WEBPACK_IMPORTED_MODULE_0__), _ArrowIcons__WEBPACK_IMPORTED_MODULE_1__), _ContentIcons__WEBPACK_IMPORTED_MODULE_2__), _UIIcons__WEBPACK_IMPORTED_MODULE_3__), iconAliases);

// Export available icon names for development/debugging
var availableIcons = Object.keys(allIcons);

// Default export for the Icon component
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (allIcons);

/***/ },

/***/ "./src/js/react-core/components/SplmsIcon/index.js"
/*!*********************************************************!*\
  !*** ./src/js/react-core/components/SplmsIcon/index.js ***!
  \*********************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   ActionIcons: () => (/* reexport module object */ _icons_ActionIcons__WEBPACK_IMPORTED_MODULE_3__),
/* harmony export */   ArrowIcons: () => (/* reexport module object */ _icons_ArrowIcons__WEBPACK_IMPORTED_MODULE_4__),
/* harmony export */   ContentIcons: () => (/* reexport module object */ _icons_ContentIcons__WEBPACK_IMPORTED_MODULE_5__),
/* harmony export */   SplmsIcon: () => (/* binding */ SplmsIcon),
/* harmony export */   UIIcons: () => (/* reexport module object */ _icons_UIIcons__WEBPACK_IMPORTED_MODULE_6__),
/* harmony export */   allIcons: () => (/* reexport safe */ _icons__WEBPACK_IMPORTED_MODULE_2__["default"]),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _icons__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./icons */ "./src/js/react-core/components/SplmsIcon/icons/index.js");
/* harmony import */ var _icons_ActionIcons__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./icons/ActionIcons */ "./src/js/react-core/components/SplmsIcon/icons/ActionIcons.js");
/* harmony import */ var _icons_ArrowIcons__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./icons/ArrowIcons */ "./src/js/react-core/components/SplmsIcon/icons/ArrowIcons.js");
/* harmony import */ var _icons_ContentIcons__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./icons/ContentIcons */ "./src/js/react-core/components/SplmsIcon/icons/ContentIcons.js");
/* harmony import */ var _icons_UIIcons__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./icons/UIIcons */ "./src/js/react-core/components/SplmsIcon/icons/UIIcons.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
var _excluded = ["name", "mode", "size", "color", "className", "style"];
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _objectWithoutProperties(e, t) { if (null == e) return {}; var o, r, i = _objectWithoutPropertiesLoose(e, t); if (Object.getOwnPropertySymbols) { var n = Object.getOwnPropertySymbols(e); for (r = 0; r < n.length; r++) o = n[r], -1 === t.indexOf(o) && {}.propertyIsEnumerable.call(e, o) && (i[o] = e[o]); } return i; }
function _objectWithoutPropertiesLoose(r, e) { if (null == r) return {}; var t = {}; for (var n in r) if ({}.hasOwnProperty.call(r, n)) { if (-1 !== e.indexOf(n)) continue; t[n] = r[n]; } return t; }




/**
 * SkillPulse LMS Icon Component
 * 
 * A flexible icon component that can render custom SVG icons or WordPress Dashicons.
 * Supports both custom SkillPulse icons and WordPress icon library.
 * 
 * @param {string} name - The name of the icon to render
 * @param {string} mode - Icon mode: 'custom' (default) or 'wp' for WordPress icons
 * @param {number|string} size - The size of the icon (default: 24)
 * @param {string} color - The color of the icon (default: 'currentColor')
 * @param {string} className - Additional CSS classes
 * @param {object} style - Inline styles
 * @param {object} ...props - Other props to pass to the SVG element
 * 
 * Usage:
 * <SplmsIcon name="plus" />                              // Custom SkillPulse icon
 * <SplmsIcon mode="wp" name="admin-post" size={20} />   // WordPress Dashicon
 * <SplmsIcon name="edit" size={20} />                   // Custom icon with size
 * <SplmsIcon mode="wp" name="yes" color="#333" />       // WordPress icon with color
 */
var SplmsIcon = /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().forwardRef(function (_ref, ref) {
  var name = _ref.name,
    _ref$mode = _ref.mode,
    mode = _ref$mode === void 0 ? 'custom' : _ref$mode,
    _ref$size = _ref.size,
    size = _ref$size === void 0 ? 24 : _ref$size,
    _ref$color = _ref.color,
    color = _ref$color === void 0 ? '' : _ref$color,
    _ref$className = _ref.className,
    className = _ref$className === void 0 ? '' : _ref$className,
    _ref$style = _ref.style,
    style = _ref$style === void 0 ? {} : _ref$style,
    props = _objectWithoutProperties(_ref, _excluded);
  // Handle WordPress icons mode
  if (mode === 'wp') {
    var wpIconStyle = _objectSpread({
      width: size,
      height: size,
      color: color
    }, style);
    return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Icon, _extends({
      icon: name,
      size: size,
      style: wpIconStyle,
      className: "wp-icon wp-icon-".concat(name, " ").concat(className).trim(),
      ref: ref
    }, props));
  }

  // Handle custom SkillPulse icons (default mode)
  var IconComponent = _icons__WEBPACK_IMPORTED_MODULE_2__["default"][name];
  if (!IconComponent) {
    console.warn("Custom icon \"".concat(name, "\" not found. Available icons:"), Object.keys(_icons__WEBPACK_IMPORTED_MODULE_2__["default"]));
    console.info('💡 Tip: Use mode="wp" for WordPress Dashicons, e.g., <SplmsIcon mode="wp" name="admin-post" />');
    return null;
  }

  // Default style with size and color for custom icons
  var iconStyle = _objectSpread({
    width: size,
    height: size,
    fill: color,
    stroke: color
  }, style);

  // Clone the custom icon component and add our props
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().cloneElement(IconComponent, _objectSpread({
    style: iconStyle,
    className: "splms-icon splms-icon-".concat(name, " ").concat(className).trim(),
    ref: ref
  }, props));
});
SplmsIcon.displayName = 'SplmsIcon';

// Main export for Icon component (conflict-safe)
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (SplmsIcon);


// Export individual icon categories for advanced usage







 // Export all icons object for reference


/***/ },

/***/ "./src/js/react-core/utility/renderBlock.js"
/*!**************************************************!*\
  !*** ./src/js/react-core/utility/renderBlock.js ***!
  \**************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   renderBlock: () => (/* binding */ renderBlock)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/dom-ready */ "@wordpress/dom-ready");
/* harmony import */ var _wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_1__);



/**
 * render Block
 **/
function renderBlock(containerId, element) {
  _wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_1___default()(function () {
    var mount = function mount() {
      var container = document.getElementById(containerId);
      if (container) {
        // Prevent multiple roots if MutationObserver fires twice
        if (!container.hasAttribute('data-react-mounted')) {
          container.setAttribute('data-react-mounted', 'true');
          var root = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createRoot)(container);
          root.render(element);
        }
        return true;
      }
      return false;
    };
    if (!mount()) {
      var observer = new MutationObserver(function (mutations, obs) {
        if (mount()) {
          obs.disconnect();
        }
      });
      observer.observe(document.body, {
        childList: true,
        subtree: true
      });

      // Fallback timeout to prevent memory leaks if metabox never renders
      setTimeout(function () {
        return observer.disconnect();
      }, 10000);
    }
  });
}

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

/***/ },

/***/ "react"
/*!************************!*\
  !*** external "React" ***!
  \************************/
(module) {

module.exports = window["React"];

/***/ },

/***/ "@wordpress/components"
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
(module) {

module.exports = window["wp"]["components"];

/***/ },

/***/ "@wordpress/dom-ready"
/*!**********************************!*\
  !*** external ["wp","domReady"] ***!
  \**********************************/
(module) {

module.exports = window["wp"]["domReady"];

/***/ },

/***/ "@wordpress/element"
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
(module) {

module.exports = window["wp"]["element"];

/***/ },

/***/ "@wordpress/i18n"
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
(module) {

module.exports = window["wp"]["i18n"];

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
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!*******************************************************!*\
  !*** ./src/js/react-core/admin/pages/wizard/index.js ***!
  \*******************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _utility_renderBlock__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../utility/renderBlock */ "./src/js/react-core/utility/renderBlock.js");
/* harmony import */ var _WizardPage__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./WizardPage */ "./src/js/react-core/admin/pages/wizard/WizardPage.js");
/**
 * Wizard Entry Point
 *
 * Main entry point for the SkillPulse LMS setup wizard.
 * Follows the same pattern as existing admin pages (e.g., license page).
 *
 * @since [SPLMS_VERSION]
 */




// Render wizard page in the designated container
(0,_utility_renderBlock__WEBPACK_IMPORTED_MODULE_0__.renderBlock)('splms-setup-wizard-root', /*#__PURE__*/React.createElement(_WizardPage__WEBPACK_IMPORTED_MODULE_1__["default"], null));
})();

/******/ })()
;
//# sourceMappingURL=wizard.js.map