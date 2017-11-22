import { TestBed, inject } from '@angular/core/testing';

import { HttpModule }      from '@angular/http';

import { BreakService } from './break.service';

describe('BreakService', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [HttpModule],
      providers: [BreakService]
    });
  });

  it('should ...', inject([BreakService], (service: BreakService) => {
    expect(service).toBeTruthy();
  }));
});
