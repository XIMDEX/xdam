import { Base } from './Base';

export interface TextI {
    multiline: boolean;
}

export class Text extends Base<string> implements TextI {
    protected _multiline = false;
    protected _type = 'text';

    constructor(params: Text | any | null = null) {
        super();
        this.update(params);
    }

    get type(): string {
        return this._type;
    }

    set multiline(multiline: boolean) {
        this._multiline = multiline;
    }
    get multiline(): boolean {
        return this._multiline;
    }
}
