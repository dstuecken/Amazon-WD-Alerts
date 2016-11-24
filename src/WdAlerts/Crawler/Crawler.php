<?php
namespace dstuecken\WdAlerts\Crawler;

use dstuecken\Notify\NotificationCenter;
use dstuecken\WdAlerts\Config;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class Crawler
 *
 * @copyright Dennis Stücken <dstuecken@me.com>
 * @licence MIT
 *
 * @package dstuecken\WdAlerts\Crawler
 */
class Crawler
{
    /**
     * @var EngineContract
     */
    private $engine;

    /**
     * @var NotificationCenter
     */
    private $notification;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ConsoleOutput
     */
    private $output;

    /**
     * @var LoggerAwareInterface
     */
    private $logger;

    /**
     * Write console text if enabled
     *
     * @param $text
     * @return $this
     */
    private function consoleOutput($text)
    {
        if ($this->config->getOption('enableConsoleTextOutput')) {
            $this->output->writeln($text);
        }

        $this->logger->info($text);

        return $this;
    }

    /**
     * Speech output on mac, text output on other systems
     *
     * @param string $text
     */
    private function macOsSpeechOutput($text) {
        if ($this->config->getOption('enableMacOsSpeechOutput')) {
            shell_exec('say -v Alex "'. $text .'"');
        } else {
            $this->consoleOutput($text);
        }
    }

    /**
     * @param $link
     *
     * @return $this
     */
    private function openBrowser($link) {
        if ($this->config->getOption('startBrowserIfDealFound')) {
            if (strstr(php_uname('s'), 'Darwin')) {
                shell_exec('open ' . $link);
            } elseif (strstr(php_uname('s'), 'Windows')) {
                shell_exec('explorer ' . $link);
            }
        }

        return $this;
    }

    /**
     * Start crawling process
     */
    public function crawl()
    {
        $shellStart = $this->config->get('hooks', 'shellStart');
        if ($shellStart) {
            shell_exec($shellStart);
        }

        $this->macOsSpeechOutput('I\'m happy to start scanning for your deals!');

        // Amazon offer listing page, e.g. https://www.amazon.de/gp/offer-listing/B00ULWWFIC
        $crawlUrl = $this->engine->getCrawlUrl();

        // Instances
        $notificationCenter = $this->notification;

        try {
            $client = new Client();
            $jar = new CookieJar();
            $listingUrl = rtrim($crawlUrl, '/') . "/" . $this->engine->getArticleId();

            if (!$this->config->has('options', 'useRefLink') || $this->config->getOption('useRefLink')) {
                $listingUrl .= $this->engine->getLinkAddition();
            }

            while (1) {
                try {
                    $res = $client->request('GET', $listingUrl, [
                        RequestOptions::ALLOW_REDIRECTS => [
                            'max'             => 10,
                        ],
                        RequestOptions::COOKIES => $jar,
                        RequestOptions::CONNECT_TIMEOUT => 10,
                        // RequestOptions::DEBUG => true,
                        RequestOptions::HEADERS => [
                            'Accept-Encoding' => 'gzip, deflate, sdch, br',
                            'Cache-Control' => 'max-age=0',
                            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                            'User-Agent' => $this->config->get('options', 'userAgent'),
                            'Accept-language' => $this->config->get('options', 'acceptLanguage'),
                            'Connection' => 'keep-alive'
                        ],
                        RequestOptions::PROXY => $this->config->getOption('proxy'),
                        RequestOptions::VERIFY => true,
                    ]);

                    if ($data = $res->getBody()->getContents()) {
                        $crawler = new \Symfony\Component\DomCrawler\Crawler($data);

                        $result = $this->engine->parse($crawler);

                        if ($result->isFound()) {
                            // Warehouse deal found, prepare message:
                            $body = sprintf(
                                "Item \"%s\" is available for %s!\nCondition is: %s\nDescription: %s\n\n%s",
                                $result->getTitle(),
                                $result->getPrice(),
                                $result->getCondition(),
                                $result->getDescription(),
                                $listingUrl
                            );

                            // Mac OS notification
                            $notificationCenter->info($body);

                            // Show console message
                            $this->consoleOutput($body);

                            // Open web browser
                            $this->openBrowser($listingUrl);

                            $shellDeal = $this->config->get('hooks', 'shellDeal');
                            if ($shellDeal) {
                                shell_exec(str_replace([$result->getTitle(), $result->getCondition(), $result->getPrice()], ['%TITLE%', '%CONDITION%', '%PRICE%'], $shellDeal));
                            }

                            // Say it out loud
                            $this->macOsSpeechOutput('Yay, your deal ' . $result->getTitle() . ' is available for ' . $result->getPrice() . '');

                            if ($this->config->get('mail', 'enabled')) {
                                $transport = \Swift_SmtpTransport::newInstance(
                                    $this->config->get('mail', 'smtp'),
                                    $this->config->get('mail', 'port'),
                                    $this->config->get('mail', 'security')
                                )
                                    ->setUsername($this->config->get('mail', 'username'))
                                    ->setPassword($this->config->get('mail', 'password'));

                                $mailer = \Swift_Mailer::newInstance($transport);
                                $message = \Swift_Message::newInstance()
                                    ->setSubject($this->config->get('mail', 'subjectPrefix') . ': ' . $result->getTitle())
                                    ->setFrom($this->config->get('mail', 'from'))
                                    ->setTo($this->config->get('mail', 'to'));

                                $message->setBody($body);

                                $mailer->send($message);
                            }
                        } else {
                            // no warehouse deal
                            $this->consoleOutput("No deal available. Current best price is <info>" . $result->getPrice() . "</info>.");

                            $shellNoDeal = $this->config->get('hooks', 'shellNoDeal');
                            if ($shellNoDeal) {
                                shell_exec($shellNoDeal);
                            }
                        }
                    } else {
                        $this->consoleOutput("Error retrieving page (". $res->getStatusCode() ."): <error>" . error_get_last()['message'] . '</error>');
                    }

                    $this->consoleOutput(sprintf("Sleeping <comment>%s</comment> seconds..\n", $this->config->getOption('updateInterval')));

                    sleep($this->config->getOption('updateInterval'));

                } catch (\Exception $e) {
                    // Amazon thinks we are a bot..
                    if (isset($data) && (strstr($data, 'To discuss automated access to Amazon data please contact'))) {
                        $this->consoleOutput('Damn, Amazon rejected our request. Retrying in five seconds.');
                    }
                    else {
                        $this->consoleOutput($e->getMessage());
                        $notificationCenter->error($e->getMessage());
                    }

                    // Sleep one second if an error occurred
                    sleep(5);
                }
            }
        } catch (\Exception $e) {
            $this->consoleOutput($e->getMessage());
            $notificationCenter->error($e->getMessage());
        }

    }

    /**
     * Crawler constructor.
     *
     * @param EngineContract $engine
     * @param NotificationCenter $notificationCenter
     * @param Config $config
     */
    public function __construct(EngineContract $engine, NotificationCenter $notificationCenter, Config $config)
    {
        $this->engine = $engine;
        $this->notification = $notificationCenter;
        $this->config = $config;

        // Console output handler
        $this->output = new ConsoleOutput();

        $this->logger = new Logger('name');

        if ($this->config->get('log', 'enabled')) {
            $this->logger->pushHandler(new StreamHandler($this->config->get('log', 'file'), Logger::INFO));
        } else {
            $this->logger->pushHandler(new NullHandler());
        }
    }
}