<?php
/*
	Класс для отправки и принятия сообщений в телеграмм	
*/

	Class Telegram {
		private $proxy;
		private $token;
		private $url = 'https://api.telegram.org/bot';
		
		function __construct($proxy, $token) {
			$this->proxy = $proxy;
			$this->token = $token;
		}

		public function getWebHook() {
			$data = file_get_contents('php://input');
			return json_decode($data, true);
		}

		public function getUpdate() {
			//offset=-1 инвертирует обновления и возращает одно в обратном порядке
			$ch = curl_init( $this->url . $this->token . '/' . 'getUpdates' . '?' . 'offset=-1'); //. '?' . 'limit=1' 
			// подключение к прокси-серверу
			curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    		curl_setopt( $ch, CURLOPT_HEADER, false );
    		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
    		curl_setopt( $ch, CURLOPT_POST, true );
    		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
			$res = curl_exec($ch);
			curl_close($ch);
			return $res;
		}

		public function sendMessage($content) {
			$ch = curl_init( $this->url . $this->token . '/' . 'sendMessage');  
			// подключение к прокси-серверу
			curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($content));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

			$res = curl_exec($ch);
			curl_close($ch);
			return $res;
		}

		
	
}