// public/js/api.js
//
// This file is loaded on EVERY page via the layout.
// It provides helper functions that any page's JS can call.
// Think of it as your "toolkit" for talking to the backend.


// ============================================================
// 1. CONFIGURATION
// ============================================================

// The base URL of your API backend.
// All API calls start with this prefix.
// Change this if you deploy to a real server.
const API_URL = 'http://127.0.0.1:8000/api';


// ============================================================
// 2. TOKEN AND USER STORAGE
//
// We use localStorage to remember the user between page loads.
// localStorage is like a small database inside the browser.
// Data stays there even after closing the browser tab.
// ============================================================

// Save the JWT token to localStorage after login
function saveToken(token) {
    localStorage.setItem('token', token);
    // setItem(key, value) stores any string under a name
}

// Read the token back (returns null if not set)
function getToken() {
    return localStorage.getItem('token');
    // getItem returns null if the key doesn't exist
}

// Save the user object { id, name, email, role }
function saveUser(user) {
    // Objects cannot be stored directly — JSON.stringify converts
    // { name: "Ahmed", role: "student" } → '{"name":"Ahmed","role":"student"}'
    localStorage.setItem('user', JSON.stringify(user));
}

// Get the user object back
function getUser() {
    const raw = localStorage.getItem('user');
    if (!raw) return null;
    // JSON.parse converts the string back to an object
    return JSON.parse(raw);
}

// Are we logged in? (true if a token exists)
function isLoggedIn() {
    return getToken() !== null;
}

// Is the logged-in user a teacher?
function isTeacher() {
    const user = getUser();
    return user !== null && user.role === 'teacher';
}

// Is the logged-in user a student?
function isStudent() {
    const user = getUser();
    return user !== null && user.role === 'student';
}

// Remove everything — called on logout
function clearAuth() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
}


// ============================================================
// 3. ROUTE PROTECTION
//
// Call these functions at the TOP of any protected page's script.
// If the condition is not met, the user is sent to /login.
// ============================================================

// Page that requires any logged-in user
function requireLogin() {
    if (!isLoggedIn()) {
        window.location.href = '/login';
    }
}

// Page that requires a teacher
function requireTeacher() {
    if (!isLoggedIn() || !isTeacher()) {
        window.location.href = '/login';
    }
}

// Page that requires a student
function requireStudent() {
    if (!isLoggedIn() || !isStudent()) {
        window.location.href = '/login';
    }
}


// ============================================================
// 4. THE CORE API FUNCTION
//
// This is the engine behind all API calls.
// It uses fetch() — the modern browser way to make HTTP requests.
// All other functions (apiGet, apiPost etc.) call this one.
// ============================================================

// method   : 'GET', 'POST', 'PUT', or 'DELETE'
// endpoint : the path after /api  e.g. '/courses' or '/login'
// data     : the object to send as JSON body (null for GET)
// auth     : true = attach the JWT token in the header
async function apiCall(method, endpoint, data = null, auth = true) {

    // Build the request configuration object
    const config = {
        method: method,
        headers: {
            // Tell the server: "I'm sending JSON"
            'Content-Type': 'application/json',
            // Tell the server: "Please send me JSON back"
            'Accept': 'application/json',
        }
    };

    // If auth=true and we have a token, attach it as a Bearer token.
    // Laravel reads the Authorization header to identify the user.
    if (auth) {
        const token = getToken();
        if (token) {
            config.headers['Authorization'] = 'Bearer ' + token;
        }
    }

    // If data was provided (for POST and PUT requests),
    // convert the JS object to a JSON string and set it as the body.
    if (data !== null) {
        config.body = JSON.stringify(data);
    }

    try {
        // fetch() sends the HTTP request.
        // await pauses here until the response arrives.
        const response = await fetch(API_URL + endpoint, config);

        // Parse the response body as JSON.
        // await again because reading the body is also async.
        const json = await response.json();

        // If we get 401 (Unauthorized), our token expired or is invalid.
        // Log the user out and send them to the login page.
        if (response.status === 401) {
            clearAuth();
            window.location.href = '/login';
            return null;
        }

        // Return an object with both the parsed data and the HTTP status.
        // The status number tells us if it succeeded:
        //   200 = OK, 201 = Created, 400 = Bad Request,
        //   403 = Forbidden, 404 = Not Found, 422 = Validation error,
        //   500 = Server crashed
        return {
            data:   json,
            status: response.status
        };

    } catch (error) {
        // This runs if there's a network error (server is down, no internet)
        console.error('Erreur réseau :', error);
        showToast('Impossible de contacter le serveur.', 'danger');
        return null;
    }
}


// ============================================================
// 5. SHORTCUT FUNCTIONS
//
// These are just convenient wrappers around apiCall()
// to make the code in each page cleaner to read.
// ============================================================

