#!/usr/bin/env bash
# Runs the test suite and writes path-neutral reports to reports/tests/.
set -euo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
report_dir="$root/reports/tests"
cd "$root"
mkdir -p "$report_dir"

vendor/bin/phpunit --no-progress --colors=never \
    --log-junit "$report_dir/junit.xml" \
    --testdox-text "$report_dir/testdox.txt" | tee "$report_dir/console.txt"

sed -i "s#$root/##g" "$report_dir/junit.xml" "$report_dir/testdox.txt" "$report_dir/console.txt"

php -r '
$suite = simplexml_load_file($argv[1])->testsuite;
printf("Generated (UTC): %s\nPHP: %s\nTests: %d\nAssertions: %d\nFailures: %d\nErrors: %d\nSkipped: %d\n",
    gmdate("Y-m-d H:i:s"), PHP_VERSION, $suite["tests"], $suite["assertions"], $suite["failures"], $suite["errors"], $suite["skipped"]);
' "$report_dir/junit.xml" > "$report_dir/summary.txt"
