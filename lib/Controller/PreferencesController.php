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

namespace OCA\Polls\Controller;

use OCP\IRequest;
use OCP\IUserSession;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCA\Polls\Service\PreferencesService;
use OCA\Polls\Service\CalendarService;

class PreferencesController extends Controller {

	/** @var PreferencesService */
	private $preferencesService;

	/** @var CalendarService */
	private $calendarService;

	/** @var IUserSession */
	private $userSession;

	use ResponseHandle;

	public function __construct(
		string $appName,
		IRequest $request,
		PreferencesService $preferencesService,
		CalendarService $calendarService,
		IUserSession $userSession
	) {
		parent::__construct($appName, $request);
		$this->calendarService = $calendarService;
		$this->preferencesService = $preferencesService;
		$this->userSession = $userSession;
	}

	/**
	 * Read all preferences
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function get(): JSONResponse {
		return $this->response(fn () => $this->preferencesService->get());
	}

	/**
	 * Write preferences
	 * @NoAdminRequired
	 */
	public function write(array $settings): JSONResponse {
		if (!$this->userSession->isLoggedIn()) {
			return new JSONResponse([], Http::STATUS_OK);
		}
		return $this->response(fn () => $this->preferencesService->write($settings));
	}

	/**
	 * Read all preferences
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getCalendars(): JSONResponse {
		return new JSONResponse(['calendars' => $this->calendarService->getCalendars()], Http::STATUS_OK);
	}
}
