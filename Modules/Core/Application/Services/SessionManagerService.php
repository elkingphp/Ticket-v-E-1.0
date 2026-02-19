<?php

namespace Modules\Core\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Jenssegers\Agent\Agent;
use Carbon\Carbon;

class SessionManagerService
{
    /**
     * Get all active sessions for a user.
     *
     * @param int $userId
     * @return \Illuminate\Support\Collection
     */
    public function getUserSessions(int $userId)
    {
        if (config('session.driver') !== 'database') {
            return collect([]);
        }

        return DB::table('sessions')
            ->where('user_id', $userId)
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) {
            $agent = $this->createAgent($session->user_agent);

            return [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'is_current_device' => $session->id === Session::getId(),
                'agent' => [
                    'is_desktop' => $agent->isDesktop(),
                    'is_mobile' => $agent->isMobile(),
                    'is_tablet' => $agent->isTablet(),
                    'browser' => $agent->browser(),
                    'platform' => $agent->platform(),
                    'device' => $agent->device(),
                ],
                'last_activity' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                'last_activity_timestamp' => $session->last_activity,
                'fingerprint' => sha1($session->user_agent . explode('.', $session->ip_address)[0]), // simple mask
            ];
        });
    }

    /**
     * Terminate a specific session.
     *
     * @param string $sessionId
     * @param int $userId
     * @return bool
     */
    public function terminateSession(string $sessionId, int $userId): bool
    {
        if ($sessionId === Session::getId()) {
            return false;
        }

        return DB::transaction(function () use ($sessionId, $userId) {
            return DB::table('sessions')
                ->where('id', $sessionId)
                ->where('user_id', $userId)
                ->delete() > 0;
        });
    }

    /**
     * Terminate all other sessions for a user.
     *
     * @param int $userId
     * @param string|null $exceptId
     * @return void
     */
    public function terminateOtherSessions(int $userId, ?string $exceptId = null): void
    {
        DB::table('sessions')
            ->where('user_id', $userId)
            ->where('id', '!=', $exceptId ?: Session::getId())
            ->delete();
    }

    /**
     * Create an Agent instance from a user agent string.
     *
     * @param string $userAgent
     * @return Agent
     */
    protected function createAgent(string $userAgent): Agent
    {
        $agent = new Agent();
        $agent->setUserAgent($userAgent);
        return $agent;
    }
}