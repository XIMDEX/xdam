import { Component, OnInit } from '@angular/core';
import { hasIn, isNil } from 'ramda';

import { ActionModel } from '@xdam/models/ActionModel';
import { HttpParams } from '@angular/common/http';
import { Item } from '@xdam/models/Item';
import { MainService } from '../services/main.service';
import { Pager } from '@xdam/models/Pager';
import { PagerModelSchema } from '@xdam/models/interfaces/PagerModel.interface';
import { SearchModel } from '@xdam/models/SearchModel';
import { XDamData } from '@xdam/models/interfaces/ItemModel.interface';
import { XDamSettingsInterface } from '@xdam/models/interfaces/Settings.interface';

import { JwtHelperService } from '../services/jwt-helper.service';
import { AuthService } from '../services/auth.service';
import { ActivatedRoute, Route, Router } from '@angular/router';
import { availableModeI, XdamModeI } from '@xdam/models/interfaces/XdamModeI.interface';
import { concat } from 'rxjs';


@Component({
    selector: 'app-home',
    templateUrl: './home.component.html',
    styleUrls: ['./home.component.scss']
})

export class HomeComponent implements OnInit {

    title = 'poly-dam';

    /**
     * A dict containing the main configurations for the application
     */
    mainConfig = null;

    /**
     * An instance of the mapper for Item model
     */
    imap = null;

    /**@ignore */
    limit = null;

    search: SearchModel;
    items: XDamData;
    settings: XDamSettingsInterface;

    /**@ignore */
    page: string;

    /**
     * The current selected page
     */
    currentPage = 1;

    /**
     * The current selected search string
     */
    searchTerm = '';

    /**
     * An array of all available facets
     */
    facets = {};
    default = true;
    action: ActionModel | null = null;

    reset = false;
    /**
     * 
     */

    xdamMode: XdamModeI;
    /**
     * 
     */

    private pagerSchema: PagerModelSchema = {
        total: 'total',
        currentPage: 'currentPage',
        lastPage: 'lastPage',
        nextPage: 'nextPage',
        prevPage: 'prevPage',
        perPage: {
            value: 'perPage'
        }
    };

    // Variables
    accessToken: any;
    accessTokenDetails: any;
    loading: boolean;


    constructor(
        jwtHelper: JwtHelperService,
        private authService: AuthService,
        private router: Router,
        private activatedRoute: ActivatedRoute,
        private mainService: MainService
    ) {
        this.accessToken = localStorage.getItem('access_token');
        this.accessTokenDetails = {
            id: jwtHelper.id(),
            name: jwtHelper.name(),
            email: jwtHelper.email()
        };
    }

    ngOnInit() {
        this.settings = this.mainService.getGeneralConfigs();
        this.search = new SearchModel();
        
        this.page = 'page';
        this.searchTerm = 'search';
        this.limit = 'limit';

        //We start the application in Course mode
        
        this.mainService.getMyProfile().subscribe(userData =>{
            this.activatedRoute.params.subscribe(params => {
                let collections = userData["data"].organizations[0].collections;

                let aviablesModes: availableModeI[] = [];

                for(let i = 0; i < collections.length; i++){
                    aviablesModes.push({
                        name: collections[i].name,
                        id:collections[i].id
                    });
                }

                let actualMode: availableModeI;

                if(params["id"]){
                    actualMode  = aviablesModes.filter(mode =>  mode.id == params["id"])[0];
                }else {
                    actualMode  = aviablesModes.filter(mode =>  mode.id === 3)[0];
                }

                this.xdamMode = {
                    currentMode: actualMode,
                    availableModes: aviablesModes
                }

                this.sendSearch(this.search);
            });            
        });        
    }

    /**
     * Appends all current params to query and makes a request storing all resources in
     * the items array
     */
    getItems() {
        let params = new HttpParams();
        params = params.append(this.page, String(this.search.page));
        if (!isNil(this.search.content)) {
            params = params.append(this.searchTerm, this.search.content === "1" ? "" : this.search.content);
        }
        if (!isNil(this.search.facets)) {
            Object.keys(this.search.facets).forEach(index => {
                const value = this.search.facets[index];
                params = params.append(`facets[${index}]`, value.join(','));
            });
        }
        params = params.append(this.limit, String(this.search.limit));

        this.mainService.list(this.xdamMode.currentMode.id + "" ,params).subscribe(
            response => {
                console.log("response", response)

                const pager:any = {
                    total: response['total'],
                    currentPage: response['current_page'],
                    lastPage: response['last_page'],
                    nextPage:response['next_page'],
                    prevPage: response['prev_page'],
                    perPage: response['per_page']
                }

                this.items = {
                    data: response['data'],
                    pager: new Pager(pager, this.pagerSchema),
                    facets: response['facets']
                };
                if (this.default) {
                    this.getDefaultFacet(response['facets']);
                }
            },
            err => console.error(err)
        );
    }

