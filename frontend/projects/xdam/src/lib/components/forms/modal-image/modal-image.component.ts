import {Component, Input, OnInit} from '@angular/core';
import {FormControl, FormGroup} from '@angular/forms';
import { faSave, faTimes } from '@fortawesome/free-solid-svg-icons';
import {ActionModel} from '../../../../models/src/lib/ActionModel';

@Component({
    selector: 'xdam-modal-image',
    templateUrl: './modal-image.component.html',
    styleUrls: ['./modal-image.component.scss']
})
export class ModalImageComponent implements OnInit {

    @Input() modal: any;
    @Input() action: ActionModel;

    faSave = faSave;
    faTimes = faTimes;

    public formImage: FormGroup = new FormGroup({
        metadataForm: new FormControl('')
    });

    constructor() {
    }

    ngOnInit() {
    }

    public onSubmit() {
        console.log('Billing Info', this.formImage.value);
    }
}
