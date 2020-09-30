/**
 * @ignore
 */
export const standard = {
    configs: {
        general: {
            language: 'en'
        },
        components: {
            main: {
                query: {
                    page: {name: 'page'},
                    limit: {name: 'limit', value: 20},
                    search: {name: 'name', value: '$'}
                }
            },
            table: {
                paginator: {
                    top: true,
                    bottom: true,
                    limits: [10, 20, 50, 100]
                }
            },
            facets : {
                active: true
            },
            tableItem: {
                fields: {
                    header: '$',
                    Imgplace: {
                        'image': 'https://via.placeholder.com/200/7ec9b8/ffffff?text=Image',
                        'audio': 'https://via.placeholder.com/200/ef680e/ffffff?text=Audio',
                        'video': 'https://via.placeholder.com/200/af8282/ffffff?text=Video',
                        'pdf': 'https://via.placeholder.com/200/5273a8/ffffff?text=pdf',
                        'other': 'https://via.placeholder.com/200/5ab1c9/ffffff?text=Other'
                    },
                    title: '$',
                    subtitle: 'Size: $'
                },
                actions: {
                    edit: true,
                    download: true,
                    delete: true
                }
            },
            tableSearch: {
                searchButtons: {
                    search: {
                        active: true
                    },
                    reset: {
                        active: true
                    },
                    clear: {
                        active: true
                    },
                    newAsset: {
                        active: true
                    }
                }
            }
        }
    }
};
