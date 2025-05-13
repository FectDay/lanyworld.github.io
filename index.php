<html>
<?php
	$start = microtime(true);
	define(PHP_DIR, __DIR__);
	include('config.php');
	include('function.php');
?>
<link rel='stylesheet' id='crayon-css'  href='styles/css/style.css?hash=<?=md5_file(PHP_DIR."/styles/css/style.css")?>' type='text/css' media='all' />
<script src="styles/js/jquery-3.4.1.min.js?hash=<?=md5_file(PHP_DIR."/styles/js/jquery-3.4.1.min.js")?>"></script>

<?if($_GET['page'] == 'info'){ ?>
<script src="styles/css/info-page/jquery.fancybox.js?hash=<?=md5_file(PHP_DIR."/styles/css/info-page/jquery.fancybox.js")?>"></script>
<link rel="stylesheet" href="styles/css/info-page/jquery.fancybox.css"/>
<?}?>

<link rel="stylesheet" href="styles/css/font-awesome.min.css"/>
<link rel="stylesheet" href="styles/css/lobibox.min.css"/>
<meta name="verification" content="8022c01d7a83583a6df9f3dd64b415" />
<link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
<meta name="viewport" content="width=device-width, initial-scale=0.8, height=device-height, minimum-scale=0.8, user-scalable=0">
<meta charset="utf-8">
<? /*Заголовок главной страницы*/
if(@$_GET['page'] == 'main' || !@$_GET['page']){?>
<title><?=$config_engine['name_project']?> - World OF Craft</title>
<?}else{
if(file_exists('tpl/'.@$_GET['page'].'.php')){
	if(in_array(@$_GET['page'], $main_config['allowed_pages']) && !empty($main_config['header_title'][$_GET['page']])){
	?>	
		<title><?=$config_engine['name_project']?> - <?=$main_config['header_title'][$_GET['page']]?></title>
	<?
	}else{
	?>	
		<title><?=$config_engine['name_project']?> - World OF Craft</title>
	<?
	}
}
?>
<?}?>
</head>
<body>
<div class="body-site">
<div style="display: none;" id="domen"><?=$config_engine['domen']?></div>

<div style="display:none;" class="hamburger-menu">
	<div class="right-hamburger-menu">
		<div class="button-a-href-g">Меню Сайта</div>
		<a href="/" class="button-a-href">Главная</a>
		<a href="/faq" class="button-a-href">Донат</a>
		<a href="/info" class="button-a-href">Возможности</a>
		<a href="/contact" class="button-a-href">Контакты</a>
		<a href="/rules" class="button-a-href">Правила</a>
	</div>
</div>

