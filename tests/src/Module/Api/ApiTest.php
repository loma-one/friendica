<?php
/**
 * @copyright Copyright (C) 2010-2021, the Friendica project
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

namespace Friendica\Test\src\Module\Api;

use Friendica\Core\Addon;
use Friendica\Core\Hook;
use Friendica\Database\Database;
use Friendica\DI;
use Friendica\Module\Api\ApiResponse;
use Friendica\Security\Authentication;
use Friendica\Test\FixtureTest;
use Friendica\Test\Util\ApiResponseDouble;
use Friendica\Test\Util\AuthenticationDouble;

abstract class ApiTest extends FixtureTest
{
	/**
	 * Assert that the string is XML and contain the root element.
	 *
	 * @param string $result       XML string
	 * @param string $root_element Root element name
	 *
	 * @return void
	 */
	protected function assertXml(string $result = '', string $root_element = '')
	{
		self::assertStringStartsWith('<?xml version="1.0"?>', $result);
		self::assertStringContainsString('<' . $root_element, $result);
		// We could probably do more checks here.
	}

	protected function setUp(): void
	{
		parent::setUp(); // TODO: Change the autogenerated stub

		$this->dice = $this->dice
			->addRule(Authentication::class, ['instanceOf' => AuthenticationDouble::class, 'shared' => true])
			->addRule(ApiResponse::class, ['instanceOf' => ApiResponseDouble::class, 'shared' => true]);
		DI::init($this->dice);

		$this->installAuthTest();
	}

	protected function tearDown(): void
	{
		ApiResponseDouble::reset();

		parent::tearDown();
	}

	/**
	 * installs auththest.
	 *
	 * @throws \Exception
	 */
	public function installAuthTest()
	{
		$addon           = 'authtest';
		$addon_file_path = __DIR__ . '/../../../Util/authtest/authtest.php';
		$t               = @filemtime($addon_file_path);

		@include_once($addon_file_path);
		if (function_exists($addon . '_install')) {
			$func = $addon . '_install';
			$func(DI::app());
		}

		/** @var Database $dba */
		$dba = $this->dice->create(Database::class);

		$dba->insert('addon', [
			'name'         => $addon,
			'installed'    => true,
			'timestamp'    => $t,
			'plugin_admin' => function_exists($addon . '_addon_admin'),
			'hidden'       => file_exists('addon/' . $addon . '/.hidden')
		]);

		Addon::loadAddons();
		Hook::loadHooks();
	}
}
