import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ModalMultimediaComponent } from './modal-multimedia.component';

describe('ModalCourseComponent', () => {
  let component: ModalMultimediaComponent;
  let fixture: ComponentFixture<ModalMultimediaComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ModalMultimediaComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ModalMultimediaComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
