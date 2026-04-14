let SPLMSHelper = class {
	constructor() {
		this.init();
	}

	init() {
		this.accordion();
	}

	initNotice( id, type, message, actions = [] ) {
		let notice = {
			id: id, // prevent duplicates
			isDismissible: true,
			type: 'snackbar',
			actions: actions
		};

		// Handle the response from the server
		wp.data.dispatch( 'core/notices' ).createNotice(
			type, // Can be one of: success, info, warning, error
			message, // message
			notice
		);
	}

	toastNotice( message, type = 'success', autoClose = true, noticeActions = [], noticeActionsCallback = null ) {
		if ( !jQuery( '.splms-toast-parent' ).length ) {
			jQuery( 'body' ).append( '<div class="splms-toast-parent splms-toast-right"></div>' );
		}

		let alert = type === 'success' ? 'success'
			: type === 'error' ? 'danger'
				: type === 'warning' ? 'warning' : 'primary';

		let hasMessage = (message !== undefined && message !== null && message.trim() !== '');

		let content = jQuery( `
        <div class="splms-notification splms-is-${ alert }">
            <div class="splms-notification-content">
            <p class="${ !hasMessage ? 'splms-d-none' : '' }">${ message }</p>
            ${noticeActions.length > 0 ? '<div class="splms-notification-actions"></div>' : ''}
            </div>
            <button class="splms-notification-close">
                <span class="dashicons dashicons-dismiss"></span>
            </button>
        </div>
    ` );


		content.find( '.splms-notification-close' ).click( function () {
			content.remove();
		} );

		if ( noticeActions.length > 0 ) {
			noticeActions.forEach( action => {
				let actionButton = jQuery( '<button></button>' );
				actionButton.text( action.label );
				actionButton.addClass( 'splms-notification-action' );
				actionButton.on( 'click', function () {
					if ( noticeActionsCallback ) {
						noticeActionsCallback( action );
					}
				} );
				content.find( '.splms-notification-actions' ).append( actionButton );
			} );
		}

		jQuery( '.splms-toast-parent' ).append( content );

		if ( autoClose ) {
			setTimeout( function () {
				if ( content ) {
					content.fadeOut( 'fast', function () {
						jQuery( this ).remove();
					} );
				}
			}, 5000 );
		}
	}

	accordion() {
		jQuery( document ).on( 'click', '.splms-accordion', function ( e ) {
			e.preventDefault();
			jQuery( this ).next().slideToggle();
			jQuery( this ).toggleClass( 'active' );
			jQuery( this ).find( 'span.splms-accordion-arrow' ).toggleClass( 'dashicons-arrow-down-alt2' ).toggleClass( 'dashicons-arrow-up-alt2' );
		} );
	}

	/**
     * Modal default events
     *
     * @param {string} modalMode Modal mode
     */
	modalDefaultEvents( modalMode ) {
		if ( 'get_lesson_modal' === modalMode ) {
			this.selectFeaturedImage();
		}

		if( 'get_quiz_modal' === modalMode ) {

			if ( 'step-2' === jQuery( '.splms-form-step' ).attr( 'id' ) ) {
				/**
                 * Sortable list for questions.
                 */
				SPLMSCore.helper.sortableList( '.splms-questions-list', '.splms-questions-list__question', function ( questions_order ) {
					if ( jQuery( '.splms-form-step' ).find( '#questions_order' ).length === 0 ) {
						jQuery( '.splms-form-step' ).append( '<input type="hidden" name="questions_order" id="questions_order" value="">' );
					}
					jQuery( '#questions_order' ).val( questions_order );
				},
				'toArray'
				);
			}

		}

		if ( 'get_question_modal' === modalMode ) {
			/**
             * Sortable list for lesson/quiz and other.
             */
			SPLMSCore.helper.sortableList( '.splms-modal__window__content__body__form__answer_wrapper__items', '.splms-modal__window__content__body__form__answer_wrapper__item'  );
		}
	}

	selectFeaturedImage() {
		jQuery( '.splms-upload-image button' ).on( 'click', function ( e ) {
			e.preventDefault();
			var frame = wp.media( {
				title: 'Select or Upload Media',
				button: {
					text: 'Use this media'
				},
				multiple: false
			} );

			frame.on( 'select', function () {
				const attachment = frame.state().get( 'selection' ).first().toJSON();
				const imageWrap = jQuery( '.splms-upload-empty-image-wrap' );
				imageWrap.addClass( 'has-image' );
				imageWrap.css( 'background-image', 'url(' + attachment.url + ')' );
				imageWrap.find( '.inner-image' ).hide();
				imageWrap.find( '.inner-p' ).hide();
				imageWrap.closest( '.splms-upload-image' ).find( '.splms-btn--label' ).text( 'Change Image' );
				imageWrap.find( '#lesson_feature_image' ).val( attachment.id );

				if ( imageWrap.find( '.splms-remove-image' ).length === 0 ) {
					imageWrap.append( '<span class="dashicons dashicons-dismiss splms-remove-image"></span>' );
				}
			} );

			frame.open();
		} );

		jQuery( document ).on( 'click', '.splms-remove-image', function () {
			const imageWrap = jQuery( '.splms-upload-empty-image-wrap' );
			imageWrap.removeClass( 'has-image' );
			imageWrap.css( 'background-image', 'none' );
			imageWrap.find( '.inner-image' ).show();
			imageWrap.find( '.inner-p' ).show();
			imageWrap.closest( '.splms-upload-image' ).find( '.splms-btn--label' ).text( 'Upload Image' );
			imageWrap.find( '#lesson_feature_image' ).val( '' );
			jQuery( this ).remove();
		} );
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
	sortableList( listId, listItems, updateCallback = null, sortableOrder = 'serialize', sortableOptions = {} ) {
		jQuery( listId ).sortable( {
			items: listItems,
			placeholder: 'sortable-placeholder',
			tolerance: 'pointer',
			distance: 1,
			forcePlaceholderSize: true,
			opacity: 0.6,
			helper: 'clone',
			cursor: 'move',
			update: function ( event, ui ) {
				if ( updateCallback ) {
					let item_order = jQuery( this ).sortable( sortableOrder,{ attribute: 'data-order-id'} );
					updateCallback( item_order, event, ui );
				}
			},
			...sortableOptions
		} );
	}

	initTinyMCE( selector ) {
		if ( typeof tinymce !== 'undefined' ) {
			tinymce.remove( selector ); // Remove any existing TinyMCE instances
			// Initialize TinyMCE
			tinymce.init( {
				selector: selector,
				menubar: false,
				toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | code', // Define the toolbar buttons
			} );
		}
	}

};

export { SPLMSHelper };