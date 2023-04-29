<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <meta name="description" content="あなたの人生のSTEPを共有しよう。
              STEPは、あなたの学びの「STEP」をほかのだれかに共有し、あなたも他のだれかの学びの「STEP」にチャレンジできるサービスです。">
        <title>STEP | あなたの人生のSTEPを共有しよう</title>
        <!-- reset.cssの読み込み-->
        <link rel="stylesheet" href="https://unpkg.com/ress@4.0.0/dist/ress.min.css">
        <!-- googleFonts・font-awesome・cssの読み込み-->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" rel="stylesheet">
        <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
        <!--ファビコン画像の読み込み-->
        <link rel="shortcut icon" href="{{ asset('/images/favicon.svg') }}">

    </head>
    <body>
        <div id="app" ontouchstart>
            <index-component></index-component>
        </div>
    </body>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=smoothscroll"></script>
    <script src="{{ mix('/js/app.js') }}" defer></script>
</html>
