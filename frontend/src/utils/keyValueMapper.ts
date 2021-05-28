export const getDataFromKey = (data, facet_mapping: {} = null) => {
    //get values from facet[key] and replace or add the keys/value on data.description
    facet_mapping = {
      'categories': 'category',
      'tags': 'skills' 
    };
    //the object mapper:
    //key = prop to update/add on data.description
    //value = key with data in data.description


    let facets_value = {
      ...data
    };
    let newData = {
      ...facets_value.data
    }

    let fv = JSON.parse(JSON.stringify(facets_value));
    let nd = JSON.parse(JSON.stringify(newData));

    Object.keys(facet_mapping).map((e, i) => {
        nd.description[e] = fv[e];
    })

    fv.data = nd;
    
    return fv;
  }