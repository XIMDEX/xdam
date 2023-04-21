<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="Image to share">
    <meta name="twitter:image" content="{{ $url }}">
    <title>{{ $title }}</title>
</head>
<body>
    @if ($fileType === 'image')
        <img src="{{ $url }}" alt="Share on Twitter">
    @elseif ($fileType === 'video')
        <video src="{{ $url }}" controls></video>
    @endif
</body>
</html>