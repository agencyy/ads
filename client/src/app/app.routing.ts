import { Routes, RouterModule } from '@angular/router';

import { TestComponent } from "./test/test.component";
import { HomeComponent } from './basic/home/home.component';

const APP_ROUTES: Routes = [
	{ path: '', component: HomeComponent },
	{ path: 'test', component: TestComponent }
];

export const routing = RouterModule.forRoot(APP_ROUTES);