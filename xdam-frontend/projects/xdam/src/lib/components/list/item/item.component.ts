import { Component, EventEmitter, Input, Output } from '@angular/core';
import { ListItemActionsI, ListItemOptionI } from './../../../../models/src/lib/interfaces/ListOptions.interface';
import { faDownload, faEdit, faTrash } from '@fortawesome/free-solid-svg-icons';

import { Item } from '../../../../models/src/lib/Item';
import { SweetAlertOptions } from 'sweetalert2';
import { hasIn } from 'ramda';
import { sprintf } from 'sprintf-js';

@Component({
    selector: 'xdam-item',
    templateUrl: './item.component.html',
    styleUrls: ['./item.component.scss']
})
export class ItemComponent {
    faDownload = faDownload;
    faEdit = faEdit;
    faTrash = faTrash;

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
        return sprintf(this.settings.title, this.item.title);
    }

    set img(url: string) {
        this.item.image = url;
    }

    get img(): string {
        return this.item.image;
    }

    get actions(): ListItemActionsI | null {
        return this.settings.actions;
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
        let image = null;
        if (hasIn(this.item.type.toLowerCase(), this.settings.placeholder)) {
            image = this.settings.placeholder[this.item.type.toLowerCase()];
        } else if (hasIn('default', this.settings.placeholder)) {
            image = this.settings.placeholder['default'];
        }

        this.img = image;
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
