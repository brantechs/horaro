<?php
/*
 * Copyright (c) 2019, Sgt. Kabukiman, https://github.com/sgt-kabukiman
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace horaro\Library\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use horaro\Library\ScheduleItemIterator;
use horaro\Library\ReadableTime;

/**
 * Schedule
 */
class Schedule {
	const OPTION_COLUMN_NAME = '[[options]]';

	const COLUMN_SCHEDULED = 'col-scheduled';
	const COLUMN_ESTIMATE  = 'col-estimate';

	/**
	 * @var integer
	 */
	private $id;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $slug;

	/**
	 * @var string
	 */
	private $timezone;

	/**
	 * @var \DateTime
	 */
	private $start;

	/**
	 * @var string
	 */
	private $website;

	/**
	 * @var string
	 */
	private $twitter;

	/**
	 * @var string
	 */
       private $twitch;

       /**
        * @var string
        */
       private $youtube;

	/**
	 * @var string
	 */
	private $theme;

	/**
	 * @var string
	 */
	private $secret;

	/**
	 * @var string
	 */
	private $hidden_secret;

	/**
	 * @var string
	 */
	private $description;

	/**
	 * @var string
	 */
	private $setup_time;

	/**
	 * @var integer
	 */
	private $max_items;

	/**
	 * @var \DateTime
	 */
	private $updated_at;

	/**
	 * @var string
	 */
	private $extra;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $items;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $columns;

