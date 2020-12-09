import { faSave, faTimes } from '@fortawesome/free-solid-svg-icons';
import {EventEmitter, Input, Output} from '@angular/core';
import {CourseResource} from '../../models/forms/CourseResource';

export abstract class FormResourceBase {

    @Input() entryData: any;
    @Input() modal: any;
    @Output() exitData = new EventEmitter<CourseResource>();

    faSave = faSave;
    faTimes = faTimes;

    setMetadata(resource, data): void {
        resource.id = data.Id;
        resource.name = data.Name;
        resource.category = data.Category;
        resource.description = data.Description;
        resource.preview = data.Preview;
    }

    sendForm(modal, resource, event) {
        event.emit(resource);
        modal.close();
    }

    abstract setSpecificData();

    cancelForm() {
        this.modal.close();
    }
}
