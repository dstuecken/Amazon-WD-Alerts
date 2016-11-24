<?php
namespace dstuecken\WdAlerts;

/**
 * Class Config
 *
 * @copyright Dennis Stücken <dstuecken@me.com>
 * @licence MIT
 *
 * @package dstuecken\WdAlerts
 */
class Config
{
    /**
     * Array of multi dimensional config variables
     *
     * @var array
     */
    private $config;

    /**
     * Set config option
     *
     * @param string $section
     * @param string $key
     * @param string $val
     *
     * @return $this
     */
    public function override($section, $key, $val) {
        $this->config[$section][$key] = $val;

        return $this;
    }

    /**
     * Get config variable
     *
     * @param string $section
     * @param string $key
     *
     * @return string
     */
    public function get($section, $key)
    {
        if (isset($this->config[$section][$key])) {
            return $this->config[$section][$key];
        }

        return null;
    }

    /**
     * Check if config option is set
     *
     * @param $section
     * @param $key
     *
     * @return bool
     */
    public function has($section, $key) {
        return isset($this->config[$section][$key]);
    }

    /**
     * Get options key
     *
     * @param string $key
     *
     * @return string
     */
    public function getOption($key)
    {
        return $this->get('options', $key);
    }

    /**
     * Config constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }
}