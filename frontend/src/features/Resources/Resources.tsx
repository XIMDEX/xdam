import React, { useEffect, useState, useCallback } from 'react'
import { Grid, LinearProgress, Container } from '@material-ui/core';
import { Dropdown, Label } from 'semantic-ui-react'
import { makeStyles } from '@material-ui/core/styles';
import MainService from '../../api/service';
import { useSelector, useDispatch } from 'react-redux';
import { setFacets, setResources, setFixedFacets, selectResourcesLoading, setResourcesLoading, setLoading, setSchemas, selectCatalogueFlag } from '../../appSlice';
import IFacet from '../../interfaces/IFacet';
import { selectQuery, selectFacetsQuery, setQuery } from '../../slices/organizationSlice';
import param from '../../utils/querybuilder';
import { Resource } from './Resource';
import { ORGANIZATION, COLLECTION, WORKPSACES, COURSE } from '../../constants';
import { getOrgData, getCollData } from '../../utils/dataFind';
import Dialogs from './Modals/Dialogs';
import Pagination from '@material-ui/lab/Pagination';
import { Button as Btn, Icon as Icn } from 'semantic-ui-react';
import BatchDialog from './Modals/MassiveUpload/BatchDialog';
import store from '../../app/store';
import { SelectableGroup, createSelectable } from 'react-selectable';

const useStyles = makeStyles((theme) => ({
  root: {
    paddingTop: 0,
    padding: '0 10px',

  },
  rootWithoutSidebar: {
    width: 'calc(100% - 33px)',
    float: 'right',

    paddingRight: 10,
    position: 'relative'
  },
  loadingResources: {
    width: '100%',
    '& p': {
      position: 'relative',
      textAlign: 'center',
      left: '0',
      top: '200px'
    }
  },
  selectedFilters: {
    display: 'inline-flex',
    overflowY: 'hidden',
    overflowX: 'auto',
    width: '65%',
    height: '44px',
  },

  pagination: {
    display: 'flex',
    justifyContent: 'flex-start',
  },
  actionBtns: {
    display: 'flex',
    justifyContent: 'flex-end',
    maxHeight: 38,
  },
  disableActionsWhileLoading: {


    pointerEvents: 'none',


  },
  listContainer: {
    marginTop: 15
  },
  gridContainer: {
    marginTop: 15
  }
}
));

