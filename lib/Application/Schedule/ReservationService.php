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

class ReservationService implements IReservationService
{
	/**
	 * @var IReservationViewRepository
	 */
	private $_repository;

	/**
	 * @var IReservationListingFactory
	 */
	private $_coordinatorFactory;

	public function __construct(IReservationViewRepository $repository, IReservationListingFactory $listingFactory)
	{
		$this->_repository = $repository;
		$this->_coordinatorFactory = $listingFactory;
	}

	public function GetReservations(DateRange $dateRangeUtc, $scheduleId, $targetTimezone, $resourceIds = null)
	{
	    $filterResourcesInCode = $resourceIds != null && count($resourceIds) > 100;
	    $resourceKeys = array();
	    if ($filterResourcesInCode)
        {
            $resourceKeys = array_combine($resourceIds, $resourceIds);
        }
		$reservationListing = $this->_coordinatorFactory->CreateReservationListing($targetTimezone);

		$reservations = $this->_repository->GetReservations($dateRangeUtc->GetBegin(), $dateRangeUtc->GetEnd(), null, null, $scheduleId, ($filterResourcesInCode ? array() : $resourceIds));
		Log::Debug("Found %s reservations for schedule %s between %s and %s", count($reservations), $scheduleId, $dateRangeUtc->GetBegin(), $dateRangeUtc->GetEnd());

		foreach ($reservations as $reservation)
		{
		    if ($filterResourcesInCode && array_key_exists($reservation->ResourceId, $resourceKeys))
            {
                $reservationListing->Add($reservation);
            }
            else
            {
                $reservationListing->Add($reservation);
            }
		}

		$blackouts = $this->_repository->GetBlackoutsWithin($dateRangeUtc, $scheduleId);
		Log::Debug("Found %s blackouts for schedule %s between %s and %s", count($blackouts), $scheduleId, $dateRangeUtc->GetBegin(), $dateRangeUtc->GetEnd());

		foreach ($blackouts as $blackout)
		{
			$reservationListing->AddBlackout($blackout);
		}

		return $reservationListing;
	}

	public function GetReservationsWithoutCancelled(DateRange $dateRangeUtc, $scheduleId, $targetTimezone, $resourceIds = null)
	{
	    $filterResourcesInCode = $resourceIds != null && count($resourceIds) > 100;
	    $resourceKeys = array();
	    if ($filterResourcesInCode)
        {
            $resourceKeys = array_combine($resourceIds, $resourceIds);
        }
		$reservationListing = $this->_coordinatorFactory->CreateReservationListing($targetTimezone);

		$reservations = $this->_repository->GetReservationsWithoutCancelled($dateRangeUtc->GetBegin(), $dateRangeUtc->GetEnd(), null, null, $scheduleId, ($filterResourcesInCode ? array() : $resourceIds));
		Log::Debug("Found %s reservations for schedule %s between %s and %s", count($reservations), $scheduleId, $dateRangeUtc->GetBegin(), $dateRangeUtc->GetEnd());

		foreach ($reservations as $reservation)
		{
		    if ($filterResourcesInCode && array_key_exists($reservation->ResourceId, $resourceKeys))
            {
                $reservationListing->Add($reservation);
            }
            else
            {
                $reservationListing->Add($reservation);
            }
		}

		$blackouts = $this->_repository->GetBlackoutsWithin($dateRangeUtc, $scheduleId);
		Log::Debug("Found %s blackouts for schedule %s between %s and %s", count($blackouts), $scheduleId, $dateRangeUtc->GetBegin(), $dateRangeUtc->GetEnd());

		foreach ($blackouts as $blackout)
		{
			$reservationListing->AddBlackout($blackout);
		}

		return $reservationListing;
	}
}

interface IReservationService
{
	/**
	 * @param DateRange $dateRangeUtc range of dates to search against in UTC
	 * @param int $scheduleId
	 * @param string $targetTimezone timezone to convert the results to
	 * @param null|int[] $resourceIds
	 * @return IReservationListing
	 */
	function GetReservations(DateRange $dateRangeUtc, $scheduleId, $targetTimezone, $resourceIds = null);

	/**
	 * @param DateRange $dateRangeUtc range of dates to search against in UTC
	 * @param int $scheduleId
	 * @param string $targetTimezone timezone to convert the results to
	 * @param null|int[] $resourceIds
	 * @return IReservationListing
	 */
	function GetReservationsWithoutCancelled(DateRange $dateRangeUtc, $scheduleId, $targetTimezone, $resourceIds = null);
}
