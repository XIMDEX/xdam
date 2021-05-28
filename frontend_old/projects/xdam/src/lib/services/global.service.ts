import { Injectable, Output, EventEmitter} from '@angular/core';
import { XdamModeI, availableModeI } from '@xdam/models/interfaces/XdamModeI.interface';

@Injectable({
    providedIn: 'root'
})
export class GlobalService {
    
    @Output() modeChange: EventEmitter<availableModeI> = new EventEmitter();
    
    xdamMode: availableModeI;

    constructor() { }

    setMode(newXdamMode: availableModeI){
        this.xdamMode = newXdamMode;
        this.modeChange.emit(this.xdamMode);
    }
}
