<?php  
	$attribut = array(
		"Efisien",
		"Error",
		"Susah",
		"Mudah",
		"Hilang",
		"Banyak",
		"Kurang",
		"Lama",
		"Tidak",
		"Cepat",
		"Rumit",
		"Kesalahan",
		"Belum",
		"Gangguan",
		"Gagal",
		"Penipu",
		"Bohong",
		"Ribet",
		"Parah"
	);

	$neg0 = array(
		13,
		12,
		13,
		14,
		12,
		11,
		12,
		12,
		12,
		10,
		10,
		13,
		13,
		10,
		11,
		13,
		13,
		12,
		9
	);

	$neg1 = array(
		1,
		2,
		1,
		0,
		2,
		3,
		2,
		2,
		2,
		4,
		4,
		1,
		1,
		4,
		3,
		1,
		1,
		2,
		5
	);

	$pos0 = array(
		2,
		2,
		3,
		2,
		3,
		2,
		3,
		1,
		0,
		2,
		4,
		6,
		6,
		6,
		3,
		6,
		6,
		3,
		6
	);

	$pos1 = array(
		1,
		1,
		0,
		1,
		0,
		1,
		0,
		2,
		3,
		1,
		2,
		0,
		0,
		0,
		3,
		0,
		0,
		3,
		0
	);

	$count = count($attribut);


	$total_data = 20;
	$total_entropy = ((-3/$total_data)*LOG(3/$total_data,2))+((-14/$total_data)*LOG(14/$total_data,2));

	$jumlah0 = array();
	$jumlah1 = array();
	$entropy0 = array();
	$entropy1 = array();
	$gain = array();

	foreach ($attribut as $key => $value) {

		$jumlah0[$key] = $neg0[$key]+$pos0[$key];
		$jumlah1[$key] = $neg1[$key]+$pos1[$key];

		if ($neg0[$key]!=0 && $pos0[$key]!=0) {
			$entropy0[$key] = ((-$pos0[$key]/$jumlah0[$key])*LOG($pos0[$key]/$jumlah0[$key],2))+((-$neg0[$key]/$jumlah0[$key])*LOG($neg0[$key]/$jumlah0[$key],2));
		}else{
			$entropy0[$key] = 0;
		}
		
		if ($neg1[$key]!=0 && $pos1[$key]!=0) {
			$entropy1[$key] = ((-$pos1[$key]/$jumlah1[$key])*LOG($pos1[$key]/$jumlah1[$key],2))+((-$neg1[$key]/$jumlah1[$key])*LOG($neg1[$key]/$jumlah1[$key],2));
		}else{
			$entropy1[$key] = 0;
		}

		$gain[$key] = (($total_entropy)-(($jumlah0[$key]/$total_data)*$entropy0[$key])-(($jumlah1[$key]/$total_data)*$entropy1[$key]));
	}
?> 
<!DOCTYPE html>
<html>
<head>
	<title>C4,5</title>
	<style>
		table {
			font-family: arial, sans-serif;
			border-collapse: collapse;
			width: 100%;
		}

		td, th {
			border: 1px solid #dddddd;
			text-align: left;
			padding: 8px;
		}

		thead {
			background-color: #dddddd;
		}
	</style>
</head>
<body>
	<h1><center>C4,5</center></h1>
	<table>
		<thead>
			<tr>
				<th>No. </th>
				<th>Attribut</th>
				<th>Neg(0)</th>
				<th>Pos(0)</th>
				<th>Jumlah</th>
				<th>Entropy(0)</th>
				<th>Neg(1)</th>
				<th>Pos(1)</th>
				<th>Jumlah</th>
				<th>Entropy(1)</th>
				<th>Gain</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($attribut as $key => $value): ?>
				<tr>
					<td><?= ($key+1) ?></td>
					<td><?= $value ?></td>
					<td><?= $neg0[$key] ?></td>
					<td><?= $pos0[$key] ?></td>
					<td><?= $jumlah0[$key] ?></td>
					<td><?= $entropy0[$key] ?></td>
					<td><?= $neg1[$key] ?></td>
					<td><?= $pos1[$key] ?></td>
					<td><?= $jumlah1[$key] ?></td>
					<td><?= $entropy1[$key] ?></td>
					<td><?= $gain[$key] ?></td>
				</tr>
			<?php endforeach ?>
		</tbody>
	</table>
</body>
</html>