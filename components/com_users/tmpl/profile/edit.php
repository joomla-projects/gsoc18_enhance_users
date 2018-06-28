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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

HTMLHelper::_('behavior.tabstate');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');

$this->tab_name = 'com-users-form';

// Load user_profile plugin language
$lang = Factory::getLanguage();
$lang->load('plg_user_profile', JPATH_ADMINISTRATOR);

?>
<div class="com-users-profile__edit profile-edit">
	<?php if ($this->params->get('show_page_heading')) : ?>
		<div class="page-header">
			<h1>
				<?php echo $this->escape($this->params->get('page_heading')); ?>
			</h1>
		</div>
	<?php endif; ?>

	<script type="text/javascript">
		Joomla.twoFactorMethodChange = function(e)
		{
			var selectedPane = 'com_users_twofactor_' + jQuery('#jform_twofactor_method').val();

			jQuery.each(jQuery('#com_users_twofactor_forms_container>div'), function(i, el)
			{
				if (el.id != selectedPane)
				{
					jQuery('#' + el.id).hide(0);
				}
				else
				{
					jQuery('#' + el.id).show(0);
				}
			});
		}
	</script>

	<form id="member-profile" action="<?php echo Route::_('index.php?option=com_users&task=profile.save'); ?>" method="post" class="com-users-profile__edit-form form-validate form-horizontal well" enctype="multipart/form-data">
			<?php echo HTMLHelper::_('bootstrap.startTabSet', $this->tab_name, array('active' => 'core')); ?>
				<?php // Iterate through the form fieldsets and display each one. ?>
				<?php foreach ($this->form->getFieldsets() as $group => $fieldset) : ?>
					<?php $fields = $this->form->getFieldset($group); ?>
					<?php if (count($fields)) : ?>
						<?php echo HTMLHelper::_('bootstrap.addTab', $this->tab_name, $group, Text::_($fieldset->label)); ?>
							<fieldset>
								<?php if (isset($fieldset->description) && trim($fieldset->description)) : ?>
									<p>
										<?php echo $this->escape(Text::_($fieldset->description)); ?>
									</p>
								<?php endif; ?>
								<?php // Iterate through the fields in the set and display them. ?>
								<?php foreach ($fields as $field) : ?>
								<?php // If the field is hidden, just display the input. ?>
									<?php if ($field->hidden) : ?>
										<?php echo $field->input; ?>
									<?php else : ?>
										<div class="control-group">
											<div class="control-label">
												<?php echo $field->label; ?>
												<?php if (!$field->required && $field->type !== 'Spacer') : ?>
													<span class="optional">
														<?php echo Text::_('COM_USERS_OPTIONAL'); ?>
													</span>
												<?php endif; ?>
											</div>
											<div class="controls">
												<?php echo $field->input; ?>
											</div>
										</div>
									<?php endif; ?>
								<?php endforeach; ?>
							</fieldset>
						<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
					<?php endif; ?>
				<?php endforeach; ?>

				<?php if (count($this->twofactormethods) > 1) : ?>
					<?php echo HTMLHelper::_('bootstrap.addTab', $this->tab_name, 'twofactor', Text::_('COM_USERS_PROFILE_TWO_FACTOR_AUTH')); ?>
						<fieldset class="com-users-profile__twofactor">
							<div class="com-users-profile__twofactor-method control-group">
								<div class="control-label">
									<label id="jform_twofactor_method-lbl" for="jform_twofactor_method" class="hasTooltip"
										   title="<?php echo '<strong>' . Text::_('COM_USERS_PROFILE_TWOFACTOR_LABEL') . '</strong><br>' . Text::_('COM_USERS_PROFILE_TWOFACTOR_DESC'); ?>">
										<?php echo Text::_('COM_USERS_PROFILE_TWOFACTOR_LABEL'); ?>
									</label>
								</div>
								<div class="controls">
									<?php echo HTMLHelper::_('select.genericlist', $this->twofactormethods, 'jform[twofactor][method]', array('onchange' => 'Joomla.twoFactorMethodChange()'), 'value', 'text', $this->otpConfig->method, 'jform_twofactor_method', false); ?>
								</div>
							</div>
							<div id="com_users_twofactor_forms_container" class="com-users-profile__twofactor-form">
								<?php foreach ($this->twofactorform as $form) : ?>
									<?php $style = $form['method'] == $this->otpConfig->method ? 'display: block' : 'display: none'; ?>
									<div id="com_users_twofactor_<?php echo $form['method']; ?>" style="<?php echo $style; ?>">
										<?php echo $form['form']; ?>
									</div>
								<?php endforeach; ?>
							</div>
						</fieldset>
					<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

					<?php echo HTMLHelper::_('bootstrap.addTab', $this->tab_name, 'onetimepass', Text::_('COM_USERS_PROFILE_OTEPS')); ?>
						<fieldset class="com-users-profile__oteps">
							<joomla-alert type="info"><?php echo Text::_('COM_USERS_PROFILE_OTEPS_DESC'); ?></joomla-alert>
							<?php if (empty($this->otpConfig->otep)) : ?>
								<joomla-alert type="warning"><?php echo Text::_('COM_USERS_PROFILE_OTEPS_WAIT_DESC'); ?></joomla-alert>
							<?php else : ?>
								<?php foreach ($this->otpConfig->otep as $otep) : ?>
									<span class="col-md-3">
										<?php echo substr($otep, 0, 4); ?>-<?php echo substr($otep, 4, 4); ?>-<?php echo substr($otep, 8, 4); ?>-<?php echo substr($otep, 12, 4); ?>
									</span>
								<?php endforeach; ?>
								<div class="clearfix"></div>
							<?php endif; ?>
						</fieldset>
					<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
				<?php endif; ?>
			<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

		<div class="com-users-profile__edit-submit control-group mt-2">
			<button type="submit" class="btn btn-primary validate">
				<span class="fa fa-check" aria-hidden="true"></span>
				<?php echo Text::_('JSAVE'); ?>
			</button>
			<a class="btn btn-danger" href="index.php?option=com_users&view=profile" title="<?php echo Text::_('JCANCEL'); ?>">
				<?php echo Text::_('JCANCEL'); ?>
			</a>
			<input type="hidden" name="option" value="com_users">
			<input type="hidden" name="task" value="profile.save">
		</div>
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
