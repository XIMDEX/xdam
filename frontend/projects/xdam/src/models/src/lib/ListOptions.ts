import { ListItemOptionI, ListOptionsI } from './interfaces/ListOptions.interface';

import BaseModel from './Base';
import { ItemModel } from './interfaces/ItemModel.interface';
import { standard } from './profiles/standard';

export class ListOptions extends BaseModel implements ListOptionsI {
    private _items: ListItemOptionI = standard.list.items;
    private _model: ItemModel = null;

    public constructor(options: ListOptionsI) {
        super();
        this.update(options);
    }

    set items(items: ListItemOptionI) {
        this._items = items;
    }
    get items(): ListItemOptionI {
        return this._items;
    }

    set model(model: ItemModel) {
        this._model = model;
    }
    get model(): ItemModel {
        return this._model;
    }
}
