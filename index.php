<!doctype html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Hackaton</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.5/css/bootstrap.min.css"
  integrity="sha384-AysaV+vQoT3kOAXZkl02PThvDr8HYKPZhNT5h/CXfBThSRXQ6jW5DO2ekP5ViFdi" crossorigin="anonymous">

  <link rel="stylesheet" href="style/style.css" />
</head>
<body>

  <div class="container-fluid">

    <nav class="navbar navbar-light bg-faded">
      <ul class="nav navbar-nav">
        <li class="nav-item active">
          <a class="nav-link" href="#">Hackaton <span class="sr-only">(current)</span></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#form">Form Artist</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#spotify">Spotify</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#lastFm">Last.fm</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#wikipedia">Wikipedia</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#events">Events</a>
        </li>
      </ul>
    </nav>

    <div class="dataGroup">
      <h1>Hackathon</h1>
      <h3>Listen Music</h3>
      <form name="chooseArtist" method="POST" action="index.php" id="form">
        <div class="form-group">
          <label for="name">Artist name</label>
          <input type="text" class="form-control" id="name" name="name" required placeholder="Enter artist name">
        </div>
        <div class="form-group">
          <h3>Countries</h3>
          US :
          <input type="radio" name="country" value="US"><br>
          FR :
          <input type="radio" name="country" value="CA" checked="checked"><br>
          Other :
          <input type="radio" name="country" value="MX">
          <small id="emailHelp" class="form-text text-muted">If an artist doesn't exist with default country,
            search with another country
          </small>
        </div>
        <button type="submit" class="btn btn-primary">Search Artist</button>
      </form>

      <?php
      if (!($_POST)) {
        echo 'Veuillez selectionner un artist';
        // API spotify
      } else if (empty($_POST['name']) || empty($_POST['country'])) {
        echo 'Veuillez bien remplir les champs ! ';

      } else {

        $nameArtist = $_POST['name'];
        $countryArtist = $_POST['country'];


        $curl = curl_init(); // such as http://example.com/example.xml
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //disable the SSL !!!!!!


        $query = array(
          "limit" => 3,
          "offset" => 3,
          "q" => $nameArtist,
          "type" => "album",
          "market" => $countryArtist
        );

        $url = "https://api.spotify.com/v1/search" . "?" . http_build_query($query);
        curl_setopt($curl, CURLOPT_URL,
        $url
      );

      $result = curl_exec($curl);
      $data = json_decode($result);

      if($data->albums->total != 0){
        //ID artist for top tracks
        $artistId = $data->albums->items[1]->artists[0]->id;

        //Tous les albums
        $discographie = $data->albums->items[1]->artists[0]->external_urls->spotify;

        $artistSpotifyName = $data->albums->items[1]->artists[0]->name;
        // GET the top Track for Widget build
        $curl2 = curl_init(); // such as http://example.com/example.xml
        curl_setopt($curl2, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl2, CURLOPT_SSL_VERIFYPEER, false); //disable the SSL !!!!!!

        $url2 = "https://api.spotify.com/v1/artists/" . $artistId . "/top-tracks?country=" . $countryArtist;
        curl_setopt($curl2, CURLOPT_URL,
        $url2
      );


      $response = curl_exec($curl2);
      $dataTopTrack = json_decode($response);

      // TOP track of artist url

      //get all uri for widget track list
      $tabAllUriTracks = [];
      for ($x = 0; $x < count($dataTopTrack->tracks); $x++) {
        array_push($tabAllUriTracks, $dataTopTrack->tracks[$x]->uri);
      }

      //get all Preview
      $tabAllPreviewsTracks = [];
      for ($y = 0; $y < count($dataTopTrack->tracks); $y++) {
        array_push($tabAllPreviewsTracks, $dataTopTrack->tracks[$y]->preview_url);
      }


      //GET more information about the artist choosen
      $curl3 = curl_init();
      curl_setopt($curl3, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curl3, CURLOPT_SSL_VERIFYPEER, false); //disable the SSL !!!!!!

      $url3 = 'https://api.spotify.com/v1/artists/' . $artistId . '';
      curl_setopt($curl3, CURLOPT_URL,
      $url3
    );

    $responseInformation = curl_exec($curl3);
    $dataInformation = json_decode($responseInformation);

  }else{
    echo '<h4>Artist Not Found by Spotify</h4>';
    $artistSpotifyName = null;
  }
  //API wikipedia (biographie artist)
  // => API url https://en.wikipedia.org/w/api.php?format=json&action=query&prop=extracts&exintro=&explaintext=&titles=Stack%20Overflow
  $curl4 = curl_init();
  curl_setopt($curl4, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl4, CURLOPT_SSL_VERIFYPEER, false); //disable the SSL !!!!!!
  $nameArtistReplaceWiki = str_replace(' ', '+', $nameArtist);

  $url4 = "https://en.wikipedia.org/w/api.php?action=opensearch&search=" . $nameArtistReplaceWiki . "&namespace=0";
  curl_setopt($curl4, CURLOPT_URL,
  $url4
);

$wikipediaResponse = curl_exec($curl4);
$wikiData = json_decode($wikipediaResponse);


//API Events (concerts)
$nameArtistReplace = str_replace(' ', '+', $nameArtist);

$url = "http://api.eventful.com/rest/events/search?keywords=" . $nameArtistReplace . "&date=future&app_key=2f4pswTNhBZdtnkJ&category=concert";
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $url);    // get the url contents

