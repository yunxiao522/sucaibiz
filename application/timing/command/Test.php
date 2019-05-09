<?php


namespace app\timing\command;

use app\model\Article;
use app\model\Column;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use XS;
use XSDocument;

class Test extends Command
{
    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('test')->setDescription('Here is the plan ');
    }

    protected function execute(Input $input, Output $output)
    {

        $Xs = new XS('./application/xunsearch.ini');
        $Index = $Xs->index;
        $Article_List = Article::getAll([], 'id,title,channel,description,pubdate,litpic,click,column_id,keywords', 10000);
        foreach ($Article_List as $value){
            sleep(1);
            $value = $value->toArray();
            $value['column_type'] = Column::getField(['id'=>$value['column_id']], 'parent_id');
            // 创建文档对象
            $doc = new XSDocument();
            $doc->setFields($value);
            // 添加到索引数据库中
            $res = $Index->add($doc);
        }
//        $Index->clean();
//        dump($Index->getCustomDict());
//        $Index->setCustomDict('明星');
        $search = $Xs->search; // 搜索对象来自 XS 的属性
//        $search->setFuzzy (true);
//        $search->setLimit(20,20);
//        dump($search->setSort('id', fa)->search('明星王彦霖 column_type:54'));
//        $XSTokenizer = new \XSTokenizerAliyun();
//        dump($XSTokenizer->getTokens('唯美,高清,手绘,极简'));
        dump($search->dbTotal);
//        dump($Index->getCustomDict());
//        dump($search->getLastCount());
//        dump($Index->setCustomDict('王彦霖'));
//        dump($Index->setCustomDict('鞠婧祎|王彦霖'));
    }
}