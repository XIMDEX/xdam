import { ComponentFixture, TestBed } from '@angular/core/testing';

import { InputCategoriesComponent } from './input-categories.component';

describe('InputCategoriesComponent', () => {
  let component: InputCategoriesComponent;
  let fixture: ComponentFixture<InputCategoriesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ InputCategoriesComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(InputCategoriesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
