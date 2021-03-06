<?php
/**
 * Register using model
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class Register_Using_Model extends User_Object_Manager {
	
	function main () {
		$do=$this->getRequestValue('do');
		$action='_'.$do.'Action';
		if(!method_exists($this, $action)){
			$action='_defaultAction';
		}
		$rs .= $this->$action();
		return $rs;
	}
	
	protected function addAgreementElement(&$form_data){
		if($this->getConfigValue('register_form_agreement_enable')==1){
			$form_data['_post_agreement_check']['name'] = '_post_agreement_check';
			$form_data['_post_agreement_check']['title'] = Multilanguage::_('REGISTER_AGREEMENT_TEXT', 'system');
			$form_data['_post_agreement_check']['value'] = '';
			$form_data['_post_agreement_check']['length'] = 40;
			$form_data['_post_agreement_check']['dbtype'] = 0;
			$form_data['_post_agreement_check']['type'] = 'checkbox';
			$form_data['_post_agreement_check']['required'] = 'on';
			$form_data['_post_agreement_check']['unique'] = 'off';
		}
		
	}
	
	public function ajax_activate_sms () {
		$activation_code=$this->getRequestValue('activation_code');
		if ($activation_code == '') {
			return 'wrong_sms_code';
				
			
		}
		
		$q="SELECT active AS cnt FROM ".DB_PREFIX."_user WHERE pass=? LIMIT 1";
		
		$DBC=DBC::getInstance();
		$stmt=$DBC->query($q, array($activation_code));
		
		if(!$stmt){
			return 'wrong_sms_code';
		}else{
			$ar=$DBC->fetch($stmt);
			if((int)$ar['cnt']==0){
				$q="UPDATE ".DB_PREFIX."_user SET active=1, pass='' WHERE pass=?";
				$stmt=$DBC->query($q, array($activation_code));
				return 'activate_success';
			}
		
		}
		return 'wrong_sms_code';
	}
	
	protected function _activateAction(){
		$rs='';
		$activation_code=$this->getRequestValue('activation_code');
		$email=$this->getRequestValue('email');
		$q="SELECT active AS cnt, user_id FROM ".DB_PREFIX."_user WHERE email=? AND pass=? LIMIT 1";
		
		$DBC=DBC::getInstance();
		$stmt=$DBC->query($q, array($email, $activation_code));
		
		if(!$stmt){
			$rs=Multilanguage::_('ACTIVATION_ERROR','system');
		}else{
			$ar=$DBC->fetch($stmt);
			$new_user_id=$ar['user_id'];
			if((int)$ar['cnt']==0){
				$q="UPDATE ".DB_PREFIX."_user SET active=1 WHERE email=? AND pass=?";
				$stmt=$DBC->query($q, array($email, $activation_code));
					
				if(Multilanguage::is_set('LT_ACCOUNT_ACTIVATED','_template')){
					$rs=Multilanguage::_('LT_ACCOUNT_ACTIVATED','_template');
				}else{
					$rs=Multilanguage::_('ACCOUNT_ACTIVATED','system');
				}	
				
				if(1==$this->getConfigValue('notify_admin_about_register')){
					$this->notify_admin_about_register($new_user_id);
				}
					
				if(1==$this->getConfigValue('registration_notice')){
					$tpl=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/system/template/mails/register_email_notify_complete.tpl';
					
					global $smarty;

					$q="SELECT * FROM ".DB_PREFIX."_user WHERE email=? LIMIT 1";
					$stmt=$DBC->query($q, array($email));
					$ar=$DBC->fetch($stmt);

					$user_info = $ar;
					$query = "SELECT * FROM ".DB_PREFIX."_cache WHERE parameter=?";
					$stmt=$DBC->query($query, array($activation_code));
					$ar=$DBC->fetch($stmt);
					$password = $ar['value'];
					$query = "DELETE FROM ".DB_PREFIX."_cache WHERE parameter=?";
					$stmt=$DBC->query($query, array($activation_code));

					$smarty->assign('user_name', $user_info['fio']);
					$smarty->assign('site_url', $this->getServerFullUrl());
					if(1==intval($this->getConfigValue('email_as_login'))){
						$smarty->assign('login', $user_info['email']);
					}else{
						$smarty->assign('login', $user_info['login']);
					}

					$smarty->assign('password', $password);
					$smarty->assign('current_language', Multilanguage::get_current_language());

					$smarty->assign('email_signature', $this->getConfigValue('email_signature'));
					if(file_exists($tpl)){
						$message=$smarty->fetch($tpl);
					}else{
						$message=Multilanguage::_('NEW_REGISTER_BODY_TRIMMED','system');
					}
					if(Multilanguage::is_set('LT_NEW_REGISTER_TITLE','_template')){
						$subject = sprintf(Multilanguage::_('LT_NEW_REGISTER_TITLE','_template'), $_SERVER['HTTP_HOST']);
					}else{
						$subject = sprintf(Multilanguage::_('NEW_REGISTER_TITLE','system'), $_SERVER['HTTP_HOST']);
					}
					$to = $this->getRequestValue('email');
					$from = $this->getConfigValue('system_email');
					
					$this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
					$this->template->assign('target_url', $this->getServerFullUrl());
					
					$email_template_fetched = $this->fetch_email_template('user_activate_complete');
					
					if ( $email_template_fetched ) {
					    $subject = $email_template_fetched['subject'];
					    $message = $email_template_fetched['message'];

					    $message_array['apps_name'] = 'register_using_model';
					    $message_array['method'] = __METHOD__;
					    $message_array['message'] = "subject = $subject, message = $message";
					    $message_array['type'] = '';
					    //$this->writeLog($message_array);
					    
					}
					
					$this->sendFirmMail($to, $from, $subject, $message);
				}
					
					
			
			}else{
				header('location: '.SITEBILL_MAIN_URL.'/');
				exit();
				$rs=Multilanguage::_('ACTIVATION_ERROR','system');
			}
		}
		
		return $rs;
	}
	
	protected function postPreparedOperations($form_data){
		return $form_data;
	}
	
	protected function _new_doneAction(){
		$rs='';
	
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		
		$used_local_model=false;
		$form_data=array();
		
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php') ){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
			$ATH=new Admin_Table_Helper();
			$form_data_local=$ATH->load_model('user_register', false);
			if($form_data_local && !empty($form_data_local['user_register'])){
				$form_data[$this->table_name]=$form_data_local['user_register'];
				$used_local_model=true;
			}
		}
		
		if(empty($form_data)){
			$form_data = $this->data_model;
		}
			
		$form_data[$this->table_name]['newpass']['required'] = 'on';
		$form_data[$this->table_name]['newpass_retype']['required'] = 'on';
		unset($form_data[$this->table_name]['active']);
		
		/*if(isset($form_data[$this->table_name]['group_id'])){
			$shared_groups=$this->getConfigValue('newuser_registration_shared_groupid');
			$shared_groups=preg_replace('/[^\d,]/', '', $shared_groups);
			if($shared_groups!=''){
				$form_data[$this->table_name]['group_id']['query']='SELECT group_id, name FROM '.DB_PREFIX.'_group WHERE group_id IN ('.$shared_groups.')';
			}else{
				$form_data[$this->table_name]['group_id']['query']='SELECT group_id, name FROM '.DB_PREFIX.'_group WHERE group_id=0';
			}
		}*/
		
		if(isset($form_data[$this->table_name]['group_id'])){
			$shared_groups=$this->getConfigValue('newuser_registration_shared_groupid');
			$shared_groups=preg_replace('/[^\d,]/', '', $shared_groups);
			if($shared_groups!=''){
				$form_data[$this->table_name]['group_id']['query']='SELECT group_id, name FROM '.DB_PREFIX.'_group WHERE group_id IN ('.$shared_groups.')';
			}else{
				$form_data[$this->table_name]['group_id']['query']='SELECT group_id, name FROM '.DB_PREFIX.'_group WHERE group_id=0';
			}
		}
		
		$this->addAgreementElement($form_data[$this->table_name]);
		
		$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
		
		if(!isset($form_data[$this->table_name]['group_id'])){
			if(0!=(int)$this->getConfigValue('newuser_registration_groupid')){
				$form_data[$this->table_name]['group_id']['value'] = (int)$this->getConfigValue('newuser_registration_groupid');
			}else{
				$form_data[$this->table_name]['group_id']['value'] = $this->getGroupIdByName('realtor');
			}
		}else{
			if(''!=$this->getConfigValue('newuser_registration_shared_groupid')){
				$groups=array();
				$shared_groups=$this->getConfigValue('newuser_registration_shared_groupid');
				$shared_groups=preg_replace('/[^\d,]/', '', $shared_groups);
				$groups=explode(',', $shared_groups);
				
				if(!in_array($form_data[$this->table_name]['group_id']['value'], $groups)){
					if(0!=(int)$this->getConfigValue('newuser_registration_groupid')){
						$form_data[$this->table_name]['group_id']['value'] = (int)$this->getConfigValue('newuser_registration_groupid');
					}else{
						$form_data[$this->table_name]['group_id']['value'] = $this->getGroupIdByName('realtor');
					}
				}
			}else{
				if(0!=(int)$this->getConfigValue('newuser_registration_groupid')){
					$form_data[$this->table_name]['group_id']['value'] = (int)$this->getConfigValue('newuser_registration_groupid');
				}else{
					$form_data[$this->table_name]['group_id']['value'] = $this->getGroupIdByName('realtor');
				}
			}
		}
		if ( 0!=(int)$this->getConfigValue('apps.billing.default_tariff_id')  ) {
			$form_data[$this->table_name]['tariff_id']['value'] = $this->getConfigValue('apps.billing.default_tariff_id');
		}
		
		if ( 1==intval($this->getConfigValue('email_as_login')) && isset($form_data[$this->table_name]['login']) && $form_data[$this->table_name]['login']['value']=='') {
			$form_data[$this->table_name]['login']['value'] = $form_data[$this->table_name]['email']['value'];
		}
		
		
		if ( isset($form_data[$this->table_name]['reg_date']) ) {
			$form_data[$this->table_name]['reg_date']['value'] = date('Y-m-d H:i:s');
		}elseif($used_local_model && isset($this->data_model[$this->table_name]['reg_date'])){
			$form_data[$this->table_name]['reg_date']=$this->data_model[$this->table_name]['reg_date'];
			$form_data[$this->table_name]['reg_date']['value'] = date('Y-m-d H:i:s');
		}
		
		$form_data[$this->table_name]=$this->postPreparedOperations($form_data[$this->table_name]);
		 
		if ( !$this->check_data( $form_data[$this->table_name] ) ) {
			$form_data[$this->table_name]['imgfile']['value'] = '';
			$rs = $this->get_form($form_data[$this->table_name], 'new', 0, Multilanguage::_('L_GOREGISTER_BUTTON'), SITEBILL_MAIN_URL.'/register/');
			 
		} else {
			$new_user_id = $this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
			
			if ( $this->getError() ) {
				$form_data[$this->table_name]['imgfile']['value'] = '';
				$rs = $this->get_form($form_data[$this->table_name], 'new', 0, Multilanguage::_('L_GOREGISTER_BUTTON'), SITEBILL_MAIN_URL.'/register/');
			} else {
				$email = $form_data[$this->table_name]['email']['value'];
				$login = $form_data[$this->table_name]['login']['value'];
				$password = $form_data[$this->table_name]['newpass']['value'];
				//echo 'new done';

				if(1==$this->getConfigValue('use_registration_email_confirm')){
					$DBC=DBC::getInstance();
					$activation_code=md5(time().'_'.rand(100,999));
					$query='UPDATE '.DB_PREFIX.'_user SET pass=? WHERE user_id=?';
					$stmt=$DBC->query($query, array($activation_code, $new_user_id));
					$activation_link='<a href="http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/register?do=activate&activation_code='.$activation_code.'&email='.$email.'">http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/register?do=activate&activation_code='.$activation_code.'&email='.$email.'</a>';
					
					$tpl=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/system/template/mails/user_registration_conf.tpl';
					global $smarty;
					$smarty->assign('mail_activation_link', $this->getServerFullUrl().'/register?do=activate&activation_code='.$activation_code.'&email='.$email);
					$smarty->assign('mail_server', $this->getServerFullUrl());
					$smarty->assign('mail_current_language', Multilanguage::get_current_language());
					
					if(file_exists($tpl)){
						//$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/system/template/mails/user_registration_conf.tpl';
						$message=$smarty->fetch($tpl);
					}else{
						$message = sprintf(Multilanguage::_('NEW_REG_EMAILACCEPT_BODY','system'), $login, $password, $activation_link);
					}
						
					if(Multilanguage::is_set('LT_NEW_REG_EMAILACCEPT_TITLE','_template')){
						$subject = sprintf(Multilanguage::_('LT_NEW_REG_EMAILACCEPT_TITLE','_template'), $_SERVER['HTTP_HOST']);
					}else{
						$subject = sprintf(Multilanguage::_('NEW_REG_EMAILACCEPT_TITLE','system'), $_SERVER['HTTP_HOST']);
					}
					
					 
					$to = $email;
					$from = $this->getConfigValue('system_email');
					
					$this->template->assign('login', $login);
					$this->template->assign('password', $password);
					$this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
					$email_template_fetched = $this->fetch_email_template('registration_email_confirm');
					
					if ( $email_template_fetched ) {
					    $subject = $email_template_fetched['subject'];
					    $message = $email_template_fetched['message'];

					    $message_array['apps_name'] = 'register_using_model';
					    $message_array['method'] = __METHOD__;
					    $message_array['message'] = "subject = $subject, message = $message";
					    $message_array['type'] = '';
					    //$this->writeLog($message_array);
					    
					}
					
					
					$this->sendFirmMail($to, $from, $subject, $message);
					$query = 'DELETE FROM '.DB_PREFIX.'_cache WHERE parameter=?';
					$stmt=$DBC->query($query, array($activation_code));
					$query = "insert into ".DB_PREFIX."_cache (`parameter`, `value`) values (?, ?)";
					$stmt=$DBC->query($query, array($activation_code, $password));
					
					if(Multilanguage::is_set('LT_REGISTER_SUCCESS','_template')){
						$rs = '<h3>'.Multilanguage::_('LT_REGISTER_SUCCESS','_template').'</h3><br>';
					}else{
						$rs = '<h3>'.Multilanguage::_('REGISTER_SUCCESS','system').'</h3><br>';
					}
					if($form_data[$this->table_name]['active']['value']!=1){
						if(Multilanguage::is_set('LT_ACTIVATION_CODE_SENT','_template')){
							$rs.=Multilanguage::_('LT_ACTIVATION_CODE_SENT','_template');
						}else{
							$rs.=Multilanguage::_('ACTIVATION_CODE_SENT','system');
						}
					}
					return $rs;
				}
				
				
				if(1==$this->getConfigValue('registration_notice')){
					$this->send_registration_notice($form_data[$this->table_name]);
				}
				
				if(1==$this->getConfigValue('notify_admin_about_register')){
					$this->notify_admin_about_register($new_user_id);
				}
				$rs=$this->postRegisterAction($form_data);
				//return $rs;
				
				
			}
		}
		
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/register_user.tpl')){
			global $smarty;
			$smarty->assign('register_form', $rs);
			return $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/register_user.tpl');
		}else{
			return $rs;
		}
		
		return $rs;
	}
	
	protected function send_registration_notice($form_data){
		$to = $form_data['email']['value'];
		if(1==intval($this->getConfigValue('email_as_login'))) {
			$login = $form_data['email']['value'];
		}else{
			$login = $form_data['login']['value'];
		}
		
		$password = $form_data['newpass']['value'];
		$tpl=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/system/template/mails/user_registration.tpl';
		global $smarty;
		$smarty->assign('mail_login', $login);
		$smarty->assign('login', $login);
		$smarty->assign('mail_password', $form_data['newpass']['value']);
		$smarty->assign('password', $form_data['newpass']['value']);
		$smarty->assign('mail_server', $this->getServerFullUrl());
		
		if(file_exists($tpl)){
			//$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/system/template/mails/user_registration.tpl';
			
			$message=$smarty->fetch($tpl);
		}else{
			$message = sprintf(Multilanguage::_('NEW_REGISTER_BODY','system'), $login, $password);
		}
		
		if(Multilanguage::is_set('LT_NEW_REGISTER_TITLE','_template')){
			$subject = sprintf(Multilanguage::_('LT_NEW_REGISTER_TITLE','_template'), $_SERVER['HTTP_HOST']);
		}else{
			$subject = sprintf(Multilanguage::_('NEW_REGISTER_TITLE','system'), $_SERVER['HTTP_HOST']);
		}
		
		$from = $this->getConfigValue('system_email');
		
		$this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
		$this->template->assign('target_url', $this->getServerFullUrl());

		$email_template_fetched = $this->fetch_email_template('user_registration_complete');

		if ( $email_template_fetched ) {
		    $subject = $email_template_fetched['subject'];
		    $message = $email_template_fetched['message'];

		    $message_array['apps_name'] = 'register_using_model';
		    $message_array['method'] = __METHOD__;
		    $message_array['message'] = "subject = $subject, message = $message";
		    $message_array['type'] = '';
		    //$this->writeLog($message_array);

		}
		
		$this->sendFirmMail($to, $from, $subject, $message);
	}
	
	protected function notify_admin_about_register($new_user_id){
		$DBC=DBC::getInstance();
		$q="SELECT * FROM ".DB_PREFIX."_user WHERE user_id=? LIMIT 1";
		$stmt=$DBC->query($q, array($new_user_id));
		$user_info=$DBC->fetch($stmt);
		
		if(1==intval($this->getConfigValue('email_as_login'))) {
			$login = $user_info['email'];
		}else{
			$login = $user_info['login'];
		}
		
		$message = sprintf(Multilanguage::_('NEW_REGISTER_NEW_USER','system'), $login);
		$subject = sprintf(Multilanguage::_('NEW_REGISTER_TITLE','system'), $_SERVER['HTTP_HOST']);
		
		$to = $this->getConfigValue('order_email_acceptor');
		$from = $this->getConfigValue('order_email_acceptor');
		
		$this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
		$this->template->assign('target_url', $this->getServerFullUrl().'/admin/?action=user');
		$this->template->assign('user_info', $user_info);

		$email_template_fetched = $this->fetch_email_template('notify_admin_about_register');

		if ( $email_template_fetched ) {
		    $subject = $email_template_fetched['subject'];
		    $message = $email_template_fetched['message'];

		    $message_array['apps_name'] = 'notify_admin_about_register';
		    $message_array['method'] = __METHOD__;
		    $message_array['message'] = "subject = $subject, message = $message";
		    $message_array['type'] = '';
		    //$this->writeLog($message_array);

		}
		
		$this->sendFirmMail($to, $from, $subject, $message);
	}
	
	protected function postRegisterAction($form_data){
		$rs = '';
		if(Multilanguage::is_set('LT_REGISTER_SUCCESS','_template')){
			$rs.='<h3>'.Multilanguage::_('LT_REGISTER_SUCCESS','_template').'</h3><br>';
		}else{
		    if(1==$this->getConfigValue('use_registration_email_confirm')){
			$rs.='<h3>'.Multilanguage::_('ACTIVATION_CODE_SENT','system').'</h3><br>';
		    } else {
			$rs.=Multilanguage::_('REGISTER_SUCCESS','system');		    
		    }
		}
		$rs .= '<a href="'.SITEBILL_MAIN_URL.'/login/">Войти</a>';
		return $rs;
	}
	
	protected function _defaultAction(){
		$rs='';
		
		
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		
		$used_local_model=false;
		$form_data=array();
		
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php') ){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
			$ATH=new Admin_Table_Helper();
			$form_data_local=$ATH->load_model('user_register', false);
			if($form_data_local && !empty($form_data_local['user_register'])){
				$form_data[$this->table_name]=$form_data_local['user_register'];
				$used_local_model=true;
			}
		}
		
		if(empty($form_data)){
			$form_data = $this->data_model;
		}
		
		
	
		
		$form_data[$this->table_name]['newpass']['required'] = 'on';
		$form_data[$this->table_name]['newpass_retype']['required'] = 'on';
		unset($form_data[$this->table_name]['active']);
		
		//print_r($form_data);
		
		if(isset($form_data[$this->table_name]['group_id'])){
			$shared_groups=$this->getConfigValue('newuser_registration_shared_groupid');
			$shared_groups=preg_replace('/[^\d,]/', '', $shared_groups);
			if($shared_groups!=''){
				$form_data[$this->table_name]['group_id']['query']='SELECT group_id, name FROM '.DB_PREFIX.'_group WHERE group_id IN ('.$shared_groups.')';
			}else{
				$form_data[$this->table_name]['group_id']['query']='SELECT group_id, name FROM '.DB_PREFIX.'_group WHERE group_id=0';
			}
		}
		
		$this->addAgreementElement($form_data[$this->table_name]);
		
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/register_user.tpl')){
			global $smarty;
			$smarty->assign('register_form', $this->get_form($form_data[$this->table_name], 'new', 0, Multilanguage::_('L_GOREGISTER_BUTTON'), SITEBILL_MAIN_URL.'/register/'));
			return $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/register_user.tpl');
		}else{
			return $this->get_form($form_data[$this->table_name], 'new', 0, Multilanguage::_('L_GOREGISTER_BUTTON'), SITEBILL_MAIN_URL.'/register/');
		}
		
		return $rs;
	}
	
	
	
	public function ajaxRegister(){
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		
		$used_local_model=false;
		$form_data=array();
		
		$json_mode=false;
		if(1==intval($this->getRequestValue('json'))){
			$json_mode=true;
		}
		
		
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php') ){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
			$ATH=new Admin_Table_Helper();
			$form_data_local=$ATH->load_model('user_register', false);
			if($form_data_local && !empty($form_data_local['user_register'])){
				$form_data[$this->table_name]=$form_data_local['user_register'];
				$used_local_model=true;
			}
		}
		
		if(empty($form_data)){
			$form_data = $this->data_model;
		}
		
		$this->addAgreementElement($form_data[$this->table_name]);
		
		
		$form_data[$this->table_name]['newpass']['required'] = 'on';
		$form_data[$this->table_name]['newpass_retype']['required'] = 'on';
		unset($form_data[$this->table_name]['active']);
		
		$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
		
		
		if ( 1==intval($this->getConfigValue('email_as_login')) && isset($form_data[$this->table_name]['login']) && $form_data[$this->table_name]['login']['value']=='') {
			$form_data[$this->table_name]['login']['value'] = $form_data[$this->table_name]['email']['value']; 
		}
		
		if(!isset($form_data[$this->table_name]['group_id'])){
			if(0!=(int)$this->getConfigValue('newuser_registration_groupid')){
				$form_data[$this->table_name]['group_id']['value'] = (int)$this->getConfigValue('newuser_registration_groupid');
			}else{
				$form_data[$this->table_name]['group_id']['value'] = $this->getGroupIdByName('realtor');
			}
		}else{
			if(''!=$this->getConfigValue('newuser_registration_shared_groupid')){
				$groups=array();
				$shared_groups=$this->getConfigValue('newuser_registration_shared_groupid');
				$shared_groups=preg_replace('/[^\d,]/', '', $shared_groups);
				$groups=explode(',', $shared_groups);
				
				if(!in_array($form_data[$this->table_name]['group_id']['value'], $groups)){
					if(0!=(int)$this->getConfigValue('newuser_registration_groupid')){
						$form_data[$this->table_name]['group_id']['value'] = (int)$this->getConfigValue('newuser_registration_groupid');
					}else{
						$form_data[$this->table_name]['group_id']['value'] = $this->getGroupIdByName('realtor');
					}
				}
			}else{
				if(0!=(int)$this->getConfigValue('newuser_registration_groupid')){
					$form_data[$this->table_name]['group_id']['value'] = (int)$this->getConfigValue('newuser_registration_groupid');
				}else{
					$form_data[$this->table_name]['group_id']['value'] = $this->getGroupIdByName('realtor');
				}
			}
		}
		
		
