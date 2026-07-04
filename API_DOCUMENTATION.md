# Lu-Academic-Hub - Backend API Documentation

## Overview
Lu-Academic-Hub is a comprehensive academic support platform providing past papers, learning materials, and advanced search functionality for students.

## Base URL
```
http://localhost/api/endpoints/
```

## Authentication
All protected endpoints require a valid user session. Include `session_id` in cookies.

### Login
**POST** `/auth.php?action=login`

```json
{
  "email": "student@edu.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "user": {
    "id": 1,
    "email": "student@edu.com",
    "first_name": "John",
    "role": "student"
  }
}
```

### Register
**POST** `/auth.php?action=register`

```json
{
  "email": "student@edu.com",
  "password": "password123",
  "first_name": "John",
  "last_name": "Doe",
  "faculty": "Engineering",
  "year_of_study": 3
}
```

### Logout
**POST** `/auth.php?action=logout`

### Get Profile
**GET** `/auth.php?action=profile`

### Update Profile
**POST** `/auth.php?action=update-profile`

```json
{
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+256700123456"
}
```

## Past Papers Endpoints

### List Past Papers
**GET** `/papers.php?action=list&page=1&limit=12`

**Query Parameters:**
- `page` (int) - Page number (default: 1)
- `limit` (int) - Items per page (default: 12)
- `course_code` (string) - Filter by course code
- `faculty` (string) - Filter by faculty
- `year` (int) - Filter by year
- `semester` (string) - Filter by semester
- `difficulty` (string) - Filter by difficulty (easy, medium, hard)
- `search` (string) - Full-text search

**Response:**
```json
{
  "papers": [
    {
      "id": 1,
      "title": "Programming 101 Past Paper",
      "course_code": "CS101",
      "downloads_count": 45,
      "rating": 4.5
    }
  ],
  "page": 1,
  "limit": 12,
  "total": 150
}
```

### Get Paper Details
**GET** `/papers.php?action=detail&id=1`

**Response includes:**
- Paper details
- Reviews and ratings
- Rating summary
- Bookmark status

### Upload Past Paper
**POST** `/papers.php?action=create`

**Form Data:**
- `file` (file) - PDF or document file
- `title` (string) - Paper title
- `course_code` (string) - Course code
- `course_name` (string) - Course name
- `faculty` (string) - Faculty
- `year` (int) - Year
- `semester` (string) - Semester
- `description` (string) - Description
- `difficulty_level` (string) - easy|medium|hard

**Response:**
```json
{
  "success": true,
  "id": 45,
  "message": "Past paper uploaded successfully. Awaiting approval."
}
```

### Rate Past Paper
**POST** `/papers.php?action=rate`

```json
{
  "resource_id": 1,
  "rating": 5,
  "review": "Very helpful paper!"
}
```

### Bookmark Past Paper
**POST** `/papers.php?action=bookmark`

```json
{
  "resource_id": 1,
  "folder": "Semester 1"
}
```

### Record Download
**POST** `/papers.php?action=download`

```json
{
  "id": 1
}
```

### Get Trending Papers
**GET** `/papers.php?action=trending&limit=10`

### Get User's Papers
**GET** `/papers.php?action=user&page=1`

### Delete Paper
**DELETE** `/papers.php?action=delete&id=1`

### Remove Bookmark
**DELETE** `/papers.php?action=unbookmark&id=1`

## Learning Materials Endpoints

Similar structure to past papers with additional parameters:

### List Materials
**GET** `/materials.php?action=list&page=1&limit=12`

**Additional Query Parameters:**
- `type` (string) - Filter by type (notes, textbook, video, tutorial, guide)
- `tag` (string) - Filter by tag

### Get Material Details
**GET** `/materials.php?action=detail&id=1`

### Upload Material
**POST** `/materials.php?action=create`

