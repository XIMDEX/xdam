import React, { useEffect, useState } from 'react';
import {
  Grid,
  Card
} from '@material-ui/core';
import { createStyles, makeStyles, Theme } from '@material-ui/core/styles';
import MainService from '../../../api/service';

import { render, renderFromUrl } from '../../../utils/render';
import { Button, List, Label, Icon } from 'semantic-ui-react';
import { MULTIMEDIA } from '../../../constants';
import { iconHandler } from '../../../utils/iconHandler';

const useStyles = makeStyles((theme: Theme) =>
  createStyles({
    imgView: {
      width: '100%',
      margin: '0 auto',
      display: 'block'
    },
    mediaPlayer: {
        width: '100%'
    },
    filesList: {
        '& p': {
            '& span': {
                fontWeight: 'bold'
            }
        }
    }
  }),
);

export default function RelatedFiles( { resData, files,  withPlayer = false, onEditModal = false, setTheFiles = null, DynamicFormResourceData = null } ) {

    const classes = useStyles();
    const [resourceData, setResourceData] = useState(resData)
    const [theFiles, setTF] = useState(files)
    
    const RenderFiles = () => {

        const MediaPlayer = ({mime_type, url}) => {
            let player;
            let src;
            if(mime_type.includes('video')) {
                src = renderFromUrl(url, 'raw');
                player = (<video controls className={classes.mediaPlayer}> <source src={src} /></video>)
            } else if(mime_type.includes('image')) {
                src = renderFromUrl(url, '');
                player = (<img src={src} className={classes.mediaPlayer} /> )
            } else if(mime_type.includes('audio')) {
                src = renderFromUrl(url, '');
                player = (<audio controls> <source src={src} /> </audio> )
            }
            else {
                player = (<p>Mime type not supported</p>)
            }

            return (
                <>
                    {player}
                </>
            )
        }

        const toggleMedia = (ix) => {
            var x = document.getElementById(ix + '_media_view_');
            if (x.style.display === "none") {
                x.style.display = "block";
            } else {
                x.style.display = "none";
            }
        }

        const removeMedia = async (dam, media_id) => {
            let res = await MainService().removeMedia(dam.id, media_id);
            console.log(res)
            setTheFiles(res.files)
            DynamicFormResourceData(res)
        }

        const download = async (file) => {
            await MainService().downloadFile(file);
        }
        
        if(theFiles) {
            return (
                <List className='relatedFiles-ul'>
                    {
                        theFiles.map((f, ix) => (
                            <Card key={ix} variant='outlined' className='associated-files-card'>
                                <List.Item>                                    
                                    
                                    <List.Content>
                                        <Icon name={iconHandler(f)}></Icon>
                                        <p><strong>File name:</strong> {f.file_name}</p> 
                                        <p><strong>Mime type:</strong> {f.mime_type}</p>
                                        {
                                            withPlayer ? (
                                            <>
                                                {
                                                    f.mime_type.includes('video') || f.mime_type.includes('image') || f.mime_type.includes('audio') ? (
                                                        <><a  onClick={() => toggleMedia((f.dam_url + ix))}>View </a> <span>|</span></>
                                                    ) : null
                                                }
                                                <a  onClick={() => download((f))}> Download </a>
                                                
                                                {onEditModal ? (<>
                                                    <span>|</span> 
                                                    <a  onClick={() => removeMedia(resData, f.id)} style={{color: 'red'}}> Remove </a>
                                                </>) : null}
                                                <div id={f.dam_url + ix + '_media_view_'} style={{display: 'none'}}>
                                                    <MediaPlayer mime_type={f.mime_type} url={f.dam_url} />
                                                </div>
                                            </>
                                            ) : null
                                        }
                                    </List.Content>
                                </List.Item>
                            </Card>
                        )) 
                    }
                </List>
            )
        } else {
            return (<>...</>)
        }
    }

    return (
      <Grid container style={{minHeight: onEditModal ? 'unset' : '60vh'}}>
        <Grid item sm={12}>
            <div >
                {theFiles && theFiles.length > 0 ? (
                <>
                    <Label>{onEditModal ? 'Already attached:' : 'Associated files'}</Label> 
                    <RenderFiles /> 
                </>
                ) : null
                }
                
            </div>      
        </Grid>
      </Grid>
    )
  }