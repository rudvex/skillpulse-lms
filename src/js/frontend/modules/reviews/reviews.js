/**
 * SkillPulse LMS Reviews
 * Handles course reviews, ratings, and review interactions
 */

export class SPLMSReviews {
	constructor() {
		this.modal = null;
		this.settings = window.splms_reviews_settings || {
			min_length: 10,
			max_length: 500
		};
		this.init();
	}

	init() {
		this.bindEvents();
		this.initRatingStars();
		this.initModal();
	}

	bindEvents() {
		// Modal triggers.
		jQuery(document).on('click', '.write-review-btn', this.openModal.bind(this));
		// Use consistent modal classes
		jQuery(document).on('click', '.modal-close, .modal-overlay, .splms-modal-close', this.closeModal.bind(this));
		jQuery(document).on('click', '.cancel-btn', this.closeModal.bind(this));

		// Star rating input.
		jQuery(document).on('click', '.star-rating-input .star-btn', this.handleStarClick.bind(this));
		jQuery(document).on('mouseenter', '.star-rating-input .star-btn', this.handleStarHover.bind(this));
		jQuery(document).on('mouseleave', '.star-rating-input', this.handleStarLeave.bind(this));

		// Review form.
		jQuery(document).on('submit', '.course-review-form', this.handleReviewSubmit.bind(this));
		jQuery(document).on('input', '.course-review-form textarea', this.handleTextareaInput.bind(this));

		// Review actions.
		jQuery(document).on('click', '.helpful-btn', this.handleHelpfulClick.bind(this));
		jQuery(document).on('click', '.load-more-btn', this.handleLoadMoreClick.bind(this));
		jQuery(document).on('change', '.reviews-sort', this.handleReviewSort.bind(this));

		// ESC key to close modal.
		jQuery(document).on('keydown', this.handleKeyDown.bind(this));
	}

	initModal() {
		this.modal = jQuery('#review-submission-modal');
	}

	initRatingStars() {
		// Initialize existing rating displays.
		jQuery('.rating-stars').each(function() {
			const rating = parseFloat(jQuery(this).data('rating'));
			const stars = jQuery(this).find('.star');

			stars.each(function(index) {
				if (index < Math.floor(rating)) {
					jQuery(this).addClass('filled');
				} else if (index < rating) {
					jQuery(this).addClass('half-filled');
				}
			});
		});
	}

	openModal(e) {
		e.preventDefault();

		if (!this.modal || this.modal.length === 0) {
			this.initModal();
		}

		if (this.modal && this.modal.length > 0) {
			this.modal.fadeIn(300);
			// Use consistent body class naming
			jQuery('body').addClass('splms-modal-open');

			// Focus on first input.
			setTimeout(() => {
				this.modal.find('.star-btn').first().focus();
			}, 300);
		}
	}

	closeModal(e) {
		if (e) {
			e.preventDefault();
		}

		if (this.modal && this.modal.length > 0) {
			this.modal.fadeOut(300);
			// Use consistent body class naming
			jQuery('body').removeClass('splms-modal-open');

			// Reset form.
			const $form = this.modal.find('.course-review-form');
			if ($form.length > 0) {
				this.resetForm($form);
			}
		}
	}

	handleKeyDown(e) {
		// Close modal on ESC key.
		if (e.key === 'Escape' || e.keyCode === 27) {
			if (this.modal && this.modal.is(':visible')) {
				this.closeModal();
			}
		}
	}

	handleStarClick(e) {
		const $star = jQuery(e.currentTarget);
		const rating = $star.data('rating');
		const $container = $star.closest('.star-rating-input');

		// Update visual state.
		$container.find('.star-btn').each(function(index) {
			jQuery(this).toggleClass('active', index < rating);
		});

		// Update hidden input.
		$container.siblings('input[name="rating"]').val(rating);

		// Update rating description.
		const $ratingLabels = $container.siblings('.rating-labels');
		const labelText = $ratingLabels.find(`[data-rating="${rating}"]`).text();
		$container.siblings('.rating-description').text(labelText);
	}

	handleStarHover(e) {
		const $star = jQuery(e.currentTarget);
		const rating = $star.data('rating');
		const $container = $star.closest('.star-rating-input');

		$container.find('.star-btn').each(function(index) {
			jQuery(this).toggleClass('hover', index < rating);
		});
	}

