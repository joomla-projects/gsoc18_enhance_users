<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\LegacyFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceInterface;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseQuery;

/**
 * Base class for a database aware Joomla Model
 *
 * Acts as a Factory class for application specific objects and provides many supporting API functions.
 *
 * @since  2.5.5
 */
abstract class BaseDatabaseModel extends CMSObject
{
	/**
	 * Indicates if the internal state has been set
	 *
	 * @var    boolean
	 * @since  3.0
	 */
	protected $__state_set = null;

	/**
	 * Database Connector
	 *
	 * @var    DatabaseDriver
	 * @since  3.0
	 */
	protected $_db;

	/**
	 * The model (base) name
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $name;

	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $option = null;

	/**
	 * A state object
	 *
	 * @var    \JObject
	 * @since  3.0
	 */
	protected $state;

	/**
	 * The event to trigger when cleaning cache.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $event_clean_cache = null;

	/**
	 * The factory.
	 *
	 * @var    MVCFactoryInterface
	 * @since  4.0.0
	 */
	protected $factory;

	/**
	 * Add a directory where \JModelLegacy should search for models. You may
	 * either pass a string or an array of directories.
	 *
	 * @param   mixed   $path    A path or array[sting] of paths to search.
	 * @param   string  $prefix  A prefix for models.
	 *
	 * @return  array  An array with directory elements. If prefix is equal to '', all directories are returned.
	 *
	 * @since   3.0
	 */
	public static function addIncludePath($path = '', $prefix = '')
	{
		static $paths;

		if (!isset($paths))
		{
			$paths = array();
		}

		if (!isset($paths[$prefix]))
		{
			$paths[$prefix] = array();
		}

		if (!isset($paths['']))
		{
			$paths[''] = array();
		}

		if (!empty($path))
		{
			jimport('joomla.filesystem.path');

			foreach ((array) $path as $includePath)
			{
				if (!in_array($includePath, $paths[$prefix]))
				{
					array_unshift($paths[$prefix], \JPath::clean($includePath));
				}

				if (!in_array($includePath, $paths['']))
				{
					array_unshift($paths[''], \JPath::clean($includePath));
				}
			}
		}

		return $paths[$prefix];
	}

	/**
	 * Adds to the stack of model table paths in LIFO order.
	 *
	 * @param   mixed  $path  The directory as a string or directories as an array to add.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function addTablePath($path)
	{
		\JTable::addIncludePath($path);
	}

	/**
	 * Create the filename for a resource
	 *
	 * @param   string  $type   The resource type to create the filename for.
	 * @param   array   $parts  An associative array of filename information.
	 *
	 * @return  string  The filename
	 *
	 * @since   3.0
	 */
	protected static function _createFileName($type, $parts = array())
	{
		$filename = '';

		switch ($type)
		{
			case 'model':
				$filename = strtolower($parts['name']) . '.php';
				break;
		}

		return $filename;
	}

	/**
	 * Returns a Model object, always creating it
	 *
	 * @param   string  $type    The model type to instantiate
	 * @param   string  $prefix  Prefix for the model class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  self|boolean   A \JModelLegacy instance or false on failure
	 *
	 * @since   3.0
	 */
	public static function getInstance($type, $prefix = '', $config = array())
	{
		$type = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$modelClass = $prefix . ucfirst($type);

		if (!class_exists($modelClass))
		{
			jimport('joomla.filesystem.path');
			$path = \JPath::find(self::addIncludePath(null, $prefix), self::_createFileName('model', array('name' => $type)));

			if (!$path)
			{
				$path = \JPath::find(self::addIncludePath(null, ''), self::_createFileName('model', array('name' => $type)));
			}

			if (!$path)
			{
				return false;
			}

			require_once $path;

			if (!class_exists($modelClass))
			{
				\JLog::add(Text::sprintf('JLIB_APPLICATION_ERROR_MODELCLASS_NOT_FOUND', $modelClass), \JLog::WARNING, 'jerror');

				return false;
			}
		}

		// Check for a possible service from the container otherwise manually instantiate the class
		if (Factory::getContainer()->exists($modelClass))
		{
			return Factory::getContainer()->get($modelClass);
		}

		return new $modelClass($config);
	}

