import { Injectable } from '@angular/core';
import { Http, Headers, Response } from '@angular/http';
import { DataService } from './data.service';

@Injectable()
export class HttpService {

  server_url = "http:\/\/back.dev/";

  constructor(private http:Http) { }

  post(uri, data, callback){
    var url = this.server_url+uri;
    
    var headers = {
      headers: new Headers({
        // 'X-Requested-With':'XMLHttpRequest',
        // 'Content-Type':'application/json',
        // 'Accept':'application/json',
        'Authorization': 'Bearer '+ this.getAuthToken(),
      }) //this doest work from tutorial
    }
    this.http.post(url, data, headers).subscribe(
        (res: any) => {
          // var data = JSON.stringify(res._body);
          var data = JSON.parse(res._body);
          callback(data)
        }
    );
  }

  get(uri, callback){
    var url = this.server_url+uri;
    this.http.get(url).subscribe(
        (res: any) => {
          var data = JSON.parse(res._body);
          callback(data)
        }
    );
  }

  getAuthToken(){
    return localStorage.getItem('token');
  }


}
