<?php
return array(
	'levels' => array(
		'none' =>                0,
		'neophyte' =>           50,
		'novice' =>            250,
		'apprentice' =>       5000,
		'journeyman' =>      20000,
		'craftsman' =>      100000,
		'artisan' =>        250000,
		'adept' =>          500000,
		'expert' =>        1000000,
		'lo_master' =>     2000000,
		'um_master' =>     4000000,
		'on_master' =>    16000000,
		'ee_master' =>    64000000,
		'pa_master' =>   256000000,
		'mon_master' =>  512000000,
		'zez_master' => 1024000000,
		'ftl_master' => 2048001002,
	),
	'runecost' => array(
		array( 1, 2, 3, 4, 5, 6, 7),
		array( 2, 3, 3, 4, 4, 5, 6),
		array( 5, 6, 7, 8,10,12,14),
		array( 6, 8,12,17,19,23,29),
	),
	'runes' => array(
		array( 'LO', 'UM',  'ON',  'EE',  'PA', 'MON', 'ZEZ'),
		array( 'YA', 'VI',  'OH', 'FUL',  'EZ',  'ZO',  'ES'),
		array('BRO', 'GE', 'DES',  'IR', 'YON','KATH','THEM'),
		array('LEH','UZH','DAIN','NETA', 'SAR',  'RA', 'KOR'),
	),
	
	'CAST' => array(
		'FUL' => 'Torch',
		'FUL,IR' => 'Firebolt',
		'OH,YON' => 'Spy',
		'OH,GE,UZH' => 'Confuse',
	) ,
	
	'BREW' => array(
		'YA' => 'HealPotion',
	) ,
		
);