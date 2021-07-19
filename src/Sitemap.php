<?php

namespace Armincms\Sitemap;

use Laravel\Nova\Nova; 

class Sitemap
{   
    /**
     * Push new sitemap into site mapindex.
     * 
     * @param  string $sitemap  
     * @return $this        
     */
    public static function push($sitemap)
    {
        return (new static)->add($sitemap);
    } 

    /**
     * Push new sitemap into site mapindex.
     *  
     * @param  string $sitemap  
     * @return $this        
     */
    public function add($sitemap)
    {
        return $this->buildSitemapIndex($this->loadSitemapIndex()->push($sitemap)->unique()->all());
    } 

    /**
     * Get the sitemap list.
     * 
     * @return \Illuminate\Support\Collection
     */
    public function loadSitemapIndex()
    {
        $this->ensureSitemapExists();
        $xml = simplexml_load_file($this->path()); 

        return collect(json_decode(json_encode($xml), true))->flatten(); 
    } 

    /**
     * Ensure that the `sitemapindex.xml` file exists.
     * 
     * @return $this
     */
    public function ensureSitemapExists()
    {
        file_exists($this->path()) || $this->buildSitemapIndex([]); 

        return $this;
    } 

    /**
     * Create `sitemapindex.xml` for the given maps.
     * 
     * @return $this
     */
    public function buildSitemapIndex($maps = [])
    { 
        $xml = collect($maps)->map(function($sitemap) {
            return "<sitemap><loc>{$sitemap}</loc></sitemap>";
        })->implode(''); 

        file_put_contents($this->path(), '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$xml.'</sitemapindex>');

        return $this;
    }

    /**
     * Get path of the imap file.
     * 
     * @return string
     */
    public function path()
    {
        return public_path('sitemapindex.xml');
    }
}
