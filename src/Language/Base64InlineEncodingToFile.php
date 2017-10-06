<?php

namespace Karolina\Language;

use Respect\Validation\Validator as v;

class Base64InlineEncodingToFile
{
    private $html;
    private $imageFileRepository;
    private $fullPath;
    private $folder;

    public function __construct($imageFileRepository, $folder, $fullPath)
    {
        $this->imageFileRepository = $imageFileRepository;
        $this->fullPath = $fullPath;
        $this->folder = $folder;
    }

    public function processImages($html)
    {
        $dom = \pQuery::parseStr($html);

        foreach ($dom->query('img') as $img) {
            $string = $img->attr('src');
            $img->attr('src', $this->uploadStringToImage($string));
        }

        return $dom->html();
    }
    

    public function uploadStringToImage($string)
    {
        if (!v::url()->validate($string)) {
            $data = explode(',', $string);
            $file = base64_decode($data[1]);
            $f = finfo_open();

            $mimeType = finfo_buffer($f, $file, FILEINFO_MIME_TYPE);

            if ($mimeType == 'image/jpeg') {
                $extension = "jpg";
            } elseif ($mimeType == 'image/jpg') {
                $extension = "jpg";
            } elseif ($mimeType == 'image/gif') {
                $extension = "gif";
            } elseif ($mimeType == 'image/png') {
                $extension = "png";
            } else {
                throw new \Karolina\Exception('Not a valid image format', 'invalid_arguments');
            }

            $newfileName = $this->imageFileRepository->storeBinaryImage($file, $this->folder, $extension);

            return $this->fullPath.'/'.$newfileName;
        }

        return $string;
    }
}
