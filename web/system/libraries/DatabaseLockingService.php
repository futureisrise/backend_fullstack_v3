<?php

namespace System\Libraries;

use App;
use CriticalException;

class DatabaseLockingService {
    const MAX_NAME_LENGTH = 64;
    /**
     * @var int
     */
    private $timeout = 30;
    /**
     * @var bool
     */
    private $waitForLock = FALSE;


    /**
     * DatabaseLockingService constructor.
     */
    public function __construct()
    {

    }

    /**
     * @param string $lockName
     * @return bool
     */
    public function isLocked(string $lockName): bool
    {
        if ( ! $this->waitForLock)
        {
            $result = App::get_s()->sql('SELECT IS_FREE_LOCK("' . $lockName . '") as `lock`')->one();
            if ( ! empty($result) && $result['lock'] == 0)
            {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * @param string $lockName
     * @return bool
     */
    public function getLock(string $lockName): bool
    {
        $lock = App::get_s()->sql('SELECT GET_LOCK("' . $lockName . '", ' . $this->timeout . ')  as `lock`')->one();
        if ( ! empty($lock) && $lock['lock'] == 1)
        {
            return TRUE;
        } else
        {
            return FALSE;
        }
    }

    /**
     * @param string $lockName
     * @return bool
     */
    public function releaseLock(string $lockName): bool
    {
        $lock = App::get_s()->sql('SELECT RELEASE_LOCK("' . $lockName . '")  as `lock`')->one();
        if ( ! empty($lock) && $lock['lock'] == 1)
        {
            return TRUE;
        } else
        {
            return FALSE;
        }
    }

    /**
     * @return DatabaseLockingService
     */
    public function waitForLock(): DatabaseLockingService
    {
        $this->waitForLock = TRUE;

        return $this;
    }

    /**
     * @param int $timeout
     * @return DatabaseLockingService
     */
    public function setTimeout(int $timeout): DatabaseLockingService
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     * @throws CriticalException
     */
    public static function lock(string $name): bool
    {
        $database_locking_service = new DatabaseLockingService();
        if ($database_locking_service->getLock(substr($name, 0, self::MAX_NAME_LENGTH)))
        {
            return TRUE;
        } else
        {
            throw new CriticalException((sprintf('lock() Could not get lock on DB Lock: %s', $name)));
        }
    }

    /**
     * @param string $name
     * @return bool
     * @throws CriticalException
     */
    public static function unlock(string $name): bool
    {
        $database_locking_service = new DatabaseLockingService();
        if ($database_locking_service->releaseLock(substr($name, 0, self::MAX_NAME_LENGTH)))
        {
            return TRUE;
        } else
        {
            throw new CriticalException((sprintf('unlock() Could not release lock on DB Lock: %s', $name)));
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function is_lock(string $name): bool
    {
        $database_locking_service = new DatabaseLockingService();
        return $database_locking_service->isLocked(substr($name, 0, self::MAX_NAME_LENGTH));
    }
}
