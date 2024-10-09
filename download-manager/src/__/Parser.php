<?php

namespace WPDM\__;

class Parser {
	static function process($template, $rule = "/\{\{([^\}]+)\}\}/")
	{
		$compiled = preg_replace_callback($rule, [new self, '_var'], $template);
		return $compiled;
	}

	static function _var($matched)
	{

		if(substr_count($matched[1], "acfx_user_meta_") && file_exists('get_field')){
			$meta_name = str_replace("acfx_user_meta_", "", $matched[1]);
			$meta_value = get_field($meta_name, 'user_'.get_current_user_id());
			return $meta_value;
		}
		if(substr_count($matched[1], "acf_user_meta_")){
			$meta_name = str_replace("acf_user_meta_", "", $matched[1]);
			$data = maybe_unserialize(get_user_meta(get_current_user_id(), 'wpdm_cregf', true));
			$value = wpdm_valueof($data, $meta_name);
			if(is_array($value)) $value = implode(", ", $value);
			return $value;
		}
		if(substr_count($matched[1], "user_meta_")){
			$meta_name = str_replace("user_meta_", "", $matched[1]);
			if(substr_count($meta_name, '/')){
				$meta_name = explode("/", $meta_name);
				$meta_value = get_user_meta(get_current_user_id(), $meta_name[0], true);
				array_shift($meta_name);
				$meta_value = wpdm_valueof($meta_value, implode("/", $meta_name));
				return $meta_value;
			}
			return get_user_meta(get_current_user_id(), $meta_name, true);
		}
		if(substr_count($matched[1], "SERVER_")){
			$meta_name = str_replace("SERVER_", "", $matched[1]);
			$meta_value = wpdm_valueof($_SERVER, $meta_name);
			return $meta_value;
		}
		if(substr_count($matched[1], "REQUEST_")){
			$meta_name = str_replace("REQUEST_", "", $matched[1]);
			$meta_value = wpdm_valueof($_REQUEST, $meta_name);
			if(is_array($meta_value)) $meta_value = implode(", ", $meta_value);
			return $meta_value;
		}
		return $matched[0];
	}
}