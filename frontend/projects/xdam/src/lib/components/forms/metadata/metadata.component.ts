import {Component, forwardRef, Input, OnInit} from '@angular/core';
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

    @Input() toFill: any;

    metadata: any;
    metadataLength: number;

    public metadataForm: FormGroup = new FormGroup({});

    constructor() {
        this.metadata = structure;
        this.metadataLength = Object.keys(this.metadata).length;
    }

    ngOnInit() {
        this.metadata.default.tabs.forEach((element, i) => {
            element.fields.forEach((input, j) => {
                let valueOfField = this.toFill[i].fields[j].value;
                if(input.type === 'text'){
                    this.metadataForm.addControl(input.id, new FormControl(valueOfField));
                } else if (input.type === 'select') {
                    this.metadataForm.addControl(input.id, new FormControl(''));
                }
            });
        });
    }

    public onTouched: () => void = () => {
    };

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
