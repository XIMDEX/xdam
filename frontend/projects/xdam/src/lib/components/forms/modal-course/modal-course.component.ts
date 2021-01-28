import {Component, ElementRef, EventEmitter, Input, OnInit, Output, ViewChild} from '@angular/core';
import { faSave, faTimes } from '@fortawesome/free-solid-svg-icons';
import {  FormControl, FormGroup } from '@angular/forms';
import { ActionModel } from '../../../../models/src/lib/ActionModel';
import { is } from 'ramda';
import { ListItemOptionI } from '@xdam/models/interfaces/ListOptions.interface';
import { FormCourseComponent } from './form-course/form-course.component';
import { animation } from '@angular/animations';

@Component({
  selector: 'xdam-modal-course',
  templateUrl: './modal-course.component.html',
  styleUrls: ['./modal-course.component.scss']
})
export class ModalCourseComponent implements OnInit {
  @Input() action: ActionModel;
  @Input() modal: any;
  @Input() settings: ListItemOptionI;
  @Input() resourceUrl: string;
  @Output() dataToSave = new EventEmitter<ActionModel>();

  @ViewChild('dataFormElement') dataFormElement: FormCourseComponent;

  courseForm: FormGroup;

  faSave = faSave;
  faTimes = faTimes;

  constructor() {
    this.courseForm = new FormGroup({
      dataForm: new FormControl(''),
      metadataForm: new FormControl('')
    });
  }

  ngOnInit() {
      if (this.action.method === 'show') {
          this.courseForm.markAsTouched();
      }
  }

  sendForm(): any {
      if (this.courseForm.valid) {
          if (this.courseForm.controls.dataForm.dirty || this.courseForm.controls.metadataForm.dirty) {
              const action = new ActionModel();
              action.method = this.action.method === 'show' ? 'edit' : this.action.method;
              action.data = this.prepareData(this.courseForm.value.dataForm);
              action.data['data'] = JSON.stringify({description: this.courseForm.value.dataForm['data']});
              action.data['File'] = this.dataFormElement.getInputFiles();
              action.data['type'] = "course";
              if(action.data['File'] == null) delete action.data['File'];
              this.dataToSave.emit(action);
          }
      }
  }

  prepareData(data: any){
    Object.keys(data).forEach(key => {
      if(data[key] === ''){
        delete data[key];
      }else if(key  === 'type' && is(Array, data[key])){
        data[key] = data[key][0];
      }
    })

    return data;
  }
}
