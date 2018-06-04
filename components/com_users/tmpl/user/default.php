<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<h2> User profile </h2>
	<div>
		Name: <?php echo $this->item->name; ?>
	</div>
	<div>
		Username: <?php echo $this->item->username; ?>
	</div>
	<div>
		Email: <?php echo $this->item->email; ?>
	</div>

	<div>
		<b>Custom fields:</b>
	</div>
	<?php echo $this->item->event->beforeDisplayContent; ?>
