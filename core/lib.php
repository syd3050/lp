<?php

if(!function_exists("isAPP"))
{
  function isAPP()
  {
	  	$from = empty($_SERVER['REQUEST_URI']) ? '' : $_SERVER['REQUEST_URI'] . '&';
	  	$from .= empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'] . '&';
	  	foreach (array('app', 'iso', 'android') as $item) {
	    	if (stripos($from, 'from=' . $item . '&') !== FALSE)
	    		return TRUE;
	  	}
	  	return FALSE;
	}
}

if(!function_exists("randStr"))
{
    function randStr($len=10)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $string = time();
        for(;$len>=1;$len--) {
            $position = rand()%strlen($chars);
            $position2 = rand()%strlen($string);
            $string = substr_replace($string,substr($chars,$position,1),$position2,0);
        }
        return $string;
    }
}

if(!function_exists('getV')) {
    function getV($arr,$key,$default='')
    {
        return empty($arr[$key]) ? $default : $arr[$key];
    }
}

if(!function_exists('isAjax'))
{
    function isAjax()
    {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            return true;
        elseif (isset($_SERVER['HTTP_ACCEPT']) && strtolower($_SERVER['HTTP_ACCEPT']) == 'application/json')
            return true;
        else
            return false;
    }
}

if(!function_exists('in_array_ext'))
{
    /**
     * 查看needle是否在数组$haystack中，不考虑key
     * $needle中任何一个元素出现在$haystack中都返回true
     * @param string|array $needle 只支持二维数组，数组中任何一个元素出现在haystack中都返回true
     * @param array $haystack
     * @return bool
     */
    function in_array_ext($needle,$haystack)
    {
        if(is_array($needle))
        {
            $reverse = array_flip($haystack);
            foreach ($needle as $value)
            {
                if(isset($reverse[$value]))
                    return true;
            }
            return false;
        }
        return in_array($needle,$haystack);
    }
}

if(!function_exists('dev_dump'))
{
    function dev_dump($obj)
    {
        if($GLOBALS[ENV_KEY] == 'dev')
        {
            var_dump($obj);
        }
    }
}




