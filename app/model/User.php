<?php
namespace app\model;


class User
{
    private $_name;

    public function __construct(string $name='Jim')
    {
        $this->_name = $name;
    }

    public function getName()
    {
        return $this->_name;
    }


}