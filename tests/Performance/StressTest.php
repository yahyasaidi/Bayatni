<?php
use PHPUnit\Framework\TestCase;

class ThirdStressTest extends TestCase
{
    private $baseUrl = 'http://localhost/development';

    public function testStressDifferentEndpoints()
    {
        $batchSize = 100;
        $totalRequests = 1000;
        $errors = 0;
        $times = [];

        $endpoints = [
            '/app/controllers/bookings/booking.php',
            '/app/auth/signin.php',
            '/app/auth/signup.php',
            '/app/auth/forgotpwd.php',
            '/app/auth/resetpwd.php'
        ];

        for ($sent = 0; $sent < $totalRequests; $sent += $batchSize) {
            $multiHandle = curl_multi_init();
            $curlHandles = [];

            for ($i = 0; $i < $batchSize; $i++) {
                $endpoint = $endpoints[array_rand($endpoints)];
                $url = $this->baseUrl . $endpoint;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_multi_add_handle($multiHandle, $ch);
                $curlHandles[] = $ch;
            }

            $start = microtime(true);
            $running = null;
            do {
                curl_multi_exec($multiHandle, $running);
                curl_multi_select($multiHandle);
            } while ($running > 0);
            $end = microtime(true);

            foreach ($curlHandles as $ch) {
                $content = curl_multi_getcontent($ch);
                if ($content === false) {
                    $errors++;
                }
                curl_multi_remove_handle($multiHandle, $ch);
                curl_close($ch);
            }

            curl_multi_close($multiHandle);

            $times[] = $end - $start;
            echo "Lot de $batchSize requêtes vers des endpoints différents terminées dans " . round($end - $start, 2) . " secondes\n";
        }

        echo "\nTotal Errors: $errors / $totalRequests requests\n";
        $averageTime = array_sum($times) / count($times);
        echo "Average time per 100 requests: " . round($averageTime, 2) . " seconds\n";

        $this->assertEquals(0, $errors, "Some requests failed.");
    }
}
