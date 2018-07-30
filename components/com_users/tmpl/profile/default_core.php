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
use Joomla\CMS\Language\Text;

?>
<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'core', Text::_('COM_USERS_PROFILE_CORE_LEGEND')); ?>
	<fieldset id="com-users-profile__core users-profile-core">
		<dl class="dl-horizontal">
			<dt>
				<?php echo Text::_('COM_USERS_PROFILE_NAME_LABEL'); ?>
			</dt>
			<dd class="mb-2">
				<?php echo $this->data->name; ?>
			</dd>
			<dt>
				<?php echo Text::_('COM_USERS_PROFILE_USERNAME_LABEL'); ?>
			</dt>
			<dd class="mb-2">
				<?php echo htmlspecialchars($this->data->username, ENT_COMPAT, 'UTF-8'); ?>
			</dd>
			<dt>
				<?php echo Text::_('COM_USERS_PROFILE_REGISTERED_DATE_LABEL'); ?>
			</dt>
			<dd class="mb-2">
				<?php echo HTMLHelper::_('date', $this->data->registerDate); ?>
			</dd>
			<dt>
				<?php echo Text::_('COM_USERS_PROFILE_LAST_VISITED_DATE_LABEL'); ?>
			</dt>
			<?php if ($this->data->lastvisitDate != $this->db->getNullDate()) : ?>
				<dd>
					<?php echo HTMLHelper::_('date', $this->data->lastvisitDate); ?>
				</dd>
			<?php else : ?>
				<dd>
					<?php echo Text::_('COM_USERS_PROFILE_NEVER_VISITED'); ?>
				</dd>
			<?php endif; ?>
		</dl>
	</fieldset>
<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
