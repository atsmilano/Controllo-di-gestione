<?php
/**
 * text layer
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>, Alessandro Stucchi <wolfgan@blueocarina.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */

/**
 * text layer
 *
 * @package FormsFramework
 * @subpackage utils
 * @author Samuele Diella <samuele.diella@gmail.com>, Alessandro Stucchi <wolfgan@blueocarina.com>
 * @copyright Copyright (c) 2004-2017, Samuele Diella
 * @license https://opensource.org/licenses/LGPL-3.0
 * @link http://www.formsphpframework.com
 */
class ffText extends ffImage
{
	var $parent							= NULL;
	var $font_path                      = NULL;

	var $src_res_dim_x					= NULL;
	var $src_res_dim_y					= NULL;
	var $src_res_print_x_start			= 1;
	var $src_res_print_y_start			= 1;
	var $src_res_print_x_end			= NULL;
	var $src_res_print_y_end			= NULL;
	
	
	var $src_res_font_caption			= "";
	var $src_res_font_color_hex			= NULL;
	var $src_res_font_bgcolor_hex		= NULL;
	var $src_res_font_size				= 0;
	var $src_res_font_type				= "times.ttf";
	var $src_res_font_align				= "none";
	var $src_res_font_method			= "none";

	function ffText($new_res_dim_real_x = NULL, $new_res_dim_real_y = NULL, $src_res = NULL)
	{
		//$this->getDefaults();

		$this->ffImage($new_res_dim_real_x, $new_res_dim_real_y, $src_res);
	}
		
	function load_image()
	{
		$src_res = @imagecreatetruecolor($this->src_res_dim_x, $this->src_res_dim_y);

		if (strlen($this->src_res_font_caption))
		{
/*				if (strlen($this->src_res_font_color_hex) == 6)*/
			$color_font = imagecolorallocate($src_res
												, hexdec(substr($this->src_res_font_color_hex, 0, 2))
												, hexdec(substr($this->src_res_font_color_hex, 2, 2))
												, hexdec(substr($this->src_res_font_color_hex, 4, 2))
												);
			$color_bg = imagecolorallocate($src_res
												, hexdec(substr($this->src_res_font_bgcolor_hex, 0, 2))
												, hexdec(substr($this->src_res_font_bgcolor_hex, 2, 2))
												, hexdec(substr($this->src_res_font_bgcolor_hex, 4, 2))
												);
				
			//$tmp_transparent = imagecolortransparent($src_res, $color_bg);
			
			imagefill($src_res
							, 0
							, 0
							, $color_bg);
				
			$src_res_font_size 			= $this->src_res_font_size;
			$src_res_print_x_start		= $this->src_res_print_x_start;
			$src_res_print_y_start		= $this->src_res_print_y_start;
			$src_res_print_x_end		= $this->src_res_print_x_end === NULL
											? $this->new_res_dim_real_x
											: $this->src_res_print_x_end;
			$src_res_print_y_end		= $this->src_res_print_y_end === NULL
											? $this->new_res_dim_real_y
											: $this->src_res_print_y_end;
											
			$real_w = $src_res_print_x_end - $src_res_print_x_start;
			$real_h = $src_res_print_y_end - $src_res_print_y_start;

			do 
			{
				$bbox = imagettfbbox($src_res_font_size, 0, $this->get_template_dir($this->src_res_font_type), $this->src_res_font_caption);

				$text_width_top = abs($bbox[6]) + abs($bbox[4]);
				$text_width_bottom = abs($bbox[0]) + abs($bbox[2]);
				$text_width = $text_width_top > $text_width_bottom 
								? $text_width_top 
								: $text_width_bottom;

				$text_height_left = abs($bbox[7]) + abs($bbox[1]);
				$text_height_right = abs($bbox[5]) + abs($bbox[3]);
				$text_height = $text_height_left > $text_height_right 
								? $text_height_left 
								: $text_height_right;
								
				//ffErrorHandler::raise("ASD", E_USER_ERROR, $this, get_defined_vars());

				switch ($this->src_res_font_align)
				{
					case "top-left":
						$src_res_font_x_start = $src_res_print_x_start;
						$src_res_font_y_start = $src_res_print_y_start + abs($bbox[7]);
						break;
					case "top-middle":
						$src_res_font_x_start = ceil($src_res_print_x_start + ($real_w - $text_width) / 2);
						$src_res_font_y_start = $src_res_print_y_start + abs($bbox[7]);
						break;
					case "top-right":
						$src_res_font_x_start = $src_res_print_x_start + ($real_w - $text_width);
						$src_res_font_y_start = $src_res_print_y_start + abs($bbox[7]);
						break;
					case "bottom-left":
						$src_res_font_x_start = $src_res_print_x_start;
						$src_res_font_y_start = $src_res_print_y_start + ($real_h - $text_height + abs($bbox[7]));
						break;
					case "bottom-middle":
						$src_res_font_x_start = ceil($src_res_print_x_start + ($real_w - $text_width) / 2);
						$src_res_font_y_start = $src_res_print_y_start + ($real_h - $text_height + abs($bbox[7]));
						break;
					case "bottom-right":
						$src_res_font_x_start = $src_res_print_x_start + ($real_w - $text_width);
						$src_res_font_y_start = $src_res_print_y_start + ($real_h - $text_height + abs($bbox[7]));
						break;
					case "middle-left":
						$src_res_font_x_start = $src_res_print_x_start;
						$src_res_font_y_start = ceil($src_res_print_y_start + ($real_h - $text_height + abs($bbox[7]) + abs($bbox[1])) / 2);
						break;
					case "middle-right":
						$src_res_font_x_start = $src_res_print_x_start + ($real_w - $text_width);
						$src_res_font_y_start = ceil($src_res_print_y_start + ($real_h - $text_height + abs($bbox[7]) + abs($bbox[1])) / 2);
						break;
					case "center":
					default:
						$src_res_font_x_start = ceil($src_res_print_x_start + ($real_w - $text_width) / 2);
						$src_res_font_y_start = ceil($src_res_print_y_start + ($real_h - $text_height + abs($bbox[7]) + abs($bbox[1])) / 2);
				}
			}
			while (($this->src_res_font_method != "none") && (($text_width >= $src_res_print_x_end) && ($src_res_font_size = $src_res_font_size - 1)));

			imagettftext($src_res
						  , $src_res_font_size
						  , 0
						  , $src_res_font_x_start 
						  , $src_res_font_y_start
						  , $color_font
						  , $this->get_template_dir($this->src_res_font_type)
						  , $this->src_res_font_caption
						);
		}
		return $src_res;
	}			
		
	function get_theme()
	{
		if ($this->theme !== NULL)
			return $this->theme;
		else if ($this->theme === NULL && $this->parent !== NULL)
			return $this->parent[0]->theme;
		else
			return "default";
	}

    function get_template_dir($file)
    {
        if($this->font_path && is_file($this->font_path . "/" . $file)) {
            return $this->font_path . "/" . $file;
        } elseif(is_file(__DIR__ . "/fonts/" . $file)) {
            return __DIR__ . "/fonts/" . $file;
        } else {
            return false;
        }
    }
}
