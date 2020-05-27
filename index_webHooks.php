<?php 
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once 'conf.php';
require_once 'Telegram.php';
require_once 'Dbconnect.php';
require_once 'Payment.php';
require_once 'CommandsTeleg.php';

$pay = new Payment($QiwiToken, $wallet, '-1 month');
$teleg = new Telegram('138.197.32.120:3128', $tokenTlg);
$db = new Dbconnect($host, $db, $pass, $user, $charset);
$res = $teleg->getWebHook();

$idUser = $res['message']['chat']['id'];
$command = $res['message']['text'];

$comTeleg = new CommandsTeleg($db, $pay, $teleg, $idUser);

	switch ($command) {
		case '/start':
		// проверяем пользователя на существование и добавляем если нужно
			$comTeleg->start();				
		break;

		case '/play':
			$comTeleg->play();
			
		break;
		
		case '/subMoney':
			$comTeleg->subMoney();
		break;

		case '/setBank':
			$comTeleg->SetBank();
		break;

		case '/setTel':
		 	$comTeleg->SetTel();
		break;
	
		case '/addMoney': 
			$comTeleg->addMoney();
		break;


		case '/info':
			$comTeleg->info();
		break;
		
		default:
				
			$state = $db->GetState($idUser);

			switch ($state) {
			//снятие денег
			case 'subMoney': 	
				$comTeleg->State_subMoney($command);
			break;

			case 'bank':
				$comTeleg->State_bank($command);
			break;
							
			case 'tel':
				$comTeleg->State_tel($command);
			break;

			case 'bet':
				$comTeleg->State_bet($command);
			break;

			case 'koef':
				$comTeleg->State_koef($command);
			break;

			default:
				# code...
			break;
			}		
			

	}
