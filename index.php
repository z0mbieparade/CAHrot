<?php
require('settings_default.php');
$set = $settings;
$setup = false;
if(file_exists('settings.php')){
	include('settings.php');
	foreach($settings as $key => $val){
		$set[$key] = $val;
	}
	$setup = true;
}
if(file_exists('../all_settings.php')){
	include('../all_settings.php');
	if(isset($all_settings['CAHrot'])){
		foreach($all_settings['CAHrot'] as $key => $val){
			$set[$key] = $val;
		}
		$setup = true;
	}
}
$settings = $set;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
  <title><?php echo $settings['title']; ?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="apple-touch-icon" sizes="180x180" href="css/favicon_io/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="css/favicon_io/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="css/favicon_io/favicon-16x16.png">
  <link rel="manifest" href="css/favicon_io/site.webmanifest">
	<?php
	  $card = $settings['CAHrot_site_path'] . "css/card_img.png";
	  $url = $settings['CAHrot_site_path'];

	  if(isset($_GET['s']))
	  {
	    $card = $settings['CAHrot_site_path'] . "og_img.php?s=" . $_GET['s'];
	    $url = $settings['CAHrot_site_path'] . "?s=" . $_GET['s'];
	  }
	?>
  <meta property="og:title" content="<?php echo $settings['title']; ?>">
  <meta property="og:description" content="Tarot card spreads using Cards Against Humanity.">
  <meta property="og:image" content="<?php echo $card; ?>">
  <meta property="og:url" content="<?php echo $url; ?>">
  <meta property="og:type" content="website">

  <meta name="twitter:title" content="<?php echo $settings['title']; ?>">
  <meta name="twitter:description" content="Tarot card spreads using Cards Against Humanity.">
  <meta name="twitter:image" content="<?php echo $card; ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:creator" content="@rotterz">

  <link rel="preconnect" href="https://fonts.googleapis.com"> 
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin> 
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300&family=Schoolbell&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <body>
    <script>
			<?php if(!$setup){ ?>
				console.log('You have not created your settings.php file, please copy settings_default.php to settings.php and update it with correct settings.');
			<?php }?>
      let site_url = "<?php echo $settings['CAHrot_site_path']; ?>";
    </script>
    <div id="settings">
      <span>Select your spread:</span>
      <div>
        <select id="spread_select">
          <option value="ppf">Past/Present/Future</option>
          <option value="soa">Situation/Obstacle/Advice</option>
          <option value="ytr">You/Them/Relationship</option>
        </select>

        <button id="spread_it">Spread It</button>

        <div id="share_it">
          <svg height="22px" width="22px" viewBox="0 0 512 512.00578" xmlns="http://www.w3.org/2000/svg">
            <path fill="#534640" d="m507.523438 148.890625-138.667969-144c-4.523438-4.691406-11.457031-6.164063-17.492188-3.734375-6.058593 2.453125-10.027343 8.320312-10.027343 14.847656v69.335938h-5.332032c-114.6875 0-208 93.3125-208 208v32c0 7.421875 5.226563 13.609375 12.457032 15.296875 1.175781.296875 2.347656.425781 3.519531.425781 6.039062 0 11.820312-3.542969 14.613281-9.109375 29.996094-60.011719 90.304688-97.28125 157.398438-97.28125h25.34375v69.332031c0 6.53125 3.96875 12.398438 10.027343 14.828125 5.996094 2.453125 12.96875.960938 17.492188-3.734375l138.667969-144c5.972656-6.207031 5.972656-15.976562 0-22.207031zm0 0"/>
            <path fill="#534640" d="m448.003906 512.003906h-384c-35.285156 0-63.99999975-28.710937-63.99999975-64v-298.664062c0-35.285156 28.71484375-64 63.99999975-64h64c11.796875 0 21.332032 9.535156 21.332032 21.332031s-9.535157 21.332031-21.332032 21.332031h-64c-11.777344 0-21.335937 9.558594-21.335937 21.335938v298.664062c0 11.777344 9.558593 21.335938 21.335937 21.335938h384c11.773438 0 21.332032-9.558594 21.332032-21.335938v-170.664062c0-11.796875 9.535156-21.335938 21.332031-21.335938 11.800781 0 21.335937 9.539063 21.335937 21.335938v170.664062c0 35.289063-28.714844 64-64 64zm0 0"/>
          </svg>
        </div>
      </div>

      <input id="share_url">
    </div>

    <div id="spread"></div>

    <div id="footer">
      A silly tarot generator using data from Cards Against Humanity by <a href="<?php echo getenv('APP_SITE_PATH'); ?>">z0m.bi</a>.
      <p id="warning">DISCLAIMER: there are thousands of CAH cards, and I've done my best to filter out the extra gross ones. It's always possible I missed something, no one is perfect and filters are fallible. You can always email me at me[at]z0m.bi and let me know if you bump into something extra ick.</p> 
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="js/script.js"></script>
    <?php if(isset($settings['include_footer']) && $settings['include_footer'] !== ''){
      include($settings['include_footer']);
    } ?>
  </body>
</html>