export function Resources({ collection, organization, sidebarOpen, _user }) {
  const mainService = MainService();
  const query = useSelector(selectQuery);
  const dispatch = useDispatch();
  const classes = useStyles();
  const selectedOrg = getOrgData(_user, organization);
  const selectedColl = getCollData(selectedOrg, collection);
  const facetsQuery = useSelector(selectFacetsQuery);
  const [localResources, setLocalResources] = useState([]);
  const resourcesLoading = useSelector(selectResourcesLoading);
  const [listMode, setListMode] = useState(false);
  const reload = useSelector(selectCatalogueFlag);
  const [pagination, setPagination] = useState(null);
  const currentQuery = useSelector(selectQuery);
  const [openBatch, setOpenBatch] = React.useState(false);
  const [openCreate, setOpenCreate] = React.useState(false);
  const [selectedKeys, setSelectedKeys] = React.useState([]);
  const isSelectable = false;
  const [mainContextAction, setMainContextAction] = useState(null)

  function toggleListMode(evt) {
    var val = evt.target.getAttribute('data-value') === '1' ? true : false;
    setListMode(val);
  }

  const fetchCatalogue = async () => {

    if (typeof selectedColl !== 'undefined') {
      const catalogue = await mainService.getCatalogue(selectedColl.id, '?' + param(query) + (facetsQuery ? '&' + buildFacetsQuery(facetsQuery) : ''));
      let pages = {
        ...catalogue
      };
      delete pages.facets;
      delete pages.data;
      setPagination(pages);
      dispatch(setFacets(catalogue.facets));
      dispatch(setResources(catalogue.data));
      setLocalResources(catalogue.data);
      dispatch(setResourcesLoading(false));
      dispatch(setLoading(false));
    }

  }

  function buildFacetsQuery(f) {
    let q = ''
    Object.keys(f).map(i => {
      f[i].forEach(v => {
        q += 'facets[' + i + ']=' + v + '&'
      })
    })
    return q;
  }

  const renderFixedFacets = () => {
    let orgFacetValues = {}
    let collectionsFacets = {}
    let wspFacet = {}

    let org = selectedOrg

    _user.data.organizations.forEach((og) => {
      orgFacetValues[og.name] = {
        count: og.org_resource_count,
        id: og.id
      }
    })

    if (org.id === organization) {
      org.collections.forEach((cid) => {
        collectionsFacets[cid.name] = {
          count: cid.coll_resource_count,
          id: cid.id
        }
      })

      org.workspaces.forEach((wsp) => {
        wspFacet[wsp.name] = {
          count: wsp.wsp_resource_count,
          id: wsp.id
        }
      })
    }

    let OFF: IFacet = {
      key: ORGANIZATION,
      label: 'Organization',
      values: orgFacetValues
    }

    let CFF: IFacet = {
      key: COLLECTION,
      label: 'Collection',
      values: collectionsFacets
    }

    // let WFF : IFacet =  {
    //   key: WORKPSACES,
    //   label: 'Workspaces',
    //   values: wspFacet
    // }
    dispatch(setFixedFacets([OFF, CFF]))
  }

  const getSchemas = async () => {
    const schemas = await MainService().getSchemas();
    dispatch(setSchemas(schemas));
  }

  useEffect(() => {
    renderFixedFacets();
    fetchCatalogue();
    if (store.getState().app.schemas === null) {
      //we only need one fetch by session
      getSchemas();
    }
  }, [facetsQuery, collection, organization, reload, query])

  const Chips = ({ data }): any => (
    Object.keys(data).map(key => (
      data[key].map((value, ix) => (
        <Label >
          {key === WORKPSACES ? (value) : (value === 'true' || value === 'false' ? key + ': ' + value : value)}
        </Label>
      ))
    ))
  )

  const LoadingResources = (): any => {
    return (
      <div className={classes.loadingResources}>
        <LinearProgress />
      </div>
    )
  }

  const ResourcesNotFound = (): any => {
    return (
      <div className={classes.loadingResources}>
        <p>Resources not found</p>
      </div>
    )
  }

  const changePageLimit = (evt, data): any => {

    dispatch(setResourcesLoading(true))

    let newQuery = {
      ...query
    }

    newQuery.limit = data.value;

    dispatch(setQuery(newQuery));
  }

  const changePage = (page): any => {
    dispatch(setResourcesLoading(true))
    let newQuery = {
      ...query
    }
    newQuery.page = page;
    dispatch(setQuery(newQuery));
  }

  const PageLimit = () => {
    const opts = [
      {
        key: '12',
        text: '12',
        value: 12,
      },
      {
        key: '24',
        text: '24',
        value: 24,
      },
      {
        key: '36',
        text: '36',
        value: 36,
      },
      {
        key: '48',
        text: '48',
        value: 48,
      }
    ]
    return (
      <Dropdown
        style={{ minWidth: 40, marginRight: 4 }}
        onChange={changePageLimit}
        placeholder={pagination.per_page}
        selection
        selectOnBlur={false}
        defaultValue={pagination.per_page}
        options={opts}
      />
    )
  }

  const OrderBy = () => {
    const opts = [
      {
        key: 'new',
        text: 'Newer first',
        value: 'new',
      },
      {
        key: 'old',
        text: 'Older first',
        value: 'old',
      }

    ]
    return (
      <Dropdown
        style={{ minWidth: 40, marginRight: 4 }}
        placeholder={opts[0].text}
        selection
        selectOnBlur={false}
        defaultValue={opts[0].text}
        options={opts}
      />
    )
  }

  const removeSearch = () => {
    let newQuery = {
      ...currentQuery
    };
    newQuery.page = 1;
    newQuery.search = '';
    dispatch(setQuery(newQuery));
  }

  const handleSelection = (selectedKeys) => {
    console.log(selectedKeys)
    //setSelectedKeys(selectedKeys)
  }

  const ResCont = (): JSX.Element => {
    const ContainerList = localResources.map((item: any, key) => (
      <Grid item xs={12} key={key} className='striped'>
        <Resource data={item} resourceType={selectedColl.resource_type} listMode={listMode} />
      </Grid>
    ))


    //const R = createSelectable(Resource);
    
    // const ContainerGridSelectable = localResources.map((item: any, key) => {
    //   let selected = selectedKeys.indexOf(item.id) > -1;
    //   return (
    //     <Grid key={key} item sm={12} md={3} lg={2} xl={1} style={selected === item.id ? {border: '1px solid red'} : {}}>
    //       <R key={key} selected={selected} selectableKey={item.id}
    //         data={item} resourceType={selectedColl.resource_type} listMode={listMode}
    //       >
    //         {/* <Resource data={item} resourceType={selectedColl.resource_type} listMode={listMode} /> */}
    //       </R>
          
    //     </Grid>
    //   )
    // })


    const ContainerGrid = localResources.map((item: any, key) => {
      return (
        <Grid key={key} item sm={12} md={3} lg={2} xl={1} >
          <Resource data={item} resourceType={selectedColl.resource_type} listMode={listMode} />
        </Grid>
      )
    })

    return (<>
      {
        listMode ? (
          <Grid container className={classes.listContainer}>
              {ContainerList}
          </Grid>
        ) :
          (
            // isSelectable ? (
            //   <SelectableGroup onSelection={handleSelection}>
            //     <Grid container spacing={1} className={classes.gridContainer}>
            //         {ContainerGridSelectable}
            //     </Grid>
            //   </SelectableGroup>
            // ) : (
            //   <Grid container spacing={1} className={classes.gridContainer}>
            //     {ContainerGrid}
            //   </Grid>
            // )
            <Grid container spacing={1} className={classes.gridContainer}>
                {ContainerGrid}
            </Grid>
          )
      }
    </>);
  }

  const newBatch = () => {
    console.log('new batch');
    setMainContextAction('create');
    setOpenBatch(true);
  }

  const options = [
    { key: 'batch', icon: 'database', text: 'New batch', value: 'batch', onClick: newBatch },
  ]

  const newResource = () => {
    setMainContextAction('create');
    setOpenCreate(true);
  }

  return (
    <Grid container justify='flex-start' className={sidebarOpen ? classes.root : classes.rootWithoutSidebar} >
      <Grid item sm={12}>
        <Grid container className={resourcesLoading ? classes.disableActionsWhileLoading : ''}>
          {
            resourcesLoading ? (
              <div style={{ opacity: 1, width: '100%', marginBottom: 2 }}>
                <LoadingResources />
              </div>)
              :
              (<div style={{ opacity: 0, width: '100%', marginBottom: 2 }}>
                <LoadingResources />
              </div>)
          }

          <Grid item sm={6} >
            {
              localResources.length > 0 ? (
                <>
                  <Label style={{ marginTop: 10 }}>{pagination.total} Resource{pagination.total > 1 ? 's' : ''} found</Label>
                  {
                    currentQuery.search !== '' ? (
                      // <Label style={{marginTop: 6}}>Searching for "{currentQuery.search}"</Label>
                      <Label style={{ marginTop: 10 }} >
                        Searching for "{currentQuery.search}"
                          <Icn onClick={removeSearch} name='delete' />
                      </Label>
                    ) : null
                  }
                </>
              ) : ''
            }

          </Grid>

          <Grid item sm={6} style={{ zIndex: 2 }} className={classes.actionBtns} >
            {
              localResources.length > 0 ? (
                <>
                  <PageLimit />
                  {/* <OrderBy /> */}
                </>
              ) : ''
            }
            <Btn.Group>
              <Btn toggle icon onClick={toggleListMode} color={listMode ? 'teal' : null} data-value='1' >
                <Icn name='list layout' data-value='1' />
              </Btn>
              <Btn toggle icon onClick={toggleListMode} color={!listMode ? 'teal' : null} data-value='0'>
                <Icn name='grid layout' data-value='0' />
              </Btn>
            </Btn.Group>
            <Btn.Group color='teal' style={{ marginLeft: 4, borderRadius: '.28571429rem' }}>
              <Btn onClick={newResource} >{'New ' + selectedColl.resource_type}</Btn>
              <Dropdown
                className='button icon'
                floating
                options={options}
                trigger={<></>}
              />
            </Btn.Group>

          </Grid>

          {
            localResources.length > 0 ? (
              <Grid container style={{ marginTop: 3 }}>
                <Grid item sm={12} > <Chips data={facetsQuery} />  </Grid>
              </Grid>) : null
          }

          {
            localResources.length > 0 ? <ResCont /> : (resourcesLoading ? '' : <ResourcesNotFound />)
          }

          <Grid container>
            {
              localResources.length > 0 ? (
                <Pagination
                  disabled={resourcesLoading}
                  variant="outlined" shape="rounded"
                  count={pagination.last_page}
                  page={query.page}
                  style={{ margin: '15px auto' }} onChange={(event, val) => changePage(val)} />
              ) : ''
            }

          </Grid>
        </Grid>
      </Grid>
      <Dialogs
        dialogOpen={openCreate}
        setDialogOpen={setOpenCreate}
        resourceType={selectedColl.resource_type}
        action={mainContextAction}
      />
      <BatchDialog
        open={openBatch}
        action={mainContextAction}
        setOpenBatch={setOpenBatch}
      />
    </Grid>
  );
}