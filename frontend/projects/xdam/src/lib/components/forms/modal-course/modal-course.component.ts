import {Component, Input, OnInit} from '@angular/core';
import { faSave, faTimes } from '@fortawesome/free-solid-svg-icons';
import { FormBuilder, FormControl, FormGroup } from '@angular/forms';
import { ActionModel } from '../../../../models/src/lib/ActionModel';

@Component({
  selector: 'xdam-modal-course',
  templateUrl: './modal-course.component.html',
  styleUrls: ['./modal-course.component.scss']
})
export class ModalCourseComponent implements OnInit {
   @Input() action: ActionModel;
   @Input() toFill: any;
   @Input() modal: any;

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
  }

  sendForm(): any {
    if (this.courseForm.valid) {
        console.log(this.courseForm.value);
    }
  }

}
