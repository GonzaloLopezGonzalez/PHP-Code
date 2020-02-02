<?php

class image
{
	/**
	* Image size
	* @access private
	* @var integer
	*/
	private $size;	
	/**
	* Image width
	* @access private
	* @var integer
	*/
	private $width;	
	/**
	* Image height
	* @access private
	* @var integer
	*/
	private $height;
	/**
	* Image mimetype
	* @access private
	* @var string
	*/
	private $mimetype;
	/**
	* Image gd resource
	* @access public
	* @var private
	*/
	private $image;
	/**
	* Image path
	* @access private
	* @var string
	*/
	private $image_file;
	
	public function __construct($image)
	{
		if (!extension_loaded('gd'))
		{
			die('Please install GD library');	
		}
		
		try
		{
			$this->loadImage($image);
		}
		catch (Exception $e)
		{
			die($e->getMessage());
		}
	}
	
	public function __destruct()
	{
		imagedestroy($this->image);
	}
	
	/**
	* Creates the image object according its myme type
	*
	* This method loads the image using gd library
	*
	* @access private
	* @param string $image Image path and image name
	* @return true
	*/
	private function loadImage($image)
	{
		if (file_exists($image))
		{
			$this->image_file = $image;
			$this->mimetype = mime_content_type($image);
			switch ($this->mimetype)
			{
				case 'image/jpeg':
					$this->image = imagecreatefromjpeg($image);
					break;
				case 'image/png':
					$this->image = imagecreatefrompng($image);
					break;
				case 'image/gif':
					$this->image = imagecreatefromgif($image);
					break;
				case 'image/bmp':
					$this->image = imagecreatefrombmp($image);
					break;
				default:
					throw new Exception ('File uploaded is not a image.');
			
			}
			$this->width = imagesx($this->image);
			$this->height = imagesy($this->image);
			$this->size = intval(filesize($this->image_file) / 1024);
			
			return true;
		}
		else
		{
			throw new Exception ('File not exists.');
		}
	}
	/**
	* Get image width
	*
	* Method to get image width
	*
	* @access public
	*/
	public function getWidth()
	{
		$this->width;;
	}
	
	/**
	* Get image height
	*
	*  Method to get image height
	*
	* @access public
	*/	
	public function getHeight()
	{
		return $this->height;
	}
	
	/**
	* Get image size
	*
	* Method to get image size (KB)
	*
	* @access public
	*/
	public function getSize()
	{
		return $this->size;
	}
	
	/**
	* Save the image
	*
	* Saves the new image in the specified path
	*
	* @access private
	 @param string $new_picture new image to be saved
	 @param string $destination_path path and image name where the image will be saved
	 
	* Return image object
	*/
	
	private function saveImage($mimetype,$new_picture,$destination_path,$quality = 9)
	{
		if (empty($destination_path))
		{
			throw new Exception ('Introduce the  new file path and name.');
		}
		switch ($mimetype)
		{
			case 'image/jpeg':
				return imagejpeg($new_picture,$destination_path,$quality);
			case 'image/png':
				return imagepng($new_picture,$destination_path,$quality);
			case 'image/gif':
				return imagegif($new_picture,$destination_path,$quality);
		}
	}
	
	/**
	* Resize image
	*
	* This method resizes the image
	*
	* @access public
	* @param string $new_pic path and image name of new image
	* @param integer $newWidth new width
	* @param integer $newHeight new height
	*/
	
	public function resizeImage($new_pic,$newWidth,$newHeight)
	{
		try
		{
			if ($newWidth < 0 OR $newHeight < 0 OR empty($newWidth) OR empty($newHeight))
			{
				throw new Exception ('The dimensions of new image must be greater than 0.');
			}
			
			$ratio = $this->width / $this->height;
			if ($newWidth/$newHeight > $ratio)
			{
				$finalwidth = $newHeight*$ratio;
				$finalheight = $newHeight;
			}
			else
			{
				$finalheight = $newWidth/$ratio;
				$finalwidth = $newWidth;
			}
			$dst = imagecreatetruecolor($finalwidth, $finalheight);
			imagecopyresampled($dst, $this->image, 0, 0, 0, 0, $finalwidth, $finalheight, $this->width, $this->height);
			$this->saveImage($this->mimetype,$dst,$new_pic);

		}
		catch (Exception $e)
		{
			die($e->getMessage());
		}
	}
	
