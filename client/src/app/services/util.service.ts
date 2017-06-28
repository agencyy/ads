import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { HttpService } from './http.service';
import { DataService } from './data.service';

@Injectable()
export class UtilService {
  constructor(private router: Router, private dataService: DataService, private httpService: HttpService){}

  route(url){
    this.router.navigate([url]);
  }

  returnGetParameter(parameterName) {
    var result = null,
    tmp = [];
    location.search
        .substr(1)
        .split("&")
        .forEach((item) => {
          tmp = item.split("=");
          if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
        });
    return result;
  }
}
