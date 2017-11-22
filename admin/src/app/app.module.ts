import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { HttpModule } from '@angular/http';

import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { AppComponent } from './app.component';

import { AdminService } from './admin.service';
import { WindowService } from './window.service';
import { LeadersComponent } from './leaders/leaders.component';
import { UsersComponent } from './users/users.component';
import { LockBreaksComponent } from './lock-breaks/lock-breaks.component';
import { BreaksComponent } from './breaks/breaks.component';

@NgModule({
  declarations: [
    AppComponent,
    LeadersComponent,
    UsersComponent,
    LockBreaksComponent,
    BreaksComponent
  ],
  imports: [
    BrowserModule,
    FormsModule,
    HttpModule,
    NgbModule.forRoot()
  ],
  providers: [AdminService, WindowService],
  bootstrap: [AppComponent]
})
export class AppModule { }
