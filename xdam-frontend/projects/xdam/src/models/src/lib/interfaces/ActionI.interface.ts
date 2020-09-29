import { Item } from '../Item';

export type ActionMethods = 'new' | 'edit' | 'show' | 'select';
export type ActionStatus = 'success' | 'fail' | 'pending';

export interface ActionI {
    method: ActionMethods;
    item?: Item;
    data: any;
    errors: any;
    status: ActionStatus;
}
