<?php
namespace yyk;
class Rest{
    /*
     * 返回状态码(status)
     */
    Const RE_OK         = 200;
    Const RE_CREATED    = 201;
    Const RE_ACCEPTED   = 202;
    Const RE_DELETED    = 204;
    Const RE_FAILED     = 400;
    Const RE_UNAUTHORIZED=401;
    Const RE_FORBIDDEN  = 403;
    Const RE_NOT_FOUND  = 404;
    Const RE_ERROR      = 500;
    

    /*
     * 返回状态信息(msg)
     */
    private static $errMessage = array(
        self::RE_OK         => '获取成功。',
        self::RE_CREATED    => '保存成功。',
        self::RE_ACCEPTED   => '请求已经进入后台排队。',
        self::RE_DELETED    => '删除成功。',
        self::RE_FAILED     => '操作失败。',
        self::RE_UNAUTHORIZED=>'没有权限。',
        self::RE_FORBIDDEN  => '禁止访问。',
        self::RE_NOT_FOUND  => '没有找到。',
        self::RE_ERROR      => '错误。'
    );

    /*
     * HTTP Status Message
     */
    private static $HttpMessage = array(
        self::RE_OK => 'OK',
        self::RE_CREATED => 'CREATED',
        self::RE_ACCEPTED => 'Accepted',
        self::RE_DELETED => 'NO CONTENT',
        self::RE_FAILED => 'INVALID REQUEST',
        self::RE_UNAUTHORIZED => 'Unauthorized',
        self::RE_FORBIDDEN => 'Forbidden',
        self::RE_NOT_FOUND => 'NOT FOUND',
        self::RE_ERROR => 'INTERNAL SERVER ERROR',
    );

    /*
     * 统一输出(JSON/JSONP)
     */
    protected function export($data){
        // var_dump($data);
        if (!array_key_exists($data['code'], self::$errMessage)) {
            $data['code'] = self::RE_ERROR;
        }

        if (isset($data['msg']) && strlen($data['msg'])) {
            $data['msg'] = self::$errMessage[$data['code']] . $data['msg'];
        }
        else {
            $data['msg'] = self::$errMessage[$data['code']];
        }

        header($_SERVER['SERVER_PROTOCOL'] . ' ' . $data['code'] . ' ' . self::$HttpMessage[$data['code']]);
    	//判断是否输出JSONP
    	if (isset($_GET['callback']) && Data::filter($_GET['callback'], 9)==$_GET['callback']) {
    		echo $callback."(".json_encode($data).")";
    	}
        else {
    		echo json_encode($data);
    	}
        exit();
    }
}
