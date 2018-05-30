<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Users\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Profile model class for Users.
 *
 * @since  1.6
 */
class UsersModel extends ListModel
{

	/**
	 * @return \JDatabaseQuery|\Joomla\Database\DatabaseQuery
	 */
	protected function getListQuery()
	{
		// Get the current user for authorisation checks
		$user = \JFactory::getUser();

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				' a.id, a.name, a.username, a.email, a.access'
			)
		);

		$query->from('#__users AS a');

		// Filter by access level.
		if ($this->getState('filter.access', true))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}

		return $query;
	}
	
}
