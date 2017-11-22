import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { MaterialModule }   from '@angular/material';

import { FormsModule }      from '@angular/forms';

import { RequestComponent } from './request.component';

import { BreakService }     from '../break.service';

import { HttpModule }       from '@angular/http';

import { Observable }       from 'rxjs/Observable';

describe('RequestComponent', () => {
  let component: RequestComponent;
  let fixture: ComponentFixture<RequestComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      imports: [MaterialModule, FormsModule, HttpModule],
      declarations: [ RequestComponent ],
      providers: [BreakService]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RequestComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
    let breakService = fixture.debugElement.injector.get(BreakService);
    let spy = spyOn(breakService, 'submitBreak').and.returnValue(Observable.create(function(observer){
      observer.next({success: true});
      observer.complete();
    }));
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
  it('should have a form', ()=>{
    fixture.detectChanges();
    const compiled = fixture.debugElement.nativeElement;
    expect(compiled.querySelector('form')).toBeTruthy();
  });
  it('should submit a request', async(()=>{
    component.onSubmit({username: "Test"});
    fixture.whenStable().then(()=>{
      fixture.detectChanges();
      expect(component.requestSubmitted).toBeTruthy();
    });
  }));
});