	/**
	 * Constructor
	 *
	 * @param   array                $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 *
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		// Guess the option from the class name (Option)Model(View).
		if (empty($this->option))
		{
			$r = null;

			if (!preg_match('/(.*)Model/i', get_class($this), $r))
			{
				throw new \Exception(Text::_('JLIB_APPLICATION_ERROR_MODEL_GET_NAME'), 500);
			}

			$this->option = ComponentHelper::getComponentName($this, $r[1]);
		}

		// Set the view name
		if (empty($this->name))
		{
			if (array_key_exists('name', $config))
			{
				$this->name = $config['name'];
			}
			else
			{
				$this->name = $this->getName();
			}
		}

		// Set the model state
		if (array_key_exists('state', $config))
		{
			$this->state = $config['state'];
		}
		else
		{
			$this->state = new \JObject;
		}

		// Set the model dbo
		if (array_key_exists('dbo', $config))
		{
			$this->_db = $config['dbo'];
		}
		else
		{
			$this->_db = Factory::getDbo();
		}

		// Set the default view search path
		if (array_key_exists('table_path', $config))
		{
			$this->addTablePath($config['table_path']);
		}
		// @codeCoverageIgnoreStart
		elseif (defined('JPATH_COMPONENT_ADMINISTRATOR'))
		{
			$this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
			$this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR . '/table');
		}

		// @codeCoverageIgnoreEnd

		// Set the internal state marker - used to ignore setting state from the request
		if (!empty($config['ignore_request']))
		{
			$this->__state_set = true;
		}

		// Set the clean cache event
		if (isset($config['event_clean_cache']))
		{
			$this->event_clean_cache = $config['event_clean_cache'];
		}
		elseif (empty($this->event_clean_cache))
		{
			$this->event_clean_cache = 'onContentCleanCache';
		}

		if ($factory)
		{
			$this->factory = $factory;
			return;
		}

		$component = Factory::getApplication()->bootComponent($this->option);

		if ($component instanceof MVCFactoryServiceInterface)
		{
			$this->factory = $component->createMVCFactory(Factory::getApplication());
		}
	}

	/**
	 * Gets an array of objects from the results of database query.
	 *
	 * @param   string   $query       The query.
	 * @param   integer  $limitstart  Offset.
	 * @param   integer  $limit       The number of records.
	 *
	 * @return  object[]  An array of results.
	 *
	 * @since   3.0
	 * @throws  \RuntimeException
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$this->getDbo()->setQuery($query, $limitstart, $limit);

		return $this->getDbo()->loadObjectList();
	}

	/**
	 * Returns a record count for the query.
	 *
	 * @param   DatabaseQuery|string  $query  The query.
	 *
	 * @return  integer  Number of rows for query.
	 *
	 * @since   3.0
	 */
	protected function _getListCount($query)
	{
		// Use fast COUNT(*) on DatabaseQuery objects if there is no GROUP BY or HAVING clause:
		if ($query instanceof DatabaseQuery
			&& $query->type == 'select'
			&& $query->group === null
			&& $query->merge === null
			&& $query->querySet === null
			&& $query->having === null)
		{
			$query = clone $query;
			$query->clear('select')->clear('order')->clear('limit')->clear('offset')->select('COUNT(*)');

			$this->getDbo()->setQuery($query);

			return (int) $this->getDbo()->loadResult();
		}

		// Otherwise fall back to inefficient way of counting all results.

		// Remove the limit and offset part if it's a DatabaseQuery object
		if ($query instanceof DatabaseQuery)
		{
			$query = clone $query;
			$query->clear('limit')->clear('offset');
		}

		$this->getDbo()->setQuery($query);
		$this->getDbo()->execute();

		return (int) $this->getDbo()->getNumRows();
	}

	/**
	 * Method to load and return a model object.
	 *
	 * @param   string  $name    The name of the view
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration settings to pass to \JTable::getInstance
	 *
	 * @return  Table|boolean  Table object or boolean false if failed
	 *
	 * @since   3.0
	 * @see     \JTable::getInstance()
	 */
	protected function _createTable($name, $prefix = 'Table', $config = array())
	{
		// Make sure we are returning a DBO object
		if (!array_key_exists('dbo', $config))
		{
			$config['dbo'] = $this->getDbo();
		}

		return $this->factory->createTable($name, $prefix, $config);
	}

	/**
	 * Method to get the database driver object
	 *
	 * @return  DatabaseDriver
	 *
	 * @since   3.0
	 */
	public function getDbo()
	{
		return $this->_db;
	}

