import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { XdamModeI } from '@xdam/models/interfaces/XdamModeI.interface';


const routes: Routes = [
    {   path: '', 
        redirectTo: "collection/", 
        pathMatch: 'full' 
    },
    {
        path: 'login',
        loadChildren: () => import('./login/login.module').then(m => m.LoginModule)
    },
    {   path: 'collection/:id',
        loadChildren: () => import('./home/home.module').then(m => m.HomeModule)
    },
    /*{
        path: XdamMode.Course,
        loadChildren: () => import('./home/home.module').then(m => m.HomeModule),
        data: {mode: XdamMode.Course}
    },
    {
        path: XdamMode.Multimedia,
        loadChildren: () => import('./home/home.module').then(m => m.HomeModule),
        data: {mode: XdamMode.Multimedia}
    },*/
    
    {
        path: '**',
        redirectTo: "collection/"
    }
];

@NgModule({
    imports: [RouterModule.forRoot(routes)],
    exports: [RouterModule]
})
export class AppRoutingModule { }
