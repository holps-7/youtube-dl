<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=UA-171643906-1"></script>
		<script>
  			window.dataLayer = window.dataLayer || [];
  			function gtag(){dataLayer.push(arguments);}
  			gtag('js', new Date());

  			gtag('config', 'UA-171643906-1');
		</script>
        <meta charset="UTF-8">
        <title>YouTube Downloader | howtohack.xyz</title>
        <link rel="stylesheet" type="text/css" href="styles.css"/>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/trianglify/1.1.0/trianglify.min.js" integrity="sha384-tkkxOIuCkeNYfk85zCCUg0gvL6zxEGaONKj/9+VSGodKbMmd/Mwyh6e6GgsD0TOB" crossorigin="anonymous"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js" integrity="sha384-vk5WoKIaW/vJyUAd9n/wmopsmNhiy+L2Z+SBxGYnUkunIxVxAv/UtMOhba/xskxh" crossorigin="anonymous"></script>
    </head>

    <body>
        <script src="./js/pattern.js"></script>
        <div id="container">
            <div style="text-align:center;">
                <h1 style="color:blue;">YouTube Downloader</h1>
                <h2>Download anything on YouTube!!!</h2>
                <h4>On <a href="https://ytdl.howtohack.xyz/">ytdl.howtohack.xyz</a> you can download videos from YouTube except Private YouTube Videos</h4>
            </div>
            <div id="instructions">
                <p>Steps for downloading the videos:</p>
                <ol>
                    <li><span>Enter YouTube video link in the box</span></li>
                    <li>Click "Fetch" button</li>
                    <li>Play the Video</li>
                    <li>Download it!</li>
                </ol>
            </div>

            <div id="frm">
                <form method="post">
                    <input id="txt_url" type="text" placeholder="https://www.youtube.com/" size="80">
                    <input id="btn_fetch" type="submit" value="Fetch" name="clicks">
                </form>
            </div>
            <div id="fetch_count">
                <!------ DBMS code not included ----->
                <p id="count">Total videos fetched till now: <span id="count_number">1038</span></p>
            </div>
        </div>


        <div id="vid_container">
            <video max-width=100% height=auto controls>
                <source src="" type="video/mp4"/>
                <em>Sorry, your browser doesn't support HTML5 video.</em>
            </video>

            <script type="text/javascript" nonce="8a64fbd14ad50b4a3c096a6ed4cffe8f">
                $(function () {
                    $("#btn_fetch").click(function () {
                        var url = $("#txt_url").val();
                        var oThis = $(this);
                        oThis.attr('disabled', true);
                        $.get('video_info.php', {url: url}, function (data) {
                           console.log(data);
                            oThis.attr('disabled', false);
                            var links = data['links'];
                            var error = data['error'];
                            if (error) {
                                alert('Error: ' + error);
                                return;
                            }
                            var first = links.find(function (link) {
                                return link['format'].indexOf('video') !== -1;
                            });
                            if (typeof first === 'undefined') {
                                alert('No video found!');
                                return;
                            }
                            var stream_url = 'stream.php?url=' + encodeURIComponent(first['url']);
                            var video = $("video");
                            video.attr('src', stream_url);
                            video[0].load();
                        });
                    });
                });
            </script>
        </div>

        <div id="footer">
            Powered by <a href="https://www.howtohack.xyz/" target="_blank">howtohack.xyz</a>
        </div>
        
    </body>
</html>

