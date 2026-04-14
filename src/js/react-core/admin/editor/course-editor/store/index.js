import { createReduxStore, register } from '@wordpress/data';

import { StoreKey as CurriculumKey, StoreConfig as CurriculumConfig } from "./curriculum";
const curriculumStore = createReduxStore(CurriculumKey, CurriculumConfig);
register(curriculumStore);

import { StoreKey as SettingsKey, StoreConfig as SettingsConfig } from "./settings";
const settingsStore = createReduxStore(SettingsKey, SettingsConfig);
register(settingsStore);

// Create and register the store
import { StoreKey as TabKey, StoreConfig as TabConfig } from "./tabs";
const tabStore = createReduxStore( TabKey, TabConfig );
register(tabStore);
