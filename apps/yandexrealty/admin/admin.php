<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';
/**
 * Yandex.Realty generator backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class yandexrealty_admin extends Object_Manager {
	
	public static $EXP_T_NOTYPE=0;
	public static $EXP_T_ROOM=2;
	public static $EXP_T_FLAT=1;
	public static $EXP_T_HOUSE=3;
	public static $EXP_T_LOT=4;
	public static $EXP_T_FLAT_2=5;
	public static $EXP_T_ROOM_2=6;
	public static $EXP_T_HOUSE_2=7;
	public static $EXP_T_COTTAGE=8;
	public static $EXP_T_TOWNHOUSE=10;
	public static $EXP_T_TOWNHOUSE_2=9;
	public static $EXP_T_HOUSEPART=11;
	public static $EXP_T_HOUSEWITHLOT_2=12;
	public static $EXP_T_HOUSEWITHLOT=13;
	public static $EXP_T_COUNTRYHOUSE=14;
	public static $EXP_T_LOT_2=15;
	public static $EXP_T_LOT_3=16;
	
	public static $EXP_TY_RESIDENTIAL=1;
	public static $EXP_TY_COMMERCIAL=2;
	public static $EXP_TY_NONRESIDENTIAL=3;
	
	
	
	public static $EXP_T_OFFICE=21;
	public static $EXP_T_RETAIL=22;
	public static $EXP_T_WAREHOUSE=23;
	public static $EXP_T_FREE=24;
	public static $EXP_T_LANDCOMM=25;
	public static $EXP_T_MANUF=26;
	public static $EXP_T_AREP=27;
	public static $EXP_T_BUSSINESS=28;
	public static $EXP_T_LEGAL=29;
	public static $EXP_T_CATERING=30;
	public static $EXP_T_HOTEL=31;
	
	protected $export_file='export.yandexrealty.xml';
	protected $export_file_storage=SITEBILL_DOCUMENT_ROOT;
	protected $critical_term=30;
	protected $min_normal_term=1; // 
	protected $max_normal_term=5;
	protected $currency='RUR';
	protected $rent_period='месяц';
	protected $topicsOperations=array();
	protected $export_type='';
	
	protected $enabled_topics=array();
	protected $export_mode='YANDEX';
	
	protected $op_types;
	protected $realty_types;
	protected $realty_categories;
	protected $op_type_field;
	//private $export_to_file=1;
	
	protected $users_cache=array();
	protected $group_assoc=array();
	protected $contacts_mode=array();
	protected $contacts_export_mode=0;
    /**
     * Constructor
     */
    function __construct( $realty_type = false ) {
    	
    	$this->op_types=array('0'=>'Игнорировать','1'=>'продажа','2'=>'аренда');
    	$this->realty_types=array(
    		'0'=>'Игнорировать',
    		yandexrealty_admin::$EXP_TY_RESIDENTIAL=>'жилая',
    		yandexrealty_admin::$EXP_TY_COMMERCIAL=>'коммерческая',
    		yandexrealty_admin::$EXP_TY_NONRESIDENTIAL=>'нежилая');
    	$this->realty_categories=array(
    		yandexrealty_admin::$EXP_T_NOTYPE=>'',
	    	yandexrealty_admin::$EXP_T_FLAT=>'квартира',
    		yandexrealty_admin::$EXP_T_ROOM=>'комната',
	    	yandexrealty_admin::$EXP_T_HOUSE=>'дом',
	    	yandexrealty_admin::$EXP_T_LOT=>'участок',
	    	yandexrealty_admin::$EXP_T_FLAT_2=>'flat',
	    	yandexrealty_admin::$EXP_T_ROOM_2=>'room',
	    	yandexrealty_admin::$EXP_T_HOUSE_2=>'house',
	    	yandexrealty_admin::$EXP_T_COTTAGE=>'cottage',
	    	yandexrealty_admin::$EXP_T_TOWNHOUSE_2=>'townhouse',
	    	yandexrealty_admin::$EXP_T_TOWNHOUSE=>'таунхаус',
	    	yandexrealty_admin::$EXP_T_HOUSEPART=>'часть дома',
	    	yandexrealty_admin::$EXP_T_HOUSEWITHLOT_2=>'house with lot',
	    	yandexrealty_admin::$EXP_T_HOUSEWITHLOT=>'дом с участком',
	    	yandexrealty_admin::$EXP_T_COUNTRYHOUSE=>'дача',
	    	yandexrealty_admin::$EXP_T_LOT_2=>'lot',
	    	yandexrealty_admin::$EXP_T_LOT_3=>'земельный участок',
    			
    			yandexrealty_admin::$EXP_T_OFFICE=>'офисные помещения',
    			yandexrealty_admin::$EXP_T_RETAIL=>'торговые помещения',
    			yandexrealty_admin::$EXP_T_WAREHOUSE=>'склад',
    			yandexrealty_admin::$EXP_T_FREE=>'помещения свободного назначения',
    			yandexrealty_admin::$EXP_T_LANDCOMM=>'земли коммерческого назначения',
    			yandexrealty_admin::$EXP_T_MANUF=>'производственное помещение',
    			yandexrealty_admin::$EXP_T_AREP=>'автосервис',
    			yandexrealty_admin::$EXP_T_BUSSINESS=>'готовый бизнес',
    			yandexrealty_admin::$EXP_T_LEGAL=>'юридический адрес',
    			yandexrealty_admin::$EXP_T_CATERING=>'общепит',
    			yandexrealty_admin::$EXP_T_HOTEL=>'гостиница',
    			
    		
    	);
    	
    	$this->commercial_names=array(
    			21=>'office', 
    			22=>'retail', 
    			23=>'warehouse', 
    			24=>'free purpose', 
    			25=>'land', 
    			26=>'manufacturing', 
    			27=>'auto repair', 
    			28=>'business', 
    			29=>'legal address', 
    			30=>'public catering', 
    			31=>'hotel'
    	);
		if($this->getRequestValue('foretown')){
			$this->export_mode='ETOWN';
		}
    	$this->action='yandexrealty';
        $this->SiteBill();
        Multilanguage::appendAppDictionary('yandexrealty');
        $this->site_url='http://'.$_SERVER['SERVER_NAME'].(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL.'/' : '/');
        //$this->filename=date('YmdHis',time()).'.'.$this->fileextension;
		$this->file_header='<?xml version="1.0" encoding="utf-8" ?>'."\n";
		//$this->enabled_topics=$this->getEnabledTopics();
		$this->enabled_topics=array();
		$this->topicsOperations=array();
		//$this->topicsOperations=$this->getTopicsOperations($this->enabled_topics);
				
		
		$this->file_gen_date='<generation-date>'.$this->formdate().'</generation-date>'."\n";
		
		$this->file_start='<realty-feed xmlns="http://webmaster.yandex.ru/schemas/feed/realty/2010-06">'."\n";
		//$this->file_start.='<site>'.$this->site_url.'</site>'."\n";
		$this->file_end='</realty-feed>';
		
		require_once (SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
		$config_admin = new config_admin();
		 
		if ( !$config_admin->check_config_item('apps.yandexrealty.sell') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.sell','','Поле:Значение отвечающие за признак продажи');
		}
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.rent') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.rent','','Поле:Значение отвечающие за признак аренды');
		}
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.tofile') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.tofile','0','Выгружать в файл');
		}
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.filetime') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.filetime','86400','Время жизни файла кеша (в секундах)');
		}
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.images_field') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.images_field','','Системное имя поля, содержащего изображения');
		}
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.days_interval') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.days_interval','180','Количество дней за которое будут выбраны объявления для выгрузки');
		}
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.alias') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.alias','yandexrealty','Алиас приложения');
		}
		
		/*if ( !$config_admin->check_config_item('apps.yandexrealty.commercial_not_export') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.commercial_not_export','0','Не выгружать коммерческую');
		}*/
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.country_from') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.country_from','','Системное имя поля с именем страны');
		}
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.country_global') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.country_global','Россия','Единое название страны');
		}
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.region_from') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.region_from','','Системное имя поля с именем региона');
		}
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.region_global') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.region_global','','Единое название региона');
		}
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.street_from') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.street_from','','Системное имя поля с именем улицы');
		}
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.city_from') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.city_from','','Системное имя поля с именем города');
		}
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.city_global') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.city_global','','Единое название города');
		}
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.per_page') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.per_page','100','Количество выгружаемых записей при постраничной выгрузке');
		}
		/*
		if ( !$config_admin->check_config_item('apps.yandexrealty.nonliving_not_export') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.nonliving_not_export','0','Не выгружать нежилую');
		}
		*//*
		if ( !$config_admin->check_config_item('apps.yandexrealty.nonassociated_not_export') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.nonassociated_not_export','0','Не выгружать неассоциированные');
		}
		*/
		if ( !$config_admin->check_config_item('apps.yandexrealty.nowatermark_export') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.nowatermark_export','0','Выгружать фотографии без водяного знака');
		}
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.disable_standart_entrypoint') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.disable_standart_entrypoint','0','Отключить стандартную точку входа');
		}
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.standart_entry_alias') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.standart_entry_alias','','Алиас стандартной выдачи');
		}
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.filtering_checkbox_name') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.filtering_checkbox_name','','Системное имя поля типа checkbox фильтрующего записи для выгрузки');
		}
		/*if ( !$config_admin->check_config_item('apps.yandexrealty.target_export_pass') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.target_export_pass','','Пароль для целевой выгрузки');
		}*/
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.contacts_export_mode') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.contacts_export_mode',0,'Режим тонкой настройки контактов и групп',1);
		}
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.contacts_assoc_str') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.contacts_assoc_str','','Строка ассоциирования выгрузки контактов');
		}
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.groups_assoc_str') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.groups_assoc_str','','Строка ассоциирования назначения группы');
		}

		if ( !$config_admin->check_config_item('apps.yandexrealty.complex_enable') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.complex_enable','0','Выгружать информацию о ЖК из приложения Жилые комплексы (по-умолчанию все записи)', 1);
		}
		if ( !$config_admin->check_config_item('apps.yandexrealty.complex_yandexrealty_export') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.complex_yandexrealty_export','0','Выгружать только выбранные ЖК (только при активной опции apps.yandexrealty.complex_enable). Поле должно называться complex.yandexrealty_export', 1);
		}
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.lot_area') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.lot_area','','Системное имя поля с площадью участка');
		}
		if ( !$config_admin->check_config_item('apps.yandexrealty.lot_area_dim') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.lot_area_dim','','Размерность значения системного поля с площадью участка (sqm|ha|acr)');
		}
		
		if ( !$config_admin->check_config_item('apps.yandexrealty.add_lot_area') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.add_lot_area','','Системное имя поля с площадью дополнительного участка');
		}
		if ( !$config_admin->check_config_item('apps.yandexrealty.add_lot_area_dim') ) {
			$config_admin->addParamToConfig('apps.yandexrealty.add_lot_area_dim','','Размерность значения системного поля с площадью дополнительного участка (sqm|ha|acr)');
		}
		
		
    }
    
    function getInfo () {
    	/*$rs = "<p>URL для выгрузки: <a href=\"".$this->site_url."yandexrealty/\" target=\"_blank\">".$this->site_url."yandexrealty/</a></p>
<p>Выгрузка Yandex.Realty – необходима для того, чтобы вы могли выгружать свои объявления на сайт Яндекс.Недвижимость: <a href=\"http://realty.yandex.ru/\" target=\"_blank\">http://realty.yandex.ru/</a></p>
<p>Также вы можете выгружать объявления на сайт <a href=\"http://www.etown.ru/\" target=\"_blank\">«Недвижимость всех городов»</a>.<br> Преимущества выгрузки на этот сайта заключаются в том, что ваши объявления отображаются на сайте с полной информацией, но вместо контактов выводится ссылка на ваш сайт.<br> Для этого необходимо зарегистрироваться на сайте <a href=\"http://www.etown.ru/\" target=\"_blank\">«Недвижимость всех городов»</a> и в личном кабинете добавить адрес XML-файла с данными с вашего сайта, он находится тут: <a href=\"".$this->site_url."yandexrealty/\" target=\"_blank\">".$this->site_url."yandexrealty/</a></p> 
    ";*/
    	$rs=sprintf(Multilanguage::_('INFO','yandexrealty'),$this->site_url."yandexrealty/",$this->site_url."yandexrealty/",$this->site_url."yandexrealty/",$this->site_url."yandexrealty/");
    	return $rs;
    }
    
	protected function _assoc_table_showAction(){
    	$rs='';
    	$rs.=$this->showAssocTable();
    	return $rs;
    }
    
    protected function _assoc_table_show_saveAction(){
    	$rs='';
    	$this->saveChanges($_POST['data']);
    	$rs.=$this->_assoc_table_showAction();
    	return $rs;
    }
    
    public function _update_modelAction () {
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php') ){
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
    		$form_data=$this->get_yandex_model();
    		
   			require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';
   			require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php';
   			$TA=new table_admin();
   			$TA->create_table_and_columns($form_data, 'data');
    	}
    	$rs = 'Модель обновлена успешно';
    	return $rs;
    }
    
    protected function _make_tableAction(){
    	$rs='';
    	$this->x();
    	return $rs;
    }
    
    protected function _create_tableAction(){
    	$rs='';
    	if($this->createAssocTable()){
			$rs.='Таблица ассоциаций создана';
		}else{
			$rs.='Таблица ассоциаций не создана. Возможно она уже существует.';
		}
    	return $rs;
    }
    
    protected function _exportAction(){
    	$rs='';
    	if(file_exists($this->export_file_storage.'/'.$this->export_file)){
    		unlink($this->export_file_storage.'/'.$this->export_file);
    	}
    	if(1==$this->getConfigValue('apps.yandexrealty.tofile')){
    		$this->export();
    	}
    	return $rs;
    }
    /*
    protected function _test_exportAction(){
    	$rs='';
    	$this->collectData2();
    	return $rs;
    }
    */
    protected function _defaultAction(){
    	//$rs = $this->getTopMenu();
    	$rs .= $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/yandexrealty/admin/template/sponsors.tpl');
    	//$rs.=parent::_defaultAction();
    	return $rs; 
    }
    
   /* function main(){
    	
    	$rs=$this->getTopMenu();
    	
    	switch($this->getRequestValue('do')){
			
    		case 'assoc_table_show' : {
    			$rs.=$this->showAssocTable();
    			break;
    		}
    		case 'assoc_table_show_save' : {
    			$this->saveChanges($_POST['data']);
    			$rs.=$this->showAssocTable();
    			break;
    		}
    		case 'make_table' : {
    			$this->x();
    			break;
    		}
			case 'create_table' : {
    			if($this->createAssocTable()){
					$rs.='Таблица ассоциаций создана';
				}else{
					$rs.='Таблица ассоциаций не создана. Возможно она уже существует.';
				}
    			break;
    		}
			
    		case 'export' : {
    			if(file_exists($this->export_file_storage.'/'.$this->export_file)){
    				unlink($this->export_file_storage.'/'.$this->export_file);
    			}
    			if(1==$this->getConfigValue('apps.yandexrealty.tofile')){
    				$this->export();
    			}
    			break;
    		}
    	}
    	return $rs;
    }*/
    
    private function saveChanges($data){
    	if(!empty($data)){
    		$DBC=DBC::getInstance();
    		
    		foreach($data as $k=>$v){
    			if($v['delete']=='on'){
    				$query='DELETE FROM '.DB_PREFIX.'_yandexrealty_assoc WHERE topic_id='.$k;
    			}else{
    				$query='UPDATE '.DB_PREFIX.'_yandexrealty_assoc SET realty_type='.$v['realty_type'].', operation_type='.$v['operation_type'].', realty_category='.$v['realty_category'].' WHERE topic_id='.$k;
    			}
    			$stmt=$DBC->query($query, array(), $row, $success);
    			if ( !$success ) {
    				//echo 'ERROR ON SAVING<br>';
    			}
    		}
    	}
    }
    
    function showAssocTable(){
    	$DBC=DBC::getInstance();
    	$names=$this->getCategoriesNameArray();
    	
    	$ret='<table class="table">';
    	$ret.='<form method="post" action="'.SITEBILL_MAIN_URL.'/admin/index.php">';
    	$ret.='<thead><tr><th>Раздел</th><th>Тип недвижимости</th><th>Тип операции</th><th>Категория</th><th>Удалить</th></tr><thead>';
    	$query='SELECT * FROM '.DB_PREFIX.'_yandexrealty_assoc';
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			if ( $names[$ar['topic_id']] != '' ) {
    				$ret.='<tr><td>'.$names[$ar['topic_id']].'</td>';
    				$ret.='<td>'.$this->getRealtyTypeSelectbox($ar['realty_type'], $ar['topic_id']).'</td>';
    				$ret.='<td>'.$this->getOperationTypeSelectbox($ar['operation_type'], $ar['topic_id']).'</td>';
    				$ret.='<td>'.$this->getRealtyCategorySelectbox($ar['realty_category'], $ar['topic_id']).'</td>';
    				$ret.='<td><input type="checkbox" name="data['.$ar['topic_id'].'][delete]" /></td></tr>';
    			}
    		}
    	}
    	
    	$ret.='<input type="hidden" name="action" value="'.$this->action.'">';
    	$ret.='<input type="hidden" name="do" value="assoc_table_show_save">';
    	$ret.='<tr><td><input type="submit" class="btn btn-primary" name="submit" value="Сохранить"></td></tr>';
    	$ret.='</form>';
    	$ret.='</table>';
    	return $ret;
    }
    
    function x(){
    	$names=$this->getCategoriesNameArray();
    	$query='SELECT id FROM '.DB_PREFIX.'_topic';
    	$DBC=DBC::getInstance();
    	$stmt=$DBC->query($query);
    	$data=array();
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$data[]=$ar;
    		}
    	}
    	if(!empty($data)){
    		foreach($data as $d){
    			$query='INSERT IGNORE INTO '.DB_PREFIX.'_yandexrealty_assoc (topic_id, topic_name) VALUES ('.$d['id'].',\''.$names[$d['id']].'\')';
    			$stmt=$DBC->query($query);
    		}
    	}
    }
    
    /**
     * Unlick old file
     * @param void
     * @return boolean (true - if file delete success and false - if file not deleted)
     */
    protected function remove_old_file () {
    	if(1==$this->getConfigValue('apps.yandexrealty.tofile') && file_exists($this->export_file_storage.'/'.$this->export_file)){
    		if ( (time() - filemtime( $this->export_file_storage.'/'.$this->export_file ) ) > $this->getConfigValue('apps.yandexrealty.filetime') ) {
    			return unlink($this->export_file_storage.'/'.$this->export_file);
    		}
    	}
    	return false;
    }
    
    private function codify($string){
    	$string = json_encode($string);
    	//echo $string.'<br>';
    	//echo $string.'<br>';
    	//$string=str_replace(array('\u0000', '\u0001', '\u0002', '\u0003', '\u0004', '\u0005', '\u0006', '\u0007', '\u0008', '\u0009', '\u0010', '\u0011', '\u0012', '\u0013', '\u0004', '\u0000', '\u0001', '\u0002', '\u0003', '\u0004'), $replace, $subject)
    	$string = preg_replace('/(\\\u00[0-1][0-9|A-F|a-f])/', '', $string);
    	//$string = preg_replace('/(\\\u00[1|2][0-9])/', '', $string);
    	//$string = preg_replace('/(\\\u00[3][0-1])/', '', $string);
    	//echo $string.'<br>';
    	$string = preg_replace('/\\\u([0-9a-f]{4})/', '&#x$1;', $string );
    	return json_decode($string);
    	$rs='';
    	foreach($string as $s){
    		preg_match('/\\\u([0-9a-z]{4})/', $html, $matches);
    		if((int)$matches[1]>31){
    			$rs.='&#x'.$matches[1].';';
    		}
    	}
    	return $rs;
    	//$string = preg_replace('/\\\u([0-9a-z]{4})/', '&#x$1;', $string );
    	return json_decode($string);
    }
   
    public function export(){
    	
    	if(isset($_GET['page'])){
    		$page=(int)$_GET['page'];
    	}
    	
    	$this->setExportType();
    	$this->remove_old_file();
    		 
    	if(1==$this->getConfigValue('apps.yandexrealty.tofile') && file_exists($this->export_file_storage.'/'.$this->export_file)){
    		return file_get_contents($this->export_file_storage.'/'.$this->export_file);
    	}
    	
    	require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php';
		$Structure=new Structure_Manager();
		
		
		$category_structure = $Structure->loadCategoryStructure();
		
		$x=$Structure->createCatalogChains();
		$catalogChains=$x['txt'];
		$rs='';
    	$data=$this->collectData();
		if(empty($data)){
    		return Multilanguage::_('EXPORT_FAILED','yandexrealty');
    	}
    	
    	$limit_time_arenda = time()-604800;
    	$count=0;
    	$associations=$this->loadAssociations();
    	
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data_shared = $data_model->get_kvartira_model(false, true);
    	$form_data_shared=$form_data_shared['data'];
    	
    	
    	$image_field=trim($this->getConfigValue('apps.yandexrealty.images_field'));
    	
    	$uploadsField=false;
    	$hasUploadify=false;
    	
    	if($image_field!='' && isset($form_data_shared[$image_field]) && in_array($form_data_shared[$image_field]['type'], array('uploads', 'uploadify_image'))){
    		if($form_data_shared[$image_field]['type']=='uploadify_image'){
    			$hasUploadify=true;
    		}else{
    			$uploadsField=$image_field;
    		}
    	}else{
    		foreach($form_data_shared as $model_item){
    			if($model_item['type']=='uploadify_image'){
    				$hasUploadify=true;
    				$uploadsField=false;
    				break;
    			}elseif($uploadsField===false && $model_item['type']=='uploads'){
    				$uploadsField=$model_item['name'];
    			}
    		}
    	}
    	
    	
    	
    	
    	/*
    	 * 0 Standart mode
    	* 1 Group based
    	* 2 1st fro
    	* 2
    	* Контакты
    	* 1 - все из дата
    	* 2 - все из профиля
    	* 3 - из дата, но если не хватает, то из профиля
    	* 4 - из профиля, но если не хватает, то из дата
    	*
    	*
    	* группа 1 - 1 (все из дата)
    	* группа 2 - 1 (все из дата)
    	* группа 3 - 2 (все из профиля)
    	*
    	* а) из дата
    	* б) из профиля
    	*
    	* Тип владельца
    	* а) группы Владелец, Агент, Агентство
    	* 1 - все Владелец
    	* 2 - все Агент
    	* 3 - все Агентство
    	* 4 - по группам (настройки какая группа кому соотв.)
    	*/
    	
    	//$contacts_str='1:1;3:2';
    	//$contacts_str='*:3';
    	$contacts_str=$this->getConfigValue('apps.yandexrealty.contacts_assoc_str');
    	$groups_assoc_str=$this->getConfigValue('apps.yandexrealty.groups_assoc_str');
    	$this->contacts_export_mode=intval($this->getConfigValue('apps.yandexrealty.contacts_export_mode'));
    	//$this->contacts_export_mode=1;
    	
    	
    	//$groups_assoc_str='1:o;3:a;2:d';
    	
    	if($this->contacts_export_mode==1){
    		
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/users_manager.php';
    		$UM=new Users_Manager();
    		
    		//$contacts_mode=array();
    		//$group_assoc=array();
    		
    		$contacts_str=trim($contacts_str);
    		
    		if($contacts_str==''){
    			$this->contacts_mode['*']=2;
    		}else{
    			$matches=array();
    			if(preg_match('/^\*:([1-4])$/', $contacts_str, $matches)){
    				$this->contacts_mode['*']=$matches[1];
    			}else{
    				$matches_all=array();
    				if(preg_match_all('/((\*|[\d]+):([1-4]))/', $contacts_str, $matches_all)){
    					foreach ($matches_all[2] as $k=>$g){
							if($g=='*'){
								$this->contacts_mode['*']=$matches_all[3][$k];
							}else{
								$this->contacts_mode[intval($g)]=$matches_all[3][$k];
							}
    						
    					}
    				}else{
	    				$this->contacts_mode['*']=2;
	    			}
    			}
    		}
    		
    		$groups_assoc_str=trim($groups_assoc_str);
    		
    		if($groups_assoc_str==''){
    			$this->group_assoc['*']='o';
    		}else{
    			$matches=array();
    			if(preg_match('/^\*:([oad])$/', $groups_assoc_str, $matches)){
    				$this->group_assoc['*']=$matches[1];
    			}else{
    				if(preg_match_all('/((\*|[\d]+):([oad]))/', $groups_assoc_str, $matches_all)){
						foreach ($matches_all[2] as $k=>$g){
							if($g=='*'){
								$this->group_assoc['*']=trim($matches_all[3][$k]);
							}else{
								$this->group_assoc[intval($g)]=trim($matches_all[3][$k]);
							}
							
						}
					}else{
						$this->group_assoc['*']='o';
					}
    			}
    		}
    	}
    	
    	$xml_text='';
    	$errors=array();
    	
    	foreach($data as $data_item){
    		$rs='';
    		if($data_item['price'] > 0 AND $data_item['city'] !== ''){
				$count++;
				$rs.='<offer internal-id="'.(int)$data_item['id'].'">'."\n";
				$data_topic=$data_item['topic_id'];
				
				$this_realty_supertype=intval($associations[$data_topic]['realty_type']);
				
				if($this_realty_supertype==self::$EXP_TY_RESIDENTIAL){
					$rs.='<property-type>жилая</property-type>'."\n";
				}elseif($this_realty_supertype==self::$EXP_TY_COMMERCIAL){
					$rs.='<category>коммерческая</category>'."\n";
				}elseif(isset($data_item['property_type']) && $data_item['property_type']!=''){
					$rs.='<property-type>'.self::symbolsClear($data_item['property_type']).'</property-type>'."\n";
				}else{
					$errors[]=$data_item['id'].' DECLINED: Supertype unknown';
					continue;
				}
				
				/*if(!empty($associations) && isset($associations[$data_topic]) && $associations[$data_topic]['realty_type']!=0){
					$rs.='<property-type>'.$this->realty_types[$associations[$data_topic]['realty_type']].'</property-type>'."\n";
					
				}elseif(isset($data_item['property_type']) && $data_item['property_type']!=''){
					$rs.='<property-type>'.self::symbolsClear($data_item['property_type']).'</property-type>'."\n";
				}else{
					$rs.='<property-type>жилая</property-type>'."\n";
				}*/
				
				
				$operational_type='sale';
				if(!empty($associations) && isset($associations[$data_topic]) && $associations[$data_topic]['operation_type']!=0){
					$rs.='<type>'.$this->op_types[$associations[$data_topic]['operation_type']].'</type>'."\n";
					if($associations[$data_topic]['operation_type']==2){
						$operational_type='rent';
					}
				}else{
					$st=explode(':', $this->getConfigValue('apps.yandexrealty.sell'));
					$rt=explode(':', $this->getConfigValue('apps.yandexrealty.rent'));
					$selltype_field=trim($st[0]);
					$selltype_value=trim($st[1]);
					$renttype_field=trim($rt[0]);
					$renttype_value=trim($rt[1]);
					
					if($selltype_field!='' && $selltype_value!='' && isset($data_item[$selltype_field]) && $data_item[$selltype_field]==$selltype_value){
						$rs.='<type>продажа</type>'."\n";
					}elseif($renttype_field!='' && $renttype_value!='' && isset($data_item[$renttype_field]) && $data_item[$renttype_field]==$renttype_value){
						$rs.='<type>аренда</type>'."\n";
						$operational_type='rent';
					}elseif(isset($data_item['optype']) && (int)$data_item['optype']==1){
						$rs.='<type>аренда</type>'."\n";
						$operational_type='rent';
					}else{
						$rs.='<type>продажа</type>'."\n";
					}
								
				}
				
				if($this->export_mode=='ETOWN'){
					$rs.='<category>'.self::symbolsClear($catalogChains[$data_item['topic_id']]).'</category>'."\n";
				}elseif($this_realty_supertype==self::$EXP_TY_RESIDENTIAL){
					if(!empty($associations) && isset($associations[$data_topic]) && $associations[$data_topic]['realty_category']!=0 && isset($this->realty_categories[$associations[$data_topic]['realty_category']])){
						$rs.='<category>'.$this->realty_categories[$associations[$data_topic]['realty_category']].'</category>'."\n";
					}else{
						$errors[]=$data_item['id'].' DECLINED: Residential category unknown';
						continue;
						$rs.='<category>'.self::symbolsClear($data_item['topic']).'</category>'."\n";
					}
				}elseif($this_realty_supertype==self::$EXP_TY_COMMERCIAL){
					if(!empty($associations) && isset($associations[$data_topic]) && $associations[$data_topic]['realty_category']!=0 && isset($this->commercial_names[$associations[$data_topic]['realty_category']])){
						$rs.='<commercial-type>'.$this->commercial_names[$associations[$data_topic]['realty_category']].'</commercial-type>'."\n";
					}else{
						$errors[]=$data_item['id'].' DECLINED: Commercial type unknown';
						continue;
					}
				}
				
				//if($this_realty_supertype==self::$EXP_TY_RESIDENTIAL){
			
				/*if($this->export_mode=='ETOWN'){
					$rs.='<category>'.self::symbolsClear($catalogChains[$data_item['topic_id']]).'</category>'."\n";
				}elseif(!empty($associations) && isset($associations[$data_topic]) && $associations[$data_topic]['realty_category']!=0){
					$rs.='<category>'.$this->realty_categories[$associations[$data_topic]['realty_category']].'</category>'."\n";
				}else{
					$rs.='<category>'.self::symbolsClear($data_item['topic']).'</category>'."\n";
				}*/
				
				//$parent_category_url='';
				///$href='';
				$href=$this->getRealtyHREF($data_item['id'], true, array('topic_id'=>$data_item['topic_id'], 'alias'=>$data_item['translit_alias']));
				/*if(1==$this->getConfigValue('apps.seo.level_enable')){
					 
					if($category_structure['catalog'][$data_item['topic_id']]['url']!=''){
						$parent_category_url=trim($category_structure['catalog'][$data_item['topic_id']]['url'], '/').'/';
					}
				}
				if(1==$this->getConfigValue('apps.seo.data_alias_enable') && $data_item['translit_alias']!=''){
					$href='/'.$parent_category_url.urlencode($data_item['translit_alias']);
				}elseif(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
					$href='/'.$parent_category_url.'realty'.$data_item['id'].'.html';
				}else{
					$href='/'.$parent_category_url.'realty'.$data_item['id'];
				}*/

				//$rs.='<url>'.$this->getServerFullUrl().self::symbolsClear($href).'</url>'."\n";
				$rs.='<url>'.$href.'</url>'."\n";
				$date_timestamp=strtotime($data_item['date_added']);
				
				$rs.='<creation-date>'.$this->formdate($date_timestamp).'</creation-date>'."\n";
				if((time()-$date_timestamp)>($this->critical_term*24*3600)){
					$rs.='<last-update-date>'.$this->formdate(time()-(rand($this->min_normal_term,$this->max_normal_term)*24*3600)).'</last-update-date>'."\n";
				}
				
				if(isset($form_data_shared['expire_date']) && isset($data_item['expire_date']) && $data_item['expire_date']!='' &&  $data_item['expire_date']!='0000-00-00 00:00:00'){
					$rs.='<expire-date>'.$this->formdate(strtotime($data_item['expire_date'])).'</expire-date>'."\n";
				}
				
				if(isset($form_data_shared['payed_adv']) && isset($data_item['payed_adv'])){
					if((int)$data_item['payed_adv']==1){
						$rs.='<payed-adv>1</payed-adv>'."\n";
					}else{
						$rs.='<payed-adv>0</payed-adv>'."\n";
					}
				}
				
				if(isset($form_data_shared['manually_added']) && isset($data_item['manually_added'])){
					if((int)$data_item['manually_added']==1){
						$rs.='<manually-added>1</manually-added>'."\n";
					}else{
						$rs.='<manually-added>0</manually-added>'."\n";
					}
				}
				
				/***********************LOCATION***************************/
				$rs.='<location>'."\n";
				
				$country=trim($this->getConfigValue('apps.yandexrealty.country_global'));
				if($country==''){
					if(''!=trim($this->getConfigValue('apps.yandexrealty.country_from'))){
						$country_from=trim($this->getConfigValue('apps.yandexrealty.country_from'));
					}else{
						$country_from='';
					}
						
					if($country_from!='' && isset($data_item[$country_from])){
						$country=$data_item[$country_from];
					}else{
						$country=$data_item['country'];
					}
				}
				
				if($country==''){
					$errors[]=$data_item['id'].' DECLINED: Country unknown';
					continue;
				}else{
					$rs.='<country>'.self::symbolsClear($country).'</country>'."\n";
				}
				
				$region=trim($this->getConfigValue('apps.yandexrealty.region_global'));
				if($region==''){
					if(''!=trim($this->getConfigValue('apps.yandexrealty.region_from'))){
						$region_from=trim($this->getConfigValue('apps.yandexrealty.region_from'));
					}else{
						$region_from='';
					}
				
					if($region_from!='' && isset($data_item[$region_from])){
						$region=$data_item[$region_from];
					}else{
						$region=$data_item['region'];
					}
				}
				
				if($region!=''){
					$rs.='<region>'.self::symbolsClear($region).'</region>'."\n";
				}
				
				$city=trim($this->getConfigValue('apps.yandexrealty.city_global'));
				if($city==''){
					if(''!=trim($this->getConfigValue('apps.yandexrealty.city_from'))){
						$city_from=trim($this->getConfigValue('apps.yandexrealty.city_from'));
					}else{
						$city_from='';
					}
				
					if($city_from!='' && isset($data_item[$city_from])){
						$city=$data_item[$city_from];
					}else{
						$city=$data_item['city'];
					}
				}
				
    			if($city!=''){
					$rs.='<locality-name>'.self::symbolsClear($city).'</locality-name>'."\n";
				}
	
		    	if($data_item['district']!=''){
					$rs.='<sub-locality-name>'.self::symbolsClear($data_item['district']).'</sub-locality-name>'."\n";
				}
				$rs.='<address>';
				
				if(''!=trim($this->getConfigValue('apps.yandexrealty.street_from'))){
					$street_from=trim($this->getConfigValue('apps.yandexrealty.street_from'));
				}else{
					$street_from='';
				}
				
				if($street_from!='' && isset($data_item[$street_from])){
					$street=$data_item[$street_from];
				}else{
					$street=$data_item['street'];
				}
				//$street=preg_replace('/(шос.)/');
				$street = str_replace('шос.', 'шоссе', $street);
				$street = str_replace('ул.', 'улица', $street);
				$street = str_replace('пр.', 'проспект', $street);
				$street = str_replace('наб.', 'набережная', $street);
				$street = str_replace('бул.', 'бульвар', $street);
				$street = str_replace('пер.', 'переулок', $street);
				$street = str_replace('свх.', 'совхоз', $street);
				$street = str_replace('прд.', 'проезд', $street);
				$street = str_replace('дер.', 'деревня', $street);
				$street = str_replace('пос.', 'поселок', $street);
				$street = str_replace('ст.', 'станция', $street);
				$street = str_replace('сад-во', 'садоводство', $street);
				$street = str_replace('пгт.', 'поселок', $street);
				$street = str_replace('алл.', 'аллея', $street);
				$street = str_replace('пл.', 'площадь', $street);
				$street = str_replace('мкр.', 'микрорайон', $street);
					
				$rs.= $street;
				if($data_item['number']!=''){
					$rs.=', '.self::symbolsClear($data_item['number']);
				}
				$rs.='</address>'."\n";
				if($data_item['metro']!=''){
					$rs.='<metro>'."\n";
						$rs.='<name>'.self::symbolsClear($data_item['metro']).'</name>'."\n";
						if(isset($data_item['time_on_transport']) && (int)$data_item['time_on_transport']!=0){
							$rs.='<time-on-transport>'.(int)$data_item['time_on_transport'].'</time-on-transport>'."\n";
						}
						if(isset($data_item['time_on_foot']) && (int)$data_item['time_on_foot']!=0){
							$rs.='<time-on-foot>'.(int)$data_item['time_on_foot'].'</time-on-foot>'."\n";
						}
						
						
					$rs.='</metro>'."\n";
				}
				
				if(isset($form_data_shared['railway_station']) && isset($data_item['railway_station']) && $data_item['railway_station']!=''){
					$rs.='<railway-station>'.self::symbolsClear($data_item['railway_station']).'</railway-station>'."\n";
				}
				
				if(isset($form_data_shared['direction']) && isset($data_item['direction']) && $data_item['direction']!=''){
					$rs.='<direction>'.self::symbolsClear($data_item['direction']).'</direction>'."\n";
				}
				
				if(isset($form_data_shared['distance']) && isset($data_item['distance']) && (int)$data_item['distance']!=''){
					$rs.='<distance>'.$data_item['distance'].'</distance>'."\n";
				}
				
				if(isset($form_data_shared['geo']) && isset($data_item['geo_lat']) && $data_item['geo_lat']!='' && isset($data_item['geo_lng']) && $data_item['geo_lng']!=''){
					$rs.='<latitude>'.$data_item['geo_lat'].'</latitude>'."\n";
					$rs.='<longitude>'.$data_item['geo_lng'].'</longitude>'."\n";
				}
				
				$rs.='</location>'."\n";
				/***********************.LOCATION***************************/
				
				$rs.='<sales-agent>'."\n";
				
				if($this->contacts_export_mode==1){
					$uid=intval($data_item['user_id']);
					if(!isset($this->users_cache[$uid])){
						$this->users_cache[$uid]=$UM->getUserProfileData($uid);
					}
					$user=$this->users_cache[$uid];
					$gid=intval($user['group_id']);
					
					$contact_export_variant=0;
					
			
					if(count($this->contacts_mode)==1 && isset($this->contacts_mode['*'])){
						$contact_export_variant=$this->contacts_mode['*'];
					}elseif(isset($this->contacts_mode[$gid])){
						$contact_export_variant=$this->contacts_mode[$gid];
					}elseif(isset($this->contacts_mode['*'])){
						$contact_export_variant=$this->contacts_mode['*'];
					}
					
				
					if(count($this->group_assoc)==1 && isset($this->group_assoc['*'])){
						$exporter_type=$this->group_assoc['*'];
					}elseif(isset($this->group_assoc[$gid])){
						$exporter_type=$this->group_assoc[$gid];
					}elseif(isset($this->group_assoc['*'])){
						$exporter_type=$this->group_assoc['*'];
					}
					
					if($exporter_type=='a'){
						$rs.='<category>agency</category>'."\n";
						//$rs.='<organization>organization</organization>'."\n";
						//$rs.='<url>url</url>'."\n";
					}elseif($exporter_type=='d'){
						$rs.='<category>developer</category>'."\n";
					}else{
						$rs.='<category>owner</category>'."\n";
					}
					
					if($contact_export_variant==1){
						$rs.='<phone>'.self::symbolsClear($data_item['phone']).'</phone>'."\n";
						$rs.='<email>'.self::symbolsClear($data_item['email']).'</email>'."\n";
						$rs.='<name>'.self::symbolsClear($data_item['fio']).'</name>'."\n";
					}elseif($contact_export_variant==2){
						$rs.='<phone>'.self::symbolsClear($user['phone']).'</phone>'."\n";
						$rs.='<email>'.self::symbolsClear($user['email']).'</email>'."\n";
						$rs.='<name>'.self::symbolsClear($user['fio']).'</name>'."\n";
					}elseif($contact_export_variant==3){
						$rs.='<phone>'.(''!==self::symbolsClear($data_item['phone']) ? self::symbolsClear($data_item['phone']) : self::symbolsClear($user['phone'])).'</phone>'."\n";
						$rs.='<email>'.(''!==self::symbolsClear($data_item['email']) ? self::symbolsClear($data_item['email']) : self::symbolsClear($user['email'])).'</email>'."\n";
						$rs.='<name>'.(''!==self::symbolsClear($data_item['fio']) ? self::symbolsClear($data_item['fio']) : self::symbolsClear($user['fio'])).'</name>'."\n";
					}elseif($contact_export_variant==4){
						$rs.='<phone>'.(''!==self::symbolsClear($user['phone']) ? self::symbolsClear($user['phone']) : self::symbolsClear($data_item['phone'])).'</phone>'."\n";
						$rs.='<email>'.(''!==self::symbolsClear($user['email']) ? self::symbolsClear($user['email']) : self::symbolsClear($data_item['email'])).'</email>'."\n";
						$rs.='<name>'.(''!==self::symbolsClear($user['fio']) ? self::symbolsClear($user['fio']) : self::symbolsClear($data_item['fio'])).'</name>'."\n";
					}
					
				}else{
					
					if($data_item['fio']!='' AND $data_item['user_id']==$this->getUnregisteredUserId()){
						$rs.='<category>owner</category>'."\n";
						$rs.='<phone>'.self::symbolsClear($data_item['phone']).'</phone>'."\n";
						$rs.='<email>'.self::symbolsClear($data_item['email']).'</email>'."\n";
						$rs.='<name>'.self::symbolsClear($data_item['fio']).'</name>'."\n";
					}else{
						/// инфо про агентство
						require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/users_manager.php';
						$UM=new Users_Manager();
						
						
						$user=$UM->getUserProfileData($data_item['user_id']);
							
						if($this->getConfigValue('apps.company.enable')==1){
							if($user['company_id']!=0){
								require_once SITEBILL_DOCUMENT_ROOT.'/apps/company/admin/admin.php';
								$CA=new company_admin();
								$company=$CA->load_by_id($user['company_id']);
								//print_r($company);
								$rs.='<phone>'.self::symbolsClear($db->row['agency_agentphone']).'</phone>'."\n";
								$rs.='<organization>'.self::symbolsClear($company['name']['value']).'</organization>'."\n";
								$rs.='<category>agency</category>'."\n";
								$rs.='<url>'.self::symbolsClear($company['site']['value']).'</url>'."\n";
								$rs.='<email>'.self::symbolsClear($company['email']['value']).'</email>'."\n";
								$rs.='<name>'.self::symbolsClear($company['name']['value']).'</name>'."\n";
								$rs.='<phone>'.self::symbolsClear($company['phone1']['value']).'</phone>'."\n";
							}else{
								$rs.='<category>owner</category>'."\n";
								$rs.='<phone>'.self::symbolsClear($user['phone']).'</phone>'."\n";
								$rs.='<email>'.self::symbolsClear($user['email']).'</email>'."\n";
								$rs.='<name>'.self::symbolsClear($user['fio']).'</name>'."\n";
							}
						}else{
							$rs.='<category>owner</category>'."\n";
							$rs.='<phone>'.self::symbolsClear($user['phone']).'</phone>'."\n";
							$rs.='<email>'.self::symbolsClear($user['email']).'</email>'."\n";
							$rs.='<name>'.self::symbolsClear($user['fio']).'</name>'."\n";
						}
					}
				}
				
				
				
				if(isset($form_data_shared['partner']) && isset($data_item['partner']) && $data_item['partner']!=''){
					$rs.='<partner>'.self::symbolsClear($data_item['partner']).'</partner>'."\n";
				}
				
				$rs.='</sales-agent>'."\n";
				
				$rs.='<price>'."\n";
				$rs.='<value>'.self::symbolsClear($data_item['price']).'</value>'."\n";
				if(isset($form_data_shared['currency_id']) && isset($data_item['currency']) && $data_item['currency']!=''){
					$currency=self::currencyCheck($data_item['currency']);
				}else{
					$currency=$this->currency;
				}
				$rs.='<currency>'.$currency.'</currency>'."\n";
				
				if($operational_type=='rent' && isset($data_item['period']) && $data_item['period']!=''){
					$rs.='<period>'.self::symbolsClear($data_item['period']).'</period>'."\n";
				}
				if(isset($form_data_shared['unit']) && isset($data_item['unit']) && $data_item['unit']!=''){
					$rs.='<unit>'.self::symbolsClear($data_item['unit']).'</unit>'."\n";
				}
				
				$rs.='</price>'."\n";
				
				if(isset($form_data_shared['not_for_agents']) && isset($data_item['not_for_agents'])){
					if((int)$data_item['not_for_agents']==1){
						$rs.='<not-for-agents>1</not-for-agents>'."\n";
					}else{
						$rs.='<not-for-agents>0</not-for-agents>'."\n";
					}
				}
				
				if(isset($form_data_shared['haggle']) && isset($data_item['haggle'])){
					if((int)$data_item['haggle']==1){
						$rs.='<haggle>1</haggle>'."\n";
					}else{
						$rs.='<haggle>0</haggle>'."\n";
					}
				}
				
    			if(isset($form_data_shared['deal_status']) && isset($data_item['deal_status']) && trim($data_item['deal_status'])!=''){
					if($form_data_shared['deal_status']['type']=='safe_string'){
						$rs.='<deal-status>'.trim($data_item['deal_status']).'</deal-status>'."\n";
					}elseif($form_data_shared['deal_status']['type']=='select_box' && $data_item['deal_status']!=0 && isset($form_data_shared['deal_status']['select_data'][$data_item['deal_status']])){
						$rs.='<deal-status>'.$form_data_shared['deal_status']['select_data'][$data_item['deal_status']].'</deal-status>'."\n";
					}
					
				}else{
					//TODO: Make this error more softly
					//$errors[]=$data_item['id'].' DECLINED: Deal status unknown';
					//continue;
				}
				
				if(isset($form_data_shared['mortgage']) && isset($data_item['mortgage'])){
					if((int)$data_item['mortgage']==1){
						$rs.='<mortgage>1</mortgage>'."\n";
					}else{
						$rs.='<mortgage>0</mortgage>'."\n";
					}
				}
				
				if(isset($form_data_shared['prepayment']) && isset($data_item['prepayment']) && (int)$data_item['prepayment']!=0){
					$rs.='<prepayment>'.(int)$data_item['prepayment'].'</prepayment>'."\n";
				}
				
				if(isset($form_data_shared['rent_pledge']) && isset($data_item['rent_pledge'])){
					if((int)$data_item['rent_pledge']==1){
						$rs.='<rent-pledge>1</rent-pledge>'."\n";
					}else{
						$rs.='<rent-pledge>0</rent-pledge>'."\n";
					}
				}
				
				if(isset($form_data_shared['agent_fee']) && isset($data_item['agent_fee']) && (int)$data_item['agent_fee']!=0){
					$rs.='<agent-fee>'.(int)$data_item['agent_fee'].'</agent-fee>'."\n";
				}
				
				if(isset($form_data_shared['with_pets']) && isset($data_item['with_pets'])){
					if((int)$data_item['with_pets']==1){
						$rs.='<with-pets>1</with-pets>'."\n";
					}else{
						$rs.='<with-pets>0</with-pets>'."\n";
					}
				}
				
				if(isset($form_data_shared['with_children']) && isset($data_item['with_children'])){
					if((int)$data_item['with_children']==1){
						$rs.='<with-children>1</with-children>'."\n";
					}else{
						$rs.='<with-children>0</with-children>'."\n";
					}
				}
				
				$text=$data_item['text'];
				$text=strip_tags($text);
				$text=self::symbolsClear($text);
				$rs.='<description>'.$text.'</description>'."\n";
				//$rs.='<description>'.htmlspecialchars(strip_tags($data_item['text']), ENT_QUOTES, SITE_ENCODING).'</description>'."\n";
				
				
				if(1==(int)$this->getConfigValue('apps.yandexrealty.nowatermark_export') && 1==(int)$this->getConfigValue('save_without_watermark')){
					$image_dest=$this->getServerFullUrl().'/img/data/nowatermark/';
				}else{
					$image_dest=$this->getServerFullUrl().'/img/data/';
				}
				
				if($hasUploadify){
					$imgids=array();
					$imgs=array();
					$query='SELECT image_id FROM '.DB_PREFIX.'_data_image WHERE id='.$data_item['id'];
					$DBC=DBC::getInstance();
					$stmt=$DBC->query($query);
					if($stmt){
						while($ar=$DBC->fetch($stmt)){
							$imgids[]=$ar['image_id'];
						}
					}
					
					if(count($imgids)>0){
						$query='SELECT normal, preview FROM '.DB_PREFIX.'_image WHERE image_id IN ('.implode(',',$imgids).')';
						$stmt=$DBC->query($query);
						if($stmt){
							while($ar=$DBC->fetch($stmt)){
								$imgs[]=$ar;
							}
						}
					}
					
					if(count($imgs)>0){
					
						foreach($imgs as $v){
							if($this->export_mode=='ETOWN'){
								$rs.='<imagefile>'."\n";
								$rs.='<image>'.$image_dest.$v['preview'].'</image>'."\n";
								$rs.='<image>'.$image_dest.$v['normal'].'</image>'."\n";
								$rs.='</imagefile>'."\n";
							}else{
								$rs.='<image>'.$image_dest.$v['normal'].'</image>'."\n";
							}
					
						}
							
					}
				}elseif($uploadsField!==false && isset($data_item[$uploadsField]) && $data_item[$uploadsField]!=''){
					$imgs=unserialize($data_item[$uploadsField]);
					if(count($imgs)>0){
							
						foreach($imgs as $v){
							if($this->export_mode=='ETOWN'){
								$rs.='<imagefile>'."\n";
								$rs.='<image>'.$image_dest.$v['preview'].'</image>'."\n";
								$rs.='<image>'.$image_dest.$v['normal'].'</image>'."\n";
								$rs.='</imagefile>'."\n";
							}else{
								$rs.='<image>'.$image_dest.$v['normal'].'</image>'."\n";
							}
								
						}
							
					}
				}
				
				/*if(isset($data_item['square_kitchen'])){
					$x=preg_replace('/[^0-9\.,]/','',$data_item['square_kitchen']);
					$x=str_replace(',', '.', $x);
					$x=floatval($x);
					if($x!=0){
						$rs.='<kitchen-space>'."\n";
						$rs.='<value>'.$x.'</value>'."\n";
						$rs.='<unit>кв.м</unit>'."\n";
						$rs.='</kitchen-space>'."\n";
					}
				}*/
				
				if(isset($form_data_shared['renovation']) && isset($data_item['renovation'])/* && (int)$data_item['renovation']!=0*/){
					if($form_data_shared['renovation']['type']=='select_box' && (int)$data_item['renovation']!=0 && isset($form_data_shared['renovation']['select_data'][$data_item['renovation']])){
						$rs.='<renovation>'.self::symbolsClear($form_data_shared['renovation']['select_data'][$data_item['renovation']]).'</renovation>'."\n";
					}elseif($form_data_shared['renovation']['type']!='select_box' &&  $data_item['renovation']!=''){
						$rs.='<renovation>'.self::symbolsClear($data_item['renovation']).'</renovation>'."\n";
					}
					//$rs.='<renovation>'.self::symbolsClear($data_item['renovation']).'</renovation>'."\n";
				}
				
				if(!empty($associations) && isset($associations[$data_topic]) && $associations[$data_topic]['realty_category']!=0){
					if(!in_array($associations[$data_topic]['realty_category'], array(4, 15, 16, 25))){
						if(isset($data_item['square_all'])){
							$x=preg_replace('/[^0-9\.,]/','',$data_item['square_all']);
							$x=str_replace(',', '.', $x);
							$x=floatval($x);
							if($x!=0){
								$rs.='<area>'."\n";
								$rs.='<value>'.$x.'</value>'."\n";
								$rs.='<unit>кв.м</unit>'."\n";
								$rs.='</area>'."\n";
							}
						}
						
						if(isset($data_item['square_live'])){
							$x=preg_replace('/[^0-9\.,]/','',$data_item['square_live']);
							$x=str_replace(',', '.', $x);
							$x=floatval($x);
							if($x!=0){
								$rs.='<living-space>'."\n";
								$rs.='<value>'.$x.'</value>'."\n";
								$rs.='<unit>кв.м</unit>'."\n";
								$rs.='</living-space>'."\n";
							}
						}
						
						
						if(isset($data_item['square_kitchen'])){
							$x=preg_replace('/[^0-9\.,]/','',$data_item['square_kitchen']);
							$x=str_replace(',', '.', $x);
							$x=floatval($x);
							if($x!=0){
								$rs.='<kitchen-space>'."\n";
								$rs.='<value>'.$x.'</value>'."\n";
								$rs.='<unit>кв.м</unit>'."\n";
								$rs.='</kitchen-space>'."\n";
							}
						}
					}
				}
				
    			if(!empty($associations) && isset($associations[$data_topic]) && $associations[$data_topic]['realty_category']!=0){
					if(in_array($associations[$data_topic]['realty_category'], array(4, 12, 13, 15, 16, 25))){
						if(in_array($associations[$data_topic]['realty_category'], array(4, 15, 16, 25))){
							$lot_area_field=trim($this->getConfigValue('apps.yandexrealty.lot_area'));
							
							$lot_area_field_dim=trim($this->getConfigValue('apps.yandexrealty.lot_area_dim'));
							$meash='сот';
							if($lot_area_field_dim=='acr'){
								$meash='сот';
							}elseif($lot_area_field_dim=='sqm'){
								$meash='кв.м';
							}elseif($lot_area_field_dim=='ha'){
								$meash='га';
							}
							if($lot_area_field==''){
								$lot_area_field='lot_area';
							}
							
							if(isset($data_item[$lot_area_field])){
								$x=preg_replace('/[^0-9.,]/','',$data_item[$lot_area_field]);
								$x=str_replace(',', '.', $x);
								$x=floatval($x);
								//$x=preg_replace('/[^0-9\.,]/','',$data_item['lot_area']);
								if($x!=0){
									$rs.='<lot-area>'."\n";
									$rs.='<value>'.$x.'</value>'."\n";
									$rs.='<unit>'.$meash.'</unit>'."\n";
									$rs.='</lot-area>'."\n";
								}
							}
						}else{
							$lot_area_field=trim($this->getConfigValue('apps.yandexrealty.add_lot_area'));
							$lot_area_field_dim=trim($this->getConfigValue('apps.yandexrealty.add_lot_area_dim'));
							$meash='сот';
							if($lot_area_field_dim=='acr'){
								$meash='сот';
							}elseif($lot_area_field_dim=='sqm'){
								$meash='кв.м';
							}elseif($lot_area_field_dim=='ha'){
								$meash='га';
							}
							if($lot_area_field==''){
								$lot_area_field='lot_area';
							}
							if(isset($data_item[$lot_area_field])){
								$x=preg_replace('/[^0-9.,]/','',$data_item[$lot_area_field]);
								$x=str_replace(',', '.', $x);
								$x=floatval($x)*$k;
								//$x=preg_replace('/[^0-9\.,]/','',$data_item['lot_area']);
								if($x!=0){
									$rs.='<lot-area>'."\n";
									$rs.='<value>'.$x.'</value>'."\n";
									$rs.='<unit>'.$meash.'</unit>'."\n";
									$rs.='</lot-area>'."\n";
								}
							}
						}
						
						if(isset($form_data_shared['lot_type'])/* && $data_item['lot_type']!=''*/){
							if($form_data_shared['lot_type']['type']=='select_box' && intval($data_item['lot_type'])!=0 && isset($form_data_shared['lot_type']['select_data'][$data_item['lot_type']])){
								$rs.='<lot-type>'.self::symbolsClear($form_data_shared['lot_type']['select_data'][$data_item['lot_type']]).'</lot-type>'."\n";
							}elseif($form_data_shared['lot_type']['type']!='select_box' &&  $data_item['lot_type']!=''){
								$rs.='<lot-type>'.self::symbolsClear($data_item['lot_type']).'</lot-type>'."\n";
							}
							//$rs.='<lot-type>'.self::symbolsClear($data_item['lot_type']).'</lot-type>'."\n";
						}
					}
				}
				
				if(isset($form_data_shared['new_flat']) && isset($data_item['new_flat'])){
					if((int)$data_item['new_flat']==1){
						$rs.='<new-flat>1</new-flat>'."\n";
					}else{
						//$rs.='<new-flat>0</new-flat>'."\n";
					}
				}
				
				if(isset($form_data_shared['rooms']) && isset($data_item['rooms']) && (int)$data_item['rooms']!=0){
					$rs.='<rooms>'.(int)$data_item['rooms'].'</rooms>'."\n";
				}elseif(isset($form_data_shared['room_count']) && isset($data_item['room_count']) && (int)$data_item['room_count']!=0){
					$rs.='<rooms>'.(int)$data_item['room_count'].'</rooms>'."\n";
				}
				
				if(isset($form_data_shared['rooms_offered']) && isset($data_item['rooms_offered']) && (int)$data_item['rooms_offered']!=0){
					$rs.='<rooms-offered>'.(int)$data_item['rooms_offered'].'</rooms-offered>'."\n";
				}else{
					$rs.='<rooms-offered>'.(int)$data_item['room_count'].'</rooms-offered>'."\n";
				}
				
				if(isset($form_data_shared['open_plan']) && isset($data_item['open_plan']) && 1===intval($data_item['open_plan'])){
					$rs.='<open-plan>1</open-plan>'."\n";
				}
				
				if(isset($form_data_shared['rooms_type']) && isset($data_item['rooms_type'])/* && $data_item['rooms_type']!=''*/){
					if($form_data_shared['rooms_type']['type']=='select_box' && intval($data_item['rooms_type'])!=0 && isset($form_data_shared['rooms_type']['select_data'][$data_item['rooms_type']])){
						$rs.='<rooms-type>'.self::symbolsClear($form_data_shared['rooms_type']['select_data'][$data_item['rooms_type']]).'</rooms-type>'."\n";
					}elseif($form_data_shared['rooms_type']['type']!='select_box' &&  $data_item['rooms_type']!=''){
						$rs.='<rooms-type>'.self::symbolsClear($data_item['rooms_type']).'</rooms-type>'."\n";
					}
					
					//$rs.='<rooms-type>'.self::symbolsClear($data_item['rooms_type']).'</rooms-type>'."\n";
				}
				
				if(isset($form_data_shared['is_telephone']) && isset($data_item['is_telephone'])){
					if((int)$data_item['is_telephone']==1){
						$rs.='<phone>1</phone>'."\n";
					}else{
						$rs.='<phone>0</phone>'."\n";
					}
				}
				
				if(isset($form_data_shared['internet']) && isset($data_item['internet'])){
					if((int)$data_item['internet']==1){
						$rs.='<internet>1</internet>'."\n";
					}else{
						$rs.='<internet>0</internet>'."\n";
					}
				}
				
				if(isset($form_data_shared['room_furniture']) && isset($data_item['room_furniture'])){
					if((int)$data_item['room_furniture']==1){
						$rs.='<room-furniture>1</room-furniture>'."\n";
					}else{
						$rs.='<room-furniture>0</room-furniture>'."\n";
					}
				}
				
				if(isset($form_data_shared['kitchen_furniture']) && isset($data_item['kitchen_furniture'])){
					if((int)$data_item['kitchen_furniture']==1){
						$rs.='<kitchen-furniture>1</kitchen-furniture>'."\n";
					}else{
						$rs.='<kitchen-furniture>0</kitchen-furniture>'."\n";
					}
				}
				
				if(isset($form_data_shared['television']) && isset($data_item['television'])){
					if((int)$data_item['television']==1){
						$rs.='<television>1</television>'."\n";
					}else{
						$rs.='<television>0</television>'."\n";
					}
				}
				
				if(isset($form_data_shared['washing_machine']) && isset($data_item['washing_machine'])){
					if((int)$data_item['washing_machine']==1){
						$rs.='<washing-machine>1</washing-machine>'."\n";
					}else{
						$rs.='<washing-machine>0</washing-machine>'."\n";
					}
				}
				
				if(isset($form_data_shared['refrigerator']) && isset($data_item['refrigerator'])){
					if((int)$data_item['refrigerator']==1){
						$rs.='<refrigerator>1</refrigerator>'."\n";
					}else{
						$rs.='<refrigerator>0</refrigerator>'."\n";
					}
				}
				
				if(isset($form_data_shared['balcony']) && isset($data_item['balcony'])){
					if($form_data_shared['balcony']['type']=='select_box' && intval($data_item['balcony'])!=0 && isset($form_data_shared['balcony']['select_data'][$data_item['balcony']])){
						$rs.='<balcony>'.self::symbolsClear($form_data_shared['balcony']['select_data'][$data_item['balcony']]).'</balcony>'."\n";
					}elseif($form_data_shared['balcony']['type']!='select_box' &&  $data_item['balcony']!=''){
						$rs.='<balcony>'.self::symbolsClear($data_item['balcony']).'</balcony>'."\n";
					}
					//$rs.='<balcony>'.self::symbolsClear($data_item['balcony']).'</balcony>'."\n";
				}
				
				if(isset($form_data_shared['bathroom_unit']) && isset($data_item['bathroom_unit'])/* && $data_item['bathroom_unit']!=''*/){
					if($form_data_shared['bathroom_unit']['type']=='select_box' && intval($data_item['bathroom_unit'])!=0 && isset($form_data_shared['bathroom_unit']['select_data'][$data_item['bathroom_unit']])){
						$rs.='<bathroom-unit>'.self::symbolsClear($form_data_shared['bathroom_unit']['select_data'][$data_item['bathroom_unit']]).'</bathroom-unit>'."\n";
					}elseif($form_data_shared['bathroom_unit']['type']!='select_box' &&  $data_item['bathroom_unit']!=''){
						$rs.='<bathroom-unit>'.self::symbolsClear($data_item['bathroom_unit']).'</bathroom-unit>'."\n";
					}
					//$rs.='<bathroom-unit>'.self::symbolsClear($data_item['bathroom_unit']).'</bathroom-unit>'."\n";
				}
				
				if(isset($form_data_shared['floor_covering']) && isset($data_item['floor_covering'])/* && $data_item['floor_covering']!=''*/){
					if($form_data_shared['floor_covering']['type']=='select_box' && intval($data_item['floor_covering'])!=0 && isset($form_data_shared['floor_covering']['select_data'][$data_item['floor_covering']])){
						$rs.='<floor-covering>'.self::symbolsClear($form_data_shared['floor_covering']['select_data'][$data_item['floor_covering']]).'</floor-covering>'."\n";
					}elseif($form_data_shared['floor_covering']['type']!='select_box' &&  $data_item['floor_covering']!=''){
						$rs.='<floor-covering>'.self::symbolsClear($data_item['floor_covering']).'</floor-covering>'."\n";
					}
					//$rs.='<floor-covering>'.self::symbolsClear($data_item['floor_covering']).'</floor-covering>'."\n";
				}
				
				if(isset($form_data_shared['window_view']) && isset($data_item['window_view']) && $data_item['window_view']!=''){
					if($form_data_shared['window_view']['type']=='select_box' && intval($data_item['window_view'])!=0 && isset($form_data_shared['window_view']['select_data'][$data_item['window_view']])){
						$rs.='<window-view>'.self::symbolsClear($form_data_shared['window_view']['select_data'][$data_item['window_view']]).'</window-view>'."\n";
					}elseif($form_data_shared['window_view']['type']!='select_box' &&  $data_item['window_view']!=''){
						$rs.='<window-view>'.self::symbolsClear($data_item['window_view']).'</window-view>'."\n";
					}
					//$rs.='<window-view>'.self::symbolsClear($data_item['window_view']).'</window-view>'."\n";
				}
				
				if(isset($form_data_shared['floor']) && isset($data_item['floor']) && (int)$data_item['floor']!=0){
					$rs.='<floor>'.(int)$data_item['floor'].'</floor>'."\n";
				}
				
				if(isset($form_data_shared['floor_count']) && isset($data_item['floor_count']) && (int)$data_item['floor_count']!=0){
					$rs.='<floors-total>'.(int)$data_item['floor_count'].'</floors-total>'."\n";
				}
				
				if(isset($form_data_shared['building_name']) && isset($data_item['building_name']) && $data_item['building_name']!=''){
					$rs.='<building-name>'.self::symbolsClear($data_item['building_name']).'</building-name>'."\n";
				}
				
				if(isset($form_data_shared['building_type']) && isset($data_item['building_type'])/* && $data_item['building_type']!=''*/){
					if($form_data_shared['building_type']['type']=='select_box' && intval($data_item['building_type'])!=0 && isset($form_data_shared['building_type']['select_data'][$data_item['building_type']])){
						$rs.='<building-type>'.self::symbolsClear($form_data_shared['building_type']['select_data'][$data_item['building_type']]).'</building-type>'."\n";
					}elseif($form_data_shared['building_type']['type']!='select_box' && $data_item['building_type']!=''){
						$rs.='<building-type>'.self::symbolsClear($data_item['building_type']).'</building-type>'."\n";
					}
					//$rs.='<building-type>'.self::symbolsClear($data_item['building_type']).'</building-type>'."\n";
				}elseif(isset($form_data_shared['walls']) && isset($data_item['walls'])/* && $data_item['walls']!=''*/){
					if($form_data_shared['walls']['type']=='select_box' && intval($data_item['walls'])!=0 && isset($form_data_shared['walls']['select_data'][$data_item['walls']])){
						$rs.='<building-type>'.self::symbolsClear($form_data_shared['walls']['select_data'][$data_item['walls']]).'</building-type>'."\n";
					}elseif($form_data_shared['walls']['type']!='select_box' && $data_item['walls']!=''){
						$rs.='<building-type>'.self::symbolsClear($data_item['walls']).'</building-type>'."\n";
					}
					
				}
				
				if(isset($form_data_shared['building_series']) && isset($data_item['building_series']) && $data_item['building_series']!=''){
					$rs.='<building-series>'.self::symbolsClear($data_item['building_series']).'</building-series>'."\n";
				}
				
				if(isset($form_data_shared['building_state']) && isset($data_item['building_state']) && $data_item['building_state']!=''){
					$rs.='<building-state>'.self::symbolsClear($data_item['building_state']).'</building-state>'."\n";
				}
				
				if(isset($form_data_shared['built_year']) && isset($data_item['built_year']) && $data_item['built_year']!=''){
					$x=preg_replace('/[^0-9]/', '', $data_item['built_year']);
					if(preg_match('/([1|2][0-9][0-9][0-9])/', $x, $matches)){
						$rs.='<built-year>'.$matches[1].'</built-year>'."\n";
					}
				}
				
				if(isset($form_data_shared['ready_quarter']) && isset($data_item['ready_quarter']) && $data_item['ready_quarter']!=''){
					$x=preg_replace('/[^0-9]/', '', $data_item['ready_quarter']);
					if(preg_match('/([1-4])/', $x, $matches)){
						$rs.='<ready-quarter>'.$matches[1].'</ready-quarter>'."\n";
					}
				}
				
				if(isset($form_data_shared['lift']) && isset($data_item['lift'])){
					if((int)$data_item['lift']==1){
						$rs.='<lift>1</lift>'."\n";
					}else{
						$rs.='<lift>0</lift>'."\n";
					}
				}
				
				if(isset($form_data_shared['rubbish_chute']) && isset($data_item['rubbish_chute'])){
					if((int)$data_item['rubbish_chute']==1){
						$rs.='<rubbish-chute>1</rubbish-chute>'."\n";
					}else{
						$rs.='<rubbish-chute>0</rubbish-chute>'."\n";
					}
				}
				
				if(isset($form_data_shared['elite']) && isset($data_item['elite'])){
					if((int)$data_item['elite']==1){
						$rs.='<is-elite>1</is-elite>'."\n";
					}else{
						//$rs.='<is-elite>0</is-elite>'."\n";
					}
				}
				
				if(isset($form_data_shared['parking']) && isset($data_item['parking'])){
					if((int)$data_item['parking']==1){
						$rs.='<parking>1</parking>'."\n";
					}else{
						$rs.='<parking>0</parking>'."\n";
					}
				}
				
    			if(isset($form_data_shared['alarm']) && isset($data_item['alarm'])){
					if((int)$data_item['alarm']==1){
						$rs.='<alarm>1</alarm>'."\n";
					}else{
						$rs.='<alarm>0</alarm>'."\n";
					}
				}
				
				if(isset($form_data_shared['ceiling_height']) && isset($data_item['ceiling_height'])){
					$x=preg_replace('/[^0-9.,]/','',$data_item['ceiling_height']);
					$x=str_replace(',', '.', $x);
					$x=floatval($x);
					if($x!=0){
						$rs.='<ceiling-height>'.$x.'</ceiling-height>';
					}
				}	
				
				
				/********************ЗАГОРОДНАЯ*************************/
					
				if(isset($form_data_shared['pmg']) && isset($data_item['pmg'])){
					if((int)$data_item['pmg']==1){
						$rs.='<pmg>1</pmg>'."\n";
					}else{
						$rs.='<pmg>0</pmg>'."\n";
					}
				}
				
				if(isset($form_data_shared['kitchen']) && isset($data_item['kitchen'])){
					if((int)$data_item['kitchen']==1){
						$rs.='<kitchen>1</kitchen>'."\n";
					}else{
						$rs.='<kitchen>0</kitchen>'."\n";
					}
				}
				
				if(isset($form_data_shared['pool']) && isset($data_item['pool'])){
					if((int)$data_item['pool']==1){
						$rs.='<pool>1</pool>'."\n";
					}else{
						$rs.='<pool>0</pool>'."\n";
					}
				}
				
				if(isset($form_data_shared['billiard']) && isset($data_item['billiard'])){
					if((int)$data_item['billiard']==1){
						$rs.='<billiard>1</billiard>'."\n";
					}else{
						$rs.='<billiard>0</billiard>'."\n";
					}
				}
				
				if(isset($form_data_shared['sauna']) && isset($data_item['sauna'])){
					if((int)$data_item['sauna']==1){
						$rs.='<sauna>1</sauna>'."\n";
					}else{
						$rs.='<sauna>0</sauna>'."\n";
					}
				}
				
				if(isset($form_data_shared['heating_supply']) && isset($data_item['heating_supply'])){
					if((int)$data_item['heating_supply']==1){
						$rs.='<heating-supply>1</heating-supply>'."\n";
					}else{
						$rs.='<heating-supply>0</heating-supply>'."\n";
					}
				}
				
				if(isset($form_data_shared['water_supply']) && isset($data_item['water_supply'])){
					if((int)$data_item['water_supply']==1){
						$rs.='<water-supply>1</water-supply>'."\n";
					}else{
						$rs.='<water-supply>0</water-supply>'."\n";
					}
				}
				
				if(isset($form_data_shared['sewerage_supply']) && isset($data_item['sewerage_supply'])){
					if((int)$data_item['sewerage_supply']==1){
						$rs.='<sewerage-supply>1</sewerage-supply>'."\n";
					}else{
						$rs.='<sewerage-supply>0</sewerage-supply>'."\n";
					}
				}
				
				if(isset($form_data_shared['electricity_supply']) && isset($data_item['electricity_supply'])){
					if((int)$data_item['electricity_supply']==1){
						$rs.='<electricity-supply>1</electricity-supply>'."\n";
					}else{
						$rs.='<electricity-supply>0</electricity-supply>'."\n";
					}
				}
				
				if(isset($form_data_shared['gas_supply']) && isset($data_item['gas_supply'])){
					if((int)$data_item['gas_supply']==1){
						$rs.='<gas-supply>1</gas-supply>'."\n";
					}else{
						$rs.='<gas-supply>0</gas-supply>'."\n";
					}
				}
				
				if(isset($form_data_shared['toilet']) && isset($data_item['toilet'])/* && $data_item['toilet']!=''*/){
					if($form_data_shared['toilet']['type']=='select_box' && intval($data_item['toilet'])!=0 && isset($form_data_shared['toilet']['select_data'][$data_item['toilet']])){
						$rs.='<toilet>'.self::symbolsClear($form_data_shared['toilet']['select_data'][$data_item['toilet']]).'</toilet>'."\n";
					}elseif($form_data_shared['toilet']['type']!='select_box' && $data_item['toilet']!=''){
						$rs.='<toilet>'.self::symbolsClear($data_item['toilet']).'</toilet>'."\n";
					}
					
				}
				
				if(isset($form_data_shared['shower']) && isset($data_item['shower']) && $data_item['shower']!=''){
					if($form_data_shared['shower']['type']=='select_box' && intval($data_item['shower'])!=0 && isset($form_data_shared['shower']['select_data'][$data_item['shower']])){
						$rs.='<shower>'.self::symbolsClear($form_data_shared['shower']['select_data'][$data_item['shower']]).'</shower>'."\n";
					}elseif($form_data_shared['shower']['type']!='select_box' && $data_item['shower']!=''){
						$rs.='<shower>'.self::symbolsClear($data_item['shower']).'</shower>'."\n";
					}
					//$rs.='<shower>'.self::symbolsClear($data_item['shower']).'</shower>'."\n";
				}
				/********************.ЗАГОРОДНАЯ*************************/
				$rs.='</offer>'."\n";
				$xml_text.=$rs;
	    	}
    	}
    	//echo 1;
    	//print_r($errors);
    	if(1==$this->getConfigValue('apps.yandexrealty.tofile')){
    		$f=fopen($this->export_file_storage.'/'.$this->export_file, 'w');
    		fwrite($f,$this->file_header.$this->file_start.$this->file_gen_date.$xml_text.$this->file_end);
    		fclose($f);
    		return file_get_contents($this->export_file_storage.'/'.$this->export_file);
    	}else{
    		return $this->file_header.$this->file_start.$this->file_gen_date.$xml_text.$this->file_end;
    	}
		
	}
    
	private function createAssocTable(){
		$DBC=DBC::getInstance();
		$query="CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_yandexrealty_assoc` (
		  `topic_id` int(11) NOT NULL,
		  `topic_name` varchar(255) NOT NULL,
		  `realty_type` tinyint(4) NOT NULL DEFAULT '0',
		`realty_category` tinyint(4) NOT NULL DEFAULT '0',
		  `operation_type` tinyint(4) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`topic_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$stmt=$DBC->query($query);
		if($stmt){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	protected function loadAssociations(){
		$associations=array();
		$DBC=DBC::getInstance();
		$query='SELECT topic_id, realty_type, realty_category, operation_type FROM '.DB_PREFIX.'_yandexrealty_assoc';
		$stmt=$DBC->query($query);
		if(!$stmt){
			return $associations;
		}
		while($ar=$DBC->fetch($stmt)){
			$associations[$ar['topic_id']]=$ar;
		}
		return $associations;
	}
	
    protected function collectData(){
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data_shared = $data_model->get_kvartira_model(false, true);
    	$form_data_shared=$form_data_shared['data'];
    	
    	$select=array();
    	$leftjoin=array();
    	
    	$select[]='dt.*';
    	
    	if($this->getConfigValue('currency_enable')==1){
    		$select[]='cur.name AS currency';
    		$leftjoin[]='LEFT JOIN '.DB_PREFIX.'_currency cur ON cur.currency_id=dt.currency_id';
    	}
    	
    	if(isset($form_data_shared['topic_id'])){
    		$select[]='tp.name AS topic';
    		$leftjoin[]='LEFT JOIN '.DB_PREFIX.'_topic tp ON tp.id=dt.topic_id';
    	}
    	
    	if(isset($form_data_shared['country_id'])){
    		$select[]='cr.name AS country';
    		$leftjoin[]='LEFT JOIN '.DB_PREFIX.'_country cr USING(country_id)';
    	}
    	
    	if(isset($form_data_shared['region_id'])){
    		$select[]='re.name AS region';
    		$leftjoin[]='LEFT JOIN '.DB_PREFIX.'_region re USING(region_id)';
    	}
    	
    	if(isset($form_data_shared['city_id'])){
    		$select[]='ct.name AS city';
    		$leftjoin[]='LEFT JOIN '.DB_PREFIX.'_city ct ON dt.city_id=ct.city_id';
    	}
    	
    	if(isset($form_data_shared['district_id'])){
    		$select[]='ds.name AS district';
    		$leftjoin[]='LEFT JOIN '.DB_PREFIX.'_district ds ON dt.district_id=ds.id';
    	}
    	
    	if(isset($form_data_shared['street_id'])){
    		$select[]='st.name AS street';
    		$leftjoin[]='LEFT JOIN '.DB_PREFIX.'_street st ON st.street_id=dt.street_id';
    	}
    	
    	if(isset($form_data_shared['metro_id'])){
    		$select[]='mt.name AS metro';
    		$leftjoin[]='LEFT JOIN '.DB_PREFIX.'_metro mt ON dt.metro_id=mt.metro_id';
    	}
    	
    	$basic_query='SELECT '.implode(',', $select).' FROM '.DB_PREFIX.'_data dt '.(!empty($leftjoin) ? implode(' ', $leftjoin) : '');
    	
    	
    	//echo $this->export_type;
    	
    	$data=array();
    	
    	$DBC=DBC::getInstance();
    	$where=array();
    	 
    	//Максимальный возраст объявления 6-месяцев
    	$max_days=(int)$this->getConfigValue('apps.yandexrealty.days_interval');
    	if($max_days==0){
    		$max_date = date('Y-m-d', 0 );
    	}else{
    		$max_date = date('Y-m-d', time()- $max_days*3600*24 );
    	}
    	
    	$where[]='dt.active=1';
    	$where[]='dt.date_added > \''.$max_date.'\'';
    	
    	if(''!==trim($this->getConfigValue('apps.yandexrealty.filtering_checkbox_name'))){
    		$where[]='dt.'.trim($this->getConfigValue('apps.yandexrealty.filtering_checkbox_name')).'=1';
    	}
    	
    	
    	$query=$basic_query.(!empty($where) ? ' WHERE '.implode(' AND ', $where) : '').' ORDER BY dt.date_added DESC';
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$data[]=$ar;
    		}
    	}
    	
    	
    	
    	
    	/*
    	 * Task Mode will moved to new app
    	 */
    	/*
    	$tasks=array();
    	if($this->export_type!=''){
    		$DBC=DBC::getInstance();
    		$query='SELECT * FROM '.DB_PREFIX.'_yandexrealty_task WHERE task_label=?';
    		$stmt=$DBC->query($query, array($this->export_type));
    		if($stmt){
    			while($ar=$DBC->fetch($stmt)){
    				$tasks[]=$ar;
    			}
    		}
    	}
    	
    	if(!empty($tasks)){
    		
    		$unions=array();
    		
    		foreach($tasks as $task){
    			parse_str($task['filter_params'], $filter_params);
    			$where=array();
    			$where[]='dt.active=1';
    			$sorts=array();
    			$limit=false;
    			if(count($filter_params)>0){
    				foreach ($filter_params as $filter_param_key=>$filter_param_value){
    					$where[]='dt.'.$filter_param_key.'='.$filter_param_value;
    				}
    			}
    			if(0!=(int)$task['max_limit_params']){
    				$limit=(int)$task['max_limit_params'];
    			}
    			if(0!=(int)$task['use_date_filtering']){
    				$max_days=(int)$this->getConfigValue('apps.yandexrealty.days_interval');
    				if($max_days==0){
    					$max_date = date('Y-m-d', 0 );
    				}else{
    					$max_date = date('Y-m-d', time()- $max_days*3600*24 );
    				}
    				$where[]='dt.date_added > '.$max_date;
    			}
    			if(''!=$task['order_params']){
    				$order_params=array();
    				preg_match_all('/([a-z0-9_]+):(asc|desc)/i', $task['order_params'], $order_params);
    				if(isset($order_params[1])){
    					foreach ($order_params[1] as $k=>$v){
    						$sorts[]='dt.'.$v.' '.($order_params[2][$k]=='asc' ? 'ASC' : 'DESC');
    					}
    				}
    				
    			}
    			$unions[]=array(
    				'where'=>$where,	
    				'sorts'=>$sorts,
    				'limit'=>$limit,
    			);
    		}
    		
    		foreach ($unions as $union){
    			$queries[]=$basic_query.(!empty($union['where']) ? ' WHERE '.implode(' AND ', $union['where']) : '').(!empty($union['sorts']) ? ' ORDER BY '.implode(', ', $union['sorts']) : '').($union['limit'] ? ' LIMIT '.$union['limit'] : '');
    		}
    		
    		$data=array();
    		
    		if(count($queries)>0){
    			foreach ($queries as $query){
    				$stmt=$DBC->query($query);
    				if($stmt){
    					while($ar=$DBC->fetch($stmt)){
    						$data[$ar['id']]=$ar;
    					}
    				}
    			}
    		}
    	}
    		
    	*/
    	return $data;
    }
    
    
	protected function formdate($time=NULL){
		if($time===NULL){
			$localtm=time();
		}else{
			$localtm=$time;
		}
		
		$off=date('Z',$localtm);
		$offset=$off/3600;
		if($off>=0){
			$gmtoff='+'.gmdate('H:i',$off);
		}else{
			$gmtoff=gmdate('H:i',$off);
		}
		
		$gmttime=$localtm-$off;
		$gmtdate=date('Y-m-d\TH:i:s',$gmttime);
		return $gmtdate.$gmtoff;
	}
	
	private function getRealtyTypeSelectbox($realty_type, $topic_id){
		$ret='';
		$ret.='<select name="data['.$topic_id.'][realty_type]" class="input-medium">';
		foreach($this->realty_types as $k=>$v){
			if($realty_type==$k){
				$ret.='<option value="'.$k.'" selected="selected">'.$v.'</option>';
			}else{
				$ret.='<option value="'.$k.'">'.$v.'</option>';
			}
		}
		$ret.='</select>';
		return $ret;
	}
	
	private function getRealtyCategorySelectbox($realty_category, $topic_id){
		$ret='';
		$ret.='<select name="data['.$topic_id.'][realty_category]" class="input-medium">';
		foreach($this->realty_categories as $k=>$v){
			if($realty_category==$k){
				$ret.='<option value="'.$k.'" selected="selected">'.$v.'</option>';
			}else{
				$ret.='<option value="'.$k.'">'.$v.'</option>';
			}
		}
		$ret.='</select>';
		return $ret;
	}
	
	private function getOperationTypeSelectbox($operation_type, $topic_id){
		$ret='';
		$ret.='<select name="data['.$topic_id.'][operation_type]" class="input-medium">';
		foreach($this->op_types as $k=>$v){
			if($operation_type==$k){
				$ret.='<option value="'.$k.'" selected="selected">'.$v.'</option>';
			}else{
				$ret.='<option value="'.$k.'">'.$v.'</option>';
			}
		}
		$ret.='</select>';
		return $ret;
	}
	
	function getTopMenu () {
	    $rs = '';
	    $rs.='<p>1. Для корректной работы приложения необходимо создать таблицу ассоциаций разделов</p>';
	    $rs.='<p>2. Если таблица уже создана, но в структуру добавлялись новуе пункты меню, необходимо выполнить \'Создать/дополнить таблицу ассоциаций\' для дополнения таблицы новыми пунктами раздела. Старые пункты таблицы останутся в исходном положении.</p>';
	    $rs.='<p>3. После переименования в структуре пунктов меню, необходимо удалить из таблицы ассоциаций переименованые пункты и выполнить \'Создать/дополнить таблицу ассоциаций\' для дополнения таблицы переименованными пунктами раздела. Старые пункты таблицы останутся в исходном положении.</p>';
		$rs .= '<p><a href="?action='.$this->action.'&do=create_table" class="btn btn-primary">Создать таблицу ассоциаций в БД</a>';
	    $rs .= ' <a href="?action='.$this->action.'&do=make_table" class="btn btn-primary">Создать/дополнить таблицу ассоциаций</a>';
		$rs .= ' <a href="?action='.$this->action.'&do=assoc_table_show" class="btn btn-primary">Редактировать таблицу ассоциаций</a>';
	    $rs .= ' <a href="?action='.$this->action.'&do=export" class="btn btn-primary">'.Multilanguage::_('EXPORT','yandexrealty').'</a></p>';
	    $rs .= ' <!--a href="?action='.$this->action.'&do=update_model" onclick="return confirmUpdate();" class="btn btn-warning"><i class="icon-white icon-exclamation-sign"></i> Добавить расширенные поля в модель data</a--!></p>';
	    $rs .= $this->getInfo();
	    $rs .= '';
	    return $rs;
	}
	
	static function symbolsClear($text){
		//echo $text.'=';
		$text=preg_replace('/[[:cntrl:]]/i', '', $text);
		$text=str_replace(array('"', '&', '>', '<', '\''), array('&quot;', '&amp;', '&gt;', '&lt;', '&apos;'), $text);
		//$string=htmlspecialchars($string);
		$text=Sitebill::iconv(SITE_ENCODING, 'utf-8', $text);
		//echo $string.'<br />';
		return $text;
		//return SiteBill::iconv(SITE_ENCODING, 'utf-8', str_replace(array('"', '&', '>', '<', '\''), array('&quot;', '&amp;', '&gt;', '&lt;', '&apos;'), $text));
	}
	
	static function currencyCheck($currency_string){
		$currencies=array('RUR', 'RUB', 'USD', 'EUR', 'UAH', 'BYR', 'KZT');
		if($currency_string!=''){
			if(in_array($currency_string, $currencies)){
				return $currency_string;
			}
			if(preg_match('/белорусский/i',$currency_string)){
				return 'BYR';
			}
			
			if(preg_match('/\$/i',$currency_string)){
				return 'USD';
			}
			
			if(preg_match('/\&euro\;/i',$currency_string)){
				return 'EUR';
			}
			if(preg_match('/р\./i',$currency_string)){
				return 'RUR';
			}	
			
			if(preg_match('/€/i',$currency_string)){
				return 'EUR';
			}
				
			
			if(preg_match('/рубль/i',$currency_string)){
				return 'RUR';
			}
			if(preg_match('/руб./i',$currency_string)){
				return 'RUR';
			}
			if(preg_match('/доллар/i',$currency_string)){
				return 'USD';
			}
			if(preg_match('/США/i',$currency_string)){
				return 'USD';
			}
			if(preg_match('/евро/i',$currency_string)){
				return 'EUR';
			}
			if(preg_match('/гривна/i',$currency_string)){
				return 'UAH';
			}
			if(preg_match('/грн/i',$currency_string)){
				return 'UAH';
			}
			if(preg_match('/теньге/i',$currency_string)){
				return 'KZT';
			}
			if(preg_match('/у.е./i',$currency_string)){
				return 'USD';
			}
			if(preg_match('/сум/i',$currency_string)){
				return 'UZS';
			}
		}
		return FALSE;
	}
	
	private function getCategoriesNameArray(){
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
		$Structure_Manager = new Structure_Manager();
		$cs=$Structure_Manager->loadCategoryStructure($this->getConfigValue('use_topic_publish_status'));
		$names=array();
		foreach($cs['catalog'] as $k=>$v){
			$names[$v['id']]=$this->get_category_breadcrumbs_string( array('topic_id'=>$v['id']), $cs );
		}
		return $names;
	}
	
	function get_yandex_model () {
		//Тут создаем массив для полей из дополнительных секций яндекса
		//Все поля описаны тут http://help.yandex.ru/webmaster/realty/requirements.xml
		$form_data['data']['expire_date']['name'] = 'expire_date';
		$form_data['data']['expire_date']['title'] = 'Дата и время, до которой объявление актуально';
		$form_data['data']['expire_date']['value'] = 0;
		$form_data['data']['expire_date']['length'] = 40;
		$form_data['data']['expire_date']['type'] = 'dtdatetime';
		$form_data['data']['expire_date']['required'] = 'off';
		$form_data['data']['expire_date']['unique'] = 'off';
		 
		$form_data['data']['payed_adv']['name'] = 'payed_adv';
		$form_data['data']['payed_adv']['title'] = 'Оплаченное объявление';
		$form_data['data']['payed_adv']['value'] = 0;
		$form_data['data']['payed_adv']['type'] = 'checkbox';
	
		$form_data['data']['manually_added']['name'] = 'manually_added';
		$form_data['data']['manually_added']['title'] = 'Объявление добавлено вручную';
		$form_data['data']['manually_added']['value'] = 0;
		$form_data['data']['manually_added']['type'] = 'checkbox';
		 
		$form_data['data']['direction']['name'] = 'direction';
		$form_data['data']['direction']['title'] = 'Шоссе (только для Москвы)';
		$form_data['data']['direction']['value'] = '';
		$form_data['data']['direction']['type'] = 'safe_string';
	
		$form_data['data']['distance']['name'] = 'distance';
		$form_data['data']['distance']['title'] = 'Расстояние по шоссе до МКАД (указывается в км)';
		$form_data['data']['distance']['value'] = '';
		$form_data['data']['distance']['type'] = 'safe_string';
		 
		$form_data['data']['time_on_transport']['name'] = 'time_on_transport';
		$form_data['data']['time_on_transport']['title'] = 'Время до метро в минутах на транспорте';
		$form_data['data']['time_on_transport']['value'] = '';
		$form_data['data']['time_on_transport']['type'] = 'safe_string';
		 
		$form_data['data']['time_on_foot']['name'] = 'time_on_foot';
		$form_data['data']['time_on_foot']['title'] = 'Время до метро в минутах пешком';
		$form_data['data']['time_on_foot']['value'] = '';
		$form_data['data']['time_on_foot']['type'] = 'safe_string';
	
		$form_data['data']['railway_station']['name'] = 'railway_station';
		$form_data['data']['railway_station']['title'] = 'Ближайшая ж/д станция (для загородной недвижимости)';
		$form_data['data']['railway_station']['value'] = '';
		$form_data['data']['railway_station']['type'] = 'safe_string';
	
		$form_data['data']['not_for_agents']['name'] = 'not_for_agents';
		$form_data['data']['not_for_agents']['title'] = 'Просьба агентам не звонить';
		$form_data['data']['not_for_agents']['value'] = 0;
		$form_data['data']['not_for_agents']['type'] = 'checkbox';
		 
		$form_data['data']['haggle']['name'] = 'haggle';
		$form_data['data']['haggle']['title'] = 'Торг';
		$form_data['data']['haggle']['value'] = 0;
		$form_data['data']['haggle']['type'] = 'checkbox';
	
		$form_data['data']['mortgage']['name'] = 'mortgage';
		$form_data['data']['mortgage']['title'] = 'Ипотека';
		$form_data['data']['mortgage']['value'] = 0;
		$form_data['data']['mortgage']['type'] = 'checkbox';
	
		$form_data['data']['prepayment']['name'] = 'prepayment';
		$form_data['data']['prepayment']['title'] = 'Предоплата (указывается числовое значение в процентах без знака %)';
		$form_data['data']['prepayment']['value'] = '';
		$form_data['data']['prepayment']['type'] = 'safe_string';
		$form_data['data']['prepayment']['parameters']['rules'] = 'Type:int,Min:0,Max:100';
	
		$form_data['data']['rent_pledge']['name'] = 'rent_pledge';
		$form_data['data']['rent_pledge']['title'] = 'Залог';
		$form_data['data']['rent_pledge']['value'] = 0;
		$form_data['data']['rent_pledge']['type'] = 'checkbox';
	
		$form_data['data']['agent_fee']['name'] = 'agent_fee';
		$form_data['data']['agent_fee']['title'] = 'Комиссия арендатора (указывается числовое значение в процентах без знака %)';
		$form_data['data']['agent_fee']['value'] = '';
		$form_data['data']['agent_fee']['type'] = 'safe_string';
		$form_data['data']['agent_fee']['parameters']['rules'] = 'Type:int,Min:0,Max:1000';
		 
		$form_data['data']['with_pets']['name'] = 'with_pets';
		$form_data['data']['with_pets']['title'] = 'Можно ли с животными (для аренды)';
		$form_data['data']['with_pets']['value'] = 0;
		$form_data['data']['with_pets']['type'] = 'checkbox';
	
		$form_data['data']['with_children']['name'] = 'with_children';
		$form_data['data']['with_children']['title'] = 'Можно ли с детьми (для аренды)';
		$form_data['data']['with_children']['value'] = 1;
		$form_data['data']['with_children']['type'] = 'checkbox';
		 
		$form_data['data']['renovation']['name'] = 'renovation';
		$form_data['data']['renovation']['title'] = 'Ремонт';
		$form_data['data']['renovation']['value'] = '';
		$form_data['data']['renovation']['type'] = 'select_box';
		$form_data['data']['renovation']['select_data'] = array('0' => 'не выбрано', '1' => 'евро', '2' => 'дизайнерский', '3' => 'косметический' );
	
		//Для типа участка
		$form_data['data']['lot_type']['name'] = 'lot_type';
		$form_data['data']['lot_type']['title'] = 'Тип участка';
		$form_data['data']['lot_type']['value'] = '';
		$form_data['data']['lot_type']['type'] = 'select_box';
		$form_data['data']['lot_type']['select_data'] = array('0' => 'не выбрано', '1' => 'ИЖC', '2' => 'садоводство');
	
		$form_data['data']['lot_area']['name'] = 'lot_area';
		$form_data['data']['lot_area']['title'] = 'Площадь участка';
		$form_data['data']['lot_area']['value'] = '';
		$form_data['data']['lot_area']['type'] = 'safe_string';
		$form_data['data']['lot_area']['parameters']['rules'] = 'Type:int,Min:0,Max:10000';
		 
		//Для жилого
		$form_data['data']['new_flat']['name'] = 'new_flat';
		$form_data['data']['new_flat']['title'] = 'Новостройка';
		$form_data['data']['new_flat']['value'] = 0;
		$form_data['data']['new_flat']['type'] = 'checkbox';
	
		$form_data['data']['rooms']['name'] = 'rooms';
		$form_data['data']['rooms']['title'] = 'Общее количество комнат в квартире';
		$form_data['data']['rooms']['value'] = 0;
		$form_data['data']['rooms']['type'] = 'safe_string';
		$form_data['data']['rooms']['parameters']['rules'] = 'Type:int,Min:0,Max:50';
	
		$form_data['data']['rooms_offered']['name'] = 'rooms_offered';
		$form_data['data']['rooms_offered']['title'] = 'Количество комнат, участвующих в сделке (0 - все)';
		$form_data['data']['rooms_offered']['value'] = 0;
		$form_data['data']['rooms_offered']['type'] = 'safe_string';
		$form_data['data']['rooms_offered']['parameters']['rules'] = 'Type:int,Min:0,Max:50';
	
		$form_data['data']['open_plan']['name'] = 'open_plan';
		$form_data['data']['open_plan']['title'] = 'Свободная планировка';
		$form_data['data']['open_plan']['value'] = 0;
		$form_data['data']['open_plan']['type'] = 'checkbox';
	
		$form_data['data']['rooms_type']['name'] = 'rooms_type';
		$form_data['data']['rooms_type']['title'] = 'Тип комнат';
		$form_data['data']['rooms_type']['value'] = '';
		$form_data['data']['rooms_type']['type'] = 'select_box';
		$form_data['data']['rooms_type']['select_data'] = array('0' => 'не выбрано', '1' => 'смежные', '2' => 'раздельные');
	
		$form_data['data']['internet']['name'] = 'internet';
		$form_data['data']['internet']['title'] = 'Наличие интернета';
		$form_data['data']['internet']['value'] = 0;
		$form_data['data']['internet']['type'] = 'checkbox';
	
		$form_data['data']['room_furniture']['name'] = 'room_furniture';
		$form_data['data']['room_furniture']['title'] = 'Наличие мебели';
		$form_data['data']['room_furniture']['value'] = 0;
		$form_data['data']['room_furniture']['type'] = 'checkbox';
		 
		$form_data['data']['kitchen_furniture']['name'] = 'kitchen_furniture';
		$form_data['data']['kitchen_furniture']['title'] = 'Наличие мебели на кухне';
		$form_data['data']['kitchen_furniture']['value'] = 0;
		$form_data['data']['kitchen_furniture']['type'] = 'checkbox';
	
		$form_data['data']['television']['name'] = 'television';
		$form_data['data']['television']['title'] = 'Наличие телевизора';
		$form_data['data']['television']['value'] = 0;
		$form_data['data']['television']['type'] = 'checkbox';
	
		$form_data['data']['washing_machine']['name'] = 'washing_machine';
		$form_data['data']['washing_machine']['title'] = 'Наличие стиральной машины';
		$form_data['data']['washing_machine']['value'] = 0;
		$form_data['data']['washing_machine']['type'] = 'checkbox';
	
		$form_data['data']['refrigerator']['name'] = 'refrigerator';
		$form_data['data']['refrigerator']['title'] = 'Наличие холодильника';
		$form_data['data']['refrigerator']['value'] = 0;
		$form_data['data']['refrigerator']['type'] = 'checkbox';
	
		$form_data['data']['balcony']['name'] = 'balcony';
		$form_data['data']['balcony']['title'] = 'Тип балкона';
		$form_data['data']['balcony']['value'] = '';
		$form_data['data']['balcony']['type'] = 'select_box';
		$form_data['data']['balcony']['select_data'] = array('0' => 'не выбрано', '1' => 'балкон', '2' => 'лоджия', '3' => '2 балкона', '4' => '2 лоджии');
	
		$form_data['data']['bathroom_unit']['name'] = 'bathroom_unit';
		$form_data['data']['bathroom_unit']['title'] = 'Тип санузла';
		$form_data['data']['bathroom_unit']['value'] = '';
		$form_data['data']['bathroom_unit']['type'] = 'select_box';
		$form_data['data']['bathroom_unit']['select_data'] = array('0' => 'не выбрано', '1' => 'совмещенный', '2' => 'раздельный', '3' => '2');
		 
		$form_data['data']['floor_covering']['name'] = 'floor_covering';
		$form_data['data']['floor_covering']['title'] = 'Покрытие пола';
		$form_data['data']['floor_covering']['value'] = '';
		$form_data['data']['floor_covering']['type'] = 'select_box';
		$form_data['data']['floor_covering']['select_data'] = array('0' => 'не выбрано', '1' => 'паркет', '2' => 'ламинат', '3' => 'ковролин', '4' => 'колинолеумвролин');
	
		$form_data['data']['window_view']['name'] = 'window_view';
		$form_data['data']['window_view']['title'] = 'Вид из окон';
		$form_data['data']['window_view']['value'] = '';
		$form_data['data']['window_view']['type'] = 'select_box';
		$form_data['data']['window_view']['select_data'] = array('0' => 'не выбрано', '1' => 'во двор', '2' => 'на улицу');
		 
		$form_data['data']['building_name']['name'] = 'building_name';
		$form_data['data']['building_name']['title'] = 'Название ЖК (для новостроек)';
		$form_data['data']['building_name']['value'] = '';
		$form_data['data']['building_name']['type'] = 'safe_string';
		 
		$form_data['data']['building_type']['name'] = 'building_type';
		$form_data['data']['building_type']['title'] = 'Тип дома';
		$form_data['data']['building_type']['value'] = '';
		$form_data['data']['building_type']['type'] = 'select_box';
		$form_data['data']['building_type']['select_data'] = array('0' => 'не выбрано', '1' => 'кирпичный', '2' => 'монолит', '3' => 'панельный');
	
		$form_data['data']['building_series']['name'] = 'building_series';
		$form_data['data']['building_series']['title'] = 'Серия дома';
		$form_data['data']['building_series']['value'] = '';
		$form_data['data']['building_series']['type'] = 'safe_string';
		 
		$form_data['data']['building_state']['name'] = 'building_state';
		$form_data['data']['building_state']['title'] = 'Стадия строительства дома (для новостроек)';
		$form_data['data']['building_state']['value'] = '';
		$form_data['data']['building_state']['type'] = 'select_box';
		$form_data['data']['building_state']['select_data'] = array('' => 'не выбрано', 'unfinished' => 'строится', 'built' => 'дом построен, но не сдан', 'hand-over' => 'сдан в эксплуатацию');
		 
		 
		$form_data['data']['built_year']['name'] = 'built_year';
		$form_data['data']['built_year']['title'] = 'Год постройки или сдачи';
		$form_data['data']['built_year']['value'] = '';
		$form_data['data']['built_year']['type'] = 'safe_string';
		$form_data['data']['built_year']['parameters']['rules'] = 'Type:int,Min:0,Max:2500';
		 
		$form_data['data']['ready_quarter']['name'] = 'ready_quarter';
		$form_data['data']['ready_quarter']['title'] = 'Квартал сдачи дома';
		$form_data['data']['ready_quarter']['value'] = '';
		$form_data['data']['ready_quarter']['type'] = 'safe_string';
		$form_data['data']['ready_quarter']['parameters']['rules'] = 'Type:int,Min:0,Max:4';
		 
		$form_data['data']['lift']['name'] = 'lift';
		$form_data['data']['lift']['title'] = 'Наличие лифта';
		$form_data['data']['lift']['value'] = 0;
		$form_data['data']['lift']['type'] = 'checkbox';
		$form_data['data']['lift']['parameters']['rules'] = '';
		
		$form_data['data']['rubbish_chute']['name'] = 'rubbish_chute';
		$form_data['data']['rubbish_chute']['title'] = 'Наличие мусоропровода';
		$form_data['data']['rubbish_chute']['value'] = 0;
		$form_data['data']['rubbish_chute']['type'] = 'checkbox';
		$form_data['data']['rubbish_chute']['parameters']['rules'] = '';
		
		$form_data['data']['is_elite']['name'] = 'is_elite';
		$form_data['data']['is_elite']['title'] = 'Элитность';
		$form_data['data']['is_elite']['value'] = 0;
		$form_data['data']['is_elite']['type'] = 'checkbox';
		$form_data['data']['is_elite']['parameters']['rules'] = '';
		
		$form_data['data']['parking']['name'] = 'parking';
		$form_data['data']['parking']['title'] = 'Наличие парковки';
		$form_data['data']['parking']['value'] = 0;
		$form_data['data']['parking']['type'] = 'checkbox';
		$form_data['data']['parking']['parameters']['rules'] = '';
		
		$form_data['data']['alarm']['name'] = 'alarm';
		$form_data['data']['alarm']['title'] = 'Наличие охраны/сигнализации';
		$form_data['data']['alarm']['value'] = 0;
		$form_data['data']['alarm']['type'] = 'checkbox';
		$form_data['data']['alarm']['parameters']['rules'] = '';
		
		$form_data['data']['ceiling_height']['name'] = 'ceiling_height';
		$form_data['data']['ceiling_height']['title'] = 'Высота потолков';
		$form_data['data']['ceiling_height']['value'] = '';
		$form_data['data']['ceiling_height']['type'] = 'safe_string';
		$form_data['data']['ceiling_height']['parameters']['rules'] = 'Type:int,Min:0,Max:20';
	
		$form_data['data']['pmg']['name'] = 'pmg';
		$form_data['data']['pmg']['title'] = 'Возможность ПМЖ';
		$form_data['data']['pmg']['value'] = 0;
		$form_data['data']['pmg']['type'] = 'checkbox';
		 
		$form_data['data']['kitchen']['name'] = 'kitchen';
		$form_data['data']['kitchen']['title'] = 'Наличие кухни';
		$form_data['data']['kitchen']['value'] = 0;
		$form_data['data']['kitchen']['type'] = 'checkbox';
		 
		$form_data['data']['pool']['name'] = 'pool';
		$form_data['data']['pool']['title'] = 'Наличие бассейна';
		$form_data['data']['pool']['value'] = 0;
		$form_data['data']['pool']['type'] = 'checkbox';
		 
		$form_data['data']['billiard']['name'] = 'billiard';
		$form_data['data']['billiard']['title'] = 'Наличие бильярда';
		$form_data['data']['billiard']['value'] = 0;
		$form_data['data']['billiard']['type'] = 'checkbox';
		 
		$form_data['data']['sauna']['name'] = 'sauna';
		$form_data['data']['sauna']['title'] = 'Наличие сауны/бани';
		$form_data['data']['sauna']['value'] = 0;
		$form_data['data']['sauna']['type'] = 'checkbox';
		 
		$form_data['data']['heating_supply']['name'] = 'heating_supply';
		$form_data['data']['heating_supply']['title'] = 'Наличие отопления';
		$form_data['data']['heating_supply']['value'] = 0;
		$form_data['data']['heating_supply']['type'] = 'checkbox';
		 
		$form_data['data']['water_supply']['name'] = 'water_supply';
		$form_data['data']['water_supply']['title'] = 'Наличие водопровода';
		$form_data['data']['water_supply']['value'] = 0;
		$form_data['data']['water_supply']['type'] = 'checkbox';
		 
		$form_data['data']['sewerage_supply']['name'] = 'sewerage_supply';
		$form_data['data']['sewerage_supply']['title'] = 'Наличие канализации';
		$form_data['data']['sewerage_supply']['value'] = 0;
		$form_data['data']['sewerage_supply']['type'] = 'checkbox';
		 
		$form_data['data']['electricity_supply']['name'] = 'electricity_supply';
		$form_data['data']['electricity_supply']['title'] = 'Наличие электроснабжения';
		$form_data['data']['electricity_supply']['value'] = 0;
		$form_data['data']['electricity_supply']['type'] = 'checkbox';
		 
		$form_data['data']['gas_supply']['name'] = 'gas_supply';
		$form_data['data']['gas_supply']['title'] = 'Подключение к газовым сетям';
		$form_data['data']['gas_supply']['value'] = 0;
		$form_data['data']['gas_supply']['type'] = 'checkbox';
		 
		$form_data['data']['toilet']['name'] = 'toilet';
		$form_data['data']['toilet']['title'] = 'Расположение туалета';
		$form_data['data']['toilet']['value'] = '';
		$form_data['data']['toilet']['type'] = 'select_box';
		$form_data['data']['toilet']['select_data'] = array('' => 'не выбрано', '1' => 'в доме', '2' => 'на улице');
	
	
		$form_data['data']['shower']['name'] = 'shower';
		$form_data['data']['shower']['title'] = 'Расположение душа';
		$form_data['data']['shower']['value'] = '';
		$form_data['data']['shower']['type'] = 'select_box';
		$form_data['data']['shower']['select_data'] = array('' => 'не выбрано', '1' => 'в доме', '2' => 'на улице');
		 
		return $form_data;	 
	
	}
	
	protected function setExportType(){
		$this->export_type=mb_strtolower($this->getRequestValue('type'), SITE_ENCODING);
	}
	
	protected function setExportMode(){
		$this->export_mode='YANDEX';
	}
	
}