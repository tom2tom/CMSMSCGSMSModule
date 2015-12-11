<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

/**
 @mod: reference to current SMSG module object
 @smarty: the current smarty object
 @modify: boolean, whether to setup for full editing
 @dflttpl: boolean, whether to setup for editing default template
 @id: instance id of @mod
 @returnid: page id to use on subsequent forms and links
 @activetab: tab to return to
 @prefix: template full-name prefix ('enternumber_' or 'entertext_')
 @prefdefname: name of preference that contains the base-name of the current
  default template for @prefix
 */
function SetupTemplateList(&$mod,&$smarty,$modify,$dflttpl,
	$id,$returnid,$activetab,
	$prefix,$prefdefname
	)
{
	if($modify)
	{
		$theme = ($mod->before20) ? cmsms()->get_variable('admintheme'):
			cms_utils::get_theme_object();
		$trueicon = $theme->DisplayImage('icons/system/true.gif',$mod->Lang('default_tip'),'','','systemicon');
		$falseicon = $theme->DisplayImage('icons/system/false.gif',$mod->Lang('defaultset_tip'),'','','systemicon');
		$addicon = $theme->DisplayImage('icons/system/newobject.gif',$mod->Lang('add_template'),'','','systemicon');
		$editicon = $theme->DisplayImage('icons/system/edit.gif',$mod->Lang('edit_tip'),'','','systemicon');
		$deleteicon = $theme->DisplayImage('icons/system/delete.gif',$mod->Lang('deleteone_tip'),'','','systemicon');
		$prompt = $mod->Lang('sure_ask');
		$args = array('prefix'=>$prefix,'activetab'=>$activetab);
	}
	else
		$yes = $mod->Lang('yes');

	$defaultname = $mod->GetPreference($prefdefname);
	$rowarray = array();

	$mytemplates = $mod->ListTemplates(SMSG::MODNAME);
	array_walk($mytemplates,
		function(&$n,$k,$p){
$l=strlen($p);$n=(strncmp($n,$p,$l) === 0)?substr($n,$l):FALSE;if($n=='defaultcontent')$n=FALSE;
},$prefix);
	$mytemplates = array_filter($mytemplates);
	sort($mytemplates,SORT_LOCALE_STRING);

	foreach($mytemplates as $one)
	{
		$default = ($one == $defaultname);
		$row = new StdClass();
		if($modify)
		{
			$args['template'] = $one;
			$args['mode'] = 'edit';
			$row->name = $mod->CreateLink($id,'settemplate',$returnid,$one,$args);
			$row->editlink = $mod->CreateLink($id,'settemplate',$returnid,$editicon,$args);

			$args['mode'] = 'default';
			$row->default = ($default) ?
				$trueicon:
				$mod->CreateLink($id,'settemplate',$returnid,$falseicon,$args);

			$args['mode'] = 'delete';
			$row->deletelink = ($default) ?
				'':
				$mod->CreateLink($id,'settemplate',$returnid,$deleteicon,$args,$prompt);
		}
		else
		{
			$row->name = $one;
			$row->default = ($default) ? $yes:'';
			$row->editlink = '';
			$row->deletelink = '';
		}
		$rowarray[] = $row;
	}
	if($modify && $dflttpl)
	{
		$row = new StdClass();
		$args['template'] = 'defaultcontent';
		$args['mode'] = 'edit';
		$row->name = $mod->CreateLink($id,'settemplate',$returnid,
			'<em>'.$mod->Lang('default_template_title').'</em>',$args);
		$row->editlink = $mod->CreateLink($id,'settemplate',$returnid,$editicon,$args);

		$row->default = '';

		$reverticon = '<img src="'.$mod->GetModuleURLPath().'/images/revert.gif" alt="'.
		 $mod->Lang('reset').'" title="'.$mod->Lang('reset_tip').
		 '" class="systemicon" onclick="return confirm(\''.$prompt.'\');" />';
		$args['mode'] = 'revert';
		$row->deletelink = $mod->CreateLink($id,'settemplate',$returnid,$reverticon,$args);
		$rowarray[] = $row;
	}

	$smarty->assign($prefix.'items',$rowarray);
	$smarty->assign('parent_module_name',$mod->GetFriendlyName());
	$smarty->assign('titlename',$mod->Lang('name'));
	$smarty->assign('titledefault',$mod->Lang('default'));
	if($modify)
	{
		$args['mode'] = 'add';
		$add = $mod->CreateLink($id,'settemplate',$returnid,$addicon,$args).' '.
			$mod->CreateLink($id,'settemplate',$returnid,$mod->Lang('add_template'),$args);
	}
	else
		$add = '';
	$smarty->assign('add_'.$prefix.'template',$add);

}

