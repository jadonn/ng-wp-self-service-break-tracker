import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BreaksComponent } from './breaks.component';

describe('BreaksComponent', () => {
  let component: BreaksComponent;
  let fixture: ComponentFixture<BreaksComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BreaksComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BreaksComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should be created', () => {
    expect(component).toBeTruthy();
  });
});
