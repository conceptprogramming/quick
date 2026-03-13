<?php
namespace Services;

class OCRService
{

    private string $apiKey;
    private string $version;
    private string $apiBase;

    public function __construct()
    {
        $this->apiKey = $_ENV['REPLICATE_API_KEY'] ?? '';
        $this->version = 'a524caeaa23495bc9edc805ab08ab5fe943afd3febed884a4f3747aa32e9cd61';
        $this->apiBase = 'https://api.replicate.com/v1';
    }

    // ── Extract text from ALL pages — merge into one string ───
    public function extractAllPages(array $imagePaths): string
    {
        if (empty($imagePaths))
            return '';

        // Step 1 — Submit all pages to Replicate in parallel
        $predictionIds = [];
        foreach ($imagePaths as $index => $imagePath) {
            $base64 = $this->imageToBase64($imagePath);
            if (!$base64)
                continue;

            $predictionId = $this->submitPrediction($base64);
            if ($predictionId) {
                $predictionIds[$index] = $predictionId;
            }
        }

        if (empty($predictionIds))
            return '';

        // Step 2 — Poll all predictions until complete
        $results = $this->pollAllPredictions($predictionIds);

        // Step 3 — Merge all page texts in order
        $fullText = '';
        foreach ($imagePaths as $index => $path) {
            if (isset($results[$index]) && !empty($results[$index])) {
                $fullText .= "\n\n--- Page " . ($index + 1) . " ---\n\n";
                $fullText .= $results[$index];
            }
        }

        return trim($fullText);
    }

    // ── Submit single prediction to Replicate ─────────────────
    private function submitPrediction(string $base64Image): ?string
    {
        $payload = [
            'version' => $this->version,
            'input' => ['image' => $base64Image],
        ];

        $ch = curl_init($this->apiBase . '/predictions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Authorization: Token ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 201 || !$response)
            return null;

        $data = json_decode($response, true);
        return $data['id'] ?? null;
    }

    // ── Poll all predictions until all complete ───────────────
    private function pollAllPredictions(array $predictionIds): array
    {
        $results = [];
        $pending = $predictionIds;
        $maxWait = 120; // max 2 minutes total
        $elapsed = 0;
        $interval = 1;   // poll every 1 second

        while (!empty($pending) && $elapsed < $maxWait) {
            sleep($interval);
            $elapsed += $interval;

            foreach ($pending as $index => $predictionId) {
                $status = $this->getPredictionStatus($predictionId);

                if (!$status)
                    continue;

                if ($status['status'] === 'succeeded') {
                    $output = $status['output'] ?? '';
                    // Replicate returns array or string
                    if (is_array($output)) {
                        $output = implode('', $output);
                    }
                    $results[$index] = trim($output);
                    unset($pending[$index]);
                }

                if ($status['status'] === 'failed' || $status['status'] === 'canceled') {
                    $results[$index] = '';
                    unset($pending[$index]);
                }
            }
        }

        return $results;
    }

    // ── Get single prediction status ──────────────────────────
    private function getPredictionStatus(string $predictionId): ?array
    {
        $ch = curl_init($this->apiBase . '/predictions/' . $predictionId);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Token ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response)
            return null;
        return json_decode($response, true);
    }

    // ── Convert image file to base64 data URI ─────────────────
    private function imageToBase64(string $imagePath): ?string
    {
        if (!file_exists($imagePath))
            return null;

        $imageData = file_get_contents($imagePath);
        if (!$imageData)
            return null;

        $base64 = base64_encode($imageData);
        return 'data:image/png;base64,' . $base64;
    }
}
