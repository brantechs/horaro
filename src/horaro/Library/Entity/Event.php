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

use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 */
class Event {
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
	private $description;

	/**
	 * @var integer
	 */
	private $max_schedules;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $schedules;

	/**
	 * @var \horaro\Library\Entity\User
	 */
	private $user;

	/**
	 * @var \horaro\Library\Entity\Team
	 */
	private $team;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->schedules = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function setOwner($owner) {
		if ($owner instanceof User) {
			return $this->setUser($owner)->setTeam(null);
		}
		elseif ($owner instanceof Team) {
			return $this->setUser(null)->setTeam($owner);
		}

		throw new \InvalidArgumentException('$owner must be either a User or a Team instance, got '.get_class($owner).' instance.');
	}

	public function getOwner() {
		return $this->getTeam() ?: $this->getUser();
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
	 * @return Event
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
	 * @return Event
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
	 * Get link
	 *
	 * @return string
	 */
	public function getLink() {
		$url = '/'.$this->getSlug();

		if ($this->getSecret()) {
			$url .= '?key='.$this->getSecret();
		}

		return $url;
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
        * @return Event
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
	 * Set max schedules
	 *
	 * @param integer $maxSchedules
	 * @return Event
	 */
	public function setMaxSchedules($maxSchedules) {
		$this->max_schedules = $maxSchedules < 0 ? 0 : (int) $maxSchedules;

		return $this;
	}

	/**
	 * Get max schedules
	 *
	 * @return integer
	 */
	public function getMaxSchedules() {
		return $this->max_schedules;
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
	 * Get whether the event is public
	 *
	 * @return boolean
	 */
	public function isPublic() {
		return !$this->getSecret();
	}

	/**
	 * Add schedule
	 *
	 * @param \horaro\Library\Entity\Schedule $schedule
	 * @return Event
	 */
	public function addSchedule(Schedule $schedule) {
		$this->schedules[] = $schedule;

		return $this;
	}

	/**
	 * Remove schedule
	 *
	 * @param \horaro\Library\Entity\Schedule $schedule
	 */
	public function removeSchedule(Schedule $schedule) {
		$this->schedules->removeElement($schedule);
	}

	/**
	 * Get schedules
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getSchedules() {
		return $this->schedules;
	}

	/**
	 * Set user
	 *
	 * @param \horaro\Library\Entity\User $user
	 * @return Event
	 */
	public function setUser(User $user = null) {
		$this->user = $user;

		return $this;
	}

	/**
	 * Get user
	 *
	 * @return \horaro\Library\Entity\User
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * Set team
	 *
	 * @param \horaro\Library\Entity\Team $team
	 * @return Event
	 */
	public function setTeam(Team $team = null) {
		$this->team = $team;

		return $this;
	}

	/**
	 * Get team
	 *
	 * @return \horaro\Library\Entity\Team
	 */
	public function getTeam() {
		return $this->team;
	}
}
