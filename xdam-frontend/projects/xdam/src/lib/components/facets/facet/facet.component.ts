import { Component, EventEmitter, Input, OnChanges, OnInit, Output, SimpleChanges } from '@angular/core';
import { faMinus, faPlus } from '@fortawesome/free-solid-svg-icons';
import { hasIn, is, isNil } from 'ramda';

import { FacetModel } from '../../../../models/src/lib/FacetModel';

@Component({
    selector: 'xdam-facet',
    templateUrl: './facet.component.html',
    styleUrls: ['./facet.component.scss']
})
export class FacetComponent implements OnInit, OnChanges {
    @Input() data: FacetModel;
    @Input() value: any[];
    @Input() open: boolean = false;
    @Input() withDefault: boolean = true;

    @Output() onChange = new EventEmitter<any>();

    public isOpen: boolean = false;
    public filter = "";
    private selectedValues = [];

    constructor() {}

    ngOnInit(): void {
        this.prepareValues();
    }

    ngOnChanges(changes: SimpleChanges): void {
        if (hasIn('open', changes)) {
            this.isOpen = this.open;
        }
    }

    get titleIcon() {
        return !this.isOpen ? faPlus : faMinus;
    }

    get label(): string {
        return this.data.label;
    }

    get key(): string {
        return this.data.key;
    }

    get default(): string | null {
        return this.data.default;
    }

    get values(): any {
        return this.data.values;
    }

    public isSelected(value: string) {
        return this.selectedValues.indexOf(value) !== -1;
    }

    public toggle() {
        this.isOpen = !this.isOpen;
        this.selectFacet(null, false);
    }

    public reset() {
        this.prepareValues(true);
        const data = {};
        data[this.key] = this.selectedValues;

        this.onChange.emit({ data, isOpen: this.isOpen, emit: true });
    }

    public selectFacet(value: string | null, emit: boolean = true) {
        if (!isNil(value)) {
            const index = this.selectedValues.indexOf(value);

            if (index === -1) {
                this.selectedValues.push(value);
            } else {
                this.selectedValues.splice(index, 1);
            }
        }

        const data = {};
        data[this.key] = this.selectedValues;

        this.onChange.emit({ data, isOpen: this.isOpen, emit });
    }

    protected prepareValues(reset: boolean = false) {
        this.selectedValues = is(Array, this.value) ? this.value : [];
        if ((this.withDefault || reset) && !isNil(this.data.default)) {
            this.selectedValues.push(this.data.default);
            this.isOpen = true;
            this.selectFacet(null, false);
        } else if (reset) {
            this.selectedValues = [];
        }
    }
}
