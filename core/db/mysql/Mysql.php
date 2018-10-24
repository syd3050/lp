<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-23
 * Time: 上午11:10
 */

namespace core\db\mysql;


use core\db\DbBase;

class Mysql extends DbBase
{

    /***************************************************************
     * 使用：
     * DB::table('ta')->update(['c1'=>1,'c2'=>2],['c3'=>3,'c4'=>4]);
     * 相当于：
     * SQL: UPDATE ta SET c3=? AND c4=? WHERE c1=? AND c2=?
     * 参数: [3,4,1,2]
     *
     * @param array $conditions 条件命名数组
     * @param array $params     新值命名数组
     * @return bool
     * *************************************************************
     */
    public function update($conditions , $params)
    {
        if(empty($conditions) || empty($params))
            return false;
        list($set,$param_arr) = $this->_parse_params($params);
        list($where,$condition_arr) = $this->_parse_params($conditions);
        $param_arr = array_merge($param_arr,$condition_arr);
        $sql = "UPDATE {$this->_table} SET {$set} WHERE {$where}";
        return $this->execute($sql,$param_arr,self::TIME_OUT);
    }

    /**
     * 保存数据，支持单个保存和批量保存，例如：
     * $data = [
          ['name'=>'abc','pwd'=>'123'],
          ['name'=>'abcd','pwd'=>'123'],
          ['name'=>'abce','pwd'=>'123'],
       ] 或
     * $data = ['name'=>'abc','pwd'=>'123'];
     * @param $data
     * @return bool
     */
    public function save($data)
    {
        if(empty($data))
            return false;
        //批量保存
        if(dimension($data) > 1) {
            $one = $data[key($data)];
            $keys = array_keys($one);
            $columns = '('. implode(',',$keys) . ')';
            $columns_num = count($keys);
            $values = [];
            $tokens = array_fill(0,$columns_num,'?');
            $str = '('. implode(',',$tokens). ')';
            $values_str = '';
            foreach ($data as $item) {
                $values = array_merge($values,array_values($item));
                $values_str .= $str.',';
            }
            $values_str = rtrim($values_str,',');
        }else{
            $keys = array_keys($data);
            $columns = '('. implode(',',$keys) . ')';
            $columns_num = count($keys);
            $values = array_values($data);
            $values_str = array_fill(0,$columns_num,'?');
            $values_str = '('. implode(',',$values_str) . ')';
        }
        $sql = "INSERT INTO {$this->_table} {$columns} VALUES {$values_str};";
        return $this->execute($sql,$values,-1);
    }

    /**
     * @param $sql
     * @param int $timeout 超时时间，$timeout如果小于或等于0，表示永不超时。在规定的时间内MySQL服务器未能返回数据，底层将返回false，设置错误码为110，并切断连接
     * @return mixed
     */
    public function query($sql,$timeout = -1)
    {
        $db = $this->_pool->getFromPool();
        $db->setDefer();
        $r = $db->query($sql, $timeout);
        if(!$r) {
            //重连
            if(!($db = $this->_reconnect($db)))
                return null;
        }
        $r = $db->recv();
        if(!$r) {
            //重连
            if(!($db = $this->_reconnect($db)))
                return null;
            $r = $db->recv();
        }
        $this->_pool->backToPool($db);
        return $r;
    }

    /***************************************************************
     * 直接执行SQL语句，SQL语句可带 ? 占位符，或者命名参数
     * 例如：
     * 1:
     * SQL: select * from ta where c1=? and c2=?
     * 参数: [5,8]
     * 2:
     * SQL: select * from ta where c1=:c1 and c2=:c2;
     * 参数:['c1'=>5,'c2'=>8]
     * 当参数不多时，推荐使用第一种?占位符
     *
     * @param string $sql    SQL语句可带 ? 占位符，或者命名参数
     * @param array $params  键为数字的数组或命名参数数组
     * @param float $timeout 超时时间，单位秒，可使用小数设为毫秒：0.001
     * @return array|bool|null
     * *************************************************************
     */
    public function execute($sql,$params=[],$timeout=-1)
    {
        list($sql,$params) = $this->parseSql($sql,$params);
        dev_dump([
            //'connection'=>md5($this->_pool),
            'execute'=>'poolsize:'.$this->_pool->poolSize()
        ]);
        /**
         * @var $db
         */
        $db = $this->_pool->getFromPool();
        /**
         * @var $stmt
         */
        $stmt = $db->prepare($sql);
        if($stmt == false) {
            //重连
            if(!($db = $this->_reconnect($db)))
                return null;
            $stmt = $db->prepare($sql);
        }
        //var_dump(['param'=>$params,'sql'=>$sql]);
        $r = $stmt->execute($params,$timeout);
        //var_dump(['execute-before-back'=>$this->_pool->poolSize()]);
        $this->_pool->backToPool($db);
        return  $r;
    }

    /**
     * 重连
     * @param  \Swoole\Coroutine\Mysql $db
     * @return \Swoole\Coroutine\Mysql | bool
     */
    protected function _reconnect($db)
    {
        if ($db->errno == 2006 or $db->errno == 2013)
        {
            $times = $this->_pool->poolSize() + 1;
            do{
                $db = $this->_pool->getFromPool();
                $times--;
            }while(!$db && $times);
            return $db;
        }
        return false;
    }

    protected function _buildSql()
    {
        $sql = "SELECT {$this->_columns} FROM {$this->_table} WHERE {$this->_where} ";
        if(!empty($this->_group_by)) {
            $sql .= "GROUP BY {$this->_group_by} ";
        }
        if(!empty($this->_having)) {
            $sql .= "HAVING {$this->_having} ";
        }
        if(!empty($this->_order_by)) {
            $sql .= "ORDER BY {$this->_order_by} ";
        }
        if($this->_limit_offset != -1) {
            $sql .= "LIMIT {$this->_limit_offset},{$this->_limit_len} ";
        }
        return $sql;
    }

    /**
     * 将含有命名参数的SQL转换为 ? 占位符的SQL和对应次序的参数数组
     * 例如 :
     * $sql = select a from table_a where c2=:v2 and c1=:v1
     * $params = ['v1'=>123, 'v2'=>456]
     * 解析后返回：
     * ['select a from table_a where c2=? and c1=?', [456,123]]
     * @param $sql
     * @param $params
     * @return array [$sql,$param]
     */
    private function parseSql($sql,$params)
    {
        //不含命名参数则直接返回
        if(strpos($sql,':') === false) {
            return array($sql,$params);
        }
        $r_params = array();
        $pos_array = array();
        foreach ($params as $pk=>$param) {
            $pos_array[$param] = strpos($sql,":{$pk}");
            $sql = str_replace(":{$pk}",' ? ', $sql);
        }
        asort($pos_array);
        foreach ($pos_array as $k=>$pos) {
            $r_params[] = $k;
        }
        empty($r_params) && $r_params = $params;
        return array($sql,$r_params);
    }

    /**
     * 解析命名数组，产生查询条件和条件值数组
     * @param array $params 命名数组
     * @return array
     */
    protected function _parse_params($params)
    {
        $condition = '';
        $param_arr = array();
        foreach ($params as $column => $v) {
            $condition .= " {$column} = ? AND";
            $param_arr[] = $v;
        }
        $condition = rtrim($condition,'AND');
        return array($condition,$param_arr);
    }
}