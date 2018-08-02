<?php
	function getloginfo($mkno){
		//数据获取方式未定，暂使用固定值进行测试
		$str = '<Data>
		        <DeliveryCodeNo>MK881000400US</DeliveryCodeNo>
		        <TrackingList>
		            <TrackingList>
		                <BusinessLinkCode>STT99</BusinessLinkCode>
		                <OccurDatetime>2015-04-01 13:11:20</OccurDatetime>
		                <OccurLocation />
		                <TrackingContent>快件已签收,签人是朋友代签，签收网点是深圳华侨城站(0755-86588603，13714523244)</TrackingContent>
		            </TrackingList>
		            <TrackingList>
		                <BusinessLinkCode>STT04</BusinessLinkCode>
		                <OccurDatetime>2015-04-01 09:31:32</OccurDatetime>
		                <OccurLocation />
		                <TrackingContent>深圳华侨城站(0755-86588603，13714523244)的何亮亮正在派件，扫描员是何亮亮</TrackingContent>
		            </TrackingList>
		            <TrackingList>
		                <BusinessLinkCode>STT03</BusinessLinkCode>
		                <OccurDatetime>2015-04-01 08:18:51</OccurDatetime>
		                <OccurLocation />
		                <TrackingContent>快件到达深圳华侨城站(0755-86588603，13714523244)，上一站是深圳分拨中心扫描员是华侨城</TrackingContent>
		            </TrackingList>
		            <TrackingList>
		                <BusinessLinkCode>STT02</BusinessLinkCode>
		                <OccurDatetime>2015-04-01 05:55:38</OccurDatetime>
		                <OccurLocation />
		                <TrackingContent>快件在深圳分拨中心由曾页文扫描发往深圳华侨城站(0755-86588603，13714523244)</TrackingContent>
		            </TrackingList>
		            <TrackingList>
		                <BusinessLinkCode>STT02</BusinessLinkCode>
		                <OccurDatetime>2015-04-01 02:40:11</OccurDatetime>
		                <OccurLocation />
		                <TrackingContent>快件在东莞分拨中心由省内4扫描发往深圳分拨中心</TrackingContent>
		            </TrackingList>
		            <TrackingList>
		                <BusinessLinkCode>STT03</BusinessLinkCode>
		                <OccurDatetime>2015-04-01 00:42:54</OccurDatetime>
		                <OccurLocation />
		                <TrackingContent>快件到达东莞分拨中心，上一站是东莞分拨陆运组扫描员是过磅7</TrackingContent>
		            </TrackingList>
		            <TrackingList>
		                <BusinessLinkCode>IS</BusinessLinkCode>
		                <OccurDatetime>2015-03-31 23:03:42</OccurDatetime>
		                <OccurLocation>HuMen</OccurLocation>
		                <TrackingContent>已交接国内派送服务商</TrackingContent>
		            </TrackingList>
		            <TrackingList>
		                <BusinessLinkCode>STT02</BusinessLinkCode>
		                <OccurDatetime>2015-03-31 18:53:45</OccurDatetime>
		                <OccurLocation />
		                <TrackingContent>快件在东莞长安二站(0769-82381507)由递四方扫描发往东莞分拨中心</TrackingContent>
		            </TrackingList>
		            <TrackingList>
		                <BusinessLinkCode>CCE</BusinessLinkCode>
		                <OccurDatetime>2015-03-31 17:16:15</OccurDatetime>
		                <OccurLocation>HuMen</OccurLocation>
		                <TrackingContent>清关完成</TrackingContent>
		            </TrackingList>
		            <TrackingList>
		                <BusinessLinkCode>CC</BusinessLinkCode>
		                <OccurDatetime>2015-03-31 09:15:11</OccurDatetime>
		                <OccurLocation>HuMen</OccurLocation>
		                <TrackingContent>等待海关查验</TrackingContent>
		            </TrackingList>
		            <TrackingList>
		                <BusinessLinkCode>PICKUP</BusinessLinkCode>
		                <OccurDatetime>2015-03-30 09:46:28</OccurDatetime>
		                <OccurLocation />
		                <TrackingContent>包裹从机场提取,转往海关监管仓库等候报关</TrackingContent>
		            </TrackingList>
		            <TrackingList>
		                <BusinessLinkCode>AA</BusinessLinkCode>
		                <OccurDatetime>2015-03-27 16:32:31</OccurDatetime>
		                <OccurLocation />
		                <TrackingContent>货物到达港口</TrackingContent>
		            </TrackingList>
		            <TrackingList>
		                <BusinessLinkCode>DA</BusinessLinkCode>
		                <OccurDatetime>2015-03-25 04:54:17</OccurDatetime>
		                <OccurLocation />
		                <TrackingContent>货物离开起运港</TrackingContent>
		            </TrackingList>
		            <TrackingList>
		                <BusinessLinkCode>LO</BusinessLinkCode>
		                <OccurDatetime>2015-03-24 08:57:15</OccurDatetime>
		                <OccurLocation>Chicago</OccurLocation>
		                <TrackingContent>离开海外仓库</TrackingContent>
		            </TrackingList>
		            <TrackingList>
		                <BusinessLinkCode>PC</BusinessLinkCode>
		                <OccurDatetime>2014-12-06 11:36:15</OccurDatetime>
		                <OccurLocation />
		                <TrackingContent>完成支付</TrackingContent>
		            </TrackingList>
		            <TrackingList>
		                <BusinessLinkCode>OR</BusinessLinkCode>
		                <OccurDatetime>2014-11-24 00:44:27</OccurDatetime>
		                <OccurLocation />
		                <TrackingContent>订单信息已收到</TrackingContent>
		            </TrackingList>
		            <TrackingList>
		                <BusinessLinkCode>AO</BusinessLinkCode>
		                <OccurDatetime>2014-11-05 07:23:53</OccurDatetime>
		                <OccurLocation>Chicago</OccurLocation>
		                <TrackingContent>到达海外仓库</TrackingContent>
		            </TrackingList>
		        </TrackingList>
		    </Data>';
			return json_decode(json_encode((array) simplexml_load_string($str)), true);
	}