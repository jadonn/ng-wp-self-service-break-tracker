import { Component, OnInit } from '@angular/core';

import { AdminService } from '../admin.service';

@Component({
  selector: 'app-lock-breaks',
  templateUrl: './lock-breaks.component.html',
  styleUrls: ['./lock-breaks.component.css']
})
export class LockBreaksComponent implements OnInit {

  private breakLockStatus: boolean;
  private breakLockStatusMessage: string;
  private toggleBreakText: string;
  constructor(private adminService: AdminService) { }

  ngOnInit() {
    this.getBreakLockStatus();
  }

  getBreakLockStatus() {
    this.adminService.getBreakLockStatus().subscribe( ( response ) => {
      if ( response.success ) {
        this.breakLockStatus = response.result;
        this.updateBreakLockStatusText();
      }else {
        console.log( response.result );
      }
    })
  }

  updateBreakLockStatusText() {
    if ( this.breakLockStatus ) {
      this.breakLockStatusMessage = 'Breaks are currently locked.';
      this.toggleBreakText = 'Unlock Breaks';
    }
    if ( this.breakLockStatus === false ) {
      this.breakLockStatusMessage = 'Breaks are not currently locked.';
      this.toggleBreakText = 'Lock Breaks';
    }
  }

  lockBreaks() {
    this.adminService.lockBreaks().subscribe( ( response ) => {
      if ( response.success ) {
        this.getBreakLockStatus();
      }else {
        console.log( response.result );
      }
    });
  }

  unlockBreaks() {
    this.adminService.unlockBreaks().subscribe( ( response ) => {
      if ( response.success ) {
        this.getBreakLockStatus();
      }else {
        console.log( response.result );
      }
    });
  }

}
