<include file="./Header"/>

<script type="text/javascript">
    var apiUrl = '{$apiUrl}';
    var paramJson = '';
    var wxUser = null;
    var cpUser = null;

    // 获取domain
    function getDomain() {

        var domain = $.trim($('#domain').val());
        return domain;
    }

    // 5FC1D7027F00000155735D99E45FFE2F
    var FrontLogin = WxLogin.extend({
        refreshUserInfo: function (user) {
            wxUser = user;
            var dpNames = [];
            for (var i in user.dpName) {
                dpNames.push(user.dpName[i].dpName);
            }

            var tagNames = [];
            for (var i in user.tagName) {
                tagNames.push(user.tagName[i].tagName);
            }

            $('#loginAvatar').attr('src', user.memFace);
            $('#loginUsername').html(user.memUsername);
            $('#loginUid').html(user.memUid);
            $('#loginDepartment').html(dpNames.concat(', '));
            $('#loginTag').html(tagNames.concat(', '));
            $('#loginMobile').html(user.memMobile);
            $('#loginUserid').html(user.memUserid);
            $('#uid').val(user.memUid);
        },
        logout: function (e) {
            // 退出前端时, 也退出后台
            adminLogin.logout(e);
            wxUser = [];
            this._super(e);
        }
    });

    // 550071BF7F0000017064C43D0484F00B
    var AdminLogin = CpLogin.extend({
        refreshUserInfo: function (user) {
            cpUser = user;
            $('#cpLoginAvatar').attr('src', user.memFace);
            $('#cpLoginRealname').html(user.eaRealname);
            $('#cpLoginMobile').html(user.eaMobile);
            $('#cpLoginEmail').html(user.eaEmail);
            $('#cpLoginEaId').html(user.eaId);
            $('#cpEaid').val(user.eaId);
        },
        logout: function (e) {
            // 退出后台时, 也退出前端
            frontLogin.logout(e);
            cpUser = [];
            this._super(e);
        }
    });

    // 后台登录
    var adminLogin = new AdminLogin();
    adminLogin.setDomain(getDomain());

    // 前端登录
    var frontLogin = new FrontLogin();
    frontLogin.setDomain(getDomain());

    // 页面加载完成, 开始初始化
    $(document).ready(function () {
        $('.ui.dropdown').dropdown();
        $('.demo .ui.menu .dropdown').dropdown({
            on: 'hover'
        });
        // 获取请求 Json
        paramJson = $('#reqParam').val();
        // 获取域名(企业标识)
        $('#domain').val(enumber);
        $('#cpDomain').val(enumber);
        // 默认使用通讯录应用标识
        $('#identifier').val('Contact');
        $('#cpIdentifier').val('Contact');

        // 初始化 Api
        initApi();
        // 获取已登录用户
        if (isWxLogin(getCurrentTab())) {
            frontLogin.getUser();
        } else {
            adminLogin.getUser();
        }

        // 监听请求按钮
        $('#sendBtn').click(function () {

            if (isLoading) {
                return true;
            }
            isLoading = true;

            var params = $('#reqParam').val();
            eval('params=' + params);
            $.ajax({
                url: $('#apiUrl').val(),
                type: $('#method').val(),
                dataType: "json",
                data: params,
                success: function (data) {
                    isLoading = false;
                    $('#response').val(JSON.stringify(data, null, 4));
                    if (data.errcode > 0) {
                        alert("接口错误: " + data.errmsg);
                        return;
                    }
                },
                error: function () {
                    alert("接口错误, 通讯失败");
                    isLoading = false;
                }
            });
        });

        // 监听初始化按钮
        $('#initApiBtn').click(function (e) {

            console.log(e);
            initApi();
        });

        // 登录按钮
        $('#loginBtn, #cpLoginBtn').click(function (e) {

            console.log(e);
            var id, identifier;
            if ('loginBtn' == $(this).attr('id')) {
                identifier = $.trim($('#identifier').val());
                id = $.trim($('#uid').val());
            } else {
                identifier = $.trim($('#cpIdentifier').val());
                id = $.trim($('#cpEaid').val());
            }

            if (id.length != 32) {
                alert("请输入正确的id！" + id);
                return;
            }

            if ('loginBtn' == $(this).attr('id')) {
                frontLogin.login(id, identifier);
            } else {
                adminLogin.login(id, identifier);
            }
        });

        // 退出按钮
        $("#logoutBtn, #cpLogoutBtn").click(frontLogin.logout);

        // tab click 事件
        $('#loginTab').on('click', 'a.item', function (e) {
            console.log(e);
            if (isWxLogin(getCurrentTab())) {
                null == wxUser && frontLogin.getUser();
            } else {
                null == cpUser && adminLogin.getUser();
            }
        });
    });

    // 初始化数据
    function initApi() {

        $('#reqParam').val(paramJson);
        $('#apiUrl').val(host + '/' + enumber + '/' + apiUrl);
    }

    function isWxLogin(tab) {

        return 'login_wx' == tab;
    }

    function getCurrentTab() {

        var current = '';
        $('#loginTab').children().each(function (e) {
            console.log(e);
            if ($(this).hasClass('active')) {
                current = $(this).data('tab');
                return false;
            }
        });

        return current;
    }
