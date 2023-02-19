<?php //generates an image for shared links that shows your shared spread.
$img = true;
if($img) header('Content-type: image/png');

function default_img()
{
  $img = true;
  if(!$img){
    echo 'default image returned';
    return;
  }

  $name = 'css/card_img.png';
  $fp = fopen($name, 'rb');

  //header("Content-Type: image/png");
  header("Content-Length: " . filesize($name));

  fpassthru($fp);
}

if(isset($_GET['s']))
{
  $reasons = array(
    1 => 'Error: Removed for offensive language',
    2 => 'Error: Duplicate card',
    3 => 'Error: Card text too long',
    4 => 'Error: Removed for grammatical errors', 
  );

  preg_match('/^(ppf|soa|ytr)(.*?)$/i', $_GET['s'], $matches);
  if(isset($matches[1]) && isset($matches[2]))
  {
    $cards = explode("g", $matches[2]);

    if(count($cards) !== 3)
    {
      default_img();
      return;
    }

    $file = file_get_contents("js/cah-filtered.json");

    if(!$file)
    {
      default_img();
      return;
    }

    $json = json_decode($file, true);

    if(!$json)
    {
      default_img();
      return;
    }

    if($img) $png_image = imagecreatefrompng('css/' . $matches[1] . '.png');
    if($img) $black = imagecolorallocate($png_image, 0, 0, 0);
    if($img) $red = imagecolorallocate($png_image, 201, 0, 0);

    $font_path = 'css/Schoolbell-Regular.ttf';
    $font_size = 21;
    $line_height = $font_size + 5;

    $x = 52;
    $y = 115;
    $max_w = 212;
    $max_h = 248;

    $word_sizes = array();
    $box_space = imagettfbbox($font_size, 0, $font_path, ' ');
    $word_sizes[' '] = array(
      'w' => abs($box_space[0]) + abs($box_space[2]),
      'h' => abs($box_space[1]) + abs($box_space[5]),
      'box' => $box_space,
    );

    foreach ($cards as &$card)
    {
      $card_arr = explode("h", $card);

      if(!isset($card_arr[0]) || !isset($card_arr[1]) || !ctype_xdigit($card_arr[0]) || !ctype_xdigit($card_arr[1]))
      {
        default_img();
        return;
      }

      $pack_id = hexdec($card_arr[0]);
      $card_id = hexdec($card_arr[1]);
      $pick = null;

      if(!$json[$pack_id])
      {
        default_img();
        return;
      }

      if($json[$pack_id]['r'] !== 0)
      {
        $pick = array(
          'cid' => $card_id,
          'pid' => $pack_id,
          'txt' => isset($reasons[$json[$pack_id]['r']]) ? $reasons[$json[$pack_id]['r']] : 'Error',
          'r' => $json[$pack_id]['r'],
        );
      }
      else 
      {
        for ($i = 0; $i < $length = count($json[$pack_id]['white']); $i++)
        {
          if($json[$pack_id]['white'][$i]['cid'] === $card_id)
          {
            $pick = $json[$pack_id]['white'][$i];
            break;
          }
        }
      }

      if(!$pick)
      {
        default_img();
        return;
      }

      if($pick['r'] !== 0)
      {
        if($pick['r'] === 2){ //dupe card
          $split = explode('-', $pick['txt']);
          $dup_pid = isset($split[0]) ? (int)$split[0] : false;
          $dup_cid = isset($split[1]) ? (int)$split[1] : false;
          $dup_found = false;

          if(isset($json[$dup_pid]['white']))
          {
            if($json[$dup_pid]['r'] !== 0)
            {
              $pick['r'] = $json[$dup_pid]['r'];
            }
            else 
            {
              for ($i = 0; $i < $length = count($json[$dup_pid]['white']); $i++)
              {
                if($json[$dup_pid]['white'][$i]['cid'] === $dup_cid)
                {
                  $pick = $json[$dup_pid]['white'][$i];
                  $dup_found = true;
                  break;
                }
              }
            }
          }

          if($dup_found === false) $pick['txt'] = isset($reasons[$pick['r']]) ? $reasons[$pick['r']] : 'Error';
        }
        
        if($pick['r'] !== 2 && $pick['r'] !== 0)
        {
          $pick['txt'] = isset($reasons[$pick['r']]) ? $reasons[$pick['r']] : 'Error';
        }
      }

      $words = explode(" ", trim($pick['txt']));
      $lines = array();
      $line = '';
      $lw = 0;
      $th = 0;

      foreach ($words as &$word)
      {
        if(!isset($word_sizes[$word]))
        {
          $box = imagettfbbox($font_size, 0, $font_path, $word);
          $word_sizes[$word] = array(
            'w' => abs($box[0]) + abs($box[2]),
            'box' => $box,
          );
        }

        if($line === '')
        {
          $ww = $lw + $word_sizes[$word]['w'];
        }
        else
        {
          $ww = $lw + $word_sizes[' ']['w'] + $word_sizes[$word]['w'];
        }

        if($lw < $max_w && $ww < $max_w)
        {
          $line = $line . ($line === '' ? '' : ' ') . $word;
          $lw = $lw + ($line === '' ? 0 : $word_sizes[' ']['w']) + $word_sizes[$word]['w'];
        }
        else
        {
          $lines[] = array(
            'line' => $line,
            'w' => $lw
          );
          $th = $th + $line_height;
          $line = $word;
          $lw = $word_sizes[$word]['w'];
        }
      }

      $lines[] = array(
        'line' => $line,
        'w' => $lw
      );
      $th = $th + $line_height;


      if($img)
      {
        $ly = $y + (($max_h - $th) / 2);
        foreach($lines as &$line)
        {
          $lx = $x + (($max_w - $line['w']) / 2);
          imagettftext(
            $png_image, 
            $font_size, 
            0, 
            $lx, $ly, 
            $pick['r'] === 0 ? $black : $red, 
            $font_path, 
            $line['line']
          );
          $ly = $ly + $line_height;
        }
      }

      if(!$img) echo  $th . '<pre>' . var_export($lines, true) . '</pre>';

      $x = $x + 276;
    }

    if($img){
      imagepng($png_image);
      imagedestroy($png_image);
    }
    else
    {
      //echo '<pre>' . var_export($word_sizes, true) . '</pre>';
    }
  }
  else
  {
    default_img();
    return;
  }
}
