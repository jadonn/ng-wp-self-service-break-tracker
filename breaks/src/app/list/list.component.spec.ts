import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { HttpModule }   from '@angular/http';

import { MaterialModule } from '@angular/material';

import { ListComponent } from './list.component';

import { BreakService } from '../break.service';

import { Observable } from 'rxjs/Observable';

describe('ListComponent', () => {
  let component: ListComponent;
  let fixture: ComponentFixture<ListComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      imports: [HttpModule, MaterialModule],
      declarations: [ ListComponent ],
      providers: [BreakService]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListComponent);
    component = fixture.componentInstance;
    let testBreakList = [
      {
        name: "Employee One",
        active: true
      }
    ];
    let breakService = fixture.debugElement.injector.get(BreakService);
    let spy = spyOn(breakService, 'getBreaks').and.returnValue(Observable.create(function(observer){
      observer.next({success: true, data: testBreakList});
      observer.complete();
    }));
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
  it('should get list of breaks', async(()=>{
    fixture.whenStable().then(()=>{
      fixture.detectChanges();
      expect(component.breaks.length).toEqual(1);
      });
  }));
});
