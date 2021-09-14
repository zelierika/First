  <!DOCTYPE html>
  <html>

  <head>
    <title>SKRIPSI</title>
    <meta charset="UTF-8" />
    <link rel="stylesheet" type="text/css" href="styles/style.css" />
    <!--[if IE 6]><link rel="stylesheet" type="text/css" href="styles/ie6.css" /><![endif]-->
    <style>
      table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
      }

      td,
      th {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
      }

      thead {
        background-color: #dddddd;
      }

      @media print,
      screen {
        .choosed {
          background-color: #03fc35;
        }
      }
    </style>
  </head>

  <body>
    <div id="page">
      <?php
      $page = "c45";
      include('navbar.php');
      ?>
      <div id="content">
        <?php
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        include "koneksi.php";
        $query_attribute = "SELECT * FROM hasil_preprocess";
        $attributes = mysqli_fetch_all(mysqli_query($koneksi, $query_attribute), MYSQLI_ASSOC);
        $query_data_latih = "SELECT * FROM data_latih";
        $data_latih = mysqli_fetch_all(mysqli_query($koneksi, $query_data_latih), MYSQLI_ASSOC);
        if (sizeof($data_latih) == 0 || sizeof($attributes) == 0) {
          echo '<center>Data kosong</center>';
          return;
        }
        $data = [];
        $masterData = [
          'totalNegative' => 0,
          'totalPositive' => 0,
          'entropy' => 0,
          'gain' => [
            'max' => 0,
            'item' => []
          ]
        ];
        for ($i = 0; $i < sizeof($data_latih); $i++) {
          $filteredDataLatih = getFilteredSentence($data_latih[$i]['tweet_text']);
          for ($j = 0; $j < sizeof($attributes); $j++) {
            if (in_array(strtolower($attributes[$j]['tweet_text']), $filteredDataLatih)){
              if (trim(strtolower($data_latih[$i]['kelas'])) == 'negatif') {
                if (!isset($attributes[$j]['negative_true'])) {
                  $attributes[$j]['negative_true'] = 1;
                } else {
                  // $attributes[$j]['negative_true'] = 'nt';
                  $attributes[$j]['negative_true']++;
                }
              } else {
                if (!isset($attributes[$j]['positive_true'])) {
                  $attributes[$j]['positive_true'] = 1;
                } else {
                  // $attributes[$j]['negative_true'] = 'nt';
                  $attributes[$j]['positive_true']++;
                }
              }
            } else {
              if (trim(strtolower($data_latih[$i]['kelas'])) == 'negatif') {
                if (!isset($attributes[$j]['negative_false'])) {
                  $attributes[$j]['negative_false'] = 1;
                } else {
                  // $attributes[$j]['negative_false'] = 'nf';
                  $attributes[$j]['negative_false']++;
                }
              } else {
                if (!isset($attributes[$j]['positive_false'])) {
                  $attributes[$j]['positive_false'] = 1;
                } else {
                  // $attributes[$j]['positive_false'] = 'pf';
                  $attributes[$j]['positive_false']++;
                }
              }
            }
          }
          if (trim(strtolower($data_latih[$i]['kelas'])) == 'negatif') {
            $masterData['totalNegative']++;
          } else {
            $masterData['totalPositive']++;
          }
        }
        $masterData['entropy'] = calcEntropy($masterData['totalNegative'], $masterData['totalNegative']);
        for ($i = 0; $i < sizeof($attributes); $i++) {
          if (isset($attributes[$i]['positive_false'])) {
            $positive_false = $attributes[$i]['positive_false'];
          } else {
            $positive_false = 0;
            $attributes[$i]['positive_false'] = 0;
          }
          if (isset($attributes[$i]['negative_false'])) {
            $negative_false = $attributes[$i]['negative_false'];
          } else {
            $negative_false = 0;
            $attributes[$i]['negative_false'] = 0;
          }
          $total_false = $positive_false + $negative_false;
          if (isset($attributes[$i]['positive_true'])) {
            $positive_true = $attributes[$i]['positive_true'];
          } else {
            $positive_true = 0;
            $attributes[$i]['positive_true'] = 0;
          }
          if (isset($attributes[$i]['negative_true'])) {
            $negative_true = $attributes[$i]['negative_true'];
          } else {
            $negative_true = 0;
            $attributes[$i]['negative_true'] = 0;
          }
          $total_true = $positive_true + $negative_true;
          $attributes[$i]['entropy_false'] = calcEntropy($negative_false, $positive_false);
          $attributes[$i]['entropy_true'] = calcEntropy($negative_true, $positive_true);
          $attributes[$i]['gain'] = $masterData['entropy'] - $total_false / sizeof($data_latih) * $attributes[$i]['entropy_false'] - $total_true / sizeof($data_latih) * $attributes[$i]['entropy_true'];
          // $attributes[$i]['formula'] = $masterData['entropy'] .'- (('.$total_false .'/'. sizeof($data_latih) .'*'. $entropy_false.') - ('.$total_true .'/'. sizeof($data_latih) .'*'. $entropy_true.'))';
          if ($attributes[$i]['gain'] > $masterData['gain']['max']) {
            $masterData['gain']['max'] = $attributes[$i]['gain'];
            $masterData['gain']['item'] = [
              'id' => $attributes[$i]['id_preprocess'],
              'text' => $attributes[$i]['tweet_text'],
              'positive_false' => $positive_false,
              'negative_false' => $negative_false,
              'positive_true' => $positive_true,
              'negative_true' => $negative_true
            ];
          }
        }
        echo "<b>Entropy total : " . $masterData['entropy'] . "</b><br />";
        echo '<table>
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
          <tbody>';
        $index_attr = -1;
        for ($j = 0; $j < sizeof($attributes); $j++) {
          $no = $j + 1;
          $attribute = $attributes[$j]['tweet_text'];
          $neg0 = $attributes[$j]['negative_false'];
          $pos0 = $attributes[$j]['positive_false'];
          $total0 = $neg0 + $pos0;
          $entropy0 = $attributes[$j]['entropy_false'];
          $neg1 = $attributes[$j]['negative_true'];
          $pos1 = $attributes[$j]['positive_true'];
          $total1 = $neg1 + $pos1;
          $entropy1 = $attributes[$j]['entropy_true'];
          $gain = number_format($attributes[$j]['gain'], 20);
          $style = "";
          if ($attributes[$j]['id_preprocess'] == $masterData['gain']['item']['id']) {
            $style = "class='choosed'";
            $index_attr = $j;
          }
          echo "<tr {$style}><td>{$no}</td>
          <td>{$attribute}</td>
          <td>{$neg0}</td>
          <td>{$pos0}</td>
          <td>{$total0}</td>
          <td>{$entropy0}</td>
          <td>{$neg1}</td>
          <td>{$pos1}</td>
          <td>{$total1}</td>
          <td>{$entropy1}</td>
          <td>{$gain}</td></tr>";
        }
        array_splice($attributes, $index_attr, 1);
        echo '</tbody></table>';
        function calcEntropy($countNeg, $countPos)
        {
          $total = $countNeg + $countPos;
          if ($total == 0) {
            return 0;
          }
          $calc1 = $countNeg / $total;
          $calc2 = $countPos / $total;
          $entropy = (-1 * $calc1 * log($calc1, 2)) + (-1 * $calc2 * log($calc2, 2));
          $entropy = is_nan($entropy) ? 0 : $entropy;
          return $entropy;
        }
        $props = [];
        generateTable($attributes, $masterData, $props, $koneksi);
        function generateTable($attributes, $masterData, $props, $koneksi)
        {
          $kelas = 1;
          if ($masterData['gain']['item']['negative_true'] > $masterData['gain']['item']['positive_true']) {
            $kelas = 0;
          }
          $newNegative = $masterData['totalNegative'] - $masterData['gain']['item']['negative_true'];
          $newPositive = $masterData['totalPositive'] - $masterData['gain']['item']['positive_true'];
          $newTotal = $newNegative + $newPositive;
          $newEntropy = calcEntropy($newNegative, $newPositive);
          $props[] = "('" . strtolower($masterData['gain']['item']['text']) . "', " . $kelas . ")";
          if ($newEntropy <= 0) {
            // rekursi berakhir
            mysqli_query($koneksi, "TRUNCATE c45_props");
            $query = "INSERT INTO c45_props(attribute, result) VALUES " . implode(", ", $props);
            mysqli_query($koneksi, $query);
            return 0;
          } else {
            $masterData = [
              'totalNegative' => $newNegative,
              'totalPositive' => $newPositive,
              'entropy' => $newEntropy,
              'gain' => [
                'max' => 0,
                'item' => []
              ]
            ];
            // hitung entropy & gain
            for ($i = 0; $i < sizeof($attributes); $i++) {
              $negativeFalse = $newNegative - $attributes[$i]['negative_true'];
              $positiveFalse = $newPositive - $attributes[$i]['positive_true'];
              $totalFalse = $negativeFalse + $positiveFalse;
              $negativeTrue = $attributes[$i]['negative_true'];
              $positiveTrue = $attributes[$i]['positive_true'];
              $totalTrue = $negativeTrue + $positiveTrue;
              $entropyFalse = calcEntropy($negativeFalse, $positiveFalse);
              $entropyTrue = calcEntropy($negativeTrue, $positiveTrue);
              $gain = $masterData['entropy'] - $totalFalse / $newTotal * $entropyFalse - $totalTrue / $newTotal * $entropyTrue;
              $attributes[$i] = [
                'id_preprocess' => $attributes[$i]['id_preprocess'],
                'tweet_text' => $attributes[$i]['tweet_text'],
                'negative_false' => $negativeFalse,
                'positive_false' => $positiveFalse,
                'negative_true' => $negativeTrue,
                'positive_true' => $positiveTrue,
                'entropy_false' => $entropyFalse,
                'entropy_true' => $entropyTrue,
                'gain' => $gain
              ];
              if ($attributes[$i]['gain'] > $masterData['gain']['max']) {
                $masterData['gain']['max'] = $attributes[$i]['gain'];
                $masterData['gain']['item'] = [
                  'id' => $attributes[$i]['id_preprocess'],
                  'text' => $attributes[$i]['tweet_text'],
                  'positive_false' => $positiveFalse,
                  'negative_false' => $negativeFalse,
                  'positive_true' => $positiveTrue,
                  'negative_true' => $negativeTrue
                ];
              }
            }
            // print to table
            echo "<br /><br /><b>Entropy total : " . $masterData['entropy'] . "</b><br />";
            echo '<table>
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
              <tbody>';
            $index_attr = -1;
            for ($j = 0; $j < sizeof($attributes); $j++) {
              $no = $j + 1;
              $attribute = $attributes[$j]['tweet_text'];
              $neg0 = $attributes[$j]['negative_false'];
              $pos0 = $attributes[$j]['positive_false'];
              $total0 = $neg0 + $pos0;
              $entropy0 = $attributes[$j]['entropy_false'];
              $neg1 = $attributes[$j]['negative_true'];
              $pos1 = $attributes[$j]['positive_true'];
              $total1 = $neg1 + $pos1;
              $entropy1 = $attributes[$j]['entropy_true'];
              $gain = number_format($attributes[$j]['gain'], 14);
              $style = "";
              if ($attributes[$j]['id_preprocess'] == $masterData['gain']['item']['id']) {
                $style = "class='choosed'";
                $index_attr = $j;
              }
              echo "<tr {$style}><td>{$no}</td>
                <td>{$attribute}</td>
                <td>{$neg0}</td>
                <td>{$pos0}</td>
                <td>{$total0}</td>
                <td>{$entropy0}</td>
                <td>{$neg1}</td>
                <td>{$pos1}</td>
                <td>{$total1}</td>
                <td>{$entropy1}</td>
                <td>{$gain}</td></tr>";
            }
            array_splice($attributes, $index_attr, 1);
            echo '</tbody></table>';
            // generate new table
            generateTable($attributes, $masterData, $props, $koneksi);
          }
        }

        function getFilteredSentence($sentence) {
          //Case Folding
          $sentence = strtolower($sentence);
          $d = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '-', '/', '\\', ',', '.', '#', ':', ';', '\'', '"', '[', ']', '{', '}', ')', '(', '|', '`', '~', '!', '%', '$', '^', '&', '*', '=', '?', '+', '–');
          $sentence = str_replace($d, '', $sentence); // Hilangkan karakter yang telah disebutkan di array $d

          //Tokenisasi
          $sentence = explode(" ", $sentence);

          $tmp_kata = [];
          // print_r($sentence);
          for ($j = 0; $j < sizeof($sentence); $j++) {
            $sentence[$j] = trim($sentence[$j]);
            if ($sentence[$j] != '') {
              if (strpos($sentence[$j], '@') === false) {
                $tmp_kata[] = $sentence[$j];
              }
            }
          }

          $sentence = array_filter($tmp_kata);
          $sentence = array_values($sentence);

          //filtering                            
          $stopwords = file_get_contents("stopwords.txt");
          $stopwords = preg_split("/[\s]+/", $stopwords);
          $sentence = array_diff($sentence, $stopwords);

          //menjadikan array yang bernilai kosong
          $sentence = array_filter($sentence);
          $sentence = array_values($sentence);

          //stemming
          require_once('NaziefAdriani.php');
          foreach ($sentence as $t => $value) {
            $sentence[$t] = Stemmer($value);
          }
          /*end of stemming*/

          $sentence = array_filter($sentence);
          $sentence = array_values($sentence);

          return $sentence;
        }

        mysqli_close($koneksi);
        ?>
      </div>
    </div>
  </body>

  </html>
