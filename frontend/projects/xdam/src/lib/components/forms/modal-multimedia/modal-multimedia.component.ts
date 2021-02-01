import {Component, ElementRef, EventEmitter, Input, OnInit, Output, ViewChild} from '@angular/core';
import { faSave, faTimes } from '@fortawesome/free-solid-svg-icons';
import {  FormControl, FormGroup } from '@angular/forms';
import { ActionModel } from '../../../../models/src/lib/ActionModel';
import { is } from 'ramda';
import { ListItemOptionI } from '@xdam/models/interfaces/ListOptions.interface';
import { FormMultimediaComponent } from './form-multimedia/form-multimedia.component';

@Component({
  selector: 'xdam-modal-multimedia',
  templateUrl: './modal-multimedia.component.html',
  styleUrls: ['./modal-multimedia.component.scss']
})
export class ModalMultimediaComponent implements OnInit {
  @Input() action: ActionModel;
  @Input() modal: any;
  @Input() settings: ListItemOptionI;
  @Input() resourceUrl: string;
  @Output() dataToSave = new EventEmitter<ActionModel>();

  @ViewChild('dataFormElement') dataFormElement: FormMultimediaComponent;

  courseForm: FormGroup;

  faSave = faSave;
  faTimes = faTimes;

  constructor() {
    this.courseForm = new FormGroup({
      dataForm: new FormControl(''),
      metadataForm: new FormControl(''),
      partials: new FormControl('')
    });
  }

  ngOnInit() {
      if (this.action.method === 'show') {
          this.courseForm.markAsTouched();
      }
  }

  sendForm(): any {
    console.log('this.courseForm.valid: ', this.courseForm.valid)
      if (this.courseForm.valid) {
          if (this.courseForm.controls.dataForm.dirty || this.courseForm.controls.metadataForm.dirty) {
              const action = new ActionModel();
              action.method = this.action.method === 'show' ? 'edit' : this.action.method;
              action.data = this.prepareData(this.courseForm.value.dataForm);
              let dataData = this.courseForm.value.dataForm['data'];
              dataData['fields'] = this.courseForm.get('partials').value;
              console.log(dataData)
              action.data['data'] = JSON.stringify({description: dataData});
              action.data['File'] = this.dataFormElement.getInputFiles();
              if(action.data['File'] == null) delete action.data['File'];
              this.dataToSave.emit(action);
          }
      }
  }

  prepareData(data: any){
    Object.keys(data).forEach(key => {
      if(data[key] === ''){
        delete data[key];
      }/*else if(key  === 'type' && is(Array, data[key])){
        data[key] = data[key][0];
      }*/
    })

    return data;
  }
}
