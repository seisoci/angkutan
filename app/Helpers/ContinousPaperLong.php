<?php
namespace App\Helpers;

use stdClass;

class ContinousPaperLong {

  private $config, $component;

  public function __construct($config){
    $this->config 	= new stdClass();
    $this->component 	= new stdClass();

    $this->config->length 				= $config['panjang'];
    $this->config->rows_page 			= $config['baris'];
    $this->config->page_margin 			= $config['spasi'];
    $this->config->table_heading 		= $config['table']['header'];
    $this->config->table_column_length 	= $config['column_width']['table'];

    $this->component->header 	= $this->set_header_data($config['column_width']['header'],$config['header']);
    $this->component->body 		= $this->set_body_data($config['column_width']['table'],$config['table']['produk']);
    $this->component->footer 	= $this->set_footer_data($config['column_width']['footer'],$config['footer'],$config['table']['footer']);
  }

  private function text_align($text, $length = 0, $align = ''){
    $list_align = array(
      'left' 		=> STR_PAD_RIGHT,
      'right' 	=> STR_PAD_LEFT,
      'center' 	=> STR_PAD_BOTH
    );
    $align = isset($align) ? strtolower($align) : 'left';
    $align = in_array($align,array_keys($list_align)) ? $align : 'left';
    return str_pad($text, $length, ' ', $list_align[$align]);
  }

  private function convert_text_line($config_column, $data_row){
    $lines = [];
    foreach( $config_column AS $column_index => $length ){
      $length = $column_index > 0 ? $length - 1 : $length;
      $align 	= in_array($column_index,array(0,2,3,4,8)) ? 'right':'left';

      $text = isset($data_row[$column_index]) ? $data_row[$column_index] : '';
      $text = in_array($column_index,array(0,2,3,4,8)) ?$text : $text;
      $text = preg_replace("/\s++/"," ", $text);
      $text_split = explode("<--xx_SPLIT_xx-->", wordwrap($text, $length, "<--xx_SPLIT_xx-->"));

      foreach($text_split AS $row_index => $value){
        $lines[$row_index][$column_index] = $this->text_align($value, $length, $align);
      }
    }
    $total_rows 	= count($lines);
    $total_column 	= count($config_column);
    $output 		= [];
    for($row_index = 0; $row_index < $total_rows; $row_index++){
      for($column_index = 0; $column_index < $total_column; $column_index++){

        $length = $column_index > 0 ? $config_column[$column_index] - 1 : $config_column[$column_index];
        $align = in_array($column_index,array(0,2,4,8)) ? 'right':'left';

        $content = isset($lines[$row_index][$column_index]) ? $lines[$row_index][$column_index] : " ";

        $output[$row_index][$column_index] = $this->text_align($content, $length, $align);
      }
    }
    $result = [];
    foreach($output AS $row){
      array_push($result, implode(" ",$row));
    }
    return $result;
  }

  private function set_header_data($config_column, $data = null){
    $left_column_temp = $data['left'] ?? array();
    $right_column_temp = $data['right'] ?? array();
    $left_column = [];
    foreach($left_column_temp AS $text){
      $length = $config_column[0];
      $text = preg_replace("/\s++/"," ", $text);
      $text_split = explode("<--xx_SPLIT_xx-->", wordwrap($text, $length, "<--xx_SPLIT_xx-->"));
      $left_column = array_merge($left_column,$text_split);
    }
    $right_column = [];
    foreach($right_column_temp AS $text){
      $length = $config_column[1] - 1;
      $text = preg_replace("/\s++/"," ", $text);
      $text_split = explode("<--xx_SPLIT_xx-->", wordwrap($text, $length, "<--xx_SPLIT_xx-->"));
      $right_column = array_merge($right_column,$text_split);
    }

    $total_left_column_rows 	= count($left_column);
    $total_right_column_rows 	= count($right_column);
    $total_rows = $total_left_column_rows >= $total_right_column_rows ? $total_left_column_rows: $total_right_column_rows;
    $output = [];
    for($row_index = 0; $row_index < $total_rows; $row_index++){
      $left 	= isset($left_column[$row_index]) ? $left_column[$row_index] : "";
      $right 	= isset($right_column[$row_index]) ? $right_column[$row_index] : "";
      $output[$row_index] = array($left,$right);
    }
    array_push($output,array("",""));
    array_push($output,array((isset($data['invoice']) ? $data['invoice'] : '') , (isset($data['tanggal_invoice']) ? $data['tanggal_invoice'] : '')));
    foreach($output AS $row_index => $row){
      $line = [];
      foreach($row AS $column_index => $column ){
        $length = $column_index == 0 ? $config_column[0] : $config_column[1] - 1;
        $align 	= $column_index == 0 ? 'left' : 'right';
        $line[$column_index] = $this->text_align($column, $length, $align);
      }
      $output[$row_index] = implode(" ",$line);
    }
    return $output;
  }

