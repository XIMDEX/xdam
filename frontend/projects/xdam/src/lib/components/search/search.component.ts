import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { faEraser, faSearch, faSync, faTrashAlt } from '@fortawesome/free-solid-svg-icons';
import { XdamMode } from '@xdam/models/interfaces/XdamMode.interface';
import { hasIn, isEmpty, isNil } from 'ramda';

import { ActionModel } from '../../../models/src/lib/ActionModel';
import { SearchModel } from '../../../models/src/lib/SearchModel';
import { GlobalService } from '../../services/global.service';
import { SearchOptionsI } from './../../../models/src/lib/interfaces/SearchModel.interface';

@Component({
    selector: 'xdam-search',
    templateUrl: './search.component.html',
    styleUrls: ['./search.component.scss']
})
export class SearchComponent implements OnInit {
    @Input() settings: SearchOptionsI = null;

    @Output() search = new EventEmitter<SearchModel>();
    @Output() action = new EventEmitter<ActionModel>();
    @Output() logout = new EventEmitter<any>();
    /**@ignore */
    faSearch = faSearch;

    /**@ignore */
    faEraser = faEraser;

    /**@ignore */
    faSync = faSync;

    /**@ignore */
    faTrash = faTrashAlt;

    content: string;
    lastContent: string;

    modes = [
        {value: XdamMode.Course, label: "Curso"},
        {value: XdamMode.Multimedia, label: "Multimedia" }
    ];

    selecMode: any;

    constructor(
        private globalService_ : GlobalService
    ) {}

    ngOnInit() {}

    get input() {
        let result = null;
        if (!isNil(this.settings) && hasIn('input', this.settings)) {
            result = this.settings.input;
        }
        return result;
    }

    get actions() {
        let result = null;
        if (!isNil(this.settings) && hasIn('actions', this.settings)) {
            result = this.settings.actions;
        }
        return result;
    }

    updateSearch() {
        if (this.lastContent !== this.content && !isNil(this.content)) {
            const params = new SearchModel();
            params.content = this.content;
            params.reload = true;
            this.search.emit(params.only('content', 'page', 'reload'));
            this.lastContent = this.content;
        }
    }

    resetSearch() {
        if (!isEmpty(this.lastContent) && !isNil(this.lastContent)) {
            const params = new SearchModel();
            params.reload = true;
            this.search.emit(params.only('content', 'page', 'reload'));
            this.lastContent = this.content = '';
        } else {
            this.content = '';
        }
    }

    logoutAction() {
        this.logout.emit();
    }

    sendAction() {
        const action = new ActionModel();
        action.method = 'new';
        return this.action.emit(action);
    }

    onChangeMode({value}){
        this.globalService_.setMode(value);
    }
}
