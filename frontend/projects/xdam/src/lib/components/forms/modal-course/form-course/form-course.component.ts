import { Component, Input, OnInit, forwardRef } from '@angular/core';
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
import {ActionModel} from '../../../../../models/src/lib/ActionModel';
import {isNil} from 'ramda';

@Component({
  selector: 'xdam-form-course',
  templateUrl: './form-course.component.html',
  styleUrls: ['./form-course.component.scss'],
  providers: [
    {
        provide: NG_VALUE_ACCESSOR,
        useExisting: forwardRef(() => FormCourseComponent),
        multi: true
    },
    {
        provide: NG_VALIDATORS,
        useExisting: forwardRef(() => FormCourseComponent),
        multi: true
    }
]
})
export class FormCourseComponent implements OnInit, ControlValueAccessor {
  @Input() action: ActionModel;
  @Input() toFill: any;

  name: FormControl;

  public formMetadata: FormGroup = new FormGroup({});

  constructor() {}

  ngOnInit() {
      if (!isNil(this.action) && this.action.method === 'new') {
          this.formMetadata.addControl('resource_file', new FormControl('', Validators.required));
          this.formMetadata.addControl('resource_type', new FormControl(''));
          this.formMetadata.addControl('resource_license_type', new FormControl(''));
          this.formMetadata.addControl('resource_license', new FormControl(''));
          this.formMetadata.addControl('resource_name', new FormControl('', Validators.required));
          this.formMetadata.addControl('resource_description', new FormControl(''));
          this.formMetadata.addControl('resource_duration', new FormControl(''));
          this.formMetadata.addControl('resource_price', new FormControl(''));
      } else if (!isNil(this.action)) {
          console.log(this.action);
          Object.keys(this.toFill).forEach(key => {
              const item = this.toFill[key];
              if (item.type === 'text') {
                  const text = new FormControl(item.value);
                  if (key === 'resource_name') {
                      text.setValidators([Validators.required]);
                      text.updateValueAndValidity();
                  }
                  this.formMetadata.addControl(key, text);
              } else if (item.type === 'select' || 'file') {
                  const field = new FormControl('');
                  if (key === 'resource_file') {
                      field.setValidators([Validators.required]);
                      field.updateValueAndValidity();
                  }
                  this.formMetadata.addControl(key, field);
              }
          });
      }
      console.log(this.formMetadata);
  }

  public onTouched: () => void = () => {
  }

  writeValue(val: any): void {
      val && this.formMetadata.setValue(val, {emitEvent: false});
  }

  registerOnChange(fn: any): void {
      this.formMetadata.valueChanges.subscribe(fn);
  }

  registerOnTouched(fn: any): void {
      this.onTouched = fn;
  }

  setDisabledState?(isDisabled: boolean): void {
      isDisabled ? this.formMetadata.disable() : this.formMetadata.enable();
  }

  validate(c: AbstractControl): ValidationErrors | null {
      return this.formMetadata.valid ? null : {invalidForm: {valid: false, message: 'Metadata fields are invalid'}};
  }

}
