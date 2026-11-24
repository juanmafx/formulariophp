<?php

declare(strict_types=1);

namespace ContactForm\Persistence;

use ContactForm\Security\RateLimitStore;
use PDO;

final readonly class PdoRateLimitStore implements RateLimitStore
{
    public function __construct(private PDO $pdo)
    {
    }

    public function hit(string $bucketKey, int $windowSeconds, int $now): int
    {
        $this->pdo->prepare('DELETE FROM rate_limits WHERE expires_at <= :now AND bucket_key <> :bucket_key')
            ->execute(['now' => $now, 'bucket_key' => $bucketKey]);

        $statement = $this->pdo->prepare(
            'INSERT INTO rate_limits (bucket_key, hits, expires_at) VALUES (:bucket_key, 1, :expires_at)
             ON CONFLICT (bucket_key) DO UPDATE SET
                hits = CASE WHEN rate_limits.expires_at <= :now_for_hits THEN 1 ELSE rate_limits.hits + 1 END,
                expires_at = CASE WHEN rate_limits.expires_at <= :now_for_expiry THEN excluded.expires_at ELSE rate_limits.expires_at END
             RETURNING hits',
        );
        $statement->execute([
            'bucket_key' => $bucketKey,
            'expires_at' => $now + $windowSeconds,
            'now_for_hits' => $now,
            'now_for_expiry' => $now,
        ]);
        $hits = (int) $statement->fetchColumn();
        $statement->closeCursor();

        return $hits;
    }
}