smsg_utils::refresh_gateways($this);
$objs = smsg_utils::get_gateways_full($this);
if(!$objs)
{
	echo $this->ShowErrors($this->Lang('error_nogatewaysfound'));
	return;
}

$padm = $this->CheckPermission('AdministerSMSGateways');
$pmod = $padm || $this->CheckPermission('ModifySMSGateways');
$ptpl = $padm || $this->CheckPermission('ModifySMSGateTemplates');
$puse = $this->CheckPermission('UseSMSGateways');

$smarty->assign('padm',$padm);
$smarty->assign('pmod',$pmod);
$smarty->assign('ptpl',$ptpl);
$smarty->assign('puse',$puse);

$smarty->assign('mod',$this);

if(!empty($params['activetab']))
	$showtab = $params['activetab'];
else
	$showtab = 'gates'; //default

$headers = $this->StartTabHeaders();
if($pmod || $puse)
	$headers .=
 $this->SetTabHeader('gates',$this->Lang('gateways'),($showtab=='gates')).
 $this->SetTabHeader('test',$this->Lang('test'),($showtab=='test')).
 $this->SetTabHeader('mobiles',$this->Lang('phone_numbers'),($showtab=='mobiles'));
if($ptpl || $puse)
	$headers .=
 $this->SetTabHeader('enternumber',$this->Lang('enter_number_templates'),($showtab=='enternumber')).
 $this->SetTabHeader('entertext',$this->Lang('enter_text_templates'),($showtab=='entertext'));
if($padm)
	$headers .=
 $this->SetTabHeader('security',$this->Lang('security_tab_lbl'),($showtab=='security'));
$headers .=
 $this->EndTabHeaders().
 $this->StartTabContent();
$smarty->assign('starttabcontent',$headers);
$smarty->assign('endtab',$this->EndTab());
$smarty->assign('endtabcontent',$this->EndTabContent());
$smarty->assign('formend',$this->CreateFormEnd());

if($pmod || $puse)
{
	$smarty->assign('tabstart_gates',$this->StartTab('gates',$params));
	$smarty->assign('formstart_gates',$this->CreateFormStart($id,'savegates'));
	$smarty->assign('reporturl',$this->get_reporturl());

	if($pmod)
	{
		$names = array(-1 => $this->Lang('none'));
		foreach($objs as $key=>&$rec)
		{
			$names[$key] = $rec['obj']->get_name();
			$rec = $rec['obj']->get_setup_form();
		}
		unset($rec);
		$current = $db->GetOne('SELECT alias FROM '.cms_db_prefix().
			'module_smsg_gates WHERE enabled=1 AND active=1');
		if($current == FALSE)
			$current = '-1';

		$smarty->assign('gatecurrent',$current);
		$smarty->assign('gatesnames',$names);
	}
	else
	{
		foreach($objs as $key=>&$rec)
			$rec = $rec['obj']->get_setup_form();
		unset($rec);
	}
	$smarty->assign('gatesdata',$objs);

	$theme = ($this->before20) ? cmsms()->get_variable('admintheme'):
		cms_utils::get_theme_object();

	$smarty->assign('tabstart_test',$this->StartTab('test',$params));
	$smarty->assign('formstart_test',$this->CreateFormStart($id,'smstest'));
	
	$smarty->assign('tabstart_mobiles',$this->StartTab('mobiles',$params));
	$query = 'SELECT * FROM '.cms_db_prefix().'module_smsg_nums ORDER BY id';
	$data = $db->GetAll($query);
	if($data)
	{
		$editicon = $theme->DisplayImage('icons/system/edit.gif',$mod->Lang('edit_tip'),'','','systemicon');
		$deleteicon = $theme->DisplayImage('icons/system/delete.gif',$mod->Lang('deleteone_tip'),'','','systemicon');
		$prompt = $this->Lang('ask_delete_mobile');
		foreach($data as &$row)
		{
			$row = (object)$row;
			if($pmod)
			{
				$args = array('mid'=>$row->id);
				$rec->editlink = $this->CreateLink($id,'edit_mobile','',$editicon,$args);
				$rec->deletelink = $this->CreateLink($id,'del_mobile','',$deleteicon,$args,$prompt);
			}
		}
		unset($row);
		$smarty->assign('numbers',$data);
	}
	else
		$smarty->assign('nonumbers',$this->Lang('nonumbers'));
	if($pmod)
	{
		$text = $this->Lang('add_mobile');
		$addicon = $theme->DisplayImage('icons/system/newobject.gif',$text,'','','systemicon');
		$smarty->assign('add_mobile',$this->CreateLink($id,'edit_mobile','',$addicon).' '.
			$this->CreateLink($id,'edit_mobile','',$text));
	}
}
if($ptpl || $puse)
{
	$tid = 'enternumber';
	$smarty->assign('tabstart_enternumber',$this->StartTab($tid,$params));
	SetupTemplateList($this,$smarty,$ptpl,$padm,
		$id,$returnid,$tid, //tab to come back to
		'enternumber_', //'prefix' of templates' full-name
		SMSG::PREF_ENTERNUMBER_TPLDFLT); //preference holding name of default template

	$tid = 'entertext';
	$smarty->assign('tabstart_entertext',$this->StartTab($tid,$params));
	SetupTemplateList($this,$smarty,$ptpl,$padm,
		$id,$returnid,$tid,'entertext_',SMSG::PREF_ENTERTEXT_TPLDFLT);
}
if($padm)
{
	$smarty->assign('tabstart_security',$this->StartTab('security',$params));
	$smarty->assign('formstart_security',$this->CreateFormStart($id,'savesecurity'));
	$smarty->assign('hourlimit',$this->GetPreference('hourlimit'));
	$smarty->assign('daylimit',$this->GetPreference('daylimit'));
	$smarty->assign('logsends',$this->GetPreference('logsends'));
	$smarty->assign('logdays',$this->GetPreference('logdays'));
	$smarty->assign('logdeliveries',$this->GetPreference('logdeliveries'));
	$pw = $this->GetPreference('masterpass');
	if($pw)
	{
		$s = base64_decode(substr($pw,5));
		$pw = substr($s,5);
	}
	$smarty->assign('masterpass',$pw);
}

