<?php
/*
Plugin Name: Google Equation for WordPress
Plugin URI: http://blog.tigerlihao.cn/geq4wp
Description: Using Google chart API for LaTeX to add mathematical equations as images.
Version: 0.1.1
Author: Li Hao
Author URI: http://tigerlihao.cn
*/

/*  
    0.1.1:
    Li Hao, URI: http://tigerlihao.cn
    -   Change the file name.

    0.1.0:
    Li Hao, URI: http://tigerlihao.cn
    -   Li Hao's original version.
*/

class geq {
	var $server = "http://www.google.com/chart?cht=tx&chf=bg,s,FFFFFF00&chco=000000&chl=";		
	var $img_format = "png";
	
	// parsing the text to display tex by putting tex-images-tags into the code created by createTex
    function parseTex ($toParse) {
        // tag specification (which tags are to be replaced)
        $regex = '#\[eq\](.*?)\[/eq\]#si';
        
		return preg_replace_callback($regex, array(&$this, 'createTex'), $toParse);
    }
    
    // reading the tex-expression and create an image and a image-tag representing that expression
    function createTex($toTex) {
    	$equation_text = $toTex[1];
		$equation_hash = md5($equation_text);
		$equation_filename = 'eq_'.$equation_hash.'.'.$this->img_format;

		$cache_path = ABSPATH . '/wp-content/uploads/';
		$cache_equation_path = $cache_path . $equation_filename;
		$cache_url = get_bloginfo('wpurl') . '/wp-content/uploads/';
		$cache_equation_url = $cache_url . $equation_filename;
    
        if ( !is_file($cache_equation_path)) {
     		if (!class_exists('Snoopy')) require_once (ABSPATH.'wp-includes/class-snoopy.php');
     		
            $snoopy = new Snoopy;
			
			$snoopy->fetch( $this->server.rawurlencode(html_entity_decode($equation_text)));
            // this will copy the created tex-image to your cache-folder
            if(strlen($snoopy->results)){
				$cache_file = fopen($cache_equation_path, 'w');
				fputs($cache_file, $snoopy->results);
				fclose($cache_file);
			}
		}
        
        // returning the image-tag, referring to the image in your cache folder 
		return "<img src=\"$cache_equation_url\" align=\"absmiddle\" class=\"tex\" alt=\"".($equation_text)."\" />";
    }  
}

$geq_object = new geq;
// Equations in content an excerpt should be parsed.
add_filter('the_content', array($geq_object, 'parseTex'), 1);
add_filter('the_excerpt', array($geq_object, 'parseTex'), 1);

?>