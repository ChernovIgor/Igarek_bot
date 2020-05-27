<?php 
require_once 'conf.php';
require_once 'Telegram.php';
require_once 'Dbconnect.php';
require_once 'Payment.php';
require_once 'CommandsTeleg.php';
require_once 'RuletkaLogik.php';


/**
* @param $s string 
* @return bool 
* Проверяет строка является числом или нет  
*/


$pay = new Payment($QiwiToken, $wallet, '-1 month');
$teleg = new Telegram('35.233.136.146:3128', $tokenTlg);
$db = new Dbconnect($host, $db, $pass, $user, $charset);
$comTeleg = new CommandsTeleg($db, $pay, $teleg, $idUser);


$res = $teleg->getUpdate();
$r = json_decode($res);

$id=0;
$idUser = $r->result[$id]->message->chat->id;
$command = $r->result[$id]->message->text;


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
			
			echo $command;
			break;

	}
