<?php
/**
 * BscImageColumn class file.
 * @author BSCheshir <BSCheshir@gmail.com>
 * @copyright Copyright &copy; BSCheshir 2014-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * CPasswordHelper provides a simple traversable action.
 *
 * 
 */

class BscFor
{
	/**
	 * Get index of first next/prev nonempty element.
	 * @param array $data the data array
	 * @param int $current start index of element for check
	 * @param int $bound last possible element
	 * @param bool $reverse=false ask (false) / desc (true) direction
	 */
	public static function skipEmpty($data,$current,$bound,$reverse=false)
	{
		if($reverse)
			while(empty($data[$current])&&$current>$bound)
				$current--;
		else
			while(empty($data[$current])&&$current<$bound)
				$current++;
		return $current;
	}
}
