<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Users\Site\View\Users;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Users\Administrator\Helper\UsersHelper;

/**
 * Profile view class for Users.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The model state
	 *
	 * @var  \JObject
	 */
	protected $state;

	/**
	 * @var array
	 */
	protected $items;

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
		$this->items  = $this->get('Items');

		return parent::display($tpl);
	}

}
