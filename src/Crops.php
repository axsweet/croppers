<?php

namespace Encore\Croppers;

use Encore\Admin\Form\Field\ImageField;
use Encore\Admin\Admin;
use Encore\Admin\Form\Field\MultipleFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Crops extends MultipleFile
{
    use ImageField;

    private $ratioW = 100;

    private $ratioH = 100;

    protected $view = 'laravel-admin-croppers::croppers';

    protected static $css = [
        '/vendor/laravel-admin-ext/croppers/cropper.min.css',
    ];

    protected static $js = [
        '/vendor/laravel-admin-ext/croppers/cropper.min.js',
        '/vendor/laravel-admin-ext/croppers/layer/layer.js'
    ];

    protected function preview()
    {
        $files = is_array($this->value) ? $this->value : [];
        return array_values(array_map([$this, 'objectUrl'], $files));
    }

    /**
     * 将Base64图片转换为本地图片并保存
     * @param $base64_image_content 要保存的Base64
     * @param $path 要保存的路径
     * @return array|bool
     */
    private function base64_image_content($base64_image_content, $path)
    {
        //匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
            $type = $result[2];
            if (!file_exists($path)) {
                //检查是否有该文件夹，如果没有就创建，并给予755权限
                mkdir($path, 0755, true);
            }
            $filename = md5(microtime()) . ".{$type}";
            $all_path = $path . '/' . $filename;
            $content = base64_decode(str_replace($result[1], '', $base64_image_content));
            if (file_put_contents($all_path, $content)) {
                return ['path'=>$all_path, 'filename'=>$filename];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 上传预处理
     * @param array|UploadedFile $base64_arr
     * @return array|mixed|string|UploadedFile
     */
    public function prepare($base64_arr)
    {
        if (count($base64_arr) == 0 || empty($base64_arr)) {
//            $this->destroy();
            return $base64_arr;
        } else {
            $paths = [];
            foreach ($base64_arr as $base64) {
                if (preg_match('/data:image\/.*?;base64/is',$base64)) {
                    $image = $this->base64_image_content($base64,public_path('uploads/base64img_cache'));
                    if ($image !== false) {
                        $image = new UploadedFile($image['path'],$image['filename']);
                        $this->name = $this->getStoreName($image);
                        $this->callInterventionMethods($image->getRealPath());
                        $path = $this->uploadAndDeleteOriginal($image);
                        array_push($paths, $path);
                    }
                } else
                    array_push($paths, $base64);
            }
        }
        return $paths;
    }

    protected function uploadAndDeleteOriginal(UploadedFile $file)
    {
        $this->renameIfExists($file);

        $path = null;

        if (!is_null($this->storagePermission)) {
            $path = $this->storage->putFileAs($this->getDirectory(), $file, $this->name, $this->storagePermission);
        } else {
            $path = $this->storage->putFileAs($this->getDirectory(), $file, $this->name);
        }

//        $this->destroy();

        return $path;
    }


    public function cRatio($width,$height)
    {
        if (!empty($width) and is_numeric($width)) {
            $this->attributes['data-w'] = $width;
        } else {
            $this->attributes['data-w'] = $this->ratioW;
        }
        if (!empty($height) and is_numeric($height)) {
            $this->attributes['data-h'] = $height;
        } else {
            $this->attributes['data-h'] = $this->ratioH;
        }
        return $this;
    }

    public function render()
    {
        $this->name = $this->formatName($this->column);
        $cTitle = trans("admin_cropper.title");
        $cDone = trans("admin_cropper.done");
        $cOrigin = trans("admin_cropper.origin");
        $cClear = trans("admin_cropper.clear");
        $script = <<<EOT

        //图片类型预存

        function getMIME(url)
        {
            var preg = new RegExp('data:(.*);base64','i');
            var result = preg.exec(url);
            //console.log(result);
            if (result != null) {
                return result[1];
            } else {
                var ext = url.substring(url.lastIndexOf(".") + 1).toLowerCase();
                return 'image/' + ext
            }
        }

        //  替换预览图,替换提交数据
function fillImgAndInput(id, base64url) {
    if (id == 1) {
        if (base64url)
            $('#cropper-img1').attr('src', base64url);
        else
            $('#cropper-img1').removeAttr('src');
        $('#cropper-input1').val(base64url);
    }else if (id == 2) {
        if (base64url)
            $('#cropper-img2').attr('src', base64url);
        else
            $('#cropper-img2').removeAttr('src');
        $('#cropper-input2').val(base64url);
    } else {
        if (base64url)
            $('#cropper-img3').attr('src', base64url);
        else
            $('#cropper-img3').removeAttr('src');
        $('#cropper-input3').val(base64url);
    }
}

function croppers(imgSrc,cropperFileE)
{
    var w = $(cropperFileE).attr('data-w');
    var h = $(cropperFileE).attr('data-h');
    var crop_file_id = $(cropperFileE).attr('id').charAt($(cropperFileE).attr('id').length-1);
    var cropperImg = '<div id="croppings-div">';
    if (imgSrc)
        cropperImg += '<img id="croppings-img" src="' + imgSrc + '">';
    else
        cropperImg += '<img id="croppings-img" >';
    cropperImg += '</div>';
    //生成弹层模块
    layer.open({
        zIndex: 3000,
        type: 1,
        skin: 'layui-layer-demo', //样式类名
        area: ['800px', '600px'],
        closeBtn: 2, //第二种关闭按钮
        anim: 2,
        resize: false,
        shadeClose: false, //关闭遮罩关闭
        title: '$cTitle',
        content: cropperImg,
        btn: ['$cDone','$cOrigin','$cClear'],
        btn1: function(){
            var cas = cropper.getCroppedCanvas({
                width: w,
                height: h
            });
            //剪裁数据转换base64
            var base64url = cas.toDataURL(getMIME(imgSrc));
            //剪裁提交
            fillImgAndInput(crop_file_id, base64url);
            //销毁剪裁器实例
            cropper.destroy();
            layer.closeAll('page');
        },
        btn2:function(){
            //原数据提交
            fillImgAndInput(crop_file_id, imgSrc);
            //销毁剪裁器实例
            cropper.destroy();
        },
        btn3:function(){
            //销毁剪裁器实例
            cropper.destroy();
            layer.closeAll('page');
            //清空提交
            fillImgAndInput(crop_file_id, '');
            //清空文件选择器
            cropperFileE.val('');
        }
    });

    var image = document.getElementById('croppings-img');
    var cropper = new Cropper(image, {
        aspectRatio: w / h,
        viewMode: 2,
    });
}

//选择按钮

$('form div').on('click','.cropper-btn',function(){
    if ($(this).attr('id') == 'cropper-btn1')
        $('#cropper-file1').click();
    if ($(this).attr('id') == 'cropper-btn2')
        $('#cropper-file2').click();
    if ($(this).attr('id') == 'cropper-btn3')
        $('#cropper-file3').click();
    return false;
});

//在input file内容改变的时候触发事件
$('form').on('change','.croppers-file',function(fileE){
    //获取input存放file的files文件数组,第一个元素使用[0];
    var file = $(this)[0].files[0];
    //创建用来读取此文件的对象
    var reader = new FileReader();
    //使用该对象读取file文件
    reader.readAsDataURL(file);
    //读取文件成功后执行的方法函数
    reader.onload = function(e){
        //调取剪切函数（内部包含了一个弹出框）
        croppers(e.target.result, $(fileE.target));
        //选择所要显示图片的img，要赋值给img的src就是e中target下result里面的base64编码格式的地址
        if ($(this).attr('id'))
            fillImgAndInput($(this).attr('id').charAt($(this).attr('id').length-1), e.target.result);
    };
    return false;
});

//点击图片触发弹层
$('form').on('click','.croppers-img',function(){
    croppers($(this).attr('src'), $(this).prevAll('.croppers-file'));
    return false;
});

EOT;

        if (!$this->display) {
            return '';
        }

        Admin::script($script);
        return view($this->getView(), $this->variables(),['preview'=>$this->preview()]);
    }

}
