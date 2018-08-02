<?php
/**
 * PHPExcel  xls文件导出  暂时只有 MKBc3PtwoController 使用此功能
 * 专门用于导出数据表头字段超出26列
 * Date: 2017/09/27
 */

namespace Libm\MKILExcel;
class ExcelOperation
{

    public function __construct()
    {
        require_once (dirname(__FILE__) . '/Classes/PHPExcel.php');
        require_once (dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php');
        require_once (dirname(__FILE__) . '/Classes/PHPExcel/Writer/Excel5.php');     // 用于其他低版本xls
        require_once (dirname(__FILE__) . '/Classes/PHPExcel/Writer/Excel2007.php'); // 用于 excel-2007 格式
    }

    /**
     * 数据导出
     * @param string $fileName  文件名
     * @param array $headArr    表头数据（一维）
     * @param array $data       列表数据（二维）
     * @param int   $width      列宽
     * @return bool
     */
    public function push($fileName="", $headArr=array(), $data=array(), $width=20)
    {

        if (empty($headArr) && !is_array($headArr) && empty($data) && !is_array($data)) {
            return false;
        }

        $date = date("YmdHis",time());
        $fileName .= "_{$date}.xls";

        $objPHPExcel = new \PHPExcel();

        //设置表头
        $tem_key = "A";
        foreach($headArr as $v){
            if (strlen($tem_key) > 1) {
                $arr_key = str_split($tem_key);
                $colum = '';
                foreach ($arr_key as $ke=>$va) {
                    $colum .= chr(ord($va));
                }
            } else {
                $key = ord($tem_key);
                $colum = chr($key);
            }

            $objPHPExcel->getActiveSheet()->getColumnDimension($colum)->setWidth($width); // 列宽
            $objPHPExcel->getActiveSheet()->getStyle($colum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); // 垂直居中
            $objPHPExcel->getActiveSheet()->getStyle($colum)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $objPHPExcel->getActiveSheet()->getStyle($colum.'1')->getFont()->setBold(true); // 字体加粗
            //设置column的border
            $objPHPExcel->getActiveSheet()->getStyle($colum.'1')->getBorders()->getLeft()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle($colum.'1')->getBorders()->getTop()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle($colum.'1')->getBorders()->getBottom()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle($colum.'1')->getBorders()->getRight()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

            //设置border的color
            $objPHPExcel->getActiveSheet()->getStyle($colum.'1')->getBorders()->getLeft()->getColor()->setARGB('FF993300');
            $objPHPExcel->getActiveSheet()->getStyle($colum.'1')->getBorders()->getTop()->getColor()->setARGB('FF993300');
            $objPHPExcel->getActiveSheet()->getStyle($colum.'1')->getBorders()->getBottom()->getColor()->setARGB('FF993300');
            $objPHPExcel->getActiveSheet()->getStyle($colum.'1')->getBorders()->getRight()->getColor()->setARGB('FF993300');

            //设置填充颜色
            $objPHPExcel->getActiveSheet()->getStyle($colum.'1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle($colum.'1')->getFill()->getStartColor()->setARGB('FF808080');
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $tem_key++;
        }

        $objActSheet = $objPHPExcel->getActiveSheet();

        $border_end = 'A1'; // 边框结束位置初始化

        // 写入内容
        $column = 2;
        foreach($data as $key => $rows){ //获取一行数据
            $tem_span = "A";
            foreach($rows as $keyName=>$value){// 写入一行数据
                if (strlen($tem_span) > 1) {
                    $arr_span = str_split($tem_span);
                    $j = '';
                    foreach ($arr_span as $ke=>$va) {
                        $j .= chr(ord($va));
                    }
                } else {
                    $span = ord($tem_span);
                    $j = chr($span);
                }
                $objActSheet->setCellValue($j.$column, $value);
                $objActSheet->setCellValueExplicit($j.$column, $value,\PHPExcel_Cell_DataType::TYPE_STRING);//设置单元格为文本格式
                $border_end = $j.$column;
                $tem_span++;
            }
            $column++;
        }

        $objActSheet->getStyle("A1:".$border_end)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN); // 设置边框

        $fileName = iconv("utf-8", "gb2312", $fileName);

        //重命名表
        //$objPHPExcel->getActiveSheet()->setTitle('test');

        //设置活动单指数到第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }
}