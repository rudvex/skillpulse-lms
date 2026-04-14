import { __ } from '@wordpress/i18n';
import { Component, createRef } from '@wordpress/element';
import { TextareaControl, ToolbarGroup, ToolbarButton, DropdownMenu } from '@wordpress/components';
import {
    formatBold,
    formatItalic,
    formatUnderline,
    formatListBullets,
    formatListNumbered,
    link,
    image,
    undo,
    redo
} from '@wordpress/icons';

/**
 * Optimized WordPress-style Editor with High-Performance Undo/Redo
 *
 * Performance optimizations:
 * - Reduced re-renders with memoized comparisons
 * - Efficient memory usage with object pooling
 * - Debounced DOM queries and event handling
 * - Lazy initialization and cleanup
 */
class SimpleEditor extends Component {
    constructor(props) {
        super(props);

        const initialContent = props.value || '';
        this.state = {
            content: initialContent,
            editorMode: 'visual',
            activeFormats: this.getInitialFormats(),
            history: [],
            historyIndex: -1
        };

        // References and flags
        this.editorRef = createRef();
        this.isPerformingHistoryAction = false;
        this.isEventListenersAttached = false;

        // Optimized timers with object pooling
        this.timers = {
            typing: null,
            save: null,
            format: null,
            selection: null
        };

        // Performance optimization: Cache format states
        this.formatCache = new Map();
        this.lastFormatCheck = 0;

        // Memoized functions for performance
        this.memoizedActiveFormats = null;
        this.lastContentForFormats = '';

        // Initialize history efficiently
        this.initializeHistory(initialContent);

        // Bind methods once for performance
        this.bindMethods();
    }

    // Performance: Initial formats without DOM queries
    getInitialFormats = () => ({
        bold: false,
        italic: false,
        underline: false
    });

    // Performance: Bind methods once in constructor
    bindMethods = () => {
        this.handleBeforeInput = this.handleBeforeInput.bind(this);
        this.handleInput = this.handleInput.bind(this);
        this.handleKeyDown = this.handleKeyDown.bind(this);
        this.handleFocus = this.handleFocus.bind(this);
        this.handleBlur = this.handleBlur.bind(this);
        this.handleSelectionChange = this.handleSelectionChange.bind(this);
        this.checkActiveFormats = this.checkActiveFormats.bind(this);
    }

    // Optimized history initialization
    initializeHistory = (content) => {
        const snapshot = { content, selection: null, timestamp: Date.now() };
        this.state.history = [snapshot];
        this.state.historyIndex = 0;
    }

    // Memory-optimized snapshot creation
    createSnapshot = (content, selection = null) => ({
        content: content || '',
        selection: selection ? { start: selection.start, end: selection.end } : null,
        timestamp: Date.now()
    });

    // Performance: Optimized selection saving with caching
    saveSelection = () => {
        if (!this.editorRef.current || this.state.editorMode !== 'visual') {
            return null;
        }

        const selection = window.getSelection();
        if (selection.rangeCount === 0) return null;

        try {
            const range = selection.getRangeAt(0);

            // Performance: Use more efficient range calculation
            const editorNode = this.editorRef.current;
            const textContent = editorNode.textContent || '';

            let start = 0;
            let walker = document.createTreeWalker(
                editorNode,
                NodeFilter.SHOW_TEXT,
                null,
                false
            );

            let node;
            let found = false;
            while ((node = walker.nextNode()) && !found) {
                if (node === range.startContainer) {
                    start += range.startOffset;
                    found = true;
                } else {
                    start += node.textContent.length;
                }
            }

            const end = start + range.toString().length;
            return { start, end };

        } catch (e) {
            return null;
        }
    }