**Form Data includes:**
- `material_type` (string) - notes|textbook|video|tutorial|guide|other
- `tags` (string) - Comma-separated tags
- `difficulty_level` (string) - beginner|intermediate|advanced

### Get All Tags
**GET** `/materials.php?action=tags`

### Get Materials by Type
**GET** `/materials.php?action=bytype&type=notes&page=1`

### Material Statistics
**GET** `/materials.php?action=statistics`

## Search Endpoints

### Global Search
**GET** `/search.php?action=global&q=programming&page=1&limit=20`

**Response:**
```json
{
  "results": [
    {
      "id": 1,
      "title": "Programming Basics",
      "type": "paper|material",
      "course_code": "CS101",
      "downloads_count": 45
    }
  ],
  "count": 150,
  "page": 1,
  "limit": 20
}
```

### Advanced Search
**GET** `/search.php?action=advanced&keyword=programming&faculty=Engineering&difficulty=intermediate&sort=recent`

**Query Parameters:**
- `keyword` (string) - Search term
- `course_code` (string) - Filter by course
- `faculty` (string) - Filter by faculty
- `year` (int) - Filter by year
- `material_type` (string) - Filter by type
- `difficulty` (string) - Filter by difficulty
- `sort` (string) - recent|popular|rated
- `search_in` (string) - all|papers|materials

### Search Suggestions
**GET** `/search.php?action=suggestions&q=pro`

### Trending Searches
**GET** `/search.php?action=trending`

### Get Filter Options
**GET** `/search.php?action=courses`
**GET** `/search.php?action=faculties`
**GET** `/search.php?action=types`

## Admin Endpoints

### List Users
**GET** `/admin.php?action=users&page=1`

### Pending Papers
**GET** `/admin.php?action=pending-papers`

### Pending Materials
**GET** `/admin.php?action=pending-materials`

### Approve Paper
**POST** `/admin.php?action=approve-paper`

```json
{
  "id": 1
}
```

### Reject Paper
**POST** `/admin.php?action=reject-paper`

```json
{
  "id": 1,
  "reason": "Content violates guidelines"
}
```

### Statistics
**GET** `/admin.php?action=statistics`

**Response:**
```json
{
  "total_users": 500,
  "total_papers": 1250,
  "total_materials": 3400,
  "pending_approvals": 23
}
```

### Set User Role
**PUT** `/admin.php?action=set-role`

```json
{
  "user_id": 5,
  "role": "moderator"
}
```

### Deactivate User
**DELETE** `/admin.php?action=deactivate-user&id=5`

### Admin Logs
**GET** `/admin.php?action=logs&limit=50`

## Error Responses

All endpoints return appropriate HTTP status codes:

- `200` - OK
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `405` - Method Not Allowed
- `500` - Internal Server Error

**Error Response Format:**
```json
{
  "error": "Descriptive error message"
}
```

## Rate Limiting

- Default: 100 requests per hour per IP
- Headers returned:
  - `X-RateLimit-Limit`: Maximum requests
  - `X-RateLimit-Remaining`: Requests remaining
  - `X-RateLimit-Reset`: Unix timestamp of rate limit reset

## Pagination

Paginated responses include:

```json
{
  "data": [...],
  "page": 1,
  "limit": 12,
  "total": 150,
  "total_pages": 13,
  "has_next": true,
  "has_previous": false
}
```

## File Upload Limits

- Maximum file size: 50MB
- Allowed formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, ZIP, RAR, TXT
- All files are scanned for malicious content

## Best Practices

1. **Always validate input** - Sanitize and validate all parameters
2. **Use HTTPS** - In production, all requests should use HTTPS
3. **Handle errors gracefully** - Check error responses and handle appropriately
4. **Cache responses** - Cache frequently accessed data like trending papers
5. **Implement retry logic** - Retry failed requests with exponential backoff
6. **Use pagination** - Always paginate large result sets

## Support

For API issues or questions, contact: support@luacademic.edu
