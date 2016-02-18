<?php

namespace app\components;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MyString
 *
 * @author Rock.Lei
 */
class MyString {
    
    /**
     * 格式化字符串
     * @param type $str
     * @param type $style   1:p 2:li
     * @return string
     */
    public static function addPTag($str,$style=1){
        $temp_str = explode("\n",$str);
        $str_html_value='';
        foreach ($temp_str as $value) {
            switch ($style)
            {
                case 1:
                     $str_html_value .= '<p>'.$value.'</p>';
                     break;
                case 2:
                     $str_html_value .= '<li>'.$value.'</li>';
                     break;
            }
           
        }
        return $str_html_value;
    }
        /**
     * 自动补全Html标签
     * @param html $string 需补全字符串
     * @return string 返回字符串
     */
    public static function closetags($html) {
        // 不需要补全的标签
        $arr_single_tags = array('meta', 'img', 'br', 'link', 'area');
        // 匹配开始标签
        preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
        $openedtags = $result[1];
        // 匹配关闭标签
        preg_match_all('#</([a-z]+)>#iU', $html, $result);
        $closedtags = $result[1];
        // 计算关闭开启标签数量，如果相同就返回html数据
        $len_opened = count($openedtags);
        if (count($closedtags) == $len_opened) {
            return $html;
        }
        // 把排序数组，将最后一个开启的标签放在最前面
        $openedtags = array_reverse($openedtags);
        // 遍历开启标签数组
        for ($i = 0; $i < $len_opened; $i++) {
            // 如果需要补全的标签
            if (!in_array($openedtags[$i], $arr_single_tags)) {
                // 如果这个标签不在关闭的标签中
                if (!in_array($openedtags[$i], $closedtags)) {
                    // 直接补全闭合标签
                    $html .= '</' . $openedtags[$i] . '>';
                } else {
                    unset($closedtags[array_search($openedtags[$i], $closedtags)]);
                }
            }
        }
        return $html;
    }
    
