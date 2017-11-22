import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { HttpModule } from '@angular/http';

import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { AppComponent } from './app.component';
import { ListComponent } from './list/list.component';
import { RequestComponent } from './request/request.component';

import { BreakService }   from './break.service';
import { WindowService } from './window.service';

@NgModule({
  declarations: [
    AppComponent,
    ListComponent,
    RequestComponent
  ],
  imports: [
    BrowserModule,
    FormsModule,
    HttpModule,
    NgbModule.forRoot()
  ],
  providers: [BreakService, WindowService],
  bootstrap: [AppComponent]
})
export class AppModule { }
