import EndPointMapper from 'src/app/mappers/EndPointMapper';
import { XDamSettingsInterface } from '../interfaces/Settings.interface';
const router = new EndPointMapper();
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
            active: 'active',
            data: 'data',
            files: 'files',
            id: 'id',
            name: 'name',
            score: 'score',
            type: 'type',
            version: '_version_'
        },
        items: {
            type: '%s',
            title: '%s',
            placeholder: {
                image: 'https://via.placeholder.com/200/7ec9b8/ffffff?text=Image',
                audio: 'https://via.placeholder.com/200/ef680e/ffffff?text=Audio',
                video: 'https://via.placeholder.com/200/af8282/ffffff?text=Video',
                pdf: 'https://via.placeholder.com/200/5273a8/ffffff?text=pdf',
                default: 'https://via.placeholder.com/200/5ab1c9/ffffff?text=Other',
                course: 'https://via.placeholder.com/200/8c4966/ffffff?text=Course'
            },
            actions: {
                edit: true,
                download: true,
                delete: true,
                select: false
            },
            urlResource: router.baseUrl + router.api
        }
    }
};
