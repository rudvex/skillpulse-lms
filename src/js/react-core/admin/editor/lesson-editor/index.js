import "./store";
import { renderBlock } from '../../../utility/renderBlock';

import Header from "./main/Header";
import Settings from "./main/Settings";

// Main Render - Lesson Editor Header and Settings
renderBlock('splms-lesson-header-wrapper', <Header />);
renderBlock('splms-lesson-settings-page', <Settings />); 