<?php
// [$QueryPHP] A PHP Framework Since 2010.10.03. <Query Yet Simple>
// ©2010-2017 http://queryphp.com All rights reserved.
namespace Q\router;

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

use Q\traits\dynamic\expansion as dynamic_expansion;
use Q\http\request;
use Q\safe\safe;

/**
 * 路由解析
 *
 * @author Xiangmin Liu<635750556@qq.com>
 * @package $$
 * @since 2017.01.10
 * @version 1.0
 */
class router {
    
    use dynamic_expansion;
    
    /**
     * 注册域名
     *
     * @var array
     */
    private $arrDomains = [ ];
    
    /**
     * 注册路由
     *
     * @var array
     */
    private $arrRouters = [ ];
    
    /**
     * 参数正则
     *
     * @var array
     */
    private $arrWheres = [ ];
    
    /**
     * 域名正则
     *
     * @var array
     */
    private $arrDomainWheres = [ ];
    
    /**
     * 默认替换参数[字符串]
     *
     * @var string
     */
    const DEFAULT_REGEX = '\S+';
    
    /**
     * 分组传递参数
     *
     * @var array
     */
    private $arrGroupArgs = [ ];
    
    /**
     * 配置文件路由
     *
     * @var string
     */
    private $arrFileRouters = [ ];
    
    /**
     * 路由绑定资源
     *
     * @var string
     */
    private $arrBinds = [ ];
    
    /**
     * 域名匹配数据
     *
     * @var array
     */
    private $arrDomainData = [ ];
    
    /**
     * 导入路由规则
     *
     * @param mixed $mixRouter            
     * @param string $strUrl            
     * @param arra $in
     *            domain 域名
     *            params 参数
     *            where 参数正则
     *            prepend 插入顺序
     *            strict 严格模式，启用将在匹配正则 $
     *            prefix 前缀
     * @return void
     */
    public function import($mixRouter, $strUrl = '', $in = []) {
        // 默认参数
        $in = $this->mergeIn_ ( [ 
                'prepend' => false,
                'where' => [ ],
                'params' => [ ],
                'domain' => '',
                'prefix' => '' 
        ], $this->mergeIn_ ( $this->arrGroupArgs, $in ) );
        
        // 支持数组传入
        if (! is_array ( $mixRouter ) || count ( $mixRouter ) == count ( $mixRouter, 1 )) {
            $strTemp = $mixRouter;
            $mixRouter = [ ];
            if (is_string ( $strTemp )) {
                $mixRouter [] = [ 
                        $strTemp,
                        $strUrl,
                        $in 
                ];
            } else {
                if ($strUrl || $strTemp [1]) {
                    $mixRouter [] = [ 
                            $strTemp [0],
                            (! empty ( $strTemp [1] ) ? $strTemp [1] : $strUrl),
                            $in 
                    ];
                }
            }
        } else {
            foreach ( $mixRouter as $intKey => $arrRouter ) {
                if (! is_array ( $arrRouter ) || count ( $arrRouter ) < 2) {
                    continue;
                }
                if (! isset ( $arrRouter [2] )) {
                    $arrRouter [2] = [ ];
                }
                if (! $arrRouter [1]) {
                    $arrRouter [1] = $strUrl;
                }
                $arrRouter [2] = $this->mergeIn_ ( $in, $arrRouter [2] );
                $mixRouter [$intKey] = $arrRouter;
            }
        }
        
        foreach ( $mixRouter as $arrArgs ) {
            $strPrefix = ! empty ( $arrArgs [2] ['prefix'] ) ? $arrArgs [2] ['prefix'] : '';
            $arrArgs [0] = $strPrefix . $arrArgs [0];
            
            $arrRouter = [ 
                    'url' => $arrArgs [1],
                    'regex' => $arrArgs [0],
                    'params' => $arrArgs [2] ['params'],
                    'where' => $this->arrWheres,
                    'domain' => $arrArgs [2] ['domain'] 
            ];
            
            if (isset ( $arrArgs [2] ['strict'] )) {
                $arrRouter ['strict'] = $arrArgs [2] ['strict'];
            }
            
            // 合并参数正则
            if (! empty ( $arrArgs [2] ['where'] ) && is_array ( $arrArgs [2] ['where'] )) {
                $arrRouter ['where'] = $this->mergeWhere_ ( $arrRouter ['where'], $arrArgs [2] ['where'] );
            }
            
            if (! isset ( $this->arrRouters [$arrArgs [0]] )) {
                $this->arrRouters [$arrArgs [0]] = [ ];
            }
            
            // 优先插入
            if ($arrArgs [2] ['prepend'] === true) {
                array_unshift ( $this->arrRouters [$arrArgs [0]], $arrRouter );
            } else {
                array_push ( $this->arrRouters [$arrArgs [0]], $arrRouter );
            }
            
            // 域名支持
            if (! empty ( $arrRouter ['domain'] )) {
                $in ['router'] = true;
                $this->domain ( $arrRouter ['domain'], $arrArgs [0], $in );
            }
        }
    }
    
