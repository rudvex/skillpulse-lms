import { SplmsIcon } from "../../../../components/SplmsIcon";
import BrandLogo from "../../../../components/BrandLogo";

import { debounce } from "lodash";

import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import { Component, Fragment } from '@wordpress/element';
import { Icon, Button } from '@wordpress/components';

class Header extends Component {
    constructor(props) {
        super(props);
        this.state = {
            activeTab: "quiz",
            activePanels: [],
            activeMetaBoxes: [],
            width: window.innerWidth,
        };
        this.quizEditor = SPLMSCore.quiz_editor || {};
        this.className = "#splms-quiz-header";
    }

    componentDidMount() {
        const { setActiveTab } = this.props;

        // Check if we have hash params & redirect
        if (window.location.hash) {
            const tab = window.location.hash.substring(1); //Puts hash in variable, and removes the # character
            this.setState({ activeTab: tab });
        }
        
        if (setActiveTab) {
            setActiveTab(this.state.activeTab);
        }
        
        this.resetEditor(this.state.activeTab);
        window.addEventListener("resize", this.handleResize);
    }

    componentDidUpdate(prevProps) {
        this.resetEditor(this.state.activeTab, prevProps);
    }

    componentWillUnmount() {
        window.removeEventListener("resize", this.handleResize);
    }

    handleResize = debounce(() => {
        this.setState({ width: window.innerWidth });
    }, 1000);

    resetEditor(tabIndex = null, prev = {}) {
        const {
            openGeneralSidebar,
            isEditorSidebarOpened,
            isPublishSidebarOpened,
            isFullscreenMode,
            isDistractionFreeMode,
            toggleFeature,
            activeGeneralSidebarName,
            setActiveTab
        } = this.props;

        if (tabIndex !== null && setActiveTab) {
            setActiveTab(tabIndex);
        }

        if(isDistractionFreeMode){
            toggleFeature('distractionFree')
        }

        const isTabletOrSmaller = this.getCanvasWidth("Tablet");

        // Set header height and handle visibility
        if (this.quizEditor.setHeaderHeight) {
            this.quizEditor.setHeaderHeight();
        }

        if (this.quizEditor.maybeHideHeader) {
            this.quizEditor.maybeHideHeader(isEditorSidebarOpened, isTabletOrSmaller);
        }

        if (this.quizEditor.maybeHideBlockInserter) {
            this.quizEditor.maybeHideBlockInserter(tabIndex);
        }

        // Replace WP logo with the brand logo and handle header height in fullscreen mode
        if (isFullscreenMode) {
            setTimeout(() => {
                if (this.quizEditor.setHeaderHeight) {
                    this.quizEditor.setHeaderHeight();
                }
            }, 100);

            setTimeout(() => {
                if (this.quizEditor.setHeaderHeight) {
                    this.quizEditor.setHeaderHeight();
                }
            }, 5000);
        } else {
            setTimeout(() => {
                if (this.quizEditor.setHeaderHeight) {
                    this.quizEditor.setHeaderHeight();
                }
            }, 100);
        }

        // Bring publishable sidebar above header if publishable sidebar is opened
        if (isPublishSidebarOpened && this.quizEditor.addCSS) {
            this.quizEditor.addCSS(this.className, { "z-index": "0" });
        } else if (this.quizEditor.addCSS) {
            this.quizEditor.addCSS(this.className, { "z-index": "999" });
        }

        // Show the right view
        if (
            null !== activeGeneralSidebarName &&
            "edit-post/document" !== activeGeneralSidebarName &&
            "quiz" !== this.state.activeTab
        ) {
            tabIndex = this.state.activeTab;
            openGeneralSidebar("edit-post/document").then((resolve) => {
                this.showView(tabIndex);
            });
        } else {
            this.showView(tabIndex);
        }
    }

    // Show appropriate view
    showView(tabIndex) {
        if (tabIndex === "questions") {
            this.quizEditor.showQuestionBuilderView && this.quizEditor.showQuestionBuilderView();
        } else if (tabIndex === "settings") {
            this.quizEditor.showSettingsView && this.quizEditor.showSettingsView();
        } else {
            this.quizEditor.showDefaultView && this.quizEditor.showDefaultView();
        }
    }

    tabClassName(tabName) {
        return this.state.activeTab === tabName ? "active" : "";
    }

    // Toggle currently active tab
    handleTabClick = (tabIndex) => {
        if (tabIndex === this.state.activeTab) return;

        this.setState({
            activeTab: tabIndex,
        });

        this.resetEditor(tabIndex);
    };

