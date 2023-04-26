<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0">
        <title>STEP | あなたの人生のSTEPを共有しよう</title>
        <!-- reset.cssの読み込み-->
        <link rel="stylesheet" href="https://unpkg.com/ress@4.0.0/dist/ress.min.css">
        <!-- googleFonts・font-awesome・cssの読み込み-->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" rel="stylesheet">
        <link href="{{ mix('/css/app.css') }}" rel="stylesheet">

    </head>
    <body>
        <div id="app" ontouchstart>
            <index-component></index-component>
        </div>
    </body>
    <script src="{{ mix('/js/app.js') }}" defer></script>
</html>
