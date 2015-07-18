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

class RefreshController extends Yaf_Controller_Abstract 
{
  public $IS_POST;

  /**
   * Controller的init方法会被自动首先调用
   */	
  public function init() {
  	// 如果是ajax请求，关闭html输出
  	// if ($this->getRequest()->isXmlHttpRequest()) {

  	/**
  	 * 是否返回Response对象, 如果启用, 则Response对象在分发完成以后不会自动输出给请求端, 而是交给程序员自己控制输出.
  	 * @see http://www.laruence.com/manual/yaf.class.dispatcher.returnResponse.html
  	 */
    Yaf_Dispatcher::getInstance()->returnResponse(TRUE);

    /**
     * 关闭自动Render. 默认是开启的, 在动作执行完成以后, Yaf会自动render以动作名命名的视图模板文件.
     * @see http://www.laruence.com/manual/yaf.class.dispatcher.disableView.html
     */
    Yaf_Dispatcher::getInstance()->disableView();
    $this->IS_POST = is_post();
    $this->testmodel = new TestModel();
  }


  public function processAction() {
    //if($this->IS_POST) {
      $data = $this->testmodel->bloglist();
      echo json_encode($data);
      return TRUE;
    //}
  }

  
}