<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;


HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers');

$app = JFactory::getApplication();
?>

<div>
	<?php foreach ($this->items as $item) : ?>
		<div>
			<a href="<?php echo Route::_('index.php?option=com_users&view=user&id=' . $item->id); ?>">
				<?php
				echo $item->name;
				?>
			</a>
			<p> <?php echo $item->id; ?></p>
		</div>
	<?php endforeach; ?>
</div>

