<?php
namespace Enqueue\RdKafka\Tests\Spec;

use Enqueue\RdKafka\RdKafkaConnectionFactory;
use Interop\Queue\PsrMessage;
use Interop\Queue\Spec\SendToAndReceiveFromTopicSpec;

/**
 * @group functional
 */
class RdKafkaSendToAndReceiveFromTopicTest extends SendToAndReceiveFromTopicSpec
{
    protected function createContext()
    {
        $config = [
            'global' => [
                'group.id' => uniqid('', true),
                'metadata.broker.list' => getenv('RDKAFKA_HOST').':'.getenv('RDKAFKA_PORT'),
                'enable.auto.commit' => 'false',
            ],
            'topic' => [
                'auto.offset.reset' => 'beginning',
            ]
        ];

        return (new RdKafkaConnectionFactory($config))->createContext();
    }

    public function test()
    {
        $context = $this->createContext();

        $topic = $this->createTopic($context, uniqid('', true));

        $consumer = $context->createConsumer($topic);

        $expectedBody = __CLASS__.time();

        $context->createProducer()->send($topic, $context->createMessage($expectedBody));

        $message = $consumer->receive(10000); // 10 sec

        $this->assertInstanceOf(PsrMessage::class, $message);
        $consumer->acknowledge($message);

        $this->assertSame($expectedBody, $message->getBody());
    }
}
