<?php

namespace Modules\Educational\Application\Services;

class FacilityAvailabilityService
{
    /**
     * Check if a room is available at a given time.
     * 
     * @param int $roomId
     * @param string $startTime
     * @param string $endTime
     * @return bool
     */
    public function isRoomAvailable(int $roomId, string $startTime, string $endTime): bool
    {
        // FUTURE: This will query the database (likely using SELECT ... FOR UPDATE or 
        // checking against version columns / composite indexes) to ensure the physical
        // room isn't double booked. For now, it's just a skeleton.

        return true;
    }

    /**
     * Determine available capacity of a building or a floor.
     * 
     * @param string $level (e.g. 'building', 'floor')
     * @param int $id
     * @return int
     */
    public function getAvailableCapacity(string $level, int $id): int
    {
        // FUTURE: Traverse down to active rooms and sum their capacities.
        return 0;
    }
}
