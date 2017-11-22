import { Component, OnInit } from '@angular/core';

import { BreakService }      from '../break.service';

@Component({
  selector: 'app-request',
  templateUrl: './request.component.html',
  styleUrls: ['./request.component.css']
})
export class RequestComponent implements OnInit {

  public model = {username: '', hash: ''};
  public requestSubmitted = false;
  public requestNotification = '';
  public hideRequestNotification = true;
  public closeNotification = '';
  public hideCloseNotification = true;
  public buttonText = "Submit";

  constructor(private breakService: BreakService) { }

  ngOnInit() {
    this.updateButtonText();
  }

  onSubmit(model){
    this.breakService.submitBreak(model.username).subscribe(response =>{
      this.requestSubmitted = response.success;
      this.requestNotification = response.result;
      localStorage.setItem('self-service-break-status', 'pending');
      if(response.success){
        this.updateButtonText();
      }
      this.model.hash = '';
      this.displayRequestNotification();
    });
  }
  displayRequestNotification(){
    if(this.requestSubmitted === true){
      this.hideRequestNotification = false;
      setTimeout(()=> {
        this.requestNotification = '';
        this.hideRequestNotification = true;
      }, 5000);
    }
  }

  onClose(model){
    this.breakService.closeBreak(model.hash).subscribe(response =>{
      this.requestSubmitted = response.success;
      if(localStorage.getItem('self-service-break-status') !== null && localStorage.getItem('self-service-break-status') === 'pending'){
        localStorage.setItem('self-service-break-status', 'on break');
      } else{
        localStorage.removeItem('self-service-break-status');
      }
      this.updateButtonText();
      this.model.hash = '';
      this.closeNotification = response.result;
      this.displayCloseNotification();
    });
  }

  updateButtonText(){
    if(localStorage.getItem('self-service-break-status') !== null){
      if(localStorage.getItem('self-service-break-status') === 'pending'){
        this.buttonText = "Go to Break";
      }
      if(localStorage.getItem('self-service-break-status') === 'on break'){
        this.buttonText = "Close Break";
      }
    }else{
      this.buttonText = "Submit";
    }
  }

  displayCloseNotification(){
    if(this.requestSubmitted === true){
      this.hideCloseNotification = false;
      setTimeout(()=>{
        this.closeNotification = '';
        this.hideCloseNotification = true;
      }, 5000);
    }
  }

}
