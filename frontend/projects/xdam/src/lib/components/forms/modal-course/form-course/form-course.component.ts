import { Component,Input, OnInit, forwardRef } from '@angular/core';
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

  @Input() toFill: any;

  name: FormControl;

  public formMetadata: FormGroup = new FormGroup({});

  constructor() {}

  ngOnInit() {
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
