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

class XHProfPlugin extends Yaf_Plugin_Abstract 
{
	public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
		//Yaf_Registry::set('xhprof_start', microtime(true));
	}

	public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
	}

	public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

	}

	public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

	}

	public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

	}

	/**
	 * 在yaf路由分发之后响应正文之前，保存XHProf的性能统计数据
	 */
	public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
		$config = Yaf_Application::app()->getConfig();
		$xhprof_config = isset($config->application['xhprof']) ? $config->application['xhprof'] : array();
    if (!empty($xhprof_config)) {
      if (extension_loaded('xhprof') && isset($xhprof_config['open']) && $xhprof_config['open']) {
        $namespace = isset($xhprof_config['namespace']) ? $xhprof_config['namespace'] : current_path();
        //$namespace = isset(HttpServer::$server['request_uri']) ? HttpServer::$server['request_uri'] : ($namespace ? $namespace : 'front');
        //$namespace = str_replace('/', '_', $namespace);
        $xhprof_data = xhprof_disable();
        $xhprof_runs = new XHProfRunsFile();
        //$run_id = ucfirst($request->module) . ucfirst($request->controller) . ucfirst($request->action) . '-' . str_replace('.', '', (string)microtime(true));
        $xhprof_runs->save_run($xhprof_data, $namespace);
      }
    }
	}

}