	private function getQuality($quality)
	{
		$img_quality = filter_var($quality, FILTER_VALIDATE_INT);
		if (filter_var($quality, FILTER_VALIDATE_INT))
		{
			return ($quality > 9) ? 9 : $quality;
		}
		else 
		{
			return 9;
		}
	}
	
	
	/**
	* Compress the image
	*
	* This method compresses the image
	*
	* @access public
	* @param string $new_pic path and image name of new image
	* @param string $quality compress quality 0 - 9
	*/
	public function compressImage($new_pic,$quality)
	{
		try
		{
			$imageQuality = $this->getQuality($quality);
			if (!$this->saveImage($this->mimetype,$this->image,$new_pic,$imageQuality))
			{
				throw new Exception ('Problems with compression.');
			}
		}
		catch (Exception $e)
		{
			die($e->getMessage());
		}
	}
	
	/**
	* Image rotation
	*
	* This method rotates the image in the specified angles
	*
	* @access public
	* @param string $new_pic path and image name of new image
	* @param string $degrees angles of image rotation
	*/
	
	public function rotateImage($new_pic,$degrees = 0)
	{
		try{
			$rotated_pic = imagerotate($this->image, $degrees, 0);
			if (!$this->saveImage($this->mimetype,$rotated_pic,$new_pic))
			{
				throw new Exception ('Problems with rotating.');
			}
		} catch (Exception $e)
		{
			die($e->getMessage());
		}
	}
		
	/**
	* Filtering
	*
	* this method aplied the specified filter
	*
	* @access private
	* @param string $new_pic path and image name of new image
	* @param string $filter filte to be applied
	*/
	private function applyfilters($new_pic,$filter)
	{
		try
		{
			imagefilter($this->image, $filter);
			if (!$this->saveImage($this->mimetype,$this->image,$new_pic))
			{
				throw new Exception ('Problems applying filters.');
			}
		}
		catch (Exception $e)
		{
			die($e->getMessage());
		}
	}
	
	/**
	* Gray scale filter (Black & White)
	*
	* This method creates a copy in black & white from original picture
	*
	* @access public
	*	@param string $new_pic path and image name of new picture
	*/
	public function grayFilter($new_pic)
	{
		$this->applyfilters($new_pic,IMG_FILTER_GRAYSCALE);
	}
	
	/**
	* Negative filter (like negative photographies)
	*
	* This method creates a copy in negative from original picture
	*
	* @access public
	* @param string $new_pic path and image name of new picture
	*/
	public function negativeFilter($new_pic)
	{
		$this->applyfilters($new_pic,IMG_FILTER_NEGATE);
	}
	
	/**
	* Create favicon
	*
	* This method creates a favicon.ico
	*
	* @access public
	* @param string $favicon path and image name of new picture
	*/
	public function createFavicon($favicon)
	{
		$path = $favicon . 'favicon.ico';
		$this->ConvertToPng($path,9);
		$this->resizeImage($path,16,16);
		unset($path);
	}
	/**
	* Convert
	*
	* This method converts an image to mimetype given
	*
	* @access public
	* @param string $newPic and image name of new picture
	* @param string $mimetype  mymetype image will be converted
	*/
	
	private function convert($newPic,$mimetype)
	{
		try
		{
			if (!$this->saveImage($mimetype,$this->image,$newPic))
			{
				throw new Exception ('Problems converting image.');
			}
		}
		catch (Exception $e)
		{
			die($e->getMessage());
		}
	}
	
	/**
	* ConvertToPng
	*
	* This method converts an image into png
	*
	* @access public
	* @param string $newPngPath path and image name of new picture
	*/
	public function ConvertToPng($newPngPath)
	{
		$this->convert($newPngPath,'image/png');
	}
	
	/**
	* ConvertToJpg
	*
	* This method converts an image into jpg
	*
	* @access public
	* @param string $newPngPath path and image name of new picture
	*/
	public function ConvertToJpg($newJpgPath)
	{
		$this->convert($newPngPath,'image/jpeg');
	}
	
	/**
	* ConvertToGif
	*
	* This method converts an image into gif
	*
	* @access public
	* @param string $newPngPath path and image name of new picture
	*/
	public function ConvertToGif($newGifPath)
	{
		$this->convert($newGifPath,'image/gif');
	}
}

$image = new image('filtros/sepia.jpg');
$image->rotateImage('filtros/sepia90.jpg',90);