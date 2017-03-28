<?php
namespace dstuecken\WdAlerts\Crawler;

use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * Interface EngineContract
 *
 * @copyright Dennis Stücken <dstuecken@me.com>
 * @licence MIT
 *
 * @package dstuecken\WdAlerts\Crawler
 */
interface EngineContract
{
    /**
     * Return URL that is used to crawl for new items
     *
     * @return mixed
     */
    public function getCrawlUrl();

    /**
     * @return string
     */
    public function getArticleId();

    /**
     * @return string
     */
    public function getLinkAddition();

    /**
     * @return string
     */
    public function getLocale();

    /**
     * @param DomCrawler $crawler
     * @return ParseResult
     */
    public function parse(DomCrawler $crawler);
}