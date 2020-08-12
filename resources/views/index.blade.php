<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="stylesheet" href="{{ asset("/css/app.css") }}">

        <title>Markdown2BBCode</title>
    </head>
    <body>
        <div class="wrapper">
            <div class="container">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Markdown to BBCode</h3>
                    </div>
                    <div class="box-body">
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
                        url: '{{ route('bbcode.toBBCode') }}',
                        method: 'POST',
                        data: {
                            "markdown": $("#markdown_input").val()
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
