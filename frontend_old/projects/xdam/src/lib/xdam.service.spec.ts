import { TestBed } from '@angular/core/testing';

import { XDamService } from './component.service';

describe('XDamService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: XDamService = TestBed.get(XDamService);
    expect(service).toBeTruthy();
  });
});
