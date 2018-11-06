<?php
namespace app\model;


class User
{
    private $_name;

    public function __construct(string $name)
    {
        $this->_name = $name;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setName(String $name)
    {
        $this->_name = $name;
    }
}