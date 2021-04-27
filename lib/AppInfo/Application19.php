<?php
/**
 * @copyright Copyright (c) 2017 Vinzenz Rosenkranz <vinzenz.rosenkranz@gmail.com>
 *
 * @author Vinzenz Rosenkranz <vinzenz.rosenkranz@gmail.com>
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

namespace OCA\Polls\AppInfo;

use OCP\AppFramework\App;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\User\Events\UserDeletedEvent;
use OCA\Polls\Notification\Notifier;
use OCA\Polls\Listener\UserDeletedListener;

class Application19 extends App {
	public function __construct(array $urlParams = []) {
		parent::__construct('polls', $urlParams);
		$this->registerNotifications();
		$this->registerUserDeletedListener();
	}

	public function registerNotifications(): void {
		$notificationManager = \OC::$server->getNotificationManager();
		$notificationManager->registerNotifierService(Notifier::class);
	}

	public function registerUserDeletedListener(): void {
		$eventDispatcher = $this->getContainer()->query(IEventDispatcher::class);
		$eventDispatcher->addServiceListener(UserDeletedEvent::class, UserDeletedListener::class);
	}
}
