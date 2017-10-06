<?php
namespace Karolina\API\v1;

class ImageCollectionResponse extends Response
{
    private $imagePath;
    private $document;
        
    public function __construct($document, $imagePath)
    {
        $this->document = $document;
        $this->imagePath = $imagePath;
    }

    public function get()
    {
        $doc = $this->document;
        $imgStorageUrl = $this->imagePath;

        $response = array();

        // Put absolute urls for images
        foreach ($doc as $contentKey => $imageData) {
            $response[$contentKey]['filename'] = $imageData['filename'];
            $response[$contentKey]['url'] = $imgStorageUrl."/".$imageData['filename'];
        }

        return $response;
    }
}
