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

---

## 🚀 Quick Start for Your Colleague

### Step 1: Start the Laravel Server
```bash
cd transactix-backend
php artisan serve
```
The API will be available at: `http://localhost:8000/api`

### Step 2: Test with Postman

#### Test 1: Register a New User
- **Method:** POST
- **URL:** `http://localhost:8000/api/register`
- **Headers:**
  - `Content-Type: application/json`
  - `Accept: application/json`
- **Body (raw JSON):**
```json
{
    "name": "Test Admin",
    "email": "admin@test.com",
    "password": "AdminPass123!",
    "password_confirmation": "AdminPass123!",
    "role": "admin"
}
```

#### Test 2: Login with the User
- **Method:** POST
- **URL:** `http://localhost:8000/api/login`
- **Headers:**
  - `Content-Type: application/json`
  - `Accept: application/json`
- **Body (raw JSON):**
```json
{
    "email": "admin@test.com",
    "password": "AdminPass123!"
}
```
**Copy the `access_token` from the response!**

#### Test 3: Get User Profile (Protected)
- **Method:** GET
- **URL:** `http://localhost:8000/api/user`
- **Headers:**
  - `Accept: application/json`
  - `Authorization: Bearer {paste_your_token_here}`

#### Test 4: Logout
- **Method:** POST
- **URL:** `http://localhost:8000/api/logout`
- **Headers:**
  - `Accept: application/json`
  - `Authorization: Bearer {paste_your_token_here}`

### Expected Results:
- ✅ **Register:** Returns 201 with user data and token
- ✅ **Login:** Returns 200 with user data and token
- ✅ **Get User:** Returns 200 with user profile
- ✅ **Logout:** Returns 200 with success message

---

## 📝 Summary for Your Colleague

**Good news!** 🎉 The authentication API is **already fully implemented** and ready to use. Here's what's available:

### ✅ **Completed Authentication Features:**
1. **User Registration** - Create new accounts with role assignment
2. **User Login** - Authenticate and receive access tokens
3. **User Logout** - Revoke tokens securely
4. **Protected Routes** - Token-based access control
5. **Role-Based Access** - Admin and Cashier roles
6. **Password Security** - Strong password requirements
7. **Error Handling** - Comprehensive validation and error responses

### 🔧 **Technical Details:**
- **Framework:** Laravel with Sanctum authentication
- **Database:** Supabase (PostgreSQL)
- **Token Type:** Bearer tokens
- **Security:** Password hashing, validation, role-based access
- **Testing:** Comprehensive test suite included

### 📋 **What Your Colleague Needs:**
1. **Postman** (or any API testing tool)
2. **Laravel server running** (`php artisan serve`)
3. **This documentation** for endpoint details

The authentication system is production-ready and follows Laravel best practices! 🚀
