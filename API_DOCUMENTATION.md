# Transactix Authentication API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication Overview
This API uses Laravel Sanctum for token-based authentication. After successful login or registration, you'll receive an access token that must be included in the Authorization header for protected routes.

---

## 🔐 Authentication Endpoints

### 1. User Registration

**Endpoint:** `POST /api/register`

**Description:** Register a new user account

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!",
    "role": "cashier"
}
```

**Request Parameters:**
- `name` (string, required): User's full name (max 255 characters)
- `email` (string, required): Valid email address (max 255 characters)
- `password` (string, required): Password with minimum 8 characters, mixed case, numbers, and symbols
- `password_confirmation` (string, required): Must match the password field
- `role` (string, optional): User role - "admin" or "cashier" (defaults to "cashier")

**Success Response (201 Created):**
```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": "user_12345",
            "name": "John Doe",
            "email": "john@example.com",
            "role": "cashier"
        },
        "access_token": "1|abcdef123456...",
        "token_type": "Bearer"
    }
}
```

**Error Response (422 Validation Error):**
```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

---

### 2. User Login

**Endpoint:** `POST /api/login`

**Description:** Authenticate user and receive access token

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "Password123!"
}
```

**Request Parameters:**
- `email` (string, required): User's email address
- `password` (string, required): User's password

**Success Response (200 OK):**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": "user_12345",
            "name": "John Doe",
            "email": "john@example.com",
            "role": "cashier"
        },
        "access_token": "2|xyz789abc456...",
        "token_type": "Bearer"
    }
}
```

**Error Response (401 Unauthorized):**
```json
{
    "success": false,
    "message": "Invalid credentials"
}
```

---

### 3. User Logout

**Endpoint:** `POST /api/logout`

**Description:** Logout user and revoke access token

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {access_token}
```

**Request Body:** None required

**Success Response (200 OK):**
```json
{
    "success": true,
    "message": "Successfully logged out"
}
```

**Error Response (401 Unauthorized):**
```json
{
    "message": "Unauthenticated."
}
```

---

## 📋 Postman Collection Setup

### Step 1: Create New Collection
1. Open Postman
2. Click "New" → "Collection"
3. Name it "Transactix Authentication API"

### Step 2: Set Collection Variables
1. Go to Collection → Variables tab
2. Add these variables:
   - `base_url`: `http://localhost:8000/api`
   - `access_token`: (leave empty, will be set automatically)

### Step 3: Add Requests

#### Request 1: Register User
- **Method:** POST
- **URL:** `{{base_url}}/register`
- **Headers:**
  - `Content-Type`: `application/json`
  - `Accept`: `application/json`
- **Body (raw JSON):**
```json
{
    "name": "Test User",
    "email": "test@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!",
    "role": "cashier"
}
```

#### Request 2: Login User
- **Method:** POST
- **URL:** `{{base_url}}/login`
- **Headers:**
  - `Content-Type`: `application/json`
  - `Accept`: `application/json`
- **Body (raw JSON):**
```json
{
    "email": "test@example.com",
    "password": "Password123!"
}
```
- **Tests Script:** (to auto-save token)
```javascript
if (pm.response.code === 200) {
    const response = pm.response.json();
    pm.collectionVariables.set("access_token", response.data.access_token);
}
```

#### Request 3: Logout User
- **Method:** POST
- **URL:** `{{base_url}}/logout`
- **Headers:**
  - `Content-Type`: `application/json`
  - `Accept`: `application/json`
  - `Authorization`: `Bearer {{access_token}}`

---

## 🧪 Testing Instructions

### Prerequisites
1. Ensure Laravel server is running: `php artisan serve`
2. Ensure Supabase is properly configured
3. Database migrations are run: `php artisan migrate`

### Test Flow
1. **Register a new user** using the register endpoint
2. **Login with the user** to get an access token
3. **Use the token** for protected routes (like logout)
4. **Test logout** to revoke the token

### Sample Test Data
```json
{
    "name": "Admin User",
    "email": "admin@transactix.com",
    "password": "AdminPass123!",
    "password_confirmation": "AdminPass123!",
    "role": "admin"
}
```

```json
{
    "name": "Cashier User",
    "email": "cashier@transactix.com",
    "password": "CashierPass123!",
    "password_confirmation": "CashierPass123!",
    "role": "cashier"
}
```

---

