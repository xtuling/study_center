# PHPUnit 全局安装

Linux:

$ wget https://phar.phpunit.de/phpunit.phar
$ chmod +x phpunit.phar
$ sudo mv phpunit.phar /usr/local/bin/phpunit
$ phpunit --version
PHPUnit x.y.z by Sebastian Bergmann and contributors.

Windows:

为 PHP 的二进制可执行文件建立一个目录，例如 C:\bin
将 ;C:\bin 附加到 PATH 环境变量中（相关帮助）
下载 https://phar.phpunit.de/phpunit.phar 并将文件保存到 C:\bin\phpunit.phar
打开命令行（例如，按 Windows+R » 输入 cmd » ENTER)
建立外包覆批处理脚本（最后得到 C:\bin\phpunit.cmd）：

C:\Users\username> cd C:\bin
C:\bin> echo @php "%~dp0phpunit.phar" %* > phpunit.cmd
C:\bin> exit
新开一个命令行窗口，确认一下可以在任意路径下执行 PHPUnit：

C:\Users\username> phpunit --version
PHPUnit x.y.z by Sebastian Bergmann and contributors.
对于 Cygwin 或 MingW32 (例如 TortoiseGit) shell 环境，可以跳过第五步。 取而代之的是，把文件保存为 phpunit （没有 .phar 扩展名），然后用 chmod 775 phpunit 将其设为可执行。


# PHPUnit 官方手册

https://phpunit.de/manual/current/zh_cn/phpunit-book.html#installation.composer

最开始的时候, 在当前目录下运行shell: composer install

写具体单元测试时, 测试文件以项目结构为参考, 比如
Common/Apicp/Controller/AdminManager/AddController.class.php -->> 就在当前目录下Common/Apicp/AdminManager的目录下
新建AddTest.php (*Test是规范)

测试执行shell: phpunit (测试类)

ExampleTest.php 为示例测试类

---

待完善