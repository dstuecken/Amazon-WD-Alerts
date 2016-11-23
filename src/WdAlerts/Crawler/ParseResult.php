<?php
namespace dstuecken\WdAlerts\Crawler;

/**
 * Class ParseResult
 *
 * @copyright Dennis Stücken <dstuecken@me.com>
 * @licence MIT
 *
 * @package dstuecken\WdAlerts\Crawler
 */
class ParseResult
{
    /**
     * @var bool
     */
    private $found = false;

    /**
     * @var string
     */
    private $title = '';

    /**
     * @var string
     */
    private $price = '';

    /**
     * @var string
     */
    private $condition = '';

    /**
     * @var string
     */
    private $description = '';

    /**
     * @return boolean
     */
    public function isFound()
    {
        return $this->found;
    }

    /**
     * @param boolean $found
     *
     * @return $this
     */
    public function setFound($found)
    {
        $this->found = $found;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param string $price
     *
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param string $condition
     *
     * @return $this
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }


}