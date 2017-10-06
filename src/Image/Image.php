<?php

namespace Karolina\Image;

use Respect\Validation\Validator as v;

class Image
{
    private $cdnUrl;
    private $filename;
    private $pathToProcessed;
    private $pathToOriginal;
    private $contentKey;

    public function __construct($filename, $contentKey)
    {
        $this->setFilename($filename);
        $this->setContentKey($contentKey);

        $this->pathToOriginal = "/org/";
        $this->pathToProcessed = "/medium/";
        $this->cdnUrl = "https://aws.com/";
    }

    public function __toString()
    {
        return $this->filename;
    }

    private function isExtensionAllowed($filename)
    {
        $filename = strtolower($filename);

        $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');

        foreach ($allowedExtensions as $extensions) {
            if (v::extension($extensions)->validate($filename)) {
                return true;
            }
        }

        throw new \Exception('Image filename extension not allowed.');
    }

    private function isFilenameOk($filename)
    {
        if (v::alnum('.-_')->noWhitespace()->validate($filename)) {
            return true;
        } else {
            throw new \Exception('Image filename contains unallowed characters.');
        }
    }

    public function setPathToOriginal($path)
    {
        $this->pathToOrignial = $path;
    }

    public function setPathToProcessed($path)
    {
        $this->pathToProcessed = $path;
    }

    public function getUrl()
    {
        return $this->getProcessed();
    }

    public function getProcessed()
    {
        return $this->cdnUrl.$this->pathToProcessed.$this->filename;
    }

    public function getOriginal()
    {
        return $this->cdnUrl.$this->pathToOriginal.$this->filename;
    }

    // Content key tells you in what context the image is to be displayed
    // i.e. main_image, author_picture, etc

    public function setContentKey($key)
    {
        if (v::alnum('.-_')->noWhitespace()->validate($key)) {
            $this->contentKey = $key;
        } else {
            throw new \Exception('Content key for image must only contain alnum underscore or dash.');
        }
    }

    public function getContentKey()
    {
        return $this->contentKey;
    }

    public function setFilename($name)
    {
        if ($this->isExtensionAllowed($name) and $this->isFilenameOk($name)) {
            $this->filename = $name;
        }
    }

    public function getFilename()
    {
        return $this->filename;
    }
}
