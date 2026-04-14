import "./store";
import { renderBlock } from '../../../utility/renderBlock';

import Header from "./main/Header";
import Settings from "./main/Settings";

// Main Render - Section Editor Header and Settings.
renderBlock('splms-section-header-wrapper', <Header />);
renderBlock('splms-section-settings-page', <Settings />);
