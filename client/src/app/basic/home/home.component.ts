import { Component, OnInit } from '@angular/core';


import { HttpService } from './../../services/http.service';
import { DataService } from './../../services/data.service';
import { UtilService } from './../../services/util.service';
import { AuthService } from './../../services/auth.service'; 

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.css']
})
export class HomeComponent implements OnInit {
  email='brian.ja.schardt@gmail.com';
  password="test";
  constructor(private authService: AuthService,private httpService: HttpService, private dataService: DataService, private utilService: UtilService) { }

  ngOnInit() {
  	console.log('testing');
    var token = this.utilService.returnGetParameter('token');
    var flow = this.utilService.returnGetParameter('flow');
    console.log(token);
    if(token != null) localStorage.setItem('token', token);
  }

  onLogin(){
    this.authService.login(this.email, this.password)
      .then(()=>{
      console.log('done logging in');
      })
      .then(()=>{
        console.log('testing')
      })
  }

  onSign(){
    console.log('Sign In');
  }

  onTestAuth(){
    this.authService.testAuth();
  }

  onFacebook(){
    this.authService.loginFacebook();
  }
}
