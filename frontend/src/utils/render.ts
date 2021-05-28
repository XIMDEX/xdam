import MainService from "../api/service";

export const render = (data, quality = 'small'): string =>
{
    const defaultImage = 'noimg.png';   
    //console.log(data)
    
    if (data.hasOwnProperty('previews')) {
        return MainService().render(data.previews[0]) + '/' + quality
    // } else if (data.data.description?.hasOwnProperty('media_upload')) {
    //     return MainService().render(data.data.description.media_upload)
    } else {
        return defaultImage
    }
}

export const renderFromUrl = (url, quality = 'small'): string =>
{
    return MainService().render(url) + '/' + quality   
}