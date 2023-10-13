<?php 
$phpFileUploadErrors = array(
    0 => 'There is no error, the file uploaded with success',
    1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
    2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
    3 => 'The uploaded file was only partially uploaded',
    4 => 'No file was uploaded',
    6 => 'Missing a temporary folder',
    7 => 'Failed to write file to disk.',
    8 => 'A PHP extension stopped the file upload.',
);
if (($_FILES['file']['error']>=1)){
    exit($phpFileUploadErrors[$_FILES['file']['error']]);
}else{
    $file=$_FILES['file']['tmp_name'];
}
function check_spaces($text){
    $text_array = explode('.', $text);
    $array_check = count($text_array)-1;
    //print_r($text_array);
    // if (strpos($text_array[$array_check],'"')===0 ||strpos($text_array[$array_check],'”')===0){
    //     $text_array[$array_check]='"';
    // }else{
    //     array_pop($text_array);
    // }
    $matches = array();
    if (preg_match('/(\d+)$/',$text_array[$array_check], $matches)){
        $text_array[$array_check] = preg_replace("/(".$matches[1].")/","",$text_array[$array_check]);
    };
    if(empty($text_array[$array_check])){
        array_pop($text_array);
    }
    
    //print_r($text_array);
    $build_text = '';
    foreach ($text_array as $i=>$t){
        $text_array[$i]=str_replace("  "," ",trim($t)).". ";
        $build_text.=''.$text_array[$i].'';
    }
    $build_text = preg_replace("/(?<=[\.])\s*(?=[\]\,])/","",$build_text);
    $build_text = preg_replace("/(?<=[\.])\s*(?=[g\.])/","",$build_text);
    $build_text = preg_replace("/([\],\?,\;,\:])(?=[a-zA-Z])/","$1 ",$build_text);
    $build_text = preg_replace("/(\.)\s*(?=(\d{1,2}))/","$1",$build_text);
    $build_text = preg_replace("/\&lt\;/","less than",$build_text);
    $build_text = preg_replace("/\&gt\;/","greater than",$build_text);
    
    return $build_text;
}
function pptx_to_text($input_file){
    $zip_handle = new ZipArchive;
    $output_text = "";
    $result = $zip_handle->open($input_file);
   if($result===TRUE){;
       $slide_number = 1; //loop through slide files
       while(($xml_index = $zip_handle->locateName("ppt/notesSlides/notesSlide".$slide_number.".xml")) !== false){  
            $xml_datas = $zip_handle->getFromIndex($xml_index);
            $xml_handle = new DOMDocument();
            $xml_handle->loadXML($xml_datas, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
            $found_text = trim(strip_tags($xml_handle->saveXML()));
            //echo $found_text;
            $found_text = str_replace('. ”', '.”',check_spaces($found_text)."\n");
            $found_text =str_replace('. "', '."',$found_text);
            $found_text =str_replace('.".', '."',$found_text);
            $output_text .=$found_text;
            //$output_text .=substr($found_text,0,$len_of_slide_number)."\n";
            $slide_number++;
        }
        
    }
   $zip_handle->close();

  return $output_text;
}
// if ()
$output = pptx_to_text($file); 

$filename = explode (".ppt", $_FILES['file']['name']);

$filename = rtrim($filename[0]).".txt";
// $newfile = fopen($filename, "w") or die("Unable to open file!");
// fwrite($newfile, $output);
// fclose($newfile);

header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
header("Content-Type: application/force-download");
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header("Content-Type: text/plain");
echo $output;

ob_clean();
flush();
// unlink($filename);

exit();
?>