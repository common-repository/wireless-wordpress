=== Plugin Name ===
Contributors: messense
Donate link: http://messense.me/
Tags: wap, mobile, wireless, phone
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: trunk

Wireless WordPress插件可以为你的博客增加友好的手机版页面

== Description ==

Wireless WordPress(中文即无线WordPress，也就是WP的wap版的意思),插件自动检测手机访问；支持调整文章内容中的图片的大小以适应手机浏览器，可以选择调整或不调整，并可以自定义图片显示的高度和宽度；支持设置wap版默认主题；自带一款名为wireless的WordPress主题，此主题专为手机版制作，也可以直接作为WordPress普通主题在控制面板启用；支持用户自定义的手机版主题，只需在主题控制面板里面上传主题并在Wireless WordPress插件的设置里面将其设置为默认手机主题即可；支持定义是否允许手机版搜索引擎(Googlebot-Mobile & Yahoo!)收录手机版页面

== Installation ==

上传并启用即可

== Screenshots ==

1. wap版首页截图示例

== Frequently Asked Questions ==

= 为什么写这样一个插件 =
WordPress并不原生支持手机访问，因而产生了很多wap插件，比如MobilePress、WP Mobile Edition、WP-T-WAP、WPTouch等。我也试用过了很多这种插件，MobilePress和WP-T-WAP的界面基本相同，我不是很喜欢那种风格；WP Mobile Edition个人感觉太大、太复杂了点，也不是很喜欢;而WPTouch的显示效果很棒，只可惜这里是中国，并不是每个人都拥有iPhone/iPad/Andriod，大多数人的手机都比较低端，那种效果在目前的客户端环境下并不适用。另外，这些插件大多只能使用它所提供的主题，而我想要的是一个可以自定义手机版主题的插件。基于以上原因，我花了一点时间写了Wireless WordPress这个插件，实现了我想要的功能。

= 默认主题wireless怎么使用 =
这个主题是我原创的WP主题，比较适合手机版。这个主题支持WordPress3.0及其以上版本的菜单功能。要为手机版设置独立于web版的菜单(用于顶部导航)，您需要在控制面板启用主题wireless，然后进入外观选项的菜单设置中，新建菜单，菜单名必须为wap，然后保存即可。
该主题使用XHTML Mobile Profile标记语言，电脑浏览器同样可以访问。主题内置邮件回复提醒、自定义keywords和description、数字分页、登录可见&评论可见短代码、嵌套评论等功能。
该主题在手机自带的浏览器及Opera Mobile等对标准支持较好的浏览器中显示效果比较好，由于UC、QQ等手机浏览器使用了wap中转压缩/适应屏幕等东西，显示效果并不如手机自带浏览器。有个小问题是UC的wap中转压缩是经过UC的服务器请求的页面可能导致无法自动识别出是手机访问。由于没有使用浮动(float)布局，该主题在各种电脑浏览器中显示效果比较一致。
由于是手机版主题，wireless并未引入任何javascript,还默认禁用了WP-Postviews等插件的js(无觅相关文章的js暂时还好像无法禁用，郁闷!)。我不能保证你的其它插件不会自动引入js。

== Upgrade Notice ==

= 1.01 =
* 升级前请先删除主题目录中的wireless主题。

== Changelog ==

= 1.1 =
* 增加手机浏览器User-Agent

= 1.02 =
* 更改优化图片显示代码，使之不更改那些大小低于后台设置的大小的图片的宽度和高度。

= 1.01 =
* 修复一些bug
* 给默认主题增加相关文章功能

= 1.0 =
* 第一个版本