</script>

<br/>
<div class="ui pointing secondary menu" id="loginTab">
    <a class="item active" data-tab="login_wx">微信端登录</a>
    <a class="item" data-tab="login_cp">管理后台登录</a>
</div>
<div class="ui tab active segment" data-tab="login_wx">
    <div class="ui top attached tabular menu" style="border-bottom: 0;">
        <table class="ui striped table" style="border: 0px;">
            <tbody>
            <tr>
                <td style="width: 50%;">
                    <div class="ui vertical" style="width: 90%;">
                        <div class="item">
                            <img id="loginAvatar" class="ui avatar image"/>
                            <strong>username:&nbsp;&nbsp;</strong><cite id="loginUsername">?</cite>
                        </div>
                        <div class="item">
                            <strong>uid:&nbsp;&nbsp;</strong><cite id="loginUid">?</cite>
                        </div>
                        <div class="item">
                            <strong>department:&nbsp;&nbsp;</strong><cite id="loginDepartment">?</cite>
                        </div>
                        <div class="item">
                            <strong>tag:&nbsp;&nbsp;</strong><cite id="loginTag">?</cite>
                        </div>
                        <div class="item">
                            <strong>mobile:&nbsp;&nbsp;</strong><cite id="loginMobile">?</cite>
                        </div>
                        <div class="item">
                            <strong>userid:&nbsp;&nbsp;</strong><cite id="loginUserid">?</cite>
                        </div>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="ui vertical menu form" style="margin: 0 auto; width: 25rem;">
                        <a class="active teal item" style="border-color: transparent !important;">微信用户登录</a>
                        <a class="item">
                            <input type="text" id="uid" style="width: 16rem;"/>
                            <div class="ui label" style="margin: .7em auto;">UID</div>
                        </a>
                        <div class="item">
                            <input type="text" id="identifier" style="width: 16rem;"/>
                            <div class="ui label" style="margin: .7em auto;">Identifier</div>
                        </div>
                        <div class="item">
                            <input type="text" id="domain" style="width: 16rem;" readonly="true"/>
                            <div class="ui label" style="margin: .7em auto;">Domain</div>
                        </div>
                        <div class="ui two bottom attached buttons">
                            <div id="logoutBtn" class="ui green button">退出</div>
                            <div id="loginBtn" class="ui blue button">登录</div>
                        </div>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="ui tab segment" data-tab="login_cp">
    <div class="ui top attached tabular menu" style="border-bottom: 0;">
        <table class="ui striped table" style="border: 0px;">
            <tbody>
            <tr>
                <td style="width: 50%;">
                    <div class="ui vertical" style="width: 90%;">
                        <div class="item">
                            <img id="cpLoginAvatar" class="ui avatar image"/>
                            <strong>realname:&nbsp;&nbsp;</strong><cite id="cpLoginRealname">?</cite>
                        </div>
                        <div class="item">
                            <strong>eaId:&nbsp;&nbsp;</strong><cite id="cpLoginEaId">?</cite>
                        </div>
                        <div class="item">
                            <strong>mobile:&nbsp;&nbsp;</strong><cite id="cpLoginMobile">?</cite>
                        </div>
                        <div class="item">
                            <strong>email:&nbsp;&nbsp;</strong><cite id="cpLoginEmail">?</cite>
                        </div>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="ui vertical menu form" style="margin: 0 auto; width: 25rem;">
                        <a class="active teal item" style="border-color: transparent !important;">管理后台用户登录</a>
                        <a class="item">
                            <input type="text" id="cpEaid" style="width: 16rem;"/>
                            <div class="ui label" style="margin: .7em auto;">EaId</div>
                        </a>
                        <div class="item">
                            <input type="text" id="cpIdentifier" style="width: 16rem;"/>
                            <div class="ui label" style="margin: .7em auto;">Identifier</div>
                        </div>
                        <div class="item">
                            <input type="text" id="cpDomain" style="width: 16rem;" readonly="true"/>
                            <div class="ui label" style="margin: .7em auto;">Domain</div>
                        </div>
                        <div class="ui two bottom attached buttons">
                            <div id="cpLogoutBtn" class="ui green button">退出</div>
                            <div id="cpLoginBtn" class="ui blue button">登录</div>
                        </div>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="ui horizontal divider" style="margin: 3rem auto;">华丽的分割线</div>

