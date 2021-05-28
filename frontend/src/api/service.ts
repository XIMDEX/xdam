import api from './urlMapper'
import { Cookies } from 'react-cookie';
import * as ponyfill from 'web-streams-polyfill/ponyfill';
const streamSaver = require('streamsaver')

class AppService {
    /**
     * Dict containing options for using with the http client
     */
    private httpOptions = { headers: {} as any, headersForm: {}, params: {} };
    private cookies: Cookies;
    /**
     * @ignore
     */
    constructor() 
    {
        this.cookies = new Cookies()
        this.httpOptions.headers = {
            'Content-Type': 'application/json',
            Authorization: this.getToken()
        };

        this.httpOptions.headersForm = {
          Authorization: this.getToken()
        };

    }

    getToken()
    {
      return this.cookies.get('JWT') ? 'Bearer ' + this.cookies.get('JWT') : null
    }

    setToken(name, value)
    {
      return this.cookies.set(name, value);
    }

    async login (email: String, password: String) 
    {
      const request = {
        method: api().login.method,
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          email: email, // Use your own property name / key
          password: password,
        }),
      }
      const res = await fetch(api().login.url, request);
      const resToJson = await res.json();
    
      return resToJson;
    }

    async logout()
    {
      const request = {
        method: api().logout.method,
        headers: this.httpOptions.headers,
        body: JSON.stringify({}),
      }
      const res = await fetch(api().logout.url, request);
      const resToJson = await res.json();
      if (resToJson.code === 200) {
        this.cookies.remove('JWT');
        window.location.assign('/');
      } else {
        alert('error on logout');
        throw new Error(JSON.stringify(resToJson));
      }
    }
    
    async getUser () 
    {
      const request = {
        method: api().getUser.method,
        headers: this.httpOptions.headers
      }
      const res = await (await fetch(api().getUser.url, request)).json();
      return res;
    }

    async getSchemas () 
    {
      const request = {
        method: api().getSchemas.method,
        headers: this.httpOptions.headers
      }
      const res = await (await fetch(api().getSchemas.url, request)).json();
      return res;
    }

    async getLomesSchema () 
    {
      const request = {
        method: api().getSchemas.method,
        headers: this.httpOptions.headers
      }
      const res = await (await fetch(api().getLomesSchema.url, request)).json();
      return res;
    }

    async postLomesData (resource_id, body) {
      const _api = api().postLomesData(resource_id)
      const request = {
        method: _api.method,
        headers: this.httpOptions.headers,
        body: JSON.stringify(body),
      }
      const res = await (await fetch(_api.url, request)).json();
      return res;
    }

    async getLomesData (resource_id) 
    {
      const _api = api().getLomesData(resource_id)
      const request = {
        method: _api.method,
        headers: this.httpOptions.headers,
      }
      const res = await (await fetch(_api.url, request)).json();
      return res;
    }

    async getResource (resource_id) 
    {
      const _api = api().getResource(resource_id)
      const request = {
        method: _api.method,
        headers: this.httpOptions.headers,
      }
      const res = await (await fetch(_api.url, request)).json();
      return res;
    }

    async createResource (body) 
    {
      const request = {
        method: api().createResource.method,
        headers: this.httpOptions.headersForm,
        body: body
      }
      const res = await (await fetch(api().createResource.url, request)).json();
      return res;
    }

    createBatchOfResources (formData) 
    {
      const _api = api().createBatchOfResources;
      const request = {
        method: _api.method,
        headers: this.httpOptions.headersForm,
        body: formData
      }
      return {_api, request};
    }

    async updateResource (id, body) {
      const _api = api().updateResource(id)
      const request = {
        method: _api.method,
        headers: this.httpOptions.headersForm,
        body: body
      }
      const res = await (await fetch(_api.url, request)).json();
      return res;
    }

    async updateResourceFromLastCreated (resource_to_update) {
      const _api = api().updateResourceFromOther(resource_to_update, true, false, null)
      const request = {
        method: _api.method,
        headers: this.httpOptions.headers
      }
      const res = await (await fetch(_api.url, request)).json();
      return res;
    }

    async updateResourceFromLastUpdated (resource_to_update) {
      const _api = api().updateResourceFromOther(resource_to_update, false, true, null)
      const request = {
        method: _api.method,
        headers: this.httpOptions.headers
      }
      const res = await (await fetch(_api.url, request)).json();
      return res;
    }

    async getLastResource(collection_id, time) 
    {
      enum AVAILABLES {
        lastCreated = 'lastCreated',
        lastUpdated = 'lastUpdated'
      };
      console.log(time in AVAILABLES)
      if (!(time in AVAILABLES)) {
        throw new Error('invalid parameter: check enum AVAILABLES')
      }

      const _api = api().getLastResource(collection_id, time);
      const request = {
        method: _api.method,
        headers: this.httpOptions.headers
      }
      const res = await (await fetch(_api.url, request)).json();
      return res;
    }

    async removeResource (id) 
    {
      const _api = api().removeResource(id)
      const request = {
        method: _api.method,
        headers: this.httpOptions.headers,
      }
      const res = await fetch(_api.url, request)
      return res;
    }

    async removeMedia (dam_id, media_id) 
    {
      const _api = api().removeMedia(dam_id, media_id)
      const request = {
        method: _api.method,
        headers: this.httpOptions.headers,
      }
      const res = await(await fetch(_api.url, request)).json()
      return res;
    }
    
    async getCatalogue(id: number, query: string) 
    {
      const _api = api().getCatalog(id);
      const request = {
        method: _api.method,
        headers: this.httpOptions.headers
      }
      const res = await (await fetch(_api.url + query , request)).json();
      return res;
    }

    async setWorkspace(id: number) 
    {
      const _api = api().setWorkspace;
      const request = {
        method: _api.method,
        headers: this.httpOptions.headers,
        body: JSON.stringify({workspace_id: id}),
      }
      const res = await (await fetch(_api.url, request)).json();
      return res;
    }

    render(damUrl: string): string
    {
      const _api = api().render(damUrl);
      const res = _api.url
      return res;
    }

    async downloadFile(file) 
    {
      const _api = api().downloadFile(file.dam_url);
      const request = {
        method: _api.method,
        headers: this.httpOptions.headers
      }
      const res = await fetch(_api.url, request); 
      console.log(res);
      streamSaver.WritableStream = ponyfill.WritableStream

      const blob = await res.blob();
      const newBlob = new Blob([blob]);

      const blobUrl = window.URL.createObjectURL(newBlob);

      const link = document.createElement('a');
      link.href = blobUrl;
      link.setAttribute('download', `${file.file_name}`);
      document.body.appendChild(link);
      link.click();
      link.parentNode.removeChild(link);

      window.URL.revokeObjectURL(blob as unknown as string);

      // const fileStream = streamSaver.createWriteStream(file.file_name);
        
      // const writer = fileStream.getWriter();

      // const reader = res.body.getReader();
      // console.log(reader);
      // const pump = () => reader.read()
      //   .then(({ value, done }) => {
      //     if (done) writer.close();
      //     else {
      //       writer.write(value);
      //       return writer.ready.then(pump);
      //     }
      //   });

      // await pump()
      //   .then(() => console.log('Closed the stream, Done writing'))
      //   .catch(err => console.log(err));
     

      return true;
    }
}

export default function MainService()
{
  return new AppService()
}
