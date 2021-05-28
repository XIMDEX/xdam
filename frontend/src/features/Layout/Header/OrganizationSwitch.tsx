import React, { useEffect } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { selectOrganization, setOrganization, setFacetsQuery } from '../../../slices/organizationSlice';
import MainService from '../../../api/service';
import { setResourcesLoading } from '../../../appSlice';
import { Dropdown } from 'semantic-ui-react'

export default function OrganizationSwitch( { organizations, user } ) {
  const [selected, setSelected] = React.useState(null);
  
  const organization = useSelector(selectOrganization);
  const dispatch = useDispatch();
  
  const switchOrg = async (evt, data) => {
    const oid = data.value
    let org = user.data.organizations.find(x => x.id === oid);
    let col = org.collections[0];
    let wsp = org.workspaces.find(x => x.type === 'corporate');
    if (wsp === undefined) {
        wsp = org.workspaces.find(x => x.type === 'public');
        if (wsp === undefined) {
            // setErrors(['Error ocurred selecting default organization workspace'])
            throw new Error('Error ocurred selecting default organization workspace')
        }
    }

    const res = await MainService().setWorkspace(wsp.id)
    if (res.data['selected workspace']) {
        setSelected(org.name);
        dispatch(setOrganization({oid: oid, cid: col.id}))
        dispatch(setFacetsQuery({}))
        dispatch(setResourcesLoading(true))
    } else {
        //setErrors(['Error changing organization'])
        throw new Error('Error changing organization')
    }
    return;
  };

  useEffect(() => {
    let org = user.data.organizations.find(x => x.id === organization);
    setSelected(org.name);
  }, [])

  return (
    <>
        <Dropdown
            text={selected}
            // icon='users'
            floating
            // labeled
            button
            // className='icon'
        >
            <Dropdown.Menu>
                {/* <Dropdown.Header icon='users' content='Filter by tag' /> */}
                {
                    Object.keys(organizations).map((key, ix) => (
                        <Dropdown.Item onClick={switchOrg} key={ix} disabled={organizations[key].id == organization} value={organizations[key].id}>{key}</Dropdown.Item>
                    ))
                }
            </Dropdown.Menu>
        </Dropdown>
    </>
  );
}
