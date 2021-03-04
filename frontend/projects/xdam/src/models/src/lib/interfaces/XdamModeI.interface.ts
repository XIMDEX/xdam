//export enum XdamMode {Course = 'course' ,  Multimedia = 'multimedia'};
export interface availableModeI{
    name: string;
    id: number;
}

export interface XdamModeI{
    availableModes: availableModeI[];
    currentMode: availableModeI;
}