/*
		if(0!=(int)$this->getConfigValue('newuser_registration_groupid')){
			$form_data[$this->table_name]['group_id']['value'] = (int)$this->getConfigValue('newuser_registration_groupid');
		}else{
			$form_data[$this->table_name]['group_id']['value'] = $this->getGroupIdByName('realtor');
		}
*/
		
		if ( isset($form_data[$this->table_name]['reg_date']) ) {
			$form_data[$this->table_name]['reg_date']['value'] = date('Y-m-d H:i:s');
		}elseif($used_local_model && isset($this->data_model[$this->table_name]['reg_date'])){
			$form_data[$this->table_name]['reg_date']=$this->data_model[$this->table_name]['reg_date'];
			$form_data[$this->table_name]['reg_date']['value'] = date('Y-m-d H:i:s');
		}
		
		foreach ($form_data[$this->table_name] as $it=>$va){
			$form_data[$this->table_name][$it]['value']=SiteBill::iconv('utf-8', SITE_ENCODING, $va['value']);
		}
		
		$form_data[$this->table_name]=$this->postPreparedOperations($form_data[$this->table_name]);
			
		if ( !$this->check_data( $form_data[$this->table_name] ) ) {
			$form_data[$this->table_name]['imgfile']['value'] = '';
			if($json_mode){
				return json_encode(array('result'=>0, 'msg'=>$this->getError()));
			}
			return $this->getError();
		
		} else {
			$new_user_id = $this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
			if ( $this->getError() ) {
				$form_data[$this->table_name]['imgfile']['value'] = '';
				if($json_mode){
					return json_encode(array('result'=>0, 'msg'=>$this->getError()));
				}
				return $this->getError();
				$rs = $this->get_form($form_data[$this->table_name], 'new');
			} else {
				$email = $form_data[$this->table_name]['email']['value'];
				if ( $this->getConfigValue('apps.sms.phone_source_column') ) {
					$login = $form_data[$this->table_name][$this->getConfigValue('apps.sms.phone_source_column')]['value'];
				} else {
					if ( 1==intval($this->getConfigValue('email_as_login'))) {
						$login = $form_data[$this->table_name]['email']['value'];
					}else{
						$login = $form_data[$this->table_name]['login']['value'];
					}
					
				}
				$password = $form_data[$this->table_name]['newpass']['value'];
				
				$this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
				$this->template->assign('login', $login);
				$this->template->assign('password', $password);
				
				
				if(1==$this->getConfigValue('use_registration_sms_confirm') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/sms/admin/admin.php')){
					$activation_code=substr(md5(time().'_'.rand(100,999)), 0, 5);
					$DBC=DBC::getInstance();
					$query='UPDATE '.DB_PREFIX.'_user SET pass=? WHERE user_id=?';
					$stmt=$DBC->query($query, array($activation_code, $new_user_id));
						
					$query = 'DELETE FROM '.DB_PREFIX.'_cache WHERE parameter=?';
					$stmt=$DBC->query($query, array($activation_code));
					$query = 'INSERT INTO '.DB_PREFIX.'_cache (`parameter`, `value`) VALUES (?, ?)';
					$stmt=$DBC->query($query, array($activation_code, $password));
					
					require_once SITEBILL_DOCUMENT_ROOT.'/apps/sms/admin/admin.php';
					$SMS=new sms_admin();
					if ( $this->getConfigValue('apps.sms.sender') != '' ) {
						$sms_sender = $this->getConfigValue('apps.sms.sender');
					} else {
						$sms_sender = 'sms_sender';
					}
					$r=$SMS->send($login, 'Vash kod: '.$activation_code, $sms_sender);
					if($json_mode){
						return json_encode(array('result'=>1, 'msg'=>'confirm_sms_code'));
					} 				
					return 'confirm_sms_code';
				}
				
		
				if(1==$this->getConfigValue('use_registration_email_confirm')){
					$DBC=DBC::getInstance();
					$activation_code=md5(time().'_'.rand(100,999));
					$query='UPDATE '.DB_PREFIX.'_user SET pass=? WHERE user_id=?';
					$stmt=$DBC->query($query, array($activation_code, $new_user_id));
					$activation_link='<a href="http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/register?do=activate&activation_code='.$activation_code.'&email='.$email.'">http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/register?do=activate&activation_code='.$activation_code.'&email='.$email.'</a>';
					
						
					$tpl=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/system/template/mails/user_registration_conf.tpl';
					global $smarty;
					$smarty->assign('mail_activation_link', $this->getServerFullUrl().'/register?do=activate&activation_code='.$activation_code.'&email='.$email);
					$smarty->assign('mail_server', $this->getServerFullUrl());
					$smarty->assign('mail_current_language', Multilanguage::get_current_language());
					
					if(file_exists($tpl)){
						$message=$smarty->fetch($tpl);
					}else{
						$message = sprintf(Multilanguage::_('NEW_REG_EMAILACCEPT_BODY','system'), $activation_link);
					}
					
					if(Multilanguage::is_set('LT_NEW_REG_EMAILACCEPT_TITLE','_template')){
						$subject = sprintf(Multilanguage::_('LT_NEW_REG_EMAILACCEPT_TITLE','_template'), $_SERVER['HTTP_HOST']);
					}else{
						$subject = sprintf(Multilanguage::_('NEW_REG_EMAILACCEPT_TITLE','system'), $_SERVER['HTTP_HOST']);
					}
						
					
					$to = $email;
					$from = $this->getConfigValue('system_email');

					$email_template_fetched = $this->fetch_email_template('registration_email_confirm');
					
					if ( $email_template_fetched ) {
					    $subject = $email_template_fetched['subject'];
					    $message = $email_template_fetched['message'];

					    $message_array['apps_name'] = 'register_using_model';
					    $message_array['method'] = __METHOD__;
					    $message_array['message'] = "subject = $subject, message = $message";
					    $message_array['type'] = '';
					    //$this->writeLog($message_array);
					    
					}
						
					$this->sendFirmMail($to, $from, $subject, $message);
					$query = 'DELETE FROM '.DB_PREFIX.'_cache WHERE parameter=?';
					$stmt=$DBC->query($query, array($activation_code));
					$query = "insert into ".DB_PREFIX."_cache (`parameter`, `value`) values (?, ?)";
					$stmt=$DBC->query($query, array($activation_code, $password));
						
					if(Multilanguage::is_set('LT_REGISTER_SUCCESS','_template')){
						$rs = '<h3>'.Multilanguage::_('LT_REGISTER_SUCCESS','_template').'</h3><br>';
					}else{
						$rs = '<h3>'.Multilanguage::_('REGISTER_SUCCESS','system').'</h3><br>';
					}
					if($form_data[$this->table_name]['active']['value']!=1){
						if(Multilanguage::is_set('LT_ACTIVATION_CODE_SENT','_template')){
							$rs.=Multilanguage::_('LT_ACTIVATION_CODE_SENT','_template');
						}else{
							$rs.=Multilanguage::_('ACTIVATION_CODE_SENT','system');
						}
					}
					if($json_mode){
						return json_encode(array('result'=>1, 'msg'=>$rs));
					}
					return $rs;
				}
				
				if(1==$this->getConfigValue('notify_admin_about_register')){
					$this->notify_admin_about_register($new_user_id);
				}
				
		
				if(1==$this->getConfigValue('registration_notice')){
					$message = sprintf(Multilanguage::_('NEW_REGISTER_BODY','system'), $login, $password);
					$subject = sprintf(Multilanguage::_('NEW_REGISTER_TITLE','system'), $_SERVER['HTTP_HOST']);
		
					$to = $email;
					$from = $this->getConfigValue('system_email');
					
					$this->template->assign('target_url', $this->getServerFullUrl());

					$email_template_fetched = $this->fetch_email_template('user_registration_complete');

					if ( $email_template_fetched ) {
					    $subject = $email_template_fetched['subject'];
					    $message = $email_template_fetched['message'];

					    $message_array['apps_name'] = 'register_using_model';
					    $message_array['method'] = __METHOD__;
					    $message_array['message'] = "subject = $subject, message = $message";
					    $message_array['type'] = '';
					    //$this->writeLog($message_array);

					}
					
					$this->sendFirmMail($to, $from, $subject, $message);
				}
				if($json_mode){
					return json_encode(array('result'=>1, 'subres'=>'email_confirm', 'msg'=>''));
				}
				return 'ok';
				$rs = $this->welcome();
			}
		}
	}
	
	public function getRegisterFormElements(){
		
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php') ){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
			$ATH=new Admin_Table_Helper();
			$form_data=$ATH->load_model('user_register', false);
		}
		//var_dump($form_data);
		if(!$form_data || empty($form_data['user_register'])){
			$form_data = $this->data_model;
		}else{
			$form_data[$this->table_name] = $form_data['user_register'];
		}
		
		
		$form_data[$this->table_name]['newpass']['required'] = 'on';
		$form_data[$this->table_name]['newpass_retype']['required'] = 'on';
		unset($form_data[$this->table_name]['active']);
		
		$this->addAgreementElement($form_data[$this->table_name]);
	
		
		$reg_form_elements=array();
		foreach($form_data[$this->table_name] as $fden=>$fdev){
			if($fdev['required']=='on'){
				$reg_form_elements[$fden]=$fdev;
			}
		}
		if(isset($reg_form_elements['group_id'])){
			if($this->getConfigValue('newuser_registration_shared_groupid')!=""){
				$shared_groups=$this->getConfigValue('newuser_registration_shared_groupid');
				$shared_groups=preg_replace('/[^\d,]/', '', $shared_groups);
				//var_dump($shared_groups);
				if($shared_groups!=''){
					$reg_form_elements['group_id']['query']='SELECT * FROM '.DB_PREFIX.'_group WHERE group_id IN ('.$shared_groups.')';
				}else{
					$reg_form_elements['group_id']['query']='SELECT * FROM '.DB_PREFIX.'_group WHERE group_id=0';
				}
			}
			else
			{
				unset($reg_form_elements['group_id']);
			}
		}
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		$form_generator = new Form_Generator();
		$el = $form_generator->compile_form_elements($reg_form_elements,true);
		return $el['public'][$this->getConfigValue('default_tab_name')];
	}
	
	function checkemaildomain($email){
		list($box, $domain)=explode('@',$email);
		if($domain==''){
			return false;
		}
		$DBC=DBC::getInstance();
		$q="SELECT * FROM ".DB_PREFIX."_register_disable WHERE LOWER(`domain`)=?";
		$stmt=$DBC->query($q, array(mb_strtolower($domain, 'utf-8')));
		if($stmt){
			return false;
		}
		return true;
	}
	
	/**
	 * Check data
	 * @param array $form_data
	 * @return boolean
	 */
	function check_data ( $form_data ) {
		//var_dump($form_data['newpass']['value']);
		
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		
		if(isset($form_data['email']) && $form_data['email']['value']!=''){
			$email=$form_data['email']['value'];
			if(strlen($email)<5){
				$this->riseError(Multilanguage::_('REG_EMAIL_INVAL', 'system'));
				return false;
			}
			
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			    $this->riseError(Multilanguage::_('REG_EMAIL_INVAL', 'system'));
				return false;
			}
		}
		
		if ( $this->getRequestValue('do') != 'edit_done' ) {
			if ( !$this->checkEmail($form_data['email']['value']) ) {
				$this->riseError(Multilanguage::_('REG_EMAIL_YET_REG', 'system'));
				return false;
			}
		} else {
			if ( !$this->checkDiffEmail($form_data['email']['value'], $form_data['user_id']['value']) ) {
				$this->riseError(Multilanguage::_('REG_EMAIL_YET_REG', 'system'));
				return false;
			}
		}
		
		if(!$this->checkemaildomain($form_data['email']['value'])){
			$this->riseError(Multilanguage::_('REG_EMAIL_NOT_GOOD', 'system'));
			return false;
		}
		
		if(isset($form_data['login'])){
			if($form_data['login']['value']==''){
				$this->riseError(Multilanguage::_('REG_SET_LOGIN', 'system'));
				return false;
			}
				
			if(!preg_match('/^([a-zA-Z0-9-_@\.]*)$/', $form_data['login']['value'])){
				$this->riseError(Multilanguage::_('REG_LOGIN_REQ_3', 'system'));
				return false;
			}
				
			if(preg_match('/^(vk|tw|gl|fb|ok)([0-9]*)$/', $form_data['login']['value'])){
				$this->riseError(Multilanguage::_('REG_LOGIN_USED', 'system'));
				return false;
			}
				
			if ( !$this->checkLogin($form_data['login']['value']) ) {
				$this->riseError(Multilanguage::_('REG_LOGIN_USED', 'system'));
				return false;
			}
		}
		
		
		
		
		
		
		
		if ( !$data_model->check_data($form_data) ) {
			$this->riseError($data_model->GetErrorMessage());
			return false;
		}
		
		if ( $form_data['newpass']['value'] != '' ) {
			
			if(!$this->checkPasswordQuality($form_data['newpass']['value'], $errormsg)){
				$this->riseError($errormsg);
				return false;
			}
			
			if ( $form_data['newpass']['value'] != $form_data['newpass_retype']['value'] ) {
				$this->riseError(Multilanguage::_('PASSWORDS_NOT_EQUAL','system'));
				return false;
			}
		}
		
		return true;
	}
	
	function checkLoginQuality($login, &$msg){
		
	}
	
	function checkPasswordQuality($password, &$msg){
		$min_pass_length=(int)$this->getConfigValue('register_minpasslength');
		$max_pass_length=(int)$this->getConfigValue('register_maxpasslength');
		$min_pass_length=($min_pass_length==0 ? 5 : $min_pass_length);
		$max_pass_length=($max_pass_length==0 ? 32 : $max_pass_length);
			
		$pass_count=mb_strlen($password, SITE_ENCODING);
			
		if ( $pass_count < $min_pass_length ) {
			$msg=sprintf(Multilanguage::_('MIN_PASSWORD_LENGTH','system'), $min_pass_length);
			return false;
		}
		if ( $pass_count > $max_pass_length ) {
			$msg=sprintf(Multilanguage::_('MAX_PASSWORD_LENGTH','system'), $max_pass_length);
			return false;
		}
			
		$pass_control_type=(int)$this->getConfigValue('register_passstregth');
				
		if(preg_match_all('/(\d)/', $password, $dig_match)){
			$pass_dig_count=count($dig_match[1]);
		}else{
			$pass_dig_count=0;
		}
		
		if(preg_match_all('/([a-zа-яё])/u', $password, $smlet_match)){
			$pass_smlet_count=count($smlet_match[1]);
		}else{
			$pass_smlet_count=0;
		}
		
		if(preg_match_all('/([A-ZА-ЯЁ])/u', $password, $bglet_match)){
			$pass_bglet_count=count($bglet_match[1]);
		}else{
			$pass_bglet_count=0;
		}
		
		$pass_nonlet_count=$pass_count-$pass_dig_count-$pass_smlet_count-$pass_bglet_count;
			
		if($pass_dig_count==$pass_count){
			$first=(string)$password[0];
			$simpass='';
			for($i=1; $i<=$pass_count; $i++){
				$simpass.=$first;
			}
		
			if($simpass==$password){
				$msg=Multilanguage::_('REG_BAD_PASS','system');
				return false;
			}
		
			$simpass='';
			for($i=0; $i<$pass_count; $i++){
				$simpass.=(string)($first+$i);
			}
		
			if($simpass==$password){
				$msg=Multilanguage::_('REG_BAD_PASS','system');
				return false;
			}
		}
			
		$first=(string)$password[0];
		$simpass='';
		for($i=1; $i<=$pass_count; $i++){
			$simpass.=$first;
		}
			
		if($simpass==$password){
			$msg=Multilanguage::_('REG_BAD_PASS','system');
			return false;
		}
			
		if($pass_control_type==0){
		
		}elseif($pass_control_type==1){
			if($pass_dig_count==$pass_count || $pass_dig_count==0){
				$msg=Multilanguage::_('REG_BAD_PASS','system').'. '.Multilanguage::_('REG_BAD_PASS_REQ1','system').'.';
				return false;
			}
		}elseif($pass_control_type==2){
			if($pass_dig_count==0 || $pass_smlet_count==0 || $pass_bglet_count==0){
				$msg=Multilanguage::_('REG_BAD_PASS','system').'. '.Multilanguage::_('REG_BAD_PASS_REQ2','system').'.';
				return false;
			}
		}elseif($pass_control_type==3){
			if($pass_dig_count==0 || $pass_smlet_count==0 || $pass_bglet_count==0 || $pass_nonlet_count==0){
				$msg=Multilanguage::_('REG_BAD_PASS','system').'. '.Multilanguage::_('REG_BAD_PASS_REQ3','system').'.';
				return false;
			}
		}
		return true;
	}
	
	function welcome() {
		$rs = '<h3>'.Multilanguage::_('REGISTER_SUCCESS','system').'</h3><br>';
		$rs .= '<a href="'.SITEBILL_MAIN_URL.'/account/">'.Multilanguage::_('PRIVATE_ACCOUNT','system').'</a>';
		return $rs;
	}

	public function getUniqLogin($login){
		if ( !$this->checkLogin($login) ) {
			$DBC=DBC::getInstance();
			$query='SELECT login FROM '.DB_PREFIX.'_user WHERE login LIKE \''.$login.'%\'';
			
			$stmt=$DBC->query($query);
			$used_logins=array();
			$used_numbers=array();
			if($stmt){
				while($ar=$DBC->fetch($stmt)){
					$used_logins[]=$ar['login'];
				}
			}
			
			foreach($used_logins as $used_login){
				if(preg_match('/^'.$login.'(\d+)$/', $used_login, $matches)){
					$used_numbers[]=(int)$matches[1];
				}
			}
			if(empty($used_numbers)){
				$login=$login.'1';
			}else{
				
				rsort($used_numbers);
				$login=$login.($used_numbers[0]+1);
			}
			
			
		}
		return $login;
	}
	
}