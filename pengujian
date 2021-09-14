<!DOCTYPE html>
<html>

<head>
  <title>SKRIPSI</title>
  <meta charset="UTF-8" />
  <link rel="stylesheet" type="text/css" href="styles/style.css" />
  <link rel="stylesheet" href="vendor/treant-js/Treant.css" type="text/css" />
  <!--[if IE 6]><link rel="stylesheet" type="text/css" href="styles/ie6.css" /><![endif]-->
  <style>
    .mytable {
      font-family: arial, sans-serif;
      border-collapse: collapse;
      width: 100%;
    }

    .mytd,
    .myth {
      border: 1px solid #dddddd;
      text-align: left;
      padding: 8px;
    }

    .myhead {
      background-color: #dddddd;
    }

    @media print,
    screen {
      .choosed {
        background-color: #03fc35;
      }
    }

    .choosed {
      background-color: #03fc35;
    }

    .child {
      border: 1px solid #dddddd;
    }
  </style>
</head>

<body>
  <div id="page">
    <?php
    $page = "pengujian";
    include('navbar.php');
    ?>
    <div id="content">

      <body>
        <?php
        // Excel reader from http://code.google.com/p/php-excel-reader/
        require('vendor/spreadsheet-reader/php-excel-reader/excel_reader2.php');
        require('vendor/spreadsheet-reader/SpreadsheetReader.php');
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        include "koneksi.php";
        function getPrediction($sentence, $koneksi) {
          $word = strtolower($sentence);
          $d = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '-', '/', '\\', ',', '.', '#', ':', ';', '\'', '"', '[', ']', '{', '}', ')', '(', '|', '`', '~', '!', '@', '%', '$', '^', '&', '*', '=', '?', '+', '–');
          $word = str_replace($d, '', $word); // Hilangkan karakter yang telah disebutkan di array $d

          //Tokenisasi
          $word = explode(" ", $word);
          $word = array_filter($word);
          $word = array_values($word);

          //filtering                            
          $stopwords = file_get_contents("stopwords.txt");
          $stopwords = preg_split("/[\s]+/", $stopwords);
          $word = array_diff($word, $stopwords);
          //menjadikan array yang bernilai kosong
          $word = array_filter($word);
          $word = array_values($word);

          //stemming
          require_once('NaziefAdriani.php');
          foreach ($word as $t => $value) {
            $word[$t] = Stemmer($value);
          }
          /*end of stemming*/

          $word = array_filter($word);
          $word = array_values($word);

          // hitung bayes
          $query_bayes = "SELECT * FROM naive_bayes_props WHERE attribute IN ('" . implode("', '", $word) . "')";
          $query_config_bayes = "SELECT * FROM config WHERE kunci = 'naive_bayes'";
          $config_bayes = mysqli_fetch_all(mysqli_query($koneksi, $query_config_bayes), MYSQLI_ASSOC);
          $config_bayes = json_decode($config_bayes[0]['nilai']);
          $exec = mysqli_query($koneksi, $query_bayes);
          $bayes = mysqli_fetch_all($exec, MYSQLI_ASSOC);
          $result = [
            "naive_bayes" => [
              "negative" => 1,
              "positive" => 1
            ],
            "c45" => []
          ];
          if (sizeof($bayes) > 0) {
            // echo "<table class='mytable'><thead class='myhead'><tr><th class='myth'>Kelas</th><th class='myth'>Jpv</th>";
            $header = [];
            $negative_result = [];
            $positive_result = [];
            for ($i = 0; $i < sizeof($bayes); $i++) {
              // $header[] = "<th class='myth'>" . $bayes[$i]['attribute'] . "</th>";
              // $negative_result[] = "<td class='mytd'>" . $bayes[$i]['negative_value'] . "</td>";
              // $positive_result[] = "<td class='mytd'>" . $bayes[$i]['positive_value'] . "</td>";
              $result['naive_bayes']['negative'] *= $bayes[$i]['negative_value'];
              $result['naive_bayes']['positive'] *= $bayes[$i]['positive_value'];
            }
            $result['naive_bayes']['negative'] *= $config_bayes->negative;
            $result['naive_bayes']['positive'] *= $config_bayes->positive;
            // echo implode('', $header) . "<th class='myth'>Result</th></tr></thead><tbody>";

            $hasil = "positive";
            if ($result['naive_bayes']['negative'] >= $result['naive_bayes']['positive']) {
              $hasil = "negative";
            }
            $result['naive_bayes']['result'] = $hasil;
            // echo "<tr " . ($hasil == 'negative' ? 'class="choosed"' : '') . "><td class='mytd'>Negative</td><td class='mytd'>" . $config_bayes->negative . "</td>" . implode('', $negative_result) . "<td class='mytd'>" . $result['naive_bayes']['negative'] . "</td></tr>";
            // echo "<tr " . ($hasil == 'positive' ? 'class="choosed"' : '') . "><td class='mytd'>Positive</td><td class='mytd'>" . $config_bayes->positive . "</td>" . implode('', $positive_result) . "<td class='mytd'>" . $result['naive_bayes']['positive'] . "</td></tr>";
            // echo "</tbody></table>";
          } else {
            $result['naive_bayes'] = [ 'result' => 'tidak diketahui' ];
            // jika tidak ada attr
          }
          // echo "<br><br>";
          // // c45
          // echo "<center><h1>C4.5</h1></center>";
          $query_c45 = "SELECT * FROM c45_props";
          $c45 = mysqli_fetch_all(mysqli_query($koneksi, $query_c45), MYSQLI_ASSOC);
          $word = implode(' ', $word);
          $tree = [];
          for ($j = 0; $j < sizeof($c45); $j++) {
            if (strpos($word, $c45[$j]['attribute'])) {
              if ($c45[$j]['result'] == 1) {
                $result['c45']['result'] = 'positive';
              } else {
                $result['c45']['result'] = 'negative';
              }
              break;
            }

            if ($j == sizeof($c45) - 1) {
              if ($c45[sizeof($c45) - 1]['result'] == 1) {
                $result['c45']['result'] = 'negative';
              } else {
                $result['c45']['result'] = 'positive';
              }
            }
          }

          return $result;
        }

        if (!isset($_POST['kirim'])) {
        ?>
          <form method="POST" enctype="multipart/form-data">
            <center>
              <table>
                <tr>
                  <td>File</td>
                  <td><input type="file" name="file" /></td>
                </tr>
                <tr>
                  <td></td>
                  <td><button type="submit" name="kirim">Test</button></td>
                </tr>
              </table>
            </center>
          </form>
        <?php
        } else {
          $file = $_FILES['file'];
          if (file_exists('tmp/tmp.xls')) {
            unlink('tmp/tmp.xls');
          } else if (file_exists('tmp/tmp.xlsx')) {
            unlink('tmp/tmp.xlsx');
          }
          move_uploaded_file($file['tmp_name'], 'tmp/tmp.xlsx');
          $Spreadsheet = new SpreadsheetReader('tmp/tmp.xlsx');
          $Sheets = $Spreadsheet->Sheets();
          echo "<table class='mytable'><thead><th class='myhead'>No</th><th class='myhead'>Kalimat</th><th class='myhead'>C4.5</th><th class='myhead'>Naive Bayes</th><th class='myhead'>Hasil</th></thead><tbody>";
          foreach ($Sheets as $Index => $Name) {
            $Spreadsheet->ChangeSheet($Index);
            $i = 1;
            foreach ($Spreadsheet as $Key => $Row) {
              if (sizeof($Row) > 0) {
                if ($Row[0] != '') {
                  if ($Key == 0) {
                    continue;
                  }
                  // print_r($Row);
                  $calc = getPrediction($Row[1], $koneksi);
                  $c45Result = $calc['c45']['result'];
                  $NBResult = $calc['naive_bayes']['result'];

                  if ($c45Result=="positive" && $NBResult=="positive") {
                    $lastResult = "positive";
                  }elseif ($c45Result=="negative" && $NBResult=="negative") {
                    $lastResult = "negative";
                  }else{
                    $lastResult = "undefined";
                  }

                  echo "<tr><td class='mytd'>{$i}</td><td class='mytd'>". $Row[1] ."</td><td class='mytd'>". $calc['c45']['result'] ."</td><td class='mytd'>". $calc['naive_bayes']['result'] ."</td><td class='mytd'>".$lastResult."</td></tr>";
                }
              }
              $i++;
            }
          }
          echo "</tbody></table>";
        }
        mysqli_close(($koneksi));
        ?>
    </div>
  </div>
</body>

</html>
