<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'PDF Viewer' }}</title>
        <script src="//mozilla.github.io/pdf.js/build/pdf.js"></script>
        <style>
            body {
                background-color: #cfcfcf;
                height: 100%;
                margin: 0 auto;
                width: 100%;
            }

            iframe {
                border: none;
                bottom: 0px;
                height: 100%;
                left: 0px;
                margin: 0;
                overflow: hidden;
                padding: 0;
                position: fixed;
                top: 0px;
                width: 100%;
                z-index: 999999;
            }
        </style>
    </head>
    <body>
        <iframe id="viewer-frame" src="/ViewerJS/index.html?title={{ $title }}#{{ $url }}"></iframe>
        <script>
            document.getElementById('viewer-frame').onload = function() {
                function delay(time) {
                    return new Promise(resolve => setTimeout(resolve, time));
                }

                delay(5000).then(() => console.clear());
            };
        </script>
    </body>
</html>
