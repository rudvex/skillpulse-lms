import { createReduxStore, register } from '@wordpress/data';

// Create and register the quiz settings store
import { StoreKey as SettingsKey, StoreConfig as SettingsConfig } from "./settings";
const settingsStore = createReduxStore(SettingsKey, SettingsConfig);
register(settingsStore);

// Create and register the quiz tabs store
import { StoreKey as QuizTabKey, StoreConfig as QuizTabConfig } from "./tabs";
const quizTabStore = createReduxStore( QuizTabKey, QuizTabConfig );
register(quizTabStore); 