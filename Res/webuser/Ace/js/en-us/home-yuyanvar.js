var where = new Array(35);
function comefrom(loca,locacity) { this.loca = loca; this.locacity = locacity; }
var hot =[1, 9, 19,23,11];
where[1] = new comefrom("Beijing","Dongcheng|Xicheng|Zhaoyang|Fengtai|Shijingshan|Haidian|Mentougou|Fangshan|Tongzhou|Shunyi|Changping|Daxing|Pinggu|Huairou|Miyun|Yanqing");
where[2] = new comefrom("Tianjin","Heping|Dongli|Hedong|Xiqing|Hexi|Jinnan|Nankai|Beichen|Hebei|Wuqing|Hongqiao|Binhai|Ninghe|Jinghai|Baodi|Jizhou");
where[3] = new comefrom("Hebei","Shijiazhuang|Handan|Xingtai|Baoding|Zhangjiakou|Chengde|Langfang|Tangshan|Qinhuangdao|Cangzhou|Hengshui");
where[4] = new comefrom("Shanxi","Taiyuan|Datong|Yangquan|Changzhi|Jincheng|Shuozhou|Lvliang|Xinzhou|Jinzhong|Linfen|Yuncheng");
where[5] = new comefrom("Neimenggu","Huhehot|Baotou|Wuhai|Chifeng|Tongliao|Hulun Buir|Alsa League|Hinggan League|Ulanqab|Xilingol League|Bayan Nur|Ordos");
where[6] = new comefrom("Liaoling","Shenyang|Dalian|Anshan|Fushun|Benxi|Dandong|Jinzhou|Yingkou|Fuxin|Liaoyang|Panjin|Tieling|Zhaoyang|Huludao");
where[7] = new comefrom("Jilin","Changchun|Jilin|Siping|Liaoyuan|Tonghua|Baishan|Songyuan|Baicheng|Yanbian");
where[8] = new comefrom("Heilongjiang","Haerbin|Qiqihar|Maodanjiang|Jiamusi|Daqing|Suihua|Hegang|Jixi|Heihe|Shuangyashan|Yichun|Qitaihe|Greater Khingan Mountains");
where[9] = new comefrom("Shanghai","Huangpu|Xuhui|Changning|Jingan|Putuo|Hongkou|Yangpu|Minhang|Baoshan|Jiading|Pudong|Jinshan|Songjiang|Qingpu|Fengxian|Chongming");
where[10] = new comefrom("Jiangsu","Nanjing|Zhenjiang|Suzhou|Nantong|Yangzhou|Yancheng|Xuzhou|Lianyungang|Changzhou|Wuxi|Suqian|Taizhou|Huaian");
where[11] = new comefrom("Zhejiang","Hangzhou|Ningbo|Wenshou|Jiaqing|Huzhou|Shaoxing|Jinhua|Quzhou|Danshan|Taizhou|Lishui");
where[12] = new comefrom("Anhui","Hefei|Wuhu|Bengbu|Maanshan|Huaibei|Tongling|Anqing|Huangshan|Chuzhou|Suzhou|Chizhou|Huainan|Yingzhou|Liuan|Xuancheng|Haozhou");
where[13] = new comefrom("Fujian","Fuzhou|Xiamen|Putian|Sanming|Quanzhou|Zhangzhou|Nanping|Longyan|Ningde");
where[14] = new comefrom("Jiangxi","Nanchang|Jingdezhen|Jiujiang|Yingtan|Pingxiang|Xinyu|Ganzhou|Jian|Yichun|Fuzhou|Shangrao");
where[15] = new comefrom("Shandong","Jinan|Qingdao|Zibo|Zaozhuang|Dongying|Yantai|Weifang|Jining|Taian|Weihai|Rizhao|Laiwu|Linyi|Dezhou|Liaocheng|Binzhou|Heze");
where[16] = new comefrom("Henan","Zhengzhou|Kaifeng|Luoyang|Pingdingshan|Anyang|Hebi|Xinxiang|Jiaozuo|Puyang|Xuchang|Luohe|Sanmenxia|Nanyang|Shangqiu|Xinyang|Zhoukou|Zhumadian|Jiyuan");
where[17] = new comefrom("Hubei","Wuhan|Huangsi|Shiyan|Yichang|Xiangyang|Ezhou|Jingmen|Xiaogan|Jingzhou|Huanggang|Xianning|Suizhou|Enshi|Xiantao|Qianjiang|Tianmen|Shennongjia");
where[18] = new comefrom("Hunan","Changsha|Changde|Zhuzhou|Xiangtan|Hengyang|Yueyang|Shaoyang|Yiyang|Loudi|Huaihua|Chenzhou|Yongzhou|Xiangxi|Zhangjiajie");
where[19] = new comefrom("Guangdong","Guangzhou|Shenzhen|Zhuhai|Shantou|Dongguan|Zhongshan|Foshan|Shaoguan|Jiangmen|Zhanjiang|Maoming|Zhaoqing|Huizhou|Meizhou|Shanwei|Heyuan|Yangjiang|Qingyuan|Chaozhou|Jieyang|Yunfu");
where[20] = new comefrom("Guangxi","Nanning|Liuzhou|Guilin|Wuzhou|Beihai|Fangchenggang|Qinzhou|Guigang|Yulin|Laibin|Hezhou|Baise|Hechi|Chongzuo");
where[21] = new comefrom("Hainan","Haikou|Sanya|Sansha|Wuzhishan|Qionghai|Danzhou|Wenchang|Wanning|Dongfang|Dingan|Tunchang|Chengmai|Lingao|Baisha|Changjiang|Ledong|Lingshui|Baoting|Qiongzhong");
where[22] = new comefrom("Chongqing","Wanzhou|Fuling|Yuzhong|Dadukou|Jiangbei|Shapingba|Jiulongpo|Nanan|Beibei|Wansheng|Shuangqiao|Yubei|Banan|Qianjiang|Changshou|Qijiang|Tongnan|Tongliang|Dazu|Rongchang|Bishan|Liungping|Chengkou|Fengdou|Dianjiang|Wulong|Zhongxian|Kaixian|Yunyang|Fengjie|Wushan|Wuxi|Shizhu|Xiushan|Youyang|Pengshui|Jiangjin|Hechuan|Yongchuan|Nanchuan");
where[23] = new comefrom("Sichuan","Chengdu|Jinyang|Deyang|Zigong|Panzhihua|Guangyuan|Suining|Neijiang|Leshan|Ziyang|Nanchong|Yibin|Guangan|Dazhou|Yaan|Bazhong|Meishan|Liangshan|Luzhou|Ganzi|Aba");
where[24] = new comefrom("Guizhou","Guiyang|Liupanshui|Zunyi|Anshun|Tongren|Qianxinan|Bijie|Qiandongnan|Qiannan");
where[25] = new comefrom("Yunnan","Kunming|Dali Bai|Qujing|Yuxi|Zhaotong|Vhuxiong|Honghe|Wenshan|Puer|Xishuangbanna|Baoshan|Dehong|Lijiang|Nujiang|Diqing|Lincang");
where[26] = new comefrom("Tibet","Lhasa|Shigatse|Lhoka|Nyingchi|Qamdo|Agari|Nagqu");
where[27] = new comefrom("Shanxi","Xian|Baoji|Xianyang|Tongchuan|Weinan|Yanan|Yulin|Hanzhong|Ankang|Shangluo");
where[28] = new comefrom("Gansu","Lanzhou|Jiayuguan|Jinchang|Baiyin|Tianshui|Jiuquan|Zhangye|Wuwei|Dingxi|Longnan|Pingliang|Qingyang|Linxia|Gannan");
where[29] = new comefrom("Qinghai","Xining|Haidong|Hainan|Haibei|Huangnan|Yushu|Golog|Haixi");
where[30] = new comefrom("Ningxia","Yinchuan|Shizuishan|Wuzhong|Guyuan|Zhongwei");
where[31] = new comefrom("Xinjiang","Urumqi|Karamay|Lli|Bayingolin|Changji|Kizilsu Kirghiz|Bortala|Turpan|Hami|Kashgar|Hotan|Aksu|Kokdala|Wujiaqu|Tumu shuker|Alaer|Tacheng|Altay|Shihezi");



