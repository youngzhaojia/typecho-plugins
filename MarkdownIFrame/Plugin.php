<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * markdown编辑器：IFrame实现
 *
 * @package MarkdownIFrame
 * @author youngzhaojia@qq.com
 * @version 1.0.0
 * @link http://blog.youngfly.top
 */
class MarkdownIFrame_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 插件版本号
     * @var string
     */
    const _VERSION = '1.0.0';

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('admin/write-post.php')->bottom = array('MarkdownIFrame_Plugin', 'Insert');
        Typecho_Plugin::factory('admin/write-page.php')->bottom = array('MarkdownIFrame_Plugin', 'Insert');
//        Typecho_Plugin::factory('Widget_Archive')->header = array('MarkdownIFrame_Plugin', 'Show');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('MarkdownIFrame_Plugin','Show');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        /** 分类名称 */
//        $name = new Typecho_Widget_Helper_Form_Element_Text('word', NULL, 'Hello World', _t('说点什么'));
//        $form->addInput($name);
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}


    /**
     * 插件的实现方法
     *
     * @access public
     * @return void
     */
    public static function Insert()
    {
        $options         = Helper::options();
        ?>

        <div id="div_iframe_params" style="display:none;">
            <div class="row" style="margin-top: 10px;">
                <div class="col-mb-2" style="text-align: center;line-height: 32px;">
                    <label>http://</label>
                </div>
                <div class="col-mb-9">
                    <input type="text" class="text" id="iframe-src" style="width: 300px;"/>
                </div>
            </div>
            <div class="row" style="margin-top: 10px;">
                <div class="col-mb-2" style="text-align: center;line-height: 32px;">
                    <label>宽度</label>
                </div>
                <div class="col-mb-9">
                    <input type="number" class="w-100 text" id="iframe-width" value="500" style="height: 26px;"/>
                </div>
            </div>
            <div class="row" style="margin-top: 10px;">
                <div class="col-mb-2" style="text-align: center;line-height: 32px;">
                    <label>高度</label>
                </div>
                <div class="col-mb-9">
                    <input type="number" class="w-100 text" id="iframe-height" value="500" style="height: 26px;"/>
                </div>
            </div>
        </div>

        <script type="text/javascript" src="<?php echo Typecho_Common::url('MarkdownIFrame/layer/layer.js' , $options->pluginUrl); ?>"></script>
        <script type="text/javascript">
            $(function() {
                /* 判断是否为默认编辑器插入IF按钮 */
                if($('#wmd-button-row').length>0) {
                    $('#wmd-button-row').append('<li class="wmd-button" id="wmd-iframe-button" style="" title="插入iframe">IF</li>');
                } else {
                    $('#text').before('<a href="javascript:void(0)" id="wmd-iframe-button" title="插入iframe">插入iframe</a>');
                }

                $(document).on('click', '#wmd-iframe-button', function() {
                    layer.open({
                        type: 1,
                        title: 'iframe参数',
                        area: ['400px', '240px'],
                        shadeClose: true, //点击遮罩关闭
                        content: $('#div_iframe_params'),
                        btn: ['确认'],
                        yes: function (index, layero) {
                            layer.close(index);		//关闭层

                            var iframe_src    = $('#iframe-src').val();
                            var iframe_width  = $('#iframe-width').val();
                            var iframe_hegiht = $('#iframe-height').val();
                            if (!iframe_src || !iframe_width || !iframe_hegiht) {
                                layer.alert('参数不能为空');
                            }
                            //拼装iframe
                            add_html = '\r\n<iframe src="' + iframe_src + '" width="' + iframe_width + '" height="' + iframe_hegiht + '"></iframe>';
                            $('#text').insert({"text":add_html});
                        }
                    });
                });
            });

            //textarea光标处 插入内容
            $.fn.extend({
                "insert":function(value){
                    //默认参数
                    value=$.extend({
                        "text":"Hi, young"
                    },value);

                    var dthis = $(this)[0]; //将jQuery对象转换为DOM元素
                    //IE下
                    if(document.selection){
                        $(dthis).focus();       //输入元素textara获取焦点
                        var fus = document.selection.createRange();//获取光标位置
                        fus.text = value.text;  //在光标位置插入值
                        $(dthis).focus();   ///输入元素textara获取焦点
                    }
                    //火狐下标准
                    else if(dthis.selectionStart || dthis.selectionStart == '0'){
                        var start = dthis.selectionStart;
                        var end = dthis.selectionEnd;
                        var top = dthis.scrollTop;
                        //以下这句，应该是在焦点之前，和焦点之后的位置，中间插入我们传入的值
                        dthis.value = dthis.value.substring(0, start) + value.text + dthis.value.substring(end, dthis.value.length);
                    }
                    //在输入元素textara没有定位光标的情况
                    else{
                        this.value += value.text;
                        this.focus();
                    }
                    return $(this);
                }
            })
        </script>
        <?php
    }

    /**
     * 解析文章中出现的 iframe
     *
     * @access public
     * @return 文章内容 $content
     */
    public static function Show($content, $widget, $lastResult)
    {
        $content = empty($lastResult) ? $content : $lastResult;

        /** <iframe></iframe> **/
        $number = preg_match_all("/&lt;iframe(.*)&lt;\/iframe&gt;/",$content, $match);
        if ($number) {
            foreach ($match as $key => $value) {
                if ($key % 2 == 1) {
//                  [0] =>  src=
//                  [1] => www.baidu.com
//                  [2] =>  width=
//                  [3] => 500
//                  [4] =>  height=
//                  [5] => 500
//                  [6] => &gt;

                    //生成IFrame的html
                    $params = explode('&quot;', $value[0]);
                    $iframe_content = '<iframe src="http://' . $params[1] . '" width="' . $params[3] . '" height="' . $params[5] . '"></iframe><br/>';
                    //替换原文中的iframe文本
                    $content = str_replace($match[$key - 1][0], $iframe_content, $content);
                }
            }
        }
        return $content;
    }
}