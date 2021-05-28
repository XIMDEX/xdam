import React, { useEffect } from 'react';
import { makeStyles, Theme } from '@material-ui/core/styles';
import Tabs from '@material-ui/core/Tabs';
import { Tab } from '@material-ui/core';
import Typography from '@material-ui/core/Typography';
import Box from '@material-ui/core/Box';
import SemanticForm from "@rjsf/semantic-ui";
import { JSONSchema7 } from 'json-schema';
import { useSelector } from 'react-redux';
import { selectLomesSchema } from '../../../appSlice';
import { Dropdown, Button } from 'semantic-ui-react';
import _ from 'lodash'
import MainService from '../../../api/service';

interface TabPanelProps {
  children?: React.ReactNode;
  index: any;
  value: any;
}

function TabPanel(props: TabPanelProps) {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`vertical-tabpanel-${index}`}
      aria-labelledby={`vertical-tab-${index}`}
      {...other}
    >
      {value === index && (
        <Box p={3}>
          <Typography>{children}</Typography>
        </Box>
      )}
    </div>
  );
}

function a11yProps(index: any) {
  return {
    id: `vertical-tab-${index}`,
    'aria-controls': `vertical-tabpanel-${index}`,
  };
}

const useStyles = makeStyles((theme: Theme) => ({
  root: {
    flexGrow: 1,
    backgroundColor: theme.palette.background.paper,
    display: 'flex',
    '& [id^="vertical-tabpanel-"]': {
      width: '100%'
    },
    minHeight: '80vh'
  },
  tabs: {
    borderRight: `1px solid ${theme.palette.divider}`,
    minWidth: 220
  },
  tabPanel: {
    width: '100%'
  },
  blur: {
    width: '100%',
    height: '100%',
    '&::before': {
      content: '""',
      right: 0,
      top: 0,
      width: '73%',
      height: '100%',
      position: 'absolute',
      background: '#ffffff4a',
      backdropFilter: 'blur(2px)',
      zIndex: 1,
    }
  },
}));

export default function Lomes( {resourceData} ) {
  const classes = useStyles();
  const [value, setValue] = React.useState(0);
  const [loading, setLoading] = React.useState(true);
  const lomesSchema = useSelector(selectLomesSchema);
  const [resourceLomes, setResourceLomes] = React.useState({});
  
  const handleChange = async (event: React.ChangeEvent<{}>, newValue: number) => {
    setLoading(true)
    await getLomesData();
    setValue(newValue);
  };

  const getLomesData = async () => {

    let res = await MainService().getLomesData(resourceData.id);
    setResourceLomes(res.data);
    setLoading(false)
  }

  useEffect(() => {
    getLomesData()
  }, [resourceData])

  const CustomDropdown = function(props) {
    const opts = _.map(props.schema.enum, (val, index) => ({
      key: index,
      text: val,
      value: val,
    }));

    return (
    <>
        <label>{props.label}</label>
        <Dropdown 
          placeholder='Seleccionar'
          fluid
          selection
          options={opts}
          selectOnBlur={false}
          onChange={(e:any, {value}) => props.onChange(value?.toString())}
          value={props.value}
        />
    </>
    );
  };

  const widgets = {
    SelectWidget: CustomDropdown
  };

  const saveTabData = async (e, data) => {
    e.formData._tab_key = e.schema.key;
    let res = await MainService().postLomesData(resourceData.id, e.formData);
  }
  const tabFormData = (tab) => {
    //console.log(tab, resourceLomes)
    let formData = _.find(resourceLomes, { 'key': tab.key });
    if (formData) {
      return formData.formData;
    }
  }
  return (
    <div className={classes.root}>
      <Tabs
        orientation="vertical"
        variant="scrollable"
        value={value}
        onChange={handleChange}
        aria-label="Vertical tabs example"
        className={classes.tabs}
      >
        {
            lomesSchema.tabs.map((tab, ix) => (
                <Tab key={ix} label={tab.title} {...a11yProps(tab.key)} />
            ))
        }
      </Tabs>
        {
          lomesSchema.tabs.map((tab, ix) => (
              // console.log(tab)
              
                <TabPanel value={value} index={parseInt(tab.key) - 1} key={ix}>
                    <div className={loading ? classes.blur : null}></div>
                    <SemanticForm
                      liveOmit={false}
                      idPrefix={"rjsf_prefix"}
                      schema={tab as JSONSchema7}
                      widgets={widgets}
                      onSubmit={saveTabData}
                      formData={tabFormData(tab)}
                      noValidate
                    > 
                      <div>
                        <Button type="submit">Submit</Button>
                      </div>
                    </ SemanticForm>
                </TabPanel>
              
          )) 
        }   
    </div>
  );
}