	handleStarLeave(e) {
		const $container = jQuery(e.currentTarget);
		$container.find('.star-btn').removeClass('hover');
	}

	handleTextareaInput(e) {
		const $textarea = jQuery(e.currentTarget);
		const maxLength = parseInt($textarea.attr('maxlength')) || this.settings.max_length;
		const currentLength = $textarea.val().length;
		const $counter = $textarea.siblings('.character-counter');

		if ($counter.length) {
			$counter.find('.current-count').text(currentLength);
			$counter.toggleClass('limit-reached', currentLength >= maxLength);
			$counter.toggleClass('warning', currentLength >= maxLength * 0.9);
		}
	}

	handleReviewSubmit(e) {
		e.preventDefault();

		const $form = jQuery(e.currentTarget);
		const courseId = $form.data('course-id') || $form.find('input[name="course_id"]').val();
		const rating = $form.find('input[name="rating"]').val();
		const reviewText = $form.find('textarea[name="review_text"]').val().trim();

		// Clear previous errors.
		$form.find('.form-error').hide().text('');
		$form.find('.form-message').hide().text('');

		// Validation.
		const minLength = this.settings.min_length;
		const maxLength = this.settings.max_length;

		if (!rating || rating < 1) {
			this.showFormError($form, 'rating-input', 'Please select a rating');
			return false;
		}

		if (reviewText.length < minLength) {
			this.showFormError($form, 'comment-input', `Please write at least ${minLength} characters for your review`);
			return false;
		}

		if (reviewText.length > maxLength) {
			this.showFormError($form, 'comment-input', `Review text cannot exceed ${maxLength} characters`);
			return false;
		}

		// Submit via REST API.
		const $submitBtn = $form.find('button[type="submit"]');
		$submitBtn.addClass('loading').prop('disabled', true);

		this.submitReviewAPI(courseId, rating, reviewText, $form);
		return false;
	}

	showFormError($form, groupClass, message) {
		const $group = $form.find(`.${groupClass}`);
		const $error = $group.find('.form-error');

		if ($error.length) {
			$error.text(message).show();
		} else {
			window.SPLMSCore.helper.showNotification(message, 'error');
		}
	}

	showFormMessage($form, message, type = 'success') {
		const $message = $form.find('.form-message');

		if ($message.length) {
			$message.removeClass('success error info').addClass(type).text(message).show();
		} else {
			window.SPLMSCore.helper.showNotification(message, type);
		}
	}

	submitReviewAPI(courseId, rating, reviewText, $form) {
		const restUrl = window.splms_frontend?.rest_url || '/wp-json/';
		const nonce = window.splms_frontend?.rest_nonce || '';

		jQuery.ajax({
			url: `${restUrl}splms/v1/courses/${courseId}/reviews`,
			type: 'POST',
			data: JSON.stringify({
				rating: parseInt(rating),
				review_text: reviewText
			}),
			contentType: 'application/json',
			beforeSend: (xhr) => {
				xhr.setRequestHeader('X-WP-Nonce', nonce);
			},
			success: (response) => {
				if (response.success) {
					const message = response.data.message || 'Review submitted successfully!';
					this.showFormMessage($form, message, 'success');
					this.resetForm($form);

					// Close modal after delay.
					setTimeout(() => {
						this.closeModal();
						window.location.reload();
					}, 2000);
				} else {
					const errorMsg = response.error?.message || 'Failed to submit review';
					this.showFormMessage($form, errorMsg, 'error');
				}
			},
			error: (xhr) => {
				let errorMsg = 'Something went wrong. Please try again.';

				if (xhr.responseJSON?.error?.message) {
					errorMsg = xhr.responseJSON.error.message;
				} else if (xhr.responseJSON?.message) {
					errorMsg = xhr.responseJSON.message;
				}

				this.showFormMessage($form, errorMsg, 'error');
			},
			complete: () => {
				$form.find('button[type="submit"]').removeClass('loading').prop('disabled', false);
			}
		});
	}

	resetForm($form) {
		$form[0].reset();
		$form.find('.star-btn').removeClass('active hover');
		$form.find('.rating-description').text('');
		$form.find('.character-counter .current-count').text('0');
		$form.find('.form-error').hide().text('');
		$form.find('.form-message').hide().text('');
	}

