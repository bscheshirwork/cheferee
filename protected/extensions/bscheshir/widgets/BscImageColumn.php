<?php
/**
 * BscImageColumn class file.
 * @author BSCheshir <BSCheshir@gmail.com>
 * @copyright Copyright &copy; BSCheshir 2014-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrape.widgets
 */

Yii::import('zii.widgets.grid.CGridColumn');

/**
 * Image grid data column.
 */
class BscImageColumn extends CGridColumn
{
	/**
	 * @var string the static URL to the image. If this is set, an image will be rendered.
	 * if you need dynamic URL see urlExpression
	 */
	public $imageUrl;
	/**
	 * @var string a PHP expression that will be evaluated for every data cell and whose result will be rendered
	 * as the URL to the image of the data cells.
	 * In this expression, you can use the following variables:
	 * <ul>
	 *   <li><code>$row</code> the row number (zero-based).</li>
	 *   <li><code>$data</code> the data model for the row.</li>
	 * 	 <li><code>$this</code> the column object.</li>
	 * </ul>
	 * The PHP expression will be evaluated using {@link evaluateExpression}.
	 *
	 * A PHP expression can be any PHP code that has a value. To learn more about what an expression is,
	 * please refer to the {@link http://www.php.net/manual/en/language.expressions.php php manual}.
	 */
	public $urlExpression;
	/**
	 * @var array the HTML options for the image
	 */
	public $imageHtmlOptions=array('class'=>'image-in-column');	
	/**
	 * @var string the label to the hyperlinks in the data cells. Note that the label will not
	 * be HTML-encoded when rendering. This property is ignored if {@link labelExpression} is set.
	 * @see labelExpression
	 */
	public $label='';
	/**
	 * @var string a PHP expression that will be evaluated for every data cell and whose result will be rendered
	 * as the label of the image of the data cell.
	 * In this expression, you can use the following variables:
	 * <ul>
	 *   <li><code>$row</code> the row number (zero-based).</li>
	 *   <li><code>$data</code> the data model for the row.</li>
	 * 	 <li><code>$this</code> the column object.</li>
	 * </ul>
	 * The PHP expression will be evaluated using {@link evaluateExpression}.
	 *
	 * A PHP expression can be any PHP code that has a value. To learn more about what an expression is,
	 * please refer to the {@link http://www.php.net/manual/en/language.expressions.php php manual}.
	 */
	public $labelExpression;
	/**
	 * Renders the data cell content.
	 * This method evaluates {@link value} or {@link name} and renders the result.
	 * @param integer $row the row number (zero-based)
	 * @param mixed $data the data associated with the row
	 */
	protected function renderDataCellContent($row,$data)
	{
		if($this->urlExpression!==null)
			$imageUrl=$this->evaluateExpression($this->urlExpression,array('data'=>$data,'row'=>$row));
		else
			$imageUrl=$this->imageUrl;
		if($this->labelExpression!==null)
			$label=$this->evaluateExpression($this->labelExpression,array('data'=>$data,'row'=>$row));
		else
			$label=$this->label;
		$options=$this->imageHtmlOptions;
		echo $imageUrl===null ? $this->grid->nullDisplay : CHtml::image($imageUrl,$label,$options);
	}
}
