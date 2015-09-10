<?php

namespace App\Doc\POST;

/**
 * 提交内容
 */
class Article extends \App\Doc\CheckUser {

    /**
     * 发表日志
     */
    public function action() {
        $data['doc_title'] = $this->isP('title', '请填写标题');
        $content = $this->isP('content', '请填写内容');
        $data['doc_tree_id'] = $this->isP('tree', '请选择类型');
        $data['user_id'] = $_SESSION['user']['user_id'];
        $data['doc_updatetime'] = $data['doc_createtime'] = time();
        $data['doc_delete'] = '0';

        $this->db()->transaction();
        $baseInsert = $this->db('doc')->insert($data);
        if ($baseInsert === false) {
            $this->db()->rollBack();
            $this->error('创建文档出错');
        }

        $addJoin = $this->db('doc_join')->insert(array('doc_id' => $baseInsert, 'user_id' => $data['user_id'], 'doc_join_time' => $data['doc_createtime']));
        if ($addJoin === false) {
            $this->db()->rollBack();
            $this->error('添加参与者失败');
        }

        $addContent = $this->db('doc_content')->insert(array('doc_id' => $baseInsert, 'user_id' => $data['user_id'], 'doc_content' => $content, 'doc_content_createtime' => $data['doc_createtime']));
        if ($addContent === false) {
            $this->db()->rollBack();
            $this->error('添加时间文档内容层数出错');
        }

        $this->db()->commit();

        $this->success('发表新文档成功!', "/d/{$baseInsert}");
    }

    /**
     * 添加新内容
     */
    public function addContent() {
        $id = $this->isG('id', '丢失日志');
        $content = $this->isP('content', '请填写内容');

        $checkJoin = $this->db('doc AS d')->join("{$this->prefix}doc_join AS dj ON dj.doc_id = d.doc_id")->where("d.user_id = :user_id AND d.doc_id = :doc_id AND d.doc_delete = '0'")->find(array('user_id' => $_SESSION['user']['user_id'], 'doc_id' => $id));

        if (empty($checkJoin) || $checkJoin['doc_type'] == '4') {
            $this->error('您不是本文档的参与者或者文档不存在/被删除');
        }

        $this->db()->transaction();
        $time = time();
        $updateTime = $this->db()->query("UPDATE {$this->prefix}doc SET doc_updatetime = '{$time}' WHERE doc_id = :doc_id ", array('doc_id' => $id));
        if ($updateTime === FALSE) {
            $this->db()->rollBack();
            $this->error('更新时间出错');
        }

        $addContent = $this->db('doc_content')->insert(array('doc_id' => $id, 'user_id' => $_SESSION['user']['user_id'], 'doc_content' => $content, 'doc_content_createtime' => $time));
        if ($addContent === FALSE) {
            $this->db()->rollBack();
            $this->error('添加内容时出错');
        }

        $this->db()->commit();
        $this->success('添加内容成功!', "/d/{$id}");
    }

    /**
     * 更新内容
     */
    public function updateContent() {
        $id = $this->isG('id', '请提交您要编辑的内容');
        $content = $this->isP('content', '请填写内容');
        $checkUser = $this->db('doc_content')->where('doc_content_id = :doc_content_id AND user_id = :user_id ')->find(array('doc_content_id' => $id, 'user_id' => $_SESSION['user']['user_id']));
        if (empty($checkUser)) {
            $this->error('没有找到您要更新的内容');
        }

        $update = $this->db('doc_content')->where('doc_content_id = :doc_content_id AND user_id = :user_id ')->update(array('doc_content' => $content, 'doc_content_updatetime' => time(), 'noset' => array('doc_content_id' => $id, 'user_id' => $_SESSION['user']['user_id'])));
        if ($update === false) {
            $this->error('更新出错');
        }

        $this->success('更新成功');
    }

}