	handleHelpfulClick(e) {
		e.preventDefault();

		const $btn = jQuery(e.currentTarget);

		// Check if button is disabled (not logged in).
		if ($btn.prop('disabled')) {
			return;
		}

		const reviewId = $btn.data('review-id');
		const isActive = $btn.hasClass('active');

		if (!reviewId) {
			return;
		}

		$btn.addClass('loading').prop('disabled', true);

		this.voteHelpfulAPI(reviewId, $btn);
	}

	voteHelpfulAPI(reviewId, $btn) {
		const restUrl = window.splms_frontend?.rest_url || '/wp-json/';
		const nonce = window.splms_frontend?.rest_nonce || '';

		jQuery.ajax({
			url: `${restUrl}splms/v1/reviews/${reviewId}/vote`,
			type: 'POST',
			contentType: 'application/json',
			beforeSend: (xhr) => {
				xhr.setRequestHeader('X-WP-Nonce', nonce);
			},
			success: (response) => {
				if (response.success) {
					const isVoted = response.data.user_voted;
					const count = response.data.helpful_votes || 0;

					$btn.toggleClass('active', isVoted);
					$btn.find('.helpful-count').text(`${count} helpful`);

					window.SPLMSCore.helper.showNotification(
						isVoted ? 'Marked as helpful' : 'Vote removed',
						'success'
					);
				} else {
					const errorMsg = response.error?.message || 'Failed to vote';
					window.SPLMSCore.helper.showNotification(errorMsg, 'error');
				}
			},
			error: (xhr) => {
				let errorMsg = 'Something went wrong. Please try again.';

				if (xhr.responseJSON?.error?.message) {
					errorMsg = xhr.responseJSON.error.message;
				}

				window.SPLMSCore.helper.showNotification(errorMsg, 'error');
			},
			complete: () => {
				$btn.removeClass('loading').prop('disabled', false);
			}
		});
	}

	handleReviewSort(e) {
		const $select = jQuery(e.currentTarget);
		const sortValue = $select.val();
		const courseId = $select.data('course-id');

		if (!courseId) {
			return;
		}

		// Map sort values to API parameters.
		const sortMap = {
			'newest': { orderby: 'date', order: 'desc' },
			'oldest': { orderby: 'date', order: 'asc' },
			'highest': { orderby: 'rating', order: 'desc' },
			'lowest': { orderby: 'rating', order: 'asc' },
			'helpful': { orderby: 'helpful', order: 'desc' }
		};

		const sortParams = sortMap[sortValue] || sortMap['newest'];

		this.loadReviews(courseId, 1, sortParams);
	}

	handleLoadMoreClick(e) {
		e.preventDefault();

		const $btn = jQuery(e.currentTarget);
		const courseId = $btn.data('course-id');
		const page = parseInt($btn.data('page')) || 2;
		const totalPages = parseInt($btn.data('total-pages')) || 1;

		if (page > totalPages) {
			return;
		}

		this.loadMoreReviews(courseId, page);
	}

	loadReviews(courseId, page = 1, params = {}) {
		const $reviewsContent = jQuery('.splms-reviews-content');
		$reviewsContent.addClass('loading');

		const restUrl = window.splms_frontend?.rest_url || '/wp-json/';

		const queryParams = jQuery.param({
			page: page,
			per_page: 10,
			orderby: params.orderby || 'date',
			order: params.order || 'desc'
		});

		jQuery.ajax({
			url: `${restUrl}splms/v1/courses/${courseId}/reviews?${queryParams}`,
			type: 'GET',
			success: (response) => {
				if (response.success && response.data.reviews) {
					const $reviewsList = jQuery('.course-reviews-list');
					$reviewsList.empty();

					if (response.data.reviews.length > 0) {
						response.data.reviews.forEach(review => {
							const reviewHtml = this.renderReviewCard(review);
							$reviewsList.append(reviewHtml);
						});

						// Update load more button.
						const pagination = response.data.pagination;
						const $loadMore = jQuery('.load-more-reviews');

						if (pagination.current_page >= pagination.total_pages) {
							$loadMore.hide();
						} else {
							$loadMore.show();
							$loadMore.find('.load-more-btn').data('page', pagination.current_page + 1);
						}
					} else {
						$reviewsContent.html('<div class="no-reviews"><p>No reviews found.</p></div>');
					}
				}
			},
			error: () => {
				window.SPLMSCore.helper.showNotification('Failed to load reviews', 'error');
			},
			complete: () => {
				$reviewsContent.removeClass('loading');
			}
		});
	}

