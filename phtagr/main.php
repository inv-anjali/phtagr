<?php

session_start();

include_once("$phtagr_lib/User.php");
include_once("$phtagr_lib/Database.php");
include_once("$phtagr_lib/Config.php");
include_once("$phtagr_lib/Search.php");
include_once("$phtagr_lib/Edit.php");

include_once("$phtagr_lib/PageBase.php");
include_once("$phtagr_lib/SectionLogo.php");
include_once("$phtagr_lib/SectionQuickSearch.php");
include_once("$phtagr_lib/SectionMenu.php");
include_once("$phtagr_lib/SectionHome.php");
include_once("$phtagr_lib/SectionFooter.php");
include_once("$phtagr_lib/SectionHelp.php");

include_once("$phtagr_lib/SectionAccount.php");

include_once("$phtagr_lib/SectionExplorer.php");
include_once("$phtagr_lib/SectionBulb.php");
include_once("$phtagr_lib/SectionImage.php");
include_once("$phtagr_lib/SectionBrowser.php");
include_once("$phtagr_lib/SectionSearch.php");
include_once("$phtagr_lib/SectionUpload.php");
include_once("$phtagr_lib/SectionInstall.php");
include_once("$phtagr_lib/SectionAdmin.php");
include_once("$phtagr_lib/SectionMyAccount.php");

$page = new PageBase("phTagr");

$hdr = new SectionBase('header');
$logo = new SectionLogo();
$hdr->add_section($logo);
$qsearch = new SectionQuickSearch();
$hdr->add_section(&$qsearch);

$page->add_section(&$hdr);

$body = new SectionBase("body");
$page->add_section(&$body);

$menu = new SectionMenu();
$body->add_section(&$menu);

$cnt = new SectionBase("content");
$body->add_section(&$cnt);

$footer = new SectionBase("footer");
$fcnt = new SectionFooter("content");
$footer->add_section(&$fcnt);
$page->add_section(&$footer);

$db = new Database();
$user = new User();

$section="";
$action="";

if (isset($_REQUEST['section']))
  $section=$_REQUEST['section'];
if (isset($_REQUEST['action']))
  $action=$_REQUEST['action'];

if ($section=="install")
{
  $conf=new Config(0);
  $install = new SectionInstall();
  $cnt->add_section(&$install);
  $page->layout();
  return;
}

if (!$db->connect() && $section!="install")
{
  $msg = new SectionBase();
  $cnt->add_section(&$msg);
    
  $conf=new Config(0);
  $msg->h(_("No Installation found"));
  $link=sprintf("<a href=\"./index.php?section=install\">%s</a>",
    _("this link"));
  $text=sprintf(_("It looks as if phtagr is not completely configured. ".
    "Please follow %s to install phtagr."), $link);
  $msg->p($text);
  
  $page->layout();
  return;
}

$user->check_session();
$conf=new Config($user->get_id());

$search= new Search();
$search->from_URL();

$menu=new SectionMenu('menu', _("Menu"));
$menu->set_item_param('section');

$menu->add_item('home', _("Home"));
$menu->add_item('explorer', _("Explorer"));
if ($user->is_member() && $user->get_num_users()>1)
{
  $submenu=new SectionMenu('menu','');
  $submenu->add_param('section', 'explorer');
  $submenu->set_item_param('user');
  $submenu->add_item($user->get_id(), _("My images"));
  $menu->add_submenu('explorer', $submenu);
}
$menu->add_item('search', _("Search"));

if ($user->can_browse())
{
  $menu->add_item('browser', _("Browser"));
}
if ($user->is_member())
{
  $menu->add_item('myaccount', _("MyAccount"));
}
if ($user->is_admin())
{
  $menu->add_item('admin', _("Administration"));
}

if (isset($_REQUEST['section']))
{
  $section=$_REQUEST['section'];
    
  if (!$user->is_anonymous() && 
      $_REQUEST['section']=='account' && isset($_REQUEST['goto']))
  {
    // We need to unset the action field otherwise we might
    // execute an action we did not intend to perform.
    unset ($_REQUEST['action']);
    $section=$_REQUEST['goto'];
  } 

  if ($_REQUEST['section']=='account' && $_REQUEST['action']=='logout')
  {
    $section='home';
  }
  
  if($section=='account')
  {
    $account=new SectionAccount();
    $cnt->add_section(&$account);
  }
  else if($section=='explorer')
  {
    if($_REQUEST['action']=='edit')
    {
      $edit=new Edit();
      $edit->execute();
      unset($edit);
    }
    $explorer= new SectionExplorer();
    $cnt->add_section(&$explorer);
    $bulb = new SectionBulb();
    $body->add_section(&$bulb);
  } 
  else if($section=='image' && isset($_REQUEST['id']))
  {
    if($_REQUEST['action']=='edit')
    {
      $edit=new Edit();
      $edit->execute();
      unset($edit);
    }
    $image= new SectionImage(intval($_REQUEST['id']));
    $cnt->add_section(&$image);
  } 
  else if($section=='search')
  {
    $seg_search= new SectionSearch();
    $cnt->add_section(&$seg_search);
  } 
  else if($section=='browser')
  {
    if ($user->can_browse()) {
      $browser = new SectionBrowser();
      // Set roots of users filesystem
      $roots=$conf->get('path.fsroot[]');
      if (!$user->is_admin())
        $browser->reset_roots();
      if (count($roots)>0)
      {
        foreach ($roots as $root)
          $browser->add_root($root, '');
      }
      $cnt->add_section(&$browser);
    } else {
      $login = new SectionLogin();
      $login->section=$section;
      $login->message=_("You are not loged in!");
      $cnt->add_section(&$login);
    }
  }
  else if($section=='myaccount')
  {
    if ($user->is_member())
    {
      $myaccount=new SectionMyAccount();
      $cnt->add_section(&$myaccount);
    }
    else
    {
      $login=new SectionAccount();
      $login->message=_('You have to be logged in to access the queried page.');
      $login->section='myaccount';
      $cnt->add_section(&$login);
    }
  } 
  else if($section=='admin')
  {
    if (!$db->link || $user->is_admin()) 
    {
      $admin=new SectionAdmin();
      $cnt->add_section(&$admin);
    } else {
      $login=new SectionAccount();
      $login->message=_('You have to be logged in to access the queried page.');
      $login->section='admin';
      $cnt->add_section(&$login);
    }
  }
  else if($section=='help')
  {
    $help = new SectionHelp();
    $cnt->add_section(&$help);
  }
  else if($section=='install')
  {
    $install = new SectionInstall();
    $cnt->add_section(&$install);
  }
  else {
    $home = new SectionHome();
    $cnt->add_section(&$home);
  }
} else {
  $home = new SectionHome();
  $cnt->add_section(&$home);
}

$page->layout();

?>