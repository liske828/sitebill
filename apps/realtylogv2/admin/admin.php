<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * realtylogv2 admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class realtylogv2_admin extends Object_Manager {
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
        //$this->table_name = 'realtylogv2';
        $this->action = 'realtylogv2';
        //$this->primary_key = 'realtylog_id';
        //require_once(SITEBILL_DOCUMENT_ROOT.'/apps/realtylogv2/admin/realtylogv2_model.php');
		//$this->data_model_object=new Realtylogv2_Model();
		//$this->data_model=$this->data_model_object->get_model();
        
		require_once (SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
		$config_admin = new config_admin();
		 
		if ( !$config_admin->check_config_item('apps.realtylogv2.enable') ) {
			$this->install();
			$config_admin->addParamToConfig('apps.realtylogv2.enable','0','Включить приложение Realty Logger v2');
		}
		if ( !$config_admin->check_config_item('apps.realtylogv2.namespace') ) {
			$config_admin->addParamToConfig('apps.realtylogv2.namespace','realtylogs','Пространство адресов');
		}
		if ( !$config_admin->check_config_item('apps.realtylogv2.per_page') ) {
			$config_admin->addParamToConfig('apps.realtylogv2.per_page','10','Кол-во записей на страницу');
		}
		if ( !$config_admin->check_config_item('apps.realtylogv2.restore_notactive') ) {
			$config_admin->addParamToConfig('apps.realtylogv2.restore_notactive','0','Восстанавливать в неактивном состоянии');
		}
		if ( !$config_admin->check_config_item('apps.realtylogv2.refresh_adddate') ) {
			$config_admin->addParamToConfig('apps.realtylogv2.refresh_adddate','0','Обновлять дату добавления на текущую');
		}
		if ( !$config_admin->check_config_item('apps.realtylogv2.classic_view') ) {
			$config_admin->addParamToConfig('apps.realtylogv2.classic_view','0','Классический вид');
		}
		$this->initModel();
	}
    
    private function initModel(){
    	$this->table_name = 'realtylogv2';
    	$this->primary_key = 'realtylog_id';
    	$form_data = array();
    	 
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php') ){
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
    		$ATH=new Admin_Table_Helper();
    		$form_data=$ATH->load_model($this->table_name, false);
    		if(empty($form_data)){
    			$form_data = array();
    			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/realtylogv2/admin/realtylogv2_model.php');
    			$Object=new Realtylogv2_Model();
    			$form_data = $Object->get_model($ajax);
    			require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';
    			require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php';
    			$TA=new table_admin();
    			$TA->create_table_and_columns($form_data, $this->table_name);
    			$form_data = array();
    			$form_data=$ATH->load_model($this->table_name, false);
    			$ATH->create_table($this->table_name);
    			$ATH->update_table($this->table_name);
    		}
    		$form_data = $ATH->add_ajax($form_data);
    	}else{
    		$form_data = $Object->get_model(true);
    	}
    	 
    	$this->data_model=$form_data;
    }
    
    
    public function _preload(){
    	if ( $this->getConfigValue('apps.realtylogv2.enable') ) {
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
    		$Login = new Login();
	    	$uid=$Login->getSessionUserId();
	    	//echo $uid;
			if($uid!=0){
				$query='SELECT COUNT(realtylog_id) AS total FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE editor_id='.$uid.' AND action=\'delete\'';
				$DBC=DBC::getInstance();
				$stmt=$DBC->query($query);
				if($stmt){
					$ar=$DBC->fetch($stmt);
					$this->template->assert('trash_count',(int)$ar['total']);
				}
			}
    		$this->template->assert('realtylogv2_on',1);
    		$this->template->assert('realtylogv2_namespace',$this->getConfigValue('apps.realtylogv2.namespace'));
    	}else{
    		$this->template->assert('realtylogv2_on',0);
    	}
    	 
    }
    
    /*function main(){
    	if($this->getConfigValue('apps.realtylogv2.enable')){
    		if('showlog'==$this->getRequestValue('a')){
    			$this->showLog($this->getRequestValue('logid'));
    		}
    		//$this->install();
    		$rs.=parent::main();
    	}else{
    		$rs='Приложение не активировано, перейдите в Менеджер настроек и включите его';
    	}
    	return $rs;
    	
    }*/
    
    
    protected function _fastrAction () {
    	$DBC=DBC::getInstance();
    	$logs=array();
    	$query='SELECT realtylog_id, id FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE `action`=?';
    	$stmt=$DBC->query($query, array('delete'));
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$logs[]=$ar;
    		}
    	}
    
    	if(!empty($logs)){
    		foreach($logs as $log){
    			$r=$this->restoreLog($log['realtylog_id']);
    			if(false!==$r){
    				$query='UPDATE '.DB_PREFIX.'_data SET archived=1 WHERE `id`=?';
    				$stmt=$DBC->query($query, array($log['id']));
    					
    				$query='DELETE FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE realtylog_id=?';
    				$stmt=$DBC->query($query, array($log['realtylog_id']));
    			}
    		}
    	}
    	print_r($logs);
    	return 's';
    }
    
    protected function _remove_logAction () {
    	$log_id=intval($this->getRequestValue('log_id'));
    	$this->delete_data($this->table_name, $this->primary_key, $log_id);
    	return $this->grid();
    }
    
    protected function _restoreAction () {
    	$this->restoreLog($this->getRequestValue('id'));
    	if ( !$this->getError() ) {
    		global $smarty;
    		$smarty->assign('success', 'Запись восстановлена успешно');
    	}
    	return $this->grid();
    }
    
    protected function _viewAction () {
    	$log_id=intval($this->getRequestValue('id'));
    	
    	
    	
    	$DBC=DBC::getInstance();
    	$query='SELECT `log_data` FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE realtylog_id=?';
    	$stmt=$DBC->query($query, array($log_id));
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    		$ld=unserialize($ar['log_data']);
    		
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    		$data_model = new Data_Model();
    		$model = $data_model->get_kvartira_model(false, true);
    		 
    		$model=$data_model->init_model_data_from_var($ld, $ld['id'], $model['data'], true);
    		
    		/*require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/view.php');
    		$table_view = new Table_View();
    		$order_table = '';
    		$order_table .= '<table border="1" cellpadding="2" cellspacing="2" style="border: 1px solid gray;">';
    		$order_table .= $table_view->compile_view($model);
    		$order_table .= '</table>';
    		return $order_table;*/
    		//return $this->get_show_form($model, 'new', 0, 'Восстановить', $action = SITEBILL_MAIN_URL.'/admin/?action=realtylogv2&do=restore&id='.$log_id );
    		return $this->get_show_form($model, 'Восстановить', $action = SITEBILL_MAIN_URL.'/admin/?action=realtylogv2&do=restore&id='.$log_id );
    	}
    	
    			
    	return $this->grid();
    }
    
    function get_show_form ( $form_data=array(), $button_title = '', $action = 'index.php' ) {
    	global $smarty;
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
    	$form_generator = new Form_Generator();
    		
    		
    	$rs .= $this->get_ajax_functions();
    	if(1==$this->getConfigValue('apps.geodata.enable')){
    		$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/geodata/js/geodata.js"></script>';
    	}
    	$rs .= '<form method="post" class="form-horizontal">';
    		
    	if ( $this->getError() ) {
    		$smarty->assign('form_error',$form_generator->get_error_message_row($this->GetErrorMessage()));
    	}
    		
    	$el = $form_generator->compile_form_elements($form_data);
    
    	
    	$el['form_header']=$rs;
    	$el['form_footer']='</form>';
    		
    	
    
    
    
    	$smarty->assign('form_elements',$el);
    	$tpl_name=$this->getAdminTplFolder().'/data_form.tpl';
    	return $smarty->fetch($tpl_name);
    }
    
    function grid(){
    	global $smarty;
    	
    	
    	
    	$classic_view=intval($this->getConfigValue('apps.realtylogv2.classic_view'));
    	
    	$smarty->assign('classic_view', $classic_view);
    	
    	if(1===$classic_view){
    		
    		$page=intval($this->getRequestValue('page'));
    		if($page==0){
    			$page=1;
    		}
    		$type=$this->getRequestValue('type');
    		if(!in_array($type, array('delete', 'edit', 'new'))){
    			$type='';
    		}
    		 
    		$ids=intval($this->getRequestValue('ids'));
    		if($ids!==0){
    			$pager_p[]='ids='.$ids;
    		}
    		 
    		$pager_p=array();
    		if($type!=''){
    			$pager_p[]='type='.$type;
    		}
    		
    		$smarty->assign('type', $type);
    		
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/grid.php');
    		$common_grid = new Common_Grid($this);
    		 
    		// $common_grid->set_grid_table($this->table_name);
    		 
    		 
    		$common_grid->add_grid_item($this->primary_key);
    		$common_grid->add_grid_item('editor_id');
    		$common_grid->add_grid_item('id');
    		$common_grid->add_grid_item('action');
    		$common_grid->add_grid_item('log_date');
    		 
    		
    		$common_grid->setPagerParams(array('action'=>$this->action,'page'=>$this->getRequestValue('page'),'per_page'=>$this->getConfigValue('common_per_page'), 'type'=>$type, 'ids'=>$ids));
    		 
	    	if(!in_array($type, array('delete', 'edit', 'new'))){
	    		$query='SELECT * FROM '.DB_PREFIX.'_'.$this->table_name.($ids!=0 ? ' WHERE id='.$ids : '').' ORDER BY log_date DESC';
	    	}else{
	    		$query='SELECT * FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE `action`=\''.$type.'\''.($ids!=0 ? ' AND id='.$ids : '').' ORDER BY log_date DESC';
	    	}
    	
    		
    		$common_grid->set_grid_query($query);
    		//$rs = $common_grid->construct_grid();
    		
    		$smarty->assign('data_array', $common_grid->construct_grid_array());
    		$smarty->assign('data_pager', $common_grid->getPager());
    		
    	}else{
    		$page=intval($this->getRequestValue('page'));
    		if($page==0){
    			$page=1;
    		}
    		$type=$this->getRequestValue('type');
    		if(!in_array($type, array('delete', 'edit', 'new'))){
    			$type='';
    		}else{
    			$pager_p[]='type='.$type;
    		}
    		 
    		$ids=intval($this->getRequestValue('ids'));
    		if($ids!==0){
    			$pager_p[]='ids='.$ids;
    		}
    		 
    		
    		 
    		$per_page=intval($this->getConfigValue('apps.realtylogv2.per_page'));
    		$data=$this->getLogsList($page, $per_page, $type, $ids);
    		//print_r($data['total']);
    		$smarty->assign('items', $data['rows']);
    		$pager=array();
    		if($data['total']>0 && $data['total']>$per_page){
    			$showed=$page*$per_page;
    			$total_pages=ceil($data['total']/$per_page);
    			$pager[]='<a href="'.SITEBILL_MAIN_URL.'/admin/?action=realtylogv2&page=1'.(!empty($pager_p) ? '&'.implode('&', $pager_p) : '').'"><<</a>';
    			if($page!=1){
    				$pager[]='<a href="'.SITEBILL_MAIN_URL.'/admin/?action=realtylogv2&page='.($page-1).(!empty($pager_p) ? '&'.implode('&', $pager_p) : '').'"><</a>';
    			}
    			if($showed>=$data['total']){
    				$pager[]='<a href="'.SITEBILL_MAIN_URL.'/admin/?action=realtylogv2&page='.($page-1).(!empty($pager_p) ? '&'.implode('&', $pager_p) : '').'">'.($page-1).'</a>';
    				$pager[]='<a href="#">'.$page.' из '.$total_pages.'</a>';
    			}elseif($page==1){
    				$pager[]='<a href="#">'.$page.' из '.$total_pages.'</a>';
    				$pager[]='<a href="'.SITEBILL_MAIN_URL.'/admin/?action=realtylogv2&page='.($page+1).(!empty($pager_p) ? '&'.implode('&', $pager_p) : '').'">'.($page+1).'</a>';
    			}else{
    				$pager[]='<a href="'.SITEBILL_MAIN_URL.'/admin/?action=realtylogv2&page='.($page-1).(!empty($pager_p) ? '&'.implode('&', $pager_p) : '').'">'.($page-1).'</a>';
    				$pager[]='<a href="#">'.$page.' из '.$total_pages.'</a>';
    				$pager[]='<a href="'.SITEBILL_MAIN_URL.'/admin/?action=realtylogv2&page='.($page+1).(!empty($pager_p) ? '&'.implode('&', $pager_p) : '').'">'.($page+1).'</a>';
    			}
    			if($page<$total_pages){
    				$pager[]='<a href="'.SITEBILL_MAIN_URL.'/admin/?action=realtylogv2&page='.($page+1).(!empty($pager_p) ? '&'.implode('&', $pager_p) : '').'">></a>';
    			}
    			$pager[]='<a href="'.SITEBILL_MAIN_URL.'/admin/?action=realtylogv2&page='.$total_pages.(!empty($pager_p) ? '&'.implode('&', $pager_p) : '').'">>></a>';
    		
    		}
    		if ( $this->getError() ) {
    			$smarty->assign('error', $this->GetErrorMessage());
    		}
    		$smarty->assign('total_pages', $total_pages);
    		$smarty->assign('type', $type);
    		$smarty->assign('pager', $pager);
    		$smarty->assign('data_array', $data['data_array']);
    	}
    	
    	
    	
    	return $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/realtylogv2/admin/template/list.tpl');
    	/*require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($this);
        
       // $common_grid->set_grid_table($this->table_name);
        
        
        $common_grid->add_grid_item($this->primary_key);
        $common_grid->add_grid_item('editor_id');
        $common_grid->add_grid_item('id');
        $common_grid->add_grid_item('action');
        $common_grid->add_grid_item('log_date');
        
       // $common_grid->set_conditions(array('action'=>'delete'));
        
        //$common_grid->add_grid_control('edit');
        //$common_grid->add_grid_control('delete');
        
        $common_grid->setPagerParams(array('action'=>$this->action,'page'=>$this->getRequestValue('page'),'per_page'=>$this->getConfigValue('common_per_page')));
        
        $common_grid->set_grid_query("select * from ".DB_PREFIX."_".$this->table_name." order by log_date desc");
        $rs = $common_grid->construct_grid();
        return $rs;
        */
       
        
    }
    
    protected function getLogsList($page=1, $per_page=10, $type='', $ids=0){
    	$ret=array();
    	$total=0;
    	$DBC=DBC::getInstance();
    	if($ids!=0){
    		//$s='id=?'
    	}
    	
    	$rows=array();
    	
    	if(!in_array($type, array('delete', 'edit', 'new'))){
    		$query='SELECT SQL_CALC_FOUND_ROWS `realtylog_id`, `id`, `user_id`, `log_date`, `action`, `log_data` FROM '.DB_PREFIX.'_'.$this->table_name.($ids!=0 ? ' WHERE id=?' : '').' ORDER BY log_date DESC LIMIT '.(($page-1)*$per_page).', '.$per_page;
    		if($ids!=0){
    			$stmt=$DBC->query($query, array($ids));
    		}else{
    			$stmt=$DBC->query($query);
    		}
    		
    	}else{
    		$query='SELECT SQL_CALC_FOUND_ROWS `realtylog_id`, `id`, `user_id`, `log_date`, `action`, `log_data` FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE `action`=?'.($ids!=0 ? ' AND id=?' : '').' ORDER BY log_date DESC LIMIT '.(($page-1)*$per_page).', '.$per_page;
    		
    		if($ids!=0){
    			$stmt=$DBC->query($query, array($type, $ids));
    		}else{
    			$stmt=$DBC->query($query, array($type));
    		}
    	}
    	
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$rows[]=$ar;
    		}
    	}
    	
    	$query='SELECT FOUND_ROWS() AS _cnt';
    	$stmt=$DBC->query($query);
    	$ar=$DBC->fetch($stmt);
    	
    	$total=$ar['_cnt'];
    	
    	$short_desc_tpl='{id}{city_id}|{date_added}|{city_id}|{district_id}|{number}|цена: {price}|{user_id}';
    	$placeholds=array();
    	preg_match_all('/(\{([a-z0-9_-]+)\})/', $short_desc_tpl, $placeholds);
    	$placeholders=array();
    	
    	if(!empty($placeholds[2])){
    		foreach ($placeholds[2] as $k=>$pl){
    			$placeholders[$pl]=$placeholds[1][$k];
    		}
    	}
    	
    	
    	if(!empty($rows)){
    		if(!empty($placeholders)){
	    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    		$data_model = new Data_Model();
	    		$model = $data_model->get_kvartira_model(false, true);
    		}
    		
    		foreach($rows as $ar){
    			if(!empty($placeholders)){
    				$short='';
    				$short_parts=array();
    				$ld=unserialize($ar['log_data']);
    				$_model=$model;
    				$_model=$data_model->init_model_data_from_var($ld, $ld['id'], $_model['data'], true);
    				$data_array[$ar['realtylog_id']] = $_model;
    				$data_array[$ar['realtylog_id']]['action'] = $ar['action'];
    				
    				foreach ($placeholders as $field=>$mask){
    					if(isset($_model[$field])){
    						if(in_array($_model[$field]['type'], array('select_box', 'select_by_query'))){
    							$short_parts[$mask]=$_model[$field]['value_string'];
    						}else{
    							$short_parts[$mask]=$_model[$field]['value'];
    						}
    					}else{
    						$short_parts[$mask]='';
    					}
    				}
    				unset($_model);
    				$short=str_replace(array_keys($short_parts), array_values($short_parts), $short_desc_tpl);
    			}else{
    				$short=$short_desc_tpl;
    			}
    			
    			//echo '<pre>';
    			
    			//print_r($_model);
    			//echo '</pre>';
    			//$ar['short_desc']=print_r($_model['city_id'], true);
    			$ar['short_desc']=$short;
    			$ret[]=$ar;
    		}
    		
    		
    		
    		
    	}
    	return array('rows'=>$ret, 'total'=>$total, 'data_array' => $data_array);
    }
    
    public function getDeletedCount(){
    	$DBC=DBC::getInstance();
    	$query='SELECT COUNT(`realtylog_id`) AS _cnt FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE `action`=?';
    	$stmt=$DBC->query($query, array('delete'));
    	$ar=$DBC->fetch($stmt);
    	return intval($ar['_cnt']);
    }
    
    function getLogs($data_id,$user_id=FALSE){
    	$ret=array();
    	$data_id=(int)$data_id;
    	if($data_id){
    		if($user_id!==FALSE AND (int)$user_id!=0){
    			$query='SELECT * FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE id='.(int)$data_id.' AND user_id='.(int)$user_id.' ORDER BY log_date DESC';
    		}else{
    			$query='SELECT * FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE id='.(int)$data_id.' ORDER BY log_date DESC';
    		}
    		$DBC=DBC::getInstance();
			$stmt=$DBC->query($query);
			if($stmt){
				while($ar=$DBC->fetch($stmt)){
					$ret[]=$ar;
				}
			}
    	}
    	return $ret;
    }
    
    
    function addLog($data_id, $editor_id, $action='new', $tablename, $pk){
    	
    	$query='SELECT * FROM '.DB_PREFIX.'_'.$tablename.' WHERE '.$pk.'='.$data_id.' LIMIT 1';
   	 	$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			$ar=$DBC->fetch($stmt);
			$user_id=$ar['user_id'];
			$serialized_data=serialize($ar);
		}
    	if($tablename=='data'){
    		if($action!='delete'){
    			$this->data_model[$this->table_name]['editor_id']['value']=$editor_id;
    			$this->data_model[$this->table_name]['id']['value']=$data_id;
    			$this->data_model[$this->table_name]['user_id']['value']=$user_id;
    			$this->data_model[$this->table_name]['action']['value']=$action;
    			$this->data_model[$this->table_name]['log_date']['value']=date('Y-m-d H:i:s', time());
    			$this->data_model[$this->table_name]['log_data']['value']=$serialized_data;
    			$this->add_data($this->data_model[$this->table_name]);
    			if ( $this->getError() ) {
    				echo $this->GetErrorMessage();
    			}
    		}else{
    			$this->data_model[$this->table_name]['id']['value']=$data_id;
    			$this->data_model[$this->table_name]['user_id']['value']=$user_id;
    			$this->data_model[$this->table_name]['editor_id']['value']=$editor_id;
    			$this->data_model[$this->table_name]['action']['value']=$action;
    			$this->data_model[$this->table_name]['log_date']['value']=date('Y-m-d H:i:s', time());
    			$this->data_model[$this->table_name]['log_data']['value']=$serialized_data;
    			$this->add_data($this->data_model[$this->table_name]);
    			if ( $this->getError() ) {
    				echo $this->GetErrorMessage();
    			}
    	
    		}
    	}
    	return;
    }
	
    function install () {
		        $query='CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'_realtylogv2` (
		  `realtylog_id` int(11) NOT NULL AUTO_INCREMENT,
		  `id` int(11) NOT NULL,
		  `user_id` int(11) NOT NULL,
		  `log_data` longtext NOT NULL,
		  `action` varchar(255) NOT NULL,
		  `log_date` datetime DEFAULT NULL,
		  `editor_id` int(11) NOT NULL,
		  PRIMARY KEY (`realtylog_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET='.DB_ENCODING.' ';
		$DBC=DBC::getInstance();
        $success=false;
    	$stmt=$DBC->query($query, array(), $rows, $success);
        if(!$success){
        	$rs = Multilanguage::_('L_APPLICATION_INSTALLED_ERROR');
        }else{
        	$rs = Multilanguage::_('L_APPLICATION_INSTALLED');
        }
        return $rs;
     }
    
    function restoreLog($logid){
    	$query='SELECT * FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE '.$this->primary_key.'='.(int)$logid.' LIMIT 1';
    	$DBC=DBC::getInstance();
    	//echo $query;
    	$stmt=$DBC->query($query, array(), $row, $success);
    	if ( !$success ) {
    		$this->riseError($DBC->getLastError());
    		return false;
    	}
    	 
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    	}
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
    	$Login = new Login();
    	$uid=$Login->getSessionUserId();
    	
    	$editor=$ar['editor_id'];
    	
    	if($editor!=$uid){
    		//return;
    	}
    	
    	$primary_key_value=$ar['id'];
    	$data=unserialize($ar['log_data']);
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$cdata_model = $data_model->get_kvartira_model(false, true);
    	$cdata_model['data']=$data_model->init_model_data_from_var($data, $primary_key_value, $cdata_model['data'], true);
    	$query = 'INSERT INTO '.DB_PREFIX.'_data (id) VALUES ('.$primary_key_value.')';
    	
    	if(intval($this->getConfigValue('apps.realtylogv2.restore_notactive'))===1 && isset($cdata_model['data']['active'])){
    		$cdata_model['data']['active']['value']=0;
    	}
    	if(intval($this->getConfigValue('apps.realtylogv2.refresh_adddate'))===1 && isset($cdata_model['data']['date_added'])){
    		$cdata_model['data']['date_added']['value']=date('Y-m-d H:i:s');
    	}
    	
    	
    	
    	$stmt=$DBC->query($query, array(), $row, $success);
	    if ( !$success ) {
	        $this->riseError($DBC->getLastError().', sql = '.$query);
	        return false;
	    }
    	    	
    	$query = $data_model->get_edit_query(DB_PREFIX.'_data', 'id', $primary_key_value, $cdata_model['data']);
    	$stmt=$DBC->query($query, array(), $row, $success);
    	if ( !$success ) {
    		$this->riseError($DBC->getLastError().', sql = '.$query);
    		return false;
    	}
    	 
    	$query='DELETE FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE '.$this->primary_key.'='.(int)$logid;
    	$stmt=$DBC->query($query, array(), $row, $success);
    	if ( !$success ) {
    		$this->riseError($DBC->getLastError().', sql = '.$query);
    		return false;
    	}
    	 
    }
    
    
    
	function showLog($logid){
		$this->restoreLog($logid);
		return;
		$query='SELECT * FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE '.$this->primary_key.'='.(int)$logid.' LIMIT 1';
		$DBC=DBC::getInstance();
    	$stmt=$DBC->query($query);
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    	}else{
    		return;
    	}
		
		$primary_key_value=$ar['id'];
		$data=unserialize($ar['log_data']);
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
        $cdata_model = $data_model->get_kvartira_model($this->getConfigValue('ajax_form_in_admin'));
        $cdata_model['data']=$data_model->init_model_data_from_var($data, $primary_key_value, $cdata_model['data'], true);
        echo '<pre>';
        print_r($data);
        print_r($cdata_model);
        echo '</pre>';
	}
    
    function getTopMenu(){
    	
    }

}