import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { PartialDocumentComponent } from './partial-document.component';

describe('FormMultimediaMetadataComponent', () => {
  let component: PartialDocumentComponent;
  let fixture: ComponentFixture<PartialDocumentComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ PartialDocumentComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(PartialDocumentComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