    getDefaultFacet(data) {
        const facets = {};

        data.map(({ key, default: defFacet = null }) => {
            if (!isNil(defFacet)) {
                facets[key] = [defFacet];
            }
        });

        this.default = false;
        this.search.update({ facets });
    }

    sendSearch(data: SearchModel) {
        this.search.update(data);
        this.getItems();
    }

    downloadItem(item: Item) {
        this.mainService.downloadResource(item).subscribe(
            response => {
                const url = window.URL.createObjectURL(response);
                const downloadFile = document.createElement('a');
                document.body.appendChild(downloadFile);

                downloadFile.style.display = 'none';
                downloadFile.href = url;
                downloadFile.download = item.name;
                downloadFile.click();
                downloadFile.remove();

                window.URL.revokeObjectURL(url);
            },
            err => console.error(err)
        );
    }

    resetDam() {
        this.reset = true;
        setTimeout(() => {
            this.reset = false;
        }, 250);
    }

    deleteItem(item: Item) {
        this.mainService
            .delete(item)
            .subscribe(
                response => {},
                err => {
                    console.error(err);
                }
            )
            .add(() => {
                this.getItems();
            });
    }

    damAction(data: ActionModel) {
        const action = new ActionModel(data);
        let actionType = null;
        let deleteFilesSubcribe = null;
        if (action.method === 'select') {
            action.status = 'success';
            setTimeout(() => {
                alert(`Selectd item ${action.item.name}`);
                this.action = action;
            }, 2500);
        } else {
            if (action.method === 'show') {
                actionType = this.mainService.getResource(action);
            }else if (action.method === 'edit'){

                if(action.data.filesToDelete.length > 0){
                    let filesToDelete: any[] = action.data.filesToDelete
                    for(let i = 0; i < filesToDelete.length ; i++){
                        actionType = this.concatObservables(
                            actionType, 
                            this.mainService.deleteFileToResource(
                                {
                                    id: action.data.dataToSave.id,
                                    idFile: filesToDelete[i].id
                                }
                            )
                        )                        
                    }
                }

                if(action.data.filesToUpload.length > 0){
                    let filesToUpload: any[] = action.data.filesToUpload
                    for(let i = 0; i < filesToUpload.length ; i++){
                        actionType = this.concatObservables(
                            actionType, 
                            this.mainService.addFileToResource(action, i)
                        )                        
                    }
                }
                actionType = this.concatObservables(actionType, this.mainService.updateForm(action))
            }else if (action.method === 'new'){
                //action.data['type'] = this.xdamMode;
                actionType = this.mainService.saveForm(action);
            }

            actionType
                .subscribe( result => {
                        const { data } = result as any;

                        action.data = result;
                        action.status = 'success';
                    },
                    ({ error, message, statusText }) => {
                        action.status = 'fail';
                        if (hasIn('errors', error)) {
                            action.errors = error.errors;
                        }
                    }
                )
                .add(() => {
                    if (action.method !== 'show') {
                        this.getItems();
                    }
                    this.action = action;
                });
        }
    }
    /**
     * Logout the user and revoke his token
     */
    logout(): void {
        this.loading = true;
        localStorage.removeItem('access_token');
        this.router.navigate(['/login']);
        /*this.authService.logout(this.accessTokenDetails)
            .subscribe(() => {
                this.loading = false;
                localStorage.removeItem('access_token');
                this.router.navigate(['/login']);
            });*/
    }

    changeMode(newMode:availableModeI){
        //console.log("home", newMode.id, this.xdamMode.currentMode.id)
        //if(newMode.id + "" == this.xdamMode.currentMode.id + "") return;
        this.router.navigate( ['/collection', newMode.id])
    }

    concatObservables(fisthObs, secondObs){
        if (fisthObs){
            fisthObs = concat(
                fisthObs,
                secondObs
            );
        }else{
            fisthObs = secondObs;
        }
        return fisthObs;
    }
}
