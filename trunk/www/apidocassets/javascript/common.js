/**
 * 基础方法
 * @Description:
 * @Author: zhuxun37
 * @Date: Created in {上午10:07} 2017/4/28.
 * @Modified By:
 */

(function () {
    var initializing = false, fnTest = /xyz/.test(function () {
        xyz;
    }) ? /\b_super\b/ : /.*/;
    this.Class = function () {
        // do nothing.
    };
    Class.extend = function (prop) {
        var _super = this.prototype;
        initializing = true;
        var prototype = new this();
        initializing = false;
        for (var name in prop) {
            prototype[name] = typeof prop[name] == "function" &&
            typeof _super[name] == "function" && fnTest.test(prop[name]) ?
                (function (name, fn) {
                    return function () {
                        var tmp = this._super;
                        this._super = _super[name];
                        var ret = fn.apply(this, arguments);
                        this._super = tmp;
                        return ret;
                    };
                })(name, prop[name]) :
                prop[name];
        }
        function Class() {
            if (!initializing && this.init) {
                this.init.apply(this, arguments);
            }
        }

        Class.prototype = prototype;
        Class.constructor = Class;
        Class.extend = arguments.callee;
        return Class;
    };
})();

// 清除 cookie
function delCookie(name) {

    var exp = new Date();
    exp.setTime(exp.getTime() - 10000);
    document.cookie = name + "=" + " " + "; expires=" + exp.toGMTString() + "; domain=." + document.location.hostname;
}

// 获取 cookie
function getCookie(cookieName) {

    var name = cookieName + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1);
        if (c.indexOf(name) != -1) return c.substring(name.length, c.length);
    }

    return "";
}

var isLoading = false;

// 登录类
var Login = Class.extend({
    // init
    init: function (domain) {
        if ('undefined' != typeof domain) {
            this.domain = domain;
        } else {
            this.domain = '';
        }
    },
    // 设置domain
    setDomain: function (domain) {
        this.domain = domain;
    },
    // 获取域名
    getDomain: function () {
        // 如果域名已经获取
        if ('' != this.domain) {
            return this.domain;
        }

        var re = /^http\s?:\/\/(.*?)\/(.*?)\//g;
        var result;
        if (null === (result = re.exec(window.location.href))) {
            this.domain = '';
        } else {
            this.domain = result[2];
        }

        return this.domain;
    },
    getUser: function () {
        // do nothing.
    },
    login: function () {
        // do nothing.
    },
    logout: function () {
        // do nothing.
    },
    // 刷新用户信息
    refreshUserInfo: function (userInfo) {
        // do nothing.
    }
});

// 微信前端登录
var WxLogin = Login.extend({
    // 获取用户信息
    getUser: function () {

        var domain = this.getDomain();
        var self = this;
        $.ajax({
            url: '/' + domain + "/Public/Api/Debug/User/Data",
            type: "get",
            data: {
                _identifier: "common"
            },
            dataType: "json",
            success: function (data) {
                if (data.result == '') {
                    return false;
                }

                self.refreshUserInfo(data.result);
            },
            error: function () {
                // do nothing.
            }
        });
    },
    // 用户登录
    login: function (uid, identifier) {

        if (isLoading) {
            return true;
        }

        isLoading = true;
        var domain = this.getDomain();
        var self = this;

        $.ajax({
            url: '/' + domain + "/Public/Api/Debug/Login/SetCookie",
            type: "GET",
            dataType: "json",
            data: {
                uid: uid,
                _identifier: identifier
            },
            success: function (data) {
                isLoading = false;
                if (data.errcode > 0) {
                    alert("接口错误: " + data.errmsg);
                    return;
                }

                self.refreshUserInfo(data.result);
            },
            error: function () {
                alert("接口错误, 通讯失败");
                isLoading = false;
            }
        });
    },
    // 用户退出登录
    logout: function (e) {

        // 获取Cookie
        var ca = document.cookie.split(';');
        for (var i = 0; ca.length > i; i++) {
            var c = ca[i];
            // 去空
            while (c.charAt(0) == ' ') c = c.substring(1);
            // 获取名称
            var name = c.substring(0, c.indexOf('='));
            // 清除Cookie
            delCookie(name);
        }

        // 删除人员显示
        var delUser = {
            "memUid": '',
            "memUsername": '',
            "memFace": '',
            "dpNames": [],
            "tagNames": [],
            "memMobile": '',
            "memUserid": ''
        };
        this.refreshUserInfo(delUser);
    }
});

// 后台登录
var CpLogin = Login.extend({
    getUser: function () {
        var domain = this.getDomain();
        var self = this;
        $.ajax({
            url: '/' + domain + "/Public/Apicp/Debug/User/Data",
            type: "get",
            data: {
                _identifier: "common"
            },
            dataType: "json",
            success: function (data) {
                if (data.result == '') {
                    return false;
                }

                self.refreshUserInfo(data.result);
            },
            error: function () {
                // do nothing.
            }
        });
    },
    login: function (id, identifier) {
        if (isLoading) {
            return true;
        }

        isLoading = true;
        var domain = this.getDomain();
        var self = this;

        $.ajax({
            url: '/' + domain + "/Public/Apicp/Debug/Login/SetCookie",
            type: "GET",
            dataType: "json",
            data: {
                eaId: id,
                _identifier: identifier
            },
            success: function (data) {
                isLoading = false;
                if (data.errcode > 0) {
                    alert("接口错误: " + data.errmsg);
                    return;
                }

                self.refreshUserInfo(data.result);
            },
            error: function () {
                alert("接口错误, 通讯失败");
                isLoading = false;
            }
        });
    },
    logout: function (e) {

        // 获取Cookie
        var ca = document.cookie.split(';');
        for (var i = 0; ca.length > i; i++) {
            var c = ca[i];
            // 去空
            while (c.charAt(0) == ' ') c = c.substring(1);
            // 获取名称
            var name = c.substring(0, c.indexOf('='));
            // 清除Cookie
            delCookie(name);
        }

        // 删除人员显示
        var delUser = {
            "memFace": '',
            "eaRealname": '',
            'eaMobile': '',
            'eaEmail': ''
        };
        this.refreshUserInfo(delUser);
    }
});
