import React, { useEffect } from 'react';
import { 
  Button,
  Grid
} from '@material-ui/core';


const CustomFieldTemplate = (props) =>
{
  const {id, classNames, label, help, required, description, errors, children} = props;
  return (
    <div>
      {/* <label htmlFor={id} style={{display: 'block', textTransform: 'capitalize'}}>{label}{required ? "*" : null}</label> */}
      {/* {description} */}
      {children}
      {errors}
      {help}
    </div>
  );
}

export default CustomFieldTemplate;