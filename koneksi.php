<?php
	$host="127.0.0.1";
	$user="root";
	$password="";
	$database="jelli_20200614";

	$koneksi=mysqli_connect($host,$user,$password,$database);

	if ($koneksi){
		// echo "Berhasil Terhubung";
	} else {
		echo "Gagal Terhubung";
	}
	//mysqli_close($koneksi);
	function escape_string($s){
		$host="localhost";
		$user="root";
		$password="";
		$database="jelli_20200614";
		$koneksi=mysqli_connect($host,$user,$password,$database);
		return mysqli_real_escape_string($koneksi,$s);
	}
?>
