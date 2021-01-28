import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { XdamMode } from '@xdam/models/interfaces/XdamMode.interface';


const routes: Routes = [
    {   path: '', 
        redirectTo: XdamMode.Multimedia, 
        pathMatch: 'full' 
    },
    {
        path: XdamMode.Course,
        loadChildren: () => import('./home/home.module').then(m => m.HomeModule),
        data: {mode: XdamMode.Course}
    },
    {
        path: XdamMode.Multimedia,
        loadChildren: () => import('./home/home.module').then(m => m.HomeModule),
        data: {mode: XdamMode.Multimedia}
    },
    {
        path: 'login',
        loadChildren: () => import('./login/login.module').then(m => m.LoginModule)
    },
    {
        path: '**',
        redirectTo: XdamMode.Multimedia
    }
];

@NgModule({
    imports: [RouterModule.forRoot(routes)],
    exports: [RouterModule]
})
export class AppRoutingModule { }
