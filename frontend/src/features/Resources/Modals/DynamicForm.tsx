import React, { useEffect, memo, useState } from 'react';
import {
  Button,
  DialogContent,
  DialogContentText,
  ButtonGroup,
  Grid,
  Card
} from '@material-ui/core';
import { createStyles, makeStyles, Theme } from '@material-ui/core/styles';
import MainService from '../../../api/service';
import { MULTIMEDIA } from '../../../constants';
import { useSelector, useDispatch } from 'react-redux';
import { selectCollection } from '../../../slices/organizationSlice';
import SemanticForm from "@rjsf/semantic-ui";
import { JSONSchema7 } from 'json-schema';
import { render } from '../../../utils/render';
import Lomes from '../LOMES/Lomes';
import { Tab, Label, Icon, Dropdown, Radio } from 'semantic-ui-react'
import { Button as Btn } from 'semantic-ui-react';
import { Message } from 'semantic-ui-react';
import RelatedFiles from './RelatedFiles';
import { setFormData, selectFormData, reloadCatalogue, setLomesSchema } from '../../../appSlice';
import store from '../../../app/store';
import ArrayFieldTemplate from './DynamicFormTemplates/ArrayFieldTemplate';
import ResourceActionButtons from './ResourceActionButtons';
import { iconHandler } from '../../../utils/iconHandler';
import { InputText, InputTextArea, CustomToggle, CustomInputText, CustomDropdown } from './DynamicFormTemplates/CustomFields';

const useStyles = makeStyles((theme: Theme) =>
  createStyles({
    formControl: {
      margin: theme.spacing(1),
      minWidth: 120,
      width: '100%'
    },
    selectEmpty: {
      marginTop: theme.spacing(2),
    },
    btnPreview: {
      height: 300,
    },
    blur: {
      filter: "blur(2px)",
    },
    addPreview: {
      display: 'none',
      position: 'absolute',
      zIndex: 1,
      left: '7%',
      top: 190,
      userSelect: 'none',
      pointerEvents: 'none'
    },
    dblock: {
      display: 'block'
    },
    formTag: {
      minHeight: 300
    },
    btnGroup: {
      width: '80%',
    },
    divider: {
      padding: '10px 0'
    },
    dismiss: {
      opacity: 1,
      transition: 'opacity 2s ease-'
    },
    imgView: {
      width: '100%'
    }
  }),
);

interface IBody {
  [key: string]: any;
  type: string,
  data: string,
  collection_id: string
}

