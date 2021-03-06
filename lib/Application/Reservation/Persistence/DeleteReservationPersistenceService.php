<?php
/**
Copyright 2011-2018 Nick Korbel

This file is part of Booked Scheduler is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Booked Scheduler.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once(ROOT_DIR . 'lib/Application/Reservation/Persistence/IReservationPersistenceService.php');

interface IDeleteReservationPersistenceService extends IReservationPersistenceService
{
	/**
	 * @param string $referenceNumber
	 * @return ExistingReservationSeries
	 */
	public function LoadByReferenceNumber($referenceNumber);
}

class DeleteReservationPersistenceService implements IDeleteReservationPersistenceService
{
	/**
	 * @var IReservationRepository
	 */
	private $_repository;

	public function __construct(IReservationRepository $repository)
	{
		$this->_repository = $repository;
	}

	public function LoadByReferenceNumber($referenceNumber)
	{
		return $this->_repository->LoadByReferenceNumber($referenceNumber);
	}

	public function Persist($existingReservationSeries)
	{
	    $existingReservationSeries->Delete(ServiceLocator::GetServer()->GetUserSession(), $existingReservationSeries->GetDeleteReason());
		$this->_repository->Delete($existingReservationSeries);
	}
}
