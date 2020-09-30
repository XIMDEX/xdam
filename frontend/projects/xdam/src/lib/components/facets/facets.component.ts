import {
    ChangeDetectorRef,
    Component,
    EventEmitter,
    HostBinding,
    Input,
    OnChanges,
    Output,
    SimpleChanges,
    ViewChildren
} from '@angular/core';
import { faChevronLeft, faChevronRight } from '@fortawesome/free-solid-svg-icons';
import { hasIn, isNil } from 'ramda';

import { FacetComponent } from './facet/facet.component';
import { SearchModel } from '../../../models/src/lib/SearchModel';

@Component({
    selector: 'xdam-facets',
    templateUrl: './facets.component.html',
    styleUrls: ['./facets.component.scss']
})
export class FacetsComponent implements OnChanges {
    @Input() data;

    @Output() onChange = new EventEmitter<SearchModel>();

    @ViewChildren('facetComponent') facetComponent: FacetComponent[];

    @HostBinding('class.open_facets')
    isOpen = false;

    public facets = {};
    public first = true;
    protected openFacets = [];

    constructor(private cdr: ChangeDetectorRef) {}

    ngOnChanges(changes: SimpleChanges): void {
        if (hasIn('data', changes)) {
            let start = changes.data.isFirstChange();
            if (!isNil(changes.data.previousValue)) {
                start = (changes.data.previousValue as any[]).length === 0;
            }
            this.first = start;
        }
    }

    get openIcon() {
        return this.isOpen ? faChevronLeft : faChevronRight;
    }

    isOpenFacet(key: string): boolean {
        const result = this.openFacets.indexOf(key) !== -1;
        return result;
    }

    reset() {
        this.first = true;
        this.facetComponent.forEach(element => {
            element.reset();
        });
    }

    public setFacets(facet: any) {
        if (!isNil(facet.data)) {
            const keys = Object.keys(facet.data);
            keys.forEach(key => {
                const value = facet.data[key] as [];
                this.toggleFacet(key, value, facet.isOpen);
            });
        }

        const params = new SearchModel();
        params.facets = this.facets;
        params.reload = facet.emit;
        this.cdr.detectChanges();
        this.onChange.emit(params.only('facets', 'page', 'reload'));
    }

    protected toggleFacet(key: string, value: Array<string>, isOpen: boolean) {
        if (value.length > 0) {
            const data = {};
            data[key] = value;
            this.facets = { ...this.facets, ...data };
        } else {
            delete this.facets[key];
        }

        const i = this.openFacets.indexOf(key);
        if (isOpen && i === -1) {
            this.openFacets.push(key);
        } else if (!isOpen && i !== -1) {
            this.openFacets.splice(i, 1);
        }
    }
}
