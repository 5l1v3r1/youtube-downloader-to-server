<?php

// What YouTube URL was provided to us?
$url = (string) @$_GET['url'];

/**
 * Define a function to extract a YouTube Video ID from a URL.
 *
 * @param    string    $url    A YouTube Video URL
 * @return    mixed            If successful, will return a string. If failed, will return NULL
 */

function getYouTubeVideoIdFromUrl( $url )
{
preg_match( "/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $url, $matches );

// If this match exists.
if ( sizeof( $matches ) >= 2 && strlen( $matches[1] ) )
{
    return $matches[1];
}

return NULL;
}

/**
* Define a function to extract a YouTube encoded stream URL.
*
* @param    array    $streams    An array of streams provided to us from YouTube's "get_video_info" page.
* @return    mixed                If successful, will return the MP4 stream URL. If failed, will return NULL
 */

function getMP4FromEncodedStream( $streams )
{
foreach( $streams as $stream )
{
    // Decode this stream's data.
    parse_str( $stream, $data );

    // If we found our MP4 stream source.
    if ( stripos( $data['type'], 'video/mp4' ) === 0 )
    {
        return $data['url'];
    }
}

// We didn't find any, whoops..
return NULL;
}

// Try to validate their request.
try
{
// If an invalid YouTube URL was provided.
if ( ( $videoId = getYouTubeVideoIdFromUrl( $url ) ) === NULL )
{
    throw new Exception( 'An invalid YouTube Video URL was provided.' );
}

// Retrieve all information pertaining to this Video; more specifically, we're looking for the encoded video streams.
parse_str( file_get_contents( 'http://youtube.com/get_video_info?video_id=' . $videoId ), $videoData );

// If there's a problem extracting information.
if ( @$videoData['status'] == 'fail' )
{
    throw new Exception( $videoData['reason'] );
}

// Were we able to locate an encoded stream in MP4 format?
if ( ( $streamUrl = getMP4FromEncodedStream( explode( ',', $videoData['url_encoded_fmt_stream_map'] ) ) ) === NULL )
{
    throw new Exception( 'No MP4 video source was able to be located.' );
}

// Where will we be saving this Video?
$saveAs = dirname( __FILE__ ) . '\file/' . $videoId . '.mp4';

// Try to open the encoded video stream URL.
if ( $read = @fopen( $streamUrl, 'r' ) )
{
    // Open the file we want to save to.
    $write = @fopen( $saveAs, 'w' );

    // Write the stream to our file.
    $streamReturn = stream_copy_to_stream( $read, $write );

    // Close our files.
    @fclose( $read );
    @fclose( $write );

    // If we were unable to copy from stream.
    if ( $streamReturn === false )
    {
        throw new Exception( 'We were unable to copy this from the stream.' );
    }
}

// If our new file doesn't exist, we have a problem.
if ( !@file_exists( $saveAs ) )
{
    throw new Exception( 'We encountered an issue saving this Video.' );
}

// Everything saved properly, let them know there they can see their file.
print 'Your Video <strong>' . $videoId . '</strong> has been saved to <strong>' . $saveAs . '</strong>';
}
// If something went wrong.
catch( Exception $e )
{
print '<strong>Uh oh, it looks like an error occurred:</strong> ' . $e->getMessage( );
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>ZENDX | Youtube Downloader</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="wrapper">
    <div class="container">
        <header>
            <div class="container">
                <div class="title">
                    042coded web
                </div>
            </div>
        </header>
        <main>
            <div class="container">
                <div class="content">
                    In this sction, just add the url of the youtube video or vimeo video below
                    &nbsp;
                    &nbsp;
                    <form action="index.php" method="get">
                        <input type="text" name="url" class="url" placeholder="e.g: https://www.youtube.com/watch?v=fKw7MvhrHgg">

                        <input type="submit" name="Submit" value="DOWNLOAD">
                    </form>
                </div>
            </div>
        </main>
        <footer>
            <div class="container">
                <div class="dev-credit">
                    Copyright &copy; <a href="http://cyberlifeco.ml" title="Cyberlife Creations">Cyberlife</a>
                </div>
            </div>
        </footer>
    </div>
</div>

</body>
</html>
