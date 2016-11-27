<?php
namespace dstuecken\WdAlerts\Crawler\Amazon;

use dstuecken\WdAlerts\Crawler\EngineContract;
use Symfony\Component\Yaml\Yaml;

/**
 * Class CrawlerDefinitions
 *
 * @copyright Dennis Stücken <dstuecken@me.com>
 *
 * @licence MIT
 * @package dstuecken\WdAlerts
 */
class Engine extends AmazonAbstract implements EngineContract
{
    /**
     * Engine constructor.
     */
    public function __construct($amazonArticleId)
    {
        $this->config = Yaml::parse(file_get_contents(__DIR__ . '/config.yml'));
        $this->articleId = $amazonArticleId;
    }
}