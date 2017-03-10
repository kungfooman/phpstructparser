<?php


$code = file_get_contents("opengl.h");

preg_match_all("/(GL_[A-Z0-9_]{3,})/", $code, $output_array);
echo "<pre>\n";


$defines = array_unique($output_array[0]);
//var_dump($defines);

foreach ($defines as $define) {
	echo "{\"$define\", $define},\n";
	
	
}

echo "</pre>\n";