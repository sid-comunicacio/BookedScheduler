<?php

/**
 * Copyright 2011-2018 Nick Korbel
 *
 * This file is part of Booked Scheduler is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Booked Scheduler.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(ROOT_DIR . 'lib/Application/Reservation/ResourceAvailability.php');

class ResourceAvailabilityRule implements IReservationValidationRule
{
    /**
     * @var IResourceAvailabilityStrategy
     */
    protected $strategy;

    /**
     * @var string
     */
    protected $timezone;

    /**
     * @var IScheduleRepository
     */
    protected $scheduleRepository;

    public function __construct(IResourceAvailabilityStrategy $strategy, $timezone, IScheduleRepository $scheduleRepository)
    {
        $this->strategy = $strategy;
        $this->timezone = $timezone;
        $this->scheduleRepository = $scheduleRepository;
    }

    public function Validate($reservationSeries, $retryParameters = null)
    {
        $schedule = $this->scheduleRepository->LoadById($reservationSeries->ScheduleId());
        if ($schedule->GetAllowConcurrentReservations())
        {
            return new ReservationRuleResult();
        }
        $shouldSkipConflicts = ReservationRetryParameter::GetValue('skipconflicts', $retryParameters, new BooleanConverter()) == true;
        $conflicts = array();

        $reservations = $reservationSeries->Instances();

        $bufferTime = $reservationSeries->MaxBufferTime();

        $keyedResources = array();
        foreach ($reservationSeries->AllResources() as $resource) {
            $keyedResources[$resource->GetId()] = $resource;
        }

        /** @var Reservation $reservation */
        foreach ($reservations as $reservation) {
            Log::Debug("Checking for reservation conflicts, reference number %s on %s", $reservation->ReferenceNumber(), $reservation->StartDate());

            $startDate = $reservation->StartDate();
            $endDate = $reservation->EndDate();

            if ($bufferTime != null && !$reservationSeries->BookedBy()->IsAdmin) {
                $startDate = $startDate->SubtractInterval($bufferTime);
                $endDate = $endDate->AddInterval($bufferTime);
            }

            $existingItems = $this->strategy->GetItemsBetween($startDate, $endDate, array_keys($keyedResources));

            /** @var IReservedItemView $existingItem */
            foreach ($existingItems as $existingItem) {
                if (
                    ($bufferTime == null || $reservationSeries->BookedBy()->IsAdmin) &&
                    ($existingItem->GetStartDate()->Equals($reservation->EndDate()) ||
                        $existingItem->GetEndDate()->Equals($reservation->StartDate()))
                ) {
                    continue;
                }

                if ($this->IsInConflict($reservation, $reservationSeries, $existingItem, $keyedResources)) {
                    $skipped = false;
                    if ($shouldSkipConflicts) {
                        Log::Debug("Skipping conflicting reservation. Reference number %s conflicts with existing %s with id %s on %s",
                            $reservation->ReferenceNumber(), get_class($existingItem), $existingItem->GetId(), $reservation->StartDate());

                        $skipped = $reservationSeries->RemoveInstance($reservation);
                    }
                    if (!$skipped) {
                        Log::Debug("Reference number %s conflicts with existing %s with id %s on %s",
                            $reservation->ReferenceNumber(), get_class($existingItem), $existingItem->GetId(), $reservation->StartDate());

                        array_push($conflicts, $existingItem);
                    }
                }
            }
        }

        $thereAreConflicts = count($conflicts) > 0;

        if ($thereAreConflicts) {
            $numberOfReservationDates = count($reservationSeries->Instances());
            $shouldRetry = count($conflicts) < $numberOfReservationDates;
            $canJoinWaitlist = $numberOfReservationDates == 1;
            return new ReservationRuleResult(false,
                $this->GetErrorString($conflicts),
                $shouldRetry,
                Resources::GetInstance()->GetString('RetrySkipConflicts'),
                array(new ReservationRetryParameter('skipconflicts', true)),
                $canJoinWaitlist);
        }

        return new ReservationRuleResult();
    }

    /**
     * @param Reservation $instance
     * @param ReservationSeries $series
     * @param IReservedItemView $existingItem
     * @param BookableResource[] $keyedResources
     * @return bool
     */
    protected function IsInConflict(Reservation $instance, ReservationSeries $series, IReservedItemView $existingItem, $keyedResources)
    {
        if ((array_key_exists($existingItem->GetResourceId(), $keyedResources)) && !$existingItem->IsDeleted) {

			       return $existingItem->BufferedTimes()->Overlaps($instance->Duration());
        }

        return false;
    }

    /**
     * @param array|IReservedItemView[] $conflicts
     * @return string
     */
    protected function GetErrorString($conflicts)
    {
        $errorString = new StringBuilder();

        $errorString->Append(Resources::GetInstance()->GetString('ConflictingReservationDates'));
        $errorString->Append("\n");
        $format = Resources::GetInstance()->GetDateFormat(ResourceKeys::DATE_GENERAL);

        $dates = array();
        /** @var IReservedItemView $conflict */
        foreach ($conflicts as $conflict) {
            $dates[] = sprintf('%s - %s', $conflict->GetStartDate()->ToTimezone($this->timezone)->Format($format),
                $conflict->GetResourceName());
        }

        $uniqueDates = array_unique($dates);
        sort($uniqueDates);

        foreach ($uniqueDates as $date) {
            $errorString->Append($date);
            $errorString->Append("\n");
        }

        return $errorString->ToString();
    }
}
