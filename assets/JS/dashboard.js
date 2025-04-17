document.addEventListener("DOMContentLoaded", async () => {
    const res = await fetch("/api/auth/check_session.php");
    const data = await res.json();
  
    if (data.status !== "authenticated") {
      window.location.href = "public/login.php";
      return;
    }
  
    // عرض اسم المستخدم
    document.getElementById("userName").textContent = `أهلاً ${data.full_name} 👋`;
  });
  
  document.getElementById("logoutBtn").addEventListener("click", async () => {
    console.log("Logout button clicked");
    await fetch("/api/auth/logout.php");
    window.location.href = "login.php";
  });