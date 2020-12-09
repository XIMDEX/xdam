import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { LomesComponent } from './lomes.component';

describe('LomesComponent', () => {
  let component: LomesComponent;
  let fixture: ComponentFixture<LomesComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ LomesComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(LomesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
