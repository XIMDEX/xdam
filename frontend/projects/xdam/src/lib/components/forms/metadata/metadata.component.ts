import {Component, forwardRef, Input, OnInit} from '@angular/core';
import { isNil } from 'ramda';
import * as structure from './metadata.structure.json';
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
import { ActionModel } from '../../../../models/src/lib/ActionModel';

@Component({
    selector: 'xdam-metadata',
    templateUrl: './metadata.component.html',
    styleUrls: ['./metadata.component.scss'],
    providers: [
        {
            provide: NG_VALUE_ACCESSOR,
            useExisting: forwardRef(() => MetadataComponent),
            multi: true
        },
        {
            provide: NG_VALIDATORS,
            useExisting: forwardRef(() => MetadataComponent),
            multi: true
        }
    ]
})
export class MetadataComponent implements OnInit, ControlValueAccessor {
    @Input() action: ActionModel;

    metadata: any;
    metadataLength: number;

    public metadataForm: FormGroup = new FormGroup({});

    constructor() {
        this.metadata = structure;
        this.metadataLength = Object.keys(this.metadata).length;
    }

    ngOnInit() {
        if (this.action.method === 'show') {
            if (!isNil(this.action.item) && this.action.item.data.startsWith('{')) {
                this.initFormControlsWithData();
            } else {
                this.initFormControls();
            }
        } else if (this.action.method === 'new') {
            this.initFormControls();
        }
    }

    private initFormControls() {
        this.metadata.default.tabs.forEach(element => {
            element.fields.forEach(input => {
                this.metadataForm.addControl(input.id, new FormControl(''));
            });
        });
    }

    private initFormControlsWithData() {
        const metadataValues = JSON.parse(this.action.item.data);
        Object.keys(metadataValues).forEach(key => {
            let field: FormControl;
            if (!isNil(metadataValues[key]) && key !== 'files') {
                field = new FormControl(metadataValues[key]);
            } else {
                field = new FormControl('');
            }
            this.metadataForm.addControl(key, field);
        });
    }

    public onTouched: () => void = () => {
    }

    writeValue(val: any): void {
        val && this.metadataForm.setValue(val, {emitEvent: false});
    }

    registerOnChange(fn: any): void {
        this.metadataForm.valueChanges.subscribe(fn);
    }

    registerOnTouched(fn: any): void {
        this.onTouched = fn;
    }

    setDisabledState?(isDisabled: boolean): void {
        isDisabled ? this.metadataForm.disable() : this.metadataForm.enable();
    }

    validate(c: AbstractControl): ValidationErrors | null {
        return this.metadataForm.valid ? null : {invalidForm: {valid: false, message: 'Lomes fields are invalid'}};
    }
}
