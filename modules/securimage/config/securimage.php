<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'default' => array(
		//'ttf_file' => MODPATH.'securimage/vendor/securimage/Quiff.ttf',
		//'captcha_type' => Securimage::SI_CAPTCHA_MATHEMATIC,										// show a simple math problem instead of text
		//'case_sensitive' => true,																	// true to use case sensitve codes - not recommended
		//'image_height' => 90,																		// height in pixels of the image
		//'image_width' => 90 * M_E,																// a good formula for image size based on the height
		//'perturbation' => .75,																	// 1.0 = high distortion, higher numbers = more distortion
		//'image_bg_color' => new Securimage_Color("#0099CC"),										// image background color
		//'text_color' => new Securimage_Color("#EAEAEA"),											// captcha text color
		//'num_lines' => 8,																			// how many lines to draw over the image
		//'line_color' => new Securimage_Color("#0000CC"),											// color of lines over the image
		//'image_type' => Securimage::SI_IMAGE_JPEG,												// render as a jpeg image
		//'signature_color' => new Securimage_Color(rand(0, 64), rand(64, 128), rand(128, 255)),	// random signature color
	)
);
