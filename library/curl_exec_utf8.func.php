<?php

/**
 * The same as curl_exec except tries its best to convert the output to utf8.
 * 
 * @source http://stackoverflow.com/questions/2510868/php-convert-curl-exec-output-to-utf8
 */
function curl_exec_utf8($ch)
{
    $data = curl_exec($ch);
    if (!is_string($data)) {
        return $data;
    }

    unset($charset);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

    /* 1: HTTP Content-Type: header */
    preg_match('@([\w/+]+)(;\s*charset=(\S+))?@i', $content_type, $matches);
    if (isset($matches[3])) {
        $charset = $matches[3];
    }

    /* 2: <meta> element in the page */
    if (!isset($charset)) {
        //preg_match( '@<meta\s+http-equiv="Content-Type"\s+content="([\w/]+)(;\s*charset=([^\s"]+))?@i', $data, $matches );
        //if ( isset( $matches[3] ) )
        //    $charset = $matches[3];
        preg_match('/<[\s]*meta[^>]*charset="?([^\s"]+)\s?"/i', $data, $matches);

        if (isset($matches[1])) {
            $charset = $matches[1];
        }
    }

    /* 3: <xml> element in the page */
    if (!isset($charset)) {
        preg_match('@<\?xml.+encoding="([^\s"]+)@si', $data, $matches);
        if (isset($matches[1])) {
            $charset = $matches[1];
        }
    }

    /* 4: PHP's heuristic detection */
    if (!isset($charset)) {
        $encoding = mb_detect_encoding($data);
        if ($encoding) {
            $charset = $encoding;
        }
    }

    /* 5: Default for HTML */
    if (!isset($charset)) {
        if (strstr($content_type, 'text/html') === 0) {
            $charset = 'ISO 8859-1';
        }
    }

    /* Convert it if it is anything but UTF-8 */
    /* You can change "UTF-8"  to "UTF-8//IGNORE" to 
      ignore conversion errors and still output something reasonable */
    if (isset($charset) && (strtoupper($charset) != 'UTF-8')) {
        $data = iconv($charset, 'UTF-8', $data);
    }

    return $data;
}
