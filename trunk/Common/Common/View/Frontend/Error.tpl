<include file="Common@Frontend/Header" />

<head>
    <style type="text/css">
        body {
            background-color: #f0eff5;
        }

        .error-page {
            margin-top: 2.35rem;
            text-align: center;
        }

        .error-page img {
            width: 3.11rem;
            height: 2.53rem;
        }

        .error-page span {
            display: block;
            font-size: 0.24rem;
            color: #555555;
        }
        .display-no {
            display: none;
        }
    </style>
    <script>
        var doc = document.documentElement;
        var deviceWidth = doc.clientWidth > 640 ? 640 : doc.clientWidth;
        doc.style.fontSize = deviceWidth / 6.4 + 'px';
    </script>
</head>
<body>
<div class="error-page">
    <img src="/static/img/frontendError.png">
    <span>网络正忙，请稍后再试</span>
    <div class="display-no">
        errorCode:{$error_code};
        errorMsg:{$error_msg}
    </div>
</div>
</body>


<include file="Common@Frontend/Footer" />