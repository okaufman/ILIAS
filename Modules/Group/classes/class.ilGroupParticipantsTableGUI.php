<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesGroup
*/

class ilGroupParticipantsTableGUI extends ilTable2GUI
{
    protected $type = 'admin';
    protected $show_learning_progress = false;
    
    protected static $export_allowed = false;
    protected static $confirmation_required = true;
    protected static $accepted_ids = null;

    /**
     * Constructor
     *
     * @access public
     * @param
     * @return
     */
    public function __construct($a_parent_obj,$a_type = 'admin',$show_content = true,$show_learning_progress = false)
    {
        global $lng,$ilCtrl;
        
        $this->show_learning_progress = $show_learning_progress;
        
        $this->lng = $lng;
        $this->lng->loadLanguageModule('grp');
        $this->lng->loadLanguageModule('trac');
        $this->ctrl = $ilCtrl;
        
        $this->type = $a_type; 
        
        include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
        $this->privacy = ilPrivacySettings::_getInstance();
        
        $this->setId('grp_'.$a_type.'_'.$a_parent_obj->object->getId());
        parent::__construct($a_parent_obj,'members');
		
		$this->initAcceptedAgreements();

        $this->setFormName('participants');

        $this->addColumn('','f',"1");
        $this->addColumn($this->lng->txt('name'),'lastname','20%');
        
        foreach($this->getSelectedColumns() as $col)
        {
            $this->addColumn($this->lng->txt($col),$col);
        }
        
        if($this->show_learning_progress)
        {
            $this->addColumn($this->lng->txt('learning_progress'),'progress');
        }

        if($this->privacy->enabledGroupAccessTimes())
        {
            $this->addColumn($this->lng->txt('last_access'),'access_time_unix');
        }
        if($this->type == 'admin')
        {
            $this->setPrefix('admin');
            $this->setSelectAllCheckbox('admins');
            $this->addColumn($this->lng->txt('grp_notification'),'notification');
            $this->addCommandButton('updateStatus',$this->lng->txt('save'));
        }
        else
        {
            $this->setPrefix('member');
            $this->setSelectAllCheckbox('members');
        }
        $this->addColumn($this->lng->txt(''),'optional');
        $this->setDefaultOrderField('lastname');
        
        $this->setRowTemplate("tpl.show_participants_row.html","Modules/Group");
        
        if($show_content)
        {
            $this->enable('sort');
            $this->enable('header');
            $this->enable('numinfo');
            $this->enable('select_all');
        }
        else
        {
            $this->disable('content');
            $this->disable('header');
            $this->disable('footer');
            $this->disable('numinfo');
            $this->disable('select_all');
        }       
    }
    
