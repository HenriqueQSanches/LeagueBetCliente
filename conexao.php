<?php
ini_set('memory_limit', '-1');
try{
		$conexao = new PDO('mysql:host=localhost;dbname=banca_esportiva', 'root', '');
		$conexao ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}catch(PDOException $e){
		echo 'ERROR: ' . $e->getMessage();
	}
