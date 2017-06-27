# PHP 开发相关规范 #

> 代码书写规范以 PSR-2 规范为基础，对于类名、方法名命名规则遵循 ThinkPHP 的标准
> 
> [PSR-2 英文版](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md "英文版")
> 
> [PSR-2 中文版](https://segmentfault.com/a/1190000002521620 "中文版")
> 
> 代码注释规范以 PHPDoc 规范为标准
> 
> [PSR-5: PHPDoc](https://github.com/phpDocumentor/fig-standards/blob/master/proposed/phpdoc.md "PHPDoc")
> 
> 

# 同时就团队开发规范而言，还需要注意以下约定（不断完善） #
1. 每行结尾不允许有空格（PSR）
2. 文件结尾需要使用空行结尾（PSR）
3. 避免使用 ThinkPHP 的 D、M、S 等函数来调用类库，应该使用 use 利用命名空间来引入
4. 注意各种注释的书写格式
5. TODO、FIXME、XXX 等任务标签，必须标记 **时间**、**作者**以及**描述**
6. 所有涉及 URI 路径的均不可以直接拼写，改由统一的函数 oaUrl() 来输出
