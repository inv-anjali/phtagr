<?php
/*
 * phtagr.
 * 
 * Multi-user image gallery.
 * 
 * Copyright (C) 2006-2008 Sebastian Felis, sebastian@phtagr.org
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2 of the 
 * License.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
class PreferencesController extends AppController {

  var $name = 'Preferences';
  var $helpers = array('formular', 'form');
  var $uses = array('Preference', 'Group');

  function beforeFilter() {
    parent::beforeFilter();

    $this->requireRole(ROLE_GUEST);
  }

  function _set($userId, $path, $data) {
    $value = Set::extract($data, $path);
    $this->Preference->setValue($path, $value, $userId);
  }

  function acl() {
    $this->requireRole(ROLE_MEMBER);

    $userId = $this->getUserId();
    if (isset($this->data)) {
      // TODO check valid acl
      $this->_set($userId, 'acl.group', $this->data);
      $this->_set($userId, 'acl.write.meta', $this->data);
      $this->_set($userId, 'acl.write.tag', $this->data);
      $this->_set($userId, 'acl.write.comment', $this->data);
      $this->_set($userId, 'acl.read.preview', $this->data);
      $this->_set($userId, 'acl.read.download', $this->data);
      // debug
      $this->set('commit', $this->data);
      $this->Session->setFlash("Settings saved");
    }
    $tree = $this->Preference->getTree($userId);
    $this->data = $tree;

    $this->set('userId', $userId);
    $groups = $this->Group->findAll("Group.user_id = $userId", null, array('Group.name' => 'ASC'));
    if ($groups) {
      $groups = Set::combine($groups, '{n}.Group.id', '{n}.Group.name');
    } else {
      $groups = array();
    }
    $groups[0] = '[No Group]';
    $this->set('groups', $groups);
  }

  function system() {
    $this->requireRole(ROLE_ADMIN);

    $userId = $this->getUserId();
    if (isset($this->data)) {
      // TODO check valid acl
      $this->_set(0, 'bin.exiftool', $this->data);
      $this->_set(0, 'bin.convert', $this->data);
      $this->_set(0, 'bin.ffmpeg', $this->data);
      $this->_set(0, 'bin.flvtool2', $this->data);

      $this->_set(0, 'google.map.key', $this->data);
      // debug
      $this->set('commit', $this->data);
      $this->Session->setFlash("Settings saved");
    }
    $tree = $this->Preference->getTree($userId);
    $this->Logger->trace($tree);
    $this->data = $tree;
  }

  function getMenuItems() {
    $items = array();
    if ($this->hasRole(ROLE_ADMIN))
      $items[] = array('text' => 'System', 'link' => '/preferences/system');
    if ($this->hasRole(ROLE_MEMBER)) {
      $items[] = array('text' => 'Guest Accounts', 'link' => '/guests');
      $items[] = array('text' => 'Groups', 'link' => '/groups');
    }
    $items[] = array('text' => 'Access Rights', 'link' => '/preferences/acl');
    return $items;
  }

  function beforeRender() {
    $items = $this->getMenuItems();
    $menu = array('items' => $items, 'active' => $this->here);
    $this->set('mainMenu', $menu);
  }
}
?>