    /**
     * Get selectable columns
     * @return 
     */
    public function getSelectableColumns()
    {
        include_once './Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php';
        $ef = ilExportFieldsInfo::_getInstanceByType($this->getParentObject()->object->getType());
        $fields = $ef->getSelectableFieldsInfo();
        
        return $fields;     
    }
    
    
    /**
     * fill row 
     *
     * @access public
     * @param
     * @return
     */
    public function fillRow($a_set)
    {
        global $ilUser,$ilAccess;
        
        $this->tpl->setVariable('VAL_ID',$a_set['usr_id']);
        $this->tpl->setVariable('VAL_NAME',$a_set['lastname'].', '.$a_set['firstname']);
        if(!$ilAccess->checkAccessOfUser($a_set['usr_id'],'read','',$this->getParentObject()->object->getRefId()) and 
            is_array($info = $ilAccess->getInfo()))
        {
            $this->tpl->setVariable('PARENT_ACCESS',$info[0]['text']);
        }
        
        foreach($this->getSelectedColumns() as $field)
        {
            switch($field)
            {
                case 'gender':
                    $a_set['gender'] = $a_set['gender'] ? $this->lng->txt('gender_'.$a_set['gender']) : '';                 
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST',$a_set[$field]);
                    $this->tpl->parseCurrentBlock();
                    break;
                    
                case 'birthday':
                    $a_set['birthday'] = $a_set['birthday'] ? ilDatePresentation::formatDate(new ilDate($a_set['birthday'],IL_CAL_DATE)) : $this->lng->txt('no_date');              
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST',$a_set[$field]);
                    $this->tpl->parseCurrentBlock();
                    break;
                                        
                default:
                    $this->tpl->setCurrentBlock('custom_fields');
                    $this->tpl->setVariable('VAL_CUST',$a_set[$field] ? $a_set[$field] : '');
                    $this->tpl->parseCurrentBlock();
                    break;
            }
        }
        
        if($this->privacy->enabledGroupAccessTimes())
        {
            $this->tpl->setVariable('VAL_ACCESS',$a_set['access_time']);
        }
        
        if($this->show_learning_progress)
        {
            $this->tpl->setCurrentBlock('lp');
            switch($a_set['progress'])
            {
                case LP_STATUS_COMPLETED:
                    $this->tpl->setVariable('LP_STATUS_ALT',$this->lng->txt($a_set['progress']));
                    $this->tpl->setVariable('LP_STATUS_PATH',ilUtil::getImagePath('scorm/complete.gif'));
                    break;
                    
                case LP_STATUS_IN_PROGRESS:
                    $this->tpl->setVariable('LP_STATUS_ALT',$this->lng->txt($a_set['progress']));
                    $this->tpl->setVariable('LP_STATUS_PATH',ilUtil::getImagePath('scorm/incomplete.gif'));
                    break;

                case LP_STATUS_NOT_ATTEMPTED:
                    $this->tpl->setVariable('LP_STATUS_ALT',$this->lng->txt($a_set['progress']));
                    $this->tpl->setVariable('LP_STATUS_PATH',ilUtil::getImagePath('scorm/not_attempted.gif'));
                    break;  

                case LP_STATUS_FAILED:
                    $this->tpl->setVariable('LP_STATUS_ALT',$this->lng->txt($a_set['progress']));
                    $this->tpl->setVariable('LP_STATUS_PATH',ilUtil::getImagePath('scorm/failed.gif'));
                    break;
                                
            }
            $this->tpl->parseCurrentBlock();
        }
        
        
        if($this->type == 'admin')
        {
            $this->tpl->setVariable('VAL_POSTNAME','admins');
            $this->tpl->setVariable('VAL_NOTIFICATION_ID',$a_set['usr_id']);
            $this->tpl->setVariable('VAL_NOTIFICATION_CHECKED',$a_set['notification'] ? 'checked="checked"' : '');
        }
        else
        {
            $this->tpl->setVariable('VAL_POSTNAME','members');
        }
        
        $this->ctrl->setParameter($this->parent_obj,'member_id',$a_set['usr_id']);
        $this->tpl->setVariable('LINK_NAME',$this->ctrl->getLinkTarget($this->parent_obj,'editMember'));
        $this->tpl->setVariable('LINK_TXT',$this->lng->txt('edit'));
        $this->ctrl->clearParameters($this->parent_obj);
        
        $this->tpl->setVariable('VAL_LOGIN',$a_set['login']);
    }
    
    /**
     * Parse user data
     * @param array $a_user_data
     * @return 
     */
    public function parse($a_user_data)
    {
        include_once './Services/User/classes/class.ilUserQuery.php';
        
        $additional_fields = $this->getSelectedColumns();
        unset($additional_fields["firstname"]);
        unset($additional_fields["lastname"]);
        unset($additional_fields["last_login"]);
        unset($additional_fields["access_until"]);
		
        switch($this->type)
        {
            case 'admin':
                $part = ilGroupParticipants::_getInstanceByObjId($this->getParentObject()->object->getId())->getAdmins();
                break;              
            case 'member':
                $part = ilGroupParticipants::_getInstanceByObjId($this->getParentObject()->object->getId())->getMembers();
                break;
        }

        $usr_data = ilUserQuery::getUserListData(
            'login',
            'ASC',
            0,
            999999,
            '',
            '',
            null,
            false,
            false,
            0,
            0,
            null,
            $additional_fields,
            $part
        );
		
        foreach($usr_data['set'] as $user)
        {
            // Check acceptance
            if(!$this->checkAcceptance($user['usr_id']))
            {
				continue;
            }
            // DONE: accepted
            foreach($additional_fields as $field)
            {
                $a_user_data[$user['usr_id']][$field] = $user[$field] ? $user[$field] : '';
            }
        }
        return $this->setData($a_user_data);
    }
    
	/**
	 * Check acceptance
	 * @param object $a_usr_id
	 * @return 
	 */
    public function checkAcceptance($a_usr_id)
    {
        if(!self::$confirmation_required)
        {
            return true;
        }
        if(!self::$export_allowed)
        {
            return false;
        }
        return in_array($a_usr_id,self::$accepted_ids);
    }
    
    
    /**
     * Init acceptance
     * @return 
     */
    public function initAcceptedAgreements()
    {
        if(self::$accepted_ids !== NULL)
        {
            return true;
        }
        
        self::$export_allowed = ilPrivacySettings::_getInstance()->checkExportAccess($this->getParentObject()->object->getRefId());
        self::$confirmation_required = ilPrivacySettings::_getInstance()->groupConfirmationRequired();
		
        include_once './Modules/Course/classes/class.ilCourseAgreement.php';
        self::$accepted_ids = ilCourseAgreement::lookupAcceptedAgreements($this->getParentObject()->object->getId());
    }
    
}
?>