## 🔧 Additional Protected Endpoints

Once authenticated, you can access these protected endpoints:

- `GET /api/user` - Get current user profile
- `PUT /api/user/profile` - Update user profile
- `GET /api/products` - Get all products
- `POST /api/products` - Create new product (admin only)
- `PUT /api/products/{id}` - Update product (admin only)
- `DELETE /api/products/{id}` - Delete product (admin only)

All protected endpoints require the `Authorization: Bearer {token}` header.

**Success Response (201 Created):**
```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "cashier"
        },
        "access_token": "1|abc123def456...",
        "token_type": "Bearer"
    }
}
```

**Error Response (422 Validation Error):**
```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password must contain at least one symbol."]
    }
}
```

---

### 2. User Login

**Endpoint:** `POST /login`

**Description:** Authenticate user and get access token

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "Password123!"
}
```

**Request Parameters:**
- `email` (string, required): User's email address
- `password` (string, required): User's password

**Success Response (200 OK):**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "cashier"
        },
        "access_token": "2|xyz789abc123...",
        "token_type": "Bearer"
    }
}
```

**Error Response (401 Unauthorized):**
```json
{
    "success": false,
    "message": "Invalid credentials"
}
```

**Error Response (422 Validation Error):**
```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

---

### 3. User Logout

**Endpoint:** `POST /logout`

**Description:** Logout user and revoke access token

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {access_token}
```

**Request Body:** None (empty)

**Success Response (200 OK):**
```json
{
    "success": true,
    "message": "Successfully logged out"
}
```

**Error Response (401 Unauthorized):**
```json
{
    "message": "Unauthenticated."
}
```

---

### 4. Get Current User (Protected Route)

**Endpoint:** `GET /user`

**Description:** Get authenticated user's information

**Headers:**
```
Accept: application/json
Authorization: Bearer {access_token}
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "cashier"
        }
    }
}
```

**Error Response (401 Unauthorized):**
```json
{
    "message": "Unauthenticated."
}
```

---

## Authentication Flow

### For Frontend Implementation:

1. **Registration/Login:**
   - Send POST request to `/register` or `/login`
   - Store the `access_token` from the response (localStorage, sessionStorage, or secure cookie)
   - Store user information if needed

2. **Making Authenticated Requests:**
   - Include the token in the Authorization header: `Bearer {access_token}`
   - Example: `Authorization: Bearer 1|abc123def456...`

3. **Logout:**
   - Send POST request to `/logout` with the token
   - Clear stored token and user data from frontend

4. **Token Handling:**
   - Tokens don't expire by default but are revoked on logout
   - Handle 401 responses by redirecting to login page

---

## Error Handling

All error responses follow this structure:
```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field_name": ["Specific error message"]
    }
}
```

Common HTTP status codes:
- `200` - Success
- `201` - Created (registration)
- `401` - Unauthorized (invalid credentials or token)
- `422` - Validation Error
- `500` - Server Error

---

## Example Frontend Usage (JavaScript)

```javascript
// Registration
const register = async (userData) => {
    const response = await fetch('/api/register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(userData)
    });

    const data = await response.json();
    if (data.success) {
        localStorage.setItem('token', data.data.access_token);
        localStorage.setItem('user', JSON.stringify(data.data.user));
    }
    return data;
};

// Login
const login = async (credentials) => {
    const response = await fetch('/api/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(credentials)
    });

    const data = await response.json();
    if (data.success) {
        localStorage.setItem('token', data.data.access_token);
        localStorage.setItem('user', JSON.stringify(data.data.user));
    }
    return data;
};

// Logout
const logout = async () => {
    const token = localStorage.getItem('token');
    const response = await fetch('/api/logout', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
        }
    });

    localStorage.removeItem('token');
    localStorage.removeItem('user');
    return response.json();
};

// Authenticated request
const getUser = async () => {
    const token = localStorage.getItem('token');
    const response = await fetch('/api/user', {
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
        }
    });
    return response.json();
};
```

---

## Testing the API

You can test these endpoints using tools like Postman, Insomnia, or curl:

```bash
# Register
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"Password123!","password_confirmation":"Password123!"}'

# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"test@example.com","password":"Password123!"}'

# Get user (replace TOKEN with actual token)
curl -X GET http://localhost:8000/api/user \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN"

# Logout
curl -X POST http://localhost:8000/api/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN"
```
