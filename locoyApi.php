<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$moduleid = 21; //模块id

define('_ROOT', substr(__FILE__,0, -13));

require _ROOT.'/common.inc.php';
require _ROOT.'/module/article/common.inc.php';
require _ROOT.'/include/post.func.php';

$act = htmlspecialchars(trim($_GET['act']))? htmlspecialchars(trim($_GET['act'])): 'select';

require MD_ROOT.'/article.class.php';
$do = new article($moduleid);

ini_set('display_errors', 1);
error_reporting(E_ALL);

if($argv[1] == 'exportTag') {
    $result = $db->query("SELECT * FROM {$db->pre}article_21 WHERE status=3 ORDER BY addtime DESC");
    //$result = $db->query("SELECT * FROM {$db->pre}article_21 WHERE itemid in(6642,1369)");
    while($r = $db->fetch_array($result)) {
        if(!empty($r['tag'])) {
            if($tagids = $do->getTagId($r['tag'])) {
                $itemids_tmp = array();
                for($i=0; $i<count($tagids); $i++) {
                    $itemids_tmp[] = getTagRelateArticle($tagids[$i], $r['itemid']);
                }
                $itemids = $do->pointTagArticle($itemids_tmp);
                //var_dump($itemids);exit;
                if(!empty($itemids)) {
                    if(!$db->get_one("select * from {$db->pre}tags_relate_data where itemid={$r['itemid']}")) {
                        $db->query("insert into {$db->pre}tags_relate_data (itemid,relateids) VALUES ({$r['itemid']}, '{$itemids}')");    
                    }
                }
                //$do->updateTagRelateArticle($r['itemid'], $tagids);
                //$db->query("UPDATE {$db->pre}article_21 SET tagid='".implode(',', $tagids)."' WHERE itemid={$r['itemid']}");   
            }   
        } 
        echo "item {$r['itemid']}\n";  
    }
    echo "complete\n";
    exit;
}

if($argv[1] == 'filterTag') {
    $result = $db->query("SELECT itemid,tag FROM {$db->pre}article_21 ORDER BY addtime");
    while($r = $db->fetch_array($result)) {
        if(!empty($r['tag'])) {
            $r['tag'] = trim($r['tag'], ',');
            $db->query("UPDATE {$db->pre}article_21 SET tag='{$r['tag']}' WHERE itemid={$r['itemid']}");
        }   
    }
    exit;
}


 
if($act == 'select') {
    $catelist = category_select('catid','','',$moduleid);
    echo  $catelist;  
}else if($act == 'add') {
    
    $post = $_POST;
    
    $FD = cache_read('fields-'.substr($table, strlen($DT_PRE)).'.php');
    if($FD) require DT_ROOT.'/include/fields.func.php';
    isset($post_fields) or $post_fields = array();
    $CP = $MOD['cat_property'];
    if($CP) require DT_ROOT.'/include/property.func.php';
    isset($post_ppt) or $post_ppt = array();

    $data['catid'] = $post['catid']; //所属分类
    $data['title'] = $post['title']; //资讯标题
    $data['level'] = 0; //级别
    $data['style'] = ''; //样式
    $data['thumb'] = ''; //标题图片
    $data['linkurl'] = ''; //外部链接地址
    $data['content'] = $post['content']; //资讯内容
    $data['save_remotepic'] = 1; //下载远程图片 1-是
    $data['introduce_length'] = 120; //截取内容长度为简介
    $data['thumb_no'] = 1; //设置内容第几张图片为标题图
    $data['subtitle'] = ''; //分页标题
    $data['introduce'] = ''; //资讯简介
    $data['author'] = $post['author'];  //资讯作者
    $data['copyfrom'] = $post['copyfrom']; //资讯来源 
    $data['fromurl'] = ''; //来源链接 
    $data['tag'] = tagsReset(trim($post['tag'], ',')); //关键词(Tag)
    $data['voteid'] = ''; //插入投票
    
    if($data['status'] == '1') {
        $data['status'] = 3;     
    }else {
        $data['status'] = 2;    
    }
    $data['note'] = ''; //拒绝理由
    $data['addtime'] = date('Y-m-d H:i:s'); //添加时间
    $data['areaid'] = 0; //所在地区
    $data['hits'] = ''; //浏览次数
    $data['template'] = '';  //内容模板

    if($do->pass($data)) {
        if($FD) fields_check($post_fields);
        if($CP) property_check($post_ppt);
        $do->add($data);
        if($FD) fields_update($post_fields, $table, $do->itemid);
        if($CP) property_update($post_ppt, $moduleid, $post['catid'], $do->itemid);
        if($MOD['show_html'] && $data['status'] > 2) $do->tohtml($do->itemid); 
        echo '添加成功';   
    }else {
        echo '添加失败';
    }      
}


function tagsReset($tags = '')
{
    $arr = explode(',', $tags);
    if(!empty($arr)) {
        for($i=0; $i<count($arr); $i++) {
            if(!empty($arr[$i])) {
                $data[$arr[$i]] = $arr[$i];   
            }
        }
    }
    return isset($data)? implode(',', $data): '';
}



