import { Component, OnInit } from '@angular/core';

import { BreakService }      from '../break.service';

@Component({
  selector: 'app-list',
  templateUrl: './list.component.html',
  styleUrls: ['./list.component.css']
})
export class ListComponent implements OnInit {

  constructor(private breakService: BreakService) { }

  public breaks = [];

  ngOnInit() {
    this.getBreaks();
  }

  getBreaks(){
    this.breakService.getBreaks().subscribe(response =>{
      this.breaks = response.result;
    });
    setTimeout(()=>{
      this.getBreaks();
    }, 30000);
  }

}
