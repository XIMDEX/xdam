import {Component, Input, OnInit} from '@angular/core';

@Component({
  selector: 'xdam-thumbnail',
  templateUrl: './thumbnail.component.html',
  styleUrls: ['./thumbnail.component.scss']
})
export class ThumbnailComponent implements OnInit {

    @Input() src: string;

  constructor() { }

  ngOnInit() {
  }

}
