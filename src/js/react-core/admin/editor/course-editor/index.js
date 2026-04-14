import "./store";
import './setting-panel/registerPanel';
import { renderBlock } from '../../../utility/renderBlock';

import Header from "./main/Header";
import Curriculum from "./main/Curriculum";
import Settings from "./main/Settings";

// Main Render - Use original Header with modern styling
renderBlock('splms-course-header-wrapper', <Header />);
renderBlock('splms-course-curriculum-page', <Curriculum />);
renderBlock('splms-course-settings-page', <Settings />);