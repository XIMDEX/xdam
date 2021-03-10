import { Component, EventEmitter, Input, Output } from '@angular/core';
import { ListItemActionsI, ListItemOptionI } from './../../../../models/src/lib/interfaces/ListOptions.interface';
import { faDownload, faEdit, faTrash } from '@fortawesome/free-solid-svg-icons';


import { Item } from '../../../../models/src/lib/Item';
import { SweetAlertOptions } from 'sweetalert2';
import { hasIn } from 'ramda';
import { sprintf } from 'sprintf-js';

const CAN_BE_DOWNLOADED:string[] = ["video"];
const titles = {
    document: 'name',
    video: 'name',
    audio: 'name',
    url: 'name',
    course: 'name'
}

@Component({
    selector: 'xdam-item',
    templateUrl: './item.component.html',
    styleUrls: ['./item.component.scss']
})
export class ItemComponent {
    faDownload = faDownload;
    faEdit = faEdit;
    faTrash = faTrash;
    defaultImage = window.origin + '/assets/default_item_image.jpg';

    imagePreview = null;
    imageError = false;

    @Input() item: Item;
    @Input() settings: ListItemOptionI;

    @Output() delete = new EventEmitter<Item>();
    @Output() download = new EventEmitter<Item>();
    @Output() edit = new EventEmitter<Item>();
    @Output() select = new EventEmitter<Item>();

    constructor() {}

    get type(): string {
        return sprintf(this.settings.type, this.item.type).toUpperCase();
    }

    get title(): string {
        if(this.type.toLocaleLowerCase() === 'course'){
            return sprintf(this.settings.title, this.item.data['description']['name']);
        }else{
            return sprintf(this.settings.title, this.item.data['description']['name']);
        }
    }

    set preview(url: string) {
        this.imagePreview  = this.defaultImage;
    }

    get preview(): string {
        if (!this.imageError && this.item.previews){
            this.imagePreview = this.item.previews[this.item.previews.length-1];
            return this.settings.urlResource + '/resource/render/'+  this.imagePreview;
        }else{
            return this.defaultImage;
        }
    }

    get actions(): ListItemActionsI | null {
        return this.settings.actions;
    }

    get canBeDownloaded():boolean{
        for(let i = 0 ; i < this.item.type.length; i++){
            let type = this.item.type[i].toLowerCase();
            if(CAN_BE_DOWNLOADED.includes(type)){
                return true;
            }
        }
        return false;
    }

    get deleteModal(): SweetAlertOptions {
        return {
            title: 'Confirm Deletion',
            html: `<div class="xdam-bold">${
                this.title
            }</div><span>Are you sure you want to permanently remove this item?</span>`,
            type: 'warning',
            showCancelButton: true,
            showConfirmButton: true,
            allowEnterKey: false,
            focusConfirm: false
        };
    }
    
    imgError() {
        this.imageError = true;
        //this.preview = this.defaultImage;
    }

    editItem(evt: Event) {
        evt.stopPropagation();
        this.edit.emit(this.item);
    }

    deleteItem(confirm: boolean) {
        if (confirm) {
            this.delete.emit(this.item);
        }
    }

    downloadItem(evt: Event) {
        evt.stopPropagation();

        this.download.emit(this.item);
    }

    onSelectItem() {
        if (this.actions.select) {
            this.select.emit(this.item);
        }
    }
}
