import { Component, Input, forwardRef, AfterContentInit } from '@angular/core';
import {
  AbstractControl,
  ControlValueAccessor,
  FormControl,
  FormGroup,
  NG_VALUE_ACCESSOR,
  ValidationErrors
} from '@angular/forms';
import {isNil} from 'ramda';

const partialFields = [
    "duration"
]

@Component({
  selector: 'xdam-form-partial-video',
  templateUrl: './partial-video.component.html',
  styleUrls: ['./partial-video.component.scss'],
  providers: [
    {
        provide: NG_VALUE_ACCESSOR,
        useExisting: forwardRef(() => PartialVideoComponent),
        multi: true
    }
]
})
export class PartialVideoComponent implements AfterContentInit, ControlValueAccessor {

    @Input('action') inputAction: any;

    partialForm: FormGroup = new FormGroup({});

    onChange: any = () => { };
    onTouched: any = () => { };
    
    currentValue = null;

    ngAfterContentInit(){
        if(this.currentValue == null){
            if (!isNil(this.inputAction) && this.inputAction.method === 'show') {
                this.value = this.inputAction.data.description.fields;
            } else if (this.inputAction.method === 'new') {
                this.initFormControlsWithOutData();
            } else {
                this.initFormControlsWithOutData();
            }
            this.onChange(this.value);
            this.onTouched();
        }
        
    }

    initFormControlsWithData(values: Object){
        Object.keys(values).forEach(keyData => {
            if(partialFields.includes(keyData)){
                this.partialForm.addControl(keyData, new FormControl(values[keyData]));
            }
        });
    }

    initFormControlsWithOutData(){
        this.partialForm.addControl('duration', new FormControl(0));
    }

    get value(): any {
        return this.currentValue;
    }

    set value(obj:  any) {
        if(obj === '' || obj === null || obj === {} || obj === undefined){
            this.initFormControlsWithOutData();
        } else {
            this.initFormControlsWithData(obj);
        }
        this.onChange(this.value);
        this.onTouched();
    }

    writeValue(obj: any): void{
        this.value = obj;
    }

    registerOnChange(fn) {
        this.onChange = fn;
    }
    
    registerOnTouched(fn) { 
        this.onTouched = fn;
    }

    validate(control: AbstractControl): ValidationErrors | null {
        return { 'custom': true };
    }

    inputsChanges(){
        this.currentValue = this.partialForm.value;
        this.onChange(this.value);
        this.onTouched();
    }

}
