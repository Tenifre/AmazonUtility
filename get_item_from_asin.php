<?php
// Search node
$asin = $argv[1];

// Amazon Settings
$awsaccess_key = "";
$access_secret = "";
$associate_tag = "";
$version = "2011-08-01";

// API リクエスト実行
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

// アイテム取得
function get_item($asin) {
  global $awsaccess_key, $associate_tag, $version;

  // APIリクエスト用URL取得
  $url = get_xml_url(array(
    "Service" => "AWSECommerceService",
    "AWSAccessKeyId" => $awsaccess_key,
    "AssociateTag" => $associate_tag,
    "Operation" => "ItemLookup",
    "Version" => $version,
    "ResponseGroup" => "Large",
    "ItemId" => $asin,
    "Timestamp" => gmdate('Y-m-d\TH:i:s\Z')
  ));

  $result = request($url);

  $index = 'unknown search index';

  // レスポンスのパース
  if ($result != '') {

    $xml = simplexml_load_string($result);
    var_dump($xml);exit;

    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING,0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE,1);
    xml_parse_into_struct($parser, $result, $values, $tags);
    xml_parser_free($parser);

    return $values;
  } else {
    echo "no result\n";
  }

  return false;
}

// APIコール用URL生成
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

$item = get_item($asin);

// 出力
var_dump($item);

?>
