#文件列表#

* wiki_for_tapd.php     PHP API 接口文档代码生成器 For www.tapd.cn        生成接口文档 Markdown 代码
* api_login.html        接口登录调试工具  复制到www目录下 访问方法实例:qy.vchangyi.org/api_debug.html
                        domain 输入框为qy.vchangyi.org/local/.... 中的local, 以实际调试地址为准
                        identifier 应用标识
                        注意: 每个项目下的COOKIE_SECRET和COOKIE_DOMAIN配置,一个域名下的COOKIE,如果不一样的加密秘钥
                        就会访问一个应用是有人员信息的,另外一个应用就会没有人员信息的问题
* setLang.php		语言包生成器
			使用方法：
			开发时在 PHP 代码涉及到语言包的地方使用 `ERRORCODE/[语言 KEY]/中文` 的形式来书写。
			比如：$this->_set_error('ERRORCODE/_ERR_THIS_IS_ERROR/这是错误语言信息');
			开发完毕后使用命令行执行： php setLang.php 即可。
			脚本会自动将语言包字符串替换并生成 zh-cn.php
			!!建议执行 setLang.php 的时候对未提交的代码进行备份，避免万一!!
