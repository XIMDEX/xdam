export interface PagerOptions {
    top?: PagerOptionsItems;
    bottom?: PagerOptionsItems;
}

export interface PagerOptionsItems {
    total?: boolean;
    pager?: boolean;
    limit?: boolean;
}
