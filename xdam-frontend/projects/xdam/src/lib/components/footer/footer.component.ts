import { Component } from '@angular/core';
import { version, homepage, author } from '../../../package.json';

@Component({
    selector: 'xdam-footer',
    templateUrl: './footer.component.html',
    styleUrls: ['./footer.component.scss']
})
export class FooterComponent {
    public version: string = version;
    public homepage: string = homepage;
    public author: { name: string; url: string } = author;
}
