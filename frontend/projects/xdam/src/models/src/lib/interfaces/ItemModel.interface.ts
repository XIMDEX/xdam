import { FacetModel } from '../FacetModel';
import { PagerModel } from './PagerModel.interface';

export interface ItemModel {
    id: string;
    title: string;
    hash: string;
    size?: string;
    type: string;
    image: string;
    context: string;
}

export interface XDamData {
    data: ItemModel[] | any[];
    pager?: PagerModel;
    facets?: FacetModel[] | any[];
}
