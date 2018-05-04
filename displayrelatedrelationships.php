<?php

/**
 * @file
 * Add a table of notes from related contacts.
 *
 * Copyright (C) 2013-15, AGH Strategies, LLC <info@aghstrategies.com>
 * Licensed under the GNU Affero Public License 3.0 (see LICENSE.txt)
 */

require_once 'displayrelatedrelationships.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function displayrelatedrelationships_civicrm_config(&$config) {
  _displayrelatedrelationships_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function displayrelatedrelationships_civicrm_xmlMenu(&$files) {
  _displayrelatedrelationships_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function displayrelatedrelationships_civicrm_install() {
  return _displayrelatedrelationships_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function displayrelatedrelationships_civicrm_uninstall() {
  return _displayrelatedrelationships_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function displayrelatedrelationships_civicrm_enable() {
  return _displayrelatedrelationships_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function displayrelatedrelationships_civicrm_disable() {
  return _displayrelatedrelationships_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function displayrelatedrelationships_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _displayrelatedrelationships_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function displayrelatedrelationships_civicrm_managed(&$entities) {
  return _displayrelatedrelationships_civix_civicrm_managed($entities);
}

/**
 *
 */
function displayrelatedrelationships_civicrm_navigationMenu(&$navMenu) {
  $pages = array(
    'settings_page' => array(
      'label'      => 'Related Relationship Settings',
      'name'       => 'Related Relationship Settings',
      'url'        => 'civicrm/admin/relatedrelationships',
      'parent'    => array('Administer', 'Customize Data and Screens'),
      'permission' => 'access CiviCRM',
      'operator'   => 'AND',
      'separator'  => NULL,
      'active'     => 1,
    ),
  );
  foreach ($pages as $item) {
    // Check that our item doesn't already exist.
    $menu_item_search = array('url' => $item['url']);
    $menu_items = array();
    CRM_Core_BAO_Navigation::retrieve($menu_item_search, $menu_items);
    if (empty($menu_items)) {
      $path = implode('/', $item['parent']);
      unset($item['parent']);
      _displayrelatedrelationships_civix_insert_navigation_menu($navMenu, $path, $item);
    }
  }
}

/**
 * Implementation of hook_civicrm_alterContent
 */
function displayrelatedrelationships_civicrm_alterContent(&$content, $context, $tplName, &$object) {
  if ($context == 'page') {
    if ($tplName == 'CRM/Contact/Page/View/Relationship.tpl') {
      
      if ($object->_action == 16) {
        $marker1 = strpos($content, 'div#contact-summary-relationship-tab');
        $marker = strpos($content, '</div', $marker1);

        $content1 = substr($content, 0, $marker);
        $content3 = substr($content, $marker);
        $content2 = '
          <h3>' . ts('Related Contact Relationships') . '</h3>';
        $contact_id = $object->getVar('_contactId');

        // An array to hold the contacts who are related.
        $related_contact_ids = array();

        try {
          $relTypeResult = civicrm_api3('RelationshipType', 'get', array('options' => array('limit' => 0)));
          $relTypes = $relTypeResult['values'];
        }
        catch (CiviCRM_API3_Exception $e) {
          CRM_Core_Error::debug_log_message('API Error finding relationship types: ' . $e->getMessage());
        }

        // Get relationships where this contact is "A":
        $params = array(
          'sequential' => 1,
          'contact_id' => $contact_id,
          'options' => array('limit' => 0),
        );
        _displayrelatedrelationships_find_relationships($params, $related_contact_ids, $relTypes);
        
        // Template for the links in the table of contributions.
        $rows = array();
        $toggle = 'even';

        foreach ($related_contact_ids as $related_contact) {
          $related_contact_id = $related_contact['contact_id'];
          $displayName = $related_contact['display_name'];
  
          try {
            $related_params = array(
              'sequential' => 1,
              'contact_id' => $related_contact['contact_id'],
              'options' => array('limit' => 0),
            );
            _displayrelatedrelationships_contact_relationships($related_params, $related_relation_ids, $relTypes);
          }
          catch (CiviCRM_API3_Exception $e) {
            // Handle error here.
            $errorMessage = $e->getMessage();
            $errorCode = $e->getErrorCode();
            $errorData = $e->getExtraParams();
            return array(
              'is_error' => 1,
              'error_message' => $errorMessage,
              'error_code' => $errorCode,
              'error_data' => $errorData,
            );
          }

          try {
            $rows[] = '<div class="crm-accordion-wrapper crm-custom_search_form-accordion">
                    <div class="crm-accordion-header crm-master-accordion-header">
                    '.CRM_Utils_System::href($displayName, 'civicrm/contact/view/', 'reset=1&cid=' . $related_contact_id, FALSE).'
                    </div><div class="crm-accordion-body">
                    <table class="selector row-highlight">
            <thead class="sticky">
              <tr>
                <th scope="col">' . ts('Contact Name') . '</th>
                <th scope="col">' . ts('Relationship Type') . '</th>
                <th scope="col">' . ts('Related Contact') . '</th>
              </tr>
            </thead>';
            foreach ($related_relation_ids as $value) {
              $related_contact = $value['contact_id'];
              $related_displayName = $value['display_name'];
              $related_relationship_name = $value['relationship_name'];
              $toggle = ($toggle == 'odd') ? 'even' : 'odd';
              $description = (!empty($value['description'])) ? "<br /><span class='crm-rel-description description'>{$value['description']}</span>" : '';

              $rows[] = '<tr id="rowid' . $related_contact_id . '"class="' . $toggle . '-row crm-relationship_' . $related_contact_id . '">
                <td class="left crm-rel-contact">
                  <span class="nowrap">'.CRM_Utils_System::href($displayName, 'civicrm/contact/view/', 'reset=1&cid=' . $related_contact_id, FALSE).'</span>
                </td>
                <td class="left crm-rel-relationship">
                  <span class="nowrap">'.$related_relationship_name.'</span>'.$description.'
                </td>
                <td class="left crm-rel-second-rel-contact">
                  <span class="nowrap">'.CRM_Utils_System::href($related_displayName, 'civicrm/contact/view/', 'reset=1&cid=' . $related_contact, FALSE).'</span>
                </td>
              </tr>';
            }
            $rows[] = '</table></div></div>';
          }
          catch (CiviCRM_API3_Exception $e) {
            CRM_Core_Error::debug_log_message('API Error finding contributions: ' . $e->getMessage());
          }
        }
        
        $empty_message = '<h3>' . ts('Related Contact Relationships') . '</h3>' .
          '<div class="messages status no-popup"><div class="icon inform-icon">
            There are no related relationships for this contact.
          </div></div>';

        $content2 = empty($rows) ? $empty_message : $content2 . implode("\n", $rows) . '</table>';

        $content = $content1 . $content3 . $content2;
      }
    }
  }
}

/**
 * Find the relationships from the contact.
 *
 * @param array $params
 *   Valid API params.
 * @param array &$related_contact_ids
 *   The contact IDs gathered so far.
 * @param array $relTypes
 *   The available relationship types.
 */
function _displayrelatedrelationships_find_relationships($params, &$related_contact_ids, $relTypes) {
  try {
    $cid = CRM_Utils_Request::retrieve('cid', 'Positive');
    $relationships = civicrm_api3('Relationship', 'get', $params);
    //Civi::log()->debug('', array('relationships' => $relationships));

    foreach ($relationships['values'] as $relationship) {
      if ($relationship['is_active']) {
        $related_contact_ids[$relationship['id']] = [
          'contact_id' => $relationship['cid'],
          'display_name' => $relationship['display_name'],
          'relationship_name' => $relationship['relation'],
        ];
      }
    }
    $result = array();
    foreach($related_contact_ids as $value){
      $contact_id = $value['contact_id'];
      if(isset($result[$contact_id]))
        $index = ((count($result[$contact_id]) - 1) / 2) + 1;
      else
        $index = 1;

      $result[$contact_id]['contact_id'] = $contact_id;
      $result[$contact_id]['display_name'] = $value['display_name'];
      $result[$contact_id]['relationship_name'][] = $value['relationship_name'];
      $result[$contact_id]['description'] = $value['description'];
    }
    $related_contact_ids = array_values($result);
    //Civi::log()->debug('', array('$related_contact_ids' => $related_contact_ids));
  }
  catch (CiviCRM_API3_Exception $e) {
    CRM_Core_Error::debug_log_message('API Error finding relationships: ' . $e->getMessage());
  }
}
/**
 * Find the relationships from the contact.
 *
 * @param array $params
 *   Valid API params.
 * @param array &$related_realtion_ids
 *   The contact IDs gathered so far.
 * @param array $relTypes
 *   The available relationship types.
 */
function _displayrelatedrelationships_contact_relationships($params, &$related_relation_ids, $relTypes) {
  try {
    $cid = CRM_Utils_Request::retrieve('cid', 'Positive');
    $excludedRel =  Civi::settings()->get('relTypes_excluded');
    
    $relationships = civicrm_api3('Relationship', 'get', $params);
    //Civi::log()->debug('_displayrelatedrelationships_contact_relationships', array('relationships' => $relationships));

    $related_relation_ids = array();
    foreach ($relationships['values'] as $relationship) {
      $excluded_relation = $relationship['civicrm_relationship_type_id'].'.label_'.$relationship['rtype'];
      if($relationship['cid'] != $cid && $relationship['is_active'] && !in_array($excluded_relation, $excludedRel) ){
        $related_relation_ids[$relationship['id']] = array(
          'contact_id' => $relationship['cid'],
          'relationship_name' => $relationship['relation'],
          'display_name' => $relationship['display_name'],
          'description' => $relationship['description'],
        );
      }
    }
    //Civi::log()->debug('_displayrelatedrelationships_contact_relationships', array('$related_relation_ids' => $related_relation_ids));
  }
  catch (CiviCRM_API3_Exception $e) {
    CRM_Core_Error::debug_log_message('API Error finding relationships: ' . $e->getMessage());
  }
}
