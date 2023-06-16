<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-05-15 14:14:16
 * @LastEditors: light
 * @LastEditTime: 2023-05-26 14:41:49
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);

defined('SUN_IN') or exit('Sunphp Access Denied');

class SunGPC implements ArrayAccess{

    private $elements;

    public function __construct($args=[])
    {
        $this->elements = $args;
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
        return isset($this->elements[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
        if(isset($this->elements[$offset])){
            return $this->elements[$offset];
        }else{
            // 未设置值默认返回
            return '';
        }
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
        $this->elements[$offset] = $value;
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
        unset($this->elements[$offset]);
    }

}