<?php

namespace YouTube;
class YouTubeDownloader
{
    protected $client;
    protected $error;

    function __construct()
    {
        $this->client = new Browser();
    }

    public function getBrowser()
    {
        return $this->client;
    }

    public function getLastError()
    {
        return $this->error;
    }
    public function getPlayerUrl($video_html)
    {
        $player_url = null;
        if (preg_match('@<script\s*src="([^"]+player[^"]+js)@', $video_html, $matches)) {
            $player_url = $matches[1];
            if (strpos($player_url, '//') === 0) {
                $player_url = 'http://' . substr($player_url, 2);
            } elseif (strpos($player_url, '/') === 0) {
                $player_url = 'http://www.youtube.com' . $player_url;
            }
        }

        return $player_url;
    }

    public function getPlayerCode($player_url)
    {
        $contents = $this->client->getCached($player_url);
        return $contents;
    }

    public function extractVideoId($str)
    {
        if (preg_match('/[a-z0-9_-]{11}/i', $str, $matches)) {
            return $matches[0];
        }

        return false;
    }

    /**
     * @param array $links
     * @param string $selector mp4, 360, etc...
     * @return array
     */
    private function selectFirst($links, $selector)
    {
        $result = array();
        $formats = preg_split('/\s*,\s*/', $selector);
        foreach ($formats as $f) {
            foreach ($links as $l) {
                if (stripos($l['format'], $f) !== false || $f == 'any') {
                    $result[] = $l;
                }
            }
        }
        return $result;
    }

    public function getPageHtml($url)
    {
        $video_id = $this->extractVideoId($url);
        return $this->client->get("https://www.youtube.com/watch?v={$video_id}");
    }

    public function getPlayerResponse($page_html)
    {
        if (preg_match('/player_response":"(.*?)","/', $page_html, $matches)) {
            $match = stripslashes($matches[1]);
            $ret = json_decode($match, true);
            return $ret;
        }
        return null;
    }

    public function parsePlayerResponse($player_response, $js_code)
    {
        $parser = new Parser();
        try {
            $formats = $player_response['streamingData']['formats'];
            $adaptiveFormats = $player_response['streamingData']['adaptiveFormats'];
            if (!is_array($formats)) {
                $formats = array()
            if (!is_array($adaptiveFormats)) {
                $adaptiveFormats = array();
            }
            $formats_combined = array_merge($formats, $adaptiveFormats);
            $return = array();
            foreach ($formats_combined as $item) {
            	$cipher = '';
                if(isset($item['cipher']) || isset($item['signatureCipher'])) {
					$cipher = isset($item['cipher']) ? $item['cipher'] : $item['signatureCipher'];
				}
                $itag = $item['itag'];
                if (isset($item['url'])) {
                    $return[] = array(
                        'url' => $item['url'],
                        'itag' => $itag,
                        'format' => $parser->parseItagInfo($itag)
                    );
                    continue;
                }
                parse_str($cipher, $result);
                $url = $result['url'];
                $sp = $result['sp'];
                $signature = $result['s'];
                $decoded_signature = (new SignatureDecoder())->decode($signature, $js_code);
                $return[] = array(
                    'url' => $url . '&' . $sp . '=' . $decoded_signature,
                    'itag' => $itag,
                    'format' => $parser->parseItagInfo($itag)
                );
            }
            return $return;

        } catch (\Exception $exception) {
        } catch (\Throwable $throwable) {
        }
        return null;
    }

    public function getDownloadLinks($video_id, $selector = false)
    {
        $this->error = null;
        $page_html = $this->getPageHtml($video_id);
        if (strpos($page_html, 'We have been receiving a large volume of requests') !== false ||
            strpos($page_html, 'systems have detected unusual traffic') !== false) {
            $this->error = 'HTTP 429: Too many requests.';
            return array();
        }
        $json = $this->getPlayerResponse($page_html);
        $url = $this->getPlayerUrl($page_html);
        $js = $this->getPlayerCode($url);
        $result = $this->parsePlayerResponse($json, $js);
        if (!is_array($result)) {
            return array();
        }
        if ($selector) {
            return $this->selectFirst($result, $selector);
        }
        return $result;
    }
}
