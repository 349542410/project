<?php

	namespace Lib11\Jos;

	class JdLogic{

		private $jd;

		public function __construct(){

			$this->jd = new \Lib11\Jos\Jdsdk\Entrance();

		}

		public function test(){

			$this->jd->init(array(),'www.baidu.com',array('hello','world','hhh'));

		}


	}

