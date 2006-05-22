<?php

session_start();

$prefix='./phtagr';

include "$prefix/User.php";
include "$prefix/Sql.php";
include "$prefix/Search.php";
include "$prefix/Edit.php";

include "$prefix/PageBase.php";
include "$prefix/SectionHeaderLeft.php";
include "$prefix/SectionHeaderRight.php";
include "$prefix/SectionMenu.php";
include "$prefix/SectionHome.php";
include "$prefix/SectionFooter.php";
include "$prefix/SectionHelp.php";

include "$prefix/SectionAccount.php";

include "$prefix/SectionExplorer.php";
include "$prefix/SectionImage.php";
include "$prefix/SectionBrowser.php";
include "$prefix/SectionSearch.php";
include "$prefix/SectionSetup.php";
include "$prefix/SectionUpload.php";

$page = new PageBase("page");

$hdr = new SectionBase('header');
$headerleft = new SectionHeaderLeft();
$hdr->add_section($headerleft);
$headerright = new SectionHeaderRight();
$hdr->add_section(&$headerright);

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

$db = new Sql();
$user = new User();

if (!$db->connect() && $_REQUEST['section']!="setup")
{
  $msg = new SectionBase();
  $cnt->add_section(&$msg);
  $msg->h("Database Error");
  $text="It looks as if phtagr is not completely configured.<br/>\n".
	"Please follow <a href=\"./index.php?section=setup&amp;action=install\"> ".
    "this</a> link to install phtagr.\n";
  $msg->p($text);
  
  $menu->add_menu_item("Configure", "index.php?section=setup&amp;action=install");
  $page->layout();
  exit;
}

$user->check_session();

$pref=$db->read_pref($user->get_userid());

$menu->add_menu_item("Home", "index.php");
$menu->add_menu_item("Explorer", "index.php?section=explorer");
$menu->add_menu_item("Search", "index.php?section=search");

if ($user->can_browse())
{
  $menu->add_menu_item("Browser", "index.php?section=browser");
}
if ($user->can_upload())
{
  $menu->add_menu_item("Upload", "index.php?section=upload");
}
if ($user->is_admin())
{
  $menu->add_menu_item("Account", "index.php?section=account&amp;action=new");
  $menu->add_menu_item("Setup", "index.php?section=setup");
}

$search= new Search();
$search->from_URL();

if (isset($_REQUEST['section']))
{
  $section=$_REQUEST['section'];
    
  if ($user->is_member() && 
      $_REQUEST['section']=='account' && isset($_REQUEST['pass-section']))
  {
    $section=$_REQUEST['pass-section'];
  } 

  if ($_REQUEST['section']=='account' && $_REQUEST['action']=='logout')
  {
    $section='home';
  }
  
  if($section=='account')
  {
    $account= new SectionAccount();
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
  } 
  else if($section=='image')
  {
    if($_REQUEST['action']=='edit')
    {
      $edit=new Edit();
      $edit->execute();
      print_r($edit);
      unset($edit);
    }
    $image= new SectionImage();
    $cnt->add_section(&$image);
  } 
  else if($section=='search')
  {
    $search= new SectionSearch();
    $cnt->add_section(&$search);
  } 
  else if($section=='browser')
  {
    if ($user->can_browse()) {
      $browser = new SectionBrowser();
      $browser->root='';
      $browser->path='';
      $cnt->add_section(&$browser);
    } else {
      $login = new SectionLogin();
      $login->section=$section;
      $login->message="You are not loged in!";
      $cnt->add_section(&$login);
    }
  } 
  else if($section=='setup')
  {
    if (!$db->link || $user->is_admin()) 
    {
      $setup=new SectionSetup();
      $cnt->add_section(&$setup);
    } else {
      $login=new SectionAccount();
      $login->message='You are not loged in as an admin';
      $login->section='setup';
      $cnt->add_section(&$login);
    }
  }
  else if($section=='upload')
  {
    if ($user->can_upload())
    {
      $upload = new SectionUpload();
      $cnt->add_section(&$upload);
    }
  }
  else if($section=='help')
  {
    $help = new SectionHelp();
    $cnt->add_section(&$help);
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


/*
echo "<pre>";
print_r($_SESSION);
echo "</pre>\n";
*/
?>