	/**
	 * @var \horaro\Library\Entity\Event
	 */
	private $event;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->items   = new ArrayCollection();
		$this->columns = new ArrayCollection();
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 * @return Schedule
	 */
	public function setName($name) {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Set slug
	 *
	 * @param string $slug
	 * @return Schedule
	 */
	public function setSlug($slug) {
		$this->slug = $slug;

		return $this;
	}

	/**
	 * Get slug
	 *
	 * @return string
	 */
	public function getSlug() {
		return $this->slug;
	}

	/**
	 * Set timezone
	 *
	 * @param string $timezone
	 * @return Schedule
	 */
	public function setTimezone($timezone) {
		$this->timezone = $timezone;

		return $this;
	}

	/**
	 * Get timezone
	 *
	 * @return string
	 */
	public function getTimezone() {
		return $this->timezone;
	}

	/**
	 * Get timezone as a DateTimeZone instance
	 *
	 * @return \DateTimeZone
	 */
	public function getTimezoneInstance() {
		return new \DateTimeZone($this->getTimezone());
	}

	/**
	 * Set updated_at
	 *
	 * @param \DateTime $updatedAt
	 * @return Schedule
	 */
	public function setUpdatedAt($updatedAt) {
		$this->updated_at = $updatedAt;

		return $this;
	}

	/**
	 * Set updated_at to now UTC
	 *
	 * @return Schedule
	 */
	public function touch() {
		return $this->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
	}

	/**
	 * Get updated_at (UTC)
	 *
	 * @return \DateTime
	 */
	public function getUpdatedAt() {
		$tmpFrmt = 'Y-m-d H:i:s';

		return \DateTime::createFromFormat($tmpFrmt, $this->updated_at->format($tmpFrmt), new \DateTimeZone('UTC')); // "inject" proper timezone
	}

	/**
	 * Get updated_at with the proper local timezone
	 *
	 * @return \DateTime
	 */
	public function getLocalUpdatedAt() {
		$local = $this->getUpdatedAt();
		$local->setTimezone($this->getTimezoneInstance());

		return $local;
	}

	/**
	 * Set start
	 *
	 * @param \DateTime $start
	 * @return Schedule
	 */
	public function setStart($start) {
		$this->start = $start;

		return $this;
	}

	/**
	 * Get start (with the system timezone; most likely not what you want)
	 *
	 * @return \DateTime
	 */
	public function getStart() {
		return $this->start;
	}

	/**
	 * Get start time with the proper local timezone
	 *
	 * The timezone will be fixed to the UTC offset of the starting date and time.
	 * This is done to prevent issues when a schedule uses a timezone that changes
	 * DST during it. In that case, PHP would switch the timezone offset internally,
	 * e.g.:
	 *     2015-03-08 01:13:00 - 05:00
	 *   +            02:45:00
	 *   = 2015-03-08 03:58:00 - 04:00
	 *
	 * I would consider this a bug in PHP's DateTime::add() implementation. To
	 * avoid this, we take away the DST effect by fixing the offset right now.
	 *
	 * @return \DateTime
	 */
	public function getLocalStart() {
		$tmpFrmt = 'Y-m-d H:i:s';
		$start   = $this->getStart()->format($tmpFrmt);
		$tz      = $this->getTimezoneInstance();

		// and now the PHP dance to get the UTC offset of $tz as "[+-]HH:MM"
		$offset   = $tz->getOffset(new \DateTime($start));
		$negative = $offset < 0;

		$offset  = abs($offset);
		$hours   = floor($offset / 3600);
		$minutes = floor(($offset - $hours*3600) / 60);
		$offset  = sprintf('%s%02d:%02d', $negative ? '-' : '+', $hours, $minutes);

		return \DateTime::createFromFormat($tmpFrmt.'P', $start.$offset); // "inject" proper timezone
	}

	/**
	 * Get start time in UTC timezone
	 *
	 * @return \DateTime
	 */
	public function getUTCStart() {
		$local = $this->getLocalStart();
		$local->setTimezone(new \DateTimeZone('UTC'));

		return $local;
	}

	/**
	 * Get end time with the proper local timezone
	 *
	 * @return \DateTime
	 */
	public function getLocalEnd() {
		$t = $this->getLocalStart();

		foreach ($this->getItems() as $item) {
			$t->add($item->getDateInterval());
		}

		return $t;
	}

	/**
	 * Set max items
	 *
	 * @param integer $maxItems
	 * @return Schedule
	 */
	public function setMaxItems($maxItems) {
		$this->max_items = $maxItems < 0 ? 0 : (int) $maxItems;

		return $this;
	}

	/**
	 * Get max items
	 *
	 * @return integer
	 */
	public function getMaxItems() {
		return $this->max_items;
	}

	/**
	 * Set theme
	 *
	 * @param string $theme
	 * @return Schedule
	 */
	public function setTheme($theme) {
		$this->theme = $theme;

		return $this;
	}

	/**
	 * Get theme
	 *
	 * @return string
	 */
	public function getTheme() {
		return $this->theme;
	}

	/**
	 * Set website
	 *
	 * @param string $website
	 * @return Event
	 */
	public function setWebsite($website) {
		$this->website = $website;

		return $this;
	}

	/**
	 * Get website
	 *
	 * @return string
	 */
	public function getWebsite() {
		return $this->website;
	}

	/**
	 * Get website
	 *
	 * @return string
	 */
	public function getWebsiteHost() {
		$website = $this->getWebsite();
		if (!$website) return null;

		return parse_url($website, PHP_URL_HOST);
	}

	/**
	 * Set twitter
	 *
	 * @param string $twitter
	 * @return Event
	 */
	public function setTwitter($twitter) {
		$this->twitter = $twitter;

		return $this;
	}

	/**
	 * Get twitter
	 *
	 * @return string
	 */
	public function getTwitter() {
		return $this->twitter;
	}

	/**
	 * Set twitch
	 *
	 * @param string $twitch
	 * @return Event
	 */
        public function setTwitch($twitch) {
                $this->twitch = $twitch;

                return $this;
        }

       /**
        * Set youtube
        *
        * @param string $youtube
        * @return Schedule
        */
       public function setYoutube($youtube) {
               $this->youtube = $youtube;

               return $this;
       }

	/**
	 * Get twitch
	 *
	 * @return string
	 */
        public function getTwitch() {
                return $this->twitch;
        }

       /**
        * Get youtube
        *
        * @return string
        */
       public function getYoutube() {
               return $this->youtube;
       }

	/**
	 * Set secret
	 *
	 * @param string $secret
	 * @return Schedule
	 */
	public function setSecret($secret) {
		$secret       = trim($secret);
		$this->secret = mb_strlen($secret) === 0 ? null : $secret;

		return $this;
	}

	/**
	 * Get secret
	 *
	 * @return string
	 */
	public function getSecret() {
		return $this->secret;
	}

	/**
	 * Set hidden secret
	 *
	 * @param string $secret
	 * @return Schedule
	 */
	public function setHiddenSecret($secret) {
		$secret              = trim($secret);
		$this->hidden_secret = mb_strlen($secret) === 0 ? null : $secret;

		return $this;
	}

	/**
	 * Get hidden secret
	 *
	 * @return string
	 */
	public function getHiddenSecret() {
		return $this->hidden_secret;
	}

	/**
	 * Set description
	 *
	 * @param string $description
	 * @return Schedule
	 */
	public function setDescription($description) {
		$this->description = trim($description) ?: null;

		return $this;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Set setup time
	 *
	 * @param \DateTime $setup_time
	 * @return Schedule
	 */
	public function setSetupTime(\DateTime $setup_time = null) {
		$this->setup_time = $setup_time;

		return $this;
	}

	/**
	 * Get setup time
	 *
	 * @return \DateTime
	 */
	public function getSetupTime() {
		return $this->setup_time;
	}

	/**
	 * Get setup time in seconds
	 *
	 * @return int
	 */
	public function getSetupTimeInSeconds() {
		return ReadableTime::dateTimeToSeconds($this->getSetupTime());
	}

	/**
	 * Get setup time as ISO duration
	 *
	 * @return string
	 */
	public function getSetupTimeISODuration() {
		return ReadableTime::dateTimeToISODuration($this->getSetupTime());
	}

	/**
	 * Get setup time as DateInterval
	 *
	 * @return \DateInterval
	 */
	public function getSetupTimeDateInterval() {
		return ReadableTime::dateTimeToDateInterval($this->getSetupTime());
	}

	/**
	 * Get whether the schedule and its parent event are public
	 *
	 * @return boolean
	 */
	public function isPublic() {
		return !$this->getSecret() && $this->getEvent()->isPublic();
	}

	/**
	 * Get link
	 *
	 * @return string
	 */
	public function getLink() {
		$event = $this->getEvent();
		$url   = '/'.$event->getSlug().'/'.$this->getSlug();

		// for convenience reasons, create links that have access to the whole event if possible
		if ($event->getSecret()) {
			$url .= '?key='.$event->getSecret();
		}
		elseif ($this->getSecret()) {
			$url .= '?key='.$this->getSecret();
		}

		return $url;
	}

	/**
	 * Set extra
	 *
	 * @param array $extra
	 * @return Schedule
	 */
	public function setExtra(array $extra) {
		ksort($extra);
		$this->extra = json_encode($extra);

		return $this;
	}

	/**
	 * Get extra
	 *
	 * @return array
	 */
	public function getExtra() {
		return $this->extra === null ? [] : json_decode($this->extra, true);
	}

	public function getText($key) {
		$extra = $this->getExtra();

		return isset($extra['texts'][$key]) ? $extra['texts'][$key] : null;
	}

	/**
	 * Add item
	 *
	 * @param \horaro\Library\Entity\ScheduleItem $item
	 * @return Schedule
	 */
	public function addItem(ScheduleItem $item) {
		$this->items[] = $item;

		return $this;
	}

	/**
	 * Remove item
	 *
	 * @param \horaro\Library\Entity\ScheduleItem $item
	 */
	public function removeItem(ScheduleItem $item) {
		$this->items->removeElement($item);
	}

	/**
	 * Get items
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getItems() {
		return $this->items;
	}

	/**
	 * @return \horaro\Library\ScheduleItemIterator
	 */
	public function getScheduledItems() {
		return new ScheduleItemIterator($this);
	}

	/**
	 * Add column
	 *
	 * @param \horaro\Library\Entity\ScheduleColumn $column
	 * @return Schedule
	 */
	public function addColumn(ScheduleColumn $column) {
		$this->columns[] = $column;

		return $this;
	}

	/**
	 * Remove column
	 *
	 * @param \horaro\Library\Entity\ScheduleColumn $column
	 */
	public function removeColumn(ScheduleColumn $column) {
		$this->columns->removeElement($column);
	}

	/**
	 * Get columns
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getColumns() {
		return $this->columns;
	}

	/**
	 * Get visible columns
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getVisibleColumns() {
		return $this->columns->filter(function($col) {
			return !$col->isHidden();
		});
	}

	/**
	 * Set event
	 *
	 * @param \horaro\Library\Entity\Event $event
	 * @return Schedule
	 */
	public function setEvent(Event $event) {
		$this->event = $event;

		return $this;
	}

	/**
	 * Get event
	 *
	 * @return \horaro\Library\Entity\Event
	 */
	public function getEvent() {
		return $this->event;
	}

	public function getMaxItemWidth($columns) {
		$max = 0;

		foreach ($this->getItems() as $item) {
			$max = max($max, $item->getWidth($columns));
		}

		return $max;
	}

	public function needsSeconds() {
		$iterator = new ScheduleItemIterator($this);

		foreach ($iterator as $item) {
			if ($item->getScheduled()->format('s') !== '00') {
				return true;
			}
		}

		return false;
	}

	public function getOptionsColumn() {
		$columns = $this->getColumns();

		foreach ($columns as $col) {
			if ($col->getName() === self::OPTION_COLUMN_NAME) {
				return $col;
			}
		}

		return null;
	}
}
