import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core';

@Component({
  selector: 'xdam-input',
  templateUrl: './input.component.html',
  styleUrls: ['./input.component.scss']
})
export class InputComponent implements OnInit {

  @Input() type: string;
  @Input() id: string;
  @Input() name: string;
  @Input() label: string;

  @Output() valueChange = new EventEmitter();

  inputClass = 'field-item';
  inputValue: string;

  constructor() { }

  ngOnInit() {
  }

  @Input()
  set value(val: string) {
      this.inputValue = val;
  }

  saveChanges() {
      this.valueChange.emit(this.inputValue);
  }
}
