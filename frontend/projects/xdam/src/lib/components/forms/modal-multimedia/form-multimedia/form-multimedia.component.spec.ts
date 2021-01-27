import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { FormMultimediaComponent } from './form-multimedia.component';

describe('FormMultimediaMetadataComponent', () => {
  let component: FormMultimediaComponent;
  let fixture: ComponentFixture<FormMultimediaComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ FormMultimediaComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(FormMultimediaComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
