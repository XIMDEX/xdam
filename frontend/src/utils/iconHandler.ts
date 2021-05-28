export const iconHandler = (f) => {

    if(!f.mime_type) {
        f.mime_type = f.type
    }

    if(f.mime_type.includes('image')) {
        return 'image';
    }
    if(f.mime_type.includes('audio')) {
        return 'file audio';
    }
    if(f.mime_type.includes('video')) {
        return 'video';
    }

    if(f.mime_type.includes('zip')) {
        return 'file archive';
    }

    if(f.mime_type.includes('pdf')) {
        return 'file pdf';
    }

    return 'file';
}