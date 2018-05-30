<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;


HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers');

$app = JFactory::getApplication();
?>

hhahahahaha

<div class="">

<?php
//var_dump($this->items);
foreach ($this->items as $item) : ?>
	<div >
			<?php
			echo $item->name;
			?>

	</div>
<?php endforeach; ?>
</div>

