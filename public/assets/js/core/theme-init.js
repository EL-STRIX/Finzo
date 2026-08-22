(function(){
  var t = localStorage.getItem('finzo-theme') || 'light';
  document.documentElement.setAttribute('data-theme', t);
  if(t === 'dark') document.documentElement.classList.add('dark');
})();
