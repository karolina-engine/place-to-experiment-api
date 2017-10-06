<?php

namespace Karolina\Language;

class Field
{
    private $value;
    private $format;

    public function __construct($value, $format = "plaintext")
    {
        $this->value = $value;
        $this->format = $format;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function getAsHTML()
    {
        switch ($this->format) {
            case 'plaintext':
                    $entities = $this->htmlsafe($this->value);
                    $html = nl2br($entities);
                    return $html;
                break;
            case 'json':
                    $html = $this->jsonToHTML($this->value);
                    return $html;
                break;
            case 'html':

                    $dirty_html = $this->value;
                    $config = \HTMLPurifier_Config::createDefault();
                    $purifier = new \HTMLPurifier($config);
                    $clean_html = $purifier->purify($dirty_html);
                    $html = $clean_html;
                    
                    return $html;
                break;

            default:
                $html = $this->htmlsafe($this->value);
                return $html;
                break;
        }
    }

    public function __toString()
    {
        return (string) $this->value;
    }

    private function htmlsafe($content)
    {
        return htmlentities($content, ENT_QUOTES, 'UTF-8');
    }

    private function markdown($content)
    {
        $converter = new \League\CommonMark\CommonMarkConverter([
            'renderer' => [
                'block_separator' => "\n",
                'inner_separator' => "\n",
                'soft_break'      => "\n",
            ],
            'enable_emphasis' => true,
            'enable_strong' => true,
            'use_asterisk' => true,
            'use_underscore' => true,
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);

        return $converter->convertToHtml($content);
    }

    private function jsonToHTML($json)
    {
        if ($json and $json != "") {
            $converter = new \Karolina\Language\JsonToHtml($json);
            return $converter->getHTML();
        } else {
            return "";
        }
    }
}
