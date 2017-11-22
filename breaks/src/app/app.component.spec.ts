import { TestBed, async } from '@angular/core/testing';
import { Observable }     from 'rxjs/Observable';
import { FormsModule }    from '@angular/forms';

import { AppComponent } from './app.component';

import { RequestComponent }   from './request/request.component';
import { ListComponent }      from './list/list.component';
import { BreakService }       from './break.service';

describe('AppComponent', () => {
  beforeEach(async(() => {
    TestBed.configureTestingModule({
      imports: [FormsModule],
      declarations: [
        AppComponent,
        RequestComponent,
        ListComponent
      ],
      providers: [BreakService]
    }).compileComponents();
  }));

  beforeEach(() => {
    let fixture = TestBed.createComponent(ListComponent);
    let component = fixture.componentInstance;
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

  it('should create the app', async(() => {
    const fixture = TestBed.createComponent(AppComponent);
    const app = fixture.debugElement.componentInstance;
    expect(app).toBeTruthy();
  }));

  it(`should have as title 'Self Service Break List!'`, async(() => {
    const fixture = TestBed.createComponent(AppComponent);
    const app = fixture.debugElement.componentInstance;
    expect(app.title).toEqual('Self Service Break List!');
  }));

  it('should render title in a h1 tag', async(() => {
    const fixture = TestBed.createComponent(AppComponent);
    fixture.detectChanges();
    const compiled = fixture.debugElement.nativeElement;
    expect(compiled.querySelector('h1').textContent).toContain('Self Service Break List!');
  }));

  it('should have the app-list', async(()=>{
    const fixture = TestBed.createComponent(AppComponent);
    fixture.detectChanges();
    const compiled = fixture.debugElement.nativeElement;
    expect(compiled.querySelector('app-list')).toBeTruthy();
  }));

  it('should have the app-request', async(()=>{
    const fixture = TestBed.createComponent(AppComponent);
    fixture.detectChanges();
    const compiled = fixture.debugElement.nativeElement;
    expect(compiled.querySelector('app-request')).toBeTruthy();
  }));
});
