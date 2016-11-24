<?php
namespace dstuecken\WdAlerts\Crawler\Amazon;

use dstuecken\WdAlerts\Crawler\ParseResult;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Yaml\Yaml;

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
    private $config;

    /**
     * @var string
     */
    private $articleId;

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
    public function getLinkAddition()
    {
        return '?tag=d04c7-21';
    }

    /**
     * @param Crawler $crawler
     *
     * @return ParseResult
     */
    public function parse(Crawler $crawler)
    {
        $result = new ParseResult();

        // Some XPath text extractions
        $result->setTitle(trim($crawler->filterXPath($this->config['definitions']['xPathTitle'])->text()));
        $result->setPrice(trim($crawler->filterXPath($this->config['definitions']['xPathPrice'])->text()));

        // Is a warehouse deal available?
        $found = $crawler->filter($this->config['definitions']['searchFor']);

        if ($found->count() > 0)
        {
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
        }
        else
        {
            $result->setFound(false);
        }

        return $result;
    }

    /**
     * Engine constructor.
     */
    public function __construct($amazonArticleId)
    {
        $this->config = Yaml::parse(file_get_contents(__DIR__ . '/config.yml'));
        $this->articleId = $amazonArticleId;

        if (!isset($this->config['definitions'])) {
            throw new \ErrorException('Amazon config error: definitions section does not exist in config.yml.');
        }
    }
}