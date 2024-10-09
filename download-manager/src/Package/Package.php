<?php


namespace WPDM\Package;


use WPDM\__\__;

class  Package extends PackageController
{
    public $ID;
    public $title;
    public $description;
    public $excerpt;
    public $files;
    /**
     * @var FileList
     */
    public $fileList;
    public $post_status;
    public $version;
    public $publish_date;
    public $publish_date_timestamp;
    public $update_date;
    public $update_date_timestamp;
    public $avail_date;
    public $expire_date;
    public $expire_date_time;
    public $link_label;
    public $download_count;
    public $view_count;
    public $access;
    public $author;
    public $quota;
    public $package_size;

    function __construct($ID = null)
    {
        parent::__construct($ID);

        $this->init($ID);

    }

    function init($ID)
    {
        if ((int)$ID > 0) {
            global $wpdb;
            $pack = get_post($ID);
            if ($pack && $pack->post_type === 'wpdmpro') {
                $this->ID = $pack->ID;
                $this->title = $pack->post_title;
                $this->description = wpautop($pack->post_content);
                $this->description = str_replace("[wpdm", "[__wpdm", $this->description);
                $this->description = do_shortcode($this->description);
                $this->excerpt = wpautop($pack->post_excerpt);
                $this->post_status = $pack->post_status;
                $this->publish_date_timestamp = strtotime($pack->post_date);
                $this->publish_date = wp_date(get_option('date_format'), $this->publish_date_timestamp);
                $this->update_date_timestamp = strtotime($pack->post_modified);
                $this->update_date = wp_date(get_option('date_format'), $this->update_date_timestamp);
                $this->author = $pack->post_author;
                $this->files = $this->getFiles($ID, true);
                $meta = $this->metaData($ID);
                $this->avail_date = __::valueof($meta, 'avail_date' );
                $this->expire_date_time = __::valueof($meta, 'expire_date_time' );
                $this->expire_date = __::valueof($meta, 'expire_date' );
                $this->download_count = wpdm_valueof($meta, 'download_count');
                $this->view_count = wpdm_valueof($meta, 'view_count');
                $this->package_size = wpdm_valueof($meta, 'package_size');
                $this->quota = wpdm_valueof($meta, 'quota');
                $template_type = is_singular('wpdmpro') ? 'page' : 'link';
                $this->link_label = wpdm_valueof($meta, 'link_label');
                $this->version = wpdm_valueof($meta, 'version');
            }
        }
        $this->fileList = new FileList();
        return $this;
    }
}
