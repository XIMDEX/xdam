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
import {isArray} from 'util';
import { faDownload, faEdit, faTrash } from '@fortawesome/free-solid-svg-icons';

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
  faDownload = faDownload;
  @Input() action: ActionModel;

  public formMetadata: FormGroup = new FormGroup({});

  new: boolean;
  show: boolean;
  preview = window.origin + '/assets/default_item_image.jpg';


  constructor() {}

  ngOnInit() {
      if (!isNil(this.action) && this.action.method === 'show') {
          this.initFormControlsWithData();
      } else if (this.action.method === 'new') {
          this.initFormControls();
      } else {
          this.initFormControls();
      }
  }

  private initFormControls() {
      this.formMetadata.addControl('files', new FormControl(''));
      this.formMetadata.addControl('type', new FormControl(''));
      this.formMetadata.addControl('name', new FormControl('', Validators.required));
      this.formMetadata.addControl('score', new FormControl(''));
      this.formMetadata.addControl('version', new FormControl(''));
      this.formMetadata.addControl('active', new FormControl(''));
  }

  private initFormControlsWithData() {
      const item = this.action.item;
      let field: FormControl;
      Object.keys(item).forEach(key => {
          if (key.startsWith('_')) {
              key = key.slice(1);
          }
          if (!isNil(item[key]) && key !== 'files') {
              field = new FormControl(item[key]);
              field.markAsDirty();
          } else {
              field = new FormControl('');
          }
          this.formMetadata.addControl(key, field);
      });
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

  downloadImage(){
    //downloadUrl('https://s1.1zoom.me/big3/471/Painting_Art_Back_view_Photographer_575380_3840x2400.jpg', 'image.png');
  }

}