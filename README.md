# TikTok History API Setup Instructions

## Files Created:
- `api.php` - The main API endpoint that handles GET and POST requests
- `history.json` - JSON file that stores the history data

## Setup Instructions:

### 1. Upload Files to Server
Upload both `api.php` and `history.json` to your server at:
```
https://apps.calculatly.com/apps/tiktok/
```

So the final URLs should be:
- `https://apps.calculatly.com/apps/tiktok/api.php`
- `https://apps.calculatly.com/apps/tiktok/history.json`

### 2. Set File Permissions
Make sure the server has write permissions for both files:
- `api.php` should be executable (644 or 755)
- `history.json` should be writable by the web server (666 or 766)

### 3. Test the API

#### Test GET Request:
```bash
curl "https://apps.calculatly.com/apps/tiktok/api.php?action=get"
```
Should return: `[]` (empty array)

#### Test POST Request:
```bash
curl -X POST "https://apps.calculatly.com/apps/tiktok/api.php" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "save",
    "data": {
      "uniqueId": "testuser",
      "nickname": "Test User",
      "avatarThumb": "https://example.com/avatar.jpg",
      "followerCount": 1000,
      "followingCount": 500,
      "heartCount": 5000,
      "accountScore": 75,
      "timestamp": 1638360000000
    }
  }'
```
Should return: `{"success": true, "message": "History saved successfully"}`

### 4. Verify History Storage
After a successful POST, check that `history.json` contains the data:
```bash
curl "https://apps.calculatly.com/apps/tiktok/history.json"
```

## API Features:

### GET `/api.php?action=get`
- Returns all stored history entries as JSON array
- Sorted by timestamp (newest first)
- Handles CORS properly

### POST `/api.php`
- Saves new history entry
- Prevents duplicates (updates existing user entries)
- Limits to 1000 most recent entries
- Validates required fields
- Returns success/error responses

## Required Fields for POST:
- `uniqueId` (string)
- `nickname` (string)
- `avatarThumb` (string URL)
- `followerCount` (number)
- `followingCount` (number)
- `heartCount` (number)
- `accountScore` (number)
- `timestamp` (number)

## Troubleshooting:

1. **400 Error**: Check that all required fields are included in the POST data
2. **500 Error**: Check file permissions and PHP error logs
3. **Empty Response**: Verify the API endpoint URL is correct
4. **CORS Issues**: The API includes proper CORS headers

## Security Notes:
- This is a basic implementation for demonstration
- In production, consider adding authentication, rate limiting, and input sanitization
- The `history.json` file should not be directly accessible via web (consider moving it outside web root)

Once uploaded and permissions are set correctly, the global shared history should work perfectly!
