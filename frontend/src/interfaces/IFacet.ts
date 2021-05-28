interface IFacetValues {
    count: number,
    selected: boolean
}

interface IFacet {
    key: string,
    label: string,
    values: Record<string, IFacetValues>;
}

export default IFacet;

