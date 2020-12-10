import {Component, Input, OnInit} from '@angular/core';
import {FormControl, FormGroup} from '@angular/forms';
import { faSave, faTimes } from '@fortawesome/free-solid-svg-icons';

@Component({
    selector: 'xdam-modal-image',
    templateUrl: './modal-image.component.html',
    styleUrls: ['./modal-image.component.scss']
})
export class ModalImageComponent implements OnInit {

    @Input() modal: any;

    faSave = faSave;
    faTimes = faTimes;

    public formImage: FormGroup = new FormGroup({
        metadataForm: new FormControl('')
    });

    constructor() {
    }

    ngOnInit() {
    }

    cancelForm() {
        this.modal.close();
    }

    public onSubmit() {
        console.log('Billing Info', this.formImage.value);
    }
}
