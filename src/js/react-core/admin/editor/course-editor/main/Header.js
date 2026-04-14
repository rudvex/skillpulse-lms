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
            activeTab: "course",
            activePanels: [],
            activeMetaBoxes: [],
            width: window.innerWidth,
        };
        this.courseEditor = SPLMSCore.course_editor;
        this.className = "#splms-course-header";
    }

    componentDidMount() {
        // Check if we have hash params & redirect
        if (window.location.hash) {
            const tab = window.location.hash.substring(1); //Puts hash in variable, and removes the # character
            this.setState({ activeTab: tab });
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
        } = this.props;
        if(isDistractionFreeMode){
            console.log('You cannot enable "Distraction Free Mode" in courses');
            toggleFeature('distractionFree')
        }

        const isTabletOrSmaller = this.getCanvasWidth("Tablet");

        SPLMSCore.course_editor.setHeaderHeight();
        SPLMSCore.course_editor.maybeHideHeader(isEditorSidebarOpened, isTabletOrSmaller);
        SPLMSCore.course_editor.maybeHideBlockInserter(tabIndex);

        // Replace WP logo with the brand logo.
        if (isFullscreenMode) {
            setTimeout(() => {
                SPLMSCore.course_editor.updateFullscreenLogoLink();
                SPLMSCore.course_editor.setHeaderHeight();
            }, 100);

            setTimeout(() => {
                SPLMSCore.course_editor.updateFullscreenLogoLink();
            }, 5000);
        }else{
            setTimeout(() => {
                SPLMSCore.course_editor.setHeaderHeight();
            }, 100);
        }

        // Bring publishable sidebar above header if publishable sidebar is opened
        if (isPublishSidebarOpened ) {
            SPLMSCore.course_editor.addCSS(this.className, { "z-index": "0" });
        } else {
            SPLMSCore.course_editor.addCSS(this.className, { "z-index": "999" });
        }

        // Show the right view, always show Lessons panel if tab is not course
        if (
            null !== activeGeneralSidebarName &&
            "edit-post/document" !== activeGeneralSidebarName &&
            "course" !== this.state.activeTab
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
        if (tabIndex === "curriculum") {
            SPLMSCore.course_editor.showCurriculumView();
        } else if (tabIndex === "settings") {
            SPLMSCore.course_editor.showSettingsView();
        }  else {
            SPLMSCore.course_editor.showDefaultView();
        }
    }

    // Toggle currently active tab
    handleTabClick = (tabIndex) => {
        if (tabIndex === this.state.activeTab) return;

        this.setState({
            activeTab: tabIndex,
        });

        this.props.setActiveTab(tabIndex);

        this.resetEditor(tabIndex);
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

    tabClassName(tabName) {
        return this.state.activeTab === tabName ? "active" : "";
    }

    // Returns edited post title or saved post title
    getPostTitle = () => {
        return this.props.postTitle;
    };

    render() {
        const { post } = this.props;

        return (
            <Fragment>
                <header id="splms-course-header" role="banner">
                    <div className="splms-header-logo">
                        <div className="logo inline">
                            <BrandLogo/>
                            <div className="splms-header-separator" />
                            <h1 className="splms-header-title inline">
                                {this.getPostTitle()}
                            </h1>
                        </div>
                        <div className="splms-header-icon">
                            <div className="splms-header-icon-help">
                                {SPLMSCore_Data.back_cta_label && SPLMSCore_Data.coursesUrl && (
                                    <Button
                                        className="splms-header-icon-help-button"
                                        onClick={() => {
                                            window.open(SPLMSCore_Data.coursesUrl, "_self");
                                        }}
                                        aria-label={__("Back to Courses", "skillpulse-lms")}
                                    >
                                        <SplmsIcon name="arrowBack" size={20} />
                                        <span className="splms-header-icon-help-text">
                                        {SPLMSCore_Data.all_sp_post_types.course === post.type && __("Back to Courses", "skillpulse-lms")}
                                            {(SPLMSCore_Data.all_sp_post_types.lesson === post.type || SPLMSCore_Data.all_sp_post_types.quiz === post.type) && SPLMSCore_Data.back_cta_label}
                                    </span>
                                    </Button>
                                )}
                            </div>
                        </div>
                    </div>
                    
                    <nav className="splms-header-nav" role="navigation" aria-label={__("Course editor navigation", "skillpulse-lms")}>
                        <ul role="tablist">
                            <li 
                                className={this.tabClassName("course")} 
                                data-index="course"
                                role="presentation"
                            >
                                <a 
                                    onClick={(e) => this.handleTabClick("course")} 
                                    href="#course"
                                    role="tab"
                                    aria-selected={this.state.activeTab === "course"}
                                    aria-controls="panel-course"
                                    tabIndex={this.state.activeTab === "course" ? 0 : -1}
                                >
                                    {__("Course Page", "skillpulse-lms")}
                                </a>
                            </li>
                            <li
                                className={this.tabClassName("curriculum")}
                                data-index="curriculum"
                                role="presentation"
                            >
                                <a
                                    onClick={(e) => this.handleTabClick("curriculum")}
                                    href="#curriculum"
                                    role="tab"
                                    aria-selected={this.state.activeTab === "curriculum"}
                                    aria-controls="panel-curriculum"
                                    tabIndex={this.state.activeTab === "curriculum" ? 0 : -1}
                                >
                                    {__("Curriculum", "skillpulse-lms")}
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

        const { setActiveTab } = dispatch('splms/course-tabs');

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
