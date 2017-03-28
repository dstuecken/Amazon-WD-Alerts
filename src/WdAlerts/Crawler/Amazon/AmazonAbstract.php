<?php
namespace dstuecken\WdAlerts\Crawler\Amazon;

use dstuecken\WdAlerts\Crawler\ParseResult;
use dstuecken\WdAlerts\Exceptions\CrawlerException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class CrawlerDefinitions
 *
 * @copyright Dennis Stücken <dstuecken@me.com>
 *
 * @licence MIT
 * @package dstuecken\WdAlerts
 */
abstract class AmazonAbstract
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $articleId;

    /**
     * @return string
     */
    public function getCrawlUrl()
    {
        return isset($this->config['definitions']['offersUrl']) && $this->config['definitions']['offersUrl'] ? $this->config['definitions']['offersUrl'] : 'https://www.amazon.de/gp/offer-listing/';
    }

    /**
     * @return string
     */
    public function getArticleId()
    {
        return $this->articleId;
    }
    
    /**
     * @return string
     */
    public function getLocale() {
        return $this->config['definitions']['locale'] ?: 'de_DE';
    }
    

    /**
     * @return string
     */
    public function getLinkAddition()
    {
        return '?tag=awdal-21';
    }

    /**
     * @param Crawler $crawler
     *
     * @return ParseResult
     */
    public function parse(Crawler $crawler)
    {
        /**
         * Validate configuration before start crawling
         */
        $this->validateConfig();;
        $result = new ParseResult();

        try {
            // Some XPath text extractions
            $result->setTitle(trim($crawler->filterXPath($this->config['definitions']['xPathTitle'])->text()));
        } catch (\InvalidArgumentException $e) {
            throw new CrawlerException('Error parsing Amazons website. Please check your article id or engine. (Engine: ' . __NAMESPACE__ . '; Crawler error: ' . $e->getMessage() . ')');
        }

        if (isset($this->config['definitions']['xPathPrice']) && $this->config['definitions']['xPathPrice']) {
            $result->setPrice(trim($crawler->filterXPath($this->config['definitions']['xPathPrice'])->text()));
        }

        // Is a warehouse deal available?
        $found = $crawler->filter($this->config['definitions']['searchFor']);

        if ($found->count() > 0) {
            $result->setFound(true);

            try {
                if (isset($this->config['definitions']['xPathCondition']) && $this->config['definitions']['xPathCondition']) {
                    $result->setCondition(preg_replace("/[\\s]+/", " ", trim($crawler->filterXPath($this->config['definitions']['xPathCondition'])->text())));
                }
            } catch (\Exception $e) {
                ;
            }

            try {
                if (isset($this->config['definitions']['xPathDescription']) && $this->config['definitions']['xPathDescription']) {
                    $result->setDescription(preg_replace("/[\\s]+/", " ", trim($crawler->filterXPath($this->config['definitions']['xPathDescription'])->text())));
                }
            } catch (\Exception $e) {
                ;
            }
        } else {
            $result->setFound(false);
        }

        return $result;
    }

    /**
     * Validate amazon configuration
     *
     * @throws \ErrorException
     */
    private function validateConfig()
    {
        if (!isset($this->config['definitions'])) {
            throw new \ErrorException('Amazon config error: definitions section does not exist in config.yml.');
        }

        if (!isset($this->config['definitions']['xPathTitle']) && $this->config['definitions']['xPathTitle']) {
            throw new \ErrorException('Amazon config error: xPathTitle definition not found in config.yml.');
        }
    }
}