    /**
     * 注册全局参数正则
     *
     * @param mixed $mixRegex            
     * @param string $strValue            
     * @return void
     */
    public function regex($mixRegex, $strValue = '') {
        if (is_string ( $mixRegex )) {
            $this->arrWheres [$mixRegex] = $strValue;
        } else {
            $this->arrWheres = $this->mergeWhere_ ( $this->arrWheres, $mixRegex );
        }
    }
    
    /**
     * 注册全局域名参数正则
     *
     * @param mixed $mixRegex            
     * @param string $strValue            
     * @return void
     */
    public function regexDomain($mixRegex, $strValue = '') {
        if (is_string ( $mixRegex )) {
            $this->arrDomainWheres [$mixRegex] = $strValue;
        } else {
            $this->arrDomainWheres = $this->mergeWhere_ ( $this->arrDomainWheres, $mixRegex );
        }
    }
    
    /**
     * 注册域名
     *
     * @param string $strDomain            
     * @param mixed $mixUrl            
     * @param array $in
     *            params 扩展参数
     *            domain_where 域名参数
     *            prepend 插入顺序
     *            router 对应路由规则
     * @return void
     */
    public function domain($strDomain, $mixUrl, $in = []) {
        $in = $this->mergeIn_ ( [ 
                'prepend' => false,
                'params' => [ ],
                'domain_where' => [ ],
                'router' => false 
        ], $in );
        
        // 闭包直接转接到分组
        if ($mixUrl instanceof \Closure) {
            $in ['domain'] = $strDomain;
            $this->group ( $in, $mixUrl );
        }         

        // 注册域名
        else {
            $arrDomain = [ 
                    'url' => $mixUrl,
                    'params' => $in ['params'],
                    'router' => $in ['router'] 
            ];
            
            // 合并参数正则
            $arrDomainWheres = $this->arrDomainWheres;
            if (! empty ( $in ['domain_where'] ) && is_array ( $in ['domain_where'] )) {
                $arrDomainWheres = $this->mergeWhere_ ( $in ['domain_where'], $arrDomainWheres );
            }
            
            // 主域名只有一个，路由可以有多个
            $strDomainBox = $arrDomain ['router'] === false ? 'main' : 'rule';
            if (! isset ( $this->arrDomains [$strDomain] )) {
                $this->arrDomains [$strDomain] = [ ];
            }
            $this->arrDomains [$strDomain] ['domain_where'] = $arrDomainWheres;
            if (! isset ( $this->arrDomains [$strDomain] [$strDomainBox] )) {
                $this->arrDomains [$strDomain] [$strDomainBox] = [ ];
            }
            
            // 纯域名绑定只支持一个，可以被覆盖
            if ($arrDomain ['router'] === false) {
                $this->arrDomains [$strDomain] [$strDomainBox] = $arrDomain;
            } else {
                // 优先插入
                if ($in ['prepend'] === true) {
                    array_unshift ( $this->arrDomains [$strDomain] [$strDomainBox], $arrDomain );
                } else {
                    array_push ( $this->arrDomains [$strDomain] [$strDomainBox], $arrDomain );
                }
            }
        }
    }
    