export default function DynamicForm({ resourceType, action, schema, dataForUpdate = null, handleClose }) {
  const classes = useStyles();
  let collection_id = useSelector(selectCollection);
  //let storeFormData = useSelector(selectFormData);
  const dispatch = useDispatch();
  const [previewImage, setPreviewImage] = useState(null);
  const [formFiles, setFormFiles] = useState([]);
  const [processing, setProcessing] = useState(null);
  const [msg, setMessage] = useState(false);
  const _refForm = React.useRef(null);
  const [mediaType, setMediaType] = useState(dataForUpdate?.type ?? null);
  const [resourceData, setResourceData] = useState(null);
  const [theFiles, setTheFiles] = useState(null);
  const [loaded, setLoaded] = useState(false);
  const [tr, triggerReload] = useState(false);
  const [fillAlert, setFillAlert] = useState(false);
  
  
  useEffect(() => {
    
    if(action === 'create') {
      if(getStoreFormData() !== {}) {
        dispatch(setFormData({}));
        triggerReload(!tr)
      }
    } else if (action === 'edit') {
      
      const fetchLomesSchema = async () => { 
        let lomesSchema = await MainService().getLomesSchema();
        dispatch(setLomesSchema(lomesSchema));
      }
      let ll = localStorage.getItem('lomes_loaded');
      if(ll === null || ll === '0') {
        fetchLomesSchema()
        localStorage.setItem('lomes_loaded', '1');
      }
      
      const getResourceData = async () => {
        //get the resource from db. Data for update is faceted data
        let res = await MainService().getResource(dataForUpdate.id);
        setResourceData(res);
        setTheFiles(res.files);
      }
      if(!loaded) {
        getResourceData();
        dispatch(setFormData(dataForUpdate.data));
        setFillAlert(false);
        setLoaded(true);
      }
    } else if (action === 'view') { 

    } else {

    }
    return function cleanup() {
      //dispatch(setFormData({}));
    };
  }, [theFiles, resourceData, loaded ])
  
  const styleBtnPreview = {
    backgroundImage: 'url(' + (previewImage ? URL.createObjectURL(previewImage) : (dataForUpdate ? render(dataForUpdate) : 'noimg.png')) + ')',
    backgroundPosition: 'center',
    backgroundSize: 'cover',
    backgroundRepeat: 'no-repeat',
  }

  const handleFiles = (e) => {
    if (typeof e.target.type === 'string' && e.target.type === 'file' && e.target.name === 'Preview') {
      setPreviewImage(e.target.files[0]);
      if(formFiles.length === 0 && dataForUpdate?.files.length === 0) {
        setMediaType(e.target.files[0].type.split('/')[0])
      }
    }
    if (typeof e.target.type === 'string' && e.target.type === 'file' && e.target.name === 'File') {
      // console.log('file attached', e.target.files)
      setFormFiles(e.target.files)
      if(resourceType === MULTIMEDIA) {
        // console.log(e.target.files[0].type.split('/')[0])
        setMediaType(e.target.files[0].type.split('/')[0])
      }
    }
  
  }

  function setType() {
    
    if(resourceType !== MULTIMEDIA) {
      return resourceType;
    }

    if(formFiles.length <= 0) {
      if(resourceData?.files.length <= 0) {
        return 'image';
      } else {
        return resourceData?.files[0].mime_type.split('/')[0] ?? 'image';
      }
    } else {
      return formFiles[0].type.split('/')[0];
    }
  }

  const postData = async (form, event) => {
    localStorage.setItem('reload_catalogue', '1');
    setMessage(false)

    const data = form.formData
    /*
    IMPORTANTE: DEFINE MEDIA TYPE ON MULTIMEDIA COLLECTION.
    En la version acutal, el tipo de multimedia se define con esta logica:
      si el user sube SOLO el preview, o SOLO crea el recurso sin preview, ni files: el recurso sera tratado como 'image'
      si el user sube 1 File (o mas), el recurso sera tratado como el mime_type del 1er file cargado en la lista de Files
    */
    let body: IBody = {
      type: setType(),
      data: JSON.stringify(data),
      collection_id: collection_id.toString()
    }

    let theFormData = new FormData();
    Object.keys(body).map((e) => {
      theFormData.append(e, body[e]);
    })

    if (formFiles) {
      for (var i = 0; i < formFiles.length; i++) {
        theFormData.append('File[]', formFiles[i]);
      }
    }

    if (previewImage) {
      theFormData.append('Preview', previewImage);
    }

    let res;
    setProcessing(true)
    if (dataForUpdate) {
      res = await MainService().updateResource(dataForUpdate.id, theFormData);
    } else {
      res = await MainService().createResource(theFormData);
    }
    //FormFiles are the files 'adding'
    if(action !== 'create') {
      setFormFiles([])
    }
    setLoaded(false)
    setProcessing(false)
    console.log(res)
    setForm(res.data);
    setMessage(true)
    event.preventDefault();
    return false; // prevent reload
  }

  const getStoreFormData = () => {
    const fd = store.getState().app.formData;
    return fd;
  }

  const metaData = { menuItem: 'Main Data', render: () => <Tab.Pane > <MainData /></Tab.Pane> };
  const lomesData = { menuItem: 'LOMES', render: () => <Tab.Pane > <LomesForm /> </Tab.Pane> };

  const pane = [metaData];
  const panes = [metaData, lomesData];

  const setForm = (data) => {
    dispatch(setFormData(data))
  }
  
  const customWidgets = {
    TextWidget: InputText,
  };
  const uiSchema={
    
    "description": {
      "ui:order": ["active", "*"],
      "active": {
        "ui:widget": CustomToggle,
      },
      "course_source": {
        "ui:widget": CustomDropdown,
        "ui:options":{
          label: 'Course source'
        }
      },
      "partials": {
        "pages": {
          "ui:widget": "hidden",
        }
      },
      "description": {
        "ui:widget": InputTextArea,
        "ui:options": {
          "rows": 5
        }
      },
      "name": {
        "ui:widget": CustomInputText,        
      },
      "external_url": {
        "ui:widget": CustomInputText,
        "ui:options":{
          "title": 'External url'
        }
      },
    }
  }

  const updateResourceFromLastCreated = async () => {
    let lastUpdated = await MainService().getLastResource(collection_id, 'lastCreated');
    setForm(lastUpdated.data);
    setFillAlert(true);
    triggerReload(!tr);
    // let res = await MainService().updateResourceFromLastCreated(resourceData.id);
    // setResourceData(res);
  }

  const updateResourceFromLastUpdated = async () => {
    let lastUpdated = await MainService().getLastResource(collection_id, 'lastUpdated');
    setForm(lastUpdated.data);
    setFillAlert(true);
    triggerReload(!tr);
    // let res = await MainService().updateResourceFromLastUpdated(resourceData.id);
    // setForm(res.data);
    // setResourceData(res);
  }

  const MetaDataForm = () => {
    return (
      <Grid item sm={6}>
        <div className='forms-main-btns'>
            {/* <Btn onClick={dispatchForm} loading={processing}>Update</Btn> */}
            
            <Btn color='teal' icon='facebook' onClick={() =>  _refForm.current.click()} loading={processing}> 
              {dataForUpdate ? (
                <>
                  <Icon name='save' /> Save
                </>
              ) : 
                <>
                  <Icon name='save' /> Submit
                </>
              }
            </Btn>
            
              <Dropdown
                  text='Import data'
                  icon='clone'
                  color='teal'
                  labeled
                  button
                  className='icon teal'
              >
                <Dropdown.Menu>
                  <Dropdown.Item onClick={updateResourceFromLastCreated}>
                    last resource created
                  </Dropdown.Item>
                  <Dropdown.Item onClick={updateResourceFromLastUpdated}>
                    last resource updated
                  </Dropdown.Item>
                </Dropdown.Menu>
              </Dropdown>
            
        </div>
        <div className='form-messages'>    
          <Message className={msg ? 'zoom-message' : 'hidden-message'} info onDismiss={() => setMessage(false)}>
              <Message.Header>Done</Message.Header>
              <p>Resource {dataForUpdate ? 'updated' : 'created'}</p>
          </Message>
        </div>
        <SemanticForm
          id='sfu'
          className={fillAlert ? 'fill-alert' : ''}
          uiSchema={uiSchema}
          schema={schema as JSONSchema7}
          onSubmit={postData}
          formData={getStoreFormData()} 
          onChange={(fd)=> setForm(fd.formData)}
          ArrayFieldTemplate={ArrayFieldTemplate}
          widgets={customWidgets}
        >
          <button ref={_refForm} type="submit" style={{ display: "none" }} />
        </SemanticForm> 
      </Grid>
    )
  };

  const MainData = memo(() => {
    return (
      <Grid container style={{ height: '75vh' }}>
        <FilesAndActions />
        <MetaDataForm />
      </Grid>
    )
  });

  const LomesForm = () => {    
    return (
      <Grid container>
        <Grid item sm={12}>
          <Lomes resourceData={dataForUpdate} />
        </Grid>
      </Grid>
    )
  }

  const FilesAndActions = () => {
    return (
      <Grid item sm={6}>
          <ButtonGroup orientation='vertical'>
            <Grid item sm={12} style={{ minWidth: 400 }}>
              <span
                className={`${classes.addPreview}`}
              >Uplad preview image</span>
              <Button
                className={`${classes.btnPreview}`}
                component="label"
                style={styleBtnPreview}
                fullWidth
                variant='outlined'
              >
                <input
                  accept="image/*"
                  type="file"
                  onChange={(e)=> handleFiles(e)}
                  name='Preview'
                  hidden
                />
              </Button>
            </Grid>

            <Grid item sm={12} className={classes.divider}>
              {dataForUpdate ? (
                <ButtonGroup orientation='horizontal' fullWidth id='forms-btn-actions'>    
                    <ResourceActionButtons resource={dataForUpdate} />
                </ButtonGroup>
              ) : null}
            </Grid>

            <Grid item sm={12}>
              <Button
                variant="outlined"
                component="label"
                fullWidth
              >
                Attach files
                <input
                  type="file"
                  multiple
                  accept={resourceType === MULTIMEDIA ? "audio/*,video/*,image/*" : '*'}
                  onChange={(e)=> handleFiles(e)}
                  name='File'
                  hidden
                />
              </Button>
              {/* {resourceType === MULTIMEDIA ? (
                  <Label> You will upload a {mediaType}</Label>
              ) : null} */}
            </Grid>
          </ButtonGroup>
          <div style={{ margin: '15px 42px 0px 0px' }}>
            {
              action === 'view' || action === 'edit' ? (
                <RelatedFiles
                  resData={resourceData}
                  files={theFiles}
                  withPlayer
                  onEditModal
                  setTheFiles={setTheFiles}
                  DynamicFormResourceData={setResourceData}
                />
              ) : null
            }
            
            {
              formFiles && formFiles.length > 0 ? (
                <>
                  <Label>Adding:</Label>
                  {
                    Array.from(formFiles).map((f, i) => (
                      <Card variant='outlined' className='associated-files-card' key={i}>
                        <Icon name={iconHandler(f)}></Icon>
                        <p><strong>File name:</strong> {f.name}</p>
                        <p><strong>Mime type:</strong> {f.type}</p>
                        <p><strong>Size:</strong> {(f.size / 1000000).toFixed(2)} MB</p>
                      </Card>
                    ))
                  }
                </>
              ) : null
            }

          </div>
        </Grid>
    )
  }

  return (
    <DialogContent className='edit-create-dialog-content'>
      <DialogContentText>
        {/* Describe here how to {action} a {resourceType} */}
        <Btn color='teal' circular icon='close' onClick={() => handleClose()} className='read-card-close-button'/>
      </DialogContentText>
      <Grid container spacing={2}>
        <Grid item sm={12} id='form-content'>
          {
            dataForUpdate && action === 'edit' ? (<Tab panes={panes} />) : (<Tab panes={pane} />)
          }
        </Grid>
      </Grid>
    </DialogContent>
  );
}
