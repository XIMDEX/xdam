import { QuestionBaseComponent } from './../question.component.base';
import { isNil } from 'ramda';
import { Component, Input } from '@angular/core';
import { Text as QuestionText } from './../../../../models/forms/questions/Text';

@Component({
    selector: 'xdam-text',
    templateUrl: './text.component.html',
    styleUrls: ['./text.component.scss']
})
export class TextComponent extends QuestionBaseComponent<string> {
    @Input() question: QuestionText;

    protected defaultValue = '';

    get multiline(): boolean {
        return !isNil(this.question.multiline) ? this.question.multiline : false;
    }
}
