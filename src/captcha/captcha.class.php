<?php

class Revship_Captcha
{
	/**
	 * The name of the GET parameter indicating whether the CAPTCHA image should be regenerated.
	 */
	const REFRESH_GET_VAR='refresh';
	/**
	 * Prefix to the session variable name used by the action.
	 */
	const SESSION_KEY='Revship.Captcha';
	/**
	 * @var integer how many times should the same CAPTCHA be displayed. Defaults to 3.
	 * A value less than or equal to 0 means the test is unlimited (available since version 1.1.2).
	 */
	public $testLimit = 20;
	/**
	 * @var integer the width of the generated CAPTCHA image. Defaults to 120.
	 */
	public $width = 120;
	/**
	 * @var integer the height of the generated CAPTCHA image. Defaults to 50.
	 */
	public $height = 50;
	/**
	 * @var integer padding around the text. Defaults to 2.
	 */
	public $padding = 2;
	/**
	 * @var integer the background color. For example, 0x55FF00.
	 * Defaults to 0xFFFFFF, meaning white color.
	 */
	public $backColor = 0xFFFFFF;
	/**
	 * @var integer the font color. For example, 0x55FF00. Defaults to 0x2040A0 (blue color).
	 */
	public $foreColor = 0x2040A0;
	/**
	 * @var boolean whether to use transparent background. Defaults to false.
	 * @since 1.0.10
	 */
	public $transparent = false;
	/**
	 * @var integer the minimum length for randomly generated word. Defaults to 6.
	 */
	public $minLength = 6;
	/**
	 * @var integer the maximum length for randomly generated word. Defaults to 7.
	 */
	public $maxLength = 7;
	/**
	 * @var string the TrueType font file. Defaults to Duality.ttf which is provided
	 * with the Yii release.
	 */
	public $fontFile;
	/**
	 * @var string the fixed verification code. When this is property is set,
	 * {@link getVerifyCode} will always return this value.
	 * This is mainly used in automated tests where we want to be able to reproduce
	 * the same verification code each time we run the tests.
	 * Defaults to null, meaning the verification code will be randomly generated.
	 * @since 1.1.4
	 */
	public $fixedVerifyCode;

	/**
	 * Produce new Image, and refresh code in session
	 * 
	 */
	public function getRefreshImage()
	{
			$this->renderImage($this->getVerifyCode(true));
			Revship::end();
	}
	
	/**
	 * Validates the input to see if it matches the generated code.
	 * @param string $input user input
	 * @param boolean $caseSensitive whether the comparison should be case-sensitive
	 * @return Array
	 * (
	 * 		whether the input is valid? //bool
	 * 		need refresh? //bool
	 * )
	 */
	public function validate($input,$caseSensitive = false)
	{
		$code = $this->getVerifyCode();
		$valid = $caseSensitive ? ($input === $code) : !strcasecmp($input,$code);
		$needRefreshImage = false;
		$name = $this->getSessionKey() . 'count';
		$session = Revship::session();
		$session->set($name, $session->get($name)+1 );
		if($session->get($name) > $this->testLimit && $this->testLimit > 0)
		{
			$this->getVerifyCode(true);
			$needRefreshImage = true;
			$valid = false;
		}
		return array($valid, $needRefreshImage);
	}
	public function validateAndReset($input,$caseSensitive = false)
	{
		$code = $this->getVerifyCode();
		$valid = $caseSensitive ? ($input === $code) : !strcasecmp($input,$code);
		$this->getVerifyCode(true);
		return $valid;
	}
	/**
	 * Gets the verification code.
	 * @param string $regenerate whether the verification code should be regenerated.
	 * @return string the verification code.
	 */
	public function getVerifyCode($regenerate=false)
	{
		if($this->fixedVerifyCode !== null)
			return $this->fixedVerifyCode;

		$session = Revship::session();
		$name = $this->getSessionKey();
		if($session->get($name) === false || $regenerate)
		{
			$session->set($name, $this->generateVerifyCode());
			$session->set($name . 'count', 1);
		}
		return $session->get($name);
	}
	/**
	 * Generates a new verification code.
	 * @return string the generated verification code
	 */
	protected function generateVerifyCode()
	{
		if($this->minLength < 3)
			$this->minLength = 3;
		if($this->maxLength > 20)
			$this->maxLength = 20;
		if($this->minLength > $this->maxLength)
			$this->maxLength = $this->minLength;
		$length = mt_rand($this->minLength,$this->maxLength);

		$letters = 'bcdfghjklmnpqrstvwxyz';
		$vowels = 'aeiou';
		$code = '';
		for($i = 0; $i < $length; ++$i)
		{
			if($i % 2 && mt_rand(0,10) > 2 || !($i % 2) && mt_rand(0,10) > 9)
				$code.=$vowels[mt_rand(0,4)];
			else
				$code.=$letters[mt_rand(0,20)];
		}

		return $code;
	}
	
	/**
	 * Renders the CAPTCHA image based on the code.
	 * @param string $code the verification code
	 * @return string image content
	 */
	protected function renderImage($code)
	{
		$image = imagecreatetruecolor($this->width,$this->height);

		$backColor = imagecolorallocate($image,
				(int)($this->backColor % 0x1000000 / 0x10000),
				(int)($this->backColor % 0x10000 / 0x100),
				$this->backColor % 0x100);
		imagefilledrectangle($image,0,0,$this->width,$this->height,$backColor);
		imagecolordeallocate($image,$backColor);

		if($this->transparent)
			imagecolortransparent($image,$backColor);

		$foreColor = imagecolorallocate($image,
				(int)($this->foreColor % 0x1000000 / 0x10000),
				(int)($this->foreColor % 0x10000 / 0x100),
				$this->foreColor % 0x100);

		if($this->fontFile === null)
			$this->fontFile = dirname(__FILE__) . '/fonts/Duality.ttf';

		$offset = 2;
		$length = strlen($code);
		$box = imagettfbbox(30,0,$this->fontFile,$code);
		$w = $box[4] - $box[0] - $offset * ($length - 1);
		$h = $box[1] - $box[5];
		$scale = min(($this->width - $this->padding * 2) / $w,($this->height - $this->padding * 2) / $h);
		$x = 10;
		$y = round($this->height * 27 / 40);
		for($i = 0; $i < $length; ++$i)
		{
			$fontSize = (int)(rand(26,32) * $scale * 0.8);
			$angle = rand(-10,10);
			$letter = $code[$i];
			$box = imagettftext($image,$fontSize,$angle,$x,$y,$foreColor,$this->fontFile,$letter);
			$x = $box[2] - $offset;
		}

		imagecolordeallocate($image,$foreColor);

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Transfer-Encoding: binary');
		header("Content-type: image/png");
		imagepng($image);
		imagedestroy($image);
	}
	/**
	 * Returns the session variable name used to store verification code.
	 * @return string the session variable name
	 */
	protected function getSessionKey()
	{
		return self::SESSION_KEY ;
	}

	/**
	 * Checks if GD with FreeType support is loadded
	 * @return boolean true if GD with FreeType support is loaded, otherwise false
	 * @since 1.1.5
	 */
	public static function checkRequirements()
	{
		if (extension_loaded('gd'))
		{
			$gdinfo=gd_info();
			if( $gdinfo['FreeType Support'])
				return true;
		}
		return false;
	}
	
}
