FIRST: copy .env.example to each folder as .env and fill urls

SECOND: All builds must be executed by npm, otherwise you will get path errors. Check package.json for available scripts

examples:
"npm run build:all" will build all projects / environments
"npm run build:mhe:pre" will build only the project mhe-pre with their environment