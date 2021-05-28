export const lomesParser = (lomesCore) => {
    //RETURN JSON SCHEMA FOR REACT-JSON-SCHEMA
    //ONLY WORKS THE DROPDOWN AND TEXT FIELDS TYPES
    let output = {
        "name": "lomes",
        "title": "LOMES",
        "api": false,
        "tabs": [
        ]
    };
    
    lomesCore.tabs.forEach(tab => {
        let tabParsed = {};
        let tabProperties = {};
        tabParsed['title'] = tab.title;
        tabParsed['key'] = tab.key.toString();
        tab.fields.forEach((field) => {
            tabProperties[field.object.label] = {};
            tabProperties[field.object.label].type = 'string';
            tabProperties[field.object.label].title = field.object.label;
            if(field.type === 'dropdown') {
                tabProperties[field.object.label].enum = [];
                field.object.options.forEach((obj) =>{
                    tabProperties[field.object.label].enum.push(obj.value)
                })
            }
        })
        tabParsed['properties'] = tabProperties;
        output.tabs.push(tabParsed)
    });

    return output;
  }