<?php
namespace fboes\SmallPhpHelpers;

/**
 * @class Page
 * Page meta data and linking functionality
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   MIT License (MIT)
 */

class Page
{
    protected $title;
    protected $description;
    protected $url;
    protected $sitename;
    protected $language;
    protected $country;
    protected $latitude;
    protected $longitude;

    /**
     * Set basic properties of your site
     * @param string $title       [description]
     * @param string $url         [description]
     * @param string $description Optional
     * @param string $sitename    Optional, fallback will be domain name
     * @throws \Exception
     */
    public function __construct($title, $url, $description = null, $sitename = null)
    {
        $this->title = $title;
        $this->description = $description;

        if (!$this->isAbsoluteUrl($url)) {
            throw new \Exception('No protocol identifier found in URL, please use absolute URL');
        }

        $this->url   = $url;
        $this->sitename = !empty($sitename) ? $sitename : $this->getDomain();
        $this->setLocale('en', 'EN');
    }

    /**
     * Set locale for this page
     * @param [type] $languageCode [description]
     * @param [type] $countryCode  [description]
     * @return  self
     */
    public function setLocale($languageCode, $countryCode)
    {
        $this->language = strtolower($languageCode);
        $this->country  = strtoupper($countryCode);
        return $this;
    }

    /**
     * Return locale code for this page
     * @return string [description]
     */
    public function getLocale()
    {
        return $this->language.'_'.$this->country;
    }

    /**
     * Set WGS 84 coordinates for this page
     * @param float $latitude  [description]
     * @param float $longitude [description]
     * @return  self [description]
     */
    public function setCoordinates($latitude, $longitude)
    {
        $this->latitude  = (float) $latitude;
        $this->longitude = (float) $longitude;
        return $this;
    }

    /**
     * [basicMeta description]
     * @param  string $titlePattern %1$s being the site name, %2$s being the page title
     * @return string HTML
     */
    public function basicMeta($titlePattern = '%2$s - %1$s')
    {
        $meta =
            '<title>'.htmlspecialchars(sprintf($titlePattern, $this->sitename, $this->title)).'</title>'."\n"
            .'<meta name="description" content="'.htmlspecialchars($this->description).'" />'."\n"
        ;
        if (isset($this->latitude) && isset($this->longitude)) {
            $meta .=
                '<meta name="geo.position" content="'.htmlspecialchars($this->latitude.';'.$this->longitude).'" />'."\n"
                .'<meta name="ICBM" content="'.htmlspecialchars($this->latitude.', '.$this->longitude).'" />'."\n"
            ;
        }

        return $meta;
    }

    /**
     * Return HTML for Opengraph Meta tags
     * @see  http://ogp.me/
     * @param  string $imageUrl [description]
     * @param  string $type [description]
     * @return string HTML
     * @throws \Exception
     */
    public function opengraphMeta($imageUrl, $type = 'article')
    {
        if (!$this->isAbsoluteUrl($imageUrl)) {
            throw new \Exception('No protocol identifier found in image URL, please use absolute URL');
        }
        return
            '<meta property="og:site_name" content="'.htmlspecialchars($this->sitename).'" />'."\n"
            .'<meta property="og:title" content="'.htmlspecialchars($this->title).'" />'."\n"
            .'<meta property="og:type" content="'.htmlspecialchars($type).'" />'."\n"
            .'<meta property="og:image" content="'.htmlspecialchars($imageUrl).'" />'."\n"
            .'<meta property="og:url" content="'.htmlspecialchars($this->url).'" />'."\n"
            .'<meta property="og:description" content="'.htmlspecialchars($this->description).'" />'."\n"
        ;
    }

    /**
     * Return Facebook Share URL for this page
     * @return string [description]
     */
    public function facebookUrl()
    {
        return 'https://www.facebook.com/sharer.php?u='.rawurlencode($this->url);
    }

