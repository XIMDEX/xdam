import { isNil, hasIn } from 'ramda';
import { Input, Output, EventEmitter } from '@angular/core';

export abstract class QuestionBaseComponent<T> {
    @Input() question: any;
    @Output() onChange = new EventEmitter<T>();

    protected _value: T;
    protected abstract defaultValue: T;

    get key(): string {
        return this.question.key;
    }

    get label(): string | null {
        let label = null;
        if (hasIn('label', this.question) && !isNil(this.question.label)) {
            label = this.question.label;
        }
        return label;
    }

    set value(value: T) {
        this._value = value;
    }

    get value(): T {
        let value: T = this.defaultValue;
        if (isNil(this._value) && !isNil(this.question.value)) {
            value = this.question.value;
        } else if (!isNil(this._value)) {
            value = this._value;
        }
        return value;
    }

    get errors(): string[] {
        return this.question.errors || [];
    }

    get hasErrors(): boolean {
        return this.errors.length > 0;
    }

    public changeValue() {
        return this.onChange.emit(this.value);
    }
}
