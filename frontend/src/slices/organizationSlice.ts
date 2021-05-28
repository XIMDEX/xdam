import { createSlice } from '@reduxjs/toolkit';
import { RootState } from '../app/store';

interface UserState {
  organization: null|number,
  collection: null|number,
  query: Record<any, any>,
  facets: null|Record<any, any>
}

const initialState: UserState = {
  organization: null,
  collection: null,
  query: {
    page: 1,
    search: '',
    limit: 48,
  },
  facets: {
  }
};

export const organizationSlice = createSlice({
  name: 'organization',
  initialState,
  reducers: {
    setInitialQuery: (state, { payload }) => {
      state.query = payload;
    },
    setQuery: (state, { payload } ) => {
      state.query = payload
    },
    setFacetsQuery: (state, { payload } ) => {
      state.facets = payload
    },
    setCollection: (state, { payload } ) => {
      state.collection = payload;
    },
    setOrganization: (state, { payload } ) => {
      state.organization = payload.oid;
      state.collection = payload.cid
    }
  },
});

export const { setInitialQuery, setQuery, setCollection, setOrganization, setFacetsQuery } = organizationSlice.actions;

export const selectQuery = (state: RootState) => state.organization.query;
export const selectCollection = (state: RootState) => state.organization.collection;
export const selectOrganization = (state: RootState) => state.organization.organization;
export const selectFacetsQuery = (state: RootState) => state.organization.facets;

export default organizationSlice.reducer;
