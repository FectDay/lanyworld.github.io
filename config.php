<?PHP
	/*Общие настройки*/
	$config_engine = array(
		/*Название сайта*/
		'name_project' => 'World OF Craft - магазин превелегий',//Название сайта
		'domen' => 'http://new.sandpex.ru',//URL сайта / Пример: https://univix.ru
		/*Настройка обновления информации сайта (кеширования)*/
		'cron_key' => '70bc618au4176530866063hfbf6b127e27',
		/*Настройки базы данных*/
		'db_host' => 'localhost',//Хост базы данных
		'db_user' => 'root',//Имя пользователя в базе данных
		'db_password' => 'rvkt8gS140pbTG5W0N9nyA29ZNFi5yGAw9qR1Go3',//Пароль от базы данных
		'db_name' => 'sandpex',//Название базы данных
		'db_port' => 3306,//Порт базы данных
		/*Настройки UnitPay*/
		'up_secret_key' => '97616210746236f9d5008c84a485495d',//Секретный ключ UnitPay
		'up_market' => '157761-d4298'// ID магазина
	);
	/*Настройки мониторинга*/
	$confmon = array(
		array('name' => 'World OF Craft', 'ip' => '217.106.107.176' , 'port' => '25607')
	);
	/*Настройка доната*/
	$donate[] = array(
		'name_server' => 'Анархия',
		'rcon_host' => '217.106.107.176',
		'rcon_port' => 28607,
		'rcon_password' => 'iborovikov13',
		'donates' => array(
			'elite' => array(
				'name' => '[ Элита ] - 19 руб.',
				'cost' => 20,
				'command' => 'pex user [name] group set [name_pex]',
				'name_pex' => 'elite'
			),
			'shtorm' => array(
				'name' => '[ Шторм ] - 249 руб.',
				'cost' => 5,
				'command' => 'pex user [name] group set [name_pex]',
				'name_pex' => 'shtorm'
			),
			'vizer' => array(
				'name' => '[ Визер ] - 749 руб.',
				'cost' => 1,
				'command' => 'pex user [name] group set [name_pex]',
				'name_pex' => 'vizer'
			)
			'blaze' => array(
				'name' => '[ Блейз ] - 579 руб.',
				'cost' => 1,
				'command' => 'pex user [name] group set [name_pex]',
				'name_pex' => 'blaze'
			),
			'ender' => array(
				'name' => '[ Эндер ] - 579 руб.',
				'cost' => 1,
				'command' => 'pex user [name] group set [name_pex]',
				'name_pex' => 'ender'
			),
			'knaz' => array(
				'name' => '[ Князь ] - 149 руб.',
				'cost' => 1,
				'command' => 'pex user [name] group set [name_pex]',
				'name_pex' => 'knaz'
			),
			'fantom' => array(
				'name' => '[ Фантом ] - 999 руб.',
				'cost' => 1,
				'command' => 'pex user [name] group set [name_pex]',
				'name_pex' => 'fantom'
			),
			'geroy' => array(
				'name' => '[ Герой ] - 79 руб.',
				'cost' => 1,
				'command' => 'pex user [name] group set [name_pex]',
				'name_pex' => 'geroy'
			),
			'straj' => array(
				'name' => '[ Страж ] - 39 руб.',
				'cost' => 1,
				'command' => 'pex user [name] group set [name_pex]',
				'name_pex' => 'straj'
			),
			'd.helper' => array(
				'name' => '[ D.HELPER ] - 7000 руб.',
				'cost' => 1,
				'command' => 'pex user [name] group set [name_pex]',
				'name_pex' => 'd.helper'
			)
			

		)
	);

	
	$timeout = "10000"; //Частота обновления мониторинга в милисикундах 1000 = 1 сек.
?>