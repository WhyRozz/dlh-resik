<nav class="admin-navbar">
    <button class="menu-toggle-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
</nav>

<style>
.admin-navbar {
    height: 70px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    margin-left: 260px; /* Lebar Sidebar */
    transition: margin-left 0.3s;
}

.menu-toggle-btn { display: none; background: none; border: none; font-size: 20px; cursor: pointer; }

.navbar-search {
    background: #f0f0f0;
    border-radius: 20px;
    padding: 5px 15px;
    display: flex;
    align-items: center;
    width: 300px;
}
.navbar-search input { border: none; background: transparent; width: 100%; outline: none; padding: 5px; }
.navbar-search button { background: none; border: none; cursor: pointer; color: #888; }

.navbar-user { display: flex; align-items: center; gap: 10px; }
.avatar { width: 35px; height: 35px; background: #2e8b57; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }

@media (max-width: 768px) {
    .admin-navbar { margin-left: 0; }
    .menu-toggle-btn { display: block; }
    .navbar-search { display: none; } /* Hide search on mobile */
}
</style>
