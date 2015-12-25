<?php
#----------------------------------------------------------------------
# This file is part of CMS Made Simple module: SMSG
# Copyright (C) 2015 Tom Phane <tpgww@onepost.net>
# Refer to licence and other details at the top of file SMSG.module.php
# More info at http://dev.cmsmadesimple.org/projects/smsg
#----------------------------------------------------------------------

if(isset($params['mid']))
{
	$query = 'DELETE FROM '.cms_db_prefix().'module_smsg_nums WHERE id=?';
	$res = $db->Execute($query,array((int)$params['mid']));

	if($res)
		$this->SetMessage($this->Lang('msg_rec_deleted'));
	else
		$this->SetError($this->Lang('error_notfound'));
}

$this->Redirect($id,'defaultadmin','',array('activetab'=>'mobiles'));

?>
