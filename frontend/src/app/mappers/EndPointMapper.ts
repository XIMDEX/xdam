import { isNil } from 'ramda';
import * as apiConfig from './endpoints.config.json';
import { sprintf } from 'sprintf-js';
import { Item } from '@xdam/models/Item';
import {HttpParams} from '@angular/common/http';

/**
 * Mapper class to get endpoints url's
 */
export default class EndPointMapper {

    private _baseUrl = '';
    private _api = '';
    private _baseOptions = {};
    private readonly endpoints = {};

    constructor() {
        this.baseUrl = apiConfig.baseUrl;
        this.api = apiConfig.api;
        this.baseOptions = apiConfig.options;
        this.endpoints = apiConfig.endpoints;
    }

    get baseUrl(): string {
        return this._baseUrl;
    }

    set baseUrl(value: string) {
        this._baseUrl = value;
    }

    get api(): string {
        return this._api;
    }

    set api(value: string) {
        this._api = value;
    }

    get baseOptions(): {} {
        return this._baseOptions;
    }

    set baseOptions(value: {}) {
        this._baseOptions = value;
    }

    /**
     * This method construct the api endpoint, from model and action
     * @param model
     * @param action
     * @param item
     * @return url
     */
    public getEndPointUrl(model: string = null, action: string = null, item: Item = null): string {
        if (isNil(model) || isNil(action)) {
            throw new Error('Model and Action can not be null');
        }
        if (this.endpoints.hasOwnProperty(model) && this.endpoints[model].hasOwnProperty(action)) {
            let url = this.baseUrl.concat(this.api.concat(this.endpoints[model][action].uri));
            if (!isNil(item)) {
                url = sprintf(url, item);
            }
            return url;
        } else {
            throw new Error('Endpoint have not Model or Model have not Action');
        }
    }

    /**
     * This method return the http method of the uri
     * @param model
     * @param action
     */
    public getHttpMethod(model: string = null, action: string = null): string {
        return this.endpoints[model][action].method;
    }

    public getBaseParams() {
        return new HttpParams()
            .set('page', this.baseOptions['params']['page'])
            .set('search', this.baseOptions['params']['search'])
            .set('limit', this.baseOptions['params']['limit']);
    }
}
