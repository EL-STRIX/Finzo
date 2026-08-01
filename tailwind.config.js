/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './public/views/**/*.php',
    './public/views/**/*.html',
    './public/assets/js/**/*.js'
  ],
  theme: {
    extend: {
      colors: {
        'primary': '#002645',
        'surface': '#ffffff',
        'on-surface': '#1a1c1e'
      },
      fontFamily: {
        'headline': ['Plus Jakarta Sans', 'sans-serif'],
        'body': ['Inter', 'sans-serif']
      }
    }
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/container-queries')
  ],
}
