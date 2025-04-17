document.addEventListener('DOMContentLoaded', function() {
  const registerForm = document.getElementById('registerForm');
  const errorMessage = document.getElementById('errorMessage');
  const errorText = document.getElementById('errorText');

  registerForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      errorMessage.classList.add('hidden');
      
      // التحقق من تطابق كلمة المرور
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      
      if (password !== confirmPassword) {
          showError('كلمة المرور غير متطابقة');
          return;
      }
      
      if (password.length < 8) {
          showError('كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل');
          return;
      }
      
      if (!document.getElementById('terms').checked) {
          showError('يجب الموافقة على شروط الخدمة');
          return;
      }
      
      const formData = {
          full_name: document.getElementById('full_name').value.trim(),
          username: document.getElementById('username').value.trim(),
          email: document.getElementById('email').value.trim(),
          password: password
      };
      
      try {
          const response = await fetch('../api/auth/register.php', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
              },
              body: JSON.stringify(formData)
          });
          
          const data = await response.json();
          
          if (!response.ok) {
              throw new Error(data.message || 'خطأ في الخادم');
          }
          
          if (data.success) {
              window.location.href = data.redirect;
          } else {
              showError(data.message || 'حدث خطأ غير متوقع');
          }
      } catch (error) {
          console.error('Error:', error);
          showError(error.message || 'حدث خطأ أثناء الاتصال بالخادم');
      }
  });
  
  function showError(message) {
      errorText.textContent = message;
      errorMessage.classList.remove('hidden');
      // Scroll to the error message
      errorMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
});