import { PerPage } from './PerPage';
import { isNil, hasIn, is } from 'ramda';
import { PagerModelSchema, PagerModel, PerPageModel, PerPageModelSchema } from './interfaces/PagerModel.interface';

export class Pager implements PagerModel {
    private total_items: number | null = null;
    private current_page = 0;
    private last_page = 0;
    private per_page: PerPageModel = {
        value: 20,
        items: [10, 20, 50, 100]
    };
    private next_page: number | null = null;
    private prev_page: number | null = null;

    private subSchema: PerPageModelSchema | any = null;

    constructor(pager: { any } = null, schema: PagerModelSchema) {
        if (!isNil(pager)) {
            for (const method of Object.keys(schema)) {
                if (is(Object, schema[method])) {
                    this.subSchema = schema[method];
                    this[method] = pager;
                } else if (hasIn(method, this) && !isNil(schema[method])) {
                    this[method] = pager[schema[method]];
                }
            }
        }
    }

    set total(total: number | null) {
        this.total_items = total;
    }
    get total(): number | null {
        return this.total_items;
    }

    set currentPage(currentPage: number) {
        this.current_page = currentPage;
    }
    get currentPage(): number {
        return this.current_page;
    }

    set lastPage(lastPage: number) {
        this.last_page = lastPage;
    }
    get lastPage(): number {
        return this.last_page;
    }

    set nextPage(nextPage: number | null) {
        this.next_page = nextPage;
    }
    get nextPage(): number | null {
        return this.next_page;
    }

    set prevPage(prevPage: number | null) {
        this.prev_page = prevPage;
    }
    get prevPage(): number | null {
        return this.prev_page;
    }

    set perPage(perPage: PerPageModel | null) {
        this.per_page = new PerPage(perPage as any, this.subSchema as PerPageModelSchema);
    }
    get perPage(): PerPageModel | null {
        return this.per_page;
    }
}
