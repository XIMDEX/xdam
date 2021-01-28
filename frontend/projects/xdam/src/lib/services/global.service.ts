import { Injectable, Output, EventEmitter} from '@angular/core';
import { XdamMode } from '@xdam/models/interfaces/XdamMode.interface';

@Injectable({
    providedIn: 'root'
})
export class GlobalService {
    
    @Output() modeChange: EventEmitter<XdamMode> = new EventEmitter();
    
    xdamMode: XdamMode;

    constructor() { }

    setMode(newXdamMode: XdamMode){
        this.xdamMode = newXdamMode;
        this.modeChange.emit(this.xdamMode);
    }
}