    /**
     * 注册分组路由
     *
     * @param array $in
     *            prefix 前缀
     *            domain 域名
     *            params 参数
     *            where 参数正则
     *            prepend 插入顺序
     *            strict 严格模式，启用将在匹配正则 $
     * @param mixed $mixRouter            
     * @return void
     */
    public function group(array $in, $mixRouter) {
        // 分组参数叠加
        $this->arrGroupArgs = $in = $this->mergeIn_ ( $this->arrGroupArgs, $in );
        
        if ($mixRouter instanceof \Closure) {
            call_user_func_array ( $mixRouter, [ ] );
        } else {
            if (! is_array ( current ( $mixRouter ) )) {
                $mixRouter = [ 
                        $mixRouter 
                ];
            }
            
            foreach ( $mixRouter as $arrVal ) {
                if (! is_array ( $arrVal ) || count ( $arrVal ) < 2) {
                    continue;
                }
                
                if (! isset ( $arrVal [2] )) {
                    $arrVal [2] = [ ];
                }
                
                $this->import ( $strPrefix . $arrVal [0], $arrVal [1], $this->mergeIn_ ( $in, $arrVal [2] ) );
            }
        }
        
        $this->arrGroupArgs = [ ];
    }
    
    /**
     * 导入路由配置数据
     *
     * @param array $arrData            
     * @return void
     */
    public function cache($arrData) {
        if (isset ( $arrData ['~domains~'] )) {
            foreach ( $arrData ['~domains~'] as $arrVal ) {
                if (is_array ( $arrVal ) && isset ( $arrVal [1] )) {
                    empty ( $arrVal [2] ) && $arrVal [2] = [ ];
                    $this->domain ( $arrVal [0], $arrVal [1], $arrVal [2] );
                }
            }
            unset ( $arrData ['~domains~'] );
        }
        
        if ($arrData) {
            $this->import ( $arrData );
        }
    }
    
    /**
     * 获取绑定资源
     *
     * @param string $sBindName            
     * @return mixed
     */
    public function getBind($sBindName) {
        return isset ( $this->arrBinds [$sBindName] ) ? $this->arrBinds [$sBindName] : null;
    }
    
    /**
     * 判断是否绑定资源
     *
     * @param string $sBindName            
     * @return boolean
     */
    public function hasBind($sBindName) {
        return isset ( $this->arrBinds [$sBindName] ) ? true : false;
    }
    
    /**
     * 注册绑定资源
     *
     * 注册控制器：router::bind( 'group://topic', $mixBind )
     * 注册方法：router::bind( 'group://topic/index', $mixBind )
     *
     * @param string $sBindName            
     * @param mixed $mixBind            
     * @return void
     */
    public function bind($sBindName, $mixBind) {
        $this->arrBinds [$sBindName] = $mixBind;
    }
    
    /**
     * 匹配路由
     *
     * @return void
     */
    public function parse() {
        $arrNextParse = [ ];
        
        // 解析域名
        if ($GLOBALS ['~@option'] ['url_router_domain_on'] === true) {
            if (($arrParseData = $this->parseDomain_ ( $arrNextParse )) !== false) {
                return $arrParseData;
            }
        }
        
        // 解析路由
        $arrNextParse = $arrNextParse ? array_column ( $arrNextParse, 'url' ) : [ ];
        return $this->parseRouter_ ( $arrNextParse );
    }
    
