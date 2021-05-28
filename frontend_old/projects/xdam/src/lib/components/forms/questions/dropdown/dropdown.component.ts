import { HttpClient } from '@angular/common/http';
import { QuestionBaseComponent } from './../question.component.base';
import { isNil, hasIn, is } from 'ramda';
import { Component, Input, OnChanges, SimpleChanges } from '@angular/core';
import { Dropdown as QuestionDropdown, DropdownOptionsI } from './../../../../models/forms/questions/Dropdown';

@Component({
    selector: 'xdam-dropdown',
    templateUrl: './dropdown.component.html',
    styleUrls: ['./dropdown.component.scss']
})
export class DropdownComponent extends QuestionBaseComponent<any[] | null> implements OnChanges {
    @Input() question: QuestionDropdown;

    protected defaultValue = null;
    protected _options = null;

    constructor(private http: HttpClient) {
        super();
    }

    ngOnChanges(changes: SimpleChanges): void {
        if (hasIn('question', changes) && !isNil(changes.question.currentValue)) {
            if (this.fetchable && !isNil(this.endpoint)) {
                this.obtainOptions();
            }
        }
    }

    get map() {
        return this.question.map;
    }

    get fetchable(): boolean {
        return this.question.fetchable;
    }

    get searchable(): boolean {
        return this.question.searchable;
    }

    get endpoint(): string | null {
        return this.question.endpoint;
    }

    get multiple(): boolean {
        return this.question.multi;
    }

    get responseKey(): string {
        return this.question.responseKey;
    }

    get options(): DropdownOptionsI[] | null {
        let options = null;
        if (this.fetchable) {
            options = this._options;
        } else if (!isNil(this.question.options)) {
            options = this.question.options;
        }

        if (is(Array, options) && options.length === 0) {
            options = null;
        }
        return options;
    }

    protected obtainOptions(): void {
        this.http.get(this.endpoint).subscribe(
            data => {
                this._options = this.getResponse(data);
            },
            error => console.error(error)
        );
    }

    protected getResponse(response: any): any {
        const map = this.responseKey.split('.');
        let result = { ...response };
        for (const key of map) {
            if (hasIn(key, result)) {
                result = result[key];
            }
        }
        return result;
    }
}
