# Auth API Documentation (V1)

## Base URL
`/api/v1/auth`

---

### 1. Login
**Endpoint:** `POST /login`

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "secret_password",
    "device_name": "iPhone 13" // Required for token tracking
}
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "token": "4|c3F...", 
        "user": {
            "id": 1,
            "name": "Ahmed Ali",
            "email": "user@example.com",
            "avatar": null,
            "status": "active",
            "roles": ["admin"],
            "permissions": ["users.view"]
        }
    }
}
```

---

### 2. Register
**Endpoint:** `POST /register`

**Request Body:**
```json
{
    "first_name": "Ahmed",
    "last_name": "Ali",
    "email": "newuser@example.com",
    "password": "strong_password",
    "device_name": "Android"
}
```

**Success Response (201 Created):**
```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "token": "5|d4G...",
        "user": { ... }
    }
}
```

---

### 3. Logout
**Endpoint:** `POST /logout`
**Headers:** `Authorization: Bearer <token>`

**Success Response (200 OK):**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

---

### 4. Get Profile
**Endpoint:** `GET /user`
**Headers:** `Authorization: Bearer <token>`

**Success Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Ahmed Ali",
        ...
    }
}
```
