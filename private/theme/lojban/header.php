<?php
getSection('functions');
?>
<!DOCTYPE html>
<html><head>
  <title><?php echo getHTMLTitle(); ?></title>
  <?php metaDescriptionTag(); metaKeywordsTag(); metaAuthorTag(); metaViewportTag(); ?>
  <?php stylesheetTag(); ?>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
<div class="gridWrapper">
<header class="mainHeader">
<?php getSection('usermenu'); ?>
<h1><?php echo blogTitle(); ?></h1>
<hr/>
<ul class="topBar">
  <li><a href="<?php echo getPageURL(1);?>"><span>lo casnu be mi</span></a></li>
  <li><a href="<?php echo getPageURL(2);?>"><span>lo nabmi befi lo kibystu</span></a></li>
  <li><a href="<?php echo getPageURL(3);?>"><span>la'o gy. English gy.</span></a></li>
</ul>
</header>
<?php getSection('right'); ?>

