<?php
// Search node
$node = $argv[1];

// Amazon Settings
$awsaccess_key = "";
$access_secret = "";
$associate_tag = "";
$version = "2011-08-01";

function request($url) {
  $ch = curl_init(); 
  curl_setopt ($ch, CURLOPT_URL, $url); 
  curl_setopt ($ch, CURLOPT_HEADER, 1); 
  curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
  curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt ($ch, CURLOPT_TIMEOUT, 120);
  $result = curl_exec ($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $hsize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
  $result = substr($result, $hsize);
  curl_close($ch);

  return $result; 
}

function get_serach_index($node) {
  global $awsaccess_key, $associate_tag, $version;

  $root[352484011] = 'Apparel';
  $root[2277724051] = 'Appliances';
  $root[2017304051] = 'Automotive';
  $root[344845011] = 'Baby';
  $root[52374051] = 'Beauty';
  $root[465392] = 'Books';
  $root[701040] = 'Classical';
  $root[561958] = 'DVD';
  $root[3210981] = 'Electronics';
  $root[52033011] = 'ForeignBooks';
  $root[57239051] = 'Grocery';
  $root[160384011] = 'HealthPersonalCare';
  $root[2016929051] = 'HomeImprovement';
  $root[2277721051] = 'Hobbies';
  $root[85895051] = 'Jewelry';
  $root[3828871] = 'Kitchen';
  $root[2250738051] = 'KindleStore';
  $root[561956] = 'Music';
  $root[2128134051] = 'MP3Downloads';
  $root[2123629051] = 'MusicalInstruments';
  $root[86731051] = 'OfficeProducts';
  $root[2127209051] = 'PCHardware';
  $root[2127212051] = 'PetSupplies';
  $root[2016926051] = 'Shoes';
  $root[637392] = 'Software';
  $root[14304371] = 'SportingGoods';
  $root[13299531] = 'Toys';
  $root[2130989051] = 'VHS';
  $root[637394] = 'VideoGames';
  $root[324025011] = 'Watches';

  $url = get_xml_url(array(
    "Service" => "AWSECommerceService",
    "AWSAccessKeyId" => $awsaccess_key,
    "AssociateTag" => $associate_tag,
    "Operation" => "BrowseNodeLookup",
    "Version" => $version,
    "BrowseNodeId" => $node,
    "Timestamp" => gmdate('Y-m-d\TH:i:s\Z')
  ));

  $result = request($url);

  $index = 'unknown search index';

  if ($result != '') {
    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING,0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE,1);
    xml_parse_into_struct($parser, $result, $values, $tags);
    xml_parser_free($parser);

    foreach ($values as $key=>$val) {
      switch ($val['tag']) {
      case 'BrowseNodeId':
        $nodeId = $val['value'];

        if (isset($root[$nodeId])) {
          $index = $root[$nodeId];
          break 2;
        }
        break;
      }
    }
  } else {
    echo "no result\n";
  }
  return $index;
}

function get_xml_url($params) {
  global $access_secret;  
  $baseurl = 'http://ecs.amazonaws.jp/onca/xml';
  ksort($params);

  $canonical_string = ''; 
  foreach ($params as $k => $v) {
    $canonical_string .= '&'.urlencode_rfc3986($k).'='.urlencode_rfc3986($v);
  }   
  $canonical_string = substr($canonical_string, 1); 

  $parsed_url = parse_url($baseurl);
  $string_to_sign = "GET\n{$parsed_url['host']}\n{$parsed_url['path']}\n{$canonical_string}";
  $signature = base64_encode(hash_hmac('sha256', $string_to_sign, $access_secret, true));
  $url = $baseurl.'?'.$canonical_string.'&Signature='.urlencode_rfc3986($signature);

  return $url;
}

function urlencode_rfc3986($str) {
    return str_replace('%7E', '~', rawurlencode($str));
}

$index = get_serach_index($node);
echo "The index of node $node is $index.\n";

?>
