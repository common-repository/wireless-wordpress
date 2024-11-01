<?php
/*
  Plugin Name: Wireless-WordPress
  Plugin URI: http://messense.me/wireless-wordpress.html
  Description: 为你的WordPress增加wap主题模板功能，让你的WordPress博客拥有一个更加友好的手机版页面.
  Version: 1.1
  Author: 乱了感觉
  Author URI: http://messense.me
 */
if (!defined('WIRELESS_VERSION')) {
    define('WIRELESS_VERSION', '1.1');
}
if (!class_exists('WirelessWordPress')) {

    class WirelessWordPress {

        var $option = null;

        function __construct() {
            //启用插件自动复制wireless主题到WordPress主题目录
            register_activation_hook(__FILE__, array(&$this, 'init'));
            load_plugin_textdomain('wireless-wordpress');
            add_action('admin_menu', array(&$this, 'add_options_page'));

            add_filter('stylesheet', array(&$this, 'get_stylesheet'));
            add_filter('template', array(&$this, 'get_template'));
            add_filter('the_content', array(&$this, 'img_replace'), 99);
            //防止服务器SESSION未启用
            if (!is_admin() && $this->get_option('use_session') > 0) {
                $this->check_session();
            }
        }

        // 插件启用时执行
        function init() {

            //for wp<2.6
            if (!defined('WP_CONTENT_DIR')) {
                define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
            }
            //复制主题文件到WordPress主题目录
            $from = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'wireless';
            $to = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'wireless';
            if (!is_dir($to)) {
                $this->copy_dir($from, $to);
            }
        }

        function copy_dir($from, $to) {
            if (empty($from) || empty($to) || !is_dir($from)) {
                return;
            }
            if (!is_dir($to)) {
                @mkdir($to, 0777);
            }
            $handle = dir($from);
            while (($file = $handle->read()) !== FALSE) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (is_dir($from . DIRECTORY_SEPARATOR . $file)) {
                    $this->copy_dir($from . DIRECTORY_SEPARATOR . $file, $to . DIRECTORY_SEPARATOR . $file);
                } else {
                    @copy($from . DIRECTORY_SEPARATOR . $file, $to . DIRECTORY_SEPARATOR . $file);
                }
            }
            $handle->close();
            unset($handle);
        }

        function get_option($name) {
            if (is_null($this->option) || !is_array($this->option)) {
                $this->option = get_option('wireless_wordpress_options');
                if (is_array($this->option)) {
                    //防止session与cookie均未启用导致插件不能正常工作
                    if ($this->option['use_session'] < 1 && $this->option['use_cookie'] < 1) {
                        $this->option['use_session'] = 1;
                    }
                }
            }
            if (!$this->option || !is_array($this->option)) {
                $this->option = array(
                    'wap_theme' => 'wireless',
                    'wap_imgcut' => 0,
                    'wap_imgwidth' => 800,
                    'wap_imgheight' => 500,
                    'wap_seindex' => 1,
                    'use_cookie' => 0,
                    'use_session' => 1
                );
            }
            return $this->option[$name];
        }

        function get_stylesheet($stylesheet = '') {
            $theme = $this->get_theme();
            if (empty($theme)) {
                return $stylesheet;
            }
            $theme = get_theme($theme);
            // 不显示非公开主题模板
            if (isset($theme['Status']) && $theme['Status'] != 'publish')
                return $template;
            if (empty($theme)) {
                return $stylesheet;
            }
            return $theme['Stylesheet'];
        }

        function get_template($template) {
            $theme = $this->get_theme();
            if (empty($theme)) {
                return $template;
            }
            $theme = get_theme($theme);
            if (empty($theme)) {
                return $template;
            }
            // 不显示非公开主题模板
            if (isset($theme['Status']) && $theme['Status'] != 'publish')
                return $template;
            return $theme['Template'];
        }

        function get_theme() {
            $waptheme = $this->get_option('wap_theme'); //手机版默认主题
            if (empty($waptheme)) {
                $waptheme = 'wireless';
            }
            if (!is_dir(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $waptheme)) {
                return ''; //如果设置的手机版默认主题不存在则用电脑版主题
            }
            if ($this->is_mobile()) {
                if ($this->get_option('use_session') > 0) {
                    $_SESSION['wireless'] = 1;
                }
                if ($this->get_option('use_cookie') > 0 && !is_admin() && (!isset($_POST['action']) || $_POST['action'] != 'ajax') && (!isset($_COOKIE['wireless_' . COOKIEHASH]) || intval($_COOKIE['wireless_' . COOKIEHASH]) < 1)) {
                    setcookie(
                            "wireless_" . COOKIEHASH, '1', time() + 86400 * 30, COOKIEPATH
                    );
                }
                return $waptheme;
            } else {
                if ($this->get_option('use_session') > 0) {
                    $_SESSION['wireless'] = 0;
                }
                if ($this->get_option('use_cookie') > 0 && !is_admin() && (!isset($_POST['action']) || $_POST['action'] != 'ajax') && (!isset($_COOKIE['wireless_' . COOKIEHASH]) || intval($_COOKIE['wireless_' . COOKIEHASH]) > 0)) {
                    setcookie(
                            "wireless_" . COOKIEHASH, '0', time() + 300, COOKIEPATH
                    );
                }
                return '';
            }
        }

        function check_session() {
            if (session_id() == "") {
                session_start();
            }
        }

        function is_mobile() {
            $mobile = FALSE;
            if (isset($_GET['mobile'])) {
                $custom = intval($_GET['mobile']);
                if ($custom > 0) {
                    $mobile = TRUE;
                }
            } else {
                if (isset($_SESSION['wireless']) && $_SESSION['wireless'] > 0) {
                    $mobile = TRUE;
                } else if (isset($_COOKIE["wireless_" . COOKIEHASH]) && intval($_COOKIE["wireless_" . COOKIEHASH]) > 0) {
                    $mobile = TRUE;
                } else {
                    if (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
                        $mobile = TRUE;
                    } else {
                        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : "";
                        $regex = "/.*(mobile|nokia|iphone|ipod|andriod|bada|motorola|^mot\-|softbank|foma|docomo|kddi|ip\.browser|up\.link|";
                        $regex.="htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|ppc|";
                        $regex.="blackberry|alcate|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|";
                        $regex.="symbian|smartphone|midp|wap|phone|windows\sphone|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|";
                        if (get_option('wap_seindex') > 0) {
                            $regex.="Googlebot\-Mobile|YahooSeeker\/M1A1\-R2D2|Baiduspider\-mobile|"; //手机版搜索引擎UA
                        }
                        $regex.="jig browser|hiptop|uc|^benq|haier|^lct|opera\s*mobi|opera\s*mini|2.0 MMP|240x320|400X240|Cellphone|WinWAP).*/i";
                        if (preg_match($regex, $ua)) {
                            $mobile = TRUE;
                        }
                    }
                }
            }
            return $mobile;
        }

        /* 优化Wap页面图片显示 */

        function img_replace($content) {
            if (!isset($_SESSION['wireless']) || !$_SESSION['wireless']) {
                return $content;
            }
            if (!$this->get_option('wap_imgcut') || $this->get_option('wap_imgcut') < 1) {
                return $content;
            }
            $width = $this->get_option('wap_imgwidth');
            if (!$width) {
                $width = "200";
            }
            $height = $this->get_option('wap_imgheight');
            if (!$height) {
                $height = "200";
            }
            preg_match_all("/<img([^>]*)\s*[\/]{1}>/i", $content, $matches);
            $imgs = $matches[0];
            $attrs = $matches[1];
            foreach ($attrs as $key => $attr) {
                preg_match("/src=(['\"])([^\"]*)\\1/i", $attr, $matches);
                preg_match("/width=(['\"])(\d*)\\1/i", $attr, $matches1);
                preg_match("/height=(['\"])(\d*)\\1/i", $attr, $matches2);
                $_width = $width;
                $_height = $height;
                $the_width = $matches1[2];
                $the_height = $matches2[2];
                if ($the_width && $the_width <= $width) {
                    $_width = $the_height;
                }
                if ($the_height && $the_height <= $height) {
                    $_height = $the_height;
                }
                $imgcode = '<img src="' . $matches[2] . '" alt="" width="' . $_width . '" height="' . $_height . '" />';
                $content = str_replace($imgs[$key], $imgcode, $content);
            }
            return $content;
        }

        /* 在插件管理页面中显示你的插件菜单 */

        function add_options_page() {
            if (function_exists('add_options_page')) {
                add_options_page('Wireless-WordPress', 'Wireless-WordPress', 8, 'Wireless-WordPress', array(&$this, 'option_page'));
            }
        }

        function option_page() {
            ?>
            <div class="wrap">
                <h2>Wireless WordPress设置</h2>
                <?php
                if (isset($_POST['action']) && $_POST['action'] == 'update') {
                    update_option('wireless_wordpress_options', array(
                        'wap_theme' => $_POST['wap_theme'],
                        'wap_imgcut' => intval($_POST['wap_imgcut']),
                        'wap_imgwidth' => intval($_POST['wap_imgwidth']),
                        'wap_imgheight' => intval($_POST['wap_imgheight']),
                        'wap_seindex' => intval($_POST['wap_seindex']),
                        'use_cookie' => intval($_POST['use_cookie']),
                        'use_session' => intval($_POST['use_session'])
                    ));
                    ?>
                    <div class="updated"> 
                        <p><strong>设置已保存。</strong></p>
                    </div>
                <?php } ?>
                <form name="myform" method="post" action="options-general.php?page=Wireless-WordPress">
                    <p><strong>Wap站点主题(当前设置为 <?php echo $this->get_option('wap_theme'); ?>) : </strong></p>
                    <p>
                        <?php
                        $themes = get_themes();
                        foreach ($themes as $theme) {
                            echo '<input type="radio" name="wap_theme" id="wap_theme" value="', $theme['Name'], '" ';
                            if ($theme['Name'] == $this->get_option('wap_theme')) {
                                echo 'checked="checked"';
                            }
                            echo '/> ', $theme['Name'], '   ';
                        }
                        ?>
                    </p>
                    <p><strong>Wap调整图片大小: </strong><input type="radio" name="wap_imgcut" value="1" <?php if ($this->get_option('wap_imgcut') > 0)
                echo 'checked="checked "' ?>/> 调整 <input type="radio" name="wap_imgcut" value="0" <?php if ($this->get_option('wap_imgcut') < 1)
                                              echo 'checked="checked "' ?>/> 不调整</p>
                    <p><strong>Wap图片显示宽度</strong><i>(仅当调整图片大小时有效)</i>:</p>
                    <p><input type="text" name="wap_imgwidth" id="wap_imgwidth" value="<?php echo $this->get_option('wap_imgwidth'); ?>" /> 像素</p>
                    <p><strong>Wap图片显示高度</strong><i>(仅当调整图片大小时有效)</i>:</p>
                    <p><input type="text" name="wap_imgheight" id="wap_imgheight" value="<?php echo $this->get_option('wap_imgheight'); ?>" /> 像素</p>
                    <p><strong>允许搜索引擎收录Wap版页面? </strong><input type="radio" name="wap_seindex" value="1" <?php if ($this->get_option('wap_seindex') > 0)
                                              echo 'checked="checked "' ?>/> 允许 <input type="radio" name="wap_seindex" value="0" <?php if ($this->get_option('wap_seindex') < 1)
                                                   echo 'checked="checked "' ?>/> 禁止</p>
                    <p><strong>发送Cookies到客户端浏览器? </strong><input type="radio" name="use_cookie" value="1" <?php if ($this->get_option('use_cookie') > 0)
                                                   echo 'checked="checked "' ?>/> 发送 <input type="radio" name="use_cookie" value="0" <?php if ($this->get_option('use_cookie') < 1)
                                                     echo 'checked="checked "' ?>/> 不发送(推荐)</p>
                    <p><strong>启用PHP Session? </strong><input type="radio" name="use_session" value="1" <?php if ($this->get_option('use_session') > 0)
                                                     echo 'checked="checked "' ?>/> 启用(推荐) <input type="radio" name="use_session" value="0" <?php if ($this->get_option('use_session') < 1)
                                                  echo 'checked="checked "' ?>/> 不启用</p>
                    <p class="submit">
                        <!-- 必须，设置表单提交为“更新”操作 -->
                        <input type="hidden" name="action" value="update" />
                        <input type="submit" class="button-primary" value="更新选项" />
                    </p>
                </form>
                <h3>插件说明</h3>
                <p>Wireless WordPress插件是由 <a href="http://messense.me" title="乱了感觉" target="_blank">乱了感觉</a> 编写的一款WordPress的Wap插件,详情请点击进入<a href="http://messense.me/wireless-wordpress.html" title="插件主页" target="_blank">插件主页</a>查看。</p>
                <p>
                    Wireless WordPress插件可自动检测浏览器User Agent并将手机访问者切换到Wap版主题，插件已默认提供了一款名为wireless的wap2.0的主题，您可以自行编写您的wap主题将其放入WordPress的主题目录中，然后在Wireless WordPress插件的设置页面将其设置为Wap站点主题即可。
                </p>
                <p>
                    电脑端浏览器可以通过给地址加上一个请求参数mobile=1来体验wap版，同理可通过mobile=0强制使用web版。您可以<a href="<?php bloginfo('home'); ?>/?mobile=1" title="Wap版" target="_blank">点击此处</a>来体验Wap版WordPress博客页面。
                </p>
            </div>
            <?php
        }

    }

    //实例化
    $wl_wp = new WirelessWordPress();
} else {

    function class_conflict_exception() {
        echo '<div class="error"><p><strong>插件冲突。</strong>您的博客正在运行一个与“Wireless WordPress插件”定义了相同类名(WirelessWordPress)的插件，只有在关闭冲突插件以后“Wireless WordPress插件”才能正常启用。</p></div>';
    }

    add_action('admin_notices', 'class_conflict_exception');
}