	loadMoreReviews(courseId, page = 2) {
		const $loadMoreBtn = jQuery('.load-more-btn');
		$loadMoreBtn.addClass('loading').prop('disabled', true);

		const restUrl = window.splms_frontend?.rest_url || '/wp-json/';

		jQuery.ajax({
			url: `${restUrl}splms/v1/courses/${courseId}/reviews?page=${page}&per_page=10`,
			type: 'GET',
			success: (response) => {
				if (response.success && response.data.reviews) {
					const $reviewsList = jQuery('.course-reviews-list');

					response.data.reviews.forEach(review => {
						const reviewHtml = this.renderReviewCard(review);
						$reviewsList.append(reviewHtml);
					});

					// Update pagination.
					const pagination = response.data.pagination;

					if (pagination.current_page >= pagination.total_pages) {
						jQuery('.load-more-reviews').hide();
					} else {
						$loadMoreBtn.data('page', pagination.current_page + 1);
					}
				}
			},
			error: () => {
				window.SPLMSCore.helper.showNotification('Failed to load more reviews', 'error');
			},
			complete: () => {
				$loadMoreBtn.removeClass('loading').prop('disabled', false);
			}
		});
	}

	renderReviewCard(review) {
		const stars = this.renderStarsHtml(review.rating);
		const timeAgo = this.timeAgo(review.created_at);
		const avatarUrl = review.user?.avatar || '';
		const userName = review.user?.name || 'Anonymous';
		const reviewText = review.review_text || '';
		const helpfulCount = review.helpful_votes || 0;
		const userVoted = review.user_voted || false;

		return `
			<div class="review-item" data-review-id="${review.review_id}">
				<div class="review-header">
					<div class="student-info">
						<div class="student-avatar">
							<img src="${avatarUrl}" alt="${userName}" width="40" height="40">
						</div>
						<div class="student-details">
							<h4 class="student-name">${userName}</h4>
							<div class="review-meta">
								<div class="review-rating">${stars}</div>
								<span class="review-date">${timeAgo} ago</span>
							</div>
						</div>
					</div>
				</div>
				<div class="review-content">
					<div class="review-text">
						<p>${reviewText}</p>
					</div>
					<div class="review-actions">
						<button class="helpful-btn ${userVoted ? 'active' : ''}" data-review-id="${review.review_id}">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M14 9V5C14 3.89543 13.1046 3 12 3C10.8954 3 10 3.89543 10 5V9L7 12V20H20.28C20.7623 20.0047 21.2304 19.8369 21.6056 19.524C21.9808 19.2111 22.2377 18.7744 22.33 18.29L23.73 11.29C23.8202 10.8048 23.7498 10.3038 23.5321 9.86619C23.3144 9.42862 22.9616 9.08262 22.53 8.88L21 8.17C20.6755 8.05752 20.3245 8.05752 20 8.17L18.47 8.88C18.0384 9.08262 17.6856 9.42862 17.4679 9.86619C17.2502 10.3038 17.1798 10.8048 17.27 11.29L18.67 18.29C18.7623 18.7744 19.0192 19.2111 19.3944 19.524C19.7696 19.8369 20.2377 20.0047 20.72 20H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
							<span class="helpful-count">${helpfulCount} helpful</span>
						</button>
					</div>
				</div>
			</div>
		`;
	}

	renderStarsHtml(rating) {
		let stars = '';
		for (let i = 1; i <= 5; i++) {
			const filled = i <= rating ? 'filled' : 'empty';
			stars += `<span class="star ${filled}">★</span>`;
		}
		return stars;
	}

	timeAgo(dateString) {
		const now = new Date();
		const date = new Date(dateString);
		const diffInSeconds = Math.floor((now - date) / 1000);

		if (diffInSeconds < 60) {
			return 'Just now';
		}
		if (diffInSeconds < 3600) {
			return Math.floor(diffInSeconds / 60) + ' minutes';
		}
		if (diffInSeconds < 86400) {
			return Math.floor(diffInSeconds / 3600) + ' hours';
		}
		if (diffInSeconds < 604800) {
			return Math.floor(diffInSeconds / 86400) + ' days';
		}
		if (diffInSeconds < 2592000) {
			return Math.floor(diffInSeconds / 604800) + ' weeks';
		}
		if (diffInSeconds < 31536000) {
			return Math.floor(diffInSeconds / 2592000) + ' months';
		}
		return Math.floor(diffInSeconds / 31536000) + ' years';
	}
}
