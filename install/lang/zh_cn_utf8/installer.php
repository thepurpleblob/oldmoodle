<?php
/// Please, do not edit this file manually! It's auto generated from
/// contents stored in your standard lang pack files:
/// (langconfig.php, install.php, moodle.php, admin.php and error.php)
///
/// If you find some missing string in Moodle installation, please,
/// keep us informed using http://moodle.org/bugs Thanks!
///
/// File generated by cvs://contrib/lang2installer/installer_builder
/// using strings defined in installer_strings (same dir)

$string['admindirerror'] = '指定的管理目录不正确';
$string['admindirname'] = '管理目录';
$string['caution'] = '原因';
$string['closewindow'] = '关闭窗口';
$string['configfilenotwritten'] = '安装脚本无法自动创建一个包含您设置的config.php文件，极可能是由于Moodle目录是不能写的。您可以复制如下的代码到Moodle根目录下的config.php文件中。';
$string['configfilewritten'] = '已经成功创建了config.php文件';
$string['continue'] = '继续';
$string['database'] = '数据库';
$string['dataroot'] = '数据目录';
$string['datarooterror'] = '找不到也无法创建您指定的“数据目录”，请更正路径或手工创建它。';
$string['dbconnectionerror'] = '无法连接到您指定的数据库，请检查您的数据库设置。';
$string['dbcreationerror'] = '数据库创建错误。无法用设定中的名称创建数据库。';
$string['dbhost'] = '服务器主机';
$string['dbprefix'] = '表格名称前缀';
$string['dbtype'] = '类型';
$string['dirroot'] = 'Moodle目录';
$string['dirrooterror'] = '“Moodle目录”的设置看上去不对——在那里找不到安装好的Moodle。下面的值已经重置了。';
$string['download'] = '下载';
$string['error'] = '错误';
$string['fail'] = '失败';
$string['fileuploads'] = '上传文件';
$string['fileuploadserror'] = '这应当是开启的';
$string['gdversion'] = 'GD版本';
$string['gdversionerror'] = '为了能够处理和创建图片，服务器上必须有GD库。';
$string['globalsquotes'] = '处理全局变量的方式不安全';
$string['globalsquoteserror'] = '修正您的PHP设置：禁用register_globals和/或启动magic_quotes_gpc。';
$string['help'] = '帮助';
$string['info'] = '信息';
$string['installation'] = '安装';
$string['language'] = '语言';
$string['magicquotesruntime'] = '运行时的Magic Quotes';
$string['magicquotesruntimeerror'] = '这应该是关闭的';
$string['memorylimit'] = '内存限制';
$string['memorylimiterror'] = 'PHP内存限制设置的太低了...以后您会遇到问题的。';
$string['memorylimithelp'] = '<p>您的服务器的PHP内存限制是${a}。</p>

<p>这会使Moodle在将来运行是碰到内存问题，特别是您安装了很多模块并且/或者有很多用户。</p>

<p>我们建议可能的话把限制设定的高一些，譬如16M。有几种方法可以做到这一点:</p>
<ol>
<li>如果可以，重新编译PHP并使用<i>--enable-memory-limit</i>选项。这允许Moodle自己设定内存限制。</li>
<li>如果可以访问php.ini文件，您可以修改<b>memory_limit</b>的设置为其它值如16M。如果您无法访问，可以让您的管理员帮您修改一下。</li>
<li>在一些PHP服务器上，您可以在Moodle目录中创建一个.htaccess文件并包含如下内容:
<blockquote>php_value memory_limit 16M</blockquote>
<p>然而，在一些服务器上这会让<b>所有</b>PHP页面无法正常工作(在访问页面时会有错误)，因此您可能不得不删除.htaccess文件。</p></li>
</ol>';
$string['mysqlextensionisnotpresentinphp'] = 'PHP的MySQL扩展并未安装正确，因此无法与MySQL通信。请检查您的php.ini文件或重新编译PHP。';
$string['name'] = '名称';
$string['next'] = '向后';
$string['ok'] = '好';
$string['pass'] = '通过';
$string['password'] = '密码';
$string['phpversion'] = 'PHP版本';
$string['phpversionerror'] = 'PHP版本至少为4.1.0';
$string['phpversionhelp'] = '<p>Moodle需要PHP 4.1.0以上的版本。</p>
<p>您当前使用的是${a}</p>
<p>您必须升级PHP或者转移到一个有新版PHP的服务器上!</p>';
$string['previous'] = '向前';
$string['safemode'] = '安全模式';
$string['safemodeerror'] = '在安全模式下运行Moodle可能会有麻烦';
$string['sessionautostart'] = '自动开启会话';
$string['sessionautostarterror'] = '这应当是关闭的';
$string['status'] = '状态';
$string['thischarset'] = 'UTF-8';
$string['thislanguage'] = '简体中文';
$string['user'] = '用户';
$string['wwwroot'] = '网站地址';
$string['wwwrooterror'] = '这个网站地址似乎是错的——在那里并没有刚刚装好的Moodle。';
?>