    /**
        * 取HTML,并自动补全闭合
        *
        * param $html
        *
        * param $length
        *
        * param $end
        */
        public static function subHtml($html, $length=500) {
            $result = '';
            $tagStack = array();
            $len = 0;
            $contents = preg_split("~(<[^>]+?>)~si", $html, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            foreach($contents as $tag) 
            {
                if (trim($tag) == ""){ continue;}
                if (preg_match("~<([a-z0-9]+)[^/>]*?/>~si", $tag)) {
                    $result .= $tag;
                } else if (preg_match("~</([a-z0-9]+)[^/>]*?>~si", $tag, $match)) 
                {
                    if ($tagStack[count($tagStack)-1] == $match[1]) {
                        array_pop($tagStack);
                        $result .= $tag;
                    }
                } else if (preg_match("~<([a-z0-9]+)[^/>]*?>~si", $tag, $match)) {
                    array_push($tagStack, $match[1]);
                    $result .= $tag;
                } else if (preg_match("~<!--.*?-->~si", $tag)) {
                    $result .= $tag;
                } else {
                    if ($len + self::mstrlen($tag) < $length) {
                        $result .= $tag;
                        $len += self::mstrlen($tag);
                    } else {
                        $str = self::msubstr($tag, 0, $length - $len + 1);
                        $result .= $str;
                        break;
                    }
                }
            } while (!empty($tagStack)) {
            $result .= '</' . array_pop($tagStack) . '>';
            }
            return $result;
        }
        /**
        * 取中文字符串
        *
        * param $string 字符串
        *
        * param $start 起始位
        *
        * param $length 长度
        *
        * param $charset 编码
        *
        * param $dot 附加字串
        */
        public static function msubstr($string, $start, $length, $dot = '', $charset = 'UTF-8') {
            $string = str_replace(array('&', '"', '<', '>', ' '), array('&', '"', '<', '>', ' '), $string);
            if (strlen($string) <= $length) {
                return $string;
            }
            if (strtolower($charset) == 'utf-8') 
            {
                $n = $tn = $noc = 0;
                    while ($n < strlen($string)) {
                    $t = ord($string[$n]);
                    if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                        $tn = 1;
                        $n++;
                    } elseif (194 <= $t && $t <= 223) {
                        $tn = 2;
                        $n += 2;
                    } elseif (224 <= $t && $t <= 239) {
                        $tn = 3;
                        $n += 3;
                    } elseif (240 <= $t && $t <= 247) {
                        $tn = 4;
                        $n += 4;
                    } elseif (248 <= $t && $t <= 251) {
                        $tn = 5;
                        $n += 5;
                    } elseif ($t == 252 || $t == 253) {
                        $tn = 6;
                        $n += 6;
                    } else {
                        $n++;
                    }
                    $noc++;
                    if ($noc >= $length) {
                        break;
                    }
                }
                if ($noc > $length) {
                    $n -= $tn;
                }
                $strcut = substr($string, 0, $n);
            } else {
                for($i = 0; $i < $length; $i++) {
                    $strcut .= ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];
                }
            }
            return $strcut . $dot;
        }
        /**
        * 得字符串的长度，包括中英文。
        */
        public static function mstrlen($str, $charset = 'UTF-8') {
            if (function_exists('mb_substr')) 
            {
                $length = mb_strlen($str, $charset);
            } elseif (function_exists('iconv_substr')) 
            {
                $length = iconv_strlen($str, $charset);
            } else 
            {
                preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-f][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $text, $ar);
                $length = count($ar[0]);
            }
            return $length;
        } 
        
        /**
         * 解析文本，把链接，email转为可点击链接
         * @param type $text
         * @return type
         */
        public static function makeClickableLinks($text) {
            $text = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_+.~#?&//=]+)', '<a href="\1">\1</a>', $text);
            $text = eregi_replace('([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_+.~#?&//=]+)','\1<a href="http://\2">\2</a>', $text);
            $text = eregi_replace('([_.0-9a-z-]+@([0-9a-z][0-9a-z-]+.)+[a-z]{2,3})', '<a href="mailto:\1">\1</a>', $text);
            return $text;
        } 
        
        public static function validateMobile($mobilephone){
            $myreg = '/^1[3|4|5|7|8][0-9]\d{8}$/';
            if(preg_match($myreg, $mobilephone)){
                return true;
            }else{
                return false;
            }
        }
        public static function idcard_verify_number($idcard_base){
            if (strlen($idcard_base) != 17){ return false; }
            // 加权因子
            $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);

            // 校验码对应值
            $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');

            $checksum = 0;
            for ($i = 0; $i < strlen($idcard_base); $i++){
              $checksum += substr($idcard_base, $i, 1) * $factor[$i];
            }

            $mod = $checksum % 11;
            $verify_number = $verify_number_list[$mod];

            return $verify_number;
        }

    // 将15位身份证升级到18位
    public static function idcard_15to18($idcard){
      if (strlen($idcard) != 15){
        return false;
      }else{
        // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
        if (array_search(substr($idcard, 12, 3), array('996', '997', '998', '999')) !== false){
          $idcard = substr($idcard, 0, 6) . '18'. substr($idcard, 6, 9);
        }else{
          $idcard = substr($idcard, 0, 6) . '19'. substr($idcard, 6, 9);
        }
      }

      $idcard = $idcard . self::idcard_verify_number($idcard);

      return $idcard;
    }

    // 18位身份证校验码有效性检查
    public static function idcard_checksum18($idcard){
      if (strlen($idcard) != 18){ return false; }
      $idcard_base = substr($idcard, 0, 17);
      if (self::idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))){
        return false;
      }else{
        return true;
      }
    }
    public static function validateDate($date, $format='YYYYMMDD')
    {
        switch( $format )
        {
            case 'YYYY/MM/DD':
            case 'YYYY-MM-DD':
            list( $y, $m, $d ) = preg_split( '/[-./ ]/', $date );
            break;

            case 'YYYY/DD/MM':
            case 'YYYY-DD-MM':
            list( $y, $d, $m ) = preg_split( '/[-./ ]/', $date );
            break;

            case 'DD-MM-YYYY':
            case 'DD/MM/YYYY':
            list( $d, $m, $y ) = preg_split( '/[-./ ]/', $date );
            break;

            case 'MM-DD-YYYY':
            case 'MM/DD/YYYY':
            list( $m, $d, $y ) = preg_split( '/[-./ ]/', $date );
            break;

            case 'YYYYMMDD':
            $y = substr( $date, 0, 4 );
            $m = substr( $date, 4, 2 );
            $d = substr( $date, 6, 2 );
            break;

            case 'YYYYDDMM':
            $y = substr( $date, 0, 4 );
            $d = substr( $date, 4, 2 );
            $m = substr( $date, 6, 2 );
            break;

            default:
            throw new Exception( "Invalid Date Format" );
        }
        return checkdate( $m, $d, $y );
    }

    public static function checkInt($var)
    {
        $int_value = 0;
        if (is_numeric($var)) {
             $int_value = intval($var);
        }

        return $int_value;
    }
    public static  function characet($data){
        if( !empty($data) ){   
          $fileType = mb_detect_encoding($data , array('UTF-8')) ;  
          if( $fileType != 'UTF-8'){  
            $data = mb_convert_encoding($data ,'utf-8' , 'gbk');  
          }  
        }  
        return $data;   
      }

    public static  function htmlPreg($str){
        $str = preg_replace( "@<ul>@is", "", $str );
        $str = preg_replace( "@<ol>@is", "", $str );
        $str = preg_replace( "@<li>@is", "", $str ); 
        $str = preg_replace( "@</ul>@is", "", $str );
        $str = preg_replace( "@</ol>@is", "", $str );
        $str = preg_replace( "@</li>@is", "", $str ); 
        
        return $str;   
      }
      
    /**
     * 抓取方法的获取
     * @param $durl url
     *  @return string 返回字符串
     */
    public static function curl_file_get_contents($durl,$time_out=10){           
       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, $durl);
       curl_setopt($ch, CURLOPT_TIMEOUT, $time_out);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
       $r = curl_exec($ch);
       $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      // curl_close($ch);
       if ($httpCode == 404) {
            $r = '';
        }
       return $r;
     }
        public static function my_print_r($val){
            echo '<pre>';
            print_r($val);
            echo '</pre>';
        }
}
