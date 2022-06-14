<?php
global $tabImgs;
global $options;
global $nameSpritewithpng;
global $nameSpritewithoutpng;
$options = [];
$options = getopt('ris::', ["recursive", "output-image::", "output-style::"]);

function pathdirectdoss(string $file) {
    $fichier =  __DIR__ . DIRECTORY_SEPARATOR . "$file";
    return $fichier;
};

function nameCss() {
    global $options;
    $nameCss = "style.css";
    if (array_key_exists('s', $options) || array_key_exists('output-style', $options)) {
        $nameCss = readline("Ecrivez votre nom de CSS ");
        if ($nameCss == "") {
            $nameCss = "style.css";
        } 
    }
    return $nameCss;
};

function nameSprite() {
    global $options;
    $nameSprite = "sprite.png";
    if (array_key_exists('i', $options) || array_key_exists('output-image', $options)) {
        $nameSprite = readline("Ecrivez votre nom de sprite ");
        if ($nameSprite == "") {
            $nameSprite = "sprite.png";
        }  
    }
    pathdirectdoss("$nameSprite");
    $infoSprite = pathinfo($nameSprite);
    return $infoSprite;
};

function infoSprite() {
    $names = nameSprite();
    $allname = [];
    foreach ($names as $key => $name) {
        array_push($allname, $names["basename"], $names["filename"]);
    }
    return $allname;
}   
    
function drawArray($directory) {
    global $options;
    static $tableaux = [];
    if (is_dir($directory)) {
        if ($dossier = opendir($directory)) {
            while (($file = readdir($dossier)) !== false) {
                $typeOfFile = $directory . DIRECTORY_SEPARATOR . $file;
                if ($file != "." && $file != ".." && $file != ".DS_Store") {
                    if (array_key_exists('r', $options) || array_key_exists('recursive', $options)) {
                        if (filetype($typeOfFile) == "dir") {
                            drawArray($typeOfFile, $tableaux);    
                        }
                    }
                    if (filetype($typeOfFile) == "file") {
                        array_push($tableaux, $typeOfFile);      
                    } 
                }                            
            } 
            closedir($dossier); 
        }
    }
    return $tableaux; 
}; 
    
function incrementImg() {
    global $tabImgs;
    global $heightTotal;
    global $widthTotal;
    $heightTotal = [];
    $widthTotal = [];
    $totalHeight = 0;
    $totalWidth = 0;
    $tabImgs = drawArray('imgs'); 
    foreach ($tabImgs as $tabImg) {
        $sizeInfo = getimagesize($tabImg);
        $totalHeight += $sizeInfo[1];
        $totalWidth += $sizeInfo[0]; 
        array_push($heightTotal, $sizeInfo[1]) ;
        array_push($widthTotal, $sizeInfo[0]) ;
    }
    $img = imagecreatetruecolor($totalWidth, $totalHeight);
    imagesavealpha($img, true);
    $varImg = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $varImg);
    $allimgCreateFromPngs = [];
    foreach ($tabImgs as $tabImg) {
        array_push($allimgCreateFromPngs, imagecreatefrompng($tabImg));
    }       
    $height = 0;
    array_filter($allimgCreateFromPngs);
    foreach ($allimgCreateFromPngs as $key => $allimgCreateFromPng) { 
        if (!is_bool($allimgCreateFromPng)) {  
            imagecopy($img, $allimgCreateFromPng,  0, $height, 0, 0, imagesx($allimgCreateFromPng), imagesy($allimgCreateFromPng));
            $height += imagesy($allimgCreateFromPng); 
        }
    }
    return $img;
};
    
function laPageCss() {       
    global $nameSpritewithoutpng;      
    global $tabImgs;
    global $heightTotal;
    global $widthTotal;
    $cssName = nameCss();
    $leCss = fopen("$cssName", "w");
    fwrite($leCss,
        ".$nameSpritewithoutpng {background-image: url($nameSpritewithoutpng); background-repeat: no-repeat; display: block;};\n"
    );
    $height = 0;
    foreach ($tabImgs as $key => $tabImg) {
        $bambou = imagecreatefrompng($tabImg);
        $info = pathinfo($tabImg);
        $namewow = $info["filename"];
        fwrite($leCss, ".$nameSpritewithoutpng-$namewow {background-image: url($nameSpritewithoutpng); width: $widthTotal[$key]; height: $heightTotal[$key]; background-position: 0"."px"." $height"."px".";}\n");
        $height += imagesy($bambou); 
    }
};
    
function maine() {
    global $nameSpritewithoutpng;
    list($nameSpritewithpng, $nameSpritewithoutpng) = infoSprite();
    $fin = incrementImg();
    laPageCss();
    imagepng($fin, "$nameSpritewithpng");
    imagedestroy($fin);
}
maine();