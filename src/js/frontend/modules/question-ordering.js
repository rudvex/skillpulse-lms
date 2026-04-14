/**
 * SkillPulse LMS Ordering Question Handler
 * Handles drag-and-drop ordering interface for ordering-type quiz questions
 */
class SPLMSQuestionOrdering {
	constructor(container, questionData, quizInstance) {
		this.container = jQuery(container);
		this.questionId = questionData.id;
		this.items = Array.isArray(questionData.items) ? questionData.items : [];
		this.selectedAnswer = questionData.selectedAnswer || [];
		this.quiz = quizInstance || null;
        
		// Store correct order from question data (array of option IDs)
		this.correctOrder = [];
		if (Array.isArray(questionData.correct_answer) && questionData.correct_answer.length > 0) {
			// Use correct_answer if provided (array of option IDs)
			this.correctOrder = questionData.correct_answer.map(id => String(id).trim()).filter(id => id !== '');
		} else {
			// Use items array order as correct order
			this.correctOrder = this.items.map(item => {
				return (item && typeof item === 'object' && item.text) ? String(item.text).trim() : '';
			}).filter(text => text !== '');
		}
        
		// Store current order: array of option IDs (not indices)
		this.currentOrder = [];
		this.draggedElement = null;
        
		// Check if randomize is enabled (from settings)
		const settings = questionData.settings || {};
		this.randomize = settings.randomize_options || false;
        
		this.init();
	}
    
