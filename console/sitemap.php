<?php

//ini_set('display_errors', 1);
//error_reporting(E_ALL);

define('_ROOT', substr(__DIR__,0, -8));

require _ROOT.'/common.inc.php';


$sitemapDir = _ROOT.'/sitemap';

if(!file_exists($sitemapDir)) {
    mkdir($sitemapDir);
    chmod($sitemapDir, 0777);
}

$pagesize = 10000;
$result = $db->query("SELECT itemid,catid FROM {$db->pre}article_21 WHERE status=3");
while($r = $db->fetch_array($result)) {
	$data[$r['itemid']] = $r;
}

if($data) {
	buidXml($data);
}


function buidXml($data = array())
{
	global $sitemapDir,$pagesize;
	
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
    $xml .= "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
	
	if($data) {
		foreach($data as $val) {
			$xml .= "<url>\n";
			$xml .= "<loc>http://www.hxuew.com/news/{$val['itemid']}.html</loc>\n";
			$xml .= "<lastmod>".date('Y-m-d')."</lastmod>\n";
			$xml .= "<changefreq>hourly</changefreq>\n";
			$xml .= "<priority>0.8</priority>\n";
			$xml .= "</url>\n";
		}
		$xml .= "</urlset>\n";  
		$catname = getTreeCateName($val['catid']);
		//file_put_contents($sitemapDir."{$catname}_{$index}.xml", $xml);
		file_put_contents($sitemapDir."/news_{$index}.xml", $xml);
	}
}

function getTreeCateName($cid = 0)
{
	$cats = array(
		array('cid' => 39, 'name' => '雅思', 'enname' => 'yasi', 'child' => array(44,49,50,51,52,53,54)),
		array('cid' => 40, 'name' => '托福', 'enname' => 'tuofu', 'child' => array(45,57,58,59,60,61,62)),
		array('cid' => 41, 'name' => 'SAT', 'enname' => 'sat', 'child' => array(46,63,64,65,66,67,68,69)),
		array('cid' => 42, 'name' => 'GRE', 'enname' => 'gre', 'child' => array(47,70,71,72,73,74,75)),
		array('cid' => 43, 'name' => 'GMAT', 'enname' => 'gmat', 'child' => array(48,76,77,78,79)),
	);
	
	$name = '';
	foreach($cats as $val) {
		if($cid == $val['cid'] || in_array($cid, $val['child'])) {
			$name = $val['enname'];
			break;
		}
	}
	return $name;
}
