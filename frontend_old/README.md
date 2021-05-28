# XDAM front-end

Front end application made with Angular 7 for Ximdex Digital Asset Management service (XDAM).

## Description

This front-end provides a flexible and easy-to-use user interface for listing, uploading, editing and managing assets across your indexed resources from remote repositories (via Ximdex's XDAM backend).

## Features

* Responsiveness: Optimized to be used with most devices and screen sizes.
* Modularity: Due to its modular nature it can be integrated in any project with minimum effort.
* Lightness: With almost no external dependencies its load time aims to be minimal.

## Availability

This project is open source with AGPL v3 (see 'LICENSE')

## Environments and Api Routing
There are 3 defined environments, default, production and pre-production.
Inside the environments folder you can modify the path to which each environment points
To switch between environments you have to add the flag --configuration.
if you don't add any flag, the default environment will be used.

## Build

Run `ng build` to build the project. The build artifacts will be stored in the `dist/` directory. 
Use the `--configuration preproduction` flag for a pre-production build.
Use the `--configuration production` flag for a production build.

## Serve

Run `ng serve` to server the project (for debug purposes).
Use the `--configuration preproduction` flag for a pre-production serve.
Use the `--configuration production` flag for a production serve.

## Additional Documentation
[Technical Documentation for XDAM at Ximdex website](https://www.ximdex.com/en/documentation/xdam/)


## Changes Dec 2020

* We delete the JSON dependency for store resource metadata fields (Lomes tab), stored in index.html.
* Now we have a form for every resource type in xdam system. **ItemComponent** have a **ModalCourseComponent** and this modal is composed by two components, **FormCourseComponent** and **MetadataComponent**,
every form have specific data of the resource.
* Added a Mapper class named EndPointMapper. This class load a JSON `endpoints.config.json`, that contains all endpoints of api, when you convoke it method ```getEndPointUrl(model: string, action: string, item: Item = null)```, it return a URL endpoint. It load default params to show init items on home page.
* When new asset is wanted to create, **ItemComponent** load a specified form **ModalNewComponent** set by URL or JSON.
* Modified ItemModel to adjust on actual model.
* Move SettingsMapper structure on isolated JSON `settings.config.json`. XDAM init with this structure.
