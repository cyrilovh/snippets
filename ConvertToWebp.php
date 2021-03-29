<?php
	// display error msg 
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	// function for convert files (ex: landscape.png -> landscape.png.webp)
	function convertToWebp($path){
		if(!file_exists($path.".webp")){ // we check if file already exist (for don't create erase and recreate)
			$i = pathinfo($path);
			switch($i["extension"]) {
		    	case "jpg":
			case "jpeg":
		        	$im = imagecreatefromjpeg($path);
		        	break;
		    	case "png":
		        	$im = imagecreatefrompng($path);
		        	break;
		    	case "bmp":
		        	$im = imagecreatefrombmp($path);
		        	break;
			}
			if(isset($im)){
				imagepalettetotruecolor($im);
				imagealphablending($im, true);
				imagesavealpha($im, true);
				imagewebp($im, $path.'.webp');
				imagedestroy($im);
				echo "<p>$path converted.<p>";
			}
		}else{ // if image already exist
			echo "<p>$path.webp already exist.<p>";
		}
	}
	$files = scandir("./"); // folder to scan
	foreach($files as $key => $value){ // check element per element
		if(is_file($value)==true){ // we check if it's a file and not a folder
			convertToWebp($value);
		}
	}
?>
