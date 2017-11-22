import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { LockBreaksComponent } from './lock-breaks.component';

describe('LockBreaksComponent', () => {
  let component: LockBreaksComponent;
  let fixture: ComponentFixture<LockBreaksComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ LockBreaksComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(LockBreaksComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should be created', () => {
    expect(component).toBeTruthy();
  });
});
