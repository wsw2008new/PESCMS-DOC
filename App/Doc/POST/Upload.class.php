<?php
/**
 * PESCMS for PHP 5.4+
 *
 * Copyright (c) 2014 PESCMS (http://www.pescms.com)
 *
 * For the full copyright and license information, please view
 * the file LICENSE.md that was distributed with this source code.
 * @core version 2.6
 * @version 2.0
 */
namespace App\Doc\POST;

class Upload extends \Core\Controller\Controller {

    /**
     * 百度编辑器上传控件
     * @description 本上传方法直接基于百度原有的上传库。PESCMS在此之上进行二次安全转换（主要在图片处理上和上传目录）。
     */
    public function ueditor() {
        echo (new \Expand\UEupload\UEController())->action();
    }

    /**
     * 简单得上传控件
     */
    public function easyUpload(){
        if(!empty($_FILES['upfile'])){
            $_GET['action'] = 'uploadimage';
            $result = (new \Expand\UEupload\UEController())->action();
            $info = json_decode($result, true);
            if ($info['state'] != 'SUCCESS') {
                $this->assign('alert', $info['state']);
            } else {
                $this->assign('alert', 'SUCCESS');
                $this->assign('img', $info);
            }
        }
        $this->display();
    }

}