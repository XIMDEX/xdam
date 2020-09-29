export interface SearchModelI {
    content?: string;
    facets?: any;
    limit?: number;
    page?: number;
    reload?: boolean;
}

export interface SearchOptionsI {
    input?: SearchInputOptionsI;
    actions?: SearchInputActionsI;
}

export interface SearchInputOptionsI {
    search?: boolean;
    reset?: boolean;
    clear?: boolean;
}

export interface SearchInputActionsI {
    newAsset?: boolean;
}
