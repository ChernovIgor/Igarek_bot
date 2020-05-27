<?php 
/*
 Логика игрового автомата
*/
 Class RuletkaLogik {

 	private $koef=0;
 	private $bet = 0;
 	private $pCor = 0;

 	function __construct($bet, $koef) {
 		$this->koef = floatval($koef);
 		$this->bet = $bet;
 	}

	/**
	** @param $corProb - постоянная корректируюция вероятность 0.02 - из реальной убрать 
	** (0,1) - возможные значения 
	**/
	public function setCorreсtProbability($pCor) {
		$this->pCor = $pCor;
	}

	/*	
		но с учемот текущей вероятности, поэтому не нужно 
		чтобы она была большое 0.8 например
		$ymin - 0;
		$ymax - 1;
		x - является ставкой
	*/
		public function SetPCorSing($ymin, $ymax, $x0) {

			$this->pCor = ($this->bet<$x0) ? $ymin : $ymax;

		}

	/*
		Запускает случаюную 
	*/
		public function run() {
			$rand = rand()/getrandmax();
			$pCor = $this->pCor;
			$koef = $this->koef;
			$bet = $this->bet;
			$prize = 0;
		//основная вероятность минус корриктирующая 
			$p = (1/$koef) - $pCor;

			if($rand<=$p) {
			$prize =  (int)floor($koef * $bet); // получается выигрыш округленный до int min 
			return $prize;
		} else {
			return 0;
		}
	}

}