import {Component, EventEmitter, Input, Output, OnInit, forwardRef, HostBinding, ElementRef, AfterContentInit} from '@angular/core';
import { NG_VALUE_ACCESSOR, ControlValueAccessor} from '@angular/forms';
import { Event } from '@angular/router';

import {isNotNil, hasIn} from 'ramda';

export interface IvalueSwitch {
  valueTrue: string;
  valueFalse: string;
}


@Component({
  selector: 'xdam-input-switch',
  templateUrl: './input-switch.component.html',
  styleUrls: ['./input-switch.component.scss'],
  providers: [     
    {       provide: NG_VALUE_ACCESSOR, 
            useExisting: forwardRef(() => InputSwitchComponent),
            multi: true     
    }
  ]
})
export class InputSwitchComponent implements AfterContentInit, ControlValueAccessor  {
 //@Input('value') val: string;
  @Input('value') checked: any;
  @Input('defaultValues') defaultValues: IvalueSwitch;
  
  isChecked: boolean = true;

  ngOnChanges(changes){
    if(hasIn('resourceUrl', changes)){
      this.actualiceCheced(this.checked);
    }
  }

  ngAfterContentInit(){
    /*if(this.checked != undefined){
      this.actualiceCheced(this.checked);
      this.onChange(this.value)
      this.onTouched();
    }*/
  }

  actualiceCheced(val){
    if(val == undefined || val == null){
      this.isChecked = false;
    }else if((typeof val) != undefined && (typeof val) === 'boolean' ){
      this.isChecked = val;
    }else if((typeof val) != 'boolean' && this.defaultValues != undefined){
      if(this.defaultValues.valueTrue === val){
        this.isChecked = true;
      }else if(this.defaultValues.valueFalse === val){
        this.isChecked = false;
      }else{
        this.isChecked = false;
      }
    }else{
      this.isChecked = false;
    }
  }
  
  get value():(string | boolean) {
    if(this.defaultValues === undefined){
      return this.isChecked;
    }else{
      return this.isChecked ? this.defaultValues.valueTrue : this.defaultValues.valueFalse;
    }
    
  }

  set value(val: (string | boolean)) {
    this.actualiceCheced(val);
    this.onChange(val);
    this.onTouched();
  }
  
  onChange: any = () => { };
  onTouched: any = () => { };

  registerOnChange(fn) {
    this.onChange = fn;
  }

  registerOnTouched(fn) { 
    this.onTouched = fn;
  }

  writeValue(value) {
    if(value != null && value != undefined){
      this.actualiceCheced(value);
    }
  }
  changeSwitch(e){
    this.isChecked = !this.isChecked;
    console.log(this.isChecked)
    this.onChange(this.value);
    this.onTouched();
    e.preventDefault();
  }
}