// GET — load data (no body)
// Example: apiGet('/courses') or apiGet('/courses', false) for public
async function apiGet(endpoint, auth = true) {
    return await apiCall('GET', endpoint, null, auth);
}

// POST — create data or submit a form
// Example: apiPost('/login', { email, password }, false)
async function apiPost(endpoint, data, auth = true) {
    return await apiCall('POST', endpoint, data, auth);
}

// PUT — update existing data
// Example: apiPut('/courses/1', { title: 'New Title' })
async function apiPut(endpoint, data) {
    return await apiCall('PUT', endpoint, data, true);
}

// DELETE — remove data
// Example: apiDelete('/favorites/3')
async function apiDelete(endpoint) {
    return await apiCall('DELETE', endpoint, null, true);
}


// ============================================================
// 6. TOAST NOTIFICATIONS
//
// A small colored box that appears top-right for 3 seconds.
// The toast HTML element lives in the layout (app.blade.php).
// ============================================================

// message : the text to show
// type    : 'success' (green), 'danger' (red), 'info' (blue), 'warning' (yellow)
function showToast(message, type = 'success') {
    const toastEl  = document.getElementById('toast');
    const toastMsg = document.getElementById('toast-message');

    if (!toastEl || !toastMsg) return; // safety check

    // Set the text
    toastMsg.textContent = message;

    // Remove all old color classes then add the new one
    toastEl.classList.remove('bg-success', 'bg-danger', 'bg-info', 'bg-warning');
    toastEl.classList.add('bg-' + type);

    // Bootstrap's Toast object controls showing/hiding
    // delay: 3000 = disappears after 3 seconds
    const bsToast = new bootstrap.Toast(toastEl, { delay: 3000 });
    bsToast.show();
}


// ============================================================
// 7. NAVBAR UPDATE
//
// Called on every page load (see the <script> in app.blade.php).
// Reads localStorage to decide which nav items to show or hide.
// ============================================================

function updateNavbar() {
    const user = getUser();

    // If not logged in, keep the default state (guest links visible)
    if (!user) return;

    // --- Hide guest links ---
    const guestEl = document.getElementById('nav-guest');
    const guestRegEl = document.getElementById('nav-guest-register');
    if (guestEl) guestEl.classList.add('d-none');
    if (guestRegEl) guestRegEl.classList.add('d-none');

    // --- Show common logged-in links ---
    const logoutEl = document.getElementById('nav-logout');
    const usernameEl = document.getElementById('nav-username');
    if (logoutEl) logoutEl.classList.remove('d-none');
    if (usernameEl) usernameEl.classList.remove('d-none');

    // Set the user's name in the navbar
    const nameEl = document.getElementById('navbar-name');
    if (nameEl) nameEl.textContent = user.name;

    // --- Show role-specific links ---
    if (user.role === 'teacher') {
        const teacherEl = document.getElementById('nav-teacher');
        if (teacherEl) teacherEl.classList.remove('d-none');

    } else if (user.role === 'student') {
        const studentEl     = document.getElementById('nav-student');
        const favEl         = document.getElementById('nav-favorites');
        const recommendedEl = document.getElementById('nav-recommended');
        if (studentEl)     studentEl.classList.remove('d-none');
        if (favEl)         favEl.classList.remove('d-none');
        if (recommendedEl) recommendedEl.classList.remove('d-none');
    }
}


// ============================================================
// 8. LOGOUT
//
// Called by the "Déconnexion" button in the navbar.
// ============================================================

async function logout() {
    // Tell the server to blacklist this token
    // We don't await the result — even if it fails we still log out locally
    apiPost('/logout', {}).catch(() => {});

    // Clear everything from localStorage
    clearAuth();

    // Show a brief message
    showToast('Déconnexion réussie.', 'info');

    // Redirect to login after a short pause so the toast is seen
    setTimeout(() => {
        window.location.href = '/login';
    }, 800);
}


// ============================================================
// 9. UTILITY HELPERS
// Small functions used across multiple pages
// ============================================================

// Format a number as currency: 49.99 → "49.99 €"
function formatPrice(price) {
    if (price == 0) return 'Gratuit';
    return parseFloat(price).toFixed(2) + ' €';
}

// Truncate long text: "This is a very long desc..." → first N chars + "..."
function truncate(text, maxLength = 100) {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

// Show a loading spinner inside a container element
// Usage: setLoading('my-div', true)  — shows spinner
//        setLoading('my-div', false) — hides spinner
function setLoading(containerId, isLoading) {
    const el = document.getElementById(containerId);
    if (!el) return;
    if (isLoading) {
        el.innerHTML = `
            <div class="loading-center">
                <div class="spinner-border text-dark" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>`;
    }
    // When isLoading=false, the page replaces the content itself
}