    /**
     * 生成路由地址
     *
     * @param string $sUrl            
     * @param array $arrParams            
     * @param array $in
     *            suffix boolean 是否包含后缀
     *            normal boolean 是否为普通 url
     *            subdomain string 子域名
     * @return string
     */
    public static function url($sUrl, $arrParams = [], $in = []) {
        $in = array_merge ( [ 
                'suffix' => true,
                'normal' => false,
                'subdomain' => 'www' 
        ], $in );
        
        $in ['args_app'] = \Q\mvc\project::ARGS_APP;
        $in ['args_controller'] = \Q\mvc\project::ARGS_CONTROLLER;
        $in ['args_action'] = \Q\mvc\project::ARGS_ACTION;
        
        // 以 “/” 开头的为自定义URL
        $in ['custom'] = false;
        if (0 === strpos ( $sUrl, '/' )) {
            $in ['custom'] = true;
        }         

        // 普通 url
        else {
            if ($sUrl != '') {
                if (! strpos ( $sUrl, '://' )) {
                    $sUrl = $_GET [$in ['args_app']] . '://' . $sUrl;
                }
                
                // 解析 url
                $arrArray = parse_url ( $sUrl );
            } else {
                $arrArray = [ ];
            }
            
            $in ['app'] = isset ( $arrArray ['scheme'] ) ? $arrArray ['scheme'] : $_GET [$in ['args_app']]; // APP
                                                                                                            
            // 分析获取模块和操作(应用)
            if (! empty ( $arrParams [$in ['args_app']] )) {
                $in ['app'] = $arrParams [$in ['args_app']];
                unset ( $arrParams [$in ['args_app']] );
            }
            if (! empty ( $arrParams [$in ['args_controller']] )) {
                $in ['controller'] = $arrParams [$in ['args_controller']];
                unset ( $arrParams [$in ['args_controller']] );
            }
            if (! empty ( $arrParams [$in ['args_action']] )) {
                $in ['action'] = $arrParams [$in ['args_action']];
                unset ( $arrParams [$in ['args_action']] );
            }
            if (isset ( $arrArray ['path'] )) {
                if (! isset ( $in ['controller'] )) {
                    if (! isset ( $arrArray ['host'] )) {
                        $in ['controller'] = $_GET [\Q\mvc\project::ARGS_CONTROLLER];
                    } else {
                        $in ['controller'] = $arrArray ['host'];
                    }
                }
                
                if (! isset ( $in ['action'] )) {
                    $in ['action'] = substr ( $arrArray ['path'], 1 );
                }
            } else {
                if (! isset ( $in ['controller'] )) {
                    $in ['controller'] = $_GET [\Q\mvc\project::ARGS_CONTROLLER];
                }
                if (! isset ( $in ['action'] )) {
                    $in ['action'] = $arrArray ['host'];
                }
            }
            
            // 如果指定了查询参数
            if (isset ( $arrArray ['query'] )) {
                $arrQuery = [ ];
                parse_str ( $arrArray ['query'], $arrQuery );
                $arrParams = array_merge ( $arrQuery, $arrParams );
            }
        }
        
        // 如果开启了URL解析，则URL模式为非普通模式
        if (($GLOBALS ['~@option'] ['url_model'] == 'pathinfo' && $in ['normal'] === false) || $in ['custom'] === true) {
            // 非自定义 url
            if ($in ['custom'] === false) {
                // 额外参数
                $sStr = '/';
                foreach ( $arrParams as $sVar => $sVal ) {
                    $sStr .= $sVar . '/' . urlencode ( $sVal ) . '/';
                }
                $sStr = substr ( $sStr, 0, - 1 );
                
                // 分析 url
                $sUrl = ($GLOBALS ['~@url'] ['url_enter'] !== '/' ? $GLOBALS ['~@url'] ['url_enter'] : '') . ($GLOBALS ['~@option'] ['default_app'] != $in ['app'] ? '/' . $in ['app'] . '/' : '/');
                
                if ($sStr) {
                    $sUrl .= $in ['controller'] . '/' . $in ['action'] . $sStr;
                } else {
                    $sTemp = '';
                    if ($GLOBALS ['~@option'] ['default_controller'] != $in ['controller'] || $GLOBALS ['~@option'] ['default_action'] != $in ['action']) {
                        $sTemp .= $in ['controller'];
                    }
                    if ($GLOBALS ['~@option'] ['default_action'] != $in ['action']) {
                        $sTemp .= '/' . $in ['action'];
                    }
                    
                    if ($sTemp == '') {
                        $sUrl = rtrim ( $sUrl, '/' . '/' );
                    } else {
                        $sUrl .= $sTemp;
                    }
                    unset ( $sTemp );
                }
            }             

            // 自定义 url
            else {
                // 自定义支持参数变量替换
                if (strpos ( $sUrl, '{' ) !== false) {
                    $sUrl = preg_replace_callback ( "/{(.+?)}/", function ($arrMatches) use(&$arrParams) {
                        if (isset ( $arrParams [$arrMatches [1]] )) {
                            $sReturn = $arrParams [$arrMatches [1]];
                            unset ( $arrParams [$arrMatches [1]] );
                        } else {
                            $sReturn = $arrMatches [1];
                        }
                        return $sReturn;
                    }, $sUrl );
                }
                
                // 额外参数
                $sStr = '/';
                foreach ( $arrParams as $sVar => $sVal ) {
                    $sStr .= $sVar . '/' . urlencode ( $sVal ) . '/';
                }
                $sStr = substr ( $sStr, 0, - 1 );
                
                $sUrl .= $sStr;
            }
            
            if ($in ['suffix'] && $sUrl) {
                $sUrl .= $in ['suffix'] === true ? $GLOBALS ['~@option'] ['url_html_suffix'] : $in ['suffix'];
            }
        }         

        // 普通url模式
        else {
            $sStr = '';
            foreach ( $arrParams as $sVar => $sVal ) {
                $sStr .= $sVar . '=' . urlencode ( $sVal ) . '&';
            }
            $sStr = rtrim ( $sStr, '&' );
            
            $sTemp = '';
            if ($in ['normal'] === true || $GLOBALS ['~@option'] ['default_app'] != $in ['app']) {
                $sTemp [] = $in ['args_app'] . '=' . $in ['app'];
            }
            if ($GLOBALS ['~@option'] ['default_controller'] != $in ['controller']) {
                $sTemp [] = $in ['args_controller'] . '=' . $in ['controller'];
            }
            if ($GLOBALS ['~@option'] ['default_action'] != $in ['action']) {
                $sTemp [] = $in ['args_action'] . '=' . $in ['action'];
            }
            if ($sStr) {
                $sTemp [] = $sStr;
            }
            if (! empty ( $sTemp )) {
                $sTemp = '?' . implode ( '&', $sTemp );
            }
            $sUrl = ($in ['normal'] === true || $GLOBALS ['~@url'] ['url_enter'] !== '/' ? $GLOBALS ['~@url'] ['url_enter'] : '') . $sTemp;
            unset ( $sTemp );
        }
        
        // 子域名支持
        if ($GLOBALS ['~@option'] ['url_make_subdomain_on'] === true) {
            if ($in ['subdomain']) {
                $sUrl = static::urlFull_ ( $in ['subdomain'] ) . $sUrl;
            }
        }
        
        return $sUrl;
    }
    
