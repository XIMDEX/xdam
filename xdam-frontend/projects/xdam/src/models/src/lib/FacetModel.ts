import { FacetI } from './interfaces/FacetI.interface';
import BaseModel from './Base';

export class FacetModel extends BaseModel implements FacetI {
    private _key: string;
    private _label: string;
    private _default: string | null;
    private _values: any;

    public constructor(params: FacetI) {
        super();
        this.update(params);
    }

    set key(key: string) {
        this._key = key;
    }
    get key(): string {
        return this._key;
    }

    set label(label: string) {
        this._label = label;
    }
    get label(): string {
        return this._label;
    }

    set default(defaultVal: string | null) {
        this._default = defaultVal;
    }
    get default(): string | null {
        return this._default;
    }

    set values(values: any) {
        this._values = values;
    }
    get values(): any {
        return this._values;
    }
}
