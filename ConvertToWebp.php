<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	function convertToWebp($path){
		if(!file_exists($path.".webp")){
			$i = pathinfo($path);
			switch($i["extension"]) {
		    	case "jpg":
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
		}else{
			echo "<p>$path.webp already exist.<p>";
		}
	}
	$files = scandir("./");
	foreach($files as $key => $value) {
		if(is_file($value)==true){
			//echo  "<p>$value</p>";
			convertToWebp($value);
		}
	}
?>