  private function set_body_data($config_column, $data = null){
    $output = [];
    foreach($data AS $row){
      $rows = $this->convert_text_line($config_column, array_values($row));
      foreach($rows AS $item){
        array_push($output,$item);
      }
    }
    return $output;
  }

  private function set_footer_data($config_column, $rows, $note = null){
//    $rows = array(
//      array('Mengetahui','Meminta : '),
//      array('',''),
//      array('',''),
//      array(str_pad('.',$config_column[0] - 20,'.',STR_PAD_RIGHT),str_pad('.',$config_column[0] - 20,'.',STR_PAD_RIGHT)),
//    );
//    dd($rows);
    $lines = [];
    foreach($config_column AS $column_index => $length){
      $length = $column_index < 1 ? $length : $length - 1;
      $align 	= in_array($column_index, array(1,2)) ? 'center':'center';

      foreach($rows AS $row_index => $row){
        $lines[$row_index][$column_index] = $this->text_align($row['data'][$column_index], $length, $row['align']);
      }
    }

    $output = [];
    foreach($lines AS $line){
      array_push($output,implode(" ",$line));
    }

    $text 			= preg_replace("/\s++/"," ", ($note['catatan'] ?? ''));
    $text_split 	= explode("<--xx_SPLIT_xx-->", wordwrap($text, $this->config->length, "<--xx_SPLIT_xx-->"));
    foreach($text_split AS $line){
      array_push($output,$line);
    }
    return $output;
  }

  public function output(){
    $rows_per_page = $this->config->rows_page;
    $line = str_pad('-',$this->config->length,'-');
    $space = [];
    for($i = 0; $i < $this->config->page_margin; $i++){
      array_push($space, str_pad(' ',$this->config->length,' '));
    }

    $header = $this->component->header;
    $table_header = [];
    array_push($table_header,$line);
    $heading = [];
    foreach($this->config->table_heading AS $column_index => $title){
      $length = $column_index > 0 ? $this->config->table_column_length[$column_index] - 1: $this->config->table_column_length[$column_index];
      $align 	= in_array($column_index,array(0,2,3,4,8)) ? 'right':'left';
      $heading[$column_index] = $this->text_align($title, $length, $align);
    }
    array_push($table_header, implode(" ",$heading));
    array_push($table_header, $line);
    $body 			= $this->component->body;
    $footer 		= $this->component->footer;

    $total_rows_header 			= count($header);
    $total_rows_table_header 	= count($table_header);
    $total_rows_body 			= count($body);
    $total_rows_footer 			= count($footer);

    $is_multiple_pages = ($this->config->rows_page >= ($total_rows_header + $total_rows_body + $total_rows_footer + count($space)));

    $pages = [];
    $page = 1;
    $last_page = 1;
    $row_number = 0;
    $is_header = TRUE;
    $item_number = 0;
    foreach($body AS $row){
      isset($pages[$page]) ?: $pages[$page] = [];
      if($row_number == 0 ){
        if($page == 1){
          $pages[$page] = array_merge($pages[$page], $header, $table_header);
          $row_number = $total_rows_header + $total_rows_table_header;
        } else if($page <> $last_page){
          $pages[$page] = array_merge($pages[$page], $table_header);
          $row_number += $total_rows_table_header;
        }
      }
      array_push($pages[$page], $row);
      $item_number++;
      $row_number++;
      if( $row_number >= ($this->config->rows_page - $total_rows_footer - 1 -  $this->config->page_margin) ){
        array_push($pages[$page], $line);
        $row_number = 0;
        $last_page = $page;
        $page++;
      }
    }
    if(isset($pages[$page])){
      array_push($pages[$page], $line);
    }

    $total_page = count($pages);
    $output = [];
    foreach($pages AS $key => $lines){
      if(($key) == $total_page){
        $page_content = array_merge($lines,$footer);
      } else {
        $footline = [];
        for($i = 0; $i < ($total_rows_footer -1); $i++){
          array_push($footline, str_pad(' ',$this->config->length,' '));
        }
        $page_content = array_merge($lines,$footline);
      }
      $pagination = str_pad('Halaman ke ' . $key . ' dari ' . $total_page,$this->config->length,' ', STR_PAD_LEFT);
      $output = array_merge($output,$page_content,[$pagination],$space);
    }
    // print_r($output);die;
    $kekurangan = ($this->config->rows_page - count($output));
    for($i = 0; $i < $kekurangan; $i++){
      array_push($output, str_pad(' ',$this->config->length,' '));
    }
    return implode("\n",$output);
  }


}
