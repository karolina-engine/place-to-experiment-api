<?php

namespace Karolina\Image;

use Respect\Validation\Validator as v;

Class ImageFileRepository {

	private $s3;

	public function __construct ($s3) {

		$this->s3 = $s3;

	}

	private function getUniqueName ($extension) {

		$uuid = new \Karolina\UUID();

		$uniqueFilename = $uuid->getString().'.'.$extension;
		return $uniqueFilename;

	}

	private function isExtensionAllowed ($extension) {

		$allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');

		if (in_array($extension, $allowedExtensions)) {

			return true;

		} else {
		
			$extension = preg_replace("/[^a-z0-9.]+/i", "", $extension);
			throw new \Karolina\KarolinaException('File extension: '.$extension.' not allowed. Try jpg, png or gif.', 400, null, 'invalid_file');
		}

	}

	private function isImage ($file) {

		if (v::image()->validate($file)) {

			return true;

		} else {

			throw new \Karolina\KarolinaException('File does not appear to be an image', 400, null, 'invalid_file');
		}

	}

	private function isDestionationFolderAllowed ($destionationFolder) {

		if (v::alnum('-_')->noWhitespace()->validate($destionationFolder)) {

			return true;

		} else {

			throw new \Karolina\KarolinaException('Destination folder incorrect', 500, null, 'error');

		}


	}

	public function storeBinaryImage ($image, $destinationFolder, $extension) {

		$newFilename = $this->getUniqueName($extension);
		if ($this->isDestionationFolderAllowed($destinationFolder)
			and $this->isExtensionAllowed($extension)
			) {


			// Upload an object to Amazon S3
			$result = $this->s3->putObject(array(
			    'Bucket' => 'agitator-image-host',
			    'Key'    => $destinationFolder.'/'.$newFilename,
			    'Body' => $image

			));			
			return $newFilename;

		}


	}

	public function storeImageFile ($file, $destinationFolder) {

		// Is it a valid image?

		$clientProvidedExtension = strtolower($file->getClientOriginalExtension());
		$uploadedFilePath = $file->getRealPath();
		$newFilename = $this->getUniqueName($clientProvidedExtension);

		if (
			$this->isExtensionAllowed($clientProvidedExtension) 
			and $this->isImage($file)
			and $this->isDestionationFolderAllowed($destinationFolder)
			) {
	
			// Upload an object to Amazon S3
			$result = $this->s3->putObject(array(
			    'Bucket' => 'agitator-image-host',
			    'Key'    => $destinationFolder.'/'.$newFilename,
			    'SourceFile' => $uploadedFilePath

			));

			return $newFilename;

		}


	}

}