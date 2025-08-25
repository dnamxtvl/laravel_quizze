<?php

namespace App\Handler;

use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;
use Monolog\Formatter\JsonFormatter;
use Throwable;

class CloudWatchFormatter extends JsonFormatter implements FormatterInterface
{
    public function format(LogRecord $record): string
    {
        $appName = config('APP_NAME', 'laravel');
        $appEnv = config('APP_ENV', 'local');
        $logLevel = strtoupper($record->level->getName());

        // Format the message with prefix
        $formattedMessage = sprintf(
            "%s.%s.%s: %s",
            $appName,
            $appEnv,
            $logLevel,
            $record->message
        );

        // Create a new record with formatted message
        $formatted = new LogRecord(
            datetime: $record->datetime,
            channel: $record->channel,
            level: $record->level,
            message: $formattedMessage,
            context: !empty($record->context) ? $this->processContext($record->context) : [$record->message],
            extra: $record->extra
        );

        return parent::format($formatted);
    }

    public function formatBatch(array $records): string
    {
        $formatted = [];
        foreach ($records as $record) {
            $formatted[] = $this->format($record);
        }

        return implode('', $formatted);
    }

    private function processContext(array $context): array
    {
        return array_map(function ($item) {
            if ($item instanceof Throwable) {
                return $this->convertExceptionToArray($item);
            }
            return $item;
        }, $context);
    }

    private function convertExceptionToArray(Throwable $exception): array
    {
        $result = [
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ];

        // Xử lý trace - convert to array properly
        $result['trace'] = array_map(function ($trace) {
            return [
                'file' => $trace['file'] ?? null,
                'line' => $trace['line'] ?? null,
                'function' => $trace['function'] ?? null,
                'class' => $trace['class'] ?? null,
                'type' => $trace['type'] ?? null,
                'args' => $this->convertTraceArgs($trace['args'] ?? []),
            ];
        }, $exception->getTrace());

        // Add previous exception if exists
        if ($previous = $exception->getPrevious()) {
            $result['previous'] = $this->convertExceptionToArray($previous);
        }

        return $result;
    }

    private function convertTraceArgs(array $args): array
    {
        return array_map(function ($arg) {
            if (is_object($arg)) {
                return get_class($arg) . ' object';
            }
            if (is_resource($arg)) {
                return 'resource';
            }
            if (is_array($arg)) {
                return $this->convertTraceArgs($arg);
            }
            return $arg;
        }, $args);
    }
}
