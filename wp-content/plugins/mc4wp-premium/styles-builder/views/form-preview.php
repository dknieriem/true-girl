<?php
defined( 'ABSPATH' ) or exit;

// prevent caching, declare constants
if( ! defined( 'DONOTCACHEPAGE' )) {
	define( 'DONOTCACHEPAGE', true );
}

if( ! defined( 'DONOTMINIFY' )) {
	define( 'DONOTMINIFY', true );
}

if( ! defined( 'DONOTCDN' )) {
	define( 'DONOTCDN', true );
}

// render simple page with form in it.
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
	<link type="text/css" rel="stylesheet" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
	<?php wp_head(); ?>
    
    <style type="text/css">
        html, 
        body{ 
            min-height: 100% !important; 
            height: auto !important; 
        }

        body{ 
            background:white;
            width: 100%;
	        max-width: 100%;
         }

        /* hide other elements */
        body > *:not(#form-preview),
        #blackbox-web-debug, 
        #wpadminbar{ 
            display:none !important; 
        }

        #form-preview {
	        display: block !important;
	        width: 100%;
	        height: 100%;
	        padding: 20px;
        }
    </style>

	<!-- Container for custom CSS created by the Styles Builder interface -->
    <style type="text/css" id="custom-css"></style>

    <!--[if IE]>
        <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>
<body <?php body_class(); ?>>
	<div id="form-preview">
		<?php mc4wp_show_form( absint( $_GET['form_id'] ) ); ?>
	</div>
	<?php wp_footer(); ?>
</body>
</html>
