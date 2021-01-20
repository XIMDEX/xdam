import { hasIn, isNil } from 'ramda';

import BaseModel from './Base';
import { ItemModel } from './interfaces/ItemModel.interface';
import { standard } from './profiles/standard';

/**
 * The item model used by the table component to show info about every single resource.
 */
export class Item extends BaseModel {
    /**
     * Say if item is active or not
     * @private
     */
    //private _active: boolean;
    /**
     * The metadata of the item
     * @private
     */
    private _data: string;
    /**
     * The related files with the item
     * @private
     */
    private _files: any[];
    /**
     * The identifier of item
     * @private
     */
    private _id: string;
    /**
     * The preview of the item
     * @private
     */
    private _previews: string;
    /**
     * The name of the item
     * @private
     */
    private _name: string;
    /**
     * The score of the item
     * @private
     */
    //private _score: number;
    /**
     * The type/s or category/ies of the item
     * @private
     */
    private _type: any[];
    /**
     * The version number of item
     * @private
     */
    //private _version: number;

    /**@ignore */
    constructor(item: any = null, schema: ItemModel | null = null) {
        super();
        if (isNil(schema)) {
            schema = standard.list.model;
        }
        if (!isNil(item)) {
            this.update(this.prepareData(item, schema));
        }
    }

    /*get active(): boolean {
        return this._active;
    }

    set active(value: boolean) {
        this._active = value;
    }*/

    get data(): string {
        return this._data;
    }

    set data(value: string) {
        this._data = value;
    }

    get files(): any[] {
        return this._files;
    }

    set files(value: any[]) {
        this._files = value;
    }

    get id(): string {
        return this._id;
    }

    set id(value: string) {
        this._id = value;
    }

    get previews(): string {
        return this._previews;
    }

    set previews(value: string) {
        this._previews = value;
    }

    get name(): string {
        return this._name;
    }

    set name(value: string) {
        this._name = value;
    }

    /*get score(): number {
        return this._score;
    }

    set score(value: number) {
        this._score = value;
    }*/

    get type(): any[] {
        return this._type;
    }

    set type(value: any[]) {
        this._type = value;
    }

    /*get version(): number {
        return this._version;
    }

    set version(value: number) {
        this._version = value;
    }*/

    protected prepareData(data: {}, schema: ItemModel) {
        const result = {};
        for (const key of Object.keys(schema)) {
            const itemKey = schema[key];
            if (hasIn(itemKey, data)) {
                result[key] = data[itemKey];
            } else if (isNil(itemKey)) {
                result[key] = null;
            } else if (!hasIn(itemKey, data)) {
                result[itemKey] = null;
            } else {
                throw new Error(`Invalid item data, key ${key} is required, please check your Item model settings`);
            }
        }

        return result;
    }
}
