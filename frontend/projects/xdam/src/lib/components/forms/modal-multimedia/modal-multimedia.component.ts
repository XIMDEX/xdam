import {ChangeDetectorRef, Component, ElementRef, EventEmitter, Input, OnInit, Output, ViewChild} from '@angular/core';
import { faSave, faTimes, faDownload, faEdit, faTrash } from '@fortawesome/free-solid-svg-icons';
import {  FormControl, FormGroup, Validators } from '@angular/forms';
import { ActionModel } from '../../../../models/src/lib/ActionModel';
import {isNil, hasIn, is} from 'ramda';
import { ListItemOptionI } from '@xdam/models/interfaces/ListOptions.interface';


import { faUpload } from '@fortawesome/free-solid-svg-icons';
import Swal from 'sweetalert2'
import { QuestionComponent, ResultQuestionI } from '../inputsFroms/question/question.component';

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
  faDownload= faDownload;
  faEdit = faEdit;
  faTrash = faTrash;
  
  //Input Files
  filesToUpload: File[] = [];
  coverToUpload: FileList = null;

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
  @ViewChild('questionDeletedFile') questionDeletedFile!: QuestionComponent;
  //Image
  defaultImage = window.origin + '/assets/default_item_image.jpg';
  image = null;
  previewDelete = false;
  imageError= false;

  //Files
  filesToDelete = [];
  files =[]
  
  fileToDeleteAfterPopupAccept = {};

  constructor(private ref: ChangeDetectorRef) {}
  
  ngOnInit() {
    if (!isNil(this.action) && this.action.method === 'show') {
      this.initFormControlsWithData();
      this.initImage();
      this.files = this.action.data.files;

    } else if (this.action.method === 'new') {
        this.initFormControls();
        this.initImage();
        //this.image = this.defaultImage;
    } else {
        this.initFormControls();
    }
  }

  public sendForm(e): any {
    if(this.dataform.valid){
      if(this.dataform.dirty || this.filesToUpload.length > 0 || this.coverToUpload != null || this.deleteImage){
        const action = new ActionModel();
        action.method = this.action.method === 'show' ? 'edit' : this.action.method;
        //action.data.deletePreview = [];
        action.data = this.prepareData(this.dataform.value)
        this.dataToSave.emit(action);
      }
    }
  }

  prepareData(data){
    const objToSave:any = {
      dataToSave: null,
      filesToDelete: [],
      filesToUpload: []
    }
   
    if(this.action.method == 'new' && this.filesToUpload.length > 0){
      data['File[]'] = this.filesToUpload;
    }
    
    data['data'] = JSON.stringify({description: data['data']});
    data['type'] = this.currentType;

    if(this.coverToUpload != null && this.coverToUpload.length > 0 ){
      data['Preview'] = this.coverToUpload.item(0);
    }else if(this.previewDelete){
      objToSave.filesToDelete.push( this.action.data.previews[0])
    }

    if(this.filesToDelete.length > 0){
      objToSave.filesToDelete = objToSave.filesToDelete.concat(this.filesToDelete);
    }

    
    if(this.action.method === 'new'){
      data['File[]'] = this.filesToUpload;
    }else if(this.filesToUpload.length > 0){
      objToSave.filesToUpload = this.filesToUpload;
    }

    

    objToSave.dataToSave = data;
    return objToSave;
  }


  //Form Data
  initImage(){
    if (this.resourceUrl && !this.imageError && this.action.item.previews){
      this.image= this.resourceUrl + '/resource/render/' + this.action.item.previews[this.action.item.previews.length-1];
    } else {
      this.imageError = true;
      this.image = this.defaultImage;
    }
  }

  imgError(){
    this.imageError = true;
    this.initImage();
  }

  private initFormControls() {
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
    const item = this.action.data;
    this.currentType = this.action.item['type'] + "";
    let field: FormControl;
    this.dataform.addControl("id", new FormControl(item.id));
    let groupData: FormGroup = new FormGroup({});
    let {data} = this.action.data;
    Object.keys(data['description']).forEach(keyData => {
        groupData.addControl(keyData, new FormControl(data['description'][keyData]))
    });
    this.dataform.setControl('data', groupData);
  }

  handleFileInput(files: FileList) {
    for(let i = 0; i < files.length; i++){
      this.filesToUpload.push(files.item(i));
    }
  }

  handleImageInput(files: FileList){
    var file = files.item(0);
    if (file.type.match(/image\/*/) == null) return;

    this.previewDelete = false;
    this.coverToUpload = files;
    var reader = new FileReader();
    reader.onload = (e)=>{
        this.image = reader.result;
    }

    reader.readAsDataURL(file);

  }

  changetype(value){
    this.currentType = value;
  }

  deleteImage(e){
    e.preventDefault();
    this.image = this.defaultImage;
    this.previewDelete = true;
  }

  deleteFileToUpload(e, file){
    e.preventDefault();
    var i = this.filesToUpload.indexOf( file );
    this.filesToUpload.splice( i, 1 );
  }

  deleteFile(e, file){
    e.preventDefault();
    this.fileToDeleteAfterPopupAccept = file;
    this.questionDeletedFile.show();
  }

  acceptedDeleteFiles(result: ResultQuestionI){
    console.log(result)
    if(result.accept === true){
      var i = this.files.indexOf( this.fileToDeleteAfterPopupAccept );
      this.files.splice( i, 1 );
      this.filesToDelete.push(this.fileToDeleteAfterPopupAccept);
    }
  }


}