    // Returns edited post title or saved post title
    getPostTitle = () => {
        return this.props.postTitle || __("Quiz", "skillpulse-lms");
    };

    // Determine screen width
    getCanvasWidth = (device, actualWidth = this.state.width) => {
        let deviceWidth;

        switch (device) {
            case "Tablet":
                deviceWidth = 780;
                break;
            case "Mobile":
                deviceWidth = 360;
                break;
            default:
                return null;
        }

        return deviceWidth >= actualWidth;
    };

    render() {
        const { post, isFullscreenMode, isDistractionFreeMode } = this.props;
        
        if (!post) {
            return null;
        }

        // Only show for quiz post type
        if (post.type !== 'sp-quiz') {
            return null;
        }

        return (
            <Fragment>
                <header id="splms-quiz-header" role="banner">
                    <div className="splms-header-logo">
                        <div className="logo inline">
                            <BrandLogo />
                            <div className="splms-header-separator" />
                            <h1 className="splms-header-title inline">
                                {this.getPostTitle()}
                            </h1>
                        </div>
                        <div className="splms-header-icon">
                            <div className="splms-header-icon-help">
                                <Button
                                    className="splms-header-icon-help-button"
                                    onClick={() => {
                                        window.open("https://skillpulselms.com/docs/quizzes", "_blank", "noopener,noreferrer");
                                    }}
                                    aria-label={__("Get help with SkillPulse LMS Quizzes", "skillpulse-lms")}
                                >
                                    <SplmsIcon name="help" size={20} />
                                    <span className="splms-header-icon-help-text">
                                        {__("Quiz Help", "skillpulse-lms")}
                                    </span>
                                </Button>
                            </div>
                        </div>
                    </div>
                    
                    <nav className="splms-header-nav" role="navigation" aria-label={__("Quiz editor navigation", "skillpulse-lms")}>
                        <ul role="tablist">
                            <li 
                                className={this.tabClassName("quiz")} 
                                data-index="quiz"
                                role="presentation"
                            >
                                <a 
                                    onClick={(e) => this.handleTabClick("quiz")} 
                                    href="#quiz"
                                    role="tab"
                                    aria-selected={this.state.activeTab === "quiz"}
                                    aria-controls="panel-quiz"
                                    tabIndex={this.state.activeTab === "quiz" ? 0 : -1}
                                >
                                    {__("Quiz Page", "skillpulse-lms")}
                                </a>
                            </li>
                            <li
                                className={this.tabClassName("questions")}
                                data-index="questions"
                                role="presentation"
                            >
                                <a 
                                    onClick={(e) => this.handleTabClick("questions")} 
                                    href="#questions"
                                    role="tab"
                                    aria-selected={this.state.activeTab === "questions"}
                                    aria-controls="panel-questions"
                                    tabIndex={this.state.activeTab === "questions" ? 0 : -1}
                                >
                                    {__("Question Builder", "skillpulse-lms")}
                                </a>
                            </li>
                            <li
                                className={this.tabClassName("settings")}
                                data-index="settings"
                                role="presentation"
                            >
                                <a 
                                    onClick={(e) => this.handleTabClick("settings")} 
                                    href="#settings"
                                    role="tab"
                                    aria-selected={this.state.activeTab === "settings"}
                                    aria-controls="panel-settings"
                                    tabIndex={this.state.activeTab === "settings" ? 0 : -1}
                                >
                                    {__("Settings", "skillpulse-lms")}
                                </a>
                            </li>
                        </ul>
                    </nav>
                </header>
            </Fragment>
        );
    }
}

export default compose([
    withDispatch((dispatch, props) => {
        const { openGeneralSidebar } = dispatch("core/edit-post");
        const { toggleFeature } = dispatch("core/edit-post");

        const { setActiveTab } = dispatch('splms/quiz-tabs');

        return {
            openGeneralSidebar,
            toggleFeature,
            setActiveTab
        };
    }),
    withSelect((select, props) => {
        return {
            post: select("core/editor").getCurrentPost(),
            isFullscreenMode: select("core/edit-post").isFeatureActive(
                "fullscreenMode"
            ),
            isDistractionFreeMode: select("core/edit-post").isFeatureActive(
                "distractionFree"
            ),
            isPublishSidebarOpened: select("core/edit-post").isPublishSidebarOpened(),
            activeGeneralSidebarName: select(
                "core/edit-post"
            ).getActiveGeneralSidebarName(),
            isEditorSidebarOpened: select("core/edit-post").isEditorSidebarOpened(),
            postTitle: select('core/editor').getEditedPostAttribute('title'),
        };
    }),
])(Header); 