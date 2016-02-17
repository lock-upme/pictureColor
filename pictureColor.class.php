<?php
/**
 * 取得图片色系名称
 * 参考地址
 * http://www.cnblogs.com/codingspace/archive/2010/04/09/1707900.html
 * http://www.workwithcolor.com/color-converter-01.htm
 * http://www.easyrgb.com/index.php?X=MATH&H=18#text18
 *
 * 用法
 * $obj = new pictureColor();
 * echo $obj->colorName('E:\project\lumen\public\t.jpg');
 * echo $obj->hexName('E:\project\lumen\public\t.jpg');
 *
 * @author lock
 * @link https://github.com/lock-upme
 */



class pictureColor
{
	/**
	 * GM Lib
	 */
	public $gm = 'C:\Progra~1\GraphicsMagick-1.3.22-Q8\gm.exe';
	
	/**
	 * 获取颜色使用库类型
	 * gd or gm
	 */
	public $type = 'gd';
	
	/**
	 * 十六进制
	 */
	public $hex = array ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
	
	/**
	 * 获得图片色系
	 *
	 * @param string $file
	 * @return string
	 */
	public function colorName($file) {
		if (empty($file)) { return false; }		
		$rgb = $this->getRGB($file, $this->type);
		$hsl = $this->RGB2HSL($rgb);		
		return $this->getColorName($hsl);
	}
	
	/**
	 * 取得图片十六进制
	 *
	 * @param string $file
	 * @return string
	 */
	public function hexName($file) {
		if (empty($file)) { return false; }		
		$rgb = $this->getRGB($file, $this->type);
		return $this->RGB2Hex($rgb);
	}
	
	/**
	 * 取得图片RGB
	 *
	 * @param string $file
	 * @param string $type gd/gm
	 * @return array
	 */
	public function getRGB($file, $type='gd') {
		if (empty($file)) { return false; }
		
		if ($type == 'gd') {
			$filext = trim(strtolower(strrchr($file, '.')),'.');
			if ($filext == 'jpg' ||  $filext == 'jpeg') {
				$img = ImageCreateFromJpeg($file);
			} elseif ($filext == 'png') {
				$img = imagecreatefrompng($file);
			} elseif ($filext == 'bmp') {
				$img = imagecreatefromwbmp($file);
			} elseif ($filext == 'gif') {
				$img = imagecreatefromgif($file);
			}
			$w = imagesx($img);
			$h = imagesy($img);
			$r = $g = $b = 0;
			for($y = 0; $y < $h; $y++) {
				for($x = 0; $x < $w; $x++) {
					$rgb = imagecolorat($img, $x, $y);
					$r += $rgb >> 16;
					$g += $rgb >> 8 & 255;
					$b += $rgb & 255;
				}
			}
			$pxls = $w * $h;
			
			$r = (round($r / $pxls));
			$g = (round($g / $pxls));
			$b = (round($b / $pxls));
			/*
			$r = dechex (round($r / $pxls));
			$g = dechex (round($g / $pxls));
			$b = dechex (round($b / $pxls));
			return $r.$g.$b;
			 */
			return array( '0' => $r, '1' => $g, '2' => $b );
			
		} elseif ($type == 'gm') {	
			//$cmd = $this->gm. " identify -verbose $file | grep Mean | awk -F' ' '{print $3}' | tr -d '()'";
			$cmd = $this->gm . " identify -verbose $file";
			$res = shell_exec($cmd);
			//print_r($res);
			
			preg_match_all('/Mean:\s+[0-9]+\.[0-9]+\s\((.*)\)/', $res, $match);
			//print_r($match);
			$rgb = $match[1];
			
			if (count($rgb) != 3) { //workaround{TODO:to be fixed}
				$rgb['2'] = $rgb['1'] = $rgb['0'];
			}
			while (list($key, $val) = each($rgb)) {
				$rgb[$key] = round($val * 255, 2);
			}
			return $rgb;
		}	
	}
	
	public function RGB2Hex($rgb) {
		$hexColor = '';
		$hex = $this->hex;
		for($i = 0; $i < 3; $i ++) {
			$r = null;
			$c = $rgb [$i];
			$hexAr = array ();
	
			while ( $c > 16 ) {
				$r = $c % 16;
				$c = ($c / 16) >> 0;
				array_push ( $hexAr, $hex [$r] );
			}
			array_push ( $hexAr, $hex [$c] );
	
			$ret = array_reverse ( $hexAr );
			$item = implode ( '', $ret );
			$item = str_pad ( $item, 2, '0', STR_PAD_LEFT );
			$hexColor .= $item;
		}
		return $hexColor;
	}
	
	/**
	 * RGB转HSL
	 *
	 * @param array $rgb
	 * @return array
	 */
	public function RGB2HSL($rgb) {
		list($r, $g, $b) = $rgb;
		$r /= 255;
		$g /= 255;
		$b /= 255;
		$max = max($r, $g, $b);
		$min = min($r, $g, $b);
		$delta = $max - $min;
		$l = ($max + $min) / 2;
	
		if ($delta == 0) {
			$h = 0;
			$s = 0;
		} else {
			$s = ($l < 0.5) ? $delta / ($max + $min) : $delta / (2 - $max - $min);
	
			$deltar = ((($max - $r) / 6) + ($max / 2)) / $delta;
			$deltag = ((($max - $g) / 6) + ($max / 2)) / $delta;
			$deltab = ((($max - $b) / 6) + ($max / 2)) / $delta;
	
			if ($r == $max) {
				$h = $deltab - $deltag;
			} else if ($g == $max) {
				$h = (1 / 3) + $deltar - $deltab;
			} else if ($b == $max) {
				$h = (2 / 3) + $deltag - $deltar;
			}
			$h += ($h < 0) ? 1 : ($h > 1 ? -1 : 0);
		}
		return array($h * 360, $s * 100, $l * 100);
	}
	
	/**
	 * HSL对应颜色名称
	 *
	 * @param array $hsl
	 * @return string
	 */
	public function getColorName($hsl) {
		$colorarr = array(
				'0, 100, 50' => '红色',
				'30, 100, 50' => '橙色',
				'60, 100, 50' => '黄色',
				'120, 100, 75' => '绿色',
				'240, 100, 25' => '蓝色',
				'300, 100, 25' => '紫色',
				'255, 152, 191' => '粉红',
				//'136, 84, 24' => '棕色',
				'0, 0, 50' => '灰色',
				'0, 0, 0' => '黑色',
				'0, 0, 100' => '白色',
		);
		$distarr = array();
		foreach ($colorarr as $key => $val) {
			list($h, $s, $l) = explode(',', $key);
			$distarr[$key] = pow(($hsl['0'] - $h), 2) + pow(($hsl['1'] - $s), 2) + pow(($hsl['2'] - $l), 2);
		}
		asort($distarr);
		list($key) = each($distarr);
		return $colorarr[$key];
	}
	
}