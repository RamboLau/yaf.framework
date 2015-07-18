<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2015 panjun.liu <http://176code.com lpj163@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * The requested URL path.
 *
 * @return string
 */
function current_path() {
  static $path;

  if (isset($path)) {
    return $path;
  }
  if (isset($_SERVER['REQUEST_URI'])) {
    // This request is either a clean URL, or 'index.php', or nonsense.
    // Extract the path from REQUEST_URI.
    $request_path = strtok($_SERVER['REQUEST_URI'], '?');
    $base_path_len = strlen(rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/'));
    // Unescape and strip $base_path prefix, leaving q without a leading slash.
    $path = substr(urldecode($request_path), $base_path_len + 1);
    // If the path equals the script filename, either because 'index.php' was
    // explicitly provided in the URL, or because the server added it to
    // $_SERVER['REQUEST_URI'] even when it wasn't provided in the URL (some
    // versions of Microsoft IIS do this), the front page should be served.
    if ($path == basename($_SERVER['PHP_SELF'])) {
      $path = '';
    }
  }
  else {
    // This is the front page.
    $path = '';
  }

  // Under certain conditions Apache's RewriteRule directive prepends the value
  // assigned to $_GET['q'] with a slash. Moreover we can always have a trailing
  // slash in place, hence we need to normalize $_GET['q'].
  $path = trim($path, '/');

  return $path;	
}

/**
 * Encodes special characters in a plain-text string for display as HTML.
 * 
 * @param string $text 
 *
 * @return string
 */
function check_plain($text) {
  return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * format unixtime to string time.
 *
 * @param int $time
 *
 * @return string
 */
function time_format($time = NULL, $format = 'Y-m-d G:i:s'){
  $time = $time === NULL ? time() : intval($time);
  return date($format, $time);
}

/**
 * Encrypt special characters in a crypt string. 
 *
 * @param string $text 
 *
 * @return string
 */
function encrypt($text) {
  // init no repeat string, include A-Z,a-z,0-9,/,=,+,_,
  $lockstream = 'st=lDEFABCNOPyzghi_jQRST-UwxkVWXYZabcdefIJK6/7nopqr89LMmGH012345uv';
  $lockLen = strlen($lockstream);
  $lockCount = rand(0, $lockLen-1);
  $randomLock = $lockstream[$lockCount];
  // create a md5 string
  $password = md5(CRYPT_PASSWORD . $randomLock);
  // create a base64 string
  $text = base64_encode($text);
  $tmpStream = '';
  $i = 0; $j = 0; $k = 0;
  for ($i = 0; $i < strlen($text); $i++) {
    $k = ($k == strlen($password)) ? 0 : $k;
    $j = (strpos($lockstream, $text[$i]) + $lockCount + ord($password[$k])) % ($lockLen);
    $tmpStream .= $lockstream[$j];
    $k++;
  }
  return $tmpStream . $randomLock;
}

/**
 * Decrypt special characters in a normal string.
 *
 * @param string $text
 *
 * @return string
 */
function decrypt($text) {
  // init no repeat string, include A-Z,a-z,0-9,/,=,+,_,
  $lockstream = 'st=lDEFABCNOPyzghi_jQRST-UwxkVWXYZabcdefIJK6/7nopqr89LMmGH012345uv';
  $lockLen = strlen($lockstream);
  $txtLen = strlen($text);
  $randomLock = $text[$txtLen - 1];
  $lockCount = strpos($lockstream, $randomLock);
  $password = md5(CRYPT_PASSWORD . $randomLock);
  $text = substr($text, 0, $txtLen-1);
  $tmpStream = '';
  $i = 0; $j = 0; $k = 0;
  for ($i = 0; $i < strlen($text); $i++) {
    $k = ($k == strlen($password)) ? 0 : $k;
    $j = strpos($lockstream, $text[$i]) - $lockCount - ord($password[$k]);
    while ($j < 0){
      $j = $j + ($lockLen);
    }
    $tmpStream .= $lockstream[$j];
    $k++;
  }
  return base64_decode($tmpStream);
}

/**
 * Determine if SSL is used.
 *
 * @return bool TRUE if SSL, false if not used.
 */
function is_ssl() {
  if (isset($_SERVER['HTTPS'])) {
    if('on' == strtolower($_SERVER['HTTPS'])) 
      return TRUE;

    if(1 == $_SERVER['HTTPS']) 
      return TRUE; 
  }
  else if (isset($_SERVER['SERVER_PORT']) && 443 == $_SERVER['SERVER_PORT']) {
    return TRUE;
  }

  return FALSE;
}

/**
 * Determine if ip address is correct.
 *
 * @param string $ip_address
 *
 * @return bool TRUE if ip address is correct, false if not.
 */
function is_ip($ip_address) {
  $ip_address = check_plain(trim($ip_address));
  if (!$ip_address) 
    return FALSE;

  if (filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE) == FALSE) 
    return FALSE;

  return TRUE;
}

/**
 * Determine if request method is post.
 *
 * @return bool TRUE if method is post, false if not.
 */
function is_post() {
  if(isset($_SERVER['REQUEST_METHOD']) && 'post' == strtolower($_SERVER['REQUEST_METHOD']))
    return TRUE;

  http_response_code(405);
  return FALSE;
}

/**
 * Get or Set the HTTP response code
 *
 * @param int $code
 *
 * @return If you pass no parameters then http_response_code will get the current status code. If you pass a parameter it will set the response code.
 */
if (!function_exists('http_response_code')) {
  function http_response_code($code = NULL) {
      if ($code !== NULL) {
        switch ($code) {
            case 100: $text = 'Continue'; break;
            case 101: $text = 'Switching Protocols'; break;
            case 200: $text = 'OK'; break;
            case 201: $text = 'Created'; break;
            case 202: $text = 'Accepted'; break;
            case 203: $text = 'Non-Authoritative Information'; break;
            case 204: $text = 'No Content'; break;
            case 205: $text = 'Reset Content'; break;
            case 206: $text = 'Partial Content'; break;
            case 300: $text = 'Multiple Choices'; break;
            case 301: $text = 'Moved Permanently'; break;
            case 302: $text = 'Moved Temporarily'; break;
            case 303: $text = 'See Other'; break;
            case 304: $text = 'Not Modified'; break;
            case 305: $text = 'Use Proxy'; break;
            case 400: $text = 'Bad Request'; break;
            case 401: $text = 'Unauthorized'; break;
            case 402: $text = 'Payment Required'; break;
            case 403: $text = 'Forbidden'; break;
            case 404: $text = 'Not Found'; break;
            case 405: $text = 'Method Not Allowed'; break;
            case 406: $text = 'Not Acceptable'; break;
            case 407: $text = 'Proxy Authentication Required'; break;
            case 408: $text = 'Request Time-out'; break;
            case 409: $text = 'Conflict'; break;
            case 410: $text = 'Gone'; break;
            case 411: $text = 'Length Required'; break;
            case 412: $text = 'Precondition Failed'; break;
            case 413: $text = 'Request Entity Too Large'; break;
            case 414: $text = 'Request-URI Too Large'; break;
            case 415: $text = 'Unsupported Media Type'; break;
            case 500: $text = 'Internal Server Error'; break;
            case 501: $text = 'Not Implemented'; break;
            case 502: $text = 'Bad Gateway'; break;
            case 503: $text = 'Service Unavailable'; break;
            case 504: $text = 'Gateway Time-out'; break;
            case 505: $text = 'HTTP Version not supported'; break;
            default:
                exit('Unknown http status code "' . htmlentities($code) . '"');
            break;
        }

        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

        header($protocol . ' ' . $code . ' ' . $text);

        $GLOBALS['http_response_code'] = $code;

    } 
    else {
      $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
    }

    return $code;
  }
}