<?php

namespace Junges\Kafka\Tests\Consumers;

use Illuminate\Support\Str;
use Junges\Kafka\Consumers\CallableConsumer;
use Junges\Kafka\Tests\LaravelKafkaTestCase;
use RdKafka\Message;
use stdClass;

class CallableConsumerTest extends LaravelKafkaTestCase
{
    public function testItDecodesMessages()
    {
        $message = new Message();
        $message->payload =
            <<<JSON
            {"foo": "bar"}
            JSON;
        $message->key = Str::uuid()->toString();
        $message->topic_name = 'test-topic';

        $consumer = new CallableConsumer([$this, 'handleMessage'], [
            function ($message, callable $next): void {
                $decoded = json_decode($message->payload);
                $next($decoded);
            },
            function (stdClass $message, callable $next): void {
                $decoded = (array) $message;
                $next($decoded);
            },
        ]);

        $consumer->handle($message);
    }

    public function handleMessage(array $data): void
    {
        $this->assertEquals([
            'foo' => 'bar',
        ], $data);
    }
}