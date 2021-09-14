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
    $page = "bayes";
    include('navbar.php');
    ?>
    <div id="content">

      <body>
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
          'totalPositive' => 0
        ];

        for ($i = 0; $i < sizeof($data_latih); $i++) {
          $filteredDataLatih = getFilteredSentence($data_latih[$i]['tweet_text']);
          for ($j = 0; $j < sizeof($attributes); $j++) {
            if (in_array(strtolower($attributes[$j]['tweet_text']), $filteredDataLatih)){
              if (strtolower($data_latih[$i]['kelas']) == 'negatif') {
                if (!isset($attributes[$j]['negative_true'])) {
                  $attributes[$j]['negative_true'] = 1;
                } else {
                  $attributes[$j]['negative_true']++;
                }
              } else {
                if (!isset($attributes[$j]['positive_true'])) {
                  $attributes[$j]['positive_true'] = 1;
                } else {
                  $attributes[$j]['positive_true']++;
                }
              }

              if (!isset($data_latih[$i]['attr'])) {
                $data_latih[$i]['attr'] = [];
              }

              if (!isset($data_latih[$i]['attr'][$attributes[$j]['tweet_text']])) {
                $data_latih[$i]['attr'][$attributes[$j]['tweet_text']] = [
                  'count' => 1
                ];
              } else {
                $data_latih[$i]['attr'][$attributes[$j]['tweet_text']]['count']++;
              }
            } else {
              $data_latih[$i]['attr'][$attributes[$j]['tweet_text']] = [
                'count' => 0
              ];
            }
          }
          if (strtolower($data_latih[$i]['kelas']) == 'negatif') {
            $masterData['totalNegative']++;
          } else {
            $masterData['totalPositive']++;
          }
        }
        $masterData['total'] = $masterData['totalNegative'] + $masterData['totalPositive'];
        $header = [];
        echo '<table>
          <thead>
            <tr>
              <th>No</th>
              <th>Attr</th>
              <th>Positive</th>
              <th>Negative</th>
            </tr>
          </thead>
          <tbody>';
        $props = [];
        for ($i = 0; $i < sizeof($attributes); $i++) {
          $header[] = "<th>" . $attributes[$i]['tweet_text'] . "</th>";
          if (isset($attributes[$i]['positive_true'])) {
            $positive =  $attributes[$i]['positive_true'];
          } else {
            $positive =  0;
            $attributes[$i]['positive_true'] = 0;
          }
          if (isset($attributes[$i]['negative_true'])) {
            $negative =  $attributes[$i]['negative_true'];
          } else {
            $negative =  0;
            $attributes[$i]['negative_true'] = 0;
          }
          $attributes[$i]['prob_positive'] = ($positive + 1) / ($masterData['totalPositive'] + $masterData['total']);
          $attributes[$i]['prob_negative'] = ($negative + 1) / ($masterData['totalNegative'] + $masterData['total']);
          echo "<tr><td>" . ($i + 1) . "</td><td>" . $attributes[$i]['tweet_text'] . "</td>";
          echo "<td>" . $attributes[$i]['prob_positive'] . "</td>";
          echo "<td>" . $attributes[$i]['prob_negative'] . "</td>";
          $props[] = "('" . strtolower($attributes[$i]['tweet_text']) . "', " . $attributes[$i]['prob_positive'] . ", " . $attributes[$i]['prob_negative'] . ")";
        }
        $data = [
          'attr' => $attributes,
          'data_latih' => $data_latih
        ];

        echo '</tbody></table><br />';
        echo '<table>
          <thead>
            <tr>
              <th>No. </th>
              <th>Text \ Attr</th>
              ' . implode('', $header)
                  . '</tr>
          </thead>
          <tbody>';
        for ($j = 0; $j < sizeof($data_latih); $j++) {
          $latih = $data_latih[$j];
          echo "<tr><td>" . ($j + 1) . "</td><td>" . $latih['tweet_text'] . "</td>";
          for ($i = 0; $i < sizeof($attributes); $i++) {
            echo "<td>" . $latih['attr'][$attributes[$i]['tweet_text']]['count'] . "</td>";
          }
          echo "</tr>";
        }
        echo '</tbody></table>';
        mysqli_query($koneksi, "TRUNCATE naive_bayes_props");
        $query = "INSERT INTO naive_bayes_props(attribute, positive_value, negative_value) VALUES " . implode(', ', $props);
        mysqli_query($koneksi, $query);
        $config = json_encode([
          "negative" => $masterData['totalNegative'] / $masterData['total'],
          "positive" => $masterData['totalPositive'] / $masterData['total']
        ]);
        mysqli_query($koneksi, "UPDATE config SET nilai = '{$config}' WHERE kunci = 'naive_bayes'");

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
