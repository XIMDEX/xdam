import { FormI } from './FormI.interface';
import { ListOptionsI } from './ListOptions.interface';

import { ItemModel } from './ItemModel.interface';
import { SearchOptionsI } from './SearchModel.interface';
import { PagerOptions } from './PagerOptions.interface';

export interface Settings {
    token?: string;
    base_url: string;
    endpoints: { SettingsEdpoint };
    item_model?: ItemModel;
    base_params?: any;
}

export interface SettingsEndpoint {
    get?: string;
    post?: string;
    delete?: string;
}

export interface XDamSettingsInterface {
    search?: SearchOptionsI;
    pager?: PagerOptions;
    list?: ListOptionsI;
    facets?: boolean;
    form?: FormI;
}
