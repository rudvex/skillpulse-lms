import { registerPlugin } from '@wordpress/plugins';
import LessonList from "./components/LessonList";

// registerPlugin( "splms-lesson-panel", {
//     render() {
//         const postType = wp.data.select( "core/editor" ).getCurrentPostType();
//         console.log("postType",postType);
//         console.log("postType", SPLMSCore_Data.all_sp_post_types.lesson !== postType );
//         if ( SPLMSCore_Data.all_sp_post_types.lesson !== postType ) {
//            return <LessonList />;
//         }
//
//         return null;
//     },
//     icon: "",
// } );