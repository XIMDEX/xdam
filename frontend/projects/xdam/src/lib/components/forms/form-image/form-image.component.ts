import {Component, Input, OnInit} from '@angular/core';
import {FormControl, FormGroup} from '@angular/forms';

@Component({
    selector: 'xdam-form-image',
    templateUrl: './form-image.component.html',
    styleUrls: ['./form-image.component.scss']
})
export class FormImageComponent implements OnInit {

    @Input() modal: any;


    public formImage: FormGroup = new FormGroup({
        lomes: new FormControl('')
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
