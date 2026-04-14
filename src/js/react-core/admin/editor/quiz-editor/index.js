import '../styles/question.scss';
import "./store";
import { renderBlock } from '../../../utility/renderBlock';

import Header from "./main/Header";
import QuestionBuilder from "./main/QuestionBuilder";
import Settings from "./main/Settings";

// Main Render - Quiz Editor Header, Questions, and Settings
renderBlock('splms-quiz-header-wrapper', <Header />);
renderBlock('splms-quiz-questions-page', <QuestionBuilder />);
renderBlock('splms-quiz-settings-page', <Settings />); 