$data = curl_exec($ch); // execute curl request
curl_close($ch);

$xml = simplexml_load_string($data);
//print_r($xml);


// Last.fm API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://ws.audioscrobbler.com/2.0/?method=artist.getinfo&artist=' . $artistSpotifyName . '&api_key=2f10e515b48129ce1806ac8185b389ac&format=json');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
$content = curl_exec($ch);
curl_close($ch);
$lastFmContent = json_decode($content);
?>

<?php
if ($artistSpotifyName != null) { ?>
  <div id="spotify" class="dataGroup">

    <div class="artistCardContainer">
      <div class="card artistCard">
        <img class="card-img-top" src="<?php echo $dataInformation->images[2]->url ?>" alt="img">
        <div class="card-block">
          <h4 class="card-title"><?php echo $artistSpotifyName ?></h4>
          <p class="card-text">Followers total :
            <strong><?php echo $dataInformation->followers->total ?></strong></p>
            <p class="card-text">

              <div class="panel-group">
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <h4 class="panel-title">
                      <a data-toggle="collapse" href="#collapse1">Musical Styles</a>
                    </h4>
                  </div>
                  <div id="collapse1" class="panel-collapse collapse">
                    <ul class="list-group">
                      <?php
                      foreach ($dataInformation->genres as $genre) {
                        ?>
                        <a href="#" class="list-group-item"><?php echo $genre ?></a>
                        <?php
                      }
                      ?>
                    </ul>
                  </div>
                </div>
              </div>

            </p>
            <a href="<?php echo $discographie ?>" target="_blank" class="btn btn-primary">Access to Full
              discography</a>
            </div>
          </div>

        </div>

        <div class="row">

          <?php
          foreach ($dataTopTrack->tracks as $item) {
            ?>
            <div class="col-sm-3">
              <div class="card">
                <iframe src="https://embed.spotify.com/?uri=<?php echo($item->uri) ?>" width="300"
                  height="380"
                  frameborder="0"
                  allowtransparency="true">
                </iframe>
                <div class="card-block">
                  <h4 class="card-title"><?php echo $item->name ?></h4>
                  <p class="card-text"><?php echo $item->popularity ?> % popularity</p>
                  <p class="card-text">
                    <small class="text-muted">
                      <audio controls>
                        <source src="<?php echo $item->preview_url ?>" type="audio/ogg">
                          Your browser does not support the audio element.
                        </audio>
                      </small>
                    </p>
                  </div>
                </div>
              </div>
              <?php
            }
            ?>
          </div>
        </div>
        <?php }
        if(isset($lastFmContent->message)){
          echo 'Last.FM : ' . $lastFmContent->message;
        }else{ ?>

          <div id="lastFm" class="dataGroup">
            <h3>Artist Biography [Last.fm]</h3>
            <p>
              <?php echo $lastFmContent->artist->bio->summary ?>
            </p>

            <h5>
              Similar artists
            </h5>
            <table class="table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Url</th>
                  <th>
                    Photo
                  </th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($lastFmContent->artist->similar->artist as $key => $artist) { ?>
                  <tr>
                    <td><?php echo $artist->name; ?></td>
                    <td>
                      <a href="<?php echo $artist->url; ?>" target="_blank"><?php echo $artist->url; ?> </a></td>
                    <td>
                      <img src="<?php echo $artist->image[2]->{'#text'} ?>">
                    </td>
                  </tr>
                  <?php } ?>
                </tbody>
              </table>

            </div>
            <?php } ?>
            <div id="wikipedia" class="dataGroup">

              <h3>Extract from Wikipedia</h3
                <?php foreach ($wikiData[2] as $infos) {
                  ?>
                  <p><?php echo $infos ?></p>
                  <?php
                }
                ?>
              </div>
              <div id="events" class="dataGroup">
                <h3>Futures Concerts of <?php echo $_POST['name'] ?> sort by relevance</h3>
                <ul class="list-group">
                  <?php
                  foreach ($xml as $x) {
                    foreach ($x->event as $event) { ?>
                      <li class="list-group-item">
                        <span class="tag tag-default tag-pill float-xs-right"><?php echo $event->start_time ?></span>
                        <p>Title : <?php echo $event->title ?></p>
                        <p>Url : <a href="<?php echo $event->url ?>"><?php echo $event->url ?></a></p>
                        <p>Description : <?php echo $event->description ?></p>
                        <img src='<?php echo $event->image->thumb->url ?>'>
                      </li>
                      <?php }
                    }
                    ?>
                  </ul>
                </div>
                <?php  } ?>
              </div>
            </div>

            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"
            integrity="sha384-3ceskX3iaEnIogmQchP8opvBy3Mi7Ce34nWjpBIwVTHfGYWQS9jwHDVRnpKKHJg7"
            crossorigin="anonymous"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.3.7/js/tether.min.js"
            integrity="sha384-XTs3FgkjiBgo8qjEjBk0tGmf3wPrWtA6coPfQDfFEY8AnYJwjalXCiosYRBIBZX8"
            crossorigin="anonymous"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.5/js/bootstrap.min.js"
            integrity="sha384-BLiI7JTZm+JWlgKa0M0kGRpJbF2J8q+qreVrKBC47e3K6BW78kGLrCkeRX6I9RoK"
            crossorigin="anonymous"></script>

          </body>
          </html>
