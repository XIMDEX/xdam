import { hasIn, isNil } from 'ramda';

import BaseModel from './Base';
import { ItemModel } from './interfaces/ItemModel.interface';
import { standard } from './profiles/standard';

/**
 * The item model used by the table component to show info about every single resource.
 */
export class Item extends BaseModel {
    /**
     * The id of the resource
     */
    protected _id: string | number;
    /**
     * The title of the resource
     */
    protected _title: string;
    /**
     * The hash of the resource
     */
    protected _hash: string | number;
    /**
     * The file size of the resource
     */
    protected _size: string | null;
    /**
     * The type of the resource
     */
    protected _type: string;
    /**
     * The image src of the resource
     */
    protected _image: string;

    protected _context: string;

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

    set id(id: string | number) {
        this._id = id;
    }
    get id(): string | number {
        return this._id;
    }

    set title(title: string) {
        this._title = title;
    }
    get title(): string {
        return this._title;
    }

    set hash(hash: string | number) {
        this._hash = hash;
    }
    get hash(): string | number {
        return this._hash;
    }

    set size(size: string | null) {
        this._size = size;
    }
    get size(): string | null {
        return this._size;
    }
    set type(type: string) {
        this._type = type;
    }
    get type(): string {
        return this._type;
    }

    set image(image: string) {
        this._image = image;
    }
    get image(): string {
        return this._image;
    }

    set context(context: string) {
        this._context = context;
    }
    get context(): string {
        return this._context;
    }

    protected prepareData(data: {}, schema: ItemModel) {
        const result = {};
        for (let key of Object.keys(schema)) {
            const itemKey = schema[key];
            if (hasIn(itemKey, data)) {
                result[key] = data[itemKey];
            } else if (isNil(itemKey)) {
                result[key] = null;
            } else {
                throw new Error(`Invalid item data, key ${key} is required, please check your Item model settings`);
            }
        }

        return result;
    }
}
