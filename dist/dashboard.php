<?php
session_start();
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database configuration
require_once '../api/db.php';
require_once '../api/get_profile.php';
$db = new Database();
$conn = $db->getConnection();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Handle language change
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    if (in_array($lang, ['en', 'fr', 'ar'])) {
        // Set the language cookie (expires in 30 days)
        setcookie('language', $lang, time() + (86400 * 30), "/");
        // Update the language immediately for the current request's context
        $_COOKIE['language'] = $lang;

        // --- Start Revised Redirect Logic ---

        // Parse the current request URI
        $url_parts = parse_url($_SERVER['REQUEST_URI']);
        $query_params = [];

        // Parse the existing query string into an array if it exists
        if (isset($url_parts['query'])) {
            parse_str($url_parts['query'], $query_params);
        }

        // Remove the 'lang' parameter from the array
        unset($query_params['lang']);

        // Rebuild the query string from the modified array
        $new_query_string = http_build_query($query_params);

        // Construct the final redirect URL using the original path
        $redirect_url = $url_parts['path'];
        // Append the new query string if it's not empty
        if (!empty($new_query_string)) {
            $redirect_url .= '?' . $new_query_string;
        }

        // Redirect to the cleaned URL
        header("Location: " . $redirect_url);
        exit();

        // --- End Revised Redirect Logic ---
    }
}

// Get language from cookie (default to English)
$language = $_COOKIE['language'] ?? 'en';
$isRTL = ($language == 'ar');

// Fetch user details
$user_id = $_SESSION['user_id'];
$user = getUser($user_id);

// Get the contentpage parameter from the URL
$contentpage = isset($_GET['contentpage']) ? $_GET['contentpage'] : 'statistiques/statistiques.php';

// Define translations in PHP
$translations = [
    'en' => [
        'dashboardLabel' => 'Dashboard',
        'profileLabel' => 'Profile',
        'pageLabel' => 'Social Page',
        'settingsLabel' => 'Settings',
        'dashboardLink' => 'Overview',
        'profileLink' => 'Profile',
        'linksLink' => 'Social Links',
        'socialPageLink' => 'My Social Page',
        'appearanceLink' => 'Appearance',
        'settingsLink' => 'Settings',
        'logoutLink' => 'Logout'
    ],
    'fr' => [
        'dashboardLabel' => 'Tableau de bord',
        'profileLabel' => 'Profil',
        'pageLabel' => 'Page Sociale',
        'settingsLabel' => 'Paramètres',
        'dashboardLink' => 'Aperçu',
        'profileLink' => 'Profil',
        'linksLink' => 'Liens Sociaux',
        'socialPageLink' => 'Ma Page Sociale',
        'appearanceLink' => 'Apparence',
        'settingsLink' => 'Paramètres',
        'logoutLink' => 'Déconnexion'
    ],
    'ar' => [
        'dashboardLabel' => 'لوحة التحكم',
        'profileLabel' => 'الملف الشخصي',
        'pageLabel' => 'الصفحة الاجتماعية',
        'settingsLabel' => 'الإعدادات',
        'dashboardLink' => 'نظرة عامة',
        'profileLink' => 'الملف الشخصي',
        'linksLink' => 'الروابط الاجتماعية',
        'socialPageLink' => 'صفحتي الاجتماعية',
        'appearanceLink' => 'المظهر',
        'settingsLink' => 'الإعدادات',
        'logoutLink' => 'تسجيل الخروج'
    ]
];

