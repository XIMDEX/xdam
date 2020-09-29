export interface PagerModel {
    total?: number;
    currentPage: number;
    lastPage: number;
    nextPage?: number;
    prevPage?: number;
    perPage?: PerPageModel;
}

export interface PagerModelSchema {
    total?: string;
    currentPage?: string;
    lastPage?: string;
    nextPage?: string;
    prevPage?: string;
    perPage?: PerPageModelSchema;
}

export interface PerPageModel {
    value: number;
    items?: number[];
}

export interface PerPageModelSchema {
    value?: string;
    items?: string;
}
