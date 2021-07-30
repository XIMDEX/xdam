import React, { useState } from 'react'
import { useDispatch } from 'react-redux';
import { reloadApp } from '../../appSlice';
import { Grid, Button } from '@material-ui/core';
import MainService from '../../api/service';
import { makeStyles } from '@material-ui/core/styles';
import TextField from '@material-ui/core/TextField';
import CircularProgress from '@material-ui/core/CircularProgress';
import { useHistory } from "react-router-dom";


const useStyles = makeStyles((theme) => ({
    root: {
      marginTop: '25vh'
    },
    bgCard: {
      // border: '1px solid '+theme.palette.primary.main,
      padding: '60px 60px 100px'
    },
    mbText: {
      marginBottom: 30
    },
    title: {
      fontWeight: 'bolder',
      marginBottom: 50
    }
  }
));



export function Login() { 
  let history = useHistory();
  const classes = useStyles();
  const dispatch = useDispatch(); 
  const [loading, setLoading] = useState(false);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [errors, setErrors] = useState({});

  const writeEmail = (e) => {
    setEmail(e.target.value)
  }
  const writePassword = (e) => {
    setPassword(e.target.value)
  }

  const log = async () => {
    setLoading(true)
    let user = await MainService().login(email, password)
    if (user.error) {
      setErrors(user.error)
      setLoading(false)
      return;
    }
    MainService().setToken('JWT', user.data.access_token)

    history.push('/home');
    dispatch(reloadApp());
  }

  function RenderErrors ({ errors }) {
    if (errors) {
      return <ul>
        {Object.keys(errors).map(item => (<li key=''>{errors[item][0]}</li>))}
      </ul>
    }
    return null
  };
  
  return (
    <Grid container justify="center" className={classes.root}>
      {/* <Grid container>
        <Grid item sm={12}> 
          <Typography align='center' variant='h4' className={classes.title}>XDAM</Typography> 
        </Grid>
      </Grid> */}

      <Grid item sm={3} lg={4} />
      <Grid item sm={6} lg={4} className={classes.bgCard}>
        {/* <Typography gutterBottom variant='h5' align='center' className={classes.mbText}>Login</Typography> */}
        <img src='ximdex_dam_login.png' style={{width: '100%', marginBottom: 30}} />
        <TextField className={classes.mbText} fullWidth size='small' label="Email" variant="outlined" onChange={writeEmail}/>
        <TextField className={classes.mbText} fullWidth size='small' label="Password" variant="outlined" type="password" onChange={writePassword} />
        <Button fullWidth color='primary' variant='contained' onClick={log} disabled={loading}>
          { loading ? (<CircularProgress size={24}/>) : "LOGIN" }
        </Button>
        <RenderErrors errors={errors}/>
      </Grid>
      <Grid item sm={3} lg={4}/>
    </Grid>
  );
}