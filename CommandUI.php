<?php
/**
 * slince template collector library
 * @author Tao <taosikai@yeah.net>
 */

namespace Slince\Collector;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Slince\Event\Event;

class CommandUI extends BaseCommand
{
    /**
     * @var Collector
     */
    protected $collector;

    /**
     * @var ProgressBar
     */
    protected $progressBar;

    /**
     * 默认的采集器配置文件名
     * var string
     */
    const CONFIG_FILE = 'collector.json';

    function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->collector = new Collector('./', '');
    }

    function configure()
    {
        $this->setName('capture')
            ->addArgument('url', InputArgument::VALUE_OPTIONAL, 'Entrance url,collector will collect from this link')
            ->addOption('savepath', null, InputOption::VALUE_OPTIONAL, 'Template save path', './')
            ->addOption('whitelist',null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Whitelist url,colllector will collect')
            ->addOption('blacklist', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Blacklist url,colllector will not collect')
            ->addOption('hosts', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The host allowed collect')
            ->addOption('patterns', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The capture url regex patterns')
            ->addOption('onlyPatterns', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The host allowed collect');
    }

    function execute(InputInterface $input, OutputInterface $output)
    {
        $parameters = $this->extractArgumentsAndOptions($input);
        //如果当前目录下有配置文件自动加载配置文件
        if (is_file($configFile = getcwd() . DIRECTORY_SEPARATOR . self::CONFIG_FILE)) {
            $data = json_decode(file_get_contents($configFile), true);
            $parameters = array_merge($parameters, $data);
        }
        call_user_func_array([$this, 'initializeCollector'], $parameters);
        $this->collector->getDispatcher()->bind(Collector::EVENT_CAPTURE_URL_REPOSITORY, function(Event $event) use ($output){
            $repository = $event->getArgument('repository');
            $output->writeln(PHP_EOL);
            $output->writeln($repository->getUrl()->getUrlString());
            $progressBar = new ProgressBar($output, 100);
            $progressBar->start();
            $repository->getUrl()->setParameter('progressBar', $progressBar);
        });
        $this->collector->getDispatcher()->bind(Collector::EVENT_CAPTURED_URL_REPOSITORY, function(Event $event) use ($output){
            $repository = $event->getArgument('repository');
            $progressBar = $repository->getUrl()->getParameter('progressBar');
            $progressBar->advance(50);
            $progressBar->finish();
        });
        $this->collector->run();
    }

    /**
     * 提取参数
     * @param InputInterface $input
     * @return array
     */
    protected function extractArgumentsAndOptions(InputInterface $input)
    {
        $entranceUrl = $input->getArgument('url');
        $savePath = $input->getOption('savepath');
        $whitelistUrls = $input->getOption('whitelist');
        $blacklistUrls = $input->getOption('blacklist');
        $allowCaptureHosts = $input->getOption('hosts');
        $urlPatterns = $input->getOption('patterns');
        $onlyCaptureUrlPatterns = $input->getOption('onlyPatterns');
        return [
            'url' => $entranceUrl,
            'savepath' => $savePath,
            'whitelist' => $whitelistUrls,
            'blacklist' => $blacklistUrls,
            'hosts' => $allowCaptureHosts,
            'patterns' => $urlPatterns,
            'onlyPatterns' => $onlyCaptureUrlPatterns
        ];
    }

    /**
     * 初始化采集器
     * @param $entranceUrl
     * @param $savePath
     * @param array $allowCaptureHosts
     * @param $whitelistUrls
     * @param $blacklistUrls
     * @param $urlPatterns
     * @param $onlyCaptureUrlPatterns
     */
    protected function initializeCollector($entranceUrl, $savePath, array $allowCaptureHosts, array $whitelistUrls, array $blacklistUrls, array $urlPatterns, $onlyCaptureUrlPatterns)
    {
        $this->collector->setRawEntranceUrl($entranceUrl);
        $this->collector->setSavePath($savePath);
        if (!empty($allowCaptureHosts)) {
            $this->collector->setAllowedCaptureHosts($allowCaptureHosts);
        }
        if (!empty($whitelistUrls)) {
            $this->collector->setWhitelistUrls($whitelistUrls);
        }
        if (!empty($blacklistUrls)) {
            $this->collector->setBlacklistUrls($blacklistUrls);
        }
        if (!empty($urlPatterns)) {
            $this->collector->setUrlPatterns($urlPatterns);
        }
        $this->collector->setOnlyCaptureUrlPatterns($onlyCaptureUrlPatterns);
    }

    static function main()
    {
        $application = new Application();
        $command = new static();
        $application->add($command);
        $application->setDefaultCommand($command->getName());
        $application->run();
    }
}