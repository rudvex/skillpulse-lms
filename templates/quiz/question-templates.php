<?php
/**
 * Quiz Question Templates - JavaScript Templates for Quiz Interface
 *
 * This template contains all the underscore.js templates used for rendering quiz questions,
 * navigation, results, and other quiz interface components.
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/quiz/question-templates.php
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- Quiz Question Templates -->
<script type="text/html" id="tmpl-quiz-question">
	<div class="splms-quiz-question-card" data-question-id="{{{data.id}}}" data-question-type="{{{data.type}}}">
		<div class="splms-quiz-question-header">
			<div class="splms-quiz-question-number">
				<?php esc_html_e( 'Question', 'skillpulse-lms' ); ?> {{{data.questionNumber}}} <?php esc_html_e( 'of', 'skillpulse-lms' ); ?> {{{data.totalQuestions}}}
			</div>
			<# if (data.points) { #>
				<div class="splms-quiz-question-points">
					{{{data.points}}} <?php esc_html_e( 'points', 'skillpulse-lms' ); ?>
				</div>
			<# } #>
		</div>

		<div class="splms-quiz-question-content">
			<h3 class="splms-quiz-question-title">{{{data.question}}}</h3>
			<# if (data.description) { #>
				<div class="splms-quiz-question-description">{{{data.description}}}</div>
			<# } #>
		</div>

		<div class="splms-quiz-question-options">
			<# if (data.optionsHtml) { #>
				{{{data.optionsHtml}}}
			<# } else { #>
				<!-- Render placeholder (options should be provided via optionsHtml) -->
				<div class="splms-quiz-options-placeholder">Loading options...</div>
			<# } #>
		</div>

		<# if (data.explanation && data.showExplanation) { #>
			<div class="splms-note splms-note-info splms-quiz-question-explanation">
				<h4><?php esc_html_e( 'Explanation:', 'skillpulse-lms' ); ?></h4>
				<p>{{{data.explanation}}}</p>
			</div>
		<# } #>
	</div>
</script>

<script type="text/html" id="tmpl-quiz-navigation">
	<div class="splms-quiz-navigation">
		<# if (data.currentPage > 1) { #>
			<button type="button" class="splms-btn splms-btn-secondary" id="prev-question-btn">
				← <?php esc_html_e( 'Previous', 'skillpulse-lms' ); ?>
			</button>
		<# } #>

		<div class="splms-quiz-progress">
			<div class="splms-quiz-progress-bar">
				<div class="splms-quiz-progress-fill" style="width: {{{data.progressPercent}}}%;"></div>
			</div>
			<div class="splms-quiz-progress-text">
				{{{data.currentPage}}} / {{{data.totalPages}}}
			</div>
		</div>

		<# if (data.currentPage < data.totalPages) { #>
			<button type="button" class="splms-btn splms-btn-primary" id="next-question-btn">
				<?php esc_html_e( 'Next', 'skillpulse-lms' ); ?> →
			</button>
		<# } else { #>
			<button type="button" class="splms-btn splms-btn-primary" id="submit-quiz-btn">
				🎯 <?php esc_html_e( 'Submit Quiz', 'skillpulse-lms' ); ?>
			</button>
		<# } #>
	</div>
</script>

<script type="text/html" id="tmpl-quiz-timer">
	<div class="splms-quiz-timer">
		<div class="splms-quiz-timer-icon">⏰</div>
		<div class="splms-quiz-timer-text">
			<div class="splms-quiz-timer-label"><?php esc_html_e( 'Time Remaining', 'skillpulse-lms' ); ?></div>
			<div class="splms-quiz-timer-value">{{{data.timeDisplay}}}</div>
		</div>
	</div>
</script>

<script type="text/html" id="tmpl-quiz-loading">
	<div class="splms-quiz-loading">
		<div class="splms-quiz-loading-spinner"></div>
		<div class="splms-quiz-loading-text">{{{data.message}}}</div>
	</div>
</script>

<!-- Quiz Results Template -->
<script type="text/html" id="tmpl-splms-quiz-results">
	<div class="splms-quiz-results-container">
		<!-- Result Header -->
		<div class="splms-quiz-result-header {{data.statusClass}}">
			<div class="splms-quiz-result-icon">
				<span class="splms-quiz-result-status-icon">{{{data.statusIcon}}}</span>
			</div>
			<div class="splms-quiz-result-title">
				<h2 class="splms-quiz-result-heading">{{{data.heading}}}</h2>
				<p class="splms-quiz-result-subtitle">{{{data.subtitle}}}</p>
			</div>
		</div>

		<!-- Quiz Type Notices -->
		<# if (data.is_practice) { #>
			<div class="splms-quiz-results-notice splms-quiz-practice-notice">
				<p><?php esc_html_e( 'This is a practice quiz. Your results do not affect your course progress.', 'skillpulse-lms' ); ?></p>
			</div>
		<# } else if (data.is_survey) { #>
			<div class="splms-quiz-results-notice splms-quiz-survey-notice">
				<p><?php esc_html_e( 'Thank you for completing this survey. Your responses have been recorded.', 'skillpulse-lms' ); ?></p>
			</div>
		<# } #>

		<!-- Score Summary -->
		<div class="splms-quiz-score-summary">
			<div class="splms-quiz-score-item">
				<div class="splms-quiz-score-value splms-quiz-score-percentage">{{data.score_percentage}}%</div>
				<div class="splms-quiz-score-label"><?php esc_html_e( 'Score', 'skillpulse-lms' ); ?></div>
			</div>
			<div class="splms-quiz-score-item">
				<div class="splms-quiz-score-value splms-quiz-correct-answers">{{data.correct_answers}}</div>
				<div class="splms-quiz-score-label"><?php esc_html_e( 'Correct', 'skillpulse-lms' ); ?></div>
			</div>
			<div class="splms-quiz-score-item">
				<div class="splms-quiz-score-value splms-quiz-total-questions">{{data.total_questions}}</div>
				<div class="splms-quiz-score-label"><?php esc_html_e( 'Total', 'skillpulse-lms' ); ?></div>
			</div>
			<div class="splms-quiz-score-item">
				<div class="splms-quiz-score-value splms-quiz-time-taken">{{{data.time_taken}}}</div>
				<div class="splms-quiz-score-label"><?php esc_html_e( 'Time', 'skillpulse-lms' ); ?></div>
			</div>
		</div>

		<!-- Pass/Fail Status (hide for surveys) -->
		<# if (!data.is_survey) { #>
			<div class="splms-quiz-pass-fail-status {{data.statusClass}}">
				<div class="splms-quiz-status-content">
					<div class="splms-quiz-status-message">{{{data.statusMessage}}}</div>
					<div class="splms-quiz-passing-grade-info">
						<?php esc_html_e( 'Passing grade:', 'skillpulse-lms' ); ?> <span class="splms-quiz-passing-grade">{{data.passing_grade}}</span>%
					</div>
				</div>
			</div>
		<# } #>

		<!-- Detailed Results (if show answers is enabled) -->
		<div class="splms-quiz-detailed-results" style="display: none;">
			<h3><?php esc_html_e( 'Question Review', 'skillpulse-lms' ); ?></h3>
			<div class="splms-quiz-questions-review">
				<!-- Questions will be populated via JavaScript -->
			</div>
		</div>

		<!-- Actions -->
		<div class="splms-quiz-result-actions">
			<div class="splms-quiz-action-buttons">
				<!-- Retake button (if attempts remaining) -->
				<# if (data.retake_available) { #>
					<button type="button" class="splms-btn splms-btn-primary splms-quiz-retake-btn retake-quiz-btn">
						<svg class="splms-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M1 4V10H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M23 20V14H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10M23 14L18.36 18.36A9 9 0 0 1 3.51 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<?php esc_html_e( 'Retake Quiz', 'skillpulse-lms' ); ?>
					</button>
				<# } #>

				<!-- View answers button (if allowed) -->
				<# if (data.show_answers) { #>
					<button type="button" class="splms-btn splms-btn-secondary splms-quiz-view-answers-btn view-answers-btn">
						<svg class="splms-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M1 12S5 4 12 4S23 12 23 12S19 20 12 20S1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<?php esc_html_e( 'View Answers', 'skillpulse-lms' ); ?>
					</button>
				<# } #>

				<!-- Next button -->
				<button type="button" class="splms-btn splms-btn-primary splms-quiz-next-btn next-btn">
					<svg class="splms-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					<?php esc_html_e( 'Next', 'skillpulse-lms' ); ?>
				</button>

				<!-- View Course button -->
				<button type="button" class="splms-btn splms-btn-secondary splms-quiz-view-course-btn view-course-btn">
					<svg class="splms-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M2 3H8C9.1 3 10 3.9 10 5V19C10 20.1 9.1 21 8 21H2V3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M22 3H16C14.9 3 14 3.9 14 5V19C14 20.1 14.9 21 16 21H22V3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					<?php esc_html_e( 'View Course', 'skillpulse-lms' ); ?>
				</button>
			</div>
		</div>

		<!-- Attempt History -->
		<div class="splms-quiz-attempt-history">
			<h4><?php esc_html_e( 'Attempt History', 'skillpulse-lms' ); ?></h4>
			<div class="splms-quiz-attempts-list">
				<!-- Will be populated via JavaScript -->
			</div>
		</div>
	</div>
</script>

<!-- Separate Question Type Templates -->
<!-- Multiple Choice Question Template -->
<script type="text/html" id="tmpl-quiz-question-multiple-choice">
	<# if (data.options && Array.isArray(data.options) && data.options.length > 0) { #>
		<# _.each(data.options, function(option, index) { #>
			<#
			var optionText = '';

			if (option && typeof option === 'object') {
				optionText = option.text ? option.text : (option.option_text ? option.option_text : (option.label ? option.label : ''));
			} else if (typeof option === 'string') {
				optionText = option;
			} else {
				optionText = String(option);
			}

			// Ensure we have a value.
			if (!optionText) optionText = 'option_' + index;

			var isSelected = data.selectedAnswer == optionText || data.selectedAnswer === String(optionText);
			#>
			<label class="option-label answer-option <# if (isSelected) { #>selected<# } #>">
				<input type="radio"
						id="q{{{data.id}}}_option{{index}}"
						name="question_{{{data.id}}}"
						value="{{{optionText}}}"
						data-question-id="{{{data.id}}}"
						<# if (isSelected) { #>checked<# } #>/>
				<span class="option-text">{{optionText}}</span>
			</label>
		<# }); #>
	<# } else { #>
		<!-- No options available -->
		<div class="splms-quiz-no-options">No options available for this question.</div>
	<# } #>
</script>

<!-- Multiple Select Question Template -->
<script type="text/html" id="tmpl-quiz-question-multiple-select">
	<#
	var selectedAnswers = Array.isArray(data.selectedAnswer) ? data.selectedAnswer : (data.selectedAnswer ? [data.selectedAnswer] : []);
	#>
	<# _.each(data.options, function(option, index) { #>
		<# var optionText = (option && typeof option === 'object' && option.text) ? option.text : (typeof option === 'string' ? option : ''); #>
		<# var isSelected = _.indexOf(selectedAnswers, optionText) !== -1 || _.indexOf(selectedAnswers, String(optionText)) !== -1; #>
		<label class="option-label answer-option <# if (isSelected) { #>selected<# } #>">
			<input type="checkbox"
					id="q{{{data.id}}}_option{{index}}"
					name="question_{{{data.id}}}[]"
					value="{{{optionText}}}"
					data-question-id="{{{data.id}}}"
					<# if (isSelected) { #>checked<# } #>/>
			<span class="option-text">{{{optionText}}}</span>
		</label>
	<# }); #>
</script>

<!-- True/False Question Template -->
<script type="text/html" id="tmpl-quiz-question-true-false">
	<#
	// True/False questions should use option IDs from database (like multiple choice).
	// Find True and False options from the options array.
	var trueOption = null;
	var falseOption = null;

	if (data.options && Array.isArray(data.options) && data.options.length > 0) {
		data.options.forEach(function(option) {
			if (option && typeof option === 'object') {
				var optionText = (option.text || option.option_text || '').toLowerCase().trim();
				if (optionText === 'true') {
					trueOption = option;
				} else if (optionText === 'false') {
					falseOption = option;
				}
			}
		});
	}

	var trueText = trueOption ? (trueOption.text || 'True') : 'True';
	var falseText = falseOption ? (falseOption.text || 'False') : 'False';

	var isTrueSelected = data.selectedAnswer == trueText || data.selectedAnswer === String(trueText);
	var isFalseSelected = data.selectedAnswer == falseText || data.selectedAnswer === String(falseText);
	#>
	<label class="option-label answer-option <# if (isTrueSelected) { #>selected<# } #>">
		<input type="radio"
				id="q{{{data.id}}}_true"
				name="question_{{{data.id}}}"
				value="{{{trueText}}}"
				data-question-id="{{{data.id}}}"
				<# if (isTrueSelected) { #>checked<# } #>/>
		<span class="option-text">{{{trueText}}}</span>
	</label>
	<label class="option-label answer-option <# if (isFalseSelected) { #>selected<# } #>">
		<input type="radio"
				id="q{{{data.id}}}_false"
				name="question_{{{data.id}}}"
				value="{{{falseText}}}"
				data-question-id="{{{data.id}}}"
				<# if (isFalseSelected) { #>checked<# } #>/>
		<span class="option-text">{{{falseText}}}</span>
	</label>
</script>

<!-- Short Answer / Fill Blank Question Template -->
<script type="text/html" id="tmpl-quiz-question-short-answer">
	<# var selectedValue = Array.isArray(data.selectedAnswer) ? data.selectedAnswer[0] || '' : (data.selectedAnswer || ''); #>
	<div class="short-answer-container">
		<input type="text"
				id="q{{{data.id}}}_answer"
				name="question_{{{data.id}}}"
				data-question-id="{{{data.id}}}"
				value="{{{selectedValue}}}"
				placeholder="<?php esc_attr_e( 'Enter your answer...', 'skillpulse-lms' ); ?>"
				class="short-answer-input">
	</div>
</script>

<!-- Essay Question Template -->
<script type="text/html" id="tmpl-quiz-question-essay">
	<# var selectedValue = Array.isArray(data.selectedAnswer) ? data.selectedAnswer[0] || '' : (data.selectedAnswer || ''); #>
	<div class="essay-container">
		<textarea id="q{{{data.id}}}_answer"
					name="question_{{{data.id}}}"
					data-question-id="{{{data.id}}}"
					placeholder="<?php esc_attr_e( 'Enter your essay answer...', 'skillpulse-lms' ); ?>"
					rows="6"
					class="essay-textarea">{{{selectedValue}}}</textarea>
	</div>
</script>

<!-- Matching Question Template -->
<script type="text/html" id="tmpl-quiz-question-matching">
	<# var pairs = Array.isArray(data.pairs) ? data.pairs : []; #>
	<div class="splms-matching-container" data-question-id="{{{data.id}}}">
		<p class="splms-matching-instruction"><?php esc_html_e( 'Drag items from the right column to match them with items on the left.', 'skillpulse-lms' ); ?></p>
		<!-- Drag-and-drop interface will be rendered by question-matching.js -->
	</div>
</script>

<!-- Question Review/Result Template -->
<script type="text/html" id="tmpl-quiz-question-review">
	<div class="question-review-item question-result-item <# if (data.isCorrect) { #>correct<# } else { #>incorrect<# } #> <# if (data.needsReview) { #>pending-review<# } #>" data-question-id="{{{data.questionId}}}">
		<div class="question-review-header question-result-header">
			<span class="question-number">{{{data.questionNumber}}}.</span>
			<span class="question-status result-icon <# if (data.isCorrect) { #>correct<# } else { #>incorrect<# } #> <# if (data.needsReview) { #>pending<# } #>">{{{data.reviewIcon}}}</span>
			<span class="question-points">{{{data.points}}} <?php esc_html_e( 'points', 'skillpulse-lms' ); ?><# if (data.needsReview) { #> <span class="pending-badge">(<?php esc_html_e( 'Pending Review', 'skillpulse-lms' ); ?>)</span><# } #></span>
		</div>
		<div class="question-review-content">
			<# console.log("data", data); #>
			<h5 class="question-text">{{{data.questionText}}}</h5>
			<div class="answer-comparison">
				<div class="user-answer">
					<strong><?php esc_html_e( 'Your Answer:', 'skillpulse-lms' ); ?></strong>
					<span class="answer-value <# if (data.isCorrect) { #>correct-answer<# } else { #>incorrect-answer<# } #>">{{{data.userAnswerDisplay}}}</span>
				</div>
				<# if (!data.isCorrect || data.needsReview) { #>
					<div class="correct-answer">
						<strong><# if (data.needsReview) { #><?php esc_html_e( 'Status:', 'skillpulse-lms' ); ?><# } else if (data.questionType === 'file_upload') { #><?php esc_html_e( 'Status:', 'skillpulse-lms' ); ?><# } else { #><?php esc_html_e( 'Correct Answer:', 'skillpulse-lms' ); ?><# } #></strong>
						<span class="answer-value <# if (data.needsReview) { #>pending-answer<# } else { #>correct-answer<# } #>">{{{data.correctAnswerDisplay}}}</span>
					</div>
				<# } #>
			</div>
			<# if (data.explanation) { #>
				<div class="question-explanation">
					<strong><?php esc_html_e( 'Explanation:', 'skillpulse-lms' ); ?></strong>
					<p>{{{data.explanation}}}</p>
				</div>
			<# } #>
			<# if (data.showFeedback && data.feedback) { #>
				<div class="question-feedback <# if (data.isCorrect) { #>feedback-correct<# } else { #>feedback-incorrect<# } #>">
					<strong><?php esc_html_e( 'Feedback:', 'skillpulse-lms' ); ?></strong>
					<p>{{{data.feedback}}}</p>
				</div>
			<# } #>
			<# if (data.needsReview) { #>
				<div class="pending-review-notice">
					<strong>⏳ <?php esc_html_e( 'This question requires manual review by your instructor.', 'skillpulse-lms' ); ?></strong>
					<p><?php esc_html_e( 'Your answer has been submitted and will be graded soon. The final score will be updated after review.', 'skillpulse-lms' ); ?></p>
				</div>
			<# } #>
		</div>
	</div>
</script>

<!-- Matching Results Template -->
<script type="text/html" id="tmpl-quiz-matching-results">
	<div class="splms-matching-results-container">
		<# _.each(data.pairs, function(pair) { #>
			<div class="splms-matching-row <# if (pair.isCorrect) { #>splms-match-correct<# } else { #>splms-match-wrong<# } #>">
				<div class="splms-matching-left-item">
					<span class="splms-matching-left-label">{{{pair.leftText}}}</span>
				</div>
				<div class="splms-matching-drop-zone splms-matching-has-match">
					<# if (pair.userRight) { #>
						<div class="splms-matching-chip">
							<span class="splms-matching-chip-text">{{{pair.userRight}}}</span>
						</div>
					<# } else { #>
						<span class="splms-matching-drop-hint">—</span>
					<# } #>
				</div>
			</div>
		<# }); #>
	</div>
</script>

<!-- Ordering Question Template -->
<script type="text/html" id="tmpl-quiz-question-ordering">
	<#
	// Get items from data.items or build from options if items not available.
	var items = [];
	if (Array.isArray(data.items) && data.items.length > 0) {
		items = data.items;
	} else if (Array.isArray(data.options) && data.options.length > 0) {
		// Build items from options if items not available.
		items = _.map(data.options, function(option) {
			return {
				id: option.text || option.option_text || '',
				text: option.text || option.option_text || ''
			};
		});
	}

	// Get user's selected order (array of text values).
	var userOrder = Array.isArray(data.selectedAnswer) ? data.selectedAnswer : [];

	// Build display items - use text values for ordering.
	var displayItems = [];
	if (userOrder.length === items.length) {
		// User has an order - map userOrder to items by text.
		displayItems = _.map(userOrder, function(orderText) {
			return _.find(items, function(item) {
				var itemText = (item && typeof item === 'object' && item.text) ? item.text : '';
				return itemText === orderText || itemText === String(orderText);
			});
		}).filter(function(item) { return item !== undefined; });
	}

	// If no valid order, randomize items for initial display.
	if (displayItems.length !== items.length) {
		// Shuffle items array using Fisher-Yates algorithm.
		displayItems = items.slice();
		for (var i = displayItems.length - 1; i > 0; i--) {
			var j = Math.floor(Math.random() * (i + 1));
			var temp = displayItems[i];
			displayItems[i] = displayItems[j];
			displayItems[j] = temp;
		}
	}

	// Build ordered text values for hidden input.
	var orderedTexts = _.map(displayItems, function(item) {
		return (item && typeof item === 'object' && item.text) ? item.text : '';
	}).filter(function(text) { return text !== ''; });
	#>

	<div class="splms-ordering-container" data-question-id="{{{data.id}}}">
		<p class="splms-ordering-instruction"><?php esc_html_e( 'Drag items to reorder them correctly:', 'skillpulse-lms' ); ?></p>
		<ul class="splms-ordering-list" id="ordering_{{{data.id}}}">
			<# _.each(displayItems, function(item, index) { #>
				<# var itemText = (item && typeof item === 'object' && item.text) ? item.text : (typeof item === 'string' ? item : ''); #>
				<# var positionNumber = index + 1; #>
				<li class="splms-ordering-item"
					data-item-text="{{{itemText}}}"
					data-display-index="{{index}}">
					<span class="splms-ordering-position">{{positionNumber}}</span>
					<span class="splms-ordering-handle">
						<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M7 5H13M7 10H13M7 15H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
						</svg>
					</span>
					<span class="splms-ordering-text">{{{itemText}}}</span>
					<span class="splms-ordering-arrow">→</span>
				</li>
			<# }); #>
		</ul>
		<input type="hidden"
				name="question_{{{data.id}}}"
				id="ordering_input_{{{data.id}}}"
				data-question-id="{{{data.id}}}"
				value="{{{JSON.stringify(orderedTexts)}}}">
	</div>
</script>

<!-- File Upload Question Template -->
<script type="text/html" id="tmpl-quiz-question-file-upload">
	<# var allowedTypesStr = data.allowed_types || 'pdf,docx,jpg,png'; #>
	<# var allowedTypes = allowedTypesStr.split(','); #>
	<# var maxSize = data.max_file_size || 10; #>
	<# var allowedTypesDisplay = []; #>
	<# var allowedTypesAccept = []; #>
	<# _.each(allowedTypes, function(type) { #>
		<# var trimmedType = type.trim(); #>
		<# allowedTypesDisplay.push(trimmedType.toUpperCase()); #>
		<# allowedTypesAccept.push('.' + trimmedType); #>
	<# }); #>
	<div class="file-upload-container" data-question-id="{{{data.id}}}">
		<div class="file-upload-note">
			<p><strong><?php esc_html_e( 'Note:', 'skillpulse-lms' ); ?></strong> <?php esc_html_e( 'This question requires manual grading.', 'skillpulse-lms' ); ?></p>
			<p><?php esc_html_e( 'Allowed file types:', 'skillpulse-lms' ); ?> {{{allowedTypesDisplay.join(', ')}}}</p>
			<p><?php esc_html_e( 'Maximum file size:', 'skillpulse-lms' ); ?> {{maxSize}} MB</p>
		</div>
		<# if (data.selectedAnswer) { #>
			<div class="file-upload-preview">
				<p><?php esc_html_e( 'Current file:', 'skillpulse-lms' ); ?> <strong>{{{data.selectedAnswer}}}</strong></p>
				<button type="button" class="splms-btn splms-btn-secondary remove-file-btn" data-question-id="{{{data.id}}}">
					<?php esc_html_e( 'Remove File', 'skillpulse-lms' ); ?>
				</button>
			</div>
		<# } #>
		<div class="file-upload-field">
			<input type="file"
					id="file_{{{data.id}}}"
					name="question_{{{data.id}}}"
					data-question-id="{{{data.id}}}"
					accept="{{{allowedTypesAccept.join(',')}}}"
					data-max-size="{{maxSize * 1024 * 1024}}"
					class="file-upload-input">
			<label for="file_{{{data.id}}}" class="file-upload-label">
				<span class="file-upload-button"><?php esc_html_e( 'Choose File', 'skillpulse-lms' ); ?></span>
				<span class="file-upload-text"><?php esc_html_e( 'No file chosen', 'skillpulse-lms' ); ?></span>
			</label>
		</div>
	</div>
</script>
