<?php echo $this->Html->docType('xhtml-strict'); ?>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title><?php echo $title_for_layout?></title>
<?php 
  echo $this->Html->charset('UTF-8')."\n";
  echo $this->Html->meta('icon')."\n";
  echo $this->Html->css('default')."\n";
  echo $this->Html->script('jquery-1.5.1.min');
  // jquery ui
  echo $this->Html->css('custom-phtagr/jquery-ui-1.8.14.custom');
  echo $this->Html->script('jquery-ui-1.8.14.custom.min');

  echo $this->Html->script('jquery-phtagr');
  echo $scripts_for_layout; 
  echo $feeds_for_layout;
?>
</head>

<body><div id="page">

<div id="header"><div class="sub">
<h1><?php echo $this->Option->get('general.title', 'phTagr.'); ?></h1>
<span class="subtitle"><?php echo $this->Option->get('general.subtitle', 'Tag Your Photos Once And Find Them Forever'); ?></span>
<?php echo $this->Menu->menu('top-menu'); ?>
</div></div><!-- #header/sub -->

<div id="main-menu"><div class="sub">
<?php echo $this->Menu->menu('main-menu'); ?>
</div></div><!-- #main-menu/sub -->

<div id="main"><div class="sub">

<div id="content" class="content content-<?php echo $this->params['controller']; ?>"><div class="sub">
<?php echo $content_for_layout?>
</div></div><!-- #content/sub -->

</div></div><!-- #main/sub -->

<div id="footer"><div class="sub">
<p>&copy; 2006-2011 by <?php echo $this->Html->link("Open Source Web Gallery phTagr", 'http://www.phtagr.org'); ?></p>
</div></div><!-- #footer/sub -->

</div></body><!-- #page -->
</html>
