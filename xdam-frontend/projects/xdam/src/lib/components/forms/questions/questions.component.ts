import { QuestionBaseComponent } from './question.component.base';
import { Component, Input, HostBinding } from '@angular/core';
import { Base as QuestionBase } from './../../../models/forms/questions/Base';
import { Text as QuestionText } from '../../../models/forms/questions/Text';

@Component({
    selector: 'xdam-question',
    templateUrl: './questions.component.html',
    styleUrls: ['./questions.component.scss']
})
export class QuestionsComponent extends QuestionBaseComponent<any> {
    @Input() question: QuestionBase<any> | QuestionText;

    @HostBinding('class.field-item') baseClass = true;

    protected defaultValue: any = null;

    public changeValue(value: any = null) {
        return this.onChange.emit(value);
    }
}
