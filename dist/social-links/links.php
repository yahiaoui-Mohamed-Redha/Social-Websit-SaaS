<?php
session_start();
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database configuration
require_once '../../api/db.php';
require_once '../../api/get_profile.php';
$db = new Database();
$conn = $db->getConnection();

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Handle the case where the session variable is not set
    // For example, redirect to a login page
    header("Location: ../login.php");
    exit();
}
?>
<div class="p-4">
    <div class="max-lg:max-w-2xl mx-auto">
        <div class="text-left">
            <h2 class="text-slate-900 text-2xl font-semibold mb-4">
                Create a Social Links
            </h2>
            <p class="text-sm text-slate-500">
                Change your plant according your needs
            </p>
        </div>

        <div class="grid lg:grid-cols-5 sm:grid-cols-3 gap-10 mt-10 max-sm:max-w-sm max-sm:mx-auto">

            <a href="social-links/create-links.php?user_id=<?php echo $user_id; ?>" class="cursor-pointer border-2 border-dashed text-indigo-600 border-indigo-600 bg-indigo-50 shadow rounded-md p-6 flex items-center justify-center">
                <div class="text-2xl">
                    <i class="ti ti-square-rounded-plus"></i>
                </div>
            </a>

        </div>
    </div>
</div>

<div class="p-4 bg-white rounded-2xl mt-20">
    <div class="max-lg:max-w-2xl mx-auto">
        <div class="text-left">
            <h2 class="text-slate-900 text-2xl font-semibold mb-4">
                Recent Social Links
            </h2>
            <p class="text-sm text-slate-500">
                Change your plant according your needs
            </p>
        </div>

        <div class="grid lg:grid-cols-3 sm:grid-cols-2 gap-6 mt-10 max-sm:max-w-sm max-sm:mx-auto">
            <div class="border shadow rounded-md p-6">
                <div class="mt-6">
                    <button type="button"
                        class="w-full mt-6 px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md">
                        Buy now
                    </button>
                </div>
            </div>

            <div class="border shadow rounded-md p-6">
                <div class="mt-6">
                    <button type="button"
                        class="w-full mt-6 px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md">
                        Buy now
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>