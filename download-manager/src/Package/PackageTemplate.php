<?php


namespace WPDM\Package;


use WPDM\__\FileSystem;
use WPDM\__\Template;

class PackageTemplate
{


    function __construct()
    {

    }

    function getTemplateContent($template, $packageID, $type = 'link')
    {

        $default['link'] =  'link-template-default.php';
        $default['page'] =  'page-template-default.php';
	    $template_error = "";

	    $_template = $template;

        if ($template == '')
            $template = $default[$type];

        //Custom templates ( xml file )
        $template_content = WPDM()->packageTemplate->get($template, $type, true);

        if($template_content)
            $template = $template_content;
        else
            if(!strpos(strip_tags($template), "]")){

                //PHP templates ( php file )
                $template = wpdm_basename($template);
                $template = str_replace(".php", "", $template).".php";

                $template_file = Template::locate("{$type}-templates/{$template}", __DIR__.'/views');

                if(!$template_file)
                    $template_file = Template::locate("{$type}-templates/{$type}-template-{$template}", __DIR__.'/views');

                if(!$template_file) {
	                $template_file = Template::locate( "{$type}-templates/{$default[$type]}", __DIR__ . '/views' );
					$template_error = "<!-- Template '{$_template}' not found, using default {$type} template -->";
                }

                if($template_file !== ''){
                    ob_start();
                    global $wp_filter;
                    $all_tc = $wp_filter['the_content'];
                    unset($wp_filter['the_content']);
                    remove_filter("the_content", "wpdm_downloadable");
                    $ID = $packageID;
                    include $template_file;
                    $template = ob_get_clean();
                    $wp_filter['the_content'] = $all_tc;
                    if(!preg_match("/\[([^\]]+)\]/", $template,$found)){
                        return $template;
                    }
                } else
					$template = $_template;

            }
            return $template.$template_error;
    }

    public function parseTemplate($template, $packageID, $type = 'link')
    {
        $template = $this->getTemplateContent($template, $packageID, $type);
        preg_match_all("/\[([^\]]+)\]/", $template, $matched);
        return $matched[1];
    }

    function getValue($package, $var)
    {

    }

}
