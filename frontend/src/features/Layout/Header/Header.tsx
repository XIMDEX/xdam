import React from 'react'
import { Link } from "react-router-dom";
import { useDispatch, useSelector } from 'react-redux';
import { Grid, Typography } from '@material-ui/core';
import { Dropdown, Image } from 'semantic-ui-react'
import { makeStyles } from '@material-ui/core/styles';
import MainService from '../../../api/service';
import { setLoading, selectFixedFacets } from '../../../appSlice';
import { selectQuery } from '../../../slices/organizationSlice';
import { Search } from '../../Resources/Search';
import OrganizationSwitch from './OrganizationSwitch';

const useStyles = makeStyles((theme) => ({
    root: {
      borderBottom: '1px solid #9eadba',
      marginTop: 20,
      padding: '0 10px',
      paddingBottom: 23
    },
    headerContent: {
      maxHeight: 40
    },
    title: {
      fontWeight: 'bolder',
      marginBottom: 20,
      color: '#02947d'
    },
    logout: {
      float: 'right', 
      marginTop: 5,
    },
    divSearch: {
      display: 'flex'
    },
    rightHeader: {
      display: 'flex',
      justifyContent: 'flex-end',
      maxHeight: 38
    }
  }
));

export function Header( {_user} ) { 
  const classes = useStyles();
  const dispatch = useDispatch();
  const query = useSelector(selectQuery);
  
  const fixedFacets = useSelector(selectFixedFacets);

  const logout = () => {
    dispatch(setLoading(true))
    MainService().logout()
  }
  
  return (
    <Grid container className={classes.root}>
        <Grid item sm={3} >
          <Link to="/home" style={{textDecoration: 'none'}}>
              {/* <Typography align='left' variant='h4' className={classes.title}>XDAM</Typography>  */}
              {/* <Image src='logo_ximdex2.png' className='headerLogo'></Image> */}
              {/* <Image src='logotipo_ximdex-DAM-white-small.png' className='headerLogo'></Image> */}
              <Image src='logotipo_ximdex-DAM-small-header.png' className='headerLogo'></Image>
              
          </Link>
        </Grid>
        <Grid item sm={6} >
          {
              fixedFacets.length > 0 ? (
                <div className={classes.divSearch}>
                  <Search currentQuery={query} collections={fixedFacets[1].values}/>
                </div>
              ) : ''
          }
        </Grid>
        <Grid item sm={3} className={classes.rightHeader}> 
            {/* <Link to="/about">About</Link> */}
            {
              fixedFacets.length > 0 ? (   
                <OrganizationSwitch organizations={fixedFacets[0].values} user={_user} />
              ) : ''
            }
            <Dropdown
              text='Profile'
              // icon='cog'
              // labeled
              floating
              button
              // className='icon'
            >
              <Dropdown.Menu>
                {/* <Dropdown.Header icon='users' content='Filter by tag' /> */}
                <Dropdown.Item onClick={logout} >Logout</Dropdown.Item>      
              </Dropdown.Menu>
            </Dropdown>
            
        </Grid>
    </Grid>
  );
}

/* 
fixedFacets.map((item: any, key)=>{
  return (
    <FacetCard key={key} 
      facet={item} 
      fixed={true} 
      resources={resources} 
      collection={selectedColl} 
      organization={selectedOrg} 
      facetsQuery={currentQuery} 
      _user={_user}
    />
  )
})
*/