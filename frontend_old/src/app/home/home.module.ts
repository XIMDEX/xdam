import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HomeComponent } from './home.component';
import { XDamModule } from 'projects/xdam/src/public_api';
import { Routes, RouterModule } from '@angular/router';
import { AuthGuardService } from '../services/auth-guard.service';

const routes: Routes = [
    {
        path: '',
        component: HomeComponent,
        canActivate: [ AuthGuardService ]
    }
];

@NgModule({
  declarations: [HomeComponent],
  imports: [
        XDamModule,
        CommonModule,
        RouterModule.forChild(routes)
  ],
  providers: [],
})
export class HomeModule { }
