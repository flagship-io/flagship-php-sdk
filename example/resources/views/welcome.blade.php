<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Laravel</title>
    </head>
    <body class="antialiased">
    <h1>Hello World</h1>
    <p>This is a laravel demo using Flagship for the visitorID : <span style="color: red;">{{$visitorId}}</span> assigned on flag <span style="color: red;">{{$myFlagValue}}</span>.</p>
    </body>
</html>
