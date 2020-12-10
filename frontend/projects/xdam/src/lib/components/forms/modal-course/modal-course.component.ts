import {Component, Input, OnInit} from '@angular/core';
import { faSave, faTimes } from '@fortawesome/free-solid-svg-icons';
import {FormBuilder, FormControl, FormGroup} from "@angular/forms";

@Component({
  selector: 'xdam-modal-course',
  templateUrl: './modal-course.component.html',
  styleUrls: ['./modal-course.component.scss']
})
export class ModalCourseComponent implements OnInit {

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
    console.log(this.courseForm.value);
  }

}
