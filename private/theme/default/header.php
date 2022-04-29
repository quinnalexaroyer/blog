<!DOCTYPE html>
<html><head>
  <title><?php echo getHTMLTitle(); ?></title>
  <?php metaDescriptionTag(); metaKeywordsTag(); metaAuthorTag(); metaViewportTag(); ?>
  <?php stylesheetTag(); ?>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
<header class="mainHeader">
<?php getSection('usermenu'); ?>
<h1><?php echo blogTitle(); ?></h1>
<hr/>
</header>
<div class="gridWrapper">
<?php getSection('left'); getSection('right'); ?>

