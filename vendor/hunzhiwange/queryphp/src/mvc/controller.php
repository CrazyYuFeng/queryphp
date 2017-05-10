<?php
// [$QueryPHP] A PHP Framework Since 2010.10.03. <Query Yet Simple>
// ©2010-2017 http://queryphp.com All rights reserved.
namespace Q\mvc;

<<<queryphp
##########################################################
#   ____                          ______  _   _ ______   #
#  /     \       ___  _ __  _   _ | ___ \| | | || ___ \  #
# |   (  ||(_)| / _ \| '__|| | | || |_/ /| |_| || |_/ /  #
#  \____/ |___||  __/| |   | |_| ||  __/ |  _  ||  __/   #
#       \__   | \___ |_|    \__  || |    | | | || |      #
#     Query Yet Simple      __/  |\_|    |_| |_|\_|      #
#                          |___ /  Since 2010.10.03      #
##########################################################
queryphp;

use Q\exception\exceptions;
use Q\asset\test;
use Q\http\request;
use Q\http\response;

/**
 * 基类控制器
 *
 * @author Xiangmin Liu<635750556@qq.com>
 * @package $$
 * @since 2016.11.19
 * @version 1.0
 */
class controller {
    
    /**
     * 构造函数
     *
     * @return void
     */
    public function __construct() {
    }
    
    /**
     * 赋值
     *
     * @param mixed $mixName            
     * @param mixed $Value            
     * @return void
     */
    public function __set($mixName, $mixValue) {
        $this->assign ( $mixName, $mixValue );
    }
    
    /**
     * 获取值
     *
     * @param string $sName            
     * @return mixed
     */
    public function &__get($sName) {
        $mixValue = $this->getAssign ( $sName );
        return $mixValue;
    }
    
    /**
     * 执行子方法器
     *
     * @param string $sActionName
     *            方法名
     * @return void
     */
    public function action($sActionName) {
        // 判断是否已经注册过
        if (($objAction = $this->project ()->make ( 'app' )->getAction ( $this->project ()->controller_name, $sActionName )) && ! (is_array ( $objAction ) && isset ( $objAction [1] ) && test::isKindOf ( $objAction [0], 'Q\mvc\controller' ))) {
            return $this->project ()->make ( 'app' )->action ( $this->project ()->controller_name, $sActionName );
        }
        
        // 读取默认方法器
        $sActionName = get_class ( $this ) . '\\' . $sActionName;
        if (class_exists ( $sActionName )) {
            // 注册方法器
            $this->project ()->make ( 'app' )->registerAction ( $this->project ()->controller_name, $sActionName, [ 
                    $sActionName,
                    'run' 
            ] );
            
            // 运行方法器
            return $this->project ()->make ( 'app' )->action ( $this->project ()->controller_name, $sActionName );
        } else {
            exceptions::throws ( __ ( '控制器 %s 的方法 %s 不存在', get_class ( $this ), $sActionName ), 'Q\mvc\exception' );
        }
    }
    
    /**
     * 赋值
     *
     * @param 变量或变量数组集合 $Name            
     * @param string $Value            
     * @return this
     */
    public function assign($Name, $Value = '') {
        $this->project ()->make ( 'view' )->assign ( $Name, $Value );
        return $this;
    }
    
    /**
     * 加载视图文件
     *
     * @param string $sThemeFile            
     * @param array $in
     *            charset 编码
     *            content_type 类型
     *            return 是否返回 html 返回而不直接输出
     * @return mixed
     */
    public function display($sThemeFile = '', $in = []) {
        $in = array_merge ( [ 
                'charset' => 'utf-8',
                'content_type' => 'text/html',
                'return' => false 
        ], $in );
        return $this->project ()->make ( 'view' )->display ( $sThemeFile, $in );
    }
    
    /**
     * 返回项目容器
     *
     * @return \Q\mvc\project
     */
    public function project() {
        return project::bootstrap ();
    }
    
    /**
     * 取回赋值
     *
     * @param 变量名字 $sName            
     * @return mixed
     */
    protected function getAssign($sName) {
        return $this->project ()->make ( 'view' )->getVar ( $sName );
    }
    
    /**
     * 错误返回消息
     *
     * @param $sMessage 消息            
     * @param $in message
     *            消息内容
     *            url 跳转 url 地址
     *            time 停留时间
     *            
     * @return json
     */
    protected function error($sMessage = '', $in = []) {
        $in = array_merge ( [ 
                'message' => $sMessage ?  : __ ( '操作失败' ),
                'url' => '',
                'time' => 3 
        ], $in );
        $this->assign ( $in );
        $this->display ( $GLOBALS ['~@option'] ['theme_action_fail'] );
    }
    
    /**
     * 正确返回消息
     *
     * @param $sMessage 消息            
     * @param $in message
     *            消息内容
     *            url 跳转 url 地址
     *            time 停留时间
     *            
     * @return json
     */
    protected function success($sMessage = '', $in = []) {
        $in = array_merge ( [ 
                'message' => $sMessage ?  : __ ( '操作成功' ),
                'url' => '',
                'time' => 1 
        ], $in );
        $this->assign ( $in );
        $this->display ( $GLOBALS ['~@option'] ['theme_action_success'] );
    }
    
    /**
     * json 格式化
     *
     * @param $sMessage 消息            
     * @param $in status
     *            状态 fail = 失败，success = 成功
     *            message 消息内容
     *            
     * @return json
     */
    protected function json($sMessage = '', $in = []) {
        $in = array_merge ( [ 
                'status' => 'success',
                'message' => $sMessage 
        ], $in );
        header ( "Content-Type:text/html; charset=utf-8" );
        exit ( json_encode ( $in, JSON_UNESCAPED_UNICODE ) );
    }
    
    /**
     * URL 跳转
     *
     * @param string $sUrl            
     * @param 额外参数 $in
     *            params url 额外参数
     *            message 消息
     *            time 停留时间，0表示不停留
     * @return void
     */
    protected function redirect($sUrl, $in = []) {
        return response::redirects ( $sUrl, $in );
    }
    
    /**
     * 实现 isPost,isGet等
     *
     * @param 方法名 $sMethod            
     * @param 参数 $arrArgs            
     * @return boolean
     */
    public function __call($sMethod = '', $arrArgs = []) {
        switch ($sMethod) {
            case 'isPost' :
                return request::isPosts ();
            case 'isGet' :
                return request::isGets ();
            case 'in' :
                if (! empty ( $arrArgs [0] )) {
                    return request::ins ( $arrArgs [0], isset ( $arrArgs [1] ) ? $arrArgs [1] : 'R' );
                } else {
                    exceptions::throws ( 'Can not find method.', 'Q\mvc\exception' );
                }
            default :
                exceptions::throws ( __ ( '控制器 %s 的方法 %s 不存在', get_class ( $this ), $sMethod ), 'Q\mvc\exception' );
        }
    }
}
