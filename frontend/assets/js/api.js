// ============================================================
// EDIT THIS ONE LINE once you know your backend's URL.
// Example: "https://api.yoursite.com/api"
// ============================================================
const API_BASE = "https://YOUR-BACKEND-DOMAIN.example.com/api";

function getToken() {
    return localStorage.getItem("token");
}

function setSession(token, user) {
    localStorage.setItem("token", token);
    localStorage.setItem("user", JSON.stringify(user));
}

function getUser() {
    const raw = localStorage.getItem("user");
    return raw ? JSON.parse(raw) : null;
}

function clearSession() {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
}

// Wrapper around fetch(): attaches the Bearer token, sends/parses JSON,
// and redirects to login.html if the backend says we're not authenticated.
async function api(path, { method = "GET", body } = {}) {
    const headers = { "Content-Type": "application/json" };
    const token = getToken();
    if (token) headers["Authorization"] = "Bearer " + token;

    const res = await fetch(API_BASE + path, {
        method,
        headers,
        body: body ? JSON.stringify(body) : undefined,
    });

    if (res.status === 401) {
        clearSession();
        window.location.href = "login.html";
        return Promise.reject(new Error("Not logged in"));
    }

    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
        throw new Error(data.error || "Request failed");
    }
    return data;
}

// Call at the top of every protected page.
function requireLogin() {
    if (!getToken()) {
        window.location.href = "login.html";
    }
}

function requireAdmin() {
    requireLogin();
    const user = getUser();
    if (!user || user.role !== "admin") {
        window.location.href = "login.html";
    }
}

function logout() {
    api("/logout.php", { method: "POST" }).finally(() => {
        clearSession();
        window.location.href = "login.html";
    });
}

function money(n) {
    return "₦" + Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str ?? "";
    return div.innerHTML;
}