$jsfuncs = array();
$jsloads = array();
//show only the frameset for selected gateway
$jsloads[] = <<<EOS
 $('.sms_gateway_panel').hide();
 var \$sel = $('#sms_gateway'), 
    val = \$sel.val();
 $('#'+val).show();

EOS;
if($padm)
{
	$prompt = $this->Lang('sure_ask');
	$jsloads[] = <<<EOS
 \$sel.change(function() {
   $('.sms_gateway_panel').hide();
   var val = $(this).val();
   $('#'+val).show();
 });
 $('input[type="submit"][name$="~delete"]').click(function(ev) {
  var cb = $(this).closest('fieldset').find('input[name$="~sel"]:checked');
  if(cb.length > 0) {
   return confirm('{$prompt}');
  } else {
   return false;
  }
 });

EOS;
	//support property reordering by table-DnD
	$baseurl = $this->GetModuleURLPath();
	$jsincs = <<<EOS
<script type="text/javascript" src="'{$baseurl}/include/jquery.tablednd.min.js"></script>

EOS;
	$jsloads[] = <<<EOS
 $('.gatedata').tableDnD({
  dragClass: 'row1hover',
  onDrop: function(table, droprows) {
   var odd = true;
   var oddclass = 'row1';
   var evenclass = 'row2';
   var droprow = $(droprows)[0];
   $(table).find('tbody tr').each(function() {
    var name = odd ? oddclass : evenclass;
    if (this === droprow) {
     name = name+'hover';
    }
    $(this).removeClass().addClass(name);
    odd = !odd;
   });
  }
 }).find('tbody tr').removeAttr('onmouseover').removeAttr('onmouseout').mouseover(function() {
  var now = $(this).attr('class');
  $(this).attr('class', now+'hover');
 }).mouseout(function() {
  var now = $(this).attr('class');
  var to = now.indexOf('hover');
  $(this).attr('class', now.substring(0,to));
 });

EOS;
}

$jsfuncs[] = <<<EOS
$(document).ready(function() {

EOS;
$jsfuncs = array_merge($jsfuncs,$jsloads);
$jsfuncs[] = <<<EOS
});

EOS;

$smarty->assign('jsincs',$jsincs);
$smarty->assign('jsfuncs',$jsfuncs);

echo $this->ProcessTemplate('adminpanel.tpl');

?>
