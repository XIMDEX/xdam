import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { RootState } from './app/store';
import IFacet from './interfaces/IFacet';

interface AppState {
  user: null|Record<string,any>;
  loading: boolean;
  resourcesLoading: boolean;
  fixedFacets: IFacet[],
  facets: IFacet[],
  resources: [],
  schemas: null|Record<string,any>;
  lomesSchema: null|Record<string,any>;
  catalogueFlag: boolean,
  formData: any,
  reloadApp: boolean
}

const initialState: AppState = {
  user: null,
  loading: true,
  resourcesLoading: true,
  fixedFacets: [],
  facets: [],
  resources: [],
  schemas: null,
  lomesSchema: null,
  catalogueFlag: true,
  formData: null,
  reloadApp: false
};

export const appSlice = createSlice({
  name: 'app',
  initialState,
  reducers: {
    setUser: (state, {payload}) => {
      // Redux Toolkit allows us to write "mutating" logic in reducers. It
      // doesn't actually mutate the state because it uses the Immer library,
      // which detects changes to a "draft state" and produces a brand new
      // immutable state based off those changes
      
      state.user = payload;
    },
    setLoading: (state, {payload}) => {
        state.loading = payload;
    },
    setResourcesLoading: (state, {payload}) => {
      state.resourcesLoading = payload;
    },
    setFacets: (state, action: PayloadAction<IFacet[]>) => {
      state.facets = action.payload;
    },
    setFixedFacets: (state, action: PayloadAction<IFacet[]>) => {
      state.fixedFacets = action.payload;
    },
    setResources: (state, { payload }) => {
      state.resources = payload;
    },
    setSchemas: (state, { payload }) => {
      state.schemas = payload;
    },
    setLomesSchema: (state, { payload }) => {
      state.lomesSchema = payload;
    },
    reloadCatalogue: (state) => {
      state.catalogueFlag = !state.catalogueFlag;
    },
    setFormData: (state, { payload }) => {
      state.formData = payload
    },
    reloadApp: (state) => {
      state.reloadApp = !state.reloadApp;
    },
  },
});

export const { setUser, setLoading, setFixedFacets, setFacets, 
  setResources, setResourcesLoading, setSchemas, setLomesSchema, 
  reloadCatalogue, setFormData, reloadApp} = appSlice.actions;

// The function below is called a thunk and allows us to perform async logic. It
// can be dispatched like a regular action: `dispatch(incrementAsync(10))`. This
// will call the thunk with the `dispatch` function as the first argument. Async
// code can then be executed and other actions can be dispatched
// export const incrementAsync = (amount: number): AppThunk => dispatch => {
//   setTimeout(() => {
//     dispatch(incrementByAmount(amount));
//   }, 1000);
// };

// The function below is called a selector and allows us to select a value from
// the state. Selectors can also be defined inline where they're used instead of
// in the slice file. For example: `useSelector((state: RootState) => state.counter.value)`
export const selectUser = (state: RootState) => state.app.user;
export const selectLoading = (state: RootState) => state.app.loading;
export const selectResourcesLoading = (state: RootState) => state.app.resourcesLoading;
export const selectFacets = (state: RootState) => state.app.facets;
export const selectFixedFacets = (state: RootState) => state.app.fixedFacets;
export const selectResources = (state: RootState) => state.app.resources;
export const selectSchemas = (state: RootState) => state.app.schemas;
export const selectLomesSchema = (state: RootState) => state.app.lomesSchema;
export const selectCatalogueFlag = (state: RootState) => state.app.catalogueFlag;
export const selectReloadApp = (state: RootState) => state.app.reloadApp;
export const selectFormData = (state: RootState) => state.app.formData;

export default appSlice.reducer;
