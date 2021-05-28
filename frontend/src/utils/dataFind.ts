export const getOrgData = (user, oid) => {
    let org = user.data.organizations.find(organization => organization.id == oid);
    return org;    
}

export const getWspData = (selectedOrg, wid) => {
    let wsp = selectedOrg.workspaces.find(wsp => wsp.id == wid);
    return wsp;    
}

export const getCollData = (selectedOrg, cid) => {
    //console.log(selectedOrg, cid)
    return selectedOrg.collections.find(collection => collection.id == cid);
}

export const getCountInWorkspace = (wid, resources) =>
{   
    let q = 0;
    resources.forEach((res: any) => {
        if (res.workspaces.map(String).includes(wid.toString())) {
            q++;
        }
    })
    return q;
}

