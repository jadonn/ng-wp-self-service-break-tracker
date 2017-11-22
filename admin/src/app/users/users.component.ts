import { Component, OnInit } from '@angular/core';

import { AdminService } from '../admin.service';

@Component({
  selector: 'app-users',
  templateUrl: './users.component.html',
  styleUrls: ['./users.component.css']
})
export class UsersComponent implements OnInit {

  private addUserName = '';
  private deleteUserName = '';
  private addUserMessage = '';
  private deleteUserMessage = '';
  private users = [];

  constructor( private adminService: AdminService ) { }

  ngOnInit() {
    this.getUsers();
  }

  getUsers() {
    this.adminService.getUsers().subscribe( ( response ) => {
      if ( response.success ) {
        this.users = response.result;
      }else {
        console.log( response.result );
      }
    });
  }

  addUser( userName ) {
    this.adminService.addUser( userName ).subscribe( ( response ) => {
      if ( response.success ) {
        this.getUsers();
        this.addUserMessage = userName + ' was successfully added!';
        setTimeout( () => {
          this.addUserMessage = '';
        }, 5000);
      }else {
        console.log( response.result );
        this.addUserMessage = response.result;
      }
    });
    this.addUserName = '';
  }

  deleteUser( userName ) {
    this.adminService.deleteUser( userName ).subscribe( ( response ) => {
      if ( response.success ) {
        this.getUsers();
        this.deleteUserMessage = userName + ' was successfully deleted.';
        setTimeout( () => {
          this.deleteUserMessage = '';
        }, 5000);
      }else {
          console.log( response.result );
          this.deleteUserMessage = response.result;
      }
      this.deleteUserName = '';
    });
  }

}
