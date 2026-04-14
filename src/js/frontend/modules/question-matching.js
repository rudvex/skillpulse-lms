/**
 * SkillPulse LMS Matching Question Handler
 * Handles drag-and-drop matching interface with drop zones for matching-type quiz questions
 */
class SPLMSQuestionMatching {
	constructor(container, questionData, quizInstance) {
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
	init() {
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
	prepareItems() {
		// Extract right-side items
		let rightItems = this.pairs.map((pair, index) => ({
			originalIndex: index,
			text: pair && typeof pair === 'object' && pair.right ? pair.right : ''
		})).filter(item => item.text);
        
		// Shuffle right items if randomize is enabled
		if (this.randomize && !this.isReviewMode) {
			rightItems = this.shuffleArray([...rightItems]);
		}
        
		this.availableItems = [...rightItems];
        
		// Restore saved matches if available
		if (Array.isArray(this.selectedAnswer) && this.selectedAnswer.length > 0) {
			// selectedAnswer is an array of right-side values in order
			// We need to map them to left indices
			this.selectedAnswer.forEach((rightText, leftIndex) => {
				if (rightText && this.pairs[leftIndex]) {
					const normalizedRight = this.normalizeText(rightText);
					const foundItem = this.availableItems.find(item => 
						this.normalizeText(item.text) === normalizedRight
					);
                    
					if (foundItem) {
						this.matches[leftIndex] = foundItem.text;
						// Remove from available items
						this.availableItems = this.availableItems.filter(item => 
							item.text !== foundItem.text
						);
					}
				}
			});
		}
	}
    
	/**
     * Render the matching interface
     */
	render() {
		const leftItems = this.pairs.map((pair, index) => {
			const leftText = pair && typeof pair === 'object' && pair.left ? pair.left : '';
			const matchedText = this.matches[index] || '';
			const hasMatch = !!matchedText;
            
			return `
                <div class="splms-matching-row" data-left-index="${index}">
                    <div class="splms-matching-left-item">
                        <span class="splms-matching-left-label">${this.escapeHtml(leftText)}</span>
                    </div>
                    <div class="splms-matching-drop-zone ${hasMatch ? 'splms-matching-has-match' : ''}" 
                         data-drop-index="${index}"
                         ${!this.isReviewMode ? 'droppable="true"' : ''}>
                        ${hasMatch ? `
                            <div class="splms-matching-chip" data-matched-index="${index}">
                                <span class="splms-matching-chip-text">${this.escapeHtml(matchedText)}</span>
                                ${!this.isReviewMode ? '<button type="button" class="splms-matching-chip-remove" data-remove-index="${index}" aria-label="Remove match">×</button>' : ''}
                            </div>
                        ` : `
                            <span class="splms-matching-drop-hint">${this.getLocalizedText('Drop here', 'Drop here')}</span>
                        `}
                    </div>
                </div>
            `;
		}).join('');
        
		const availableChips = this.availableItems.map((item, index) => `
            <div class="splms-matching-chip splms-matching-chip-available ${this.isReviewMode ? 'splms-matching-review-mode' : ''}" 
                 draggable="${this.isReviewMode ? 'false' : 'true'}"
                 data-item-text="${this.escapeHtml(item.text)}"
                 data-item-index="${index}">
                <span class="splms-matching-chip-handle">☰</span>
                <span class="splms-matching-chip-text">${this.escapeHtml(item.text)}</span>
            </div>
        `).join('');
        
		const html = `
            <div class="splms-matching-interface">
                <div class="splms-matching-pairs">
                    ${leftItems}
                </div>
                <div class="splms-matching-available-section">
                    <div class="splms-matching-section-header">
                        <strong>${this.getLocalizedText('Available Items', 'Available Items')}</strong>
                    </div>
                    <div class="splms-matching-available-chips">
                        ${availableChips}
                    </div>
                </div>
            </div>
            <input type="hidden" 
                   name="question_${this.questionId}" 
                   id="matching-answer-${this.questionId}"
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

		// Make available chips draggable
		this.container.find('.splms-matching-chip-available').each(function() {
			const $chip = jQuery(this);

			// Desktop drag events
			$chip.on('dragstart', function(e) {
				self.draggedItem = {
					text: $chip.data('item-text'),
					element: $chip
				};
				e.originalEvent.dataTransfer.effectAllowed = 'move';
				e.originalEvent.dataTransfer.setData('text/html', this.outerHTML);
				$chip.addClass('splms-matching-dragging');
			});

			$chip.on('dragend', function() {
				$chip.removeClass('splms-matching-dragging');
				self.draggedItem = null;
			});

			// Touch events for mobile
			$chip.on('touchstart', function(e) {
				e.preventDefault();
				self.draggedItem = {
					text: $chip.data('item-text'),
					element: $chip
				};
				$chip.addClass('splms-matching-dragging splms-matching-touch-dragging');

				// Store initial touch position
				const touch = e.originalEvent.touches[0];
				self.touchStartX = touch.clientX;
				self.touchStartY = touch.clientY;
			});

			$chip.on('touchmove', function(e) {
				if (!self.draggedItem) return;
				e.preventDefault();

				const touch = e.originalEvent.touches[0];
				const elementBelow = document.elementFromPoint(touch.clientX, touch.clientY);

				// Remove previous hover effects
				self.container.find('.splms-matching-drop-zone').removeClass('splms-matching-touch-over');

				// Add hover effect if over a drop zone
				const $dropZone = jQuery(elementBelow).closest('.splms-matching-drop-zone');
				if ($dropZone.length) {
					$dropZone.addClass('splms-matching-touch-over');
				}
			});

			$chip.on('touchend', function(e) {
				if (!self.draggedItem) return;
				e.preventDefault();

				const touch = e.originalEvent.changedTouches[0];
				const elementBelow = document.elementFromPoint(touch.clientX, touch.clientY);
				const $dropZone = jQuery(elementBelow).closest('.splms-matching-drop-zone');

				// Clean up visual states
				$chip.removeClass('splms-matching-dragging splms-matching-touch-dragging');
				self.container.find('.splms-matching-drop-zone').removeClass('splms-matching-touch-over');

				// Handle drop if over a valid drop zone
				if ($dropZone.length && !self.isReviewMode) {
					const dropIndex = parseInt($dropZone.data('drop-index'), 10);
					self.handleDrop(dropIndex);
				} else {
					self.draggedItem = null;
				}
			});
		});
        
		// Make drop zones droppable
		this.container.find('.splms-matching-drop-zone').each(function() {
			const $dropZone = jQuery(this);
			const dropIndex = parseInt($dropZone.data('drop-index'), 10);
            
			$dropZone.on('dragover', function(e) {
				e.preventDefault();
				e.originalEvent.dataTransfer.dropEffect = 'move';
				if (!self.isReviewMode) {
					$dropZone.addClass('splms-matching-drag-over');
				}
			});
            
			$dropZone.on('dragleave', function() {
				$dropZone.removeClass('splms-matching-drag-over');
			});
            
			$dropZone.on('drop', function(e) {
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
		this.container.find('.splms-matching-chip-remove').on('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
            
			const removeIndex = parseInt(jQuery(this).data('remove-index'), 10);
			const matchedText = self.matches[removeIndex];
            
			if (matchedText) {
				// Move back to available items
				self.availableItems.push({ text: matchedText, originalIndex: -1 });
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
	initMobileClickToMatch() {
		const self = this;
		let selectedChip = null;

		// Click on available chips to select them (mobile alternative)
		this.container.find('.splms-matching-chip-available').on('click', function(e) {
			e.preventDefault();
			e.stopPropagation();

			const $chip = jQuery(this);

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
		this.container.find('.splms-matching-drop-zone').on('click', function(e) {
			if (!selectedChip) return;

			e.preventDefault();
			e.stopPropagation();

			const dropIndex = parseInt(jQuery(this).data('drop-index'), 10);

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
		jQuery(document).on('click', function(e) {
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
     * Handle drop operation for both drag and touch
     */
	handleDrop(dropIndex) {
		if (this.isReviewMode || !this.draggedItem) {
			return;
		}

		// If this drop zone already has a match, move it back to available
		if (this.matches[dropIndex]) {
			const oldText = this.matches[dropIndex];
			this.availableItems.push({ text: oldText, originalIndex: -1 });
			delete this.matches[dropIndex];
		}

		// Add new match
		this.matches[dropIndex] = this.draggedItem.text;

		// Remove from available items
		this.availableItems = this.availableItems.filter(item =>
			item.text !== this.draggedItem.text
		);

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
	updateAnswer() {
		// Build answer array: [rightText for leftIndex 0, rightText for leftIndex 1, ...]
		const answerArray = this.pairs.map((pair, leftIndex) => {
			return this.matches[leftIndex] || '';
		});
        
		const $hiddenInput = this.container.find(`#matching-answer-${this.questionId}`);
		const answerJson = JSON.stringify(answerArray);
		$hiddenInput.val(answerJson);
        
		// Also update quiz answers directly
		if (this.quiz && this.quiz.answers) {
			this.quiz.answers[this.questionId] = answerArray;
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
		return this.pairs.map((pair, leftIndex) => {
			return this.matches[leftIndex] || '';
		});
	}
    
	/**
     * Shuffle array
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
    
	/**
     * Normalize text for comparison
     */
	normalizeText(text) {
		return String(text || '').toLowerCase().trim();
	}
    
	/**
     * Get localized text (placeholder for i18n)
     */
	getLocalizedText(key, fallback) {
		// TODO: Implement proper i18n if needed
		return fallback;
	}
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
	module.exports = SPLMSQuestionMatching;
}

// Make available globally
if (typeof window !== 'undefined') {
	window.SPLMSQuestionMatching = SPLMSQuestionMatching;
}