<div class="ui floating message">
    <h2 class='ui header'>接口：{$apiUrl}</h2>
    <br/>
    <span class='ui teal tag label'>
        {$comments['description']}
    </span>

    <div class="ui raised segment">
        <span class="ui red ribbon label">接口说明</span>
        <div class="ui message">
            <p> {$comments['desc']}</p>
        </div>
    </div>

    <script type="text/javascript">
        $(function () {
            $('.menu .item').tab();
        });
    </script>

    <div class="ui top attached tabular menu">
        <a class="active item" data-tab="first"><h4>请求参数</h4></a>
        <a class="item" data-tab="second"><h4>返回结果</h4></a>
        <a class="item" data-tab="third"><h4>接口测试</h4></a>
        <a class="item" data-tab="forth"><h4>O(∩_∩)O哈哈~</h4></a>
    </div>
    <!-- 请求参数 -->
    <div class="ui bottom attached tab active segment" data-tab="first">
        <table class="ui red celled striped table">
            <thead>
            <tr>
                <th>参数名字</th>
                <th>类型</th>
                <th>是否必须</th>
                <th>默认值</th>
                <th>说明</th>
            </tr>
            </thead>
            <tbody>
            <if condition="empty($comments['param'])">
                <tr>
                    <td colspan="5">no param</td>
                </tr>
            </if>
            <foreach name="comments['param']" item="p">
                <tr>
                    <td>{$p['name']}</td>
                    <td>{$p['type']}</td>
                    <td>
                        <if condition="$p['require'] eq 'true'">
                            <font color="red">必须</font>
                            <else/>
                            可选
                        </if>
                    </td>
                    <td style="width:30%;word-wrap:break-word;word-break:break-all;">
                        <if condition="strlen($p['default']) eq 0&&empty($p['default'])">
                            无
                            <else/>
                            {$p['default']}
                        </if>
                    </td>
                    <td style="width:30%;word-wrap:break-word;word-break:break-all;">{$p['desc']}</td>
                </tr>
            </foreach>
            </tbody>
        </table>

        <br/>
        <h4 class="ui header">
            <a href="#">请求数据示例</a>
        </h4>
        <div class="ui secondary segment">
            <pre style="margin: 0;">
                <code class="syntax brush-javascript">{$paramExample}</code>
            </pre>
        </div>
    </div>
    <!-- 返回参数 -->
    <div class="ui bottom attached tab segment" data-tab="second">
        <table class="ui green celled striped table">
            <thead>
            <tr>
                <th>返回字段</th>
                <th>类型</th>
                <th>说明</th>
            </tr>
            </thead>
            <tbody>
            <if condition="empty($comments['return'])">
                <tr>
                    <td colspan="3">no return</td>
                </tr>
            </if>
            <foreach name="comments['return']" item="ret">
                <tr>
                    <td>{$ret['name']}</td>
                    <td>{$ret['type']}</td>
                    <td style="width:60%;word-wrap:break-word;word-break:break-all;">{$ret['desc']}</td>
                </tr>
            </foreach>
            </tbody>
        </table>

        <br/>
        <h4 class="ui header">
            <a href="#">返回数据示例</a>
        </h4>
        <div class="ui secondary segment">
            <pre style="margin: 0;">
                <code class="syntax brush-javascript">{$returnExample}</code>
            </pre>
        </div>
    </div>
    <!-- 接口测试 -->
    <div class="ui bottom attached tab segment" data-tab="third">
        <table class="ui striped table" style="border: 0px; width: 90%; margin: 0 auto;">
            <tbody>
            <tr>
                <td>
                    <div class="ui labeled left input" style="width: 100%;">
                        <a class="ui label" style="padding: 0;">
                            <div class="ui selection dropdown" style="min-width: 6em !important;">
                                <div class="default text">POST</div>
                                <i class="dropdown icon"></i>
                                <input id="method" type="hidden" value="POST"/>
                                <div class="menu">
                                    <div class="item" data-value="POST">POST</div>
                                    <div class="item" data-value="GET">GET</div>
                                </div>
                            </div>
                        </a>
                        <input type="text" id="apiUrl" placeholder="Api Url..."/>
                    </div>
                </td>
                <td style="width: 9em;">
                    <div id="sendBtn" class="ui blue button">发送请求</div>
                </td>
                <td style="width: 10em;">
                    <div id="initApiBtn" class="ui button">初始化数据</div>
                </td>
            </tr>
            </tbody>
        </table>
        <table class="ui striped table" style="border: 0px;">
            <tbody>
            <tr>
                <td style="width: 33%;">
                    <div class="ui segment form">
                        <a class="ui blue ribbon label">请求</a>
                        <textarea id="reqParam"
                                  style="margin-top: 10px; height: 48em; max-height: 48em;">{$paramJson}</textarea>
                    </div>
                </td>
                <td style="width: 33%;">
                    <div class="ui segment form">
                        <a class="ui ribbon label">响应</a>
                        <textarea id="response" style="margin-top: 10px; height: 48em; max-height: 48em;"></textarea>
                    </div>
                </td>
                <td style="width: 34%;">
                    <div class="ui segment form">
                        <a class="ui green ribbon label">标准响应</a>
                        <textarea readonly
                                  style="margin-top: 10px; height: 48em; max-height: 48em;">{$returnJson}</textarea>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <!-- 未知参数 -->
    <div class="ui bottom attached tab segment" data-tab="forth">你太好奇了, 这样不好......</div>

</div>

<p/>

<include file="./Footer"/>