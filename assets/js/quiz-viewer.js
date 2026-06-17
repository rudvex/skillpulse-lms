/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

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

/***/ "./src/js/frontend/modules/quiz-viewer/QuizTimer.js"
/*!**********************************************************!*\
  !*** ./src/js/frontend/modules/quiz-viewer/QuizTimer.js ***!
  \**********************************************************/
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
 * Quiz Timer - Header Timer Display
 *
 * Displays and updates the quiz timer in the header.
 * Works with the main SPLMSQuiz class for timer countdown logic.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */
var SPLMSQuizTimer = /*#__PURE__*/function () {
  function SPLMSQuizTimer(timerElement) {
    _classCallCheck(this, SPLMSQuizTimer);
    this.timerElement = timerElement;
    this.valueElement = timerElement === null || timerElement === void 0 ? void 0 : timerElement.querySelector('.splms-timer-value');
    this.timeLimit = parseInt(timerElement === null || timerElement === void 0 ? void 0 : timerElement.dataset.timeLimit) || 0;
    this.timeRemaining = 0;
    this.isWarning = false;
    this.isCritical = false;
    this.init();
  }
  return _createClass(SPLMSQuizTimer, [{
    key: "init",
    value: function init() {
      if (!this.timerElement || !this.valueElement) {
        return;
      }

      // Timer is hidden by default, shown when quiz starts.
      if (this.timeLimit > 0) {
        this.timeRemaining = this.timeLimit * 60; // Convert minutes to seconds.
        this.updateDisplay();
      }
    }
  }, {
    key: "show",
    value: function show() {
      if (this.timerElement && this.timeLimit > 0) {
        this.timerElement.style.display = '';
        this.timeRemaining = this.timeLimit * 60;
        this.updateDisplay();
      }
    }
  }, {
    key: "hide",
    value: function hide() {
      if (this.timerElement) {
        this.timerElement.style.display = 'none';
      }
    }
  }, {
    key: "updateTime",
    value: function updateTime(seconds) {
      this.timeRemaining = seconds;
      this.updateDisplay();
      this.checkWarnings();
    }
  }, {
    key: "updateDisplay",
    value: function updateDisplay() {
      if (!this.valueElement) {
        return;
      }
      var minutes = Math.floor(this.timeRemaining / 60);
      var seconds = this.timeRemaining % 60;

      // Format time as MM:SS.
      var timeString = "".concat(String(minutes).padStart(2, '0'), ":").concat(String(seconds).padStart(2, '0'));
      this.valueElement.textContent = timeString;
    }
  }, {
    key: "checkWarnings",
    value: function checkWarnings() {
      if (!this.timerElement) {
        return;
      }

      // Critical warning: last 1 minute.
      if (this.timeRemaining <= 60 && !this.isCritical) {
        this.isCritical = true;
        this.timerElement.classList.add('is-critical');
        this.timerElement.classList.remove('is-warning');

        // Dispatch event for other components.
        var event = new CustomEvent('splms:quiz:timerCritical', {
          detail: {
            timeRemaining: this.timeRemaining
          }
        });
        document.dispatchEvent(event);
      }
      // Warning: last 5 minutes.
      else if (this.timeRemaining <= 300 && this.timeRemaining > 60 && !this.isWarning) {
        this.isWarning = true;
        this.timerElement.classList.add('is-warning');

        // Dispatch event for other components.
        var _event = new CustomEvent('splms:quiz:timerWarning', {
          detail: {
            timeRemaining: this.timeRemaining
          }
        });
        document.dispatchEvent(_event);
      }
      // Reset warnings if time increases (shouldn't happen but defensive).
      else if (this.timeRemaining > 300) {
        this.isWarning = false;
        this.isCritical = false;
        this.timerElement.classList.remove('is-warning', 'is-critical');
      }
    }
  }, {
    key: "destroy",
    value: function destroy() {
      // Cleanup if needed.
    }
  }]);
}();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (SPLMSQuizTimer);

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
/*!***********************************************************!*\
  !*** ./src/js/frontend/modules/quiz-viewer/QuizViewer.js ***!
  \***********************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _QuizTimer_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./QuizTimer.js */ "./src/js/frontend/modules/quiz-viewer/QuizTimer.js");
/* harmony import */ var _lesson_viewer_SidebarToggle_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../lesson-viewer/SidebarToggle.js */ "./src/js/frontend/modules/lesson-viewer/SidebarToggle.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Quiz Viewer - Fullscreen Quiz Controller
 *
 * Lightweight wrapper that integrates the existing quiz system with the fullscreen UI.
 * The main quiz logic is handled by SPLMSQuiz class.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */



var SPLMSQuizViewer = /*#__PURE__*/function () {
  function SPLMSQuizViewer() {
    _classCallCheck(this, SPLMSQuizViewer);
    this.container = document.getElementById('splms-quiz-fullscreen-container');
    if (!this.container) {
      return;
    }
    this.quizId = this.container.dataset.quizId;
    this.courseId = this.container.dataset.courseId;

    // Initialize components.
    this.quizTimer = null;
    this.sidebarToggle = null;
    this.init();
  }
  return _createClass(SPLMSQuizViewer, [{
    key: "init",
    value: function init() {
      // Initialize quiz timer in header.
      var timerDisplay = document.querySelector('.splms-quiz-timer-display');
      if (timerDisplay) {
        this.quizTimer = new _QuizTimer_js__WEBPACK_IMPORTED_MODULE_0__["default"](timerDisplay);
      }

      // Initialize sidebar toggle (reuse from lesson viewer).
      this.sidebarToggle = new _lesson_viewer_SidebarToggle_js__WEBPACK_IMPORTED_MODULE_1__["default"]();

      // Handle mobile menu toggle.
      this.initMobileMenu();

      // Handle section collapse/expand in sidebar.
      this.initSectionToggle();

      // Listen for quiz events to update timer.
      this.bindQuizEvents();
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
    key: "bindQuizEvents",
    value: function bindQuizEvents() {
      var _this = this;
      // Listen for quiz start event to show timer.
      document.addEventListener('splms:quiz:started', function (e) {
        if (_this.quizTimer) {
          _this.quizTimer.show();
        }
      });

      // Listen for quiz submitted event to hide timer.
      document.addEventListener('splms:quiz:submitted', function (e) {
        if (_this.quizTimer) {
          _this.quizTimer.hide();
        }

        // Update sidebar if quiz was passed.
        if (e.detail && e.detail.results && e.detail.results.passed) {
          _this.updateSidebarQuizStatus();
        }
      });

      // Listen for timer updates from SPLMSQuiz.
      document.addEventListener('splms:quiz:timerUpdate', function (e) {
        if (_this.quizTimer && e.detail && e.detail.timeRemaining !== undefined) {
          _this.quizTimer.updateTime(e.detail.timeRemaining);
        }
      });
    }
  }, {
    key: "updateSidebarQuizStatus",
    value: function updateSidebarQuizStatus() {
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
    key: "destroy",
    value: function destroy() {
      if (this.quizTimer) {
        this.quizTimer.destroy();
      }
      if (this.sidebarToggle) {
        this.sidebarToggle.destroy();
      }
    }
  }]);
}(); // Initialize when DOM is ready.
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function () {
    window.splmsQuizViewer = new SPLMSQuizViewer();
  });
} else {
  window.splmsQuizViewer = new SPLMSQuizViewer();
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (SPLMSQuizViewer);
})();

/******/ })()
;
//# sourceMappingURL=quiz-viewer.js.map