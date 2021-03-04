import { Injectable } from '@angular/core';
import {HttpClient, HttpHeaders, HttpParams} from '@angular/common/http';
@Injectable({
    providedIn: 'root'
})
export class AuthService {
    // Variables
    /*authUrl = 'http://localhost:81/xdam-backend/public/index.php/oauth/token';
    apiUrl = 'http://localhost:81/xdam-backend/public/index.php/api/v1';*/

    authUrl = 'http://localhost/xdam-v2/xdam/backend/public/api/v1/auth/login';
    apiUrl = 'http://localhost:81/xdam-backend/public/index.php/api/v1'

    options: any;
    /**
     * Constructor
     * @param http The http client object
     */
    constructor(
        private http: HttpClient
    ) {
        this.options = {
            headers: new HttpHeaders({
                Accept: 'application/json',
                'Content-Type': 'application/json'
            })
        };
    }
    /**
     * Get an access token
     * @param e The email address
     * @param p The password string
     */
    login(e: string, p: string) {
        return this.http.post(this.authUrl, {
            email: e,
            password: p,
        }, this.options);
    }
    /**
     * Revoke the authenticated user token
     */
    logout(accesTokenDetails: any) {
        this.options.headers.Authorization = 'Bearer ' + localStorage.getItem('access_token');
        localStorage.removeItem("access_token")
        const params = new HttpParams().set('user', accesTokenDetails.id);
        return this.http.get(this.apiUrl + '/token/revoke', {headers: this.options.headers, params});
    }
}
