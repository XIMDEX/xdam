import { isNil, hasIn } from 'ramda';
import { PerPageModel, PerPageModelSchema } from './interfaces/PagerModel.interface';

export class PerPage implements PerPageModel {
    private current: number = 20;
    private values_list: number[] = [10, 20, 50, 100];

    constructor(value: { any } = null, schema: PerPageModelSchema) {
        if (!isNil(value)) {
            for (const method of Object.keys(schema)) {
                if (hasIn(method, this) && !isNil(schema[method])) {
                    this[method] = value[schema[method]];
                }
            }
        }
    }

    set value(value: number) {
        this.current = value;
    }
    get value(): number | null {
        return this.current;
    }

    set items(items: number[]) {
        this.values_list = items;
    }
    get items(): number[] {
        return this.values_list;
    }
}