    /**
     * 返回完整 URL 地址
     *
     * @param string $sDomain
     * @param string $sHttpPrefix
     * @param string $sHttpSuffix
     * @return string
     */
    private static function urlFull_($sDomain = '', $sHttpPrefix = '', $sHttpSuffix = '') {
        static $sHttpPrefix = '', $sHttpSuffix = '';
        if (! $sHttpPrefix) {
            $sHttpPrefix = static::isSsl () ? 'https://' : 'http://';
            $sHttpSuffix = $GLOBALS ['~@option'] ['url_router_domain_top'];
        }
        return $sHttpPrefix . ($sDomain && $sDomain != '*' ? $sDomain . '.' : '') . $sHttpSuffix;
    }
    
    /**
     * 分析 url 数据
     * like [home://blog/index?arg1=1&arg2=2]
     *
     * @param string $sUrl            
     * @return array
     */
    public static function parseNodeUrl($sUrl) {
        $arrData = [ ];
        
        // 解析 url
        if (strpos ( $sUrl, '://' ) === false) {
            $sUrl = 'QueryPHP://' . $sUrl;
        }
        $sUrl = parse_url ( $sUrl );
        
        // 应用
        if ($sUrl ['scheme'] != 'QueryPHP') {
            $arrData [\Q\mvc\project::ARGS_APP] = $sUrl ['scheme'];
        }
        
        // 控制器
        $arrData [\Q\mvc\project::ARGS_CONTROLLER] = $sUrl ['host'];
        
        // 方法
        if (isset ( $sUrl ['path'] ) && $sUrl ['path'] != '/') {
            $arrData [\Q\mvc\project::ARGS_ACTION] = ltrim ( $sUrl ['path'], '/' );
        }
        
        // 额外参数
        if (isset ( $sUrl ['query'] )) {
            foreach ( explode ( '&', $sUrl ['query'] ) as $strQuery ) {
                $strQuery = explode ( '=', $strQuery );
                $arrData [$strQuery [0]] = $strQuery [1];
            }
        }
        
        return $arrData;
    }
    
