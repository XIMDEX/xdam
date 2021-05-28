import React, { useState, useEffect } from "react";
import { Button} from "@material-ui/core";
import { BOOK, COURSE } from "../../../constants";
import ResourcesActions from "../../../utils/ResourcesService";

function ResourceActionButtons( { resource } ) {
  const [actions, setAction] = useState({})
  
  useEffect(() => {  
    setAction(ResourcesActions().getActions(resource));
  }, [])
  return (
    <>
    {
      Object.keys(actions).map((action, i) => (
          action !== 'create' ? 
          <Button 
            key={i}
            color='primary' 
            component='a' 
            variant='outlined' 
            href={actions[action].href} 
            target='_blank'
          >
            {actions[action].label} 
          </Button>
            : 
          null
        
      ))
    }
    </>
  );
}

export default ResourceActionButtons;


