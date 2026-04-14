import { createReduxStore, register } from '@wordpress/data';

// Create and register the lesson settings store
import { StoreKey as SettingsKey, StoreConfig as SettingsConfig } from "./settings";
const settingsStore = createReduxStore(SettingsKey, SettingsConfig);
register(settingsStore);

// Create and register the lesson tabs store
import { StoreKey as LessonTabKey, StoreConfig as LessonTabConfig } from "./tabs";
const lessonTabStore = createReduxStore( LessonTabKey, LessonTabConfig );
register(lessonTabStore); 