	/**
	 * Method to get the model name
	 *
	 * The model name. By default parsed using the classname or it can be set
	 * by passing a $config['name'] in the class constructor
	 *
	 * @return  string  The name of the model
	 *
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function getName()
	{
		if (empty($this->name))
		{
			$r = null;

			if (!preg_match('/Model(.*)/i', get_class($this), $r))
			{
				throw new \Exception(Text::_('JLIB_APPLICATION_ERROR_MODEL_GET_NAME'), 500);
			}

			$this->name = str_replace(['\\', 'model'], '', strtolower($r[1]));
		}

		return $this->name;
	}

	/**
	 * Method to get model state variables
	 *
	 * @param   string  $property  Optional parameter name
	 * @param   mixed   $default   Optional default value
	 *
	 * @return  mixed  The property where specified, the state object where omitted
	 *
	 * @since   3.0
	 */
	public function getState($property = null, $default = null)
	{
		if (!$this->__state_set)
		{
			// Protected method to auto-populate the model state.
			$this->populateState();

			// Set the model state set flag to true.
			$this->__state_set = true;
		}

		return $property === null ? $this->state : $this->state->get($property, $default);
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  Table  A Table object
	 *
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function getTable($name = '', $prefix = '', $options = array())
	{
		if (empty($name))
		{
			$name = $this->getName();
		}

		// We need this ugly code to deal with non-namespaced MVC code
		if (empty($prefix) && $this->factory instanceof LegacyFactory)
		{
			$prefix = 'Table';
		}

		if ($table = $this->_createTable($name, $prefix, $options))
		{
			return $table;
		}

		throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
	}

	/**
	 * Method to load a row for editing from the version history table.
	 *
	 * @param   integer  $version_id  Key to the version history table.
	 * @param   Table    &$table      Content table object being loaded.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since   3.2
	 */
	public function loadHistory($version_id, Table &$table)
	{
		// Only attempt to check the row in if it exists, otherwise do an early exit.
		if (!$version_id)
		{
			return false;
		}

		// Get an instance of the row to checkout.
		$historyTable = Table::getInstance('Contenthistory');

		if (!$historyTable->load($version_id))
		{
			$this->setError($historyTable->getError());

			return false;
		}

		$rowArray = ArrayHelper::fromObject(json_decode($historyTable->version_data));
		$typeId   = Table::getInstance('Contenttype')->getTypeId($this->typeAlias);

		if ($historyTable->ucm_type_id != $typeId)
		{
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_HISTORY_ID_MISMATCH'));

			$key = $table->getKeyName();

			if (isset($rowArray[$key]))
			{
				$table->checkIn($rowArray[$key]);
			}

			return false;
		}

		$this->setState('save_date', $historyTable->save_date);
		$this->setState('version_note', $historyTable->version_note);

		return $table->bind($rowArray);
	}

	/**
	 * Method to check if the given record is checked out by the current user
	 *
	 * @param   \stdClass  $item  The record to check
	 *
	 * @return  bool
	 */
	public function isCheckedOut($item)
	{
		$table = $this->getTable();
		$checkedOutField = $table->getColumnAlias('checked_out');

		if (property_exists($item, $checkedOutField) && $item->{$checkedOutField} != Factory::getUser()->id)
		{
			return true;
		}

		return false;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @return  void
	 *
	 * @note    Calling getState in this method will result in recursion.
	 * @since   3.0
	 */
	protected function populateState()
	{
	}

	/**
	 * Method to set the database driver object
	 *
	 * @param   DatabaseDriver  $db  A DatabaseDriver based object
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function setDbo($db)
	{
		$this->_db = $db;
	}

	/**
	 * Method to set model state variables
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value of the property to set or null.
	 *
	 * @return  mixed  The previous value of the property or null if not set.
	 *
	 * @since   3.0
	 */
	public function setState($property, $value = null)
	{
		return $this->state->set($property, $value);
	}

	/**
	 * Clean the cache
	 *
	 * @param   string  $group  The cache group
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function cleanCache($group = null)
	{
		$conf = Factory::getConfig();

		$options = [
			'defaultgroup' => $group ?: ($this->option ?? Factory::getApplication()->input->get('option')),
			'cachebase'    => $conf->get('cache_path', JPATH_CACHE),
			'result'       => true,
		];

		try
		{
			/** @var \JCacheControllerCallback $cache */
			$cache = \JCache::getInstance('callback', $options);
			$cache->clean();
		}
		catch (\JCacheException $exception)
		{
			$options['result'] = false;
		}

		// Trigger the onContentCleanCache event.
		Factory::getApplication()->triggerEvent($this->event_clean_cache, $options);
	}

	/**
	 * Boots the component with the given name.
	 *
	 * @param   string  $component  The component name, eg. com_content.
	 *
	 * @return  ComponentInterface  The service container
	 *
	 * @since   4.0.0
	 */
	protected function bootComponent($component): ComponentInterface
	{
		return Factory::getApplication()->bootComponent($component);
	}
}
