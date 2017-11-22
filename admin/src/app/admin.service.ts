import { Injectable } from '@angular/core';

import { Http, Headers } from '@angular/http';

import 'rxjs/add/operator/map';

import { WindowService } from './window.service';

@Injectable()
export class AdminService {

  private apiHost: string;
  private nonce: string;

  constructor(private windowService: WindowService, private http: Http) {
    this.apiHost = windowService.nativeWindow.adminAppInfo.home_url + '/wp-json/self-service-breaks/v1/api';
    this.nonce = windowService.nativeWindow.adminAppInfo.nonce;
  }

  getLeaders() {
    const headers = this.getHeaders();
    return this.http.get( this.apiHost + '/admin/leaders', { headers } )
                    .map( (res) => res.json())
                    .map( (res) => {
                      return res;
                    });
  }

  addLeader( leaderName ) {
    const headers = this.getHeaders();
    return this.http.post( this.apiHost + '/admin/leaders', { 'leaderName': leaderName }, { headers } )
                    .map( ( res ) => res.json() )
                    .map( ( res ) => {
                      return res;
                    });
  }

  deleteLeader( leaderName ) {
    const headers = this.getHeaders();
    return this.http.delete( this.apiHost + '/admin/leaders', { 'headers': headers, 'params': { 'leaderName': leaderName } } )
                    .map( ( res ) => res.json() )
                    .map( ( res ) => {
                      return res;
                    });
  }

  getUsers() {
    const headers = this.getHeaders();
    return this.http.get( this.apiHost + '/admin/users', { headers } )
                    .map( (res) => res.json() )
                    .map( (res) => {
                      return res;
                    });
  }

  addUser( userName ) {
    const headers = this.getHeaders();
    return this.http.post( this.apiHost + '/admin/users', { 'username': userName }, { headers } )
                    .map( ( res ) => res.json() )
                    .map( ( res ) => {
                      return res;
                    });
  }

  deleteUser( userName ) {
    const headers = this.getHeaders();
    return this.http.delete( this.apiHost + '/admin/users', { 'headers': headers, 'params': { 'username': userName } } )
                    .map( ( res ) => res.json() )
                    .map( ( res ) => {
                      return res;
                    });
  }

  getBreakLockStatus() {
    const headers = this.getHeaders();
    return this.http.get( this.apiHost + '/admin/lock-breaks', { headers } )
                    .map( ( res ) => res.json() )
                    .map( ( res ) => {
                      return res;
                    });
  }

  lockBreaks() {
    const headers = this.getHeaders();
    return this.http.post( this.apiHost + '/admin/lock-breaks', { 'lock': true }, { headers } )
                    .map( ( res ) => res.json() )
                    .map( ( res ) => {
                      return res;
                    });
  }

  unlockBreaks() {
    const headers = this.getHeaders();
    return this.http.delete( this.apiHost + '/admin/lock-breaks', { 'headers': headers, 'params': { 'lock': false } } )
                    .map( ( res ) => res.json() )
                    .map( ( res ) => {
                      return res;
                    });
  }

  getHeaders() {
    const headers = new Headers();
    headers.append( 'X-WP-Nonce', this.nonce );
    headers.append( 'Cache-Control', 'no-cache, no-store, must-revalidate' );
    return headers;
  }

}
