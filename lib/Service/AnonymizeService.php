<?php
/**
 * @copyright Copyright (c) 2017 Vinzenz Rosenkranz <vinzenz.rosenkranz@gmail.com>
 *
 * @author René Gieling <github@dartcafe.de>
 *
 * @license GNU AGPL version 3 or any later version
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


namespace OCA\Polls\Service;

use OCA\Polls\Db\Vote;
use OCA\Polls\Db\VoteMapper;
use OCA\Polls\Db\Comment;
use OCA\Polls\Db\CommentMapper;
use OCA\Polls\Db\Option;
use OCA\Polls\Db\OptionMapper;
use OCA\Polls\Db\Poll;

class AnonymizeService {

	/** @var VoteMapper */
	private $voteMapper;

	/** @var CommentMapper */
	private $commentMapper;

	/** @var OptionMapper */
	private $optionMapper;

	/** @var array */
	private $anonList;

	/** @var string|null */
	private $userId;

	/** @var int */
	private $pollId;

	public function __construct(
		VoteMapper $voteMapper,
		CommentMapper $commentMapper,
		OptionMapper $optionMapper
	) {
		$this->voteMapper = $voteMapper;
		$this->commentMapper = $commentMapper;
		$this->optionMapper = $optionMapper;
		$this->anonList = [];
		$this->userId = null;
		$this->pollId = 0;
	}

	/**
	 * Anonymizes the participants of a poll
	 * $array Input list which should be anonymized must be a collection of Vote or Comment
	 * Returns the original array with anonymized user names
	 */
	public function anonymize(array &$array): void {
		// get mapping for the complete poll
		foreach ($array as &$element) {
			if (!$element->getUserId() || $element->getUserId() === $this->userId) {
				// skip current user
				continue;
			}
			$element->setUserId($this->anonList[$element->getUserId()] ?? 'Unknown user');
		}
	}

	/**
	 * Initialize anonymizer with pollId and userId
	 * Creates a mapping list with unique Anonymous strings based on the partcipants of a poll
	 * $userId - usernames, which will not be anonymized
	 */
	public function set(int $pollId, string $userId): void {
		$this->pollId = $pollId;
		$this->userId = $userId;
		$votes = $this->voteMapper->findByPoll($pollId);
		$comments = $this->commentMapper->findByPoll($pollId);
		$options = $this->optionMapper->findByPoll($pollId);
		$i = 0;

		foreach ($votes as $element) {
			if (!array_key_exists($element->getUserId(), $this->anonList) && $element->getUserId() !== $userId) {
				$this->anonList[$element->getUserId()] = 'Anonymous ' . ++$i;
			}
		}

		foreach ($comments as $element) {
			if (!array_key_exists($element->getUserId(), $this->anonList) && $element->getUserId() !== $userId) {
				$this->anonList[$element->getUserId()] = 'Anonymous ' . ++$i;
			}
		}
		foreach ($options as $element) {
			if (!array_key_exists($element->getUserId(), $this->anonList) && $element->getUserId() !== $userId) {
				$this->anonList[$element->getUserId()] = 'Anonymous ' . ++$i;
			}
		}
		return;
	}

	/**
	 * Replaces userIds with displayName to avoid exposing usernames in public polls
	 */
	public static function replaceUserId(&$arrayOrObject, string $userId) : void {
		if (is_array($arrayOrObject)) {
			foreach ($arrayOrObject as $item) {
				if ($item->getUserId() === $userId) {
					continue;
				}
				if ($item instanceof Comment || $item instanceof Vote) {
					$item->setUserId($item->getDisplayName());
				}
				if ($item instanceof Option || $item instanceof Poll) {
					$item->setOwner($item->getDisplayName());
				}
			}
			return;
		}

		if ($arrayOrObject->getUserId() === $userId) {
			return;
		}

		if ($arrayOrObject instanceof Option || $arrayOrObject instanceof Poll) {
			$arrayOrObject->setOwner($arrayOrObject->getDisplayName());
		}

		if ($arrayOrObject instanceof Comment || $arrayOrObject instanceof Vote) {
			$arrayOrObject->setUserId($arrayOrObject->getDisplayName());
		}

		return;
	}
}
