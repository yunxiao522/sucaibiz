<?php

namespace app\admin\controller;

use app\common\controller\BaseController;
use SucaiZ\config;
use XS;
use AlibabaCloud\Client\AlibabaCloud;
use app\model\Article;
use app\model\Column;
use XSDocument;


class Test extends BaseController
{
    public function __construct(array $items = [])
    {
        parent::__construct();
    }

    public function index()
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
        $Index->clean();
//        dump($Index->getCustomDict());
//        $Index->setCustomDict('明星');
        $search = $Xs->search; // 搜索对象来自 XS 的属性
//        $search->setFuzzy (true);
//        $search->setLimit(20,20);
//        dump($search->setSort('id', fa)->search('明星王彦霖 column_type:54'));
        $XSTokenizer = new \XSTokenizerAliyun();
//        dump($XSTokenizer->getTokens('唯美,高清,手绘,极简'));
        dump($search->dbTotal);
//        dump($Index->getCustomDict());
//        dump($search->getLastCount());
//        dump($Index->setCustomDict('王彦霖'));
//        dump($Index->setCustomDict('鞠婧祎|王彦霖'));

    }

    public function test2()
    {
        $data = [
            "lang"=>"ZH",
            "text"=>"明星王彦霖",
        ];
        // 设置一个全局客户端
        AlibabaCloud::accessKeyClient(config::get('cfg_aliyun_app_id'),config::get('cfg_aliyun_app_key'))->regionId('cn-shanghai')->asDefaultClient();
        $result = AlibabaCloud::roaRequest()->product('nlp')->version('2018-04-08')->pathPattern('/nlp/api/wordsegment/general')->method('post')
            ->options($data)
            ->body(json_encode($data, JSON_UNESCAPED_UNICODE))
            ->request();
        $result = $result->toArray();
        $result = array_column($result['data'], 'word');
        dump($result);
    }

    private function getNMingXing()
    {
        $str='<a href="/suxing">苏醒</a>

<a href="/chenchusheng">陈楚生</a>

<a href="/amulong">阿穆隆</a>

<a href="/hangeng">韩庚</a>

<a href="/wangrui">王睿</a>

<a href="/wuzun">吴尊</a>

<a href="/chenglong">成龙</a>

<a href="/guodegang">郭德纲</a>

<a href="/mingdao">明道</a>

<a href="/jingboran">井柏然</a>

<a href="/linzhengying">林正英</a>

<a href="/zhouxingchi">周星驰</a>

<a href="/lilianjie">李连杰</a>

<a href="/qiaorenliang">乔任梁</a>

<a href="/wanggang">王刚</a>

<a href="/matianyu">马天宇</a>

<a href="/fuxinbo">付辛博</a>

<a href="/zhaobenshan">赵本山</a>

<a href="/zhourunfa">周润发</a>

<a href="/huangxiaoming">黄晓明</a>

<a href="/lixiaolong">李小龙</a>

<a href="/gutianle">古天乐</a>

<a href="/liangchaowei">梁朝伟</a>

<a href="/zhenzidan">甄子丹</a>

<a href="/sunxiaobao">孙小宝</a>

<a href="/liyugang">李玉刚</a>

<a href="/liyifeng">李易峰</a>

<a href="/tongdawei">佟大为</a>

<a href="/wuzongxian">吴宗宪</a>

<a href="/haimingwei">海鸣威</a>

<a href="/huge">胡歌</a>

<a href="/weichen">魏晨</a>

<a href="/zhangjie">张杰</a>

<a href="/yuhaoming">俞灏明</a>

<a href="/wangyuexin">王栎鑫</a>

<a href="/songxiaobo">宋晓波</a>

<a href="/zhangyuan">张远</a>

<a href="/zhangdianfei">张殿菲</a>

<a href="/zhangchao">张超</a>

<a href="/liweilian">立威廉</a>

<a href="/wudiwen">巫迪文</a>

<a href="/huangrihua">黄日华</a>

<a href="/wujianfei">吴建飞</a>

<a href="/yanyalun">炎亚纶</a>

<a href="/xuezhiqian">薛之谦</a>

<a href="/wangxiaokun">王啸坤</a>

<a href="/liuzhoucheng">刘洲成</a>

<a href="/yusiyuan">俞思远</a>

<a href="/linfeng">林峰</a>

<a href="/shiyang">师洋</a>

<a href="/weibing">魏斌</a>

<a href="/jijie">吉杰</a>

<a href="/wangzhengliang">王铮亮</a>

<a href="/yaozheng">姚政</a>

<a href="/shixiaolong">释小龙</a>

<a href="/tangyuzhe">唐禹哲</a>

<a href="/zhengyuanchang">郑元畅</a>

<a href="/yanan">闫安</a>

<a href="/chenkun">陈坤</a>

<a href="/chenyiru">辰亦儒</a>

<a href="/hejiong">何炅</a>

<a href="/wangshaowei">王绍伟</a>

<a href="/junjun">君君</a>

<a href="/wangdongcheng">汪东城</a>

<a href="/zhangtao">张涛</a>

<a href="/niudongwen">牛东文</a>

<a href="/shengchao">盛超</a>

<a href="/luhu">陆虎</a>

<a href="/chenguanxi">陈冠希</a>

<a href="/zhangxiaochen">张晓晨</a>

<a href="/xiehexuan">谢和弦</a>

<a href="/lvyang">吕杨</a>

<a href="/baojianfeng">保剑锋</a>

<a href="/douzhikong">窦智孔</a>

<a href="/guojingming">郭敬明</a>

<a href="/guobiao">郭彪</a>

<a href="/zhangyi">张译</a>

<a href="/chenbolin">陈柏霖</a>

<a href="/liuye">刘烨</a>

<a href="/maxueyang">马雪阳</a>

<a href="/wangrenfu">王仁甫</a>

<a href="/yuwenle">余文乐</a>

<a href="/luyi">陆毅</a>

<a href="/guopinchao">郭品超</a>

<a href="/chenhaomin">陈浩民</a>

<a href="/herundong">何润东</a>

<a href="/zhangweijian">张卫健</a>

<a href="/hejunxiang">贺军翔</a>

<a href="/xushaoxiang">许绍洋</a>

<a href="/qiushengyi">邱胜翊</a>

<a href="/yangqiyu">杨奇煜</a>

<a href="/zhuanghaoquan">庄濠全</a>

<a href="/sunxiezhi">孙协志</a>

<a href="/xumengzhe">许孟哲</a>

<a href="/wujing">吴京</a>

<a href="/chenshi">陈石</a>

<a href="/zhengyijian">郑伊健</a>

<a href="/zhouluming">周路明</a>

<a href="/pengyuan">彭于晏</a>

<a href="/cetian">侧田</a>

<a href="/lizhinan">李智楠</a>

<a href="/dazhangwei">大张伟</a>

<a href="/liaojunjie">廖俊杰</a>

<a href="/yanchengxu">言承旭</a>

<a href="/huojianhua">霍建华</a>

<a href="/chendi">陈迪</a>

<a href="/keyoulun">柯有纶</a>

<a href="/caiwenyou">蔡旻佑</a>

<a href="/gurenqi">顾人麒</a>

<a href="/tanxu">谭旭</a>

<a href="/gaoxiaochen">郜晓晨</a>

<a href="/huanghaibing">黄海冰</a>

<a href="/yuanbin">元斌</a>

<a href="/caishangpu">蔡尚甫</a>

<a href="/toro">toro</a>

<a href="/liaoyiyin">廖亦崟</a>

<a href="/liujunwei">刘俊纬</a>

<a href="/guojinan">郭晋安</a>

<a href="/mahaisheng">马海生</a>

<a href="/zhonghanliang">钟汉良</a>

<a href="/zhaohongfei">赵鸿飞</a>

<a href="/tae">TAE</a>

<a href="/zhangyishan">张一山</a>

<a href="/dengchao">邓超</a>

<a href="/chendexiu">陈德修</a>

<a href="/liuxianhua">刘宪华</a>

<a href="/chentaishen">成泰燊</a>

<a href="/wengruidi">翁瑞迪</a>

<a href="/huangxin">黄鑫</a>

<a href="/huangzongze">黄宗泽</a>

<a href="/dengning">邓宁</a>

<a href="/wanghan">汪涵</a>

<a href="/nieyuan">聂远</a>

<a href="/chendaoming">陈道明</a>

<a href="/qiuyicheng">邱翊橙</a>

<a href="/shanzijia">杉籽伽</a>

<a href="/liquan">李铨</a>

<a href="/chengxianjun">程显军</a>

<a href="/fangzhongxin">方中信</a>

<a href="/wuzhuoyi">吴卓羲</a>

<a href="/madezhong">马德钟</a>

<a href="/wuyanzu">吴彦祖</a>

<a href="/zhongkai">钟凯</a>

<a href="/chenzeyu">陈泽宇</a>

<a href="/huyuwei">胡宇崴</a>

<a href="/hubing">胡兵</a>

<a href="/caojun">曹骏</a>

<a href="/maofangyuan">毛方圆</a>

<a href="/zhangzizhou">张子洲</a>

<a href="/lianglin">利昂霖</a>

<a href="/qiuze">邱泽</a>

<a href="/zhuzhen">朱桢</a>

<a href="/huangjian">黄键</a>

<a href="/zhangshuang">张爽</a>

<a href="/huanghongsheng">黄鸿升</a>

<a href="/chenchaowei">陈超尉</a>

<a href="/laimingwei">赖铭伟</a>

<a href="/shenjianhong">沈建宏</a>

<a href="/wujianhao">吴建豪</a>

<a href="/litianlong">李天龙</a>

<a href="/jiangweiliang">江伟良</a>

<a href="/linuoyi">黎诺懿</a>

<a href="/jr">简孝儒JR</a>

<a href="/gubaoming">顾宝明</a>

<a href="/gongyi">龚毅</a>

<a href="/yuanye">袁野</a>

<a href="/chenyi">陈奕</a>

<a href="/weijiahong">韦佳宏</a>

<a href="/sagangyun">萨钢云</a>

<a href="/huangweide">黄维德</a>

<a href="/liwei">李威</a>

<a href="/gufeng">顾峰</a>

<a href="/lizhixi">李志希</a>

<a href="/zhengjiaying">郑嘉颖</a>

<a href="/jiaoenjun">焦恩俊</a>

<a href="/xiaoliyang">萧立扬</a>

<a href="/liuwei">刘维</a>

<a href="/yangjunyi">杨俊毅</a>

<a href="/huangzihua">黄子华</a>

<a href="/xiongrulin">熊汝霖</a>

<a href="/chenxiaodong">陈晓东</a>

<a href="/caiguoqing">蔡国庆</a>

<a href="/zhanghan">张翰</a>

<a href="/ruanjingtian">阮经天</a>

<a href="/wuyi">武艺</a>

<a href="/fengshaofeng">冯绍峰</a>

<a href="/zhuzhixiao">朱梓骁</a>

<a href="/wuyifan">吴亦凡</a>

<a href="/zhangyixing">张艺兴</a>

<a href="/yubo">于波</a>

<a href="/hawick">刘恺威</a>

<a href="/machenlong">马晨龙</a>

<a href="/oyyc">欧阳羽臣</a>

<a href="/lishiqi">李诗琦</a>

<a href="/lianxun">连勋</a>

<a href="/yangyang">杨洋</a>

<a href="/kenchu">朱孝天</a>

<a href="/louisliu">刘谦</a>

<a href="/IzzTsui">徐正曦</a>

<a href="/chensicheng">陈思成</a>

<a href="/DuChun">杜淳</a>

<a href="/Mark">赵又廷</a>

<a href="/zhangrui">张睿</a>

<a href="/jiangjingfu">蒋劲夫</a>

<a href="/BLUE">蓝正龙</a>

<a href="/yuxiaotong">于小彤</a>

<a href="/chenhei">陈赫</a>

<a href="/Eric">王传君</a>

<a href="/Kenny">林更新</a>

<a href="/zhangruoyun">张若昀</a>

<a href="/lijiahang">李佳航</a>

<a href="/RonaldLaw">羅鈞滿</a>

<a href="/sunyizhou">孙艺洲</a>

<a href="/Tiger">叶子淳</a>

<a href="/Coffee">康飞</a>

<a href="/luojin">罗晋</a>

<a href="/Kingscar">金世佳</a>

<a href="/Mikey">何晟铭</a>

<a href="/yaoxing">姚鑫</a>

<a href="/tangzeng">唐曾</a>

<a href="/liuenyou">刘恩佑</a>

<a href="/Lanrence">王新</a>

<a href="/KennethMa">马国明</a>

<a href="/wengzhang">文章</a>

<a href="/yuanhong">袁弘</a>

<a href="/liyuze">李雨泽</a>

<a href="/chenxiao">陈晓</a>

<a href="/leinuoer">雷诺儿</a>

<a href="/Haoming">昊明</a>

<a href="/jiame">高梓淇</a>

<a href="/yushaoqun">余少群</a>

<a href="/liliren">李李仁</a>

<a href="/Smile">贾乃亮</a>

<a href="/qiuxinzhi">邱心志</a>

<a href="/miracle">戚迹</a>

<a href="/duanyihuong">段奕宏</a>

<a href="/RoyceWong">王灿</a>

<a href="/Vega">李维嘉</a>

<a href="/jungle">林江国</a>

<a href="/Sam">林子闳</a>

<a href="/SammulChan">陈键锋</a>

<a href="/lijie">李解</a>

<a href="/Calvin">李宗翰</a>

<a href="/Hito">杜海涛</a>

<a href="/linyixun">林奕勋</a>

<a href="/AdamCheng">郑少秋</a>

<a href="/Johnny">张迪</a>

<a href="/wangyi">王煜</a>

<a href="/Matthew">许明杰</a>

<a href="/June">毛若懿</a>

<a href="/WongChoLam">王祖蓝</a>

<a href="/kejiahao">柯家豪</a>

<a href="/KoChenTung">柯震东</a>

<a href="/CheneyChen">陈学冬</a>

<a href="/RenChong">任重</a>

<a href="/qiaozhenyu">乔振宇</a>

<a href="/OscarSun">孙坚</a>

<a href="/Chris">王宥胜</a>

<a href="/huangyubo">黄誉博</a>

<a href="/kelvin">陆昱霖</a>

<a href="/BingbingLi">李冰冰</a>

<a href="/ShenLin">林申</a>

<a href="/YifWang">王亦丰</a>

<a href="/HuangHaiBo">黄海波</a>

<a href="/wangyu">王雨</a>

<a href="/anzaixian">安宰贤</a>

<a href="/YunxiangGao">高云翔</a>

<a href="/WilliamChan">陈伟霆</a>

<a href="/zhangyang">张扬</a>

<a href="/chenxin">陈欣</a>

<a href="/KenChang">张智尧</a>

<a href="/zhengkai">郑恺</a>

<a href="/JerryLee">李晨</a>

<a href="/kaishao">郑凯</a>

<a href="/Simon">连晨翔</a>

<a href="/liuzhihong">刘志宏</a>

<a href="/wangbaoqiang">王宝强</a>

<a href="/Struggle">肖旭</a>

<a href="/ZhuYiLong">朱一龙</a>

<a href="/HanDong">韩栋</a>

<a href="/huangxuan">黄轩</a>

<a href="/Leo">吴磊</a>

<a href="/make">马可</a>

<a href="/baijingting">白敬亭</a>

<a href="/YangTong">杨桐</a>

<a href="/zhangyunlong">张云龙</a>

<a href="/Andy">张丹峰</a>

<a href="/zhangzhuowen">张倬闻</a>

<a href="/GuanyingPeng">彭冠英</a>

<a href="/xiaoshenyang">小沈阳</a>

<a href="/yueyunpeng">岳云鹏</a>

<a href="/zhangfengyi">张丰毅</a>

<a href="/chengyi">成毅</a>

<a href="/qinjunjie">秦俊杰</a>

<a href="/kongchuinan">孔垂楠</a>

<a href="/jiangzile">蒋梓乐</a>

<a href="/yanzidong">晏紫东</a>

<a href="/tongmengshi">佟梦实</a>

<a href="/zhangziwen">张子文</a>

<a href="/yangyouning">杨祐宁</a>

<a href="/denglun">邓伦</a>

<a href="/ranxu">冉旭</a>

<a href="/weidaxun">魏大勋</a>

<a href="/wanglei">王雷</a>

<a href="/zhengyecheng">郑业成</a>

<a href="/lihongyi">李宏毅</a>

<a href="/shenteng">沈腾</a>

<a href="/zhangyijie">张逸杰</a>

<a href="/xingshaolin">邢昭林</a>

<a href="/ronghui">容晖</a>

<a href="/yinzheng">尹正</a>

<a href="/zhaozhiwei">赵志伟</a>

<a href="/huangbo">黄渤</a>

<a href="/sunhonglei">孙红雷</a>

<a href="/huanglei">黄磊</a>

<a href="/zhangmingen">张铭恩</a>

<a href="/liuhaoran">刘昊然</a>

<a href="/zhanglunshuo">张伦硕</a>

<a href="/gaoxiaosong">高晓松</a>

<a href="/gaoweiguang">高伟光</a>

<a href="/renjialun">任嘉伦</a>

<a href="/wangkai">王凯</a>

<a href="/xuweizhou">许魏洲</a>

<a href="/jindong">靳东</a>

<a href="/fengjianyu">冯建宇</a>

<a href="/wangqing">王青</a>

<a href="/xiaozhan">肖战</a>

<a href="/xuhaiqiao">徐海乔</a>

<a href="/zhuyawen">朱亚文</a>

<a href="/shengyilun">盛一伦</a>

<a href="/zhangbinbin">张彬彬</a>

<a href="/majiaqi">马嘉祺</a>

<a href="/Dylan">熊梓淇</a>

<a href="/August">蔡徐坤</a>

<a href="/PurbaRgyal">蒲巴甲</a>

<a href="/huyitian">胡一天</a>

<a href="/ShawnDou">窦骁</a>

<a href="/Kim">金瀚</a>

<a href="/SongWeilong">宋威龙</a>

<a href="/mingkai">明凯</a>

<a href="/chenzheyuan">陈哲远</a>';
        $reg1='/<a href="[^"]*"[^>]*>(.*)<\/a>/';
        preg_match_all($reg1,$str,$aarray);
        return $aarray[1];
    }

}