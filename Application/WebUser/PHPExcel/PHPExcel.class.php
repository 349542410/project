<?php

    namespace WebUser\PHPExcel;

    class PHPExcel{

        public $xls;
        private $error = '';

        public function getError(){
            return $this->error;
        }

        public function __construct(){

            // 引用PHPexcel 类
            include_once(dirname(__FILE__) . '/PHPExcel/Classes/PHPExcel.php');  
            include_once(dirname(__FILE__) . '/PHPExcel/Classes/PHPExcel/IOFactory.php');

        }

        public function read($inputFileName='',$encode='utf-8'){

            if(empty($inputFileName)||!file_exists($inputFileName)){
                $this->error = 'File does not exist';
                return false;
            }

            

            \set_error_handler(array('PHPExcel_Exception','errorHandlerCallback'), E_ALL ^ E_NOTICE);
            try{
                $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);         //检测类型
                $this->xls = \PHPExcel_IOFactory::createReader($inputFileType);         //创建excel对象
                $this->xls->setReadDataOnly(true);                                      //设置只读（加快速度）
                $objPHPExcel = $this->xls->load($inputFileName,$encode);                //载入文件

                $objWorksheet = $objPHPExcel->getActiveSheet();
                $highestRow = $objWorksheet->getHighestRow();
                $highestColumn = $objWorksheet->getHighestColumn();
                $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
            }catch(\PHPExcel_Exception $e){
                $this->error = $e->getMessage();
                return false;
            }
            

            $excelData = array();
            for ($row = 0; $row <= $highestRow; $row++) {
                for ($col = 0; $col < $highestColumnIndex; $col++) {
                    $excelData[$row][] = trim((string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
                }
            }
            return $excelData;

        }


        public function test(){

            return $this->xls;

        }


        // 修改原有excel
        // 写入数据为 $data，从第 $row 行开始
        public function write($data, $row = 4){

            $inputFileName = dirname(__FILE__) . "/temp/cc-import.xlsx";
            $encode = 'utf8';

            if(empty($data)){
                $this->error = "data can not be empty";
                return false;
            }


            \set_error_handler(array('PHPExcel_Exception','errorHandlerCallback'), E_ALL ^ E_NOTICE);
            try{
                $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);         //检测类型
                $this->xls = \PHPExcel_IOFactory::createReader($inputFileType);         //创建excel对象
                $objPHPExcel = $this->xls->load($inputFileName,$encode);                //载入文件

            }catch(\PHPExcel_Exception $e){
                $this->error = $e->getMessage();
                return false;
            }


            // $objPHPExcel->setActiveSheetIndex(15)->setCellValue('A0', '111')->setCellValue('A1', '222');

            /* 开始写入 */
            // 最大 A-Z

            foreach($data as $key=>$value){
                $diff = ord('A');
                foreach($value as $k=>$v){
                    $objPHPExcel->getActiveSheet()->setCellValue(chr($k + $diff) . $row, $v);
                }
                $row++;
            }
            
            /* 写入完成 */


            // 输出（保存）文件
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="批量下单.xlsx"');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            $objPHPExcel->disconnectWorksheets();

        }


        // 导出到新文件
        public function write_empty($header=[], $body=[], $filename=''){

            if(empty($filename)){
                $filename = 'filename';
            }

            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);

            // 获取当前活动表
            $objActSheet = $objPHPExcel->getActiveSheet();

            // 设置活动表名称
            $objActSheet->setTitle('order_info');

            // 设置单元格为文本格式
            $objPHPExcel->getActiveSheet()->getStyle('B')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $objPHPExcel->getActiveSheet()->getStyle('F')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $objPHPExcel->getActiveSheet()->getStyle('I')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);

            // 设置单元格宽度自适应
            $objActSheet->getColumnDimension( 'A')->setAutoSize(true);
            $objActSheet->getColumnDimension( 'B')->setAutoSize(true);
            $objActSheet->getColumnDimension( 'C')->setAutoSize(true);
            $objActSheet->getColumnDimension( 'D')->setAutoSize(true);
            $objActSheet->getColumnDimension( 'E')->setAutoSize(true);
            $objActSheet->getColumnDimension( 'F')->setAutoSize(true);
            $objActSheet->getColumnDimension( 'G')->setAutoSize(true);
            $objActSheet->getColumnDimension( 'H')->setAutoSize(true);
            $objActSheet->getColumnDimension( 'I')->setAutoSize(true);
            $objActSheet->getColumnDimension( 'J')->setAutoSize(true);
            $objActSheet->getColumnDimension( 'K')->setAutoSize(true);
            $objActSheet->getColumnDimension( 'L')->setAutoSize(true);
            $objActSheet->getColumnDimension( 'M')->setAutoSize(true);
            $objActSheet->getColumnDimension( 'N')->setAutoSize(true);
            $objActSheet->getColumnDimension( 'O')->setAutoSize(true);
            $objActSheet->getColumnDimension( 'P')->setAutoSize(true);
            $objActSheet->getColumnDimension( 'Q')->setAutoSize(true);
            $objActSheet->getColumnDimension( 'R')->setAutoSize(true);
            $objActSheet->getColumnDimension( 'S')->setAutoSize(true);

            if(!empty($header)){
                $objPHPExcel->getActiveSheet()->fromArray($header, null, 'A1');
            }

            if(!empty($body)){
                $objPHPExcel->getActiveSheet()->fromArray($body, null, 'A2');
            }

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');
            // If you're serving to IE over SSL, then the following may be needed
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            $objPHPExcel->disconnectWorksheets();

        }


        // private function next_chr($str){

        //     $len = strlen($str);

        //     if($len == 1){
        //         $last = $str;
        //     }else{
        //         $last = substr($str, -1, 1);
        //     }

        //     if($last == 'Z'){
        //         if($len == 1){
        //             return 'AA';
        //         }else{

        //         }
        //         return $str . 'A';
        //     }else{
        //         $nl = chr(ord($last)+1);
        //         if($len == 1){
        //             return $nl;
        //         }else{
        //             return substr($str, 0, $len-1) . $nl;
        //         }
        //     }
            
        // }


    }