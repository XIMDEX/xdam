import {Component, OnInit} from '@angular/core';
import { CourseResource } from '../../../models/forms/CourseResource';
import {FormResourceBase} from '../FormResource.base';

@Component({
  selector: 'xdam-form-course',
  templateUrl: './form-course.component.html',
  styleUrls: ['./form-course.component.scss']
})
export class FormCourseComponent extends FormResourceBase implements OnInit {

  course: CourseResource;

  constructor() {
      super();
      this.course = new CourseResource();
  }

  ngOnInit() {
      this.setMetadata(this.course, this.entryData);
      this.setSpecificData();
  }

  setMetadata(course: any, data: any) {
      super.setMetadata(course, data);
  }

  setSpecificData() {
      this.course.price = this.entryData.Price;
      this.course.duration = this.entryData.Duration;
  }
}
