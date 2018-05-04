<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2018                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2018
 */

require_once 'CRM/Admin/Form/Setting.php';

/**
 * This class generates form components for Relationship Type.
 */
class CRM_Admin_Form_Setting_relatedrelationships extends CRM_Admin_Form_Setting {
  
  /**
   * Set default values for the form.
   *
   * Default values are retrieved from the database.
   */
  public function setDefaultValues() {
    if (!$this->_defaults) {
      $this->_defaults = array();
      $this->_defaults['relTypes_excluded'] = Civi::settings()->get('relTypes_excluded');
      $this->_defaults['contactFields_included'] = Civi::settings()->get('contactFields_included');
    }
    return $this->_defaults;
  }
  

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    CRM_Utils_System::setTitle(ts('Related Relationship Settings'));
    $relTypeResult = civicrm_api3('RelationshipType', 'get', array('options' => array('limit' => 0)));
    $relTypes = $relTypeResult['values'];
    $relType = array(ts('- select -'));
    foreach($relTypes as $key=>$value){
      if( isset($value['label_a_b']) && isset($value['label_b_a']) ) {
        
        $relType[$value['id'].'.label_a_b'] = 'Relationship A to B : '.$value['label_a_b'];
        $relType[$value['id'].'.label_b_a'] = 'Relationship B to A : '.$value['label_b_a'];
      }
    }

    $this->addElement('advmultiselect', 'relTypes_excluded', ts('Select Relationship Type to Exclude'), $relType, array('class' => 'crm-select2', 'size' => 10, 'style' => 'width:300px', 'multiple' => 1));
    
    $entities = array(
      'contact',
      'address',
    );
    
    $fields = array();
    foreach ($entities as $entity) {
      $getFields = civicrm_api3($entity, 'getfields');
      foreach ($getFields['values'] as $field => $info){
        $fields[$field] = $info['title'];
      }
    }
    $this->addElement('advmultiselect', 'contactFields_included', ts('Select Fields to Display'), $fields, array('class' => 'crm-select2', 'size' => 10, 'style' => 'width:300px', 'multiple' => 1));
   
    $this->addFormRule(array('CRM_Admin_Form_Setting_relatedrelationships', 'formRule'), $this);
    
    parent::buildQuickForm();
  }

  /**
   * Global form rule.
   *
   * @param array $fields
   *   The input form values.
   * @param array $files
   *   The uploaded files if any.
   * @param array $options
   *   Additional user data.
   *
   * @return bool|array
   *   true if no errors, else array of errors
   */
  public static function formRule($fields, $files, $options) {
    $errors = array();
    if (array_key_exists('contactFields_included', $fields) && is_array($fields['contactFields_included'])) {
      $count = count($fields['contactFields_included']);
      if($count > 5){
        $errors['contactFields_included'] = ts('Slect maximum 5 Fields to display.');
      }
    }
    return $errors;
  }
  
  /**
   * postProcess the form object.
   */
  public function postProcess() {
    $params = $this->controller->exportValues($this->_name);
    
    Civi::settings()->set('relTypes_excluded', $params['relTypes_excluded']);
    Civi::settings()->set('contactFields_included', $params['contactFields_included']);
    CRM_Core_Config::clearDBCache();
    CRM_Utils_System::flushCache();
    CRM_Core_Resources::singleton()->resetCacheCode();
    CRM_Core_Session::setStatus(" ", ts('Changes Saved'), "success");
  }
}
