<?php
error_reporting(E_WARNING);
ini_set('display_errors','On');
require_once("../system/starter.php");
ini_set("include_path", $include_path );
require_once(SITEBILL_DOCUMENT_ROOT.'/third/smarty/Smarty.class.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill_krascap.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/language/russian.php');
$smarty = new Smarty;
$sitebill = new SiteBill();
$sitebill->writeLog('MAIN! '.uniqid().' ~ '.microtime());
$smarty->template_dir = SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$sitebill->getConfigValue('theme');
$smarty->cache_dir    = SITEBILL_DOCUMENT_ROOT.'/cache/smarty';
$smarty->compile_dir  = SITEBILL_DOCUMENT_ROOT.'/cache/compile';
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/currency/admin/admin.php');
$currency_admin = new currency_admin();
$currency_admin->recalcCoursesByCron();
exit();