// Get current translations
$currentTranslations = $translations[$language] ?? $translations['en'];
?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>" dir="<?php echo $isRTL ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialFolio - Dashboard</title>
    <link rel="stylesheet" href="../src/output.css">
    <style>
        .active {
            background-color: #EEF2FF;
            color: #4F46E5;
        }
        
        /* Loading Animation */
        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #4F46E5;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Content Page Opacity */
        #contentpage {
            transition: opacity 0.5s ease-in-out;
        }

        #contentpage.loaded {
            opacity: 1;
        }
        
        /* Custom styles */
        .profile-pic-upload {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .profile-pic-preview {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #E5E7EB;
        }
        
        .link-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background-color: #F9FAFB;
            border-radius: 0.5rem;
            border: 1px solid #E5E7EB;
        }
        
        .link-platform {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .platform-icon {
            width: 24px;
            height: 24px;
        }

        /* RTL-specific styles */
        [dir="rtl"] .sidebar {
            right: 0;
            left: auto;
            border-right: none;
            border-left: 1px solid #E5E7EB;
        }

        [dir="rtl"] .content {
            margin-right: 17rem;
            margin-left: 0;
        }

        [dir="rtl"] .header {
            padding-right: 17rem;
            padding-left: 0;
        }

        [dir="rtl"] .flex-row-reverse {
            flex-direction: row-reverse;
        }

        [dir="rtl"] .mr-3 {
            margin-right: 0 !important;
            margin-left: 0.75rem !important;
        }

        [dir="rtl"] .ml-3 {
            margin-left: 0 !important;
            margin-right: 0.75rem !important;
        }

        [dir="rtl"] .text-right {
            text-align: left;
        }

        [dir="rtl"] .text-left {
            text-align: right;
        }

        /* Scrollbar styling */
        .sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: #E5E7EB #F9FAFB;
        }

        .sidebar-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-scroll::-webkit-scrollbar-track {
            background: #F9FAFB;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background-color: #E5E7EB;
            border-radius: 6px;
        }
    </style>
