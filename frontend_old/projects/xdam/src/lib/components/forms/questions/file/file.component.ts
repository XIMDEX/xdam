import { QuestionBaseComponent } from './../question.component.base';
import { Component, Input } from '@angular/core';
import { File as QuestionFile } from './../../../../models/forms/questions/File';

@Component({
    selector: 'xdam-file',
    templateUrl: './file.component.html',
    styleUrls: ['./file.component.scss']
})
export class FileComponent extends QuestionBaseComponent<FileList | File | any[]> {
    @Input() question: QuestionFile;

    protected defaultValue = [];

    get accept(): string {
        return this.question.accept;
    }

    get multiple(): boolean {
        return this.question.multiple;
    }

    public changeValue(evt: Event = null) {
        const { target } = evt;
        let files: FileList | File = (target as HTMLInputElement).files;
        if (!this.multiple) {
            files = (files as FileList).item(0);
        }
        return this.onChange.emit(files);
    }
}
