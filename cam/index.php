<?php

function listdir_by_date($path){
    $files = glob($path.'/*',GLOB_ONLYDIR);
    
    foreach ($files as $f){
        $tmp[basename($f)] = filectime($f);
    }
    
    arsort($tmp);
    $files = array_keys($tmp);
    
    return $files;
}

function listdir_desc($path){
    $files = array();
    
    if ($handle = opendir($path)) {

        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && $file != ".DS_Store") {
                $files[$file] = $file;
            }
        }
    }
    closedir($handle);
    
    krsort($files);
    
    return $files;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
    <title>Arthur's Peeps</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/_css/reset.css" type="text/css" media="screen, projection and (min-device-width: 481px)">
    <link rel="stylesheet" type="text/css" href="../_css/main.css" media="screen, projection and (min-device-width: 481px)">
    <link rel="stylesheet" type="text/css" href="../_css/mobile.css" media="only screen and (max-device-width: 480px)">
    <script language="javascript" type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.0/jquery.min.js"></script>

	 <!-- Lightbox Stuff -->
    <link rel="stylesheet" type="text/css" href="/_tools/jqlightbox/css/jquery.lightbox-0.5.css" media="screen">
    <script language="javascript" type="text/javascript" src="/_tools/jqlightbox/js/jquery.lightbox-0.5.min.js"></script>
    <script type="text/javascript">
		if (window.outerWidth && window.outerWidth > 485) {
      	$(function() {
         	$('.lbox a').lightBox();
        	});
    	}
    </script>
    <!-- END Lightbox Stuff -->

    <script type="text/javascript" src="/_js/analytics.js"></script>
</head>

<body>
    <div id="page">
        <div id="camheader">
            <h1>Smile! You're on <a href="/arthur">Arthur</a> Cam at the <a href="http://itp.nyu.edu/show">ITP Winter Show '11</a>! :)</h1>
        </div>

        <div id="galContent">
            <div id="gallery">
                <ul class="thumbs">
                    <?php
                    		$imgdir = '_img';
                        $list = listdir_desc($imgdir);

								foreach( $list as $fname ){
                        	$imgpath = $imgdir.'/'.$fname; 
                        	//20111026-120845-cam.jpg
                           //0123456789012345678
                           $imgyr = substr($fname,0,4);
                           $imgmo = substr($fname,4,2);
                           $imgday = substr($fname,6,2);
                           $imghr = substr($fname,9,2)+1;
                           $imgmin = substr($fname,11,2);
                           $imgsec = substr($fname,13,2);
                           $imgapm = 'am';
                                                
                           if($imghr > 12){
                           	$imghr = $imghr-12;
                              $imgapm = 'pm';
                           }
                                                
                           $imgtitle = $imghr.":".$imgmin.":".$imgsec.$imgapm." on ".$imgmo.".".$imgday.".".$imgyr;
                        
                           echo "\t\t\t\t\t\t";
                           echo '<li class="images"><div class="lbox"><a href="'.$imgpath.'" title="'.$imgtitle.'"><img src="'.$imgpath.'"/></a></div><div class="perma">'.$imgtitle.'<br/><a href="'.$imgpath.'">permalink &gt;</a></divn></li>';
                           echo "\n";
                        }
                    ?>
                </ul><!-- close <ul class="thumbs"> -->
            </div><!-- close <div id="gallery"> -->

            <div id="footer" style="clear:both">
                <p>&copy; 2011.12 - patent pending.<br>
                <a href="/arthur">Arthur</a> is <a href="/blog">Jannae Jacks's</a> final project for <a href="https://itp.nyu.edu/physcomp/">Introduction to Physical Computing</a> in <a href="http://itp.nyu.edu/">New York University's Interactive Telecommunications Program (ITP)</a>.<br>
                <br>
                <a href="mailto:jannae@gmail.com">jannae@gmail.com</a></p>
            </div>
        </div><!-- close <div id="content"> -->
    </div><!-- close <div id="page">-->
</body>
</html>
