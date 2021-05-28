import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { PartialVideoComponent } from './partial-video.component';

describe('FormMultimediaMetadataComponent', () => {
  let component: PartialVideoComponent;
  let fixture: ComponentFixture<PartialVideoComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ PartialVideoComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(PartialVideoComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
