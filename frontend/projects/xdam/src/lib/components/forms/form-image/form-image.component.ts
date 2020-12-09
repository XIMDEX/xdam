import {Component, Input, OnInit, Output} from '@angular/core';
import {FormResourceBase} from '../FormResource.base';
import {ImageResource} from '../../../models/forms/ImageResource';

@Component({
  selector: 'xdam-form-image',
  templateUrl: './form-image.component.html',
  styleUrls: ['./form-image.component.scss']
})
export class FormImageComponent extends FormResourceBase implements OnInit {

  image: ImageResource;

  constructor() {
      super();
      this.image = new ImageResource();
  }

    ngOnInit() {
        this.setMetadata(this.image, this.entryData);
        this.setSpecificData();
    }

    setMetadata(image: any, data: any) {
        super.setMetadata(image, data);
    }

    setSpecificData() {
        this.image.image = this.entryData.Preview;
    }

}