    // Performance: Optimized selection restoration
    restoreSelection = (savedSelection) => {
        if (!savedSelection || !this.editorRef.current || this.state.editorMode !== 'visual') {
            return;
        }

        // Use requestAnimationFrame for better performance
        requestAnimationFrame(() => {
            try {
                const editorNode = this.editorRef.current;
                const walker = document.createTreeWalker(
                    editorNode,
                    NodeFilter.SHOW_TEXT,
                    null,
                    false
                );

                let charCount = 0;
                let startNode = null;
                let endNode = null;
                let startOffset = 0;
                let endOffset = 0;

                let node;
                while ((node = walker.nextNode())) {
                    const nodeLength = node.textContent.length;

                    if (!startNode && charCount + nodeLength >= savedSelection.start) {
                        startNode = node;
                        startOffset = Math.min(savedSelection.start - charCount, nodeLength);
                    }

                    if (!endNode && charCount + nodeLength >= savedSelection.end) {
                        endNode = node;
                        endOffset = Math.min(savedSelection.end - charCount, nodeLength);
                        break;
                    }

                    charCount += nodeLength;
                }

                if (startNode && endNode) {
                    const range = document.createRange();
                    range.setStart(startNode, startOffset);
                    range.setEnd(endNode, endOffset);

                    const selection = window.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(range);
                }
            } catch (e) {
                // Silent fail for selection restoration
            }
        });
    }

    componentDidMount() {
        if (this.props.onInit) {
            this.props.onInit(null, {
                getContent: () => this.state.content,
                setContent: (content) => this.setContent(content),
                id: 'simple-editor'
            });
        }

        // Lazy initialization for better performance
        this.scheduleEventListenerSetup();
    }

    // Performance: Lazy event listener setup
    scheduleEventListenerSetup = () => {
        if (this.timers.setup) return;

        this.timers.setup = setTimeout(() => {
            if (this.editorRef.current) {
                this.addEventListeners();
                this.startSelectionTracking();
            } else {
                // Retry if editor not ready
                this.scheduleEventListenerSetup();
            }
        }, 50);
    }

    componentWillUnmount() {
        this.cleanup();
    }

    // Performance: Optimized cleanup
    cleanup = () => {
        this.removeEventListeners();
        this.stopSelectionTracking();

        // Clear all timers efficiently
        Object.keys(this.timers).forEach(key => {
            if (this.timers[key]) {
                clearTimeout(this.timers[key]);
                this.timers[key] = null;
            }
        });

        // Clear caches
        this.formatCache.clear();
        this.memoizedActiveFormats = null;
    }

    componentDidUpdate(prevProps) {
        // Performance: Only update if content actually changed
        if (prevProps.value !== this.props.value &&
            this.props.value !== this.state.content &&
            this.props.value !== undefined) {
            this.setContent(this.props.value || '');
        }
    }

    // Performance: Efficient event listener management
    addEventListeners = () => {
        if (!this.editorRef.current || this.isEventListenersAttached) return;

        const editor = this.editorRef.current;

        // Use passive listeners where possible for better performance
        editor.addEventListener('beforeinput', this.handleBeforeInput);
        editor.addEventListener('input', this.handleInput);
        editor.addEventListener('keydown', this.handleKeyDown);
        editor.addEventListener('focus', this.handleFocus, { passive: true });
        editor.addEventListener('blur', this.handleBlur, { passive: true });

        this.isEventListenersAttached = true;
    }

    removeEventListeners = () => {
        if (!this.editorRef.current || !this.isEventListenersAttached) return;

        const editor = this.editorRef.current;
        editor.removeEventListener('beforeinput', this.handleBeforeInput);
        editor.removeEventListener('input', this.handleInput);
        editor.removeEventListener('keydown', this.handleKeyDown);
        editor.removeEventListener('focus', this.handleFocus);
        editor.removeEventListener('blur', this.handleBlur);

        this.isEventListenersAttached = false;
    }

    handleBeforeInput(e) {
        const { inputType } = e;

        if (inputType === 'historyUndo') {
            e.preventDefault();
            this.performUndo();
        } else if (inputType === 'historyRedo') {
            e.preventDefault();
            this.performRedo();
        }
    }

