<?php

/**
 * Author: Showket Ahmed
 * Website: https://cloudbillmaster.com
 * Email: dev@cloudbillmaster.com
 */

/**
 * Barcode Class
 */
namespace CBM\Barcode;

use GdImage;

	class Barcode
	{
		// Input for Barcode
		private String $input;

		// Additional Input
		public Array $text = [];

		// Barcode Type
		public String $type;

		// Backgroyund Color
		public Array $bgcolor = [255,255,255];

		// Text Color
		public Array $txcolor = [0,0,0];

		// Image Width
		public Int $width = 300;

		// Image Height
		public Int $height = 120;

		// Barcode Binary Stream
		public String $stream;

		// GdImage
		public GdImage $image;

		public function __construct(string $input, string $type = '128B')
		{
			$this->input = trim($input);
			$this->type = "Type".strtoupper(trim($type));
		}

		// Generate Binary Stream
		public function generate():void
		{			
			$class = "\\CBM\\Barcode\\".$this->type;
			$type = (class_exists($class)) ? new $class : new Type128B;

			$data = $type->data();

			$bin = '0000000000';
			
			//add start code
			$bin .= $type::START_CODE_BINARY;
			
			//add text
			$position = 0;
			$checksum = $type::START_CODE_VALUE;

			$count = strlen($this->input);
			for ($i = 0; $i < $count; $i++) { 
				
				$position++;
				$bin .= $data[$this->input[$i]]['bin'] ?? '';
				
				$checksum = $checksum + ($position * ($data[$this->input[$i]]['value'] ?? 0));
			}

			// Checksum
			$checksum = $checksum % 103;
			foreach ($data as $val)
			{				
				if($val['value'] == $checksum){
					$checksum = $val['bin'];
					break;
				}
			}

			$bin .= $checksum;
			$bin .= $type::STOP_PATTERN;
			$bin .= '0000000000';

			$this->image = imagecreate($this->width, $this->height);
			
			//add bg color
			imagecolorallocate($this->image, $this->bgcolor[0], $this->bgcolor[1], $this->bgcolor[2]);
			
			$bg = imagecolorallocate($this->image, 255, 255, 255);
			$fill = imagecolorallocate($this->image, $this->txcolor[0], $this->txcolor[1], $this->txcolor[2]);

			$count = strlen($bin);
			$gap = $this->width / $count;

			for ($i = 0; $i < $count; $i++) { 				
				$x1 = $i * $gap;
				$x2 = $x1 + $gap;
				$color = ($bin[$i]) ? $fill : $bg;
				imagefilledrectangle($this->image, $x1, 10, $x2, ($this->height - 50), $color);
			}
			// Set Additional Texts
			$txt_height = 50;
			foreach($this->text as $text){
				imagestring($this->image, 5, 20, ($this->height - $txt_height), $text, $fill);
				$txt_height -= 15;
			}
			imagestring($this->image, 5, 20, ($this->height - $txt_height), $this->input, $fill);
			$this->stream = imagejpeg($this->image);
			// return $stream;
			var_dump($this->stream);
			die;

			imagedestroy($this->image);
		}
	// Destruct
	}
