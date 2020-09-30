import { Component, EventEmitter, Input, Output } from '@angular/core';

import { ActionModel } from '../../../models/src/lib/ActionModel';
import { Item } from '../../../models/src/lib/Item';
import { ListOptionsI } from './../../../models/src/lib/interfaces/ListOptions.interface';

@Component({
    selector: 'xdam-list',
    templateUrl: './list.component.html',
    styleUrls: ['./list.component.scss']
})
export class ListComponent {
    /**
     * The items received by the DAM component
     */
    @Input() items: Item[];
    @Input() settings: ListOptionsI;

    @Output() delete = new EventEmitter<Item>();
    @Output() download = new EventEmitter<Item>();
    @Output() edit = new EventEmitter<ActionModel>();
    @Output() select = new EventEmitter<ActionModel>();

    editItem(item: Item) {
        const action = new ActionModel();
        action.method = 'show';
        action.item = item;
        this.edit.emit(action);
    }

    deleteItem(item: Item) {
        this.delete.emit(item);
    }

    downloadItem(item: Item) {
        this.download.emit(item);
    }

    selectItem(item: Item) {
        const action = new ActionModel();
        action.method = 'select';
        action.item = item;
        this.select.emit(action);
    }
}
