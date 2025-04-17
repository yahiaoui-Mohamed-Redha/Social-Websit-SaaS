document.addEventListener('DOMContentLoaded', function() {
  const loginForm = document.getElementById('loginForm');
  const errorMessage = document.getElementById('errorMessage');
  const errorText = document.getElementById('errorText');

  loginForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      // إخفاء رسالة الخطأ
      errorMessage.classList.add('hidden');
      
      // جمع بيانات النموذج
      const formData = {
          email: document.getElementById('email').value,
          password: document.getElementById('password').value,
          remember_me: document.getElementById('remember_me').checked
      };
      
      // إرسال البيانات إلى الخادم
      fetch('../api/auth/login.php', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
          },
          body: JSON.stringify(formData)
      })
      .then(response => response.json())
      .then(data => {
          if (data.success) {
              // توجيه المستخدم إلى لوحة التحكم
              window.location.href = data.redirect;
          } else {
              showError(data.message);
          }
      })
      .catch((error) => {
          showError('حدث خطأ أثناء الاتصال بالخادم');
          console.error('Error:', error);
      });
  });
  
  function showError(message) {
      errorText.textContent = message;
      errorMessage.classList.remove('hidden');
  }
});