<div class="head-conent">
	<div id="up_block" class="wrapper">
		<div class="content-head">
			<a href="/main" class="logotype">
				<div class="logotype-text">SandPex</div>
				<div class="online-all"><div class="online-players">Онлайн: 4951</div></div>
			</a>
			<div class="head-menu">
				<a href="/" class="main-head-link a1">
					<div class="ico-haed-link"></div>
					<div class="text-haed-link">Главная</div>
				</a>
				<a href="/faq" class="main-head-link a2">
					<div class="ico-haed-link ico-2"></div>
					<div class="text-haed-link">Донат</div>
				</a>
				<a href="/info" class="main-head-link a3">
					<div class="ico-haed-link ico-3"></div>
					<div class="text-haed-link">Возможности</div>
				</a>
				<a href="/contact" class="main-head-link a4">
					<div class="ico-haed-link ico-4"></div>
					<div class="text-haed-link">Контакты</div>
				</a>
				<a href="/rules" class="main-head-link a5">
					<div class="ico-haed-link ico-5"></div>
					<div class="text-haed-link">Правила</div>
				</a>
			</div>
			<div class="right-head-element main">
				<div class="head-decoration-1"></div>
				<div class="text-right-head">
					<div class="ico-text-right-head"></div>
					<div class="title-text-right-head">Консоль</div>
				</div>
			</div>
			<div style="display:none;" class="right-head-element humburger">
				<div class="head-decoration-1"></div>
				<div class="text-right-head">
					<div class="hanmt">
						<svg class="ham hamRotate ham4" viewBox="0 0 100 100" width="80" onclick="this.classList.toggle('active')"> <path class="line top" d="m 70,33 h -40 c 0,0 -8.5,-0.149796 -8.5,8.5 0,8.649796 8.5,8.5 8.5,8.5 h 20 v -20"></path><path class="line middle" d="m 70,50 h -40"></path><path class="line bottom" d="m 30,67 h 40 c 0,0 8.5,0.149796 8.5,-8.5 0,-8.649796 -8.5,-8.5 -8.5,-8.5 h -20 v 20"></path> </svg>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
		<? /*Загрузка страницы, если была выбрана главная страница*/
		if(@$_GET['page'] == 'main' || !@$_GET['page']){?>
		
		<?}?>
		<?/*Загрузка страниц*/
		if(!@$_GET['page'] or @$_GET['page'] == 'main'){
			include('tpl/main.php');
		}else{
			if(file_exists('tpl/'.@$_GET['page'].'.php')){
				if(in_array(@$_GET['page'], $main_config['allowed_pages'])){
					if(in_array(@$_GET['page'], $main_config['auth_pages'])){
						if(!empty($_SESSION['login'])){
							include('tpl/'.@$_GET['page'].'.php');
						}else{
							/*Ошибка, если страница требует авторизации*/
							?>
								<div id="up_block" class="wrapper">
									<div class="content">
								<div class="error-block">
									<div class="rules_name">Страница недоступна!</div>
									<div class="error-panel">
										<div class="message-title">Авторизируйтесь пожалуйста, чтобы войти на данную страницу!</div>
									</div>
								</div>
								</div>
								</div>
							<?
						}
					}else{
						include('tpl/'.@$_GET['page'].'.php');
					}
				}else{
					/*Ошибка, если страница не разрешена в function.php allowed_pages*/
				?>
					<div id="up_block" class="wrapper">
					<div class="content">
					<div class="error-block">
						<div class="rules_name">Страница недоступна!</div>
						<div class="error-panel">
							<div class="message-title">Это страница была запрещена для перехода!</div>
						</div>
					</div>
					</div>
					</div>
				<?
				}
		}else{
				?>
					<div id="up_block" class="wrapper">
					<div class="content">
					<div class="error-block">
						<div class="rules_name">Страница не найдена!</div>
						<div class="error-panel">
							<div class="message-title">Страницы не существует, либо она была удалена.</div>
						</div>
					</div>
					</div>
					</div>
				<?
		}
		}?>
<div class="footer">
	<div class="footer-content">
		<div class="logo-footer">
			<div class="logotype-footer">SandPex</div>
			<div class="footer-coop">SANDPEX.RU - 2020 </div>
		</div>
		<div class="logo-coop-payment">	
			<div class="link-payment-logo"></div>
			<div class="link-payment-logo" style="background-image: url(/img/footer-link-2.png);width: 68px;margin-top: 4px;margin-right: 22px;"></div>
			<div class="link-payment-logo" style="background-image: url(/img/footer-link-3.png);width: 52px;margin-top: 5px;margin-right: 10px;height: 28px;"></div>
			<div class="link-payment-logo" style="background-image: url(/img/footer-link-4.png);width: 103px;margin-right: 18px;margin-top: 10px;height: 35px;"></div>
			<div class="link-payment-logo last" style="background-image: url(/img/footer-link-5.png);width: 103px;margin-top: 14px;height: 35px;"></div>
		</div>
		<div class="right-footer">
			<a href="https://vk.com/sandpex" class="vk-link"></a>
		</div>
	</div>
</div>
<script src="styles/js/main.js?hash=<?=md5_file(PHP_DIR."/styles/js/main.js")?>"></script>
<script src="styles/js/lobibox.js"></script>
 <div class="time_load_page" style="display:none;"><? echo 'Время полной загрузки: ' . ( microtime(true) - $start ) . ' сек.';?></div>
</div>
</body>
</html>
