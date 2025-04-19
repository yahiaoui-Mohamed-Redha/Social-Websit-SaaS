<?php
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
    header("Location: ../../public/login.php");
    exit();
}


// الحصول على معلومات البورتفوليو
$stmt = $conn->prepare("SELECT * FROM portfolios WHERE user_id = ?");
$stmt->execute([$user['id']]);
$portfolio = $stmt->fetch(PDO::FETCH_ASSOC);

// الحصول على الروابط الاجتماعية
$stmt = $conn->prepare("SELECT * FROM social_links WHERE user_id = ?");
$stmt->execute([$user['id']]);
$social_links = $stmt->fetchAll(PDO::FETCH_ASSOC);

// الحصول على إحصائيات المتابعين
$stmt = $conn->prepare("SELECT * FROM follower_stats WHERE user_id = ?");
$stmt->execute([$user['id']]);
$follower_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// زيادة عدد المشاهدات
if (!isset($_SESSION['viewed_portfolio_' . $user['id']])) {
    $_SESSION['viewed_portfolio_' . $user['id']] = true;
    $stmt = $pdo->prepare("UPDATE portfolios SET views = views + 1 WHERE user_id = ?");
    $stmt->execute([$user['id']]);
}
?>

    <div class="min-h-screen">
        <!-- المحتوى الرئيسي -->
        <div class="max-w-3xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                <!-- صورة الغلاف -->
                <div class="h-32 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
                
                <!-- معلومات الملف الشخصي -->
                <div class="px-6 sm:px-8 -mt-16">
                    <div class="flex flex-col items-center">
                        <img class="h-32 w-32 rounded-full border-4 border-white object-cover" src="<?php echo !empty($user['profile_pic']) ? 'assets/images/' . $user['profile_pic'] : 'https://via.placeholder.com/150'; ?>" alt="Profile Picture">
                        <h1 class="mt-4 text-2xl font-bold text-gray-900"><?php echo $user['full_name'] ?? $user['username']; ?></h1>
                        <p class="mt-2 text-gray-600"><?php echo $portfolio['title'] ?? 'ملفي الشخصي'; ?></p>
                    </div>
                    
                    <!-- الوصف -->
                    <div class="mt-6 text-center">
                        <p class="text-gray-700"><?php echo $portfolio['description'] ?? 'مرحبًا، هذا هو ملف التعريف الخاص بي'; ?></p>
                    </div>
                    
                    <!-- الإحصائيات -->
                    <div class="mt-8 grid grid-cols-3 divide-x divide-gray-200 text-center">
                        <div class="px-4 py-2">
                            <p class="text-sm font-medium text-gray-500">المشاهدات</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900"><?php echo number_format($portfolio['views'] ?? 0); ?></p>
                        </div>
                        <div class="px-4 py-2">
                            <p class="text-sm font-medium text-gray-500">الروابط</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900"><?php echo count($social_links); ?></p>
                        </div>
                        <div class="px-4 py-2">
                            <p class="text-sm font-medium text-gray-500">المتابعون</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">
                                <?php 
                                $total_followers = 0;
                                foreach ($follower_stats as $stat) {
                                    $total_followers += $stat['follower_count'];
                                }
                                echo number_format($total_followers);
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- الروابط الاجتماعية -->
                <div class="mt-8 px-6 pb-8 sm:px-8">
                    <h2 class="text-xl font-semibold text-gray-900">روابط التواصل</h2>
                    
                    <?php if (empty($social_links)): ?>
                        <p class="mt-4 text-gray-500">لا توجد روابط متاحة حاليًا.</p>
                    <?php else: ?>
                        <div class="mt-4 grid grid-cols-1 gap-4">
                            <?php foreach ($social_links as $link): ?>
                                <?php 
                                $platform = strtolower($link['platform']);
                                $icon = '';
                                $color = 'gray';
                                
                                switch ($platform) {
                                    case 'instagram':
                                        $icon = 'fab fa-instagram';
                                        $color = 'bg-gradient-to-r from-purple-500 to-pink-500';
                                        break;
                                    case 'tiktok':
                                        $icon = 'fab fa-tiktok';
                                        $color = 'bg-black';
                                        break;
                                    case 'youtube':
                                        $icon = 'fab fa-youtube';
                                        $color = 'bg-red-600';
                                        break;
                                    case 'twitter':
                                        $icon = 'fab fa-twitter';
                                        $color = 'bg-blue-400';
                                        break;
                                    case 'linkedin':
                                        $icon = 'fab fa-linkedin';
                                        $color = 'bg-blue-700';
                                        break;
                                    default:
                                        $icon = 'fas fa-link';
                                        $color = 'bg-indigo-600';
                                }
                                
                                // البحث عن عدد المتابعين لهذا الرابط
                                $follower_count = 0;
                                foreach ($follower_stats as $stat) {
                                    if (strtolower($stat['platform']) === $platform && $stat['username'] === $link['url']) {
                                        $follower_count = $stat['follower_count'];
                                        break;
                                    }
                                }
                                ?>
                                
                                <a href="<?php echo $link['url']; ?>" target="_blank" class="group relative flex items-center space-x-3 rounded-lg border border-gray-200 bg-white px-6 py-5 shadow-sm hover:border-gray-400 focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full <?php echo $color; ?> flex items-center justify-center text-white">
                                            <i class="<?php echo $icon; ?>"></i>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="focus:outline-none">
                                            <span class="absolute inset-0" aria-hidden="true"></span>
                                            <p class="text-sm font-medium text-gray-900"><?php echo ucfirst($platform); ?></p>
                                            <?php if ($follower_count > 0): ?>
                                                <p class="truncate text-sm text-gray-500"><?php echo number_format($follower_count); ?> متابع</p>
                                            <?php else: ?>
                                                <p class="truncate text-sm text-gray-500"><?php echo $link['url']; ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 self-center">
                                        <i class="fas fa-chevron-left text-gray-400 group-hover:text-gray-600"></i>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- حقوق النشر -->
            <div class="mt-8 text-center text-sm text-gray-500">
                <p>تم الإنشاء باستخدام SocialFolio</p>
            </div>
        </div>
    </div>