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

define ('DS', DIRECTORY_SEPARATOR);
define ('ROOT_PATH', realpath(dirname(__FILE__) . '/../'));
define ('CONF_PATH', ROOT_PATH . DS . 'conf' . DS);

class HttpServer 
{
	public static $instance;

	public $http;
	public static $get;
	public static $post;
	public static $header;
	public static $server;
	private $application;

	public function __construct() {
		$http = new swoole_http_server("0.0.0.0", 8080);
		$http->set(
			array(
				'worker_num'            => 16,         //worker进程数 
        'max_conn'              => 10000,           //最大允许的连接数， 此参数用来设置Server最大允许维持多少个tcp连接。超过此数量后，新进入的连接将被拒绝。
        'max_request'           => 5000,        //此参数表示worker进程在处理完n次请求后结束运行。manager会重新创建一个worker进程。此选项用来防止worker进程内存溢出。
        #'ipc_mode'              => 1,           // 1，默认项，使用Unix Socket作为进程间通信,2，使用系统消息队列作为进程通信方式
        #'task_worker_num'       => 4,    //task_worker进程数 
        #'task_ipc_mode'         => 1,      //1, 使用unix socket通信，2, 使用消息队列通信，3, 使用消息队列通信，并设置为争抢模式
        #'task_max_request'      => 8,   //设置task进程的最大任务数
        'dispatch_mode'         => 1,      //1平均分配，2按FD取摸固定分配，3抢占式分配，默认为取摸(dispatch=2)
        'daemonize'             => 1,          //守护进程化
        #'backlog'               => 10,            //最多同时有多少个等待accept的连接
        #'open_tcp_keepalive'    => 1, //启用tcp keepalive
        #'tcp_defer_accept'      => 1,   //当一个TCP连接有数据发送时才触发accept
        #'open_tcp_nodelay'      => 1,   //开启后TCP连接发送数据时会无关闭Nagle合并算法，立即发往客户端连接。在某些场景下，如http服务器，可以提升响应速度。 
        'log_file'              => '/var/log/swoole.log' //日志文件路径
        //'task_tmpdir'         => APP_PATH . '/data/task',
        //'heartbeat_check_interval' => 5, //每隔多少秒检测一次，单位秒，Swoole会轮询所有TCP连接，将超过心跳时间的连接关闭掉
        //'heartbeat_idle_time' => 5, //TCP连接的最大闲置时间，单位s , 如果某fd最后一次发包距离现在的时间超过heartbeat_idle_time会把这个连接关闭。
			)
		);

		$http->on('WorkerStart' , array($this, 'onWorkerStart'));

		$http->on('request', function($request, $response) {
			if (isset($request->server)) {
				HttpServer::$server = $request->server;
			}
			else{
				HttpServer::$server = [];
			}
			
			if (isset($request->header)) {
				HttpServer::$header = $request->header;
			}
			else {
				HttpServer::$header = [];
			}
			
			if (isset($request->get)) {
				HttpServer::$get = $request->get;
			}
			else {
				HttpServer::$get = [];
			}
			
			if (isset($request->post)) {
				HttpServer::$post = $request->post;
			}
			else {
				HttpServer::$post = [];
			}

			// TODO handle img

			ob_start();
			try {
				//var_dump(HttpServer::$server['request_uri']);
				$_SERVER['REQUEST_URI'] = HttpServer::$server['request_uri'];
				//$_SERVER['REMOTE_ADDR'] = HttpServer::$server['request_uri'];
				$yaf_request = new Yaf_Request_Http(HttpServer::$server['request_uri']);
			  $this->application->getDispatcher()->dispatch($yaf_request);
			  // unset(Yaf_Application::app());
			} 
			catch (\Exception $e) {
				var_dump($e);
			}
			
	    $result = ob_get_contents();

	  	ob_end_clean();

	  	// add Header
	  	$response->header('Content-Type', 'no-cache, must-revalidate');
	  	$response->header('Content-Type', 'application/json');
	  	//$response->header('Content-Encoding', 'gzip');
	  	$response->header('Server', 'verycloud');
	  	// add cookies
	  	
	  	// set status
	  	$response->end($result);
		});

    $http->on('WorkerStop', array($this, 'onWorkerStop'));

		$http->start();
	}

	public function onWorkerStart($serv,$worker_id) {
		define('APP_PATH', dirname(__DIR__));
		$this->application = new Yaf_Application(APP_PATH . "/conf/app.ini");
		ob_start();
		$this->application->bootstrap()->run();
		ob_end_clean();
	}

	public function onWorkerStop($serv,$worker_id) {
		if(extension_loaded('opcache')) {
			// zend_opcache的opcache清理函数
			opcache_reset();
		}
	}

	public static function getInstance() {
		if (!self::$instance) {
      self::$instance = new HttpServer;
    }
    return self::$instance;
	}
}

HttpServer::getInstance();