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
      // this.toFill.forEach(input => {
      //     if (input.type === 'text') {
      //         let text = new FormControl(input.value);
      //         if (input.id === 'resource_name') {
      //             text.setValidators(Validators.required);
      //         }
      //         this.formMetadata.addControl(input.id, text);
      //     } else if(input.type === 'select' || 'file') {
      //         let field = new FormControl('');
      //         if (input.type === 'file') {
      //             field.setValidators(Validators.required);
      //         }
      //         this.formMetadata.addControl(input.id, field);
      //     }
      // });
      this.formMetadata.addControl(this.toFill.resource_file, new FormControl('', Validators.required));
  }

  public onTouched: () => void = () => {
  };

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
