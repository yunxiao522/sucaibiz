<?php


namespace app\timing\command;

use app\timing\model\Queue;
use SucaiZ\config;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Exception;

class Task extends Command
{
    /**
     * @var array
     * kafka服务器地址
     */
    private $broker_list;
    /**
     * @var string
     * 消费者分组名称
     */
    private $groupid = 'myConsumerGroup';
    /**
     * @var string
     * 获取数据模式
     */
    private $offset = 'smallest';
    /**
     * @var string
     * kafka使用的topic
     */
    private $topic = 'test';
    /**
     * @var string
     * mysql队列数据表名称
     */
    private static $table = 'queue';

    public function __construct()
    {
        parent::__construct();
        $this->broker_list = config::get('cfg_kafka_host');
    }

    protected function configure()
    {
        $this->setName('task')->setDescription('Here is the remark ');
    }

    protected function execute(Input $input, Output $output)
    {
        //防止执行超时
        set_time_limit(0);
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
        $conf->set('group.id', $this->groupid);
        // Initial list of Kafka brokers
        $conf->set('metadata.broker.list', $this->broker_list);
        $topicConf = new \RdKafka\TopicConf();
        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'smallest': start from the beginning
        $topicConf->set('auto.offset.reset', $this->offset);
        // Set the configuration to use for subscribed/assigned topics
        $conf->setDefaultTopicConf($topicConf);
        $consumer = new \RdKafka\KafkaConsumer($conf);
        // Subscribe to topic 'test'
        $consumer->subscribe([$this->topic]);
        while (true) {
            $message = $consumer->consume(12 * 1000);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    try {
                        self::callback($message->payload);
                    } catch (\Exception $exception) {
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
//                    throw new \Exception($message->errstr(), $message->err);
                    break;
            }
        }
    }

    /**
     * @param $argc
     * @throws Exception
     * @throws \think\exception\PDOException
     * Description 队列执行回调函数
     */
    private static function callback($argc)
    {
        $fun = self::str_separat($argc,'fun');
        $queue_id = self::insertQueue($fun, 2, 0, $argc, 1);
        try{
            $res = (new static())->$fun(self::str_separat($argc,'agrs'));
            if($res){
                self::modifyQueue($queue_id,[
                    'status'=>2,
                    'out_time'=>time()
                ]);
            }else{
                self::modifyQueue($queue_id,[
                    'status'=>3
                ]);
            }
        }catch (Exception $e){
            self::modifyQueue($queue_id,[
                'error_info'=>$e->getMessage(),
                'status'=>3
            ]);
        }
    }

    /**
     * @param string $queue_name
     * @param string $queue_type
     * @param string $out_time
     * @param string $content
     * @param string $status
     * @return int|string
     * Description 将数据写入队列表
     */
    private static function insertQueue($queue_name = '', $queue_type = '', $out_time = '', $content = '', $status = '')
    {
        $d = [
            'queue_name' => $queue_name,
            'queue_type' => $queue_type,
            'out_time' => $out_time,
            'content' => $content,
            'status' => $status,
            'create_time' => time()
        ];
        return (new Queue())->add($d);
    }

    /**
     * @param string $id
     * @param string $out_time
     * @param $data
     * @return int|string
     * @throws Exception
     * @throws \think\exception\PDOException
     * Description 修改队列信息
     */
    private static function modifyQueue($id,$data){
        return (new Queue())->edit(['id'=>$id],$data);
    }

    /**
     * @param $argc
     * @param string $type
     * @return mixed
     * Description 分离json字符串中的数据
     */
    private static function str_separat($argc ,$type = 'fun')
    {
        $agrc_arr = json_decode($argc, true);
        return $agrc_arr[$type];
    }

    public function __call($name, $arguments)
    {
        $exe = new Execute();
        return call_user_func_array([$exe,$name],$arguments);
        // TODO: Implement __callStatic() method.
    }
}