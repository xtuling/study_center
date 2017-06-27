<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>{$title}</title>
    <link rel="stylesheet" href="/apidocassets/css/semantic.min.css">
    <link rel="stylesheet" href="/apidocassets/css/icon.min.css">
    <script type="text/javascript">
        (function () {
            var eventSupport = ('querySelector' in document && 'addEventListener' in window),
                jsonSupport = (typeof JSON !== 'undefined'),
                jQuery = (eventSupport && jsonSupport)
                    ? '/apidocassets/javascript/jquery.min.js'
                    : '/apidocassets/javascript/jquery.legacy.min.js';
            document.write('<script src="' + jQuery + '"><\/script>');
        }());
    </script>
    <script src="/apidocassets/javascript/semantic.min.js"></script>
    <script src="/apidocassets/javascript/common.js"></script>
    <script src="/apidocassets/javascript/syntax/jquery.syntax.js"></script>
    <script src="/apidocassets/javascript/syntax/jquery.syntax.cache.js"></script>

    <script type="text/javascript">
        var host = '{$host}';
        var enumber = '{$enumber}';
        $(function () {
            jQuery.syntax({theme: 'paper', blockLayout: 'fixed'});
            $('.demo .example .ui.accordion').accordion();
            $('.demo .ui.dropdown').dropdown();
            $('.demo .ui.menu .dropdown').dropdown({
                on: 'hover'
            });
            $('.href').on('click', function () {
                window.location.href = $(this).attr('href');
            });
        });

        // loading
        var loading = new function () {

            var self = this;
            // 显示 loading
            self.show = function (text) {
                if ('undefined' == typeof(text)) {
                    text = '加载中...';
                }

                $('.basic.test.modal').find('.text').text(text);
                $('.basic.test.modal').modal('setting', 'closable', false).modal('show');
            };
            // 隐藏 loading
            self.hide = function () {
                $('.basic.test.modal').modal('hide');
            };
        };

    </script>
</head>
<body>

<div class="ui basic test modal">
    <div class="ui segment" style="width: 10rem; margin: 0 auto;">
        <div class="ui active inverted dimmer">
            <div class="ui large text loader">
                加载
            </div>
        </div>
        <br><br>
        <br><br>
        <br><br>
    </div>
</div>

<div class="main demo container">

    <div class="ui large top fixed menu transition visible" style="display: flex !important;">
        <div class="ui container">
            <div class="header item">API_DOC<code>(1.0)</code></div>
            <a class="{$currentMenu['Index/ListClass']}item" href="{$classUrl}">文件列表</a>
            <if condition="!empty($dirs)">
                <div class="ui menu" style="border: 0px;border-radius: 0px;">
                    <div class="ui pointing dropdown link item" tabindex="-1">
                        <i class="dropdown icon" tabindex="0" style="margin: 0 .38em 0  !important;"></i>
                        <span class="text">切换目录</span>
                        <div class="menu" tabindex="-1" style="margin-left: -50%;">
                            <foreach name="dirs" item="d">
                                <div href="{$classUrl}?dir={$d}" class="item href">{$d}</div>
                            </foreach>
                        </div>
                    </div>
                </div>
            </if>
            <a class="{$currentMenu['Index/ListMethod']}item">接口列表</a>
            <a class="{$currentMenu['Index/Doc']}item">文档详情</a>
            <a class="{$currentMenu['Index/Index']}item" href="{$indexUrl}">使用说明</a>
        </div>
    </div>
    <div class="ui text container" style="max-width: none !important; margin-top: 50px;">
