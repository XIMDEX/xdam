<ol class="breadcrumb">
    <li>Profiles</li>
</ol>

A profile is a Javascript constant with a json structure in a .js file for the purpose of slightly modifying the behavior and appearance of XDAM when used by the user.

The structure of the **standard** profile applied by default is as follows:

```javascript
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
```

As we can see, the file is divided into several subsections.

## General configuration

This section is reserved for configurations that affects the general user experience of the app. Currently it only has one section for translations (That is also a work in progress).

## Component configuration

This subsection refers to behaviour and appearence of every component involved in the view.

### Main

```javascript
 main: {
     query: {
         page: {name: 'page'},
         limit: {name: 'limit', value: 20},
         search: {name: 'name', value: '$'}
     }
 }
```

In this option we can find some options for querying data:

* **Page**: The name of the json variable that is used for querying for a page of a set of paginated data.
* **Limit**: It contains the name of the json variable used as a key to limit the collection of data that we will receive and the default value sent.
* **Search**: A simple map with the name of the variable for searchs in requests and a **$** in value, that denotes every value set in the search bar is sent without any modifications (A value of **$ long** will add a "long" word to every search query).

### Table

```javascript
table: {
    paginator: {
        top: true,
        bottom: true,
        limits: [10, 20, 50, 100]
    }
}
```

Here we can find position options for the paginator (True if the paginator should show at top or bottom) as well as an array with the limits the limit selector will show as options.

### Facets

```javascript
 facets : {
 	active: true
 }
```

Right now only allows the option to enable/disable the component.

### Table Item

```json
tableItem: {
    fields: {
        header: '$',
        Imgplace: {

    	},
        title: '$',
        subtitle: 'Size: $'
    },
    actions: {
        edit: true,
        download: true,
        delete: true
	}
}
```

This section configures the table item component and its options, here we can find various subsections:

* **Fields**: A mapper for fields names like header, title or subtitle names (As the search in the main section **$** means the original value). ImgPlace is a WIP for custom placeholders.
* **Actions**: This subsections allows us to enable or disable contextual options for every item.

### Table Search

```javascript
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
```

In this subsection we can currently enable or disable actions buttons in the search bar. </br></br>