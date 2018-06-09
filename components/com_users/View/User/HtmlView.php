<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Users\Site\View\User;

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * Profile view class for Users.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The article object
	 *
	 * @var  \stdClass
	 */
	protected $item;

	/**
	 * The page parameters
	 *
	 * @var    \Joomla\Registry\Registry|null
	 * @since  4.0.0
	 */
	protected $params = null;

	/**
	 * The model state
	 *
	 * @var  \JObject
	 */
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed   A string if successful, otherwise an Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$app        = \JFactory::getApplication();
		$this->item  = $this->get('Item');
		$this->state      = $this->get('State');

		// Process the content plugins.
		PluginHelper::importPlugin('content');
		$offset = $this->state->get('list.offset');

		// Store the events for later
		$this->item->event = new \stdClass;

		$results = $app->triggerEvent('onContentAfterTitle', array('com_users.user', &$this->item, &$this->item->params, $offset));
		$this->item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $app->triggerEvent('onContentBeforeDisplay', array('com_users.user', &$this->item, &$this->item->params, $offset));
		$this->item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $app->triggerEvent('onContentAfterDisplay', array('com_users.user', &$this->item, &$this->item->params, $offset));
		$this->item->event->afterDisplayContent = trim(implode("\n", $results));

		return parent::display($tpl);
	}
}
