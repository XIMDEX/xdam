import React, { useState } from 'react';
import { createStyles, Theme, makeStyles } from '@material-ui/core/styles';
import { useDispatch, useSelector } from 'react-redux';
import { setQuery, selectCollection, setCollection, setFacetsQuery } from '../../slices/organizationSlice';
import { Input, Dropdown } from 'semantic-ui-react'

import { setResourcesLoading } from '../../appSlice';
import _ from 'lodash'

const useStyles = makeStyles((theme: Theme) =>
  createStyles({
    root: {
      flexGrow: 1,
    },
    inputRoot: {
      color: 'inherit',
    },
    inputInput: {
      padding: theme.spacing(1, 1, 1, 0),
      // vertical padding + font size from searchIcon
      paddingLeft: `calc(1em + ${theme.spacing(4)}px)`,
      transition: theme.transitions.create('width'),
      width: '100%',
      [theme.breakpoints.up('sm')]: {
        width: '12ch',
        '&:focus': {
          width: '20ch',
        },
      },
    },
  }),
);

export interface Props {
  currentQuery?: any;
  collections?: any;
}

export function Search( Props ) {
  const { currentQuery, collections } = Props;
  
  const classes = useStyles();
  const [search, setSearch] = useState('')
  const dispatch = useDispatch()
  const collection = useSelector(selectCollection);

  const onEnter = (e) => {
    if(e.key === 'Enter') {
      goSearch()
    }
  }
  const goSearch = () => {  
      dispatch(setResourcesLoading(true))
      let newQuery = {
          ...currentQuery
      };
      newQuery.page = 1;
      let cleanSearch = search.replace(/[`~!@#$%^&*()+\;:'"<>\{\}\[\]\\\/]/gi, '');
      newQuery.search = cleanSearch;
      dispatch(setQuery(newQuery))
  }

  const _setSearch = (evt) => {
    setSearch(evt.target.value)
  }

  
  const switchCollection = async (e, data) => {
    dispatch(setResourcesLoading(true))
    const cid = data.value
    let newQuery = {
      ...currentQuery
    }
    newQuery.page = 1;
    newQuery.search = '';
    
    dispatch(setQuery(newQuery))
    dispatch(setCollection(cid))
    dispatch(setFacetsQuery({}))
    return;
  };

  const stateOptions = _.map(collections, (coll, index) => ({
    key: index,
    text: index,
    value: coll.id,
  }))


  return (
    <div className={classes.root}>
          <Input
            fluid
            // action={{
            //   color: 'teal',
            //   icon: 'search',
            //   onClick: goSearch
            // }}
            onKeyDown={onEnter}
            onChange={_setSearch}
            placeholder='Search...'
            labelPosition='left'
            label={<Dropdown placeholder='Collection' onChange={switchCollection} selection options={stateOptions} value={collection}/>}
          />
    </div>
  );
}