	/**
     * Initialize the ordering interface
     */
	init() {
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
	shuffleArray(array) {
		const shuffled = [...array];
		for (let i = shuffled.length - 1; i > 0; i--) {
			const j = Math.floor(Math.random() * (i + 1));
			[shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
		}
		return shuffled;
	}
    
	/**
     * Prepare initial order from saved answer or randomize
     * Returns array of option IDs in the order to display
     */
	prepareOrder() {
		if (Array.isArray(this.selectedAnswer) && this.selectedAnswer.length === this.items.length) {
			// Restore saved user order
			const firstAnswer = this.selectedAnswer[0];
			const hasIds = this.items.some(item => {
				const itemId = (item && typeof item === 'object' && item.id) ? String(item.id).trim() : '';
				return itemId === firstAnswer;
			});
            
			if (hasIds) {
				// Restore saved order (already option IDs)
				this.currentOrder = this.selectedAnswer.map(id => String(id).trim()).filter(id => id !== '');
			} else {
				// Convert indices to option IDs
				this.currentOrder = this.selectedAnswer.map(index => {
					const item = this.items[index];
					return (item && typeof item === 'object' && item.id) ? String(item.id).trim() : '';
				}).filter(id => id !== '');
			}
		} else {
			// No saved answer - always randomize on frontend (unless in review mode)
			if (!this.isReviewMode) {
				// Randomize the order for display so students must figure out correct sequence
				this.currentOrder = this.shuffleArray([...this.correctOrder]);
			} else {
				// In review mode, show correct order
				this.currentOrder = [...this.correctOrder];
			}
		}
	}
    
	/**
     * Render the ordering interface
     */
	render() {
		// Get items in current order (by option ID)
		const orderedItems = this.currentOrder.map(optionId => {
			return this.items.find(item => {
				const itemId = (item && typeof item === 'object' && item.id) ? item.id : '';
				return itemId === optionId;
			});
		}).filter(item => item !== undefined);
        
		const itemsHtml = orderedItems.map((item, displayIndex) => {
			const itemId = (item && typeof item === 'object' && item.id) ? item.id : '';
			const itemText = (item && typeof item === 'object' && item.text) ? item.text : (typeof item === 'string' ? item : '');
			const positionNumber = displayIndex + 1;
            
			return `
                <li class="splms-ordering-item ${this.isReviewMode ? 'splms-ordering-review-mode' : ''}" 
                    draggable="${this.isReviewMode ? 'false' : 'true'}"
                    data-option-id="${this.escapeHtml(itemId)}"
                    data-display-index="${displayIndex}">
                    <span class="splms-ordering-position">${positionNumber}</span>
                    <span class="splms-ordering-handle">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 5H13M7 10H13M7 15H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span class="splms-ordering-text">${this.escapeHtml(itemText)}</span>
                    <span class="splms-ordering-arrow">→</span>
                </li>
            `;
		}).join('');
        
		const html = `
            <ul class="splms-ordering-list" id="ordering_list_${this.questionId}">
                ${itemsHtml}
            </ul>
            <input type="hidden" 
                   name="question_${this.questionId}" 
                   id="ordering_input_${this.questionId}"
                   data-question-id="${this.questionId}"
                   value="">
        `;
        
		this.container.html(html);
	}
    
	/**
     * Initialize drag and drop functionality
     */
	initDragAndDrop() {
		const self = this;
		const $list = this.container.find('.splms-ordering-list');
        
		// Make items draggable
		$list.find('.splms-ordering-item').each(function() {
			const $item = jQuery(this);
            
			$item.on('dragstart', function(e) {
				self.draggedElement = this;
				const displayIndex = parseInt($item.data('display-index'), 10);
				e.originalEvent.dataTransfer.effectAllowed = 'move';
				e.originalEvent.dataTransfer.setData('text/html', this.outerHTML);
				e.originalEvent.dataTransfer.setData('text/plain', displayIndex.toString());
				$item.addClass('splms-ordering-dragging');
			});
            
			$item.on('dragend', function() {
				$item.removeClass('splms-ordering-dragging');
				self.draggedElement = null;
			});

			// Touch events for mobile - mirror desktop behavior
			$item.on('touchstart', function() {
				self.draggedElement = this;
				$item.addClass('splms-ordering-dragging');
			});

			$item.on('touchend', function() {
				$item.removeClass('splms-ordering-dragging');
				self.draggedElement = null;
			});
		});
        
		// Handle drop zones
		$list.on('dragover', '.splms-ordering-item', function(e) {
			e.preventDefault();
			e.originalEvent.dataTransfer.dropEffect = 'move';
			if (this !== self.draggedElement) {
				jQuery(this).addClass('splms-ordering-drag-over');
			}
		});
        
		$list.on('dragleave', '.splms-ordering-item', function() {
			jQuery(this).removeClass('splms-ordering-drag-over');
		});
        
		$list.on('drop', '.splms-ordering-item', function(e) {
			e.preventDefault();
			jQuery(this).removeClass('splms-ordering-drag-over');

			if (self.isReviewMode || !self.draggedElement) {
				return false;
			}

			const draggedDisplayIndex = parseInt(jQuery(self.draggedElement).data('display-index'), 10);
			const droppedDisplayIndex = parseInt(jQuery(this).data('display-index'), 10);

			if (draggedDisplayIndex !== droppedDisplayIndex) {
				self.handleReorder(draggedDisplayIndex, droppedDisplayIndex);
			}

			return false;
		});

		// Touch drop zones - exactly like desktop
		$list.on('touchstart', '.splms-ordering-item', function() {
			if (this !== self.draggedElement) {
				jQuery(this).addClass('splms-ordering-drag-over');
			}
		});

		$list.on('touchend', '.splms-ordering-item', function() {
			jQuery(this).removeClass('splms-ordering-drag-over');

			if (self.isReviewMode || !self.draggedElement || this === self.draggedElement) {
				return;
			}

			const draggedDisplayIndex = parseInt(jQuery(self.draggedElement).data('display-index'), 10);
			const droppedDisplayIndex = parseInt(jQuery(this).data('display-index'), 10);

			if (draggedDisplayIndex !== droppedDisplayIndex) {
				self.handleReorder(draggedDisplayIndex, droppedDisplayIndex);
			}
		});
	}

	/**
	 * Handle reordering logic (shared between drag and click interactions)
	 */
	handleReorder(fromIndex, toIndex) {
		if (fromIndex === toIndex || this.isReviewMode) {
			return;
		}

		// Reorder the currentOrder array (which contains option IDs)
		const [removed] = this.currentOrder.splice(fromIndex, 1);
		this.currentOrder.splice(toIndex, 0, removed);

		// Re-render with updated positions
		this.render();
		this.initDragAndDrop();
		this.initMobileSupport();
		this.updateAnswer();

		// Add a subtle animation to show the reorder happened
		const $list = this.container.find('.splms-ordering-list');
		$list.addClass('splms-ordering-reordered');
		setTimeout(() => {
			$list.removeClass('splms-ordering-reordered');
		}, 300);
	}

	/**
	 * Initialize mobile click-to-select functionality
	 */
	initMobileSupport() {
		const self = this;
		let selectedItem = null;
		let selectedIndex = -1;

		// Click on items to select them for mobile
		this.container.find('.splms-ordering-item').on('click', function(e) {
			e.preventDefault();
			e.stopPropagation();

			const $item = jQuery(this);
			const itemIndex = parseInt($item.data('display-index'), 10);

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
			const targetIndex = itemIndex;

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
		jQuery(document).on('click', function(e) {
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
	showMobileInstruction(text) {
		let $instruction = this.container.find('.splms-mobile-instruction');
		if (!$instruction.length) {
			$instruction = jQuery('<div class="splms-mobile-instruction"></div>');
			this.container.prepend($instruction);
		}
		$instruction.text(text).show();
	}

	/**
	 * Hide mobile instruction
	 */
	hideMobileInstruction() {
		this.container.find('.splms-mobile-instruction').hide();
	}

	/**
     * Update the hidden input with current answer order
     * Stores array of option IDs in the order they appear
     */
	updateAnswer() {
		const $hiddenInput = this.container.find(`#ordering_input_${this.questionId}`);
		// Store array of option IDs in current order
		const answerJson = JSON.stringify(this.currentOrder);
		$hiddenInput.val(answerJson);
        
		// Also update quiz answers directly
		if (this.quiz && this.quiz.answers) {
			this.quiz.answers[this.questionId] = this.currentOrder;
			if (typeof this.quiz.saveQuizState === 'function') {
				this.quiz.saveQuizState();
			}
		}
        
		// Trigger change event
		const hiddenInputElement = $hiddenInput[0];
		if (hiddenInputElement) {
			const nativeEvent = new Event('change', { bubbles: true });
			hiddenInputElement.dispatchEvent(nativeEvent);
		}
		$hiddenInput.trigger('change');
	}
    
	/**
     * Get current answer as array
     */
	getAnswer() {
		return [...this.currentOrder];
	}
    
	/**
     * Escape HTML
     */
	escapeHtml(text) {
		const map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			'\'': '&#039;'
		};
		return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
	}
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
	module.exports = SPLMSQuestionOrdering;
}

// Make available globally
if (typeof window !== 'undefined') {
	window.SPLMSQuestionOrdering = SPLMSQuestionOrdering;
}

