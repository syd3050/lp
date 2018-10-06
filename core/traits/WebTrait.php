<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-26
 * Time: 下午1:46
 */
namespace core\traits;

trait WebTrait
{
    /**
     * 跳转
     * @param string $url 目标地址
     * @param string $info 提示信息
     * @param int $sec 等待时间
     */
    public function redirect($url,$info=null,$sec=3)
    {
        if(is_null($info))
            header("Location:$url");
        else
        {
            echo"<meta http-equiv=\"refresh\" content=".$sec.";URL=".$url.">";
            echo $info;
        }
        exit;
    }

    /**
     * 返回Ajax格式信息
     * @param $result
     * @param int $options
     * @param string $charset
     */
    public function ajaxReturn($result,$options=0,$charset='utf-8')
    {
        $content = json_encode($result,$options);
        header("Content-Type:text/html; charset=$charset");
        echo $content;
    }
}