import React, { useEffect, useState } from 'react';
import Dialog from '@material-ui/core/Dialog';
import { 
  DialogTitle,
  DialogContent,
  DialogContentText,
  Grid 
} from '@material-ui/core';
import DynamicForm from './DynamicForm';
import { COURSE, MULTIMEDIA, IMAGE, VIDEO, AUDIO, BOOK, ACTIVITY, ASSESSMENT } from '../../../constants';
import { Typography } from '@material-ui/core';
import { createStyles, makeStyles, Theme } from '@material-ui/core/styles';
import { useSelector, useDispatch } from 'react-redux';
import { selectSchemas, reloadCatalogue, setResourcesLoading } from '../../../appSlice';
import { Button as Btn, Dropdown } from 'semantic-ui-react';
import ViewResource from './ViewResource';
import ResourcesActions from '../../../utils/ResourcesService';
import BatchDialog from './MassiveUpload/BatchDialog';

const useStyles = makeStyles((theme: Theme) =>
  createStyles({
    closeDialog: {
      position: 'absolute',
      top: 10,
      right: 10
    }
  }),
);

export default function Dialogs( { resourceType, action, dialogOpen = false, resourceData = null, setDialogOpen = null } ) {
  const classes = useStyles(); 
  const [open, setOpen] = React.useState(false);
  const schemas = useSelector(selectSchemas);
  const dispatch = useDispatch();

  const handleClickOpen = () => {
    switch (resourceType) {
      case COURSE:
          if(action === 'create') {
            ResourcesActions().getCourseActions().create()
          } else {
            setOpen(true)
          }
        break;
      default:
        setOpen(true)
        break;
    }
  };

  const handleClose = () => {
    //console.log(reload, action)
    let shouldReload = localStorage.getItem('reload_catalogue');
    if(shouldReload === '1') {
      dispatch(setResourcesLoading(true));
      console.log('reloading catalogue...');
      dispatch(reloadCatalogue());
      localStorage.setItem('reload_catalogue', '0');
    }
    if(setDialogOpen) {
      setDialogOpen(false)
    }
    setOpen(false);
    
  };

  useEffect(() => {
    console.log(action)
    if(dialogOpen) {
      handleClickOpen()
    }
  }, [dialogOpen])

  const HandleForms = () => {
    let form;
    switch (resourceType) {
      case COURSE:
        form = (<DynamicForm handleClose={handleClose} resourceType={resourceType} action={action} schema={schemas.course_validator} dataForUpdate={resourceData}  />);
        break;
      case MULTIMEDIA:
      case IMAGE:
      case VIDEO:
      case AUDIO: 
        form = (<DynamicForm handleClose={handleClose} resourceType={resourceType} action={action} schema={schemas.multimedia_validator} dataForUpdate={resourceData} />);
        break;
      case BOOK:
        form = (<DynamicForm handleClose={handleClose} resourceType={resourceType} action={action} schema={schemas.book_validator} dataForUpdate={resourceData} />);
        break;
      case ACTIVITY:
          form = (<DynamicForm handleClose={handleClose} resourceType={resourceType} action={action} schema={schemas.activity_validator} dataForUpdate={resourceData} />);
        break;
      case ASSESSMENT:
          form = (<DynamicForm handleClose={handleClose} resourceType={resourceType} action={action} schema={schemas.assessment_validator} dataForUpdate={resourceData} />);
        break;
      default:
        form = (<Typography>Corrupted resource: type of "{resourceType}" is not allowed</Typography>);
        console.error('The resource type "' + resourceType + '" is bad')
        break;
    }
    return (
      form
    )
  }

  const TheResource = () => {
    return (
      <DialogContent>
        <DialogContentText>
          {/* Describe here how to {action} a {resourceType} */}
          
        </DialogContentText>
        <Grid container spacing={2}>
          <Grid item sm={12}>
            <ViewResource resData={resourceData} />
          </Grid>
        </Grid>
      </DialogContent>
    )
  }

  return (
    <div id='dialogs_container'>
      <Dialog 
        open={open} 
        onClose={() => handleClose()} 
        fullWidth 
        maxWidth={'md'}  
        aria-labelledby="form-dialog-title"
      >
        <DialogTitle id="form-dialog-title" style={{textTransform: 'capitalize'}}>{resourceData?.name}</DialogTitle>
        {schemas ? ( action !== 'view' ? <HandleForms /> :  <TheResource /> ) : 'Loading Schema'}
      </Dialog>
    </div>
  );
}

