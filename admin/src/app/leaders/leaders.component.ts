import { Component, OnInit } from '@angular/core';

import { AdminService } from '../admin.service';

@Component({
  selector: 'app-leaders',
  templateUrl: './leaders.component.html',
  styleUrls: ['./leaders.component.css']
})
export class LeadersComponent implements OnInit {

  private addLeaderName = '';
  private deleteLeaderName = '';
  private addLeaderMessage = '';
  private deleteLeaderMessage = '';
  private leaders = [];

  constructor(private adminService: AdminService) { }

  ngOnInit() {
    this.getLeaders();
  }

  getLeaders() {
    this.adminService.getLeaders().subscribe( ( response ) => {
      if ( response.success ) {
        this.leaders = response.result;
      }else {
        console.log( response.result );
      }
    });
  }

  addLeader( leaderName ) {
    this.adminService.addLeader( leaderName ).subscribe( ( response ) => {
      if ( response.success ) {
        this.getLeaders();
        this.addLeaderMessage = leaderName + ' was successfully added!';
        setTimeout(function(){
          this.addLeadersMessage = '';
        }, 5000 );
      }else {
        console.log( response.result );
        this.addLeaderMessage = response.result;
      }
    });
    this.addLeaderName = '';
  }

  deleteLeader( leaderName ) {
    this.adminService.deleteLeader( leaderName ).subscribe( ( response ) => {
      if ( response.success ) {
        this.getLeaders();
        this.deleteLeaderMessage = leaderName + ' was successfully deleted.';
        setTimeout(() => {
          this.deleteLeaderMessage = '';
        }, 5000);
      }else {
        console.log( response.result );
        this.deleteLeaderMessage = response.result;
      }
      this.deleteLeaderName = '';
    });
    this.deleteLeaderName = '';
  }

}
