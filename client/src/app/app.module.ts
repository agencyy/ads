import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { HttpModule } from '@angular/http';
import { FormsModule } from '@angular/forms';

import {BrowserAnimationsModule} from '@angular/platform-browser/animations';
import {MdGridListModule, MdInputModule, MdButtonModule, MdCheckboxModule, MdSidenavModule} from '@angular/material';

import { routing } from './app.routing';

import { AppComponent } from './app.component';

//services
import { HttpService } from './services/http.service';
import { UtilService } from './services/util.service';
import { DataService } from './services/data.service';
import { AuthService } from './services/auth.service';
//Components
import { TestComponent } from './test/test.component';
import { HomeComponent } from './basic/home/home.component';
import { MainMenuComponent } from './partials/main-menu/main-menu.component';

@NgModule({
  declarations: [
    AppComponent,
    TestComponent,
    HomeComponent,
    MainMenuComponent
  ],
  imports: [
    BrowserModule,
    HttpModule,
    FormsModule,
    routing,
    BrowserAnimationsModule,
    MdGridListModule,
    MdButtonModule,
    MdCheckboxModule,
    MdSidenavModule,
    MdInputModule,
  ],
  providers: [
  	HttpService,
    UtilService,
    DataService,
    AuthService,
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
