<?php

/**
 * @copyright Copyright (c) 2021 John Molakvoæ <skjnldsv@protonmail.com>
 *
 * @author John Molakvoæ <skjnldsv@protonmail.com>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCP\Profile;

use OCP\IUser;

/**
 * @since 23
 */
interface IProfileAction {

	/**
	 * preload the user specific value required by the action
	 * e.g. an email is loaded for the email action or userId for Talk
	 *
	 * @since 23
	 */
	public function preload(IUser $user): void;

	/**
	 * returns the unique ID of the action
	 * e.g. 'email'
	 *
	 * @since 23
	 */
	public function getId(): string;

	/**
	 * returns the translated title as it should be displayed
	 * e.g. 'Mail john@domain.com'
	 *
	 * use the L10N service to translate it
	 *
	 * @since 23
	 */
	public function getTitle(): string;

	/**
	 * returns the translated label as it should be displayed
	 * e.g. 'Mail'
	 *
	 * use the L10N service to translate it
	 *
	 * @since 23
	 */
	public function getLabel(): string;

	/**
	 * returns the priority as an integer
	 *
	 * the actions are sorted in ascending order
	 *
	 * e.g. 60
	 *
	 * @since 23
	 */
	public function getPriority(): int;

	/**
	 * returns the 16*16 SVG icon URL
	 *
	 * @since 23
	 */
	public function getIcon(): string;

	/**
	 * returns the target of the action
	 * e.g. 'mailto:john@domain.com'
	 *
	 * @since 23
	 */
	public function getTarget(): string;
}
