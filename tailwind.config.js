/** @type {import('tailwindcss').Config} */
module.exports = {
    // content: ["./dist/**/*.{html,js,php}", 
    //   "./dist/*.{html,js,php}", 
    //   "./src/**/*.{html,js,php}", 
    //   "./src/*.{html,js,php}",],

    content: [
      "./src/**/*.{html,js,php}",
      "./dist/**/*.{html,js,php}",
      "./public/**/*.{html,js,php}",
      "./**/*.php"
    ],
    darkMode: 'class',
    theme: {
      extend: {
        colors: {
          sidebar:"#f9f9f9",
          primary: {"50":"#eff6ff","100":"#dbeafe","200":"#bfdbfe","300":"#93c5fd","400":"#60a5fa","500":"#3b82f6","600":"#2563eb","700":"#1d4ed8","800":"#1e40af","900":"#1e3a8a","950":"#172554"},
          goldp:"rgb(254, 204, 11)",
          blue550: "rgb(206 219 246 / 30%)",
          blue650:"rgb(5 107 227)",
          logout: "#f60347",
        },
        fontFamily: {
          bodyfont: ["Poppins"],
          'body': [
            'Inter', 
            'ui-sans-serif', 
            'system-ui', 
            '-apple-system', 
            'system-ui', 
            'Segoe UI', 
            'Roboto', 
            'Helvetica Neue', 
            'Arial', 
            'Noto Sans', 
            'sans-serif', 
            'Apple Color Emoji', 
            'Segoe UI Emoji', 
            'Segoe UI Symbol', 
            'Noto Color Emoji'
          ],
            'sans': [
            'Inter', 
            'ui-sans-serif', 
            'system-ui', 
            '-apple-system', 
            'system-ui', 
            'Segoe UI', 
            'Roboto', 
            'Helvetica Neue', 
            'Arial', 
            'Noto Sans', 
            'sans-serif', 
            'Apple Color Emoji', 
            'Segoe UI Emoji', 
            'Segoe UI Symbol', 
            'Noto Color Emoji'
          ]
        }
      },
    },
    plugins: [
      require('tailwind-scrollbar'),
    ],
  }  