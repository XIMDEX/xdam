import React, { useEffect, useState, useRef } from 'react';
import Button from '@material-ui/core/Button';
import TextField from '@material-ui/core/TextField';
import Dialog from '@material-ui/core/Dialog';
import DialogActions from '@material-ui/core/DialogActions';
import DialogContent from '@material-ui/core/DialogContent';
import DialogContentText from '@material-ui/core/DialogContentText';
import DialogTitle from '@material-ui/core/DialogTitle';
import { FileDrop } from 'react-file-drop';
import { Dropdown, Segment, Grid, Form, Button as Btn, Divider, Input, Message, Label, Icon, Progress } from 'semantic-ui-react';
import { useSelector, useDispatch } from 'react-redux';
import { selectUser, reloadCatalogue } from '../../../../appSlice';
import api from '../../../../api/urlMapper';
import MainService from '../../../../api/service';
import { selectCollection } from '../../../../slices/organizationSlice';
import axios from 'axios';


export default function BatchDialog( {open, setOpenBatch, action} ) {
    const [files, setFiles] = useState(null);
    const [workspace, setWorkspace] = useState(null);
    const [newWorkspace, setNewWorkspace] = useState('');
    const [focus, setFocus] = useState(null);
    const [message, setMessage] = useState(null);
    const [server, setServerConf] = useState(null);
    const [progress, setProgress] = useState(null);
    const [highlighted, highlightArea] = useState(false);
    const [uploaded, filesUploaded] = useState(false);
    const [errorOnUpload, setErrorOnUpload] = useState(null);

    const collection = useSelector(selectCollection);
    const dispatch = useDispatch();
    const fileInputRef = useRef(null);
    const workspaces = useSelector(selectUser).data.selected_org_data.workspaces;

    useEffect(() => {
        
        console.log('UPDATED')

        const get_post_max_size = async () => {
            let res = await axios.get(api().baseUrl + '/ini_pms', {
                headers: {
                    authorization: api().auth,
                }
            });
            console.log(res);
            if(res.status === 200) {

                setServerConf(res.data);
            }
        }
        
        get_post_max_size();
        
    }, [uploaded])

    const handleClose = () => {
        setFiles(null);
        setFocus(null);
        filesUploaded(false);
        setProgress(null);
        setErrorOnUpload(null);
        setOpenBatch(false);
    };

    const handleOnEntered = () => {
        setNewWorkspace(now());
    }

    const onFileInputChange = (event) => {
        const { files } = event.target;
        setFiles(Array.from(files));
    }

    const onTargetClick = () => {
        fileInputRef.current.click()
    }

    const handleFiles = (i) => {
        let updated = [...files];
        updated.splice(i, 1);
        if(updated.length < 1) {
            setFiles(null);
            setProgress(null);
            filesUploaded(false);
            return;
        }
        setFiles(updated);
    }

    const handleUpload = async () => {
        filesUploaded(false);
        let fd = new FormData();
        if (workspace === null && newWorkspace === '') {
            //error. Select one
            setMessage('Please select one Workspace');
            return;
        }

        fd.append('collection', collection.toString());
        
        // if(workspace) {
        //     fd.append('workspace', workspace.id);
        //     fd.append('create_wsp', '0');
        // } else {
        //     fd.append('workspace', newWorkspace);
        //     fd.append('create_wsp', '1');
        // }

        fd.append('workspace', newWorkspace);
        fd.append('create_wsp', '1');

        for (var i = 0; i < files.length; i++) {
            fd.append('files[]', files[i]);
        }
        
        const { request, _api } = await MainService().createBatchOfResources(fd);
        const config = {
            onUploadProgress: progressEvent => {
              //console.log(progressEvent)
              let progress = (progressEvent.loaded / progressEvent.total) * 100;
              setProgress(progress);
            },
            headers: request.headers
          }
          
        axios.post(_api.url, fd, config).then(res => {
            
            if (res.status == 200) {
                console.log("done: ", res.data.message);
                filesUploaded(true);
                dispatch(reloadCatalogue())
            }
          }).catch(err => {
            filesUploaded(true);
            setErrorOnUpload("error: " + err.message);
            console.log("error: ", err.message);
        })
        
    }

    function now () {
        var today = new Date();
        var date = today.getDate()+'-'+(today.getMonth()+1)+'-'+today.getFullYear();
        var time = today.getHours()+'_'+today.getMinutes()+'_'+today.getSeconds();
        console.log(date + ' '+ time);
        return 'Batch ' + date + ' '+ time;
        // const timeElapsed = Date.now();
        // const today = new Date(timeElapsed);
        // return today.toDateString(); // "Sun Jun 14 2020"
    }

    const DropContent = () => {
        return (
            files ? 
            (
                <> 
                    {
                        progress ? (
                            <>
                                
                                <Progress success={uploaded} error={errorOnUpload} percent={progress} />
                                {
                                    errorOnUpload ? 
                                        <Message error> {errorOnUpload}</Message>
                                        : 
                                        <Message info hidden={!uploaded}> Upload done</Message>
                                }
                                
                                
                            </>
                        ) : null
                    }
                    
                    {
                        files.map((file: File, i) => (
                            <div className='file-item'>
                                <Message success={uploaded && !errorOnUpload} error={errorOnUpload} size='small'>
                                    {file.name}
                                    <Btn style={uploaded || progress ? {display: 'none'} : {}} size='tiny' circular icon='close' onClick={() => handleFiles(i)} />    
                                </Message> 
                            </div>
                        ))
                    }
                </>
            ) : ( 
                <>
                    <div className='label-drop'> 
                        <p>
                            <Label>Click or Drop here </Label>
                        </p>
                    </div> 
                    <input
                        onChange={onFileInputChange}
                        ref={fileInputRef}
                        type="file"
                        multiple
                        hidden
                    />
                </>
            )
        )
    }

    const newBatch = () => {
        setFiles(null);
        setErrorOnUpload(null);
        setProgress(null);
        filesUploaded(false);
        setNewWorkspace(now());
    }

    return (
        <div>
            <Dialog 
                open={open} 
                onEntered={handleOnEntered}
                onChange={()=> console.log('CHANGED')}
                onClose={handleClose} 
                aria-labelledby="batch-dialog"
                fullWidth
                maxWidth={'md'}
                
            >
                <DialogTitle >New batch</DialogTitle>
                <DialogContent style={{height: '100vh'}}>
                    <Btn icon='close' circular onClick={handleClose} color="teal" className='read-card-close-button'/>    
                    
                    
                    <DialogActions >
                        <Btn 
                                disabled={files === null}
                                onClick={uploaded ? newBatch : handleUpload} 
                                color="teal"
                                loading={progress && !uploaded}    
                            >
                                {uploaded ? (
                                    <><Icon name='repeat'/> New batch</>
                                ) : (
                                    <><Icon name='save' /> Upload</>
                                )}
                            </Btn>
                    </DialogActions>
                    <div className='batch-form'>
                        {/* <Message> Select one Workspace </Message>
                        <Message 
                            hidden={message === null}
                            warning 
                            onDismiss={() => setMessage(null)}
                        >{message}</Message>
                        <Segment size='large'>
                            
                            <Grid columns={2} relaxed='very' stackable textAlign='center'>
                                <Grid.Column>
                                    <Dropdown
                                        style={focus === 'new' ? {opacity: 0.5} : {opacity: 1}}
                                        text={workspace ? workspace.name : 'Select one'}
                                        color='teal'
                                        button
                                        onFocus={() => {
                                            setFocus('exist')
                                            setNewWorkspace('');
                                        }}
                                    >
                                        <Dropdown.Menu>
                                            {
                                                workspaces.map(wsp => (
                                                    <Dropdown.Item onClick={() => setWorkspace(wsp)}>
                                                        {wsp.name}
                                                    </Dropdown.Item>
                                                ))
                                            }
                                        </Dropdown.Menu>
                                    </Dropdown>
                                </Grid.Column>
                                <Grid.Column verticalAlign='middle'>
                                    <Input 
                                        style={focus === 'exist' ? {opacity: 0.5} : {opacity: 1}}
                                        placeholder='Create a new one' 
                                        onFocus={() => {
                                            setFocus('new')
                                            setWorkspace(null)
                                        }}
                                        onChange={(e, d) => {
                                            setNewWorkspace(d.value)
                                        }}
                                        value={newWorkspace}
                                    />
                                </Grid.Column>
                            </Grid>
                            <Divider vertical>Or</Divider>
                        </Segment> */}
                        <Message info > 
                            It will create a new Workspace named:
                            <Input 
                                size='small'
                                style={(focus === 'exist' ? {minWidth: 200, opacity: 0.5, marginLeft: 10} : {minWidth: 200, opacity: 1, marginLeft: 10})}
                                placeholder='Create a new one' 
                                onFocus={() => {
                                    setFocus('new')
                                    setWorkspace(null)
                                }}
                                onChange={(e, d) => {
                                    setNewWorkspace(d.value)
                                }}
                                value={newWorkspace}
                            />
                        </Message>
                        <Message warning> LIMIT: A total of {server?.pms}{server?.pms.includes('M') || server?.pms.includes('m') ? 'B' : 'MB'} in no more than {server?.mfu} files per batch</Message>
                    </div>
                    
                    
                    <div
                        style={files ? {} : {cursor: 'pointer'}} 
                        className={highlighted ? 'batch-drop-area bda-highlight' : 'batch-drop-area'}
                    >
                        
                        <FileDrop
                            onFrameDragEnter={(event) => {
                                //console.log('onFrameDragEnter', event);
                                highlightArea(true)
                            }} //in
                            //onFrameDragLeave={(event) => console.log('onFrameDragLeave', event)}
                            onFrameDrop={(event) => {
                                console.log('onFrameDrop', event);
                                highlightArea(false)
                            }} //drop
                            //onDragOver={(event) => console.log('onDragOver', event)}
                            //onDragLeave={(event) => console.log('onDragLeave', event)}
                            onDrop={(files, event) => {
                                console.log('onDrop!', files, event); 
                                setProgress(null);
                                filesUploaded(false);
                                setFiles(Array.from(files))
                            }}
                            onTargetClick={files ? null : onTargetClick}
                        >
                        {
                            <DropContent />
                        }  
                        </FileDrop>
                    </div>
                </DialogContent>
                
            </Dialog>
        </div>
    );
}
