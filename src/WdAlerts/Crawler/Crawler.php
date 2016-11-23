<?php
namespace dstuecken\WdAlerts\Crawler;

use dstuecken\Notify\NotificationCenter;
use dstuecken\WdAlerts\Config;
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
     * Start crawling process
     */
    public function crawl()
    {
        $shellStart = $this->config->get('hooks', 'shellStart');
        if ($shellStart) {
            shell_exec($shellStart);
        }

        if ($this->config->getOption('enableMacOsSpeechOutput')) {
            shell_exec('say -v Alex "I\'m happy to start scanning for your deals!"');
        }

        // Amazon offer listing page, e.g. https://www.amazon.de/gp/offer-listing/B00ULWWFIC
        $amazonPageUrl = $this->engine->getCrawlUrl();

        // Instances
        $notificationCenter = $this->notification;

        try {
            $contextOptions = [
                "ssl" => [
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ],
                'http' => [
                    'method' => "GET",
                    'header' =>
                        "Accept-language: " . $this->config->get('options', 'acceptLanguage') . "\r\n" .
                        "User-Agent: " . $this->config->get('options', 'userAgent') . "\r\n",
                    'proxy' => $this->config->getOption('proxy'),
                    'request_fulluri' => true,
                ],
            ];

            while (1) {
                try {
                    $listingUrl = rtrim($amazonPageUrl, '/') . "/" . $this->engine->getArticleId();

                    if ($data = @file_get_contents($listingUrl, false, stream_context_create($contextOptions))) {
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

                            $notificationCenter->info($body);

                            $this->consoleOutput($body);

                            $shellDeal = $this->config->get('hooks', 'shellDeal');
                            if ($shellDeal) {
                                shell_exec(str_replace([$result->getTitle(), $result->getCondition(), $result->getPrice()], ['%TITLE%', '%CONDITION%', '%PRICE%'], $shellDeal));
                            }

                            if ($this->config->getOption('enableMacOsSpeechOutput')) {
                                shell_exec('say -v Alex "Yay, your deal ' . $result->getTitle() . ' is available for ' . $result->getPrice() . '"');
                            }

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
                        $this->consoleOutput("Error retrieving page: <error>" . error_get_last()['message'] . '</error>');
                    }

                    $this->consoleOutput(sprintf("Sleeping <comment>%s</comment> seconds..\n", $this->config->getOption('updateInterval')));

                    sleep($this->config->getOption('updateInterval'));

                } catch (\Exception $e) {
                    $this->consoleOutput($e->getMessage());
                    $notificationCenter->error($e->getMessage());

                    // Sleep one second if an error occurred
                    sleep(1);
                }
            }
        } catch (\Exception $e) {
            $this->consoleOutput($e->getMessage());
            $notificationCenter->error($e->getMessage());
        }

    }

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
        }
    }
}