    /**
     * 解析域名路由
     *
     * @param array $arrNextParse            
     * @return void
     */
    private function parseDomain_(&$arrNextParse) {
        $strHost = request::getHosts ();
        
        $booFindDomain = false;
        foreach ( $this->arrDomains as $sKey => $arrDomains ) {
            
            // 直接匹配成功
            if ($strHost === $sKey || $strHost === $sKey . '.' . $GLOBALS ['~@option'] ['url_router_domain_top']) {
                $booFindDomain = true;
            }            

            // 域名参数支持
            elseif (strpos ( $sKey, '{' ) !== false) {
                if (strpos ( $sKey, $GLOBALS ['~@option'] ['url_router_domain_top'] ) === false) {
                    $sKey = $sKey . '.' . $GLOBALS ['~@option'] ['url_router_domain_top'];
                }
                
                // 解析匹配正则
                $sKey = $this->formatRegex_ ( $sKey );
                $sKey = preg_replace_callback ( "/{(.+?)}/", function ($arrMatches) use(&$arrDomains) {
                    $arrDomains ['args'] [] = $arrMatches [1];
                    return '(' . (isset ( $arrDomains ['domain_where'] [$arrMatches [1]] ) ? $arrDomains ['domain_where'] [$arrMatches [1]] : $this->DEFAULT_REGEX) . ')';
                }, $sKey );
                $sKey = '/^' . $sKey . '$/';
                
                // 匹配结果
                if (preg_match ( $sKey, $strHost, $arrRes )) {
                    // 变量解析
                    if (isset ( $arrDomains ['args'] )) {
                        array_shift ( $arrRes );
                        foreach ( $arrDomains ['args'] as $intArgsKey => $strArgs ) {
                            $this->arrDomainData [$strArgs] = $arrRes [$intArgsKey];
                        }
                    }
                    
                    $booFindDomain = true;
                }
            }
            
            // 分析结果
            if ($booFindDomain === true) {
                if (isset ( $arrDomains ['rule'] )) {
                    $arrNextParse = $arrDomains ['rule'];
                    return false;
                } else {
                    $arrData = static::parseNodeUrl ( $arrDomains ['main'] ['url'] );
                    
                    // 额外参数[放入 GET]
                    if (is_array ( $arrDomains ['main'] ['params'] ) && $arrDomains ['main'] ['params']) {
                        $arrData = array_merge ( $arrData, $arrDomains ['main'] ['params'] );
                    }
                    
                    // 合并域名匹配数据
                    $arrData = array_merge ( $this->arrDomainData, $arrData );
                    
                    return $arrData;
                }
            }
        }
    }
    
