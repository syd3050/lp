<?php
namespace app\service;

use app\model\User;

interface IUserService
{
    public function add(User $user);

    public function del(User $user);

    public function update(User $user);

    public function query(array $conditions,int $pageNo = 1,int $pageSize = 10);
}