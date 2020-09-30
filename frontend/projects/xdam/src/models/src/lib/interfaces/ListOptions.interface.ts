import { ItemModel } from './ItemModel.interface';

export interface ListOptionsI {
    items?: ListItemOptionI;
    model?: ItemModel;
}

export interface ListItemOptionI {
    type?: string;
    title?: string;
    placeholder?: {};
    actions?: ListItemActionsI;
}

export interface ListItemActionsI {
    edit?: boolean;
    download?: boolean;
    delete?: boolean;
    select?: boolean;
}