    /**
     * 解析路由规格
     *
     * @param array $arrNextParse            
     * @return array
     */
    private function parseRouter_($arrNextParse = []) {
        $arrData = [ ];
        $sPathinfo = $_SERVER ['PATH_INFO'];
        
        // 匹配路由
        foreach ( $this->arrRouters as $sKey => $arrRouters ) {
            // 域名过滤掉无关路由
            if ($arrNextParse && ! in_array ( $sKey, $arrNextParse )) {
                continue;
            }
            
            foreach ( $arrRouters as $arrRouter ) {
                // 尝试匹配
                $booFindFouter = false;
                if ($arrRouter ['regex'] == $sPathinfo) {
                    $booFindFouter = true;
                } else {
                    // 解析匹配正则
                    $arrRouter ['regex'] = $this->formatRegex_ ( $arrRouter ['regex'] );
                    $arrRouter ['regex'] = preg_replace_callback ( "/{(.+?)}/", function ($arrMatches) use(&$arrRouter) {
                        $arrRouter ['args'] [] = $arrMatches [1];
                        return '(' . (isset ( $arrRouter ['where'] [$arrMatches [1]] ) ? $arrRouter ['where'] [$arrMatches [1]] : $this->DEFAULT_REGEX) . ')';
                    }, $arrRouter ['regex'] );
                    $arrRouter ['regex'] = '/^\/' . $arrRouter ['regex'] . ((isset ( $arrRouter ['strict'] ) ? $arrRouter ['strict'] : $GLOBALS ['~@option'] ['url_router_strict']) ? '$' : '') . '/';
                    
                    // 匹配结果
                    if (preg_match ( $arrRouter ['regex'], $sPathinfo, $arrRes )) {
                        $booFindFouter = true;
                    }
                }
                
                // 分析结果
                if ($booFindFouter === true) {
                    $arrData = static::parseNodeUrl ( $arrRouter ['url'] );
                    
                    // 额外参数
                    if (is_array ( $arrRouter ['params'] ) && $arrRouter ['params']) {
                        $arrData = array_merge ( $arrData, $arrRouter ['params'] );
                    }
                    
                    // 变量解析
                    if (isset ( $arrRouter ['args'] )) {
                        array_shift ( $arrRes );
                        foreach ( $arrRouter ['args'] as $intArgsKey => $strArgs ) {
                            $arrData [$strArgs] = $arrRes [$intArgsKey];
                        }
                    }
                    break 2;
                }
            }
        }
        
        // 合并域名匹配数据
        $arrData = array_merge ( $this->arrDomainData, $arrData );
        
        return $arrData;
    }
    
    /**
     * 格式化正则
     *
     * @param string $sRegex            
     * @return string
     */
    private function formatRegex_($sRegex) {
        $sRegex = safe::escapeRegexCharacter ( $sRegex );
        
        // 还原变量特殊标记
        return str_replace ( [ 
                '\{',
                '\}' 
        ], [ 
                '{',
                '}' 
        ], $sRegex );
    }
    
    /**
     * 合并 in 参数
     *
     * @param array $in            
     * @param array $arrExtend            
     * @return array
     */
    private function mergeIn_(array $in, array $arrExtend) {
        // 合并特殊参数
        foreach ( [ 
                'params',
                'where',
                'domain_where' 
        ] as $strType ) {
            if (! empty ( $arrExtend [$strType] ) && is_array ( $arrExtend [$strType] )) {
                if (! isset ( $in [$strType] )) {
                    $in [$strType] = [ ];
                }
                $in [$strType] = $this->mergeWhere_ ( $in [$strType], $arrExtend [$strType] );
            }
        }
        
        // 合并额外参数
        foreach ( [ 
                'prefix',
                'domain',
                'prepend',
                'strict',
                'router' 
        ] as $strType ) {
            if (isset ( $arrExtend [$strType] )) {
                $in [$strType] = $arrExtend [$strType];
            }
        }
        
        return $in;
    }
    
    /**
     * 合并 where 正则参数
     *
     * @param array $arrWhere            
     * @param array $arrExtend            
     * @return array
     */
    private function mergeWhere_(array $arrWhere, array $arrExtend) {
        // 合并参数正则
        if (! empty ( $arrExtend ) && is_array ( $arrExtend )) {
            if (is_string ( key ( $arrExtend ) )) {
                $arrWhere = array_merge ( $arrWhere, $arrExtend );
            } else {
                $arrWhere [$arrExtend [0]] = $arrExtend [1];
            }
        }
        
        return $arrWhere;
    }
}
