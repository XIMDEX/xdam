import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ModalCourseComponent } from './modal-course.component';

describe('FormCourseComponent', () => {
  let component: ModalCourseComponent;
  let fixture: ComponentFixture<ModalCourseComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ModalCourseComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ModalCourseComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
