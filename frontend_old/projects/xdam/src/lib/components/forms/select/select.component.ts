import {Component, OnInit, Input, Output, EventEmitter} from '@angular/core';

@Component({
  selector: 'xdam-select',
  templateUrl: './select.component.html',
  styleUrls: ['./select.component.scss']
})
export class SelectComponent implements OnInit {

  @Input() id: string;
  @Input() name: string;
  @Input() key: string;
  @Input() label: string;
  @Input() options: [];

  @Output() valueChange = new EventEmitter<string>();

  selectValue: string;

  selectClass = 'field-item';

  constructor() {}

  ngOnInit() {
  }

  @Input()
  set value(val: string) {
    this.selectValue = val;
  }

  saveChanges() {
    return this.valueChange.emit(this.selectValue);
  }

}
