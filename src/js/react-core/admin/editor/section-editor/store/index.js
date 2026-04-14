import { createReduxStore, register } from '@wordpress/data';

// Create and register the section settings store.
import { StoreKey as SettingsKey, StoreConfig as SettingsConfig } from "./settings";
const settingsStore = createReduxStore(SettingsKey, SettingsConfig);
register(settingsStore);

// Create and register the section tabs store.
import { StoreKey as SectionTabKey, StoreConfig as SectionTabConfig } from "./tabs";
const sectionTabStore = createReduxStore( SectionTabKey, SectionTabConfig );
register(sectionTabStore);
