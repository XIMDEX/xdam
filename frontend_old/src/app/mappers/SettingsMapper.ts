import { XDamSettings } from '@xdam/models/XDamSettings';
import * as settings from './settings.config.json';
import { hasIn } from 'ramda';

/**
 * This class extracts and maps data about the components
 * configuration given the active profile.
 */
export default class SettingsMapper extends XDamSettings {
    /**@ignore */
    constructor() {
        // const xdam = hasIn('$xdam', window) ? (<any>window).$xdam : {};
        const _settings = settings['default'];
        let params = null;
        if (hasIn('settings', _settings)) {
            params = _settings;
        }
        super(params);
    }
}
