<?php /* Smarty version 2.6.30, created on 2018-12-14 12:54:57
         compiled from homepage.html */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <title>MovieLook</title>


 <!--Tab页图标-->
 <link rel="shortcut icon" type="image/x-icon" href="images/logo/logosmall.png" />

 <!--插入样式表-->
 <link rel="stylesheet" type="text/css" href="style/css/style.css" media="all" />
 <link href='http://fonts.googleapis.com/css?family=Amaranth' rel='stylesheet' type='text/css'>
 <link href='http://fonts.googleapis.com/css?family=Droid+Serif:400,400italic,700,700italic' rel='stylesheet' type='text/css'>
 <!--prettyPhoto所需样式表-->
 <link rel="stylesheet" type="text/css" media="screen" href="style/css/prettyPhoto.css" />
 <!--[if IE 7]-->
 <!--<link rel="stylesheet" type="text/css" href="style/css/ie7.css" media="all" />-->
 <!--[endif]-->
 <!--[if IE 8]-->
 <!--<link rel="stylesheet" type="text/css" href="style/css/ie8.css" media="all" />-->
 <!--[endif]-->
 <!--[if IE 9]-->
 <!--<link rel="stylesheet" type="text/css" href="style/css/ie9.css" media="all" />-->
 <!--[endif]-->

 <!--插入js脚本-->
 <script src="http://libs.baidu.com/jquery/1.8.3/jquery.min.js"></script>
 <!--设置多级导航菜单的插件-->
 <script type="text/javascript" src="style/js/ddsmoothmenu.js"></script>
 <!--设置滚动传送的插件-->
 <script type="text/javascript" src="style/js/jquery.jcarousel.js"></script>
 <script type="text/javascript" src="style/js/jquery.prettyPhoto.js"></script>
 <!--这个是自己写的js脚本-->
 <script type="text/javascript" src="style/js/carousel.js"></script>
 <script type="text/javascript" src="style/js/jquery.flexslider-min.js"></script>
 <script type="text/javascript" src="style/js/jquery.masonry.min.js"></script>
 <script type="text/javascript" src="style/js/jquery.slickforms.js"></script>
 <!--scripts.js负责侧栏的时间监控-->
 <script type="text/javascript" src="style/js/scripts.js"></script>
 <script type="text/javascript" src="style/js/slogan.js"></script>
 <!--[if !IE]> -->
 <script type="text/javascript" src="style/js/jquery.corner.js"></script>
 <!-- <![endif]-->
 <link rel="stylesheet" type="text/css" href="style/css/ddsmoothmenu.css" />
 <script type="text/javascript">
    <?php echo ' 
	 ddsmoothmenu.init({
         mainmenuid: "smoothmenu1",
         orientation: \'h\',
         classname: \'ddsmoothmenu\',
         contentsource: [\'smoothmenu1\',\'./smarty/templates/sidebar.html\']	,
         //customtheme: ["#1c5a80", "#18374a"]
     })
	'; ?>
 
 </script>

 <script>
    <?php echo '
     window.onload=function()
     {
         var p1=document.getElementsByTagName("p");
         var num=0;
         function tick(){
             if (num<6)
             {
                 $(\'#beauty\').html(\'“爱，让每一个被爱的人都无可豁免的去爱”——《请以你的名字呼唤我》\');
                 num++;
             }
             if(num>5&&num<=11)
             {
                 $(\'#beauty\').html(\'“你以后想成为什么样的人?”“什么意思，难道我以后就不能成为我自己了吗？”——《阿甘正传》\');
                 num++;
             }
             if(num>11&&num<=16)
             {
                 $(\'#beauty\').html(\'“希望是美好的，也许是人间至善，而美好的事物永不消逝。”——《肖申克的救赎》\');
                 num++;
             }
             if(num>16)
             {
                 $(\'#beauty\').html(\'“凡事都有可能，永远别说永远。”——《放牛班的春天》\');
                 num++;
                 if(num>21){num=0;}
             }

         }
         setInterval(tick, 1000);
         tick();
     }
	'; ?>

 </script>
</head>
<body>
<div id="wrapper">
 <!--menu start-->
 <a class="animateddrawer" id="ddsmoothmenu-mobiletoggle" href="#">
  <span></span>
 </a>
 <div id="smoothmenu1" class="ddsmoothmenu"></div>
 <!--menu end-->
 <div id="welcontent">
  <h1 class="txtwelcome">MovieLook</h1>
  <div id="slogan" class="txtwelcome2" data-text="创造你的电影数字视界"></div>
  <p id="beauty"></p>
 </div>
</div>
</body>
</html>