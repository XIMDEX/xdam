import { Base } from './Base';

export interface FileI {
    multiple?: boolean;
    accept?: string;
}

export class File extends Base<FileList | any[]> implements FileI {
    protected _type = 'file';
    protected _accept = '*/*';
    protected _multiple = false;

    constructor(params: File | any | null = null) {
        super();
        this.update(params);
    }

    get type(): string {
        return this._type;
    }

    set accept(accept: string) {
        this._accept = accept;
    }
    get accept(): string {
        return this._accept;
    }

    set multiple(multiple: boolean) {
        this._multiple = multiple;
    }
    get multiple(): boolean {
        return this._multiple;
    }
}
