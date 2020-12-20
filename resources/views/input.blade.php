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
                        <h3 class="box-title">Markdown to BBCode | <a href="{{ route('welcome') }}">当前模板：{{ $parser }}</a></h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            {!! (new \Parsedown())->parse($describe) !!}
                        </div>
                        <div class="form-group">
                            <label for="markdown_input">输入你的 Markdown</label>
                            <textarea style="resize: none;" id="markdown_input" rows="14" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button id="to_bbcode" type="button" class="btn btn-primary">
                            <i class="glyphicon glyphicon-send"></i>&nbsp;
                            转换到 BBCode
                        </button>
                        <button id="clean_markdown" type="button" class="btn btn-danger">
                            <i class="glyphicon glyphicon-repeat"></i>&nbsp;
                            清空
                        </button>
                    </div>
                </div>

                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">BBCode</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label for="bbcode_output">转换的BBCode</label>
                            <textarea style="resize: none;" id="bbcode_output" rows="10" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button id="copy_bbcode" type="button" class="btn btn-primary">
                            <i class="glyphicon glyphicon-copy"></i>&nbsp;
                            复制
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/jquery@2.2.4/dist/jquery.min.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                $("#to_bbcode").click(function () {
                    $.ajax({
                        method: 'POST',
                        data: {
                            'markdown': $("#markdown_input").val(),
                            'parser': "{{ $parser }}"
                        },
                        processData: true,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                    }).done(function (data) {
                        $("#bbcode_output").val(data);
                    });
                });

                $("#clean_markdown").click(function () {
                    $("#markdown_input").val('');
                });

                $("#copy_bbcode").click(function () {
                    let bbcodeArea = $("#bbcode_output");
                    bbcodeArea.select();
                    bbcodeArea.setSelectionRange(0, 99999);
                    document.execCommand("SelectAll");
                    document.execCommand("Copy");
                });
            });
        </script>
    </body>
</html>
