<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesAdvancedMetaData
*/
include_once('Services/Table/classes/class.ilTable2GUI.php');
include_once('Services/MetaData/classes/class.ilMDCopyrightSelectionEntry.php');

class ilMDCopyrightTableGUI extends ilTable2GUI
{
	protected $lng = null;
	protected $ctrl;
	protected $parent_obj;
	protected $has_write; // [bool]
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($a_parent_obj,$a_parent_cmd = '',$a_has_write = false)
	{
	 	global $DIC;

	 	$lng = $DIC['lng'];
	 	$ilCtrl = $DIC['ilCtrl'];
	 	
	 	$this->lng = $lng;
	 	$this->ctrl = $ilCtrl;
		$this->has_write = (bool)$a_has_write;
	 	
	 	parent::__construct($a_parent_obj,$a_parent_cmd);
		
		if($this->has_write)
		{
			$this->addColumn('','f',1);
			$this->addColumn($lng->txt("order"), "order");
			$this->addCommandButton("saveCopyrightPosition", $lng->txt("meta_save_order"));
		}
	 	$this->addColumn($this->lng->txt('title'),'title',"30%");
	 	$this->addColumn($this->lng->txt('md_used'),'used',"5%");
	 	$this->addColumn($this->lng->txt('md_copyright_preview'),'preview',"50%");
		$this->addColumn($this->lng->txt('meta_copyright_status'),'status',"5%");
		
		if($this->has_write)
		{
			$this->addColumn('','edit',"10%");
		}
	 	
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.show_copyright_row.html","Services/MetaData");
		$this->setDefaultOrderField("order");
		$this->setDefaultOrderDirection("asc");
	}
	
	/**
	 * Fill row
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function fillRow($a_set)
	{
		if($this->has_write)
		{
			if($a_set['default'])
			{
				$this->tpl->setVariable('DISABLED',"disabled");
			}
			$this->tpl->setVariable('VAL_ID',$a_set['id']);

			// order
			if($a_set["position"])
			{
				$this->tpl->setCurrentBlock("order");
				$this->tpl->setVariable("ORDER_NAME", "order[c_".$a_set["id"]."]");
				if($a_set["position"] == 0) {
					$position = 10;
				} else {
					$position = (int)$a_set['position']*10;
				}
				$this->tpl->setVariable("ORDER_VALUE", $position);
				$this->tpl->parseCurrentBlock();
			}

		}
		$this->tpl->setVariable('VAL_TITLE',$a_set['title']);
		if(strlen($a_set['description']))
		{
			$this->tpl->setVariable('VAL_DESCRIPTION',$a_set['description']);
		}
		$this->tpl->setVariable('VAL_USAGE',$a_set['used']);
		$this->tpl->setVariable('VAL_PREVIEW',$a_set['preview']);
		if($a_set['status']){
			$this->tpl->setVariable('VAL_STATUS', $this->lng->txt('meta_copyright_outdated'));
		} else {
			$this->tpl->setVariable('VAL_STATUS', $this->lng->txt('meta_copyright_in_use'));
		}
		
		if($this->has_write)
		{
			$this->ctrl->setParameter($this->getParentObject(),'entry_id',$a_set['id']);
			$this->tpl->setVariable('EDIT_LINK',$this->ctrl->getLinkTarget($this->getParentObject(),'editEntry'));
			$this->ctrl->clearParameters($this->getParentObject());

			$this->tpl->setVariable('TXT_EDIT',$this->lng->txt('edit'));

			if((int)$a_set['used'] > 0)
			{
				$this->tpl->setCurrentBlock("link_usage");
				$this->ctrl->setParameter($this->getParentObject(),'entry_id',$a_set['id']);
				$this->tpl->setVariable('USAGE_LINK',$this->ctrl->getLinkTarget($this->getParentObject(),'showCopyrightUsages'));
				$this->ctrl->clearParameters($this->getParentObject());

				$this->tpl->setVariable('TXT_USAGE',$this->lng->txt('meta_copyright_show_usages'));
				$this->tpl->parseCurrentBlock();
			}
		}
	}
	
	/**
	 * Parse records
	 *
	 * @access public
	 * @param array array of record objects
	 * 
	 */
	public function parseSelections()
	{
		$entries = ilMDCopyrightSelectionEntry::_getEntries();

	 	foreach( $entries as $entry)
	 	{
			$tmp_arr['id'] = $entry->getEntryId();
			$tmp_arr['title'] = $entry->getTitle();
			$tmp_arr['description']	= $entry->getDescription();
			$tmp_arr['used'] = $entry->getUsage();
			$tmp_arr['preview'] = $entry->getCopyright();
			$tmp_arr['default'] = $entry->getIsDefault();
			$tmp_arr['status'] = $entry->getOutdated();
			$tmp_arr['position'] = $entry->getOrderPosition();
			
			$entry_arr[] = $tmp_arr;
	 	}

	 	$this->setData($entry_arr ? $entry_arr : array());
	}
} 


?>