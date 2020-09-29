import { Settings } from '@xdam/models/interfaces/Settings.interface';
import { hasIn, isNil } from 'ramda';
import { HttpParams } from '@angular/common/http';
import { isFunction } from 'util';

/**
 * Mapper class for routes and models configurations in index or environment file
 */
export default class RouterMapper {
    protected readonly loadType = {
        window: () => (hasIn('$xdam', window) ? (<any>window).$xdam : null),
        property: null,
        url: null
    };

    /**
     * The base URL for all requests
     */
    private _baseUrl = '';

    /**
     * The availables routes in API for the requests
     */
    private _routes = null;

    /**
     * The auth token for requests
     */
    private _token = '';

    /**
     * The base parameters for all queries
     */
    private _baseParams = null;

    /**
     * @ignore
     */
    constructor() {
        this.init();
    }

    /**@ignore */
    set baseUrl(url: string) {
        this._baseUrl = url;
    }

    /**@ignore */
    get baseUrl(): string {
        return this._baseUrl;
    }

    /**@ignore */
    set routes(routes: { SettingsEdpoint } | null) {
        this._routes = routes;
    }

    /**@ignore */
    get routes(): { SettingsEdpoint } | null {
        return this._routes;
    }

    /**@ignore */
    set token(token: string | null) {
        this._token = token;
    }

    /**@ignore */
    get token(): string | null {
        return this._token;
    }

    /**@ignore */
    set baseParams(baseParams) {
        this._baseParams = baseParams;
    }

    /**@ignore */
    getBaseParams(params: HttpParams) {
        if (!isNil(this._baseParams)) {
            for (const key of Object.keys(this._baseParams)) {
                params = params.append(key, String(this.baseParams[key]));
            }
        }
        return params;
    }

    /**@ignore */
    protected urlParams() {
        const url = new URL(window.location.href);
        return url.searchParams;
    }

    /**
     * Initializes the mapper extracting values from the environment and the active window,
     * prioritising the window object.
     */
    private init() {
        const xdam: Settings = this.loadProperties();

        if (isNil(xdam)) {
            throw new Error('Failed to load Dam settings');
        }

        this.baseUrl = xdam.base_url;
        this.token = xdam.token;
        this.routes = xdam.endpoints;
        this.baseParams = xdam.base_params;
    }

    private loadProperties(props: Settings | null = null): Settings {
        if (isNil(props)) {
            for (const type of Object.values(this.loadType)) {
                props = isFunction(type) ? type() : type;
                if (!isNil(props)) {
                    break;
                }
            }
        }
        return props;
    }
}
