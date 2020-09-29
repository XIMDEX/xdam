import BaseModel from '../../../../models/src/lib/Base';
import { isNil } from 'ramda';

export interface BaseI<T> {
    value?: T;
    realName?: string;
    key?: string;
    label?: string;
    required?: boolean;
    order?: number;
    type?: string;
    val?: any;
    errors?: string[];
}

export class Base<T> extends BaseModel implements BaseI<any> {
    protected _value: T;
    protected _realName: string;
    protected _key: string;
    protected _label: string;
    protected _required: boolean;
    protected _order: number;
    protected _type: string;
    protected _errors: string[];

    public constructor(params: Base<any> | null = null) {
        super();
        this.update(params);
    }

    set value(value: any) {
        this._value = value;
    }
    get value(): any {
        return this._value;
    }

    set realName(realName: string) {
        this._realName = realName;
    }
    get realName(): string {
        return this._realName;
    }

    set key(key: string) {
        this._key = key;
    }
    get key(): string {
        return isNil(this._key) ? String(Math.floor(Date.now() / 1000)) : this._key;
    }

    set label(label: string) {
        this._label = label;
    }
    get label(): string {
        return this._label;
    }

    set required(required: boolean) {
        this._required = required;
    }
    get required(): boolean {
        return this._required;
    }

    set order(order: number) {
        this._order = order;
    }
    get order(): number {
        return isNil(this._order) ? 1 : this._order;
    }

    set type(type: string) {
        this._type = type;
    }
    get type(): string {
        return this._type;
    }

    set errors(errors: string[]) {
        this._errors = errors;
    }
    get errors(): string[] {
        return this._errors;
    }
}
