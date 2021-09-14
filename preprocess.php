<!DOCTYPE html>
<html>

<head>
  <title>SKRIPSI</title>
  <meta charset="UTF-8" />
  <link rel="stylesheet" type="text/css" href="styles/style.css" />
  <!--[if IE 6]><link rel="stylesheet" type="text/css" href="styles/ie6.css" /><![endif]-->
</head>

<body>
  <div id="page">
    <?php
    $page = "preprocess";
    include('navbar.php');
    ?>
    <div id="content">
      <br />
      <br />
      <table align="center" width="700px" border="1" cellpadding="10">
        <tr>
          <th height="400px" width="700px" colspan="2" bgcolor="#F0FFFF">
            <?php
            include "koneksi.php";
            $i = 0;
            $word = '';
            $sql = mysqli_query($koneksi, "SELECT * FROM data_latih");
            $numRow = mysqli_num_rows($sql);
            while ($data = mysqli_fetch_assoc($sql)) {
              $word = $word . ' ' . $data['tweet_text'];
            }
            mysqli_free_result($sql);
            unset($sql, $data);
            echo "<br><br><b>Hasil Proses <i>Crawling</i> : </b><br>";
            print_r($word);
            $i++;

            //Case Folding
            $word = strtolower($word);
            $d = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '-', '/', '\\', ',', '.', '#', ':', ';', '\'', '"', '[', ']', '{', '}', ')', '(', '|', '`', '~', '!', '%', '$', '^', '&', '*', '=', '?', '+', '–');
            $word = str_replace($d, '', $word); // Hilangkan karakter yang telah disebutkan di array $d

            echo "<b>Hasil proses <i>Case Folding </i>:</b><br> " . $word;

            //Tokenisasi
            $word = explode(" ", $word);

            $tmp_kata = [];
            for ($j = 0; $j < sizeof($word); $j++) {
              $word[$j] = trim($word[$j]);
              if ($word[$j] != '') {
                if (strpos($word[$j], '@') === false) {
                  $tmp_kata[] = $word[$j];
                }
              }
            }

            $word = array_filter($tmp_kata);
            $word = array_values($word);
            echo "<br><br><b>Hasil Proses <i>Tokenizing</i> : </b><br>";
            print_r($word);
            //filtering                            
            $stopwords = file_get_contents("stopwords.txt");
            $stopwords = preg_split("/[\s]+/", $stopwords);
            $word = array_diff($word, $stopwords);
            //menjadikan array yang bernilai kosong
            $word = array_filter($word);
            $word = array_values($word);
            echo "<br><br><b>Hasil proses <i>Filtering : </b></i><br>";
            print_r($word);
            echo "<br><br>";

            //stemming
            require_once('NaziefAdriani.php');
            foreach ($word as $t => $value) {
              $word[$t] = Stemmer($value);
            }
            /*end of stemming*/

            $word = array_filter($word);
            $word = array_values($word);
            //$judul = implode(" ", $kalimat);
            echo "<b>Hasil proses <i>Stemming</i> Nazief & Adriani : </b><br>";
            print_r($word);
            echo "<br><br>";
            ?>

            <?php
            $i = 0;
            // $cekpreprocess = mysqli_query($koneksi, "SELECT * FROM hasil_preprocess");
            // $numRow = mysqli_num_rows($cekpreprocess);
            echo '<table border=1><tr><th>No</th><th>Tweet</th></tr>';
            mysqli_query($koneksi, "TRUNCATE TABLE hasil_preprocess");
            if (is_array($word)) {
              foreach ($word as $key => $value) {
                $s = mysqli_real_escape_string($koneksi, $value);
                $query_check = "SELECT * FROM hasil_preprocess WHERE tweet_text = '{$s}'";
                $numRow = mysqli_query($koneksi, $query_check);
                if (mysqli_num_rows($numRow) == 0) {
                  mysqli_query($koneksi, "INSERT INTO hasil_preprocess (tweet_text) VALUES ('{$s}')");
                  echo "<tr><td>" . ($i + 1) . "</td><td>" . $value . "</td></tr>";
                }
              }
            }
            echo '</table>';
            ?>
          </th>
        </tr>
      </table>
    </div>
  </div>
</body>

</html>
