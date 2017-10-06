<?php

namespace Karolina\Content;

class Link
{
    private $url;
    private $title;

    public function __construct($url, $title = "")
    {
        $this->setUrl($url);
        $this->title = $title;
    }

    private function setUrl($url)
    {
        $url = trim($url);

        if (strpos($url, 'http://') === 0 or strpos($url, 'https://') === 0) {
        } else {
            $url = "http://".$url;
        }

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $this->url = $url;
        } else {
            throw new \Karolina\Exception('This link is invalid: '.$url, 'invalid_link');
        }
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getSite()
    {
        $domain = parse_url($this->url, PHP_URL_HOST);


        $knownSites = ['facebook.com', 'twitter.com', 'linkedin.com', 'instagram.com'];

        foreach ($knownSites as $site) {
            if ($this->endsWith($domain, '.'.$site) or $this->startsWith($domain, $site)) {
                return $site;
            }
        }

        return $domain;
    }

    private function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    private function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }
}

class LinkDocument
{
    private $link;
    public function __construct(Link $link)
    {
        $this->link = $link;
    }

    public function get()
    {
        $doc['url'] = $this->link->getUrl();
        $doc['title'] = $this->link->getTitle();
        $doc['site'] = $this->link->getSite();

        return $doc;
    }
}
