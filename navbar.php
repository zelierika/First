<div id="header">
  <div id="section">
    <div>
      <h1 >SENTIMEN ANALISIS TWITTER</h1>
    </div>
  <span>
     <img src="images/logoo.png" alt=""></span>
  </div>
  <ul>
    <li <?=($page == 'home' ? 'class="current"' : '')?>><a href="home.php">Home</a></li>
    <li <?=($page == 'crawling' ? 'class="current"' : '')?>><a href="crawling.php">Crawling Data</a></li>
    <li <?=($page == 'preprocess' ? 'class="current"' : '')?>><a href="preprocess.php">Preprocessing</a></li>
    <li <?=($page == 'c45' ? 'class="current"' : '')?>><a href="c45_coba.php">Algoritma c4.5</a></li>
    <li <?=($page == 'bayes' ? 'class="current"' : '')?>><a href="bayes.php">Algoritma Naive Bayes</a></li>
    <li <?=($page == 'pengujian' ? 'class="current"' : '')?>><a href="pengujian.php">Pengujian</a></li>
  </ul>
</div>