import React from 'react';
import { makeStyles, Theme, createStyles } from '@material-ui/core/styles';
import ListItem from '@material-ui/core/ListItem';
import ListItemAvatar from '@material-ui/core/ListItemAvatar';
import ListItemText from '@material-ui/core/ListItemText';
import Avatar from '@material-ui/core/Avatar';
import LazyImage from './LazyImage';
import { Icon } from 'semantic-ui-react';

const useStyles = makeStyles((theme: Theme) =>
  createStyles({
    root: {
        flexGrow: 1,
    },
    demo: {
      backgroundColor: theme.palette.background.paper,
    },
    title: {
      margin: theme.spacing(4, 0, 2),
    },
    columnData: {
        display: 'inline-flex',
        '& span': {
            padding: '0 12px'
        }
    }
  }),
);

export default function ListResource( props ) {
    const { data, preview, truncate, edit, remove, itemView } = props
    const classes = useStyles();

    return (
            <ListItem>
                <ListItemAvatar>
                    <Avatar variant='rounded' style={{width: 100, height: 80}}>
                        <LazyImage
                            src={preview}
                            alt='lazy_img'
                        />  
                    </Avatar>
                </ListItemAvatar>
                <ListItemText style={{marginLeft: 10}}
                    primary={truncate(data.name || data.data.description.course_title || data.data.description.name, 74)}
                />
                {/* <ListItemSecondaryAction >
                    <IconButton edge="end" aria-label="delete" onClick={remove}>
                        <DeleteIcon />
                    </IconButton>
                    
                    <IconButton edge="end" aria-label="delete" onClick={edit}>
                        <EditIcon />
                    </IconButton>
                    <div className={classes.columnData}>
                        
                    </div>
                    
                </ListItemSecondaryAction> */}
                
                <div className="dam-item-actions in-list">
                    
                    <button className="xdam-btn-primary btn-rou bg-primary group" title="Edit" onClick={edit}>
                        <Icon name='edit' />
                    </button>
                    
                    <button className="xdam-btn-primary bg-primary group" title="Delete"  onClick={remove}>
                        <Icon name='trash' />
                    </button>
                </div>
            
            </ListItem>
            
                
    );
}