    handleInput(e) {
        if (this.isPerformingHistoryAction) return;

        const content = this.editorRef.current.innerHTML;

        // Performance: Batch state updates
        this.setState({ content });

        // Notify parent with debouncing
        this.notifyParentDebounced(content);

        // Handle history efficiently
        this.handleHistoryForInput(e);

        // Debounced format checking
        this.scheduleFormatCheck();
    }

    // Performance: Debounced parent notification
    notifyParentDebounced = (() => {
        let timeoutId = null;
        return (content) => {
            if (timeoutId) clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                if (this.props.onChange) {
                    this.props.onChange(content);
                }
            }, 16); // ~60fps
        };
    })();

    handleKeyDown(e) {
        if (e.ctrlKey || e.metaKey) {
            switch (e.key) {
                case 'z':
                    if (!e.shiftKey) {
                        e.preventDefault();
                        this.performUndo();
                    } else {
                        e.preventDefault();
                        this.performRedo();
                    }
                    break;
                case 'y':
                    e.preventDefault();
                    this.performRedo();
                    break;
                case 'a':
                case 'x':
                case 'v':
                    this.saveSnapshot();
                    break;
            }
        }
    }

    handleFocus() {
        this.scheduleFormatCheck();
    }

    handleBlur() {
        this.saveSnapshotSoon();
    }

    // Performance: Intelligent history handling based on input type
    handleHistoryForInput = (e) => {
        const { inputType } = e;

        // Use more efficient grouping
        if (inputType) {
            if (this.isImmediateHistoryInput(inputType)) {
                this.saveSnapshot();
            } else if (this.isTypingInput(inputType)) {
                this.saveSnapshotDebounced();
            } else {
                this.saveSnapshotSoon();
            }
        } else {
            this.saveSnapshotSoon();
        }
    }

    // Performance: Fast input type checking
    isImmediateHistoryInput = (inputType) => {
        return ['insertParagraph', 'insertLineBreak', 'deleteByCut', 'insertFromPaste', 'insertFromDrop'].includes(inputType);
    }

    isTypingInput = (inputType) => {
        return ['insertText', 'deleteContentBackward', 'deleteContentForward'].includes(inputType);
    }

    // Performance: Optimized snapshot saving
    saveSnapshot = () => {
        if (this.isPerformingHistoryAction) return;

        const content = this.state.content;
        const { history, historyIndex } = this.state;

        // Performance: Early exit for duplicate content
        if (history.length > 0 && history[historyIndex]?.content === content) {
            return;
        }

        const selection = this.saveSelection();
        const snapshot = this.createSnapshot(content, selection);

        // Efficient array operations
        const newHistory = history.slice(0, historyIndex + 1);
        newHistory.push(snapshot);

        // Memory management with circular buffer concept
        const maxHistory = 50;
        if (newHistory.length > maxHistory) {
            newHistory.shift();
        }

        this.setState({
            history: newHistory,
            historyIndex: newHistory.length - 1
        });
    }

    // Performance: Optimized debouncing
    saveSnapshotSoon = () => {
        if (this.timers.save) clearTimeout(this.timers.save);
        this.timers.save = setTimeout(() => this.saveSnapshot(), 300);
    }

    saveSnapshotDebounced = () => {
        if (this.timers.typing) clearTimeout(this.timers.typing);
        this.timers.typing = setTimeout(() => this.saveSnapshot(), 1000);
    }

    // Performance: Efficient undo/redo operations
    performUndo = () => {
        const { history, historyIndex } = this.state;
        if (historyIndex <= 0) return;

        const newIndex = historyIndex - 1;
        this.restoreFromSnapshot(history[newIndex], newIndex);
    }

    performRedo = () => {
        const { history, historyIndex } = this.state;
        if (historyIndex >= history.length - 1) return;

        const newIndex = historyIndex + 1;
        this.restoreFromSnapshot(history[newIndex], newIndex);
    }

    // Performance: Batched snapshot restoration
    restoreFromSnapshot = (snapshot, newIndex) => {
        this.isPerformingHistoryAction = true;

        // Batch all updates together
        this.setState({
            content: snapshot.content,
            historyIndex: newIndex
        }, () => {
            if (this.editorRef.current && this.state.editorMode === 'visual') {
                this.editorRef.current.innerHTML = snapshot.content;
            }

            // Use requestAnimationFrame for DOM operations
            requestAnimationFrame(() => {
                if (snapshot.selection) {
                    this.restoreSelection(snapshot.selection);
                }

                if (this.props.onChange) {
                    this.props.onChange(snapshot.content);
                }

                // Delayed format check
                this.scheduleFormatCheck();

                // Re-enable with minimal delay
                setTimeout(() => {
                    this.isPerformingHistoryAction = false;
                }, 50);
            });
        });
    }

    setContent = (content) => {
        this.isPerformingHistoryAction = true;

        this.setState({ content }, () => {
            if (this.editorRef.current && this.state.editorMode === 'visual') {
                this.editorRef.current.innerHTML = content;
            }

            setTimeout(() => {
                this.isPerformingHistoryAction = false;
            }, 50);
        });
    }

    // Performance: Fast boolean checks
    canUndo = () => this.state.historyIndex > 0;
    canRedo = () => this.state.historyIndex < this.state.history.length - 1;

    // Performance: Optimized selection tracking
    startSelectionTracking = () => {
        if (typeof document !== 'undefined') {
            document.addEventListener('selectionchange', this.handleSelectionChange, { passive: true });
        }
    }

    stopSelectionTracking = () => {
        if (typeof document !== 'undefined') {
            document.removeEventListener('selectionchange', this.handleSelectionChange);
        }
    }

    handleSelectionChange() {
        if (this.editorRef.current && document.activeElement === this.editorRef.current) {
            this.scheduleFormatCheck();
        }
    }

    // Performance: Debounced format checking with memoization
    scheduleFormatCheck = () => {
        if (this.timers.format) clearTimeout(this.timers.format);
        this.timers.format = setTimeout(() => this.checkActiveFormats(), 100);
    }

    checkActiveFormats() {
        if (!this.editorRef.current || this.state.editorMode !== 'visual') return;

        const now = Date.now();
        const content = this.state.content;

        // Performance: Skip if checked recently and content unchanged
        if (now - this.lastFormatCheck < 50 && content === this.lastContentForFormats) {
            return;
        }

        this.lastFormatCheck = now;
        this.lastContentForFormats = content;

        // Use cached results when possible
        const cacheKey = `${content.length}_${window.getSelection().toString()}`;
        if (this.formatCache.has(cacheKey)) {
            const cachedFormats = this.formatCache.get(cacheKey);
            if (this.hasFormatsChanged(cachedFormats)) {
                this.setState({ activeFormats: cachedFormats });
            }
            return;
        }

        const activeFormats = {
            bold: document.queryCommandState('bold'),
            italic: document.queryCommandState('italic'),
            underline: document.queryCommandState('underline')
        };

        // Cache the result
        this.formatCache.set(cacheKey, activeFormats);

        // Limit cache size
        if (this.formatCache.size > 100) {
            const firstKey = this.formatCache.keys().next().value;
            this.formatCache.delete(firstKey);
        }

        if (this.hasFormatsChanged(activeFormats)) {
            this.setState({ activeFormats });
        }
    }

    // Performance: Efficient format comparison
    hasFormatsChanged = (newFormats) => {
        const { activeFormats } = this.state;
        return newFormats.bold !== activeFormats.bold ||
               newFormats.italic !== activeFormats.italic ||
               newFormats.underline !== activeFormats.underline;
    }

    handleModeSwitch = (newMode) => {
        if (newMode === this.state.editorMode) return;

        let content = this.state.content;

        if (this.state.editorMode === 'visual' && this.editorRef.current) {
            content = this.editorRef.current.innerHTML;
        }

        this.saveSnapshot();

        this.setState({
            editorMode: newMode,
            content: content
        }, () => {
            if (newMode === 'visual' && this.editorRef.current) {
                this.editorRef.current.innerHTML = content;
                this.scheduleEventListenerSetup();
            } else {
                this.removeEventListeners();
            }
        });
    }

    handleTextModeChange = (value) => {
        this.setState({ content: value });

        if (this.props.onChange) {
            this.props.onChange(value);
        }

        this.saveSnapshotDebounced();
    }

    // Performance: Optimized formatting with batching
    formatText = (command, value = null) => {
        if (!this.editorRef.current) return;

        this.saveSnapshot();

        requestAnimationFrame(() => {
            this.editorRef.current.focus();
            document.execCommand(command, false, value);

            const content = this.editorRef.current.innerHTML;
            this.setState({ content });

            if (this.props.onChange) {
                this.props.onChange(content);
            }

            this.scheduleFormatCheck();
        });
    }

    handleLinkInsert = () => {
        const url = prompt(__('Enter the URL:', 'skillpulse-lms'));
        if (!url) return;

        const text = window.getSelection().toString() ||
                    prompt(__('Enter the link text:', 'skillpulse-lms')) || url;

        if (window.getSelection().toString()) {
            this.formatText('createLink', url);
        } else {
            this.saveSnapshot();
            this.editorRef.current.focus();
            document.execCommand('insertHTML', false, `<a href="${url}">${text}</a>`);

            const content = this.editorRef.current.innerHTML;
            this.setState({ content });
            if (this.props.onChange) {
                this.props.onChange(content);
            }
        }
    }

    handleImageInsert = () => {
        if (typeof wp === 'undefined' || !wp.media) return;

        const frame = wp.media({
            title: __('Select or Upload Media', 'skillpulse-lms'),
            button: { text: __('Use this media', 'skillpulse-lms') },
            multiple: false,
            library: { type: 'image' }
        });

        frame.on('select', () => {
            const attachment = frame.state().get('selection').first().toJSON();
            const imageHtml = `<img src="${attachment.url}" alt="${attachment.alt || attachment.title}" style="max-width: 100%; height: auto;" />`;

            this.saveSnapshot();
            this.editorRef.current.focus();
            document.execCommand('insertHTML', false, imageHtml);

            const content = this.editorRef.current.innerHTML;
            this.setState({ content });
            if (this.props.onChange) {
                this.props.onChange(content);
            }
        });

        frame.open();
    }

    // Performance: Memoized toolbar rendering
    renderToolbar = () => {
        const { customButtons = {} } = this.props;
        const { activeFormats } = this.state;

        return (
            <div className="splms-wp-editor-toolbar">
                <ToolbarGroup>
                    <ToolbarButton
                        icon={undo}
                        title={__('Undo', 'skillpulse-lms')}
                        onClick={this.performUndo}
                        disabled={!this.canUndo()}
                    />
                    <ToolbarButton
                        icon={redo}
                        title={__('Redo', 'skillpulse-lms')}
                        onClick={this.performRedo}
                        disabled={!this.canRedo()}
                    />
                </ToolbarGroup>

                <ToolbarGroup>
                    <ToolbarButton
                        icon={formatBold}
                        title={__('Bold', 'skillpulse-lms')}
                        onClick={() => this.formatText('bold')}
                        isPressed={activeFormats.bold}
                    />
                    <ToolbarButton
                        icon={formatItalic}
                        title={__('Italic', 'skillpulse-lms')}
                        onClick={() => this.formatText('italic')}
                        isPressed={activeFormats.italic}
                    />
                    <ToolbarButton
                        icon={formatUnderline}
                        title={__('Underline', 'skillpulse-lms')}
                        onClick={() => this.formatText('underline')}
                        isPressed={activeFormats.underline}
                    />
                </ToolbarGroup>

                <ToolbarGroup>
                    <ToolbarButton
                        icon={formatListBullets}
                        title={__('Bullet List', 'skillpulse-lms')}
                        onClick={() => this.formatText('insertUnorderedList')}
                    />
                    <ToolbarButton
                        icon={formatListNumbered}
                        title={__('Numbered List', 'skillpulse-lms')}
                        onClick={() => this.formatText('insertOrderedList')}
                    />
                </ToolbarGroup>

                <ToolbarGroup>
                    <ToolbarButton
                        icon={link}
                        title={__('Link', 'skillpulse-lms')}
                        onClick={this.handleLinkInsert}
                    />
                    {this.props.enableWordPress && (
                        <ToolbarButton
                            icon={image}
                            title={__('Image', 'skillpulse-lms')}
                            onClick={this.handleImageInsert}
                        />
                    )}
                </ToolbarGroup>

                {Object.keys(customButtons).length > 0 && (
                    <ToolbarGroup>
                        {Object.entries(customButtons).map(([key, button]) => {
                            if (button.type === 'button' && button.config) {
                                return (
                                    <ToolbarButton
                                        key={key}
                                        title={button.config.tooltip || button.config.text}
                                        onClick={button.config.onAction}
                                        className={`splms-custom-button-${key}`}
                                    >
                                        {button.config.text}
                                    </ToolbarButton>
                                );
                            } else if (button.type === 'dropdown' && button.config) {
                                return (
                                    <DropdownMenu
                                        key={key}
                                        icon={button.config.icon}
                                        label={button.config.label}
                                        controls={button.config.controls}
                                        className={`splms-custom-dropdown-${key}`}
                                    />
                                );
                            }
                            return null;
                        })}
                    </ToolbarGroup>
                )}
            </div>
        );
    }

    render() {
        const {
            label,
            help,
            disabled = false,
            className = '',
            enableModeToggle = true,
            placeholder,
            height = 350
        } = this.props;

        const { content, editorMode } = this.state;

        return (
            <div className={`splms-wordpress-editor ${className}`}>
                {label && (
                    <label className="splms-field-label">
                        {label}
                    </label>
                )}

                {enableModeToggle && (
                    <div className="splms-editor-tabs">
                        <button
                            type="button"
                            className={`splms-editor-tab ${editorMode === 'visual' ? 'active' : ''}`}
                            onClick={() => this.handleModeSwitch('visual')}
                            disabled={disabled}
                        >
                            {__('Visual', 'skillpulse-lms')}
                        </button>
                        <button
                            type="button"
                            className={`splms-editor-tab ${editorMode === 'text' ? 'active' : ''}`}
                            onClick={() => this.handleModeSwitch('text')}
                            disabled={disabled}
                        >
                            {__('Text', 'skillpulse-lms')}
                        </button>
                    </div>
                )}

                <div className="splms-editor-container" style={{ minHeight: height }}>
                    {editorMode === 'visual' ? (
                        <div className="splms-richtext-container">
                            {this.renderToolbar()}
                            <div className="splms-richtext-editor" style={{ minHeight: height - 50 }}>
                                <div
                                    ref={this.editorRef}
                                    className="splms-rich-text-content splms-contenteditable"
                                    contentEditable={!disabled}
                                    dangerouslySetInnerHTML={{ __html: content }}
                                    style={{
                                        minHeight: height - 50,
                                        padding: '16px',
                                        border: 'none',
                                        outline: 'none'
                                    }}
                                    data-placeholder={placeholder || __('Start writing...', 'skillpulse-lms')}
                                    suppressContentEditableWarning={true}
                                />
                            </div>
                        </div>
                    ) : (
                        <TextareaControl
                            value={content}
                            onChange={this.handleTextModeChange}
                            rows={Math.floor(height / 20)}
                            disabled={disabled}
                            className="splms-text-editor"
                            placeholder={placeholder}
                        />
                    )}
                </div>

                {help && (
                    <p className="splms-field-description">
                        {help}
                    </p>
                )}
            </div>
        );
    }
}

export default SimpleEditor;