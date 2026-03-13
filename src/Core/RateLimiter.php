<?php
namespace Core;

use Database;

class RateLimiter
{
    private static function cleanup(string $identifier, string $action, int $window): void
    {
        $db = Database::getInstance();
        $db->prepare("
            DELETE FROM rate_limits
            WHERE identifier = :id
              AND action      = :action
              AND TIMESTAMPDIFF(SECOND, window_start, UTC_TIMESTAMP()) >= :window
        ")->execute(['id' => $identifier, 'action' => $action, 'window' => $window]);
    }

    // Returns true if allowed, false if blocked
    public static function check(string $identifier, string $action): bool
    {
        $limits = RATE_LIMITS[$action] ?? null;
        if (!$limits) return true;

        $max    = $limits['max'];
        $window = $limits['window'];
        $db     = Database::getInstance();

        self::cleanup($identifier, $action, $window);

        // Get current hits
        $st = $db->prepare("
            SELECT hits FROM rate_limits
            WHERE identifier = :id AND action = :action
            LIMIT 1
        ");
        $st->execute(['id' => $identifier, 'action' => $action]);
        $row = $st->fetch();

        if (!$row) {
            // First hit in this window
            $db->prepare("
                INSERT INTO rate_limits (identifier, action, hits, window_start)
                VALUES (:id, :action, 1, UTC_TIMESTAMP())
            ")->execute(['id' => $identifier, 'action' => $action]);
            return true;
        }

        // Already at max — blocked
        if ($row['hits'] >= $max) {
            return false;
        }

        // Increment hits
        $db->prepare("
            UPDATE rate_limits SET hits = hits + 1
            WHERE identifier = :id AND action = :action
        ")->execute(['id' => $identifier, 'action' => $action]);

        return true;
    }

    public static function isBlocked(string $identifier, string $action): bool
    {
        $limits = RATE_LIMITS[$action] ?? null;
        if (!$limits) return false;

        self::cleanup($identifier, $action, $limits['window']);

        $db = Database::getInstance();
        $st = $db->prepare("
            SELECT hits FROM rate_limits
            WHERE identifier = :id AND action = :action
            LIMIT 1
        ");
        $st->execute(['id' => $identifier, 'action' => $action]);
        $row = $st->fetch();

        return (int) ($row['hits'] ?? 0) >= (int) $limits['max'];
    }

    public static function block(string $identifier, string $action): void
    {
        $limits = RATE_LIMITS[$action] ?? null;
        if (!$limits) return;

        $db = Database::getInstance();
        self::cleanup($identifier, $action, $limits['window']);

        $st = $db->prepare("
            SELECT id FROM rate_limits
            WHERE identifier = :id AND action = :action
            LIMIT 1
        ");
        $st->execute(['id' => $identifier, 'action' => $action]);
        $row = $st->fetch();

        if ($row) {
            $db->prepare("
                UPDATE rate_limits
                SET hits = :hits, window_start = UTC_TIMESTAMP()
                WHERE id = :row_id
            ")->execute([
                'hits' => $limits['max'],
                'row_id' => $row['id'],
            ]);
            return;
        }

        $db->prepare("
            INSERT INTO rate_limits (identifier, action, hits, window_start)
            VALUES (:id, :action, :hits, UTC_TIMESTAMP())
        ")->execute([
            'id' => $identifier,
            'action' => $action,
            'hits' => $limits['max'],
        ]);
    }

    // Get remaining attempts
    public static function remaining(string $identifier, string $action): int
    {
        $limits = RATE_LIMITS[$action] ?? null;
        if (!$limits) return 999;

        self::cleanup($identifier, $action, $limits['window']);

        $db = Database::getInstance();
        $st = $db->prepare("
            SELECT hits FROM rate_limits
            WHERE identifier = :id AND action = :action
            LIMIT 1
        ");
        $st->execute(['id' => $identifier, 'action' => $action]);
        $row = $st->fetch();

        return max(0, $limits['max'] - ($row['hits'] ?? 0));
    }
}
