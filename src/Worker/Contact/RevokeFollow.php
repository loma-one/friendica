<?php
/**
 * @copyright Copyright (C) 2010-2022, the Friendica project
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

namespace Friendica\Worker\Contact;

use Friendica\Core\Protocol;
use Friendica\Core\Worker;
use Friendica\Model\Contact;

class RevokeFollow
{
	/**
	 * Issue asynchronous follow revokation message to remote servers.
	 * The local relationship has already been updated, so we can't use the user-specific contact
	 *
	 * @param int $cid Target public contact id
	 * @param int $uid Source local user id
	 * @throws \Friendica\Network\HTTPException\InternalServerErrorException
	 * @throws \ImagickException
	 */
	public static function execute(int $cid, int $uid)
	{
		$contact = Contact::getById($cid);
		if (empty($contact)) {
			return;
		}

		$result = Protocol::revokeFollow($contact, $uid);
		if ($result === false) {
			Worker::defer();
		}
	}
}
