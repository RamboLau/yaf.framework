<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2015 panjun.liu <http://176code.com lpj163@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * yaf Bootstrap类, 以_init开头的方法，都会被yaf调用
 *
 * @see http://www.laruence.com/manual/ch06s02.html
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同 
 */
class Bootstrap extends Yaf_Bootstrap_Abstract
{
	public $is_ajax = false;

	/**
	 * 自动加载类
	 */
   public function _initLoader($dispatcher) {
      Yaf_Loader::getInstance()->registerLocalNameSpace(array("Database"));
   }

	/**
	 * 初始化配置
	 */
  public function _initConfig() {
		//把配置保存起来
		$this->_config = Yaf_Application::app()->getConfig();
		Yaf_Registry::set('config', $this->_config);
	}

	/**
	 * 初始化plugin(插件)
	 */
	public function _initPlugin(Yaf_Dispatcher $dispatcher) {
		//注册xhprof插件
		if (isset($this->_config->application->xhprof) && $this->_config->application->xhprof) {
			$XHProf = new XHProfPlugin();
			$dispatcher->registerPlugin($XHProf);
		}
	}

	/**
	 * 初始化route
	 */
	public function _initRoute(Yaf_Dispatcher $dispatcher) {
		//在这里注册自己的路由协议,默认使用简单路由
	}
	
	/**
	 * 初始化view
	 */
	public function _initView(Yaf_Dispatcher $dispatcher) {
		//在这里注册自己的view控制器，例如smarty,firekylin
	}

	/**
	 * 加载常用的函数库
	 */
	public function _initLibrary() {
		Yaf_Loader::import(APP_PATH . '/application/library/Functions.php');
	}

  /**
   * 初始化XHProf
   */
  /*public function _initXHProf(Yaf_Dispatcher $dispatcher) {
  	if (!extension_loaded('xhprof')) {
  		throw new Exception("Your web server does not appear to support xhprof extension.", 1);
  	}
  	else {
	  	$enable_xhprof = isset($this->_config->application->xhprof) ? $this->_config->application->xhprof : 0;
	    if ($enable_xhprof) {
	      $xhprof_config = $this->_config->application->xhprof->toArray();
	      if ($xhprof_config && isset($xhprof_config['open']) && $xhprof_config['open']) {
	          $default_flags = XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY;
	          $ignore_functions = isset($xhprof_config['ignored_functions']) && is_array($xhprof_config['ignored_functions']) ? $xhprof_config['ignored_functions'] : array();
	          if (isset($xhprof_config['flags'])) {
	             xhprof_enable($xhprof_config['flags'], $ignore_functions);
	          } 
	          else {
	             xhprof_enable($default_flags, $ignore_functions);
	          }
	      }
	    }
  	}
  }*/

  /**
   * 加载数据库
   */
	public function _initDatabase() {
		$type     = $this->_config->database->type;
		$host     = $this->_config->database->host;
		$port     = $this->_config->database->port;
		$username = $this->_config->database->username;
		$password = $this->_config->database->password;
		$databaseName = $this->_config->database->databaseName;
		$dsn = $type . ':' . 'dbname=' . $databaseName . ';host=' . $host;
		Yaf_Registry::set('db', new Database($dsn, $username, $password));
	}


  /**
   * 初始化错误处理
   */
  public function _initErrors() {
    //报错是否开启
    $enable_debug = isset($this->_config->application->debug) ? $this->_config->application->debug : 0;
    if ($enable_debug) {
    	error_reporting(-1);
      ini_set('display_errors', 'On');
    } 
    else {
      error_reporting(0);
      ini_set('display_errors', 'Off');
    }
    // set_error_handler(['Error', 'errorAction']);
  }

}