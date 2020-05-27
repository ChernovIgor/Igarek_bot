<?php
Class CommandsTeleg {
	private $db;
	private $pay;
	private $teleg;
	private $idUser;

/*
$db - класс для работы с БД
$teleg - класс для работы а отправкой сообщений в телеграмм
$idUser - id человека кому отправляется сообщение
*/
public function __construct($db, $pay, $teleg, $idUser) {
	$this->db = $db;
	$this->pay = $pay;
	$this->teleg = $teleg;
	$this->idUser = $idUser;
}

/*
	проверка на дробное десятичное число
*/
private function DefBet($s) {
	$pattern = '/^\d+\.?\d*$/';
	preg_match($pattern, $s, $match);
	return count($match[0])>0 ? true : false;
}
/*
	проверка на целое число
*/
private function DefNumber($s) {
	$pattern = '/^\d+$/';
	preg_match($pattern, $s, $match);
	return count($match[0])>0 ? true : false;
}


/*
	Отправка хозяину о списании средств с 

*/
	private function sendFounder($idFounder, $money) {
		$db = $this->db;
		$idUser = $this->idUser;
		$bank = $db->GetBank($idUser);
		$tel = $db->GetTel($idUser);
		$teleg = $this->teleg;
		$str = <<<EOD
		Перекинуть
		тел  $tel
		руб  $money
		банк $bank
		EOD;
		$response = array(
		'chat_id' => $idFounder,
		'text' => $str,
		'parse_mode' => 'HTML',
		);
		$teleg->sendMessage($response);
	} 

	/*
	Переводит в состояние bet
*/
	public function play() {
		$db = $this->db;
		$teleg = $this->teleg;
		$idUser = $this->idUser;

		$str = <<<EOD
		Введите стакву в руб:
		Например: 54
		EOD;

		$response = array(
			'chat_id' => $idUser,
			'text' => $str,
			'parse_mode' => 'HTML',
		);
		$teleg->sendMessage($response);
		$db->SetState($idUser, 'bet');
	}

/*
 Переводит в состояние 'bank'
*/
 public function SetBank() {
 	$db = $this->db;
 	$teleg = $this->teleg;
 	$idUser = $this->idUser;

 	$str = 'Введите банк:';
 	$replyMarkup = array(
 		'keyboard' => array(array("Сбербанк", "Киви")), 
 		'resize_keyboard' => true,     
 		'one_time_keyboard' => true
 	);
 	$encodedMarkup = json_encode($replyMarkup);
 	$response = array(
 		'chat_id' => $idUser,
 		'text' => $str,
 		'parse_mode' => 'HTML',
 		'reply_markup' => $encodedMarkup
 	);
 	$teleg->sendMessage($response);
 	$db->SetState($idUser, 'bank');
 }

 
/*
	переводит в состояние тел
*/
	public function SetTel() {
		$db = $this->db;
		$teleg = $this->teleg;
		$idUser = $this->idUser;

		$str = 
		'Введите свой номер телефона привязанный к банку
		Пример 9964013050';
		$db->SetState($idUser, 'tel');
		$response = array(
			'chat_id' => $idUser,
			'text' => $str,
			'parse_mode' => 'HTML'
		);
		$teleg->sendMessage($response);	
	}
/*
	Переводит в состояние 'subMoney'
*/
	public function subMoney() {
		$db = $this->db;
		$teleg = $this->teleg;
		$idUser = $this->idUser;

		$message = '';
		$bank = $db->GetBank($idUser);
		$tel = $db->GetTel($idUser);
		if($bank == null) {
			$this->SetBank($db, $teleg, $idUser);

		} elseif ($tel==null) {
			$this->SetTel($db, $teleg, $idUser);
		} else {
			$balance = $db->GetBalance($idUser);		
			$bank = $db->GetBank($idUser);
			$db->SetState($idUser, 'subMoney');
			$str = <<<EOD
			<b>Введите сумму:</b> 
			Пример: 103
			<b>Баланс: $balance</b>
			Банк: $bank
			Тел: $tel
			Изменить номер /setTel
			Изменить банк /setBank
			EOD;	
			$response = array(
				'chat_id' => $idUser,
				'text' => $str,
				'parse_mode' => 'HTML'
			);
			$teleg->sendMessage($response);
		}
	}

/*
Вывод информаций и приложении
*/
public function info() {
	$teleg = $this->teleg;
	$idUser = $this->idUser;
	
	$str = <<<EOD
	<b>Пополнение счета</b>
	Qiwi кошелек: +7(996)401-30-50
	Комментарий к платежу: $idUser
	После оплаты выполните комманду /addMoney ,
	для проверки оплаты
	<b><i>При пополнении не ошибитесь</i></b>
	Задать телефон /setTel
	Задать банк /setBank
	В главное меню /start
	EOD;
	$response = array(
		'chat_id' => $idUser,
		'text' => $str,
		'parse_mode' => 'HTML'
	);

	$teleg->sendMessage($response);
}



public function start() {
	$db = $this->db;
	$teleg = $this->teleg;
	$idUser = $this->idUser;
	
	$db->AddUser($idUser); 
	$balance = $db->GetBalance($idUser);
	$str = <<<EOD
	Ваш счет: $balance р
	Играть /play
	Пополнить счет /addMoney
	Вывести /subMoney
	Инфо /info
	EOD;

	$response = array(
		'chat_id' => $idUser,
		'text' => $str,
		'parse_mode' => 'HTML'
	);

	$teleg->sendMessage($response);
}


public function addMoney() {

	$pay = $this->pay;
	$idUser = $this->idUser;
	$db = $this->db;
	$teleg = $this->teleg;

	$strOut = '';
	$trnInfo = $pay->selectTransaction($idUser);

	if($db->SetTransaction($trnInfo['txnId'])) {
		//если транзакции не было в базе
		$db->addMoney($idUser, $trnInfo['balance']);
		$strOut = "Баланс пополнен на {$trnInfo['balance']} р 
		В главное меню /start";
	} else {
		//в любом другом случае
		$strOut = "Что-то пошло не так 
		В главное меню /start";
	}

	$response = array(
		'chat_id' => $idUser,
		'text' => $strOut,
		'parse_mode' => 'HTML'
	);
	$teleg->sendMessage($response);
}



public function State_koef($command) {
	$db = $this->db;
	$teleg = $this->teleg;
	$idUser = $this->idUser;

	if($this->DefBet($command)) {
		$koef = $command;
		$bet = $db->GetBet($idUser);
		
		//_______________________________
		$ymin = -0.2;
		$ymax = 0.2;
		$x0 = 250;
		$rl = new RuletkaLogik($bet, $koef);
		$rl->SetPCorSing($ymin, $ymax, $x0);
		$prize = $rl->run();
		//_______________________________
		$db->SetStateDefault($idUser);
		$db->addMoney($idUser, $prize);
		$str = "Ваш выигрыш {$prize} р";
	} 

	$response = array(
		'chat_id' => $idUser,
		'text' => $str,
		'parse_mode' => 'HTML'
	);
	$teleg->sendMessage($response);

	$db->SetStateDefault($idUser);
	$this->start();
}

public function State_bet($command) {
	$db = $this->db;
	$teleg = $this->teleg;
	$idUser = $this->idUser;

	if($this->DefNumber($command)) {
		$bet = $command;
		$db->SetBet($idUser, $bet);
		if($db->subMoney($idUser, $bet)) {
			$str = <<<EOD
			Введите коэффициент:
			Например: 1.5
			EOD;

			$db->SetState($idUser, 'koef');
		} else {
			$str = 'У вас не хватает средств';
		}

	} else {
		$str = 'Неправильный формат ввода';
	}

	$response = array(
		'chat_id' => $idUser,
		'text' => $str,
		'parse_mode' => 'HTML'
	);
	$teleg->sendMessage($response);

}



public function State_subMoney($command) {
	$db = $this->db;
	$idUser = $this->idUser;
	$teleg = $this->teleg;
	$str = '';

	if($this->DefNumber($command)) {
		$money = (int)$command;

		//переписать норм
		$this->sendFounder(465167096, $money); 
		
		$str = <<<EOD
		Средства сняты
		Главная /start
		EOD;
	} else {
		$str = <<<EOD
		Нельзя снять мешьше, чем есть на счете
		Главная /start
		EOD;
	}
	$db->subMoney($idUser, $money);
	$response = array(
		'chat_id' => $idUser,
		'text' => $str,
		'parse_mode' => 'HTML'
	);
	$db->SetStateDefault($idUser);
	$teleg->sendMessage($response);

}
public function State_tel($command) {
	$db = $this->db;
	$idUser = $this->idUser;
	$teleg = $this->$teleg;
	
	$bank = $db->GetBank($idUser);
	$tel = $db->GetTel($idUser);

	if($this->DefNumber($command)) {
		$db->SetTel($idUser, $command);
		$db->SetStateDefault($idUser);
		$this->subMoney($db, $teleg, $idUser);
	} else {
		$str = 'Неверный фомат номера';
		$response = array(
			'chat_id' => $idUser,
			'text' => $str,
			'parse_mode' => 'HTML',
		);
		$teleg->sendMessage($response);
	}
}

public function State_bank($command) {
	$db = $this->db;
	$idUser = $this->idUser;
	$teleg = $this->teleg;

	$db->SetBank($idUser, $command);
	$db->SetStateDefault($idUser);

	//убираю клавиатуру
	$str = 'Принял';
	$replyMarkup = array(
		'remove_keyboard' => true
	);
	$encodedMarkup = json_encode($replyMarkup);
	$response = array(
		'chat_id' => $idUser,
		'text' => $str,
		'parse_mode' => 'HTML',
		'reply_markup' => $encodedMarkup
	);
	$teleg->sendMessage($response);
	$this->subMoney($db, $teleg, $idUser);
}


}