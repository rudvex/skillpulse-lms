/**
 * Video Tracker
 *
 * Tracks video playback progress for YouTube, Vimeo, and HTML5 videos.
 * Updates the watch percentage and dispatches events when completion requirements are met.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

class SPLMSVideoTracker {
	constructor(container, lessonId) {
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
		this.cookieKey = `splms_video_progress_${this.lessonId}`;
		this.lastSaveTime = 0;
		this.courseId = this.getCourseId();

		// Use localized REST URL for subdirectory/custom prefix compatibility.
		const frontendData = window.splms_frontend || {};
		this.restBase = (frontendData.rest_url || '/wp-json/').replace(/\/$/, '');

		this.init();
	}

	async init() {
		// Load existing progress first
		await this.loadProgress();

		switch (this.videoType) {
			case 'youtube':
				this.initYouTube();
				break;
			case 'vimeo':
				this.initVimeo();
				break;
			case 'html5':
				this.initHTML5();
				break;
			default:
				console.warn('Unknown video type:', this.videoType);
		}
	}

	initYouTube() {
		// Load YouTube IFrame API if not already loaded.
		if (!window.YT) {
			const tag = document.createElement('script');
			tag.src = 'https://www.youtube.com/iframe_api';
			const firstScriptTag = document.getElementsByTagName('script')[0];
			firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
		}

		// Wait for API to be ready.
		const initPlayer = () => {
			const playerElement = document.getElementById(`splms-youtube-player-${this.lessonId}`);
			if (!playerElement) return;

			this.player = new YT.Player(`splms-youtube-player-${this.lessonId}`, {
				height: '100%',
				width: '100%',
				videoId: this.videoId,
				playerVars: {
					rel: 0,
					modestbranding: 1
				},
				events: {
					onReady: this.onYouTubeReady.bind(this),
					onStateChange: this.onYouTubeStateChange.bind(this)
				}
			});
		};

		if (window.YT && window.YT.Player) {
			initPlayer();
		} else {
			window.onYouTubeIframeAPIReady = initPlayer;
		}
	}

	onYouTubeReady(event) {
		this.duration = event.target.getDuration();
		this.startTracking();
		// Resume from last position after a short delay
		setTimeout(() => {
			this.resumeFromLastPosition();
		}, 1000);
	}

	onYouTubeStateChange(event) {
		// YT.PlayerState.PLAYING = 1
		if (event.data === 1) {
			this.startTracking();
		} else {
			this.stopTracking();
		}
	}

	initVimeo() {
		// Load Vimeo Player API if not already loaded.
		if (!window.Vimeo) {
			const script = document.createElement('script');
			script.src = 'https://player.vimeo.com/api/player.js';
			script.onload = () => this.createVimeoPlayer();
			document.head.appendChild(script);
		} else {
			this.createVimeoPlayer();
		}
	}

	createVimeoPlayer() {
		const iframe = document.getElementById(`splms-vimeo-player-${this.lessonId}`);
		if (!iframe) return;

		this.player = new Vimeo.Player(iframe);

		this.player.getDuration().then((duration) => {
			this.duration = duration;
			// Resume from last position after getting duration
			setTimeout(() => {
				this.resumeFromLastPosition();
			}, 1000);
		});

		this.player.on('play', () => {
			this.startTracking();
		});

		this.player.on('pause', () => {
			this.stopTracking();
		});

		this.player.on('ended', () => {
			this.stopTracking();
		});
	}

	initHTML5() {
		this.player = document.getElementById(`splms-html5-player-${this.lessonId}`);
		if (!this.player) return;

		this.player.addEventListener('loadedmetadata', () => {
			this.duration = this.player.duration;
			// Resume from last position after metadata is loaded
			setTimeout(() => {
				this.resumeFromLastPosition();
			}, 500);
		});

		this.player.addEventListener('play', () => {
			this.startTracking();
		});

		this.player.addEventListener('pause', () => {
			this.stopTracking();
		});

		this.player.addEventListener('ended', () => {
			this.stopTracking();
		});
	}

	startTracking() {
		if (this.updateInterval) return;

		// Track progress every second
		this.updateInterval = setInterval(() => {
			this.updateProgress();
		}, 1000);

		// Save progress every 30 seconds
		if (!this.saveInterval) {
			this.saveInterval = setInterval(() => {
				this.saveProgress();
			}, 30000);
		}
	}

	stopTracking() {
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

	async updateProgress() {
		let currentTime = 0;

		try {
			switch (this.videoType) {
				case 'youtube':
					if (this.player && this.player.getCurrentTime) {
						currentTime = await this.player.getCurrentTime();
					}
					break;
				case 'vimeo':
					if (this.player && this.player.getCurrentTime) {
						currentTime = await this.player.getCurrentTime();
					}
					break;
				case 'html5':
					currentTime = this.player.currentTime;
					break;
			}

			if (this.duration > 0) {
				// Track watched segments (each second).
				const segment = Math.floor(currentTime);
				this.watchedSegments.add(segment);

				// Calculate watch percentage.
				const totalSegments = Math.floor(this.duration);
				this.watchPercentage = (this.watchedSegments.size / totalSegments) * 100;

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
		} catch (error) {
			console.error('Error updating video progress:', error);
		}
	}

	dispatchCompletionEvent() {
		const event = new CustomEvent('splms:videoCompleted', {
			detail: {
				lessonId: this.lessonId,
				watchPercentage: this.watchPercentage,
				completionRequired: this.completionRequired
			}
		});
		document.dispatchEvent(event);
	}

	getWatchPercentage() {
		return this.watchPercentage;
	}

	isVideoCompleted() {
		return this.isCompleted;
	}

	destroy() {
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
	getCourseId() {
		// Try to get from lesson viewer container
		const lessonContainer = document.getElementById('splms-lesson-fullscreen-container');
		if (lessonContainer && lessonContainer.dataset.courseId) {
			return parseInt(lessonContainer.dataset.courseId);
		}

		// Fallback: try to get from body class or other sources
		const bodyClasses = document.body.className;
		const courseMatch = bodyClasses.match(/course-(\d+)/);
		if (courseMatch) {
			return parseInt(courseMatch[1]);
		}

		// Last fallback: check for course ID in URL or meta
		const urlParams = new URLSearchParams(window.location.search);
		if (urlParams.get('course_id')) {
			return parseInt(urlParams.get('course_id'));
		}

		return 0;
	}

	/**
	 * Load existing progress from server and cookie
	 */
	async loadProgress() {
		try {
			// Try to load from server first
			const serverProgress = await this.fetchServerProgress();

			// Load from cookie as fallback
			const cookieProgress = this.loadFromCookie();

			// Use the most recent data
			const progress = this.selectMostRecent(serverProgress, cookieProgress);

			if (progress) {
				this.restoreProgress(progress);
			}
		} catch (error) {
			console.warn('Error loading video progress:', error);
			// Try cookie fallback
			const cookieProgress = this.loadFromCookie();
			if (cookieProgress) {
				this.restoreProgress(cookieProgress);
			}
		}
	}

	/**
	 * Fetch progress from server via REST API
	 */
	async fetchServerProgress() {
		if (!this.lessonId) return null;

		try {
			const response = await fetch(`${this.restBase}/splms/v1/lessons/${this.lessonId}/video-progress`, {
				method: 'GET',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
				}
			});

			if (response.ok) {
				const data = await response.json();
				return data && data.lastUpdated ? data : null;
			}
		} catch (error) {
			console.warn('Failed to fetch server progress:', error);
		}

		return null;
	}

	/**
	 * Load progress from cookie
	 */
	loadFromCookie() {
		try {
			const cookieValue = this.getCookie(this.cookieKey);
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
	selectMostRecent(serverProgress, cookieProgress) {
		if (!serverProgress && !cookieProgress) return null;
		if (!serverProgress) return cookieProgress;
		if (!cookieProgress) return serverProgress;

		// Compare timestamps
		const serverTime = new Date(serverProgress.lastUpdated || 0).getTime();
		const cookieTime = cookieProgress.timestamp || 0;

		return serverTime > cookieTime ? serverProgress : cookieProgress;
	}

	/**
	 * Restore progress data to tracker state
	 */
	restoreProgress(progressData) {
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
	async saveProgress() {
		const progressData = {
			segments: Array.from(this.watchedSegments),
			percentage: this.watchPercentage,
			lastPosition: this.lastPosition,
			totalWatchTime: this.totalWatchTime,
			timestamp: Date.now()
		};

		// Save to cookie immediately (fast fallback)
		this.saveToCookie(progressData);

		// Save to server (background)
		if (this.courseId) {
			this.saveToServer(progressData);
		}
	}

	/**
	 * Save progress to cookie
	 */
	saveToCookie(progressData) {
		try {
			const cookieValue = JSON.stringify(progressData);
			this.setCookie(this.cookieKey, cookieValue, 7); // 7 days
		} catch (error) {
			console.warn('Error saving progress to cookie:', error);
		}
	}

	/**
	 * Save progress to server via REST API
	 */
	async saveToServer(progressData) {
		if (!this.lessonId || !this.courseId) return;

		try {
			// Add video metadata
			const videoData = {
				course_id: this.courseId,
				segments: progressData.segments,
				percentage: progressData.percentage,
				lastPosition: progressData.lastPosition,
				totalWatchTime: progressData.totalWatchTime,
				videoType: this.videoType,
				videoId: this.videoId,
				duration: this.duration
			};

			const response = await fetch(`${this.restBase}/splms/v1/lessons/${this.lessonId}/video-progress`, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify(videoData)
			});

			if (!response.ok) {
				console.warn('Failed to save video progress to server:', response.statusText);
			}
		} catch (error) {
			console.warn('Error saving progress to server:', error);
		}
	}

	/**
	 * Resume video from last position
	 */
	async resumeFromLastPosition() {
		if (!this.lastPosition || this.lastPosition < 10) return; // Don't resume if less than 10 seconds

		try {
			switch (this.videoType) {
				case 'youtube':
					if (this.player && this.player.seekTo) {
						this.player.seekTo(this.lastPosition, true);
					}
					break;
				case 'vimeo':
					if (this.player && this.player.setCurrentTime) {
						this.player.setCurrentTime(this.lastPosition);
					}
					break;
				case 'html5':
					if (this.player) {
						this.player.currentTime = this.lastPosition;
					}
					break;
			}

		} catch (error) {
			console.warn('Error resuming from last position:', error);
		}
	}

	// ========== Cookie Utility Methods ==========

	/**
	 * Set a cookie
	 */
	setCookie(name, value, days) {
		const expires = new Date();
		expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
		document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/;SameSite=Strict`;
	}

	/**
	 * Get a cookie value
	 */
	getCookie(name) {
		const nameEQ = name + '=';
		const ca = document.cookie.split(';');
		for (let i = 0; i < ca.length; i++) {
			let c = ca[i];
			while (c.charAt(0) === ' ') c = c.substring(1, c.length);
			if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
		}
		return null;
	}
}

export default SPLMSVideoTracker;
