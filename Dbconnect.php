<?php 

	Class Dbconnect {
		private $pdo;

		function __construct($host, $db, $pass,$user, $charset) {
			$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    		$opt = [
       	 		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        		PDO::ATTR_EMULATE_PREPARES   => false,
    		];
    		$this->pdo = new PDO($dsn, $user, $pass, $opt);
		}

		public function GetBalance($idUser) {
			$query = 'SELECT balance FROM users WHERE id=?';
			$stmt = $this->pdo->prepare($query);
			$stmt->execute([$idUser]);
			return  $stmt->fetch()['balance'];	
		}

		private function SetBalance($idUser, $balance) {
			$query = 'UPDATE users  SET balance = ? WHERE id=?';
			$stmt = $this->pdo->prepare($query);
			$stmt->execute([$balance, $idUser]);
			return  true;	
		}

		/**
		* @return boolean; 
		**/
		private function ExixstUser($idUser) {
			$query = 'SELECT id FROM users WHERE id=?';
			$stmt = $this->pdo->prepare($query);
			$stmt->execute([$idUser]);
			return  (bool)($stmt->fetch());
		}


		public function SetBank($idUser, $bank) {
			$query = 'UPDATE users  SET bank = ? WHERE id=?';
			$stmt = $this->pdo->prepare($query);
			$stmt->execute([$bank, $idUser]);
			return true;
		}

		public function GetBank($idUser) {
			$query = 'SELECT bank FROM users WHERE id=?';
			$stmt = $this->pdo->prepare($query);
			$stmt->execute([$idUser]);
			return $stmt->fetch()['bank'];
		}

		public function SetTel($idUser, $tel) {
			$query = 'UPDATE users  SET tel = ? WHERE id=?';
			$stmt = $this->pdo->prepare($query);
			$stmt->execute([$tel, $idUser]);
			return true;
		}

		public function GetTel($idUser) {
			$query = 'SELECT tel FROM users WHERE id=?';
			$stmt = $this->pdo->prepare($query);
			$stmt->execute([$idUser]);
			return $stmt->fetch()['tel'];
		}


		
		
		public function SetBet($idUser, $bet) {
			$query = 'UPDATE users  SET bet = ? WHERE id=?';
			$stmt = $this->pdo->prepare($query);
			$stmt->execute([$bet, $idUser]);
			return true;
		}

		public function GetBet($idUser) {
			$query = 'SELECT bet FROM users WHERE id=?';
			$stmt = $this->pdo->prepare($query);
			$stmt->execute([$idUser]);
			return $stmt->fetch()['bet'];
		}
		
		/*
			Вывод текущего состояния 
		*/
		public function GetState($idUser) {
			$query = 'SELECT state FROM users WHERE id=?';
			$stmt = $this->pdo->prepare($query);
			$stmt->execute([$idUser]);
			return $stmt->fetch()['state'];
		}

		/*
		 установка состояния для ответа на сообщение
		*/
		public function SetState($idUser, $state) {
			$query = 'UPDATE users  SET state = ? WHERE id=?';
			$stmt = $this->pdo->prepare($query);
			$stmt->execute([$state, $idUser]);
			return true;
		}
		/*
			subMoney - сколько снять денег
		*/
		public function SetStateSubMoney($idUser) {
			$s = 'subMoney';
			$query = 'UPDATE users  SET state = ? WHERE id=?';
			$stmt = $this->pdo->prepare($query);
			$stmt->execute([$s, $idUser]);
			return true;	
		}
		/*
			поставить состояние по умолчанию
		*/
		public function SetStateDefault($idUser) {
			$s = 'default';
			$query = 'UPDATE users  SET state = ? WHERE id=?';
			$stmt = $this->pdo->prepare($query);
			$stmt->execute([$s, $idUser]);
			return true;
		}
		
		public function ExistTransaction($idTrns) {
			$query = 'SELECT id FROM transatcions WHERE id=?';
			$stmt = $this->pdo->prepare($query);
			$stmt->execute([$idTrns]);
			return  (bool)($stmt->fetch());	
		}

		public function SetTransaction($idTrns) {
			if(!$this->ExistTransaction($idTrns)) {
				$query = 'INSERT INTO transatcions (id) VALUES (?)';
				$stmt = $this->pdo->prepare($query);
				$stmt->execute([$idTrns]);
				return true;
			} else {
				return false;
			}
		}

		public function AddUser($idUser) {
			
			if(!$this->ExixstUser($idUser)) {
				//addUser
				$query = 'INSERT INTO users (id) VALUES (?)';
    			$stmt = $this->pdo->prepare($query);
    			$stmt->execute([$idUser]);
    			return true;	
			} else{
				return false;
			}
		}

		public function addMoney($idUser, $addMoney) {
			$money = $this->GetBalance($idUser);
			$summ = (int)($money) + (int)($addMoney);
			$this->SetBalance($idUser, $summ);
			return true;
		}

		public function subMoney($idUser, $sumMoney) {
			$money = $this->GetBalance($idUser);
			$summ = (int)($money) - (int)($sumMoney);
			if($summ < 0) return false;
			$this->SetBalance($idUser, $summ);
			return true;	
		}
	}