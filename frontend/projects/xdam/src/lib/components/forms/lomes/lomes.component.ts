import {Component, forwardRef, OnInit} from '@angular/core';
import * as structure from './lomes.structure.json';
import {
    AbstractControl,
    ControlValueAccessor,
    FormControl,
    FormGroup,
    NG_VALIDATORS,
    NG_VALUE_ACCESSOR,
    ValidationErrors,
    Validators
} from '@angular/forms';

@Component({
    selector: 'xdam-lomes',
    templateUrl: './lomes.component.html',
    styleUrls: ['./lomes.component.scss'],
    providers: [
        {
            provide: NG_VALUE_ACCESSOR,
            useExisting: forwardRef(() => LomesComponent),
            multi: true
        },
        {
            provide: NG_VALIDATORS,
            useExisting: forwardRef(() => LomesComponent),
            multi: true
        }
    ]
})
export class LomesComponent implements OnInit, ControlValueAccessor {
    lomes: any;
    lomesLength: number;

    public lomesForm: FormGroup = new FormGroup({});

    constructor() {
        this.lomes = structure;
        this.lomesLength = Object.keys(this.lomes).length;
    }

    ngOnInit() {
        this.lomes.default.tabs.forEach(element => {
            element.fields.forEach(input => {
                this.lomesForm.addControl(input.id, new FormControl('', Validators.required));
            });
        });
    }

    public onTouched: () => void = () => {
    };

    writeValue(val: any): void {
        val && this.lomesForm.setValue(val, {emitEvent: false});
    }

    registerOnChange(fn: any): void {
        this.lomesForm.valueChanges.subscribe(fn);
    }

    registerOnTouched(fn: any): void {
        this.onTouched = fn;
    }

    setDisabledState?(isDisabled: boolean): void {
        isDisabled ? this.lomesForm.disable() : this.lomesForm.enable();
    }

    validate(c: AbstractControl): ValidationErrors | null {
        return this.lomesForm.valid ? null : {invalidForm: {valid: false, message: 'Lomes fields are invalid'}};
    }
}
