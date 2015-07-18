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

class TestModel
{

    public function __construct()
    {
        $this->db = Yaf_Registry::get('db');
    }



    /**
     * 获取博客列表 -1 删除 0 草稿 1 正常 2 置顶
     */
    public function bloglist()
    {
    	$sql = "SELECT ddm.id, ddm.name, ddm.uid, ddm.sub_uid, ddm.username, ddm.pid, ddm.gid, ddm.type, ddm.authid, ddm.mark, ddm.is_auth, ddm.recordnum, ddm.status, ddm.note, ddm.createtime, ddm.updatetime, ddm.endtime FROM dns_domain ddm";
        //var_dump($this->db);
    	//$data = $this->db->fetchAll('dns_domain', array('status' => 0), array('id', 'name'));

$data = $this->db->fetchAll($sql);


        return $data;
    }

}
