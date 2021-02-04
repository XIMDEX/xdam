import {ChangeDetectorRef, Component, ElementRef, EventEmitter, Input, OnInit, Output, ViewChild} from '@angular/core';
import { faSave, faTimes } from '@fortawesome/free-solid-svg-icons';
import {  FormControl, FormGroup, Validators } from '@angular/forms';
import { ActionModel } from '../../../../models/src/lib/ActionModel';
import {isNil, hasIn, is} from 'ramda';
import { ListItemOptionI } from '@xdam/models/interfaces/ListOptions.interface';

import { faUpload } from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'xdam-modal-multimedia',
  templateUrl: './modal-multimedia.component.html',
  styleUrls: ['./modal-multimedia.component.scss']
})
export class ModalMultimediaComponent implements OnInit {

  //Icons
  faUpload = faUpload;
  faSave = faSave;
  faTimes = faTimes;

  //Input Files
  fileToUpload:FileList = null;
  coverToUpload:FileList = null;

  //form Grups
  public data: FormGroup = new FormGroup({});
  public dataform: FormGroup = new FormGroup({data: this.data});

  currentType:string = 'document';

  @Input() action: ActionModel;
  @Input() modal: any;
  @Input() settings: ListItemOptionI;
  @Input() resourceUrl: string;
  @Output() dataToSave = new EventEmitter<ActionModel>();

  @ViewChild('imgPreview') imgPreview: ElementRef;

  //Image
  defaultImage = window.origin + '/assets/default_item_image.jpg';
  image = null;
  imageError= false;


  constructor(private ref: ChangeDetectorRef) {}
  
  ngOnInit() {
      if (!isNil(this.action) && this.action.method === 'show') {
        this.initFormControlsWithData();
        this.initImage();
      } else if (this.action.method === 'new') {
          this.initFormControls();
          this.image = this.defaultImage;
      } else {
          this.initFormControls();
      }
  }

  public sendForm(e): any {
    if(this.dataform.valid){
      if(this.dataform.dirty || this.fileToUpload != null || this.coverToUpload != null){
        const action = new ActionModel();
        action.method = this.action.method === 'show' ? 'edit' : this.action.method;
        action.data = this.prepareData(this.dataform.value)
        this.dataToSave.emit(action);
      }
    }
  }

  prepareData(data){
    data['type'] = this.currentType;
    data['data'] = JSON.stringify({description: data['data']})

    if(this.coverToUpload != null && this.coverToUpload.length > 0){
      data['File'] = this.coverToUpload.item(0);
      //data['data']['mimetype'] = this.coverToUpload.item(0).type.split('/')[0];
    } else {
      delete data['File'];
    }

    //Detete Fields not required
    //delete data['files'];

    return data;
  }


  //Form Data

  initImage(){
    if (this.resourceUrl && !this.imageError && this.action.item.files){
        this.image= this.resourceUrl + '/resource/render/' + this.action.item.files[this.action.item.files.length-1] ;
    } else {
        this.image = this.defaultImage;
    }
  }

  imgError(){
    this.imageError = true;
    this.initImage();
  }

  private initFormControls() {
    //this.dataform.addControl('files', new FormControl(''));
    this.dataform.addControl('type', new FormControl('document'));
    this.dataform.addControl('active', new FormControl(''));
    let groupData: FormGroup = new FormGroup({});
    groupData.addControl('active', new FormControl(true));
    groupData.addControl('name', new FormControl('', Validators.required));
    groupData.addControl('external_url', new FormControl('', Validators.required));
    groupData.addControl('description', new FormControl('', Validators.required));
    groupData.addControl('tags', new FormControl([], Validators.required));
    groupData.addControl('categories', new FormControl([], Validators.required));
    groupData.addControl('partials', new FormControl({}));
    this.dataform.setControl('data', groupData);
  }

  private initFormControlsWithData() {
    const item = this.action.item;
    this.currentType = this.action.item['type'] + "";
    let field: FormControl;
    Object.keys(item).forEach(key => {
      if (key.startsWith('_')) {
          key = key.slice(1);
      }
      if(key === 'data'){
          let groupData: FormGroup = new FormGroup({});
          let {data} = this.action.item;
          Object.keys(data['description']).forEach(keyData => {
              groupData.addControl(keyData, new FormControl(data['description'][keyData]))
          });
          this.dataform.setControl('data', groupData);
          return
      } else if (!isNil(item[key]) && key !== 'files' ) {
          field = new FormControl(item[key]);
          field.markAsDirty();
      } else {
          field = new FormControl('');
      }
        this.dataform.addControl(key, field);
    });
  }

  handleFileInput(files: FileList) {
    this.fileToUpload = files;
    const type = this.fileToUpload.item(0).type.split('/')[0];
    switch(type){
      case 'image':
        this.currentType = 'image';
        break;
      case 'video':
        this.currentType = 'video';
        break;
      case 'aplication':
          this.currentType = 'document';
          break;
      case 'text':
        this.currentType = 'document';
        break;
    }
  }
  handleImageInput(files: FileList){
    var file = files.item(0);

    if (file.type.match(/image\/*/) == null) return;

    this.coverToUpload = files;
    var reader = new FileReader();
    reader.onload = (e)=>{
        this.imgPreview.nativeElement.src = reader.result;
    }

    reader.readAsDataURL(file);

  }

  changetype(value){
    this.currentType = value;
  }

}
