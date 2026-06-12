<?php
// Yüksek sesli çift vuruşlu zil sesi üretir (WAV, 44.1kHz, 16-bit, mono).
$sampleRate = 44100;
$duration = 2.5;
$n = (int)($sampleRate * $duration);
$samples = array_fill(0, $n, 0.0);

// Zil kısmi frekansları (tubular bell benzeri inharmonik seri)
$partials = [
    [523.25, 1.00],
    [659.25, 0.60],
    [783.99, 0.45],
    [1046.50, 0.35],
    [1318.51, 0.22],
    [2093.00, 0.12],
];

$strikes = [0.0, 0.6]; // iki vuruş
foreach ($strikes as $strikeTime) {
    $start = (int)($strikeTime * $sampleRate);
    for ($i = $start; $i < $n; $i++) {
        $t = ($i - $start) / $sampleRate;
        $env = exp(-3.0 * $t); // sönümleme
        $v = 0.0;
        foreach ($partials as [$freq, $amp]) {
            $v += $amp * sin(2 * M_PI * $freq * $t) * exp(-2.2 * $t * ($freq / 523.25));
        }
        $samples[$i] += $v * $env;
    }
}

// Normalize (yüksek ama kırpmasız)
$max = max(array_map('abs', $samples));
$gain = 0.95 / $max;

$data = '';
foreach ($samples as $s) {
    $data .= pack('v', (int)round(max(-1.0, min(1.0, $s * $gain)) * 32767) & 0xFFFF);
}

$byteRate = $sampleRate * 2;
$dataSize = strlen($data);
$wav = 'RIFF' . pack('V', 36 + $dataSize) . 'WAVE'
    . 'fmt ' . pack('V', 16) . pack('v', 1) . pack('v', 1)
    . pack('V', $sampleRate) . pack('V', $byteRate) . pack('v', 2) . pack('v', 16)
    . 'data' . pack('V', $dataSize) . $data;

$dir = __DIR__ . '/../public/assets/audio';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}
file_put_contents($dir . '/bell.wav', $wav);
echo "OK: " . $dir . "/bell.wav (" . strlen($wav) . " bytes)\n";