</head>
<body class="bg-gray-100">

    <header class="header fixed w-full float-<?php echo $isRTL ? 'left' : 'right'; ?> z-[97] bg-white shadow-md p-4 flex justify-between items-center">
        <div class="flex justify-center items-center">
            <h1 class="text-2xl font-bold text-indigo-600">SocialFolio</h1>
        </div>
        <div class="flex items-center space-x-9 <?php echo $isRTL ? 'flex-row-reverse' : ''; ?>">
            <!-- User Information -->
            <div class="flex items-center <?php echo $isRTL ? 'flex-row-reverse' : ''; ?>">
                <img src="<?php echo $user['profile_pic'] ? '../uploads/' . $user['profile_pic'] : '../assets/image/default-profile.jpg'; ?>" alt="User" class="h-10 w-10 rounded-full <?php echo $isRTL ? 'ml-3' : 'mr-3'; ?>">
                <div class="<?php echo $isRTL ? 'text-right' : 'text-left'; ?>">
                    <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></p>
                    <p class="text-xs text-gray-500">@<?php echo htmlspecialchars($user['username']); ?></p>
                </div>
            </div>

            <!-- Language Selector -->
            <select id="languageSelector" class="p-2 border border-gray-300 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="en" <?php echo $language == 'en' ? 'selected' : ''; ?>>English</option>
                <option value="fr" <?php echo $language == 'fr' ? 'selected' : ''; ?>>Français</option>
                <option value="ar" <?php echo $language == 'ar' ? 'selected' : ''; ?>>العربية</option>
            </select>
        </div>
    </header>

    <!-- Sidebar - Position changes based on RTL -->
    <aside class="sidebar z-[97] fixed flex flex-col justify-start top-14 <?php echo $isRTL ? 'right-0' : 'left-0'; ?> min-w-[16.3rem] h-screen pt-2 overflow-hidden bg-white shadow-lg transition-transform <?php echo $isRTL ? '-translate-x-full sm:translate-x-0' : '-translate-x-full sm:translate-x-0'; ?>" aria-label="Sidebar">
        <div class="mt-6 pl-5 pb-4 overflow-y-auto sidebar-scroll">
            <nav class="w-full pr-5 nav fixed-on-h632 -mx-3 bottom-6 top-[90px] flex flex-col flex-1 justify-between space-y-4">

                <div class="space-y-4">
                    <div class="space-y-2.5">
                        <label class="px-3 text-xs font-semibold text-gray-500 uppercase"><?php echo $currentTranslations['dashboardLabel']; ?></label>

                        <a class="flex items-center px-3 py-2 mt-2 text-gray-600 transition-colors duration-300 transform rounded-lg hover:bg-indigo-50 hover:text-indigo-600" href="statistiques/statistiques.php">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" />
                            </svg>
                            <span class="mx-2 text-sm font-medium"><?php echo $currentTranslations['dashboardLink']; ?></span>
                        </a>
                    </div>

                    <div class="space-y-2.5">
                        <label class="px-3 text-xs font-semibold text-gray-500 uppercase"><?php echo $currentTranslations['profileLabel']; ?></label>

                        <a class="flex items-center px-3 py-2 mt-2 text-gray-600 transition-colors duration-300 transform rounded-lg hover:bg-indigo-50 hover:text-indigo-600" href="profile/profile.php">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
                                <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path>
                                <path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"></path>
                                <path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855"></path>
                            </svg>
                            <span class="mx-2 text-sm font-medium"><?php echo $currentTranslations['profileLink']; ?></span>
                        </a>

                        <a class="flex items-center px-3 py-2 mt-2 text-gray-600 transition-colors duration-300 transform rounded-lg hover:bg-indigo-50 hover:text-indigo-600" href="links/links.php">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
                                <path d="M9 15l6 -6"></path>
                                <path d="M11 6l.463 -.536a5 5 0 0 1 7.071 7.072l-.534 .464"></path>
                                <path d="M13 18l-.397 .534a5.068 5.068 0 0 1 -7.127 0a4.972 4.972 0 0 1 0 -7.071l.524 -.463"></path>
                            </svg>
                            <span class="mx-2 text-sm font-medium"><?php echo $currentTranslations['linksLink']; ?></span>
                        </a>
                    </div>

                    <div class="space-y-2.5">
                        <label class="px-3 text-xs font-semibold text-gray-500 uppercase"><?php echo $currentTranslations['pageLabel']; ?></label>

                        <a class="flex items-center px-3 py-2 mt-2 text-gray-600 transition-colors duration-300 transform rounded-lg hover:bg-indigo-50 hover:text-indigo-600" href="social-page/social-page.php">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
                                <path d="M3 19a9 9 0 0 1 9 0a9 9 0 0 1 9 0"></path>
                                <path d="M3 6a9 9 0 0 1 9 0a9 9 0 0 1 9 0"></path>
                                <path d="M3 6l0 13"></path>
                                <path d="M12 6l0 13"></path>
                                <path d="M21 6l0 13"></path>
                            </svg>
                            <span class="mx-2 text-sm font-medium"><?php echo $currentTranslations['socialPageLink']; ?></span>
                        </a>

                        <a class="flex items-center px-3 py-2 mt-2 text-gray-600 transition-colors duration-300 transform rounded-lg hover:bg-indigo-50 hover:text-indigo-600" href="appearance/appearance.php">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
                                <path d="M12 21a9 9 0 0 0 9 -9a9 9 0 0 0 -9 -9a9 9 0 0 0 -9 9a9 9 0 0 0 9 9z"></path>
                                <path d="M12 3c-3.866 0 -7 3.272 -7 7a7 7 0 0 0 7 7a7 7 0 0 0 7 -7a7 7 0 0 0 -7 -7z"></path>
                                <path d="M12 7v2"></path>
                                <path d="M12 15v2"></path>
                                <path d="M12 11v2"></path>
                            </svg>
                            <span class="mx-2 text-sm font-medium"><?php echo $currentTranslations['appearanceLink']; ?></span>
                        </a>
                    </div>
                </div>

                <div class="space-y-2.5">
                    <label class="px-3 text-xs font-semibold text-gray-500 uppercase"><?php echo $currentTranslations['settingsLabel']; ?></label>

                    <a class="flex items-center px-3 py-2 mt-2 text-gray-600 transition-colors duration-300 transform rounded-lg hover:bg-indigo-50 hover:text-indigo-600" href="settings/settings.php">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.15.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="mx-2 text-sm font-medium"><?php echo $currentTranslations['settingsLink']; ?></span>
                    </a>

                    <a class="flex items-center px-3 py-2 mt-2 text-gray-600 transition-colors duration-300 transform rounded-lg hover:bg-red-50 hover:text-red-600" href="#" onclick="logout()">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="1.5">
                            <path d="M10 8v-2a2 2 0 0 1 2 -2h7a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-7a2 2 0 0 1 -2 -2v-2"></path>
                            <path d="M15 12h-12l3 -3"></path>
                            <path d="M6 15l-3 -3"></path>
                        </svg>
                        <span class="mx-2 text-sm font-medium"><?php echo $currentTranslations['logoutLink']; ?></span>
                    </a>
                </div>
            </nav>
        </div>
    </aside>

    <div class="content <?php echo $isRTL ? 'mr-[17rem]' : 'ml-[17rem]'; ?> w-[calc(100%-16.3rem)] float-<?php echo $isRTL ? 'left' : 'right'; ?>">
        
        <div class="px-6 py-8 mt-20" id="contentpage"></div>
    </div>

    <script src="../node_modules/jquery/dist/jquery.min.js"></script>
    <script src="../apexcharts/dist/apexcharts.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <script>
        // Function to load pages dynamically
        function loadPage(page) {
            // Show loading animation
            $("#contentpage").html("<div class='loader'></div>");

            // Save the current page in sessionStorage
            sessionStorage.setItem("currentPage", page);

            // Simulate a delay for the loading animation (optional)
            setTimeout(function() {
                $.ajax({
                    type: "GET",
                    url: page,
                    success: function(data) {
                        // Add content to the contentpage element
                        $("#contentpage").html(data);

                        // Set the active link in the sidebar
                        setActiveLink();

                        // Push the new state to the browser's history
                        history.pushState({ page: page }, "", "dashboard.php?contentpage=" + page);
                        const currentPage = window.location.href;

                        // Load and execute JavaScript specific to the loaded page
                        if (window.location.href.includes('manage_users.php')) {
                            $.getScript('js/manage_users.js', function() {
                                console.log('manage_users.js loaded and executed');
                            });
                        }

                        if (window.location.href.includes('gerer_pn.php')) {
                            $.getScript('js/gerer_pn.js', function() {
                                console.log('gerer_pn.js loaded and executed');
                            });
                        }

                        if (window.location.href.includes('gerer_rp.php')) {
                            $.getScript('js/gerer_rp.js', function() {
                                console.log('gerer_rp.js loaded and executed');
                            });
                        }

                        if (window.location.href.includes('gerer_ord.php')) {
                            $.getScript('js/gerer_ord.js', function() {
                                console.log('gerer_ord.js loaded and executed');
                            });
                        }

                        if (window.location.href.includes('statistiques.php')) {
                            $.getScript('js/statistiques.js', function() {
                                console.log('statistiques.js loaded and executed');
                            });
                        }

                        if (window.location.href.includes('manage_type_panne.php')) {
                            $.getScript('js/gerer_typpn.js', function() {
                                console.log('gerer_typpn.js loaded and executed');
                            });
                        }

                        if (window.location.href.includes('P-GFI.php')) {
                            $.getScript('js/P-GFI.js', function() {
                                console.log('P-GFI.js loaded and executed');
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        alert("An error occurred while loading the page: " + error);
                    }
                });
            }, 500);
        }


        // Set the active link in the sidebar
        function setActiveLink() {
            var currentPage = sessionStorage.getItem("currentPage") || 'dashboard/dashboard.php';

            // Remove active class from all links
            $("aside a").removeClass("active");

            // Activate the appropriate link based on the current page
            if (currentPage.includes("profile")) {
                $("aside a[href*='profile']").addClass("active");
            } else if (currentPage.includes("links")) {
                $("aside a[href*='links']").addClass("active");
            } else if (currentPage.includes("social-page")) {
                $("aside a[href*='social-page']").addClass("active");
            } else if (currentPage.includes("appearance")) {
                $("aside a[href*='appearance']").addClass("active");
            } else if (currentPage.includes("settings")) {
                $("aside a[href*='settings']").addClass("active");
            } else {
                $("aside a[href*='statistiques']").addClass("active");
            }
        }

        // Update page title based on content
        function updatePageTitle() {
            const currentPage = sessionStorage.getItem("currentPage") || 'dashboard/dashboard.php';
            const isArabic = document.documentElement.lang === 'ar';
            let title = "Dashboard";
            
            if (currentPage.includes("profile")) {
                title = isArabic ? "الملف الشخصي" : "Profile";
            } else if (currentPage.includes("links")) {
                title = isArabic ? "الروابط الاجتماعية" : "Social Links";
            } else if (currentPage.includes("social-page")) {
                title = isArabic ? "الصفحة الاجتماعية" : "Social Page";
            } else if (currentPage.includes("appearance")) {
                title = isArabic ? "المظهر" : "Appearance";
            } else if (currentPage.includes("settings")) {
                title = isArabic ? "الإعدادات" : "Settings";
            }
            
            document.title = "SocialFolio - " + title;
        }

        // Logout
        function logout() {
            if (confirm("Are you sure you want to log out?")) {
                sessionStorage.clear();
                window.location.href = "../api/auth/logout.php";
            }
        }

        // Load the current page when the document is ready
        $(document).ready(function() {
            // Load the current page from sessionStorage or default to 'statistiques.php'
            var currentPage = sessionStorage.getItem("currentPage");
            if (currentPage) {
                loadPage(currentPage);
            } else {
                loadPage('statistiques/statistiques.php?admin_id=<?php echo $user_id; ?>'); // Default page
            }

            // Handle clicks on links with class="load-page-link"
            $(document).on("click", "a.load-page-link", function(event) {
                event.preventDefault();
                var page = $(this).attr("href");
                loadPage(page);
            });

            // Delegate events to all links in the sidebar
            $("aside").on("click", "a[href]", function(event) {
                event.preventDefault();
                var page = $(this).attr("href");
                if (page !== "#") {
                    loadPage(page);
                }
            });

            // Manage language selector
            $("select[name='language']").on("change", function() {
                var language = $(this).val();
                localStorage.setItem("selectedLanguage", language);
                alert("Language changed to " + language);
            });

            // Handle form submission using AJAX
            $('#createAccountForm').on('submit', function(e) {
                e.preventDefault();
                $('#createAccountForm').html('<div class="loader"></div>');
                $.ajax({
                    url: 'create_users.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response === "Data inserted successfully") {
                            alert("Account created successfully!");
                            window.location.href = 'admin_page.php?contentpage=gerer_les_comptes/manage_users.php';
                        } else {
                            alert("Error: " + response);
                            $('#createAccountForm').load('create_users.php #createAccountForm', function() {
                                toggleFields();
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        alert("An error occurred while submitting the form: " + error);
                        $('#createAccountForm').load('create_users.php #createAccountForm', function() {
                            toggleFields();
                        });
                    }
                });
            });

            $('#panneForm').on('submit', function(e) {
                e.preventDefault();
                $('#panneForm').html('<div class="loader"></div>');
                $.ajax({
                    url: 'gerer_pn/signaler_des_panne.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response === "Data inserted successfully") {
                            alert("Account created successfully!");
                            window.location.href = 'admin_page.php?contentpage=gerer_pn/signaler_des_panne.php';
                        } else {
                            alert("Error: " + response);
                            $('#createAccountForm').load('gerer_pn/signaler_des_panne.php #panneForm', function() {
                                toggleFields();
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        alert("An error occurred while submitting the form: " + error);
                        $('#panneForm').load('gerer_pn/signaler_des_panne.php #panneForm', function() {
                            toggleFields();
                        });
                    }
                });
            });
        });

        $(window).on("popstate", function(event) {
            if (event.originalEvent.state) {
                var page = event.originalEvent.state.page;
                loadPage(page);
            }
        });

        // Language Selector Event Listener
        document.getElementById("languageSelector").addEventListener("change", function (e) {
            const selectedLanguage = e.target.value;
            // Redirect to the same page with lang parameter
            window.location.href = window.location.pathname + '?lang=' + selectedLanguage + 
                                 (window.location.search.includes('contentpage=') ? 
                                 '&' + window.location.search.split('?')[1] : '');
        });
    </script>
</body>
</html>