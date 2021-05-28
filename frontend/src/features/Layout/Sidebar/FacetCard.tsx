import React, { useState, useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux';
import { Grid, Button, IconButton, Typography, TextField } from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';
import _ from 'lodash'
import { ORGANIZATION, COLLECTION } from '../../../constants';
import { setFacetsQuery, setQuery, selectQuery } from '../../../slices/organizationSlice';
import { setResourcesLoading } from '../../../appSlice';
import ClearIcon from '@material-ui/icons/Clear';
import KeyboardArrowDownIcon from '@material-ui/icons/KeyboardArrowDown';
import KeyboardArrowUpIcon from '@material-ui/icons/KeyboardArrowUp';
import { Icon } from 'semantic-ui-react';

const useStyles = makeStyles((theme) => ({
    sidebarRoot: {
      "& ul": {
        paddingLeft: 8
      },
      width: 'calc(100% - 30px)',
      marginBottom: 10,
      marginLeft: 15,
      backgroundColor: '#FFF',
      '& .MuiOutlinedInput-inputMarginDense': {
        paddingTop: 4.5,
        paddingBottom: 4.5
      },
    },
    cardFacet: {
        width: '100%',
        padding: '0 4px'
    },
    headerContent: {
      maxHeight: 40
    },
    hidden: {
        display: 'none'
    },
    margin: {
        padding: 5,
    },
    title: {
      fontWeight: 'bolder',
      marginBottom: 20
    },
    capitalize: {
        textTransform: 'capitalize' 
    },
    clearIcon: {
        position: 'relative',
        top: 3,
        right: 3,
        paddingLeft: 3,
        transform: 'scale(0.75)'
    }
  }
));

export function FacetCard({ facet, fixed, resources, collection, organization, facetsQuery, _user }) { 
    const classes = useStyles()
    const values = facet.values ?? {}
    const dispatch = useDispatch()
    const [facetValues, setFacetValues] = useState(values)
    const [errors, setErrors] = useState([])
    const [cardOpen, setCardOpen] = useState(facet.key === COLLECTION || facet.key === ORGANIZATION ? false : true)
    const selectedOrg = organization
    const selectedColl = collection
    const [search, setSearch] = useState('')
    const currentFacets = JSON.parse(JSON.stringify(facetsQuery));
    const cQuery = useSelector(selectQuery);
    
    useEffect( () => {
        if (search === '') {
            setFacetValues(facet.values)
        }
    }, [collection, facetsQuery, organization, resources])
    
    function onFilterChange(e) {
        setSearch(e.target.value);
        var result = _.pickBy(values, function(value, key) {
            
            return _.includes(key.toLowerCase(), e.target.value.toLowerCase());
        });
        setFacetValues(result)
    }
    async function filterRadio (evt)
    {
        evt.target.checked = true;
        const facetKey = facet.key;
        const facetValue = evt.target.value;

        if (currentFacets.hasOwnProperty(facetKey)) {
            currentFacets[facetKey].splice(facetKey, 1, facetValue)
        } else {
            currentFacets[facetKey] = []
            currentFacets[facetKey].push(facetValue)
        }
        let nQ = {
            ...cQuery
        };
        nQ.page = 1
        dispatch(setResourcesLoading(true))
        dispatch(setQuery(nQ));
        dispatch(setFacetsQuery(currentFacets));

    }
    async function filterCheck (evt)
    {
        const checked = evt.target.checked; 
        const facetKey = facet.key;
        const facetValue = evt.target.value;
        
        if (currentFacets.hasOwnProperty(facetKey)) {
            if (checked) {
                if (!currentFacets[facetKey].includes(facetValue)) {
                    currentFacets[facetKey].push(facetValue)
                }
            } else {
                if (currentFacets[facetKey].includes(facetValue)) {
                    currentFacets[facetKey].forEach((item, i) => {
                        if (item === facetValue) {
                            currentFacets[facetKey].splice(i, 1)
                            if (currentFacets[facetKey].length < 1) {
                                delete currentFacets[facetKey]
                            }
                            return;
                        }    
                    })
                }
            }
        } else {
            currentFacets[facetKey] = []
            currentFacets[facetKey].push(facetValue)
        }
        let nQ = {
            ...cQuery
        };
        nQ.page = 1
        dispatch(setResourcesLoading(true))
        dispatch(setQuery(nQ));
        dispatch(setFacetsQuery(currentFacets))
    }

    function Errors(): any
    {
        return (
            errors.map((error) => (
                <span>{error}</span>
            ))
        )
    }

    function getChecked(toCheck, key): boolean
    {
        let check = Object.keys(currentFacets)
        let isChecked = false;
        check.forEach(item => {
            if (key === item) {
                if (currentFacets[item].includes(toCheck.toString())) {
                    isChecked = true;
                    return;
                }
            }
        })
        return isChecked;
    }
    
    function clearFilter(evt)
    {
        const facetKey = facet.key;
        if (currentFacets.hasOwnProperty(facetKey)) {
            delete currentFacets[facetKey]
        }
        dispatch(setResourcesLoading(true))
        dispatch(setFacetsQuery(currentFacets))
    }

    function toggleCard()
    {
        var toggle = !cardOpen;
        setCardOpen(toggle)
    }

    function label()
    {
        if (facet.key === ORGANIZATION && typeof selectedOrg === 'object') {
            return selectedOrg.name
        }
        if (facet.key === COLLECTION && typeof selectedColl === 'object') {
            return selectedColl.name
        }

        // if (facet.key === 'types') {
        //     return 'Attached files';
        // }

        return facet.label
    }

    function FacetItems( { fixed } ): any {
        if (facet.key === COLLECTION || facet.key === ORGANIZATION) {
            return (
                Object.keys(facetValues).map((name, index) => (
                    <li key={index} style={{listStyleType: "none"}}>
                        <input type="radio"
                            name={facet.key} 
                            value={facetValues[name].id} 
                            onChange={filterRadio} 
                            defaultChecked={facet.key === COLLECTION ? (collection.id === facetValues[name].id) : (organization.id === facetValues[name].id)}
                            id={(facet.key +'-'+ name +'-'+facetValues[name].id).replace(/ /g,'--')}
                        /> 
                        <label htmlFor={(facet.key +'-'+ name +'-'+facetValues[name].id).replace(/ /g,'--')}>
                            <span>{ name.replace('collection', '') } <strong>({facetValues[name].count})</strong> </span>
                        </label>
                    </li>
                    )
                )
            )
        } else {
            if (facetValues) {
                return (
                    Object.keys(facetValues).map((name, index) => (
                        //switch lines to hidden facets values in zero
                        // <li key={index} style={{listStyleType: "none"}} className={facetValues[name].count < 1 ? classes.hidden : null}>
                        <li key={index} style={{listStyleType: "none"}}>
                            <input 
                                type={facetValues[name].radio ? 'radio' : 'checkbox'} 
                                name={facet.key}
                                // disabled={facetValues[name].count < 1}
                                value={fixed ? facetValues[name].id : name} 
                                onChange={facetValues[name].radio ? filterRadio : filterCheck}
                                checked={getChecked(fixed ? facetValues[name].id : name, facet.key)} 
                                id={(facet.key +'-'+ name +'-'+facetValues[name].id).replace(/ /g,'--')}
                            /> 
                            <label htmlFor={(facet.key +'-'+ name +'-'+facetValues[name].id).replace(/ /g,'--')}>
                                <span>{ name } <strong>({ facetValues[name].count })</strong></span>
                            </label>
                            
                        </li>
                        )
                    )
                )
            } else {
                return (
                    <></>
                )
            }
        }
    }
    
    return (
        
        <Grid container className={`${classes.sidebarRoot} ${cardOpen ? 'cardOpen' : null} facetCard facets-context`} >
                {/* Errors */}
                <Grid item sm={12}>
                    <Errors />
                </Grid>
                {/* Facet title */}
                <Grid container>
                    <Grid item sm={!fixed && currentFacets.hasOwnProperty(facet.key) ? 11 : 12}>
                        <Button aria-label="facet-title" 
                            onClick={toggleCard} 
                            fullWidth 
                            endIcon={cardOpen ? (<Icon name='minus' size='small'/>) : (<Icon name='plus' size='small' />)}
                        >
                            <Grid container>
                                <Grid item sm={12}>
                                    <Typography className={classes.capitalize} align='left' color='primary'>
                                        <strong className={!cardOpen ? 'darkLabel' : 'whiteLabel'}>{label()}</strong>
                                    </Typography>    
                                </Grid>
                            </Grid>
                        </Button>
                    </Grid>
                    {
                        !fixed && currentFacets.hasOwnProperty(facet.key) ? (
                            <Grid item sm={1}>
                                <IconButton color='primary' size='small' onClick={clearFilter} className={classes.clearIcon}>
                                    <ClearIcon color='secondary'/>
                                </IconButton>
                            </Grid>
                        ) : null
                    }
                </Grid>
                <div className={cardOpen ? classes.cardFacet : classes.hidden}>
                    {
                        Object.keys(facetValues).length > 4 && search === '' ? (
                        // true ? (
                            <Grid item sm={12}>
                                <TextField 
                                    onChange={ onFilterChange } 
                                    placeholder='search' 
                                    size='small'
                                    fullWidth
                                />
                            </Grid>
                        ) : (search !== '' ? (
                            <Grid item sm={12}>
                                <TextField 
                                    onChange={ onFilterChange } 
                                    placeholder='search' 
                                    size='small'
                                    fullWidth
                                />
                            </Grid>
                        ) : null)
                    }
                    
                    {/* Facet values */}
                    <Grid item sm={12}>
                    <ul> 
                        {
                            organization && collection ? (<FacetItems fixed={fixed}/>) : ''
                        }
                    </ul>
                    </Grid>
                </div>
            </Grid>
        
    );
}


