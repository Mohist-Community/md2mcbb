<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>@import "https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css";
        @import "https://cdn.jsdelivr.net/npm/admin-lte@2.4.18/dist/css/AdminLTE.min.css";
        @import "https://cdn.jsdelivr.net/npm/admin-lte@2.4.18/dist/css/skins/_all-skins.min.css";

        html {
            height: 100%;
        }

        *[onclick]:not([onclick=""]) {
            cursor: pointer;
        }

        body {
            font-family: '微软雅黑 Light', 'Noto Sans SC', sans-serif;
            font-weight: 200;
            background-color: #f1f1f1;
            height: 100%;

            background-repeat: no-repeat;
            background-size: cover;
        }

        a {
            text-decoration: none;
        }

        ::-webkit-scrollbar {
            width: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #1396FF;
        }

        .wrapper {
            box-sizing: border-box;
            padding-top: 60px;
        }
    </style>

    <title>Markdown2BBCode</title>
</head>
<body>
<div class="wrapper">
    <div class="container">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    模板选择
                </h3>
            </div>
            <div class="box-body">
                @foreach($parsers as $parser)
                    <li>
                        <a href="{{ route('input',['parser'=>$parser]) }}">{{ $parser }}</a>
                    </li>
                @endforeach
            </div>
        </div>
    </div>
</div>
</body>
</html>
