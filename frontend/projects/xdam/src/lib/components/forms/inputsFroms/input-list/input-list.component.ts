import { AfterContentInit, AfterViewInit, Component, forwardRef, Input, OnInit, SimpleChange } from '@angular/core';
import { NG_VALUE_ACCESSOR, ControlValueAccessor } from '@angular/forms';

import { faTimes } from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'xdam-input-list',
  templateUrl: './input-list.component.html',
  styleUrls: ['./input-list.component.scss'],
  providers: [     
    {       provide: NG_VALUE_ACCESSOR, 
            useExisting: forwardRef(() => InputListComponent),
            multi: true     
    }
  ]
})
export class InputListComponent implements AfterContentInit, ControlValueAccessor{
  fatimes = faTimes;

  currentValue: string[] = [];

  @Input('value') inputValue: [string];

  onChange: any = () => { };
  onTouched: any = () => { };

  ngOnChanges(changes: SimpleChange){
    if(!changes['inputValue'].isFirstChange()){
      this.value = this.inputValue;
      this.onChange(this.value);
      this.onTouched();
    }
  }

  ngAfterContentInit(){
    if(this.inputValue  != undefined){
      this.value = this.inputValue;
      this.onChange(this.value);
      this.onTouched();
    }
  }

  set value(value:string[]){
    if(value == undefined){
      this.currentValue = []
    }else{
      this.currentValue = value;
    }
  }

  get value():string[]{
    return this.currentValue;
  }

  addListItem(e, newItem){
    e.preventDefault()
    if(newItem.value === "") return;

    this.currentValue.push(newItem.value);
    newItem.value = "";
    this.onChange(this.value);
    this.onTouched();
  }

  deleteListItem(i){
    this.currentValue.splice(i, 1);
    this.onChange(this.value);
    this.onTouched();
  }

  registerOnChange(fn) {
    this.onChange = fn;
  }

  registerOnTouched(fn) { 
    this.onTouched = fn;
  }

  writeValue(value:string[]) {
    if (value != undefined) {
      this.value = value;
    }
  }
}
