import { __ } from '@wordpress/i18n';
import { debounce } from "lodash";
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import { Component, Fragment } from '@wordpress/element';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { Spinner } from '@wordpress/components';

class LessonList extends Component {
  constructor(props) {
    super(props);
    this.state = {
      isAddingLesson: false,
      search: "",
      isLoading: false,
      page: 1,
    };
    this.isSavingFlag = true;
  }
  componentDidMount() {
    this.props.refreshSidebarLessons(this.state.page);
  }

  refreshSidebarLessons = debounce((search) => {
    this.props.refreshSidebarLessons(1, search).then((resolve) => {
      this.setState({ isLoading: false });
    });
  }, 300);

  render() {
      const { searchedLessonsData } = this.props;
      console.log(this.props);
      return (
          <div className="skillpluse-lms-lesson-panel">
              <PluginDocumentSettingPanel
                  name="splms-lesson-panel"
                  title={ __( "Lessons", "skillpulse-lms" ) }
                  className="splms-lesson-panel"
                  initialOpen={ true }
              >
                  {searchedLessonsData && (
                      <div className="splms-search-lessons">
                          <input
                              type="text"
                              placeholder={ __( "Search Lessons", "skillpulse-lms" ) }
                              onChange={ ( e ) => this.handleSearch( e.target.value ) }
                          />
                          { this.state.isLoading && <Spinner/> }
                      </div>
                  ) }

              </PluginDocumentSettingPanel>
          </div>
      )
  }
}

export default compose( [
    withDispatch( ( dispatch, ownProps ) => {
        const { refreshSidebarLessons } = dispatch( 'splms/course-curriculum' );
        return {
            refreshSidebarLessons,
        };
    } ),
    withSelect( ( select ) => {
        const { getSearchedLessonsData } = select( 'splms/course-curriculum' );
        return {
            searchedLessonsData: getSearchedLessonsData(),
        };
    } ),
] )( LessonList );
