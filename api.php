<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Define the history file path
$historyFile = __DIR__ . '/history.json';

// Ensure history.json exists
if (!file_exists($historyFile)) {
    file_put_contents($historyFile, json_encode([]));
}

// Function to read history
function readHistory() {
    global $historyFile;
    $data = file_get_contents($historyFile);
    return json_decode($data, true) ?: [];
}

// Function to write history
function writeHistory($data) {
    global $historyFile;
    return file_put_contents($historyFile, json_encode($data, JSON_PRETTY_PRINT));
}

// Handle GET request (fetch history)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $history = readHistory();

        // Sort by timestamp (newest first)
        usort($history, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });

        echo json_encode($history);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to read history', 'message' => $e->getMessage()]);
    }
    exit();
}

// Handle POST request (save history)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || !isset($data['action']) || $data['action'] !== 'save') {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request format']);
            exit();
        }

        if (!isset($data['data'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing history data']);
            exit();
        }

        $historyEntry = $data['data'];

        // Validate required fields
        $requiredFields = ['uniqueId', 'nickname', 'avatarThumb', 'followerCount', 'followingCount', 'heartCount', 'accountScore', 'timestamp'];
        foreach ($requiredFields as $field) {
            if (!isset($historyEntry[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Missing required field: $field"]);
                exit();
            }
        }

        $history = readHistory();

        // Remove existing entry for this user (to avoid duplicates)
        $history = array_filter($history, function($entry) use ($historyEntry) {
            return $entry['uniqueId'] !== $historyEntry['uniqueId'];
        });

        // Add new entry
        $history[] = $historyEntry;

        // Keep only the latest 1000 entries to prevent file from growing too large
        if (count($history) > 1000) {
            usort($history, function($a, $b) {
                return $b['timestamp'] - $a['timestamp'];
            });
            $history = array_slice($history, 0, 1000);
        }

        if (writeHistory($history)) {
            echo json_encode(['success' => true, 'message' => 'History saved successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save history']);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
    }
    exit();
}

// Invalid request method
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>
