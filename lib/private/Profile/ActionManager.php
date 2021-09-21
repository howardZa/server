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

namespace OC\Profile;


use function Safe\uasort;
use InvalidArgumentException;
use OC\Profile\Actions\EmailAction;
use OCP\IUser;
use OCP\Profile\IActionManager;
use OCP\Profile\IProfileAction;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

/**
 * @since 23
 */
class ActionManager implements IActionManager {

	/** @var ContainerInterface */
	private $container;

	/** @var LoggerInterface */
	private $logger;

	/** @var IProfileAction[] */
	private $actions = [];

	/** @var string[] */
	private $actionQueue = [];

	/** @var string[] */
	private $serverActionQueue = [
		EmailAction::class,
		PhoneAction::class,
		WebsiteAction::class,
		TwitterAction::class,
	];

	public function __construct(
		ContainerInterface $container,
		LoggerInterface $logger
	) {
		$this->container = $container;
		$this->logger = $logger;
	}

	/**
	 * @inheritDoc
	 */
	public function queueAction(string $actionClass): void {
		$this->actionQueue[] = $actionClass;
	}

	/**
	 * Register a new action for the user profile page
	 */
	private function registerAction(IProfileAction $action): void {
		if (array_key_exists($action->getId(), $this->actions)) {
			throw new InvalidArgumentException('Profile action with this id has already been registered');
		}

		$this->actions[$action->getId()] = $action;
	}

	private function loadActions(IUser $user): void {
		$queuedActions = array_merge($this->serverActionQueue, $this->actionQueue);

		foreach ($queuedActions as $actionClass) {
			try {
				/** @var IProfileAction $action */
				$action = $this->container->get($actionClass);
				if (!($action instanceof IProfileAction)) {
					$this->logger->error("$actionClass is not an IProfileAction instance");
				}
				$action->preload($user);
				$this->registerAction($action);
			} catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
				$this->logger->error(
					"Could not load profile action class: $actionClass",
					[
						'exception' => $e,
					]
				);
			}
		}

		// Action registration complete, empty the action queues
		$this->serverActionQueue = [];
		$this->actionQueue = [];
	}

	/**
	 * @inheritDoc
	 */
	public function getActions(IUser $user): array {
		$this->loadActions($user);

		$actionsClone = $this->actions;
		uasort($actionsClone, function (IProfileAction $a, IProfileAction $b) {
			// sort by ascending priority
			return $a->getPriority() === $b->getPriority() ? 0 : ($a->getPriority() < $b->getPriority() ? -1 : 1);
		});
		return $actionsClone;
	}
}
