<?php

function processCsv($filename) {
    // Read CSV file
    $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    // Remove BOM if exists
    $lines[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $lines[0]);
    
    // Initialize the result array
    $result = [];
    
    // Skip first two lines (header)
    for ($i = 2; $i < count($lines); $i++) {
        // Parse CSV line
        $data = str_getcsv($lines[$i], ";");
        
        // Get relevant fields
        $branch = $data[0];  // Alan Adı
        $city = $data[1];    // İl Adı
        $district = $data[2]; // İlçe Adı
        $school = $data[3];   // Kurum Adı
        $score = $data[6];    // KPSS Puanı
        $appointmentCount = $data[7]; // Atama Sayısı
        
        // Create structure if not exists
        if (!isset($result[$branch])) {
            $result[$branch] = [];
        }
        if (!isset($result[$branch][$city])) {
            $result[$branch][$city] = [
                'total_appointments' => 0,
                'districts' => [],
                'highest_score' => 0,
                'lowest_score' => 999,
                'schools' => []
            ];
        }
        
        // Update statistics
        $result[$branch][$city]['total_appointments'] += (int)$appointmentCount;
        $result[$branch][$city]['highest_score'] = max($result[$branch][$city]['highest_score'], (float)$score);
        $result[$branch][$city]['lowest_score'] = min($result[$branch][$city]['lowest_score'], (float)$score);
        
        // Add district if not exists
        if (!in_array($district, $result[$branch][$city]['districts'])) {
            $result[$branch][$city]['districts'][] = $district;
        }
        
        // Add school info
        $result[$branch][$city]['schools'][] = [
            'name' => $school,
            'district' => $district,
            'score' => (float)$score,
            'appointments' => (int)$appointmentCount
        ];
    }
    
    // Convert to JSON
    return json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

// Usage
$filename = "input/2023.csv";
$json = processCsv($filename);

// Output JSON to file
file_put_contents('generated/2023.json', $json);

echo "JSON file has been created successfully!";