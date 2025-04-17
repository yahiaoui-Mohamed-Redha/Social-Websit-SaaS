<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - SocialFolio</title>
    <link rel="stylesheet" href="../src/output.css">
</head>
<body class="bg-gray-100 font-sans">
    <!-- شريط التنقل -->
    <nav class="bg-white shadow">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="/" class="text-2xl font-bold text-indigo-600">SocialFolio</a>
            <div class="flex items-center space-x-4">
                <a href="/dashboard.html" class="px-4 py-2 text-gray-700 hover:text-indigo-600">لوحة التحكم</a>
                <button id="logoutBtn" class="px-4 py-2 text-gray-700 hover:text-indigo-600">تسجيل الخروج</button>
                <img src="/assets/images/default-profile.jpg" alt="صورة الملف الشخصي" class="w-10 h-10 rounded-full">
            </div>
        </div>
    </nav>

    <!-- محتوى لوحة التحكم -->
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row gap-8">
            <!-- القائمة الجانبية -->
            <aside class="w-full md:w-64 bg-white rounded-lg shadow p-4">
                <ul class="space-y-2">
                    <li><a href="#" class="block px-4 py-2 text-indigo-600 bg-indigo-50 rounded">نظرة عامة</a></li>
                    <li><a href="#profile-section" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">الملف الشخصي</a></li>
                    <li><a href="#links-section" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">الروابط الاجتماعية</a></li>
                    <li><a href="#social-page-section" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">صفحتي الاجتماعية</a></li>
                    <li><a href="#appearance-section" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">المظهر</a></li>
                </ul>
            </aside>

            <!-- المحتوى الرئيسي -->
            <main class="flex-1 bg-white rounded-lg shadow p-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">مرحبًا بك في لوحة التحكم</h1>
                
                <!-- قسم الملف الشخصي -->
                <section id="profile-section" class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">الملف الشخصي</h2>
                    <form id="profile-form">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">الاسم الكامل</label>
                                <input type="text" id="full_name" name="full_name" class="w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">اسم المستخدم</label>
                                <input type="text" id="username" name="username" class="w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">نبذة عنك</label>
                                <textarea id="bio" name="bio" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                            </div>
                            <div>
                                <label for="profile_pic" class="block text-sm font-medium text-gray-700 mb-1">صورة الملف الشخصي</label>
                                <input type="file" id="profile_pic" name="profile_pic" class="w-full">
                            </div>
                        </div>
                        <button type="submit" class="mt-4 px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">حفظ التغييرات</button>
                    </form>
                </section>

                <!-- قسم الروابط الاجتماعية -->
                <section id="links-section" class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">الروابط الاجتماعية</h2>
                    <div class="space-y-4">
                        <div class="p-4 border rounded-lg">
                            <h3 class="font-medium mb-2">إضافة رابط جديد</h3>
                            <form id="add-link-form" class="flex flex-col md:flex-row gap-4">
                                <select name="platform" class="px-4 py-2 border rounded-lg flex-1">
                                    <option value="instagram">Instagram</option>
                                    <option value="tiktok">TikTok</option>
                                    <option value="youtube">YouTube</option>
                                    <option value="twitter">Twitter</option>
                                    <option value="linkedin">LinkedIn</option>
                                    <option value="other">أخرى</option>
                                </select>
                                <input type="text" name="url" placeholder="رابط الحساب" class="px-4 py-2 border rounded-lg flex-1">
                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">إضافة</button>
                            </form>
                        </div>

                        <div id="links-list" class="space-y-3">
                            <!-- سيتم ملء الروابط هنا عبر JavaScript -->
                        </div>
                    </div>
                </section>

                <!-- قسم الصفحة الاجتماعية -->
                <section id="social-page-section">
                    <h2 class="text-xl font-semibold mb-4">صفحتي الاجتماعية</h2>
                    <form id="social-page-form">
                        <div class="space-y-4">
                            <div>
                                <label for="page_title" class="block text-sm font-medium text-gray-700 mb-1">عنوان الصفحة</label>
                                <input type="text" id="page_title" name="page_title" class="w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label for="page_description" class="block text-sm font-medium text-gray-700 mb-1">وصف الصفحة</label>
                                <textarea id="page_description" name="page_description" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                            </div>
                            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">حفظ الصفحة</button>
                        </div>
                    </form>
                </section>
            </main>
        </div>
    </div>

    <script src="../assets/js/dashboard.js"></script>
</body>
</html>