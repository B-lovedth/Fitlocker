<aside class="dashboard-navbar" id="sideNav">
    <div id="topSide">
        <a href="homepage.php" class="home-link">
            <img src="./assets/Logos/fitlocker-mini-logo.png" alt="mini-logo" id="miniLogo">
        </a>
        <a>
            <button
                type="button"
                class="nav-icon btn"
                data-page="dashboard"
                id="dashboardIcon"
                onclick="switchActive(this), window.location.href='dashboard.php'">
                <img src="./assets/icons/dashboard-icon.svg" alt="dashboard-icon">
            </button>
        </a>
        <a>
            <button
                type="button"
                class="nav-icon btn"
                data-page="registerClient"
                id="registerIcon"
                onclick="switchActive(this), window.location.href='registerClient.php'">
                <img src="./assets/icons/edit.svg" alt="">
            </button>
        </a>
        <a>
            <button
                type="button"
                class="nav-icon btn"
                data-page="search"
                id="searchIcon"
                onclick="switchActive(this), window.location.href='search.php'">
                <img src="./assets/icons/search.svg" alt="search-icon">
            </button>
        </a>
    </div>
    <div id="bottomSide">
        <button type="button" class="nav-icon" id="helpIcon" onclick="window.location.href='./about.php#contactUs'">
            <img src="./assets/icons/question-circle.svg" alt="">
        </button>
        <button type="button" class="nav-icon" id="accountsIcon">
            <img src="./assets/icons/avatar.svg" alt="profile-icon">
        </button>
        <button type="button" class="nav-icon logoutIcon" id="logoutIcon">
            <img src="./assets/icons/expand.svg" alt="profile-icon">
        </button>

    </div>
</aside>