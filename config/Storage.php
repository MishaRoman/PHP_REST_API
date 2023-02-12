<?php 

class Storage
{
	public static function saveImage(array $file, string $uploadPath): string
	{
		$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

		$valid_extensions = ['jpg', 'jpeg', 'png'];

		if(!in_array($fileExtension, $valid_extensions)) {
			throw new Exception("File extension is not supported");
		}

		if($file['size'] > 5000000) {
			throw new Exception("Maximum filesize is 5MB");
		}

		$filename = self::generateRandomString(12) . '.' . $fileExtension;
		
		move_uploaded_file($file['tmp_name'], $uploadPath . '/' . $filename);

		return $filename;
	}

	private static function generateRandomString(int $length = 10): string
	{
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[random_int(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}
}