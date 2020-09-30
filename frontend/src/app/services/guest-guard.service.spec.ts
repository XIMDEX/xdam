import { TestBed } from '@angular/core/testing';

import { GuestGuardService } from './guest-guard.service';

describe('GuestGuardService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: GuestGuardService = TestBed.get(GuestGuardService);
    expect(service).toBeTruthy();
  });
});
