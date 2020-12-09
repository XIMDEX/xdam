import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { FormCourseComponent } from './form-course.component';

describe('FormCourseComponent', () => {
  let component: FormCourseComponent;
  let fixture: ComponentFixture<FormCourseComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ FormCourseComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(FormCourseComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
