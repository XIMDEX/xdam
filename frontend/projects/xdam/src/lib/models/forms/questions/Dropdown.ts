import { Base } from './Base';

export interface DropdownI {
    multi: boolean;
    searchable: boolean;
    fetchable: boolean;
    endpoint?: string | null;
    options?: DropdownOptionsI[] | null;
    map?: DropdownOptionsI;
    responseKey?: string;
}

export interface DropdownOptionsI {
    key: string;
    value: any;
}

export class Dropdown extends Base<string> implements DropdownI {
    protected _multi = false;
    protected _searchable = false;
    protected _fetchable = false;
    protected _endpoint: string | null = null;
    protected _options: DropdownOptionsI[] | null = null;
    protected _map: DropdownOptionsI = { key: 'key', value: 'value' };
    protected _responseKey: string = 'result.data';
    protected _type = 'dropdown';

    constructor(params: Dropdown | any | null = null) {
        super();
        this.update(params);
    }

    get type(): string {
        return this._type;
    }

    set multi(multi: boolean) {
        this._multi = multi;
    }
    get multi(): boolean {
        return this._multi;
    }

    set searchable(searchable: boolean) {
        this._searchable = searchable;
    }
    get searchable(): boolean {
        return this._searchable;
    }

    set fetchable(fetchable: boolean) {
        this._fetchable = fetchable;
    }
    get fetchable(): boolean {
        return this._fetchable;
    }

    set endpoint(endpoint: string | null) {
        this._endpoint = endpoint;
    }
    get endpoint(): string | null {
        return this._endpoint;
    }

    set options(options: DropdownOptionsI[] | null) {
        this._options = options;
    }
    get options(): DropdownOptionsI[] | null {
        return this._options;
    }

    set map(map: DropdownOptionsI) {
        this._map = map;
    }
    get map(): DropdownOptionsI {
        return this._map;
    }

    set responseKey(responseKey: string) {
        this._responseKey = responseKey;
    }
    get responseKey(): string {
        return this._responseKey;
    }
}
