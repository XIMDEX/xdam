import { Component, EventEmitter, Input, OnChanges, OnInit, Output, SimpleChanges } from '@angular/core';
import { PagerModel, PerPageModel } from './../../../models/src/lib/interfaces/PagerModel.interface';
import { faCaretLeft, faCaretRight, faStepBackward, faStepForward } from '@fortawesome/free-solid-svg-icons';
import { hasIn, isNil } from 'ramda';

import { PagerOptionsItems } from './../../../models/src/lib/interfaces/PagerOptions.interface';
import { SearchModel } from '../../../models/src/lib/SearchModel';

@Component({
    selector: 'xdam-paginator',
    templateUrl: './paginator.component.html',
    styleUrls: ['./paginator.component.scss']
})
export class PaginatorComponent implements OnInit, OnChanges {
    @Input() total: number | null = null;
    @Input() perPage: PerPageModel | null = null;
    @Input() pager: PagerModel | null = null;
    @Input() settings: PagerOptionsItems;

    @Output() change = new EventEmitter<SearchModel>();

    paginator = [];

    constructor() {}

    ngOnInit(): void {
        this.setPaginator();
    }

    ngOnChanges(changes: SimpleChanges): void {
        const { pager = false } = changes;
        if (pager && !pager.isFirstChange()) {
            this.setPaginator();
        }
    }

    /**
     * Creates the paginator data based on info about current and total pages
     */
    setPaginator() {
        const paginator = [];
        const pagShortedLimit = 3;
        let pagShorted = false;
        /* Ensure that the current page is treated as a Number*/
        this.pager.currentPage = Number(this.pager.currentPage);

        if (this.pager.lastPage >= pagShortedLimit) {
            pagShorted = true;
        }

        if (this.pager.currentPage > 2 && pagShorted) {
            paginator.push({ value: 1, active: false, icon: faStepBackward });
            paginator.push({ value: this.pager.currentPage - 1, active: false, icon: faCaretLeft });
        }
        for (let i = 1; i <= this.pager.lastPage; i++) {
            if (pagShorted) {
                if (i < (this.pager.currentPage + pagShortedLimit) && i > (this.pager.currentPage - pagShortedLimit)) {
                    paginator.push({ value: i, active: i === this.pager.currentPage, icon: null });
                }
            } else {
                paginator.push({ value: i, active: i === this.pager.currentPage, icon: null });
            }
        }
        if (this.pager.currentPage < this.pager.lastPage - 2 && pagShorted) {
            paginator.push({ value: this.pager.currentPage + 1, active: false, icon: faCaretRight });
            paginator.push({ value: this.pager.lastPage, active: false, icon: faStepForward });
        }
        this.paginator = paginator;
    }

    get hasTotal() {
        return !isNil(this.settings) && hasIn('total', this.settings) ? this.settings.total : false;
    }

    get hasPager() {
        return !isNil(this.settings) && hasIn('pager', this.settings) ? this.settings.pager : false;
    }

    get hasLimit() {
        return !isNil(this.settings) && hasIn('limit', this.settings) ? this.settings.limit : false;
    }

    updateLimit(value: number) {
        const params = new SearchModel();
        params.limit = value;
        params.reload = true;
        this.change.emit(params.only('limit', 'page', 'reload'));
    }

    updatePage(value: number) {
        const params = new SearchModel();
        params.limit = this.perPage.value;
        params.page = value;
        params.reload = true;
        this.change.emit(params.only('limit', 'page', 'reload'));
    }

    reset() {
        const params = new SearchModel();
        params.reload = true;
        this.change.emit(params.only('limit', 'page', 'reload'));
    }
}
