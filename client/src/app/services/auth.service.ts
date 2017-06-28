import { Injectable } from '@angular/core';

import { HttpService } from './http.service';
import { DataService } from './data.service';
import { UtilService } from './util.service';

@Injectable()
export class AuthService {
  constructor(private dataService: DataService, private httpService: HttpService, private utilService: UtilService){}

  decodeAuthToken(){
  	var token = this.dataService.auth_token;
  	var base64Url = token.split('.')[1];
  	var base64 = base64Url.replace('-','+').replace('_', '/');
  	return JSON.parse(window.atob(base64));
  }

  login(email, password){
  	return new Promise(resolve => {
  		this.httpService.post('api/user/signin', {email:email, password:password}, (data)=>{
	      this.dataService.auth_token = data.token;
	      localStorage.setItem('token', data.token);
	      resolve();
	    });
  	})
  }

  testAuth(){
    console.log('testing auth');
  	return new Promise(resolve => {
  		this.httpService.post('api/test', {}, (data)=>{
	      console.log(data);
	      resolve();
	    });
  	})
  }

  getToken(){
    return localStorage.getItem('token');
  }

  getSocialRedirectUrl(service){
    var uri = 'api/login/'+service;
    return new Promise(resolve => {
      this.httpService.get(uri, (data)=>{
        resolve(data);
      });
    });
  }

  loginFacebook(){
    this.getSocialRedirectUrl('facebook').then((data: any)=>{
      window.location.href = data.redirect_url; //this will redirect the the specified url that is in the backend as client url
    })
  }
}
