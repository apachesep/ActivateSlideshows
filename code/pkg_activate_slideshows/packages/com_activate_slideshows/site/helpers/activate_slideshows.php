<?php
/**
 * @version     1.0.0
 * @package     com_activate_slideshows
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Activate Media <andrea@activatemedia.co.uk> - http://activatemedia.co.uk
 */

defined('_JEXEC') or die;

abstract class Activate_slideshowsHelper
{
	public static function getLatestSlideshowID()
	{
		$sql = "SELECT slideshows.id FROM #__activate_slideshows_slideshows as slideshows WHERE slideshows.state = 1 ORDER BY id DESC;";
		$db = JFactory::getDbo();
		$db->setQuery($sql);
		$db->execute();
		$result = $db->loadResult();
		return $result;
	}
	
	public static function getItems($slideshow_ids)
	{
		if(!isset($slideshow_ids) OR empty($slideshow_ids))
		{
			return array();
		}
		else
		{
			$IN = "";
			$slideshows_count = count($slideshow_ids);
			$index = 0;
			foreach($slideshow_ids as $slideshow_id)
			{
				$IN .= $slideshow_id;
				if($index++!=$slideshows_count-1) $IN.=",";
			}			
			$sql = "SELECT * FROM #__di_images WHERE state = 1 AND component = 'com_activate_slideshows' AND object_id IN (".$IN.") ORDER BY featured DESC,ordering ASC";
			$db = JFactory::getDbo();
			$db->setQuery($sql);
			$db->execute();
			$result = $db->loadAssocList();
			
			$items = self::addImagePath(JURI::base()."images/di/", $result);
			//echo "<pre>".print_r($items,1)."</pre>";
			return $items;
		}
	}
	
	public static function getSlideshows($slideshow_ids='')
	{
		$db = JFactory::getDbo();
		if(!isset($slideshow_ids) OR empty($slideshow_ids))
		{
			// All the slideshows
			$sql = "SELECT slideshows.* FROM #__activate_slideshows_slideshows as slideshows WHERE slideshows.state = 1 ORDER BY slideshows.ordering ASC;";									
		}
		else
		{
			$IN = "";
			$slideshows_count = count($slideshow_ids);
			$index = 0;
			foreach($slideshow_ids as $slideshow_id)
			{
				$IN .= $slideshow_id;
				if($index++!=$slideshows_count-1) $IN.=",";
			}			

			$sql = "SELECT slideshows.* FROM #__activate_slideshows_slideshows as slideshows WHERE id IN (".$IN.") AND slideshows.state = 1 ORDER BY slideshows.ordering ASC;";						
			
		}
		
		$db->setQuery($sql);
		$db->execute();
		$result = $db->loadAssocList();
			
			$index = 0;
			foreach($result as $item)
			{				
				$sql = "SELECT images.object_image_id, images.object_id, images.filename, images.title, images.link, images.featured, images.link_target FROM #__di_images as images WHERE images.object_id = ".$item["id"]." AND images.state = 1 AND images.component = 'com_activate_slideshows' ORDER BY featured DESC LIMIT 1;";
				$db->setQuery($sql);
				$db->execute();
				$result2 = $db->loadAssocList();
				$result[$index++]["images"] = self::addImagePath(JURI::base()."images/di/", $result2);
			}
			$items = $result;
			$items = self::addSlideshowURL($items);
			return $items;
	}
	
	public static function addImagePath($path, $items)
	{
		for($i=0; $i<count($items); $i++)
		{
			$items[$i]["filepath"] = $path.$items[$i]["object_id"]."_".$items[$i]["object_image_id"]."_".$items[$i]["filename"];
		}
		//echo "<pre>".print_r($items,1)."</pre>";
		return $items;
	}
	
	public static function addSlideshowURL($items)
	{
		for($i=0; $i<count($items); $i++)
		{
			$items[$i]["slideshow_link"] = JRoute::_('index.php?option=com_activate_slideshows&view=slideshow&id=' . (int)$items[$i]["id"]);
		}
		return $items;		
	}
}

