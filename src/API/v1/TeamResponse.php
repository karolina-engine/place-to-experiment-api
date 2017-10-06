<?php
namespace Karolina\API\v1;

class TeamResponse extends Response
{
    private $document;
    private $imagePath;
        
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

        // Put absolute urls for teams
        foreach ($doc as $memberId => $memberData) {
            if ($doc[$memberId]['image'] != null) {
                $response[$memberId]['image'] = $imgStorageUrl."/".$memberData['image'];
            } else {
                $response[$memberId]['image'] = null;
            }

            $response[$memberId]['profile_id'] = (string) $memberId;
            $response[$memberId]['first_name'] = $memberData['first_name'];
            $response[$memberId]['last_name'] = $memberData['last_name'];
        }

        return $response;
    }
}
