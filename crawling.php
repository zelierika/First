  <?php include "twitteroauth/twitteroauth.php"; ?>
  <?php
  $consumer_key = "VlwuaeDaxiPxaVP6YkcJ7Ynx0";
  $consumer_secret = "xHLruKefys7iAG0GRdwyBKAMGK1FF3uREzu237M18I80olmPMK";
  $access_token = "459209750-frKYFV6w9sCLzEiUuYSWYZLBjLh7enpDoxNsY2YQ";
  $access_token_secret = "qmlUHdXMsPPDGIEmit4odCYZV4SedXB5V7hfwV63YXQRv";

  $twitter = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);
  ?>
  <?php
  set_time_limit(0);
  session_start();
  ?>
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
      $page = "crawling";
      include('navbar.php');
      ?>
      <div id="content">
        <div>
          <br />
          <br />
          <table align="center" width="700px" border=1 cellpadding="10">
            <tr>
              <td>
                <form action="" method="post">
                  <input type="text" name="keyword" />
                </form>
              </td>
            </tr>
            <tr>
              <th height="400px" width="700px" colspan="2" bgcolor="#F0FFFF">
                <?php
                if (isset($_POST['keyword'])) {
                  echo '<table border=1>';
                  $tweets = $twitter->get('https://api.twitter.com/1.1/search/tweets.json?q=' . $_POST['keyword'] . '&lang=id&result_type=mixed&count=100');
                  foreach ($tweets as $tweet) {
                    foreach ($tweet as $t) {
                      if (is_object($t)) {
                        echo '<img src="' . $t->user->profile_image_url . '" />' . $t->text . '<br>.';
                      }
                    }
                  }
                }
                ?>
              </th>
            </tr>
          </table>
        </div>
      </div>
    </div>
    <?php
    include "koneksi.php";
    $i = 0;
    $cekCrawling = mysqli_query($koneksi, "SELECT * FROM crawling");
    $numRow = mysqli_num_rows($cekCrawling);
    echo '<table border=1><tr><th>No</th><th>Tweet</th></tr>';
    if ($numRow > 0) {
      mysqli_query($koneksi, "TRUNCATE TABLE crawling");
      foreach ($tweets as $tweet) {
        foreach ($tweet as $t) {
          if (is_object($t)) {
            $s = escape_string($t->text);
            mysqli_query($koneksi, "INSERT INTO crawling (tweet_text) VALUES ('{$s}')");
            echo "<tr><td>" . ($i + 1) . "</td><td>" . $t->text . "</td></tr>";
            $i++;
          }
        }
      }
    } else {
      foreach ($tweets as $tweet) {
        foreach ($tweet as $t) {
          if (is_object($t)) {
            mysqli_query($koneksi, "INSERT INTO crawling (tweet_text) VALUES ('$t->text')");
            echo "<tr><td>" . ($i + 1) . "</td><td>" . $t->text . "</td></tr>";
            $i++;
          }
        }
      }
    }
    echo '</table>';
  ?>
  </body>
  </html>
