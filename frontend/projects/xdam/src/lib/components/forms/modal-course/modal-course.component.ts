import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import { faSave, faTimes } from '@fortawesome/free-solid-svg-icons';
import { FormBuilder, FormControl, FormGroup } from '@angular/forms';
import { ActionModel } from '../../../../models/src/lib/ActionModel';
import { Item } from '../../../../models/src/lib/Item';
import { isNil } from 'ramda';

@Component({
  selector: 'xdam-modal-course',
  templateUrl: './modal-course.component.html',
  styleUrls: ['./modal-course.component.scss']
})
export class ModalCourseComponent implements OnInit {
   @Input() action: ActionModel;
   @Input() modal: any;

   @Output() dataToSave = new EventEmitter<ActionModel>();

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
              action.method = 'edit'; //this.action.method;
              action.data = this.courseForm.value.dataForm;
              action.data['data'] = JSON.stringify({description: this.courseForm.value.dataForm['data']});
              this.dataToSave.emit(action);
          }
      }
  }
}
