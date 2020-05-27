<?php 


class Payment {
	 private $headers; // заголовок киви кошелька
	 private $token; // токен киви кошелька
	 private $ans; // json всех поступлений за период
	 private $wallet; // номер на который поступает платеж
	 private $min; //поличество минут с момента пополнения показывать //-2 month '-30 minutes'
	 /*
		1) Извлечь норм кол-во платежей в все их обработать 
		2) Ограничить время поступление средств -15 munutes (Как вариант)
	 */
	function __construct($token,$wallet, $min) {
		$this->wallet = $wallet;		
		$this->$token = $token;
		$this->min = $min;
		$this->headers = array(
			'Accept: application/json',
			'Content-type: application/json',
			'Authorization: Bearer ' . $this->$token
		);
	}

	

	/*
	 После upBalance - полениния всех данных за опред период 
	 идет поиск нужного пополения (если оно есть) 
	 если да тогда записать id транзакции и пополнить баланс в базе данных
	*/
	
	/**
	* если известна предыдущая транзакция этого пользователя тогда сравнить с текущей 
	* если она больше значит транзакция новая
	* 	integer @idUser --  
	*	return [txnId, comment, money] 
	**/
// пока человек не дождется пополнения счета он не будет проводить еще один платеж
	public function selectTransaction($idUser) {
		$aPay = json_decode($this->getTransHis());
		$status = 'SUCCESS';	
		$currency = 643; // валюта рубли

		
		foreach ($aPay->data as $pay) {

			if(($idUser == $pay->comment) && 
				$pay->total->currency==$currency &&
				$pay->status == $status) {
					$balance = (int)round($pay->sum->amount);
					return ['idUser' => $idUser,'txnId' => $pay->txnId, 'balance' => $balance];
				}
			}
			return false;
		}
		
	
	// добавить минуты, чтобы можно было бы быстро изменять их
	/*
		Извлекает все поступления за указанны период
	*/
	public function getTransHis() {
		$url = 'https://edge.qiwi.com/payment-history/v2/persons/' . $this->wallet . '/payments';
		$startDate = new DateTime('now' , new DateTimeZone("+5")); // utc +5
		$endDate = clone $startDate;
		$startDate->modify($this->min);

		$data = array(
			'rows' => '10', 
			'operation' => 'IN',
			'startDate' => urlencode($startDate->format('c')),
			'endDate' => urlencode($endDate->format('c'))
		);

		$params = '';
    	foreach($data as $key=>$value)
            $params .= $key.'='.$value.'&';
         
        $params = trim($params, '&');
	
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url.'?'.$params );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
		$response = curl_exec($ch);
		curl_close ($ch);
		$this->ans = $response; // засунуть в свойство
		return $response;
	}
}