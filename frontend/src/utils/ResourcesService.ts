import { Cookies } from 'react-cookie';
import { COURSE, BOOK, MULTIMEDIA, IMAGE, VIDEO, AUDIO, ACTIVITY, ASSESSMENT } from '../constants';

class ResourcesService {

    constructor() 
    {

    }

    getMultimediaActions()
    {
      return {
        create: { href: '', label: '' } 
      }
    }

    getBookActions(resourceId)
    {
      let baseUrl = process.env.REACT_APP_BOOK_EDITOR_URL;
      let edit = 'edit/';
      let convert = 'convert/';
      let token = 'vd9NxuORVjd8xlkZfqAfEQjJw4rXuuPEVysaEV1T';
      let rid = resourceId + '/';
      
      let ob = {
        edit: { label: 'Edit', href: baseUrl + edit + rid + token },
        convert: { label: 'Convert', href: baseUrl + convert + rid + token },
      } 
      
      return ob;
    }

    getActivityActions()
    {
      return {
        create: { href: '', label: '' } 
      }
    }

    getAssessmentActions()
    {
      return {
        create: { href: '', label: '' } 
      }
    }

    getCourseActions()
    {
      return {
        create: () => {
          window.open(process.env.REACT_APP_COURSE_EDITOR_URL, '_blank').focus()
        },
        edit: { href: process.env.REACT_APP_COURSE_EDITOR_URL, label: 'Editor' }
      }
    }

    getActions(resource) {
      let ra = {}

      switch (resource.type) {
        case COURSE:
          ra = this.getCourseActions();
          break;
        case BOOK:
          ra = this.getBookActions(resource.id);
          break;
        case MULTIMEDIA:
        case IMAGE:
        case VIDEO:
        case AUDIO:
          ra = this.getMultimediaActions();
          break;
        case ACTIVITY:
          ra = this.getActivityActions();
          break;
        case ASSESSMENT:
          ra = this.getAssessmentActions();
          break;
        default:
          throw new Error('resource type invalid: ' + resource.type)
          break;
      }

      return ra;
    }
}

export default function ResourcesActions()
{
  return new ResourcesService()
}
