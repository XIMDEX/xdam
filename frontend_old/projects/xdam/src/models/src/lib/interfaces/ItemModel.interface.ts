import { FacetModel } from '../FacetModel';
import { PagerModel } from './PagerModel.interface';

export interface ItemModel {
    active: string;
    data: string;
    files: string;
    id: string;
    name: string;
    score: string;
    type: string;
    version: string;
    previews?: string;
}

export interface XDamData {
    data: ItemModel[] | any[];
    pager?: PagerModel;
    facets?: FacetModel[] | any[];
}
