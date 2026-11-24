<?php

declare(strict_types=1);

namespace ContactForm\Tests\Unit;

use ContactForm\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    #[DataProvider('clientAddresses')]
    public function testNormalizesClientNetworkForRateLimiting(string $address, string $expected): void
    {
        self::assertSame($expected, (new Request('GET', '/', [], null, $address))->clientNetwork());
    }

    public static function clientAddresses(): array
    {
        return [
            'ipv4 stays exact' => ['192.0.2.10', '192.0.2.10'],
            'ipv6 groups by /64' => ['2001:db8:1:2:3:4:5:6', '2001:db8:1:2::/64'],
            'ipv6 sibling host' => ['2001:db8:1:2:ffff::1', '2001:db8:1:2::/64'],
            'ipv6 uppercase' => ['2001:DB8:1:2::A', '2001:db8:1:2::/64'],
            'ipv4-mapped ipv6' => ['::ffff:192.0.2.10', '192.0.2.10'],
            'unparseable value' => ['unknown', 'unknown'],
        ];
    }
}
