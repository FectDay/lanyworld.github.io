<?PHP
@define(DIR_SCRIPT, __DIR__);

$main_config = array(
	/*Доступные страницы*/
	'allowed_pages' => array('main','contact', 'info', 'faq', 'rules'),
	/*Страницы, которые могут быть доступны только с авторизацией, должно быть и в allowed_pages*/
	'auth_pages' => array('j87'),
	/*Страницы, для которых подгружать дополнителюную информацию пользователя, должно быть и в auth_pages*/
	'info_auth_user' => array('j87'),
	/*Заголовки страниц*/
	'header_title' => array(
		'contact' => 'Связь с администрацией',
		'info' => 'Возможности донат услуг',
		'faq' => 'Как купить донат',
		'rules' => 'Правила игрового проекта'
	)
);

if(!file_exists(DIR_SCRIPT.'/lib/autoinstall/unitpay_'.md5($config_engine['up_market'].$config_engine['up_secret_key'].$config_engine['db_host'].$config_engine['db_user'].$config_engine['db_password'].$config_engine['db_name']))){
	/*Автоматическая настройка конфига UnitPay с помощью config.php*/
	$unit_pay_config = file_get_contents(DIR_SCRIPT.'/lib/autoinstall/data/unitpay_config.create');
	$unit_pay_config = str_replace('SECRET_KEY_INCLUDE', $config_engine['up_secret_key'], $unit_pay_config);
	$unit_pay_config = str_replace('DB_HOST_INCLUDE', $config_engine['db_host'], $unit_pay_config);
	$unit_pay_config = str_replace('DB_USER_INCLUDE', $config_engine['db_user'], $unit_pay_config);
	$unit_pay_config = str_replace('DB_PASS_INCLUDE', $config_engine['db_password'], $unit_pay_config);
	$unit_pay_config = str_replace('DB_NAME_INCLUDE', $config_engine['db_name'], $unit_pay_config);
	$unit_pay_config = str_replace('DB_PORT_INCLUDE', $config_engine['db_port'], $unit_pay_config);
	if(file_exists(DIR_SCRIPT.'/lib/donate/config.php')){
		unlink(DIR_SCRIPT.'/lib/donate/config.php');
	}
	file_put_contents(DIR_SCRIPT.'/lib/donate/config.php', $unit_pay_config);
	file_put_contents(DIR_SCRIPT.'/lib/autoinstall/unitpay_'.md5($config_engine['up_market'].$config_engine['up_secret_key'].$config_engine['db_host'].$config_engine['db_user'].$config_engine['db_password'].$config_engine['db_name']), json_encode($mysqli));
}

if(!file_exists(DIR_SCRIPT.'/lib/autoinstall/bd_'.md5($config_engine['db_host'].$config_engine['db_user'].$config_engine['db_password'].$config_engine['db_name']))){
	$mysqli = @new mysqli (
        $config_engine['db_host'], $config_engine['db_user'], $config_engine['db_password'], $config_engine['db_name'], $config_engine['db_port']
    );
	$mysqli->set_charset("utf8");
    /* проверка подключения */
    if (mysqli_connect_errno()) {
		echo '<meta charset="utf-8"><style>body {background: black;color: white;font-family: monospace;}</style>';
		echo "<div style='color:red;'><b>Невозможно подключиться к базе данных</b></div><br>";
		echo '<title>Невозможно подключиться к базе данных</title>';
		echo "[CODE] MYSQL: <b>".$mysqli->connect_errno.'</b>';
		echo "<br>[TEXT] MYSQL: <b>".$mysqli->connect_error.'</b>';
        exit();
    }
	/*Добавление unitpay_payments (Нужен для оплаты)*/
	$mysqli->query("CREATE TABLE `unitpay_payments` (`id` int(10) NOT NULL,`unitpayId` varchar(255) NOT NULL,`account` varchar(255) NOT NULL,`sum` float NOT NULL,`itemsCount` int(11) NOT NULL DEFAULT '1',`dateCreate` datetime NOT NULL,`dateComplete` datetime DEFAULT NULL,`status` tinyint(4) NOT NULL DEFAULT '0') ENGINE=InnoDB DEFAULT CHARSET=utf8;");
	$mysqli->query("ALTER TABLE `unitpay_payments`ADD PRIMARY KEY (`id`);");
	$mysqli->query("ALTER TABLE `unitpay_payments` MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;");
	/*Добавление unitpay_log (Нужен для прослеживания нечестных транзакций, а также для блока "последние оплаты")*/
	$mysqli->query("DROP TABLE IF EXISTS `unitpay_log`;CREATE TABLE `unitpay_log` (`id` int(11) NOT NULL COMMENT 'Ид пользователя',`username` varchar(100) NOT NULL DEFAULT '' COMMENT 'Логин пользователя',`sum` int(8) NOT NULL COMMENT 'Сколько заплатил',`pex` varchar(100) NOT NULL COMMENT 'Какую привилегию купил',`server_id` varchar(11) NOT NULL,`pex_command` varchar(155) NOT NULL,`time_put` varchar(50) NOT NULL COMMENT 'Время, когда был выдан донат') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
	$mysqli->query("ALTER TABLE `unitpay_log` ADD PRIMARY KEY (`id`);");
	$mysqli->query("ALTER TABLE `unitpay_log` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Ид пользователя';");
	/*Завершаем установку*/
	file_put_contents(DIR_SCRIPT.'/lib/autoinstall/bd_'.md5($config_engine['db_host'].$config_engine['db_user'].$config_engine['db_password'].$config_engine['db_name']), json_encode($mysqli));
}

function GetImageFromUrl($link) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, 0);
	curl_setopt($ch,CURLOPT_URL,$link);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$result=curl_exec($ch);
	curl_close($ch);
	return $result;
}

function checkRemoteFile($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_NOBODY, 1);
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	if(curl_exec($ch)!==FALSE) {
		return true;
	}else{
		return false;
	} 
}

function passed_time($a){
	 $time = time();
	 $tm = date('H:i', $a);
	 $d = date('d', $a);
	 $m = date('m', $a);
	 $y = date('Y', $a);
	 $last = round(($time - $a)/60);
	 $last_s = round(($time - $a));
	 if( $last < 55 ){
		 if($last == 0){
			  return "$last_s секунд(а) назад";
		 }
		 return "$last минут(ы) назад";
	 }else{
		 if($d.$m.$y == date('dmY',$time)){
			 return "Сегодня в $tm";
		 }else{
			 if($d.$m.$y == date('dmY', strtotime('-1 day'))){
				 return "Вчера в $tm";
			 }else{
				 if($y == date('Y',$time)){
					return "$tm $d/$m"; 
				 }else{
					 return "$tm $d/$m/$y";
				 }
			 }
		 }
	 }
}

function strip_data($text)
{
    $quotes = array ("\x27", "\x22", "\x60", "\t", "\n", "\r", "*", "%", "<", ">", "?", "!" );
    $goodquotes = array ("-", "+", "#" );
    $repquotes = array ("\-", "\+", "\#" );
    $text = trim( strip_tags( $text ) );
    $text = str_replace( $quotes, '', $text );
    $text = str_replace( $goodquotes, $repquotes, $text );
    $text = ereg_replace(" +", " ", $text);
            
    return $text;
}
?>