    /**
     * Return iFrame URL with Facebook functionality for this page
     * @return string [description]
     */
    public function facebookIframe()
    {
        return 'https://www.facebook.com/plugins/like.php?locale='
            .rawurlencode($this->getLocale()).'&href='
            .rawurlencode($this->url).'&send=false&layout=button_count&width=120'
            .'&show_faces=false&action=recommend&colorscheme=light&font&height=21'
            ;
    }

    /**
     * Return Twitter Tweet URL for this page
     * @param  string $text  Optional, if not set title of this page will be used as tweet text
     * @return string [description]
     */
    public function twitterUrl($text = '')
    {
        $text = (!empty($text)) ? $text : $this->title;
        return 'https://twitter.com/intent/tweet?original_referer='
            .rawurlencode($this->url).'&source=tweetbutton&text='
            .rawurlencode($text).'&url='.rawurlencode($this->url)
            ;
    }

    /**
     * Return iFrame URL with Twitter functionality for this page
     * @param  string $text  Optional, if not set title of this page will be used as tweet text
     * @return string [description]
     */
    public function twitterIframe($text = '')
    {
        $text = (!empty($text)) ? $text : $this->title;
        return 'https://platform.twitter.com/widgets/tweet_button.html?url='
            .rawurlencode($this->url).'&counturl='
            .rawurlencode($this->url).'&text='
            .rawurlencode($text).'&count=horizontal&lang='
            .rawurlencode($this->language)
            ;
    }

    /**
     * Return Google Plus Share URL for this page
     * @return string [description]
     */
    public function googlePlusUrl()
    {
        return 'https://plus.google.com/share?url='.rawurlencode($this->url);
    }

    /**
     * Return iFrame URL with Google Plus functionality for this page
     * @return string [description]
     */
    public function googlePlusIframe()
    {
        return 'https://plusone.google.com/_/+1/fastbutton?url='
            .rawurlencode($this->url).'&size=medium&count=true&hl='
            .rawurlencode($this->language).'&jsh=m%3B%2F_%2Fapps-static%2F_%2Fjs%2Fgapi%2F__features__%2Frt%3Dj%2F'
            .'ver%3DZRN-6HhYiow.de.%2Fsv%3D1%2Fam%3D!It_EKMXP3lKIo3Dfjw%2Fd%3D1%2F#id=I1_1331298342130&parent='
            .rawurlencode($this->getDomain()).'&rpctoken=309731857&_methods=onPlusOne%2C_ready%2C_close%2C_'
            .'open%2C_resizeMe%2C_renderstart'
            ;
    }


    /**
     * Return URL to share this page via mail.
     * @param  string $subject Pattern: use %1$s for title, %2$s for URL, %3$s for sitename, %4$s for description
     * @param  string $body    Pattern: use %1$s for title, %2$s for URL, %3$s for sitename, %4$s for description
     * @return string [description]
     */
    public function emailUrl($subject = 'Recommendation from %3$s', $body = 'Recommending "%1$s" (%2$s) from %3$s.')
    {
        $subject = sprintf(_($subject), $this->title, $this->url, $this->sitename, $this->description);
        $body    = sprintf(_($body), $this->title, $this->url, $this->sitename, $this->description);

        return 'mailto:?subject='.rawurlencode($subject).'&body='.rawurlencode($body);
    }

    /**
     * Return URL to print this page
     * @return string [description]
     */
    public function printUrl()
    {
        return 'javascript:window.print();';
    }

    /**
     * Optional onclick-attribute value for link to open in nice extra window
     * @return string [description]
     */
    public function popupEvent()
    {
        return "window.open(this.href, 'social', 'width:640,height:320,resizable:yes,toolbar:no');return false;";
    }

    protected function getDomain()
    {
        return preg_replace('#^[a-zA-z]+://([^/]+).*$#', '$1', $this->url);
    }

    protected function isAbsoluteUrl($url)
    {
        return preg_match('#^[a-zA-Z]+://#', $url);
    }
}
