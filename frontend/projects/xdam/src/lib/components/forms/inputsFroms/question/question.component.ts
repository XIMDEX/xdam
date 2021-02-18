import {Component, EventEmitter, Input, Output} from '@angular/core';

export interface ResultQuestionI{
  accept: boolean
}


@Component({
  selector: 'xdam-question',
  templateUrl: './question.component.html',
  styleUrls: ['./question.component.scss']
})
export class QuestionComponent {
  @Input('title') titleInput: string;
  
  @Output('accepted') eventAccept = new EventEmitter<ResultQuestionI>();

  active: boolean = false;


  get title(): String{
    return this.titleInput
  }
  
  public show(){
    this.active = true;
  }

  result(result: boolean){
    this.active = false;
    this.eventAccept.emit({"accept": result})
  }
 
}
