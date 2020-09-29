import { XDamSettingsInterface } from '../interfaces/Settings.interface';

export const standard: XDamSettingsInterface = {
    facets: true,
    search: {
        input: {
            search: true,
            reset: true,
            clear: true
        },
        actions: {
            newAsset: true
        }
    },
    pager: {
        top: {
            total: true,
            pager: true,
            limit: true
        },
        bottom: {
            pager: true
        }
    },
    list: {
        model: {
            id: 'id',
            title: 'title',
            hash: 'hash',
            size: 'size',
            type: 'type',
            image: 'image',
            context: 'context'
        },
        items: {
            type: '%s',
            title: '%s',
            placeholder: {
                image: 'https://via.placeholder.com/200/7ec9b8/ffffff?text=Image',
                audio: 'https://via.placeholder.com/200/ef680e/ffffff?text=Audio',
                video: 'https://via.placeholder.com/200/af8282/ffffff?text=Video',
                pdf: 'https://via.placeholder.com/200/5273a8/ffffff?text=pdf',
                default: 'https://via.placeholder.com/200/5ab1c9/ffffff?text=Other'
            },
            actions: {
                edit: true,
                download: true,
                delete: true,
                select: false
            }
        }
    }
};
