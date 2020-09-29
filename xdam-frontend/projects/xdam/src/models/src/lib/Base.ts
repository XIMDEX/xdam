import { hasIn, isNil } from 'ramda';

export default class BaseModel {
    get(setting: string): any {
        let value = null;
        if (hasIn(setting, this)) {
            value = this[setting];
        }
        return value;
    }

    only(...params: string[]): any {
        const props = Object.getOwnPropertyNames(this).map(key => {
            let method = key;
            if (key.startsWith('_')) {
                method = method.slice(1);
            }
            return method;
        });

        for (const key of props) {
            if (params.indexOf(key) === -1) {
                delete this[key];
                delete this[`_${key}`];
            }
        }
        return this;
    }

    update(params: any) {
        if (!isNil(params)) {
            Object.keys(params).forEach(key => {
                let method = key;
                if (key.startsWith('_')) {
                    method = method.slice(1);
                }

                if (hasIn(method, this)) {
                    try {
                        this[method] = params[key];
                    } catch (e) {}
                }
            });
        }
    }
}
