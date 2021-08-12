<?php
// Clears the cache and prevent unwanted output
ob_clean();
/*
@ini_set('error_reporting', E_ALL & ~ E_NOTICE);
@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 'Off');
*/
$file = urldecode($_GET["p"]);
//$file = "Videos/2021/driving.mp4"; // The media file's location
//$file = "Videos/2021/DJI_0084.MP4";
$mime = "video/mp4"; // The MIME type of the file, this should be replaced with your own.
$size = filesize($file); // The size of the file
// Send the content type header
header('Content-type: ' . $mime);

// Check if it's a HTTP range request
if(isset($_SERVER['HTTP_RANGE'])){
    // Parse the range header to get the byte offset
    $ranges = array_map(
        'intval', // Parse the parts into integer
        explode(
            '-', // The range separator
            substr($_SERVER['HTTP_RANGE'], 6) // Skip the `bytes=` part of the header
        )
    );

    // If the last range param is empty, it means the EOF (End of File)
    if(!$ranges[1]){
        $ranges[1] = $size - 1;
    }

    // Send the appropriate headers
    header('HTTP/1.1 206 Partial Content');
    header('Accept-Ranges: bytes');
    header('Content-Length: ' . ($ranges[1] - $ranges[0])); // The size of the range

    // Send the ranges we offered
    header(
        sprintf(
            'Content-Range: bytes %d-%d/%d', // The header format
            $ranges[0], // The start range
            $ranges[1], // The end range
            $size // Total size of the file
        )
    );

    // It's time to output the file
    $f = fopen($file, 'rb'); // Open the file in binary mode
    $chunkSize = 8192; // The size of each chunk to output

    // Seek to the requested start range
    fseek($f, $ranges[0]);

    // Start outputting the data
    while(true){
        // Check if we have outputted all the data requested
        if(ftell($f) >= $ranges[1]){
            break;
        }

        // Output the data
        echo fread($f, $chunkSize);

        // Flush the buffer immediately
        @ob_flush();
        flush();
    }
}
else {
    // It's not a range request, output the file anyway
    header('Content-Length: ' . $size);

    // Read the file
    @readfile($file);

    // and flush the buffer
    @ob_flush();
    flush();
}

/*
    $file = 'Videos/2021/driving.mp4';
    $fp = @fopen($file, 'rb');
    $size   = filesize($file); // File size
    $length = $size;           // Content length
    $start  = 0;               // Start byte
    $end    = $size - 1;       // End byte
    header('Content-type: video/mp4');
    //header("Accept-Ranges: 0-$length");
    header("Accept-Ranges: bytes");
    if (isset($_SERVER['HTTP_RANGE'])) {
        $c_start = $start;
        $c_end   = $end;
        list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
        if (strpos($range, ',') !== false) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes $start-$end/$size");
            exit;
        }
        if ($range == '-') {
            $c_start = $size - substr($range, 1);
        }else{
            $range  = explode('-', $range);
            $c_start = $range[0];
            $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
        }
        $c_end = ($c_end > $end) ? $end : $c_end;
        if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes $start-$end/$size");
            exit;
        }
        $start  = $c_start;
        $end    = $c_end;
        $length = $end - $start + 1;
        fseek($fp, $start);
        header('HTTP/1.1 206 Partial Content');
    }
    header("Content-Range: bytes $start-$end/$size");
    header("Content-Length: ".$length);
    $buffer = 1024 * 8;
    while(!feof($fp) && ($p = ftell($fp)) <= $end) {
        if ($p + $buffer > $end) {
            $buffer = $end - $p + 1;
        }
        set_time_limit(0);
        echo fread($fp, $buffer);
        flush();
    }
    fclose($fp);
    exit();
    */
    ?>