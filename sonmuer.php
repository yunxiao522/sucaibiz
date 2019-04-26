<?php

class kafka{
    public static function consumer(){
        $conf = new \RdKafka\Conf();
        // Set a rebalance callback to log partition assignments (optional)
        $conf->setRebalanceCb(function (\RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
            switch ($err) {
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    $kafka->assign($partitions);
                    break;
                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    $kafka->assign(NULL);
                    break;
                default:
                    throw new \Exception($err);
            }
        });
        // Configure the group.id. All consumer with the same group.id will consume
        // different partitions.
        $conf->set('group.id', 'myConsumerGroup');
        // Initial list of Kafka brokers
        $conf->set('metadata.broker.list', '10.30.109.27:9092');
        $topicConf = new \RdKafka\TopicConf();
        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'smallest': start from the beginning
        $topicConf->set('auto.offset.reset', 'smallest');
        // Set the configuration to use for subscribed/assigned topics
        $conf->setDefaultTopicConf($topicConf);
        $consumer = new \RdKafka\KafkaConsumer($conf);
        // Subscribe to topic 'test'
        $consumer->subscribe(['test']);
        while (true) {
            $message = $consumer->consume(10*1000);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    try{
                        self::callback($message->payload);
                    }catch ( \Exception $exception){
                        var_dump($exception);
                    }
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    echo "No more messages; will wait for more\n";
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    echo "Timed out\n";
                    break;
                default:
                    throw new \Exception($message->errstr(), $message->err);
                    break;
            }
        }
    }

    public static function callback($argc){
        self::$argc();
    }

    public static function __callStatic($name, $arguments)
    {
        $fun_arr = json_decode($name,true);
        if(isset($fun_arr['fun'])){
            $fun = $fun_arr['fun'];
            (new self)->$fun($fun_arr['agrs']);
        }
    }

    private function sms($data){
        var_dump($data);
    }

    private function test($data){
        var_dump($data);
    }
}
kafka::consumer();