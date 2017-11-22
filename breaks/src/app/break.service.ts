import { Injectable, Inject } from '@angular/core';

import { Http } from '@angular/http';

import { WindowService } from './window.service';

import 'rxjs/add/operator/map';

@Injectable()
export class BreakService {

  public breaks;

  private apiHost: String;

  constructor(private windowService: WindowService, private http: Http) {
    this.apiHost = windowService.nativeWindow.breaksAppInfo.home_url + '/wp-json/self-service-breaks/v1/api';
   }

  getBreaks(){
    return this.http.get(this.apiHost + '/breaks')
                    .map(res => res.json())
                    .map((res) =>{
                      return res;
                    });
  }

  submitBreak(user){
    return this.http.post(this.apiHost + '/submit', {username: user})
                    .map(res => res.json())
                    .map((res) =>{
                        return res;
                    });
  }
  closeBreak(hash){
    return this.http.post(this.apiHost + '/close', {break: hash})
                    .map(res => res.json())
                    .map((res) =>{
                        return res;
                    });
  }
}
