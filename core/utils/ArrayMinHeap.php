<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-17
 * Time: 下午4:13
 */

namespace core\utils;


class ArrayMinHeap extends \SplMinHeap
{

    /**
     * @var bool 默认按KEY比较
     */
    private $by_key = true;

    public function __construct($by_key = true)
    {
        $this->by_key = boolval($by_key);
    }

    public function compare($value1, $value2)
    {
        if($this->by_key) {
            $v1 = array_keys($value1)[0];
            $v2 = array_keys($value2)[0];
        }else{
            $v1 = array_values($value1)[0];
            $v2 = array_values($value2)[0];
        }
        return $v1 < $v2 ? 1 : ($v1 == $v2 ? 0 : -1);
    }
}