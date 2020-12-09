import { Component, OnInit } from '@angular/core';
import * as structure from './lomes.structure.json';

@Component({
  selector: 'xdam-lomes',
  templateUrl: './lomes.component.html',
  styleUrls: ['./lomes.component.scss']
})
export class LomesComponent implements OnInit {
    lomes: any;
    lomesLength: number;

  constructor() {
      this.lomes = structure;
      this.lomesLength = Object.keys(this.lomes).length;
  }

  ngOnInit() {
  }

}
