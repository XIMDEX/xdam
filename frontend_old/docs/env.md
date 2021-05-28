<ol class="breadcrumb">
    <li>Environment</li>
</ol>

This configuration json is a essential part of the XDAM operation and manages internal mappings and variable values needed for a correct setup for the application. 

Here you can see an example of a environment json that connects to a api located in a example **example.net** host:

```javascript
window.$xdam = {
        token: 'YOUR_LOGIN_TOKEN',
        base_url: 'http://example.net/api',
        base_params: {
        },
        profile: 'standard',
        endpoints: {
          resources: {
            list: 'resources',
            get: 'resources',
            post: 'resources',
            delete: 'resources'
          }
        },
        models: {
          item: {
            id: 'resource_id',
            title: 'name',
            hash: 'id',
            size: 'file_size',
            type: 'type',
            image: 'preview'
          },
          requests: {
            get: 'hash',
            delete: 'id',
            put: 'id'
          }
        },
        forms: {
          name: 'resource-form',
          endpoint: 'resources',
          api: false,
          fields: [
            {
              type: 'dropdown',
              object: {
                key: 'shop_id',
                multi: true,
                searchable: true,
                fetchable: true,
                endpoint: 'shops?limit=0',
                label: 'Choose a shop',
                options: [],
                order: 1,
                realName: 'shops',
                map: {
                  key: 'id',
                  value: 'name'
                }
              }
            },
            {
              type: 'dropdown',
              object: {
                key: 'category',
                label: 'Select a category',
                options: [
                  {
                    key: '1',
                    value: 'Hardware'
                  },
                  {
                    key: '2',
                    value: 'Software'
                  }
                ],
                order: 2
              }
            },
            {
              type: 'depdrop',
              object: {
                realName: 'items',
                key: 'item_id',
                multi: false,
                searchable: true,
                fetchable: true,
                endpoint: 'items?limit=0',
                label: 'Select a item',
                ref: 'category',
                options: [],
                order: 3,
                map: {
                  key: 'id',
                  value: 'title'
                }
              }
            },
            {
              type: "text",
              object: {
                realName: 'buy_desc',
                key: 'buy_desc',
                label: 'Description',
                order: 4
              }
            },
            {
              type: "text-area",
              object: {
                realName: 'buy_desc',
                key: 'buy_desc',
                label: 'Description',
                order: 5
              }
            }
          ]
        }
      }
```

key and realname are the asset key where data is stored

## General parameters

```javascript
token: 'YOUR_LOGIN_TOKEN',
base_url: 'http://example.net/api',
base_params: {},
profile: 'standard'
```

These are the general configuration for the application:

* **token**: The auth token used in requests to the API.
* **base_url**: The URL where the API is located.
* **base_params**: A set of basic request parameters in a key-value format that will be always sent in every request the app makes.
* **profile**: The current selected profile.

## Endpoints parameters

```javascript
endpoints: {
    resources: {
        list: 'resources',
        get: 'resources',
        post: 'resources',
        delete: 'resources'
    }
}
```

The endpoints set for every method of the application, in that case the app will request **http://example.net/api/resources** for all methods.

## Models parameters

```javascript
models: {
    item: {
        id: 'resource_id',
        title: 'name',
        hash: 'id',
        size: 'file_size',
        type: 'type',
        image: 'preview'
    },
    requests: {
        get: 'hash',
        delete: 'id',
        put: 'id'
	}
}
```

These configuration options refer to models mapping between back-end and front-end models. Currently the configuration only allows to modify one model and the requests mapping:

* **item**: Maps every property of the model used in table items to their respective json response. So every key is a front model property mapped to its value that is the key of the json response.
* **requests**: Define the main model field used for every request as the identifier.

## Forms configuration

```javascript
forms: {
    name: 'resource-form',
    endpoint: 'resources',
    api: false,
    fields: []
}
```

This section is used for the application for the creation of an aditional form in the new asset modal with different input types. That way, the form can send and receive any info needed for edit or adding resources.

Furthermore for every field we want to include we have to create a schema to map the needed features required in every field. 

### Fields

A field is a json object that is later mapped to a form field in the application.

#### Attributes

| Field name |            Allowed values            |                         Description                          |
| :--------: | :----------------------------------: | :----------------------------------------------------------: |
|    type    | *text, text-area, dropdown, depdrop* |                    Define the input type                     |
|  realName  |                String                |  The name of the asset field where the field data is stored  |
|    key     |                String                | Used as a identifier for the field and the name of the asset field if no realName is provided |
|   label    |                String                |               A placeable label for the field                |
|   order    |                 Int                  |              The appeareance order of the field              |
|    ref     |                String                | **(Depdrop only)** The key of the dropdown or depdrop this depdrop depends on |
|  options   |                Array                 | **(Dropdown and Deprop only)** An array of objects {key: "a key", value: "a label"} used to display options in selectors. Empty if field is fetchable. |
|   multi    |               Boolean                | **(Dropdown and Deprop only)** True if selector allows more than one option selected, false otherwise |
| fetchable  |               Boolean                | **(Dropdown and Deprop only)** True if data has to be fetch from an endpoint, false otherwise |
|  endpoint  |                String                | **(Dropdown and Deprop only)** The endpoint from where the options data should be fetched |
| searchable |               Boolean                | **(Dropdown and Deprop only)** True if the field should allow search, false otherwise |
|    map     |                Object                | **(Dropdown and Deprop only)** A mapping with key,value to indicate what pair of data from the fetch response is used to construct the options (Only used when fetchable) |

</br></br>