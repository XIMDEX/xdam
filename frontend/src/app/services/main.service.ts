import { ActionModel } from '@xdam/models/ActionModel';
import { Item } from '@xdam/models/Item';
import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import SettingsMapper from '../mappers/SettingsMapper';
import EndPointMapper from '../mappers/EndPointMapper';
import { XdamModeI } from '@xdam/models/interfaces/XdamModeI.interface';
import {Router} from "@angular/router"

/**
 * Service who acts as a global state for the application.
 */
@Injectable({
    providedIn: 'root'
})
export class MainService {
    /**
     * Dict containing options for using with the http client
     */
    private httpOptions = { headers: {}, params: {} };

    /**
     * An instance of the EndPointMapper
     */
    private router: EndPointMapper;

    /**
     * An instance of the ConfigMapper
     */
    private configs: SettingsMapper;

    /**
     * @ignore
     */
    constructor(
        private http: HttpClient,
        private routerI: Router
    ) {
        this.router = new EndPointMapper();
        this.configs = new SettingsMapper();

        this.httpOptions.headers = new HttpHeaders({
            'Access-Control-Allow-Origin': '*',
            'Content-Type': 'application/json',
            Authorization: this.getToken()
        });
    }

    getBaseUrl() {
        return this.router.baseUrl;
    }

    /**
     * Gets the token parsed by the mapper.
     */
    getToken() {
        let accesToken = localStorage.getItem('access_token');
        if(accesToken){
            return "Bearer " + accesToken;
        }else{
            this.routerI.navigate(['/login']);
        }
         
    }

    /**
     * Gets general profile configs from the active profile.
     */
    getGeneralConfigs() {
        return this.configs;
    }

    /**
     * Gets the desired component profile config from the active profile.
     * @param component The desired component
     */
    getComponentConfigs(component = null) {
        return this.configs.get(component);
    }

    /**
     * Calls getResources method with the desired request parameters.
     * @param params The parameters
     * @returns {Observable} The response of getResources
     */
    list(xdamMode: XdamModeI | string ,params: HttpParams = null) {
        return this.getResources(xdamMode, params);
    }


    /**
     * Builds a query and fetchs data from the API.
     * @param {string} end The API endpoint
     * @param {string} key The key of the parameter in the params dict
     * @param {string} param The parameter to assign in the params dict
     * @returns {Observable} The response as a observable
     */
    getOptions(end: string, key: string, param: string) {
        const url = `${this.getBaseUrl()}${end}`;
        const params = {};
        params[key] = param;
        const heads = new HttpHeaders({
            'Access-Control-Allow-Origin': '*',
            Accept: 'application/json',
            Authorization: this.getToken()
        });
        return this.http.get(url, { headers: heads, params: params });
    }

    /**
     * Fetchs all the resources from the API, with default params
     * @param {Object} params The parameters dict for the query
     * @returns {Observable} The response as a observable
     */
    getResources(type, params: HttpParams = null) {
        const url = this.router.getEndPointUrlString('catalogue', 'index', type);
        delete params['updates']['default'];
        //delete params['updates']['limit'];
        //params = this.router.getBaseParams();
        //console.log("1", this.getToken())
        
        this.httpOptions.params = params;
        return this.http.get(url, this.httpOptions);
    }

    /**
     * Gets a single resource from the API.
     * @param id The identifier of the resource
     * @returns {Observable} The response as a observable
     */
    getResource(data: ActionModel) {
        const url = this.router.getEndPointUrl('resource', 'get', data.item);
        return this.http.get(url);
    }

    /**
     * Gets a single resource from the API.
     * @param id The identifier of the resource
     * @returns {Observable} The response as a observable
     */
    getMyProfile() {
        const heads = new HttpHeaders({
            'Access-Control-Allow-Origin': '*',
            Accept: 'application/json',
            Authorization: this.getToken()
        });

        const url = this.router.getEndPointUrl('user', 'get');
        return this.http.get(url,  { headers: heads });
    }

    /**
     * Deletes a resource from the server given its ID.
     * @param id The resource ID
     * @returns {Observable} The response as a observable
     */
    delete(item: Item) {
        const url = this.router.getEndPointUrl('resource', 'delete', item);
        return this.http.delete(url, { headers: this.httpOptions.headers });
    }

    /**
     * Receives a FormData object and send the form to the server.
     * @param {FormData} form The form to be sent
     * @returns {Observable} The response as a observable
     */
    saveForm(data: ActionModel) {
        const heads = new HttpHeaders({
            'Access-Control-Allow-Origin': '*',
            Accept: 'application/json',
            Authorization: this.getToken()
        });
        const formData = data.toFormData();
        const url = this.router.getEndPointUrl('resource', 'store');

        return this.http.post(url, formData, { headers: heads });
    }

    /**
     * Receives a FormData object and send the form to the server.
     * @param {FormData} form The form to be sent
     * @returns {Observable} The response as a observable
     */
    updateForm(data: ActionModel) {
        const heads = new HttpHeaders({
            'Access-Control-Allow-Origin': '*',
            Accept: 'application/json',
            Authorization: this.getToken()
        });
        //const method = data.method === 'new' ? 'post' : 'put';
        let formData;
        let url;

        formData = data.toFormData();
        url = this.router.getEndPointUrl('resource', 'update', new Item(data.data.dataToSave));

        /*if (method === 'put') {
            formData.append('_method', 'PUT');
        }*/
        return this.http.post(url, formData, { headers: heads });
    }

    /**
     * 
     * @param item 
     */
    deleteFileToResource(fileToDelete: {id: string; idsFile: any[]}, ) {
        const heads = new HttpHeaders({
            'Access-Control-Allow-Origin': '*',
            "Content-Type": 'application/json',
            Authorization: this.getToken()
        });
        console.log(fileToDelete)

        const url = this.router.getEndPointUrl('resource', 'deleteFile', fileToDelete);
        let jsonToSend: string[] = [];
        for(let i = 0; i < fileToDelete.idsFile.length; i ++ ){
            jsonToSend.push(fileToDelete.idsFile[i]["id"])
        }

        return this.http.put(url, jsonToSend, { headers: heads });
    }

    /**
     * 
     * @param item 
     */
    addFileToResource(data: ActionModel, index) {
        
        const heads = new HttpHeaders({
            'Access-Control-Allow-Origin': '*',
            Accept: 'application/json',
            Authorization: this.getToken()
        });
        let formData = data.filesToUploadToFormData(index);
        const url = this.router.getEndPointUrl('resource', 'addFile', new Item(data.data.dataToSave));

        return this.http.post(url, formData, { headers: heads });
    }

    /**
     * Downloads a resource as a blob given its ID.
     * @param id The resource ID
     * @returns {Observable} The response as a observable
     */
    downloadResource(item: Item) {
        const heads = new HttpHeaders({
            'Access-Control-Allow-Origin': '*',
            Authorization: this.getToken()
        });

         const url = ''; // this.getRoute('get', this.endPoint);
        // url = sprintf(url, item);

        return this.http.get(url + '/file', { headers: heads, responseType: 'blob' });
    }
}
