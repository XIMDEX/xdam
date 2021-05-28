import React, { useState } from "react";
import { Card, CardContent, Typography, Grid, IconButton } from "@material-ui/core";
import { Icon, Button, Divider, Label } from "semantic-ui-react";


const InputText = ({data, array, popIndex, indexToPop}) => {
  
  return (<>
    <div className='forms-textField'>
      {/* <label htmlFor={data.props.idSchema.$id}>{data.props.formData}</label> */}
          {
            array.includes(data.props.formData) ? (
              // <input className='forms-currentItems' id={data.props.idSchema.$id} disabled type='text' defaultValue={data.props.formData} />
              <Label className='forms-currentItems' size='large'>{data.props.formData}</Label>
            ) : (
              <input className='forms-onArrayAddItem' id={data.props.idSchema.$id} type='text' defaultValue={data.props.formData} onChange={(event) => data.props.onChange(event.target.value)}/>
            )
          }
    </div>
    <Button icon='close' size='mini' className={array.includes(data.props.formData) ? 'forms-btn-removeArrayItem' : 'forms-btn-removeArrayItem f-editing'} onClick={popIndex(indexToPop)} />
    </>
  )
}

function ArrayFieldTemplate(props) {
  const [data, setData] = useState(props.formData)
  return (

        <Card variant='outlined' className='forms-arrayField'>  
            <label className='forms-arrayLabel'>{props.title}</label>
            {props.canAdd && (
                <Button icon='plus' circular size='mini' color='teal' className='forms-btn-addArrayItem' onClick={props.onAddClick} />
            )}
            <div className='forms-arrayContainer'>
              {props.items && props.items.map(element => (
                <div key={element.key} className='forms-arrayItem'>
                  <InputText 
                    data={element.children} 
                    popIndex={element.onDropIndexClick} 
                    indexToPop={element.index} 
                    array={data}/>
                </div>
              ))}
            </div>
        </Card>
  );
}

export default ArrayFieldTemplate;