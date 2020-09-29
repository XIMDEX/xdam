export interface FormI {
    name: string;
    fields: FieldI[];
}

export interface FieldI {
    type: string;
    object: any;
}
