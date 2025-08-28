<?php

namespace App\Logging;

use App\Handler\CloudWatchFormatter;
use Aws\CloudWatchLogs\CloudWatchLogsClient;
use App\Handler\CloudWatch;
use Exception;
use Monolog\Logger;
use Monolog\Formatter\JsonFormatter;
use Monolog\Level;

class CloudWatchLoggerFactory
{
    /**
     * @throws Exception
     */
    public function __invoke(array $config): Logger
    {
        // Instantiate AWS SDK CloudWatch Logs Client
        $client = new CloudWatchLogsClient($config['sdk']);

        // Instantiate handler (tags are optional)
        $handler = new CloudWatch(
            $client,
            $config['group_name'],
            $config['stream_name'],
            $config['retention'],
            10000,
            ['my-awesome-tag' => 'tag-value'],
            Level::Info
        );

        // Optionally set the JsonFormatter to be able to access your log messages in a structured way
        $handler->setFormatter(new CloudWatchFormatter());

        $name = $config['name'] ?? 'cloudwatch';

        // Create a log channel
        $logger = new Logger($name);

        // Set handler
        $logger->pushHandler($handler